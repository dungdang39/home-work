<?php

namespace App\Admin\Controller;

use App\Admin\Model\CreateSocialProviderRequest;
use App\Admin\Model\UpdateSocialProviderRequest;
use App\Admin\Service\SocialService;
use Core\BaseController;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class SocialController extends BaseController
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
        $providers = $this->service->getProviders();

        $response_data = [
            "providers" => $providers
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/social_form.html', $response_data);
    }

    /**
     * 소셜 로그인 추가
     * @todo 제공하지 않는 소셜로그인은 등록되지 않도록 검증 추가
     * @todo 애플 로그인을 추가할 경우 `social_provider_config` 테이블에 필드를 추가해야함.
     * @todo 애플 로그인 외의 다른 소셜로그인에 추가 필드가 필요한지 확인
     */
    public function insert(Request $request, Response $response, CreateSocialProviderRequest $data): Response
    {
        if ($this->service->isExistProvider($data->provider_key)) {
            throw new Exception('이미 등록된 소셜 로그인입니다.');
        }

        $this->service->createProvider($data->publics());

        return $this->redirectRoute($request, $response, 'admin.setting.api.social');
    }

    /**
     * 소셜 로그인 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateSocialProviderRequest $data): Response
    {
        foreach ($data->providers as $key => $value) {
            $this->service->update($key, $value);
        }

        return $this->redirectRoute($request, $response, 'admin.setting.api.social');
    }

    /**
     * 소셜 로그인 삭제
     */
    public function delete(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();

        $this->service->deleteProvider($request_body['provider_key']);

        return $this->redirectRoute($request, $response, 'admin.setting.api.social');
    }
}
