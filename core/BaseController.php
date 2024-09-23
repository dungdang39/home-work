<?php

namespace Core;

use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;

class BaseController
{
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
        $route_context = RouteContext::fromRequest($request);
        $url = $route_context->getRouteParser()->urlFor($route_name, $route_params, $query_params);

        return $response->withRedirect($url, $status);
    }
}
