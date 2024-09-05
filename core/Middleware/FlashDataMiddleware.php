<?php

namespace Core\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;

/**
 * 이전 요청 데이터를 세션에 저장하는 미들웨어
 * @todo: 예외 발생시에만 저장하도록 변경
 */
class FlashDataMiddleware implements MiddlewareInterface
{
	public function process(Request $request, RequestHandler $handler): Response
    {
		if (!empty($_SESSION['_flash.old'])) {
			$_SESSION['_flash.new'] = $_SESSION['_flash.old'];
		} else {
			unset($_SESSION['_flash.new']);
		}

        unset($_SESSION['_flash.old']);

		$_SESSION['_flash.old'] = $request->getParsedBody();

		$response = $handler->handle($request);

        return $response;
    }
}