<?php

namespace Core\Middleware;

use App\Base\Service\MemberService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;

/**
 * LoginMember Middleware
 */
class LoginMemberMiddleware
{
    private MemberService $member_service;

    public function __construct(MemberService $member_service)
    {
        $this->member_service = $member_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $mb_id = isset($_SESSION['ss_mb_id']) ? $_SESSION['ss_mb_id'] : '';
        $member = $this->member_service->fetchMemberById($mb_id);
        
        if ($member) {
            if ($member['mb_leave_date'] && $member['mb_leave_date'] <= date("Ymd")) {
                throw new Exception('탈퇴한 회원입니다.');
            }
            if ($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date("Ymd")) {
                throw new Exception('차단된 회원입니다.');
            }
        }

        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('login_member', $member);
        $request = $request->withAttribute('login_member', $member);

        return $handler->handle($request);
    }
}
