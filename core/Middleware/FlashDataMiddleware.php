<?php

namespace Core\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Flash\Messages;

/**
 * 이전 요청 데이터를 세션(flash)에 저장하는 미들웨어
 */
class FlashDataMiddleware implements MiddlewareInterface
{
	protected $flash;

    public function __construct(Messages $flash)
	{
		$this->flash = $flash;
	}

	public function process(Request $request, RequestHandler $handler): Response
    {
		$this->flash->addMessage('old', $request->getParsedBody());

		return $handler->handle($request);
    }
}