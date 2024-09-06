<?php
/**
 * Flash message extension for Twig
 * @see https://github.com/slimphp/Slim-Flash
 */

namespace Core\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Slim\Flash\Messages;

final class FlashExtension extends AbstractExtension
{
    protected $flash;

    public function __construct(Messages $flash)
    {
        $this->flash = $flash;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('old', [$this, 'old']),
            new TwigFunction('errors', [$this, 'errors']),
            new TwigFunction('error_first_message', [$this, 'error_first_message']),
        ];
    }

    /**
     * 이전 요청에서 이전 입력 값을 가져옵니다.
     * - laravel의 old() 함수 참고
     * 
     * @param string $key  요청 데이터의 키
     * @param mixed $default  기본값
     * @return string  이전 입력 값
     */
    public function old(string $key = null, $default = null): string
    {
        $old = $this->flash->getFirstMessage('old', $default);

        $value = $old[$key] ?? null;

        if (is_null($value) && is_array($default)) {
            return $default[$key] ?? '';
        }

        return $value ?? $default;
    }


    /**
     * 이전 요청에서 오류 메시지 목록 반환
     * @return array  오류 메시지 목록
     */
    public function errors()
    {
        $errors = $this->flash->getFirstMessage('errors', []);

        if (!is_array($errors)) {
            return [$errors];
        }

        return $errors;
    }

    /**
     * 이전 요청에서 첫 번째 오류 메시지 반환
     * @return string  오류 메시지
     */
    public function error_first_message(): string
    {
        $errors = $this->flash->getFirstMessage('errors', []);

        if (is_array($errors) && count($errors) > 0) {
            return $errors[0];
        }

        if (is_string($errors)) {
            return $errors;
        }

        return '';
    }
}
