<?php

namespace App\Admin\Controller;

use App\Admin\Model\CreateSocialProviderRequest;
use App\Admin\Model\UpdateSocialProviderRequest;
use App\Admin\Service\SocialService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class SocialController
{
    private SocialService $service;

    public function __construct(
        SocialService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 소셜 로그인 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $providers = $this->service->getProviders();

        $response_data = [
            "providers" => $providers
        ];
        return $view->render($response, '/admin/social_form.html', $response_data);
    }

    /**
     * 소셜 로그인 추가
     * @todo 제공하지 않는 소셜로그인은 등록되지 않도록 검증 추가
     * @todo 애플 로그인을 추가할 경우 `social_provider_config` 테이블에 필드를 추가해야함.
     * @todo 애플 로그인 외의 다른 소셜로그인에 추가 필드가 필요한지 확인
     */
    public function insert(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody();
            $data = CreateSocialProviderRequest::load($body)->toArray();

            if ($this->service->isExistProvider($data['provider_key'])) {
                throw new Exception('이미 등록된 소셜 로그인입니다.');
            }

            $this->service->createProvider($data);
        } catch (Exception $e) {
            throw $e;
        }

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.setting.api.social');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 소셜 로그인 설정 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = UpdateSocialProviderRequest::load($request_body)->toArray();

            foreach ($data['providers'] as $key => $data) {
                $this->service->update($key, $data);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.setting.api.social');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 소셜 로그인 삭제
     */
    public function delete(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $provider_key = $request_body['provider_key'];
    
            $this->service->deleteProvider($provider_key);
        } catch (\Exception $e) {
            throw $e;
        }

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.setting.api.social');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }
}
