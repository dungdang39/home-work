<?php

namespace App\Admin\Controller;

use App\Member\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

/**
 * 운영진 설정
 * @TODO: 관리자화면 확인 후 개발 진행
 */
class AdministratorController
{
    private MemberService $member_service;

    public function __construct(
        MemberService $member_service
    ) {
        $this->member_service = $member_service;
    }

    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $theme_path = $request->getAttribute('theme_path');

        // @todo 로그인 체크

        return $view->render($response, $theme_path . '/admin/administrator_form.html');
    }
}
