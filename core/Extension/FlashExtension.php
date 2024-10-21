<?php
/**
 * Flash message extension for Twig
 * @see https://github.com/slimphp/Slim-Flash
 */

namespace Core\Extension;

use Core\Lib\FlashMessage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FlashExtension extends AbstractExtension
{
    protected FlashMessage $flash;

    public function __construct(FlashMessage $flash)
    {
        $this->flash = $flash;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('old', [$this, 'old']),
            new TwigFunction('messages', [$this, 'messages']),
            new TwigFunction('first_message', [$this, 'first_message']),
            new TwigFunction('confirm_message', [$this, 'confirm_message']),
        ];
    }

    /**
     * 이전 요청에서 이전 입력 값을 가져옵니다.
     * - laravel의 old() 함수 참고
     * 
     * @param string $key  요청 데이터의 키
     * @param mixed $default  기본값
     * @return mixed  이전 입력 값
     */
    public function old(string $key = null, $default = null)
    {
        $old = $this->flash->getFirstMessage($this->flash::KEY_OLD);

        // $key가 null인 경우, 전체 $old 배열을 반환
        if ($key === null) {
            return $old;
        }

        if (is_array($default)) {
            $default = array_key_exists($key, $default) ? $default[$key] : null;
        }
        if (is_object($default)) {
            $default = property_exists($default, $key) ? $default->$key : null;
        }

        return $old[$key] ?? $default;
    }


    /**
     * 이전 요청에서 메시지 목록 반환
     * @return array 메시지 목록
     */
    public function messages()
    {
        $messages = $this->flash->getFirstMessage($this->flash::KEY_MESSAGE, []);

        if (!is_array($messages)) {
            return [$messages];
        }

        return $messages;
    }

    /**
     * 이전 요청에서 첫 번째 메시지 반환
     * @return string  메시지
     */
    public function first_message(): string
    {
        $messages = $this->flash->getFirstMessage($this->flash::KEY_MESSAGE, []);

        if (is_array($messages) && count($messages) > 0) {
            return $messages[0];
        }

        if (is_string($messages)) {
            return $messages;
        }

        return '';
    }

    /**
     * 이전 요청에서 확인 메시지 및 url 배열 반환
     * @return array 메시지 및 url 배열
     */
    public function confirm_message(): array
    {
        $message = '';
        $url = $this->flash->getFirstMessage($this->flash::KEY_CONFIRM_URL, '');

        $messages = $this->flash->getFirstMessage($this->flash::KEY_CONFIRM, []);
        if (is_array($messages) && count($messages) > 0) {
            $message = $messages[0];
        } else if (is_string($messages)) {
            $message = $messages;
        }

        return ['message' => $message, 'url' => $url];
    }
}
