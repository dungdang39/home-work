<?php

namespace Bootstrap;

use Core\Image\Strategies\ImageStrategyInterface;
use Core\Image\Strategies\ImageStrategyV2;
use Core\Image\Strategies\ImageStrategyV3;
use Core\Lib\FlashMessage;
use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Csrf\Guard;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Factory\ServerRequestCreatorFactory;

class ContainerConfig
{
    /**
     * 기본 컨테이너 설정
     */
    public static function configure(Container $container)
    {
        // Request
        $container->set(Request::class, function () {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            return $serverRequestCreator->createServerRequestFromGlobals();
        });

        // CSRF
        // Post Data 크기가 post_max_size보다 클 경우, CSRF 검증 값이 정상적으로 전달되지 않아
        // CSRF 검증 실패로 처리되는 문제가 있어 해당 위치에 post_max_size 체크를 추가함.
        $container->set('csrf', function ($container) {
            $responseFactory = $container->get('responseFactory');
            $guard = new Guard($responseFactory);
            $guard->setPersistentTokenMode(true);
            $guard->setFailureHandler(function (Request $request, $handler) {
                $content_length = $request->getServerParams()['CONTENT_LENGTH'] ?? 0;
                $post_max_size = (int)ini_get('post_max_size');
                $post_max_size_byte = $post_max_size * 1024 * 1024; // MB -> Byte
                if ($content_length >= $post_max_size_byte) {
                    throw new HttpBadRequestException($request, "Post Data는 최대 {$post_max_size}MB까지 전송 가능합니다.");
                }
                throw new HttpForbiddenException($request, 'CSRF 검증 실패. 새로고침 후 다시 시도하세요.');
            });
            return $guard;
        });

        // Flash 메시지 설정
        // 기본적으로 세션을 사용하므로 설정 이전에 세션을 시작해야 함
        $container->set('flash', fn() => new FlashMessage());

        // Image처리 전략 설정
        $container->set(ImageStrategyInterface::class, function () {
            $version = getPackageVersion('intervention/image');
            return version_compare($version, '3.0.0', '<') ? new ImageStrategyV2() : new ImageStrategyV3();
        });

        // 추가 설정 로드
        // self::loadExtend($container);
    }

    /**
     * @todo 성능적인 부분을 고려해야함.
     */
    public static function loadExtend(Container $container)
    {
        // 고정된 경로에서 클래스 파일 로드
        $extend_path = dirname(__DIR__) . '/extend/Container/';
        $files = glob($extend_path . '*.php');

        foreach ($files as $file) {
            // 클래스명을 경로로부터 추출
            $class_name = basename($file, '.php');
            $class_namespace = "Extend\\Container\\{$class_name}";

            // 클래스가 존재하면 인스턴스를 만들고, __invoke로 호출
            if (class_exists($class_namespace)) {
                $instance = new $class_namespace();
                if ($instance::isEnable()) {
                    $instance::load($container);
                }
            }
        }
    }
}
