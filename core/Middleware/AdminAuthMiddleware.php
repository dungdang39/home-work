<?php

namespace Core\Middleware;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * AdminAuth Middleware
 */
class AdminAuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $login_member = $request->getAttribute('login_member');

        if (!$login_member) {
            throw new Exception('로그인이 필요합니다.');
        }
        // @todo 관리자 권한 추가 체크

        return $handler->handle($request);
    }
}
