<?php

namespace Core\Middleware;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * 최고관리자 체크 미들웨어
 */
class SuperAdminAuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        if (!is_super_admin($config, $mb_id)) {
            throw new Exception('해당 메뉴는 최고관리자만 접근할 수 있습니다.');
        }

        return $handler->handle($request);
    }
}