<?php

namespace Core\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;

/**
 * LoginAuth Middleware
 */
class LoginAuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $login_member = $request->getAttribute('login_member');
        if (!$login_member) {
            throw new HttpForbiddenException($request, '로그인이 필요합니다.');
        }

        return $handler->handle($request);
    }
}
