<?php

namespace Core\Middleware;

use App\Base\Service\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;

/**
 * 최고관리자 체크 미들웨어
 */
class SuperAdminAuthMiddleware
{
    private ConfigService $config_service;

    public function __construct(
        ConfigService $config_service,
    ) {
        $this->config_service = $config_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $member = $request->getAttribute('login_member');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        if (!$this->config_service->isSuperAdmin($mb_id)) {
            throw new HttpForbiddenException($request, '해당 메뉴는 최고관리자만 접근할 수 있습니다.');
        }

        return $handler->handle($request);
    }
}