<?php

namespace Core;

use Exception;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;

class BaseController
{
    protected $container;
    protected Messages $flash;

    // 플래시 메시지에서 에러 메시지를 저장할 때 사용할 키
    public const ERROR_FLASH_KEY = 'errors';

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->flash = $container->get('flash');
    }

    /**
     * 의존성 주입된 컨테이너에서 속성을 동적으로 가져오는 매직 메서드
     * 
     * @param string $property 컨테이너에서 가져올 속성의 이름
     * @return mixed 요청된 속성이 존재하면 반환, 그렇지 않으면 null
     */
    public function __get($property)
    {
        if ($this->container->has($property)) {
            return $this->container->get($property);
        }

        return null;
    }

    /**
     * 리디렉션을 처리하는 공통 메서드
     * 
     * @param Response $response 서버 응답 객체
     * @param string $url 리디렉션할 URL
     * @param int $status HTTP 상태 코드 (기본값 302)
     * @return Response 리디렉션된 응답 객체
     */
    protected function redirect(Response $response, string $url, ?int $status = 302): Response
    {
        if (empty($url)) {
            $url = '/';
        }

        return $response->withHeader('Location', $url)->withStatus($status);
    }

    /**
     * 리디렉션을 처리하는 공통 메서드
     * 
     * @param Request $request 클라이언트 요청 객체
     * @param Response $response 서버 응답 객체
     * @param string $route_name 리디렉션할 라우터 이름
     * @param array $route_params 경로 파라미터 (경로 이름을 사용할 경우)
     * @param array $query_params 쿼리 스트링 파라미터
     * @param int $status HTTP 상태 코드 (기본값 302)
     * @return Response 리디렉션된 응답 객체
     * @throws Exception 경로 이름을 사용할 때 경로 파라미터가 누락된 경우
     */
    protected function redirectRoute(
        Request $request,
        Response $response,
        string $route_name,
        array $route_params = [],
        array $query_params = [],
        int $status = 302
    ): Response {
        try {
            $route_context = RouteContext::fromRequest($request);
            $url = $route_context->getRouteParser()->urlFor($route_name, $route_params, $query_params);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirect($response, $url, $status);
    }

    /**
     * 컨트롤러 공통 예외 처리 메서드
     * 
     * 예외 발생 시 플래시 메시지에 에러 내용을 저장하고
     * 사용자를 이전 페이지로 리디렉션함.
     * 
     * @param Request $request 클라이언트 요청 객체
     * @param Response $response 서버 응답 객체
     * @param Exception $e 발생한 예외 객체
     * @return Response 리디렉션된 응답 객체
     */
    protected function handleException(Request $request, Response $response, Exception $e): Response
    {
        $this->flash->addMessage(self::ERROR_FLASH_KEY, $e->getMessage());

        $referer = $request->getHeaderLine('Referer');
        if (empty($referer) || !filter_var($referer, FILTER_VALIDATE_URL)) {
            $referer = '/'; // 기본값으로 루트 설정
        }

        // 로깅 추가 (선택적)
        // $this->logger->error($e->getMessage(), ['exception' => $e]);

        return $this->redirect($response, $referer);
    }
}
