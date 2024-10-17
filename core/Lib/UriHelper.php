<?php

namespace Core\Lib;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class UriHelper
{
    /**
     * 기본 URL(스키마 + 호스트 + 베이스 경로)을 반환합니다.
     *
     * @param Request $request PSR-7 요청 객체
     * @return string 스키마와 호스트, 베이스 경로를 포함하는 기본 URL 문자열.
     */
    public function getBaseUrl(Request $request): string
    {
        $routeContext = RouteContext::fromRequest($request);
        return rtrim($this->getSchemeAndHost($request) . $routeContext->getBasePath(), '/');
    }

    /**
     * 요청 URI의 전체 경로를 반환합니다.
     *
     * 스키마, 호스트, 경로를 포함한 전체 URI를 반환합니다.
     *
     * @param Request $request PSR-7 요청 객체
     * @return string 스키마, 호스트, 경로를 포함한 전체 URI 문자열.
     */
    public function getFullUri(Request $request): string
    {
        return $this->getSchemeAndHost($request) . $request->getUri()->getPath();
    }

    /**
     * 쿼리 문자열을 포함한 요청 URI의 전체 경로를 반환합니다.
     *
     * 스키마, 호스트, 경로, 쿼리 문자열을 포함한 전체 URI를 반환합니다.
     *
     * @param Request $request PSR-7 요청 객체
     * @return string 스키마, 호스트, 경로, 쿼리 문자열을 포함한 전체 URI 문자열.
     */
    public function getFullUriWithQuery(Request $request): string
    {
        $full_uri = $this->getFullUri($request);
        $query = $request->getUri()->getQuery();

        return $full_uri . ($query ? "?{$query}" : '');
    }

    /**
     * 기본 경로를 반환합니다.
     *
     * @param Request $request PSR-7 요청 객체
     * @return string 베이스 경로 문자열.
     */
    public static function getBasePath(Request $request): string
    {
        return $request->getAttribute('base_path');
    }

    /**
     * 스키마와 호스트 정보를 반환합니다.
     *
     * 스키마(http 또는 https), 사용자 정보(있는 경우), 호스트(도메인 이름),
     * 그리고 기본 포트(80, 443)가 아닌 경우 포트를 포함하는 문자열을 구성합니다.
     *
     * @param Request $request PSR-7 요청 객체
     * @return string 스키마, 사용자 정보, 호스트, 포트를 포함하는 URI 문자열.
     */
    private function getSchemeAndHost(Request $request): string
    {
        $uri = $request->getUri();
        $scheme = $uri->getScheme(); // 스키마 (http 또는 https)
        $host = $uri->getHost(); // 호스트 (도메인 이름)
        $port = $uri->getPort(); // 포트 번호
        $user = $uri->getUserInfo(); // 사용자 정보 (있는 경우)

        $port_string = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
        $user_string = $user ? "{$user}@" : '';

        return "{$scheme}://{$user_string}{$host}{$port_string}";
    }
}
