<?php

namespace App\Admin\Controller;

use App\Social\Model\CreateSocialProviderRequest;
use App\Social\Model\UpdateSocialProviderRequest;
use App\Social\SocialService;
use Core\BaseController;
use Core\Exception\HttpConflictException;
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
        $socials = $this->service->getSocials();

        $exclude = array_column($socials, 'provider');
        $available_socials = $this->service->getAvailableSocials($exclude);

        $response_data = [
            "socials" => $socials,
            "available_socials" => $available_socials,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/social_form.html', $response_data);
    }

    /**
     * 소셜 로그인 추가
     */
    public function insert(Request $request, Response $response, CreateSocialProviderRequest $data): Response
    {
        if ($this->service->exists($data->provider)) {
            throw new HttpConflictException($request, '이미 등록된 소셜 로그인입니다.');
        }

        $this->service->createSocial($data->publics());

        return $this->redirectRoute($request, $response, 'admin.setting.api.social');
    }

    /**
     * 소셜 로그인 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateSocialProviderRequest $data): Response
    {
        $this->service->updateSocials($data->publics());

        return $this->redirectRoute($request, $response, 'admin.setting.api.social');
    }

    /**
     * 소셜 로그인 삭제
     */
    public function delete(Response $response, string $provider): Response
    {
        $social = $this->service->getSocial($provider);

        $this->service->deleteSocial($social['provider']);

        return $response->withJson(['message' => "소셜 로그인 정보가 삭제되었습니다."]);
    }
}
