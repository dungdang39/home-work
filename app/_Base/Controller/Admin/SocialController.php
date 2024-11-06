<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\CreateSocialProviderRequest;
use App\Base\Model\Admin\UpdateSocialProviderRequest;
use App\Base\Service\SocialService;
use App\Base\Social\Provider\Config\AppleConfig;
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
        return $view->render($response, '@admin/config/api/social_form.html', $response_data);
    }

    /**
     * 소셜 로그인 추가
     */
    public function insert(Request $request, Response $response, CreateSocialProviderRequest $data): Response
    {
        if ($this->service->exists($data->provider)) {
            throw new HttpConflictException($request, '이미 등록된 소셜 로그인입니다.');
        }

        if (AppleConfig::PROVIDER === $data->provider) {
            $data->keys['key_file'] = $this->service->uploadKeyFile($data->key_file);
        }

        $this->service->createSocial($data->publics());

        return $this->redirectRoute($request, $response, 'admin.config.api.social');
    }

    /**
     * 소셜 로그인 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateSocialProviderRequest $data): Response
    {
        if ($data->key_file) {
            foreach ($data->socials as $key => $social) {
                if (AppleConfig::PROVIDER === $key) {
                    $data->socials[$key]['keys']['key_file'] = $this->service->uploadKeyFile($data->key_file);
                    continue;
                }
            }
        }

        $this->service->updateSocials($data->publics());

        return $this->redirectRoute($request, $response, 'admin.config.api.social');
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
