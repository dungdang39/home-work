<?php

namespace App\Admin\Controller;

use App\Member\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
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

        return $view->render($response, '/admin/dashboard.html');
    }
}