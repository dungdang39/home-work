<?php

namespace Core\Handlers;

use Core\AppConfig;
use Core\Exception\Renderers\JsonErrorRenderer;
use Core\Extension\FlashExtension;
use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use PDOException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Http\Response;

/**
 * HTTP Error Handler
 */
class HttpErrorHandler extends SlimErrorHandler
{
    private container $container;

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        Container $container,
        ?LoggerInterface $logger = null,
    ) {
        $this->container = $container;
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    /**
     * 응답에 사용할 HTTP 상태 코드 결정
     * 
     * - HttpException이 아니더라도 코드가 설정되어 있으면 해당 코드를 사용
     * 
     * @return int HTTP 상태 코드
     * @see SlimErrorHandler::determineStatusCode()
     */
    protected function determineStatusCode(): int
    {
        if ($this->method === 'OPTIONS') {
            return 200;
        }

        return (int)$this->exception->getCode() ?: 500;
    }

    /**
     * 응답 생성
     * 
     * - HTML 형식의 응답인 경우, 에러 메시지 및 이전 입력값을 플래시 메시지로 저장하고 이전 페이지로 리다이렉트.
     * - 그 외의 경우, JSON 형식으로 응답
     * 
     * @return ResponseInterface 응답 객체
     * @see SlimErrorHandler::respond()
     */
    protected function respond(): ResponseInterface
    {
        if ($this->exception instanceof PDOException) {
            $this->statusCode = 500;
        }

        // 기본 응답 형식이 HTML 형식이고 GET 요청이 아닌 경우
        // GET 요청인 경우, Referer로 인해 무한 리다이렉트가 발생할 수 있음
        if ($this->contentType === 'text/html' && $this->request->getMethod() !== 'GET') {
            if ($this->exception instanceof PDOException) {
                $message = 'DB operator error' . ($this->displayErrorDetails ? ': ' . $this->exception->getMessage() : '');
            } else {
                $message = $this->exception->getMessage();
            }

            /** @var Response $response */
            $response = $this->responseFactory->createResponse($this->statusCode);

            $flash = $this->container->get('flash');
            $flash->addMessage(FlashExtension::OLD_FLASH_KEY, $this->request->getParsedBody());
            $flash->addMessage(FlashExtension::ERROR_FLASH_KEY, $message);

            $referer = $this->request->getHeaderLine('Referer');
            if (empty($referer) || !filter_var($referer, FILTER_VALIDATE_URL)) {
                $referer = AppConfig::getInstance()->get('BASE_URL') ?? '/';
            }

            return $response->withRedirect($referer);
        }

        // 그 외의 응답 형식은 JSON으로 처리
        $this->registerErrorRenderer('application/json', JsonErrorRenderer::class);
        $this->forceContentType('application/json');
        
        return parent::respond();
    }
}
