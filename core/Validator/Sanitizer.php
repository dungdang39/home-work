<?php

namespace Core\Validator;

/**
 * 필터링 함수 클래스
 */
class Sanitizer
{
    /**
     * 객체 속성 중 문자열 속성을 XSS 필터링
     * @param object $object 필터링할 객체
     * @param array $exclude_attrs 필터링 제외 속성
     * @return void
     */
    public static function cleanXssAll(object &$object, array $exclude_attrs = []): void
    {
        foreach ($object as $key => $value) {
            if (!in_array($key, $exclude_attrs)) {
                $object->$key = self::cleanXss($value);
            }
        }
    }

    /**
     * XSS 필터링
     * @param mixed $value 필터링할 값
     * @return mixed
     */
    public static function cleanXss($value)
    {
        if (is_string($value)) {
            return strip_tags(clean_xss_attributes($value));
        }
        return $value;
    }

    /**
     * 문자열을 "\n"으로 나누고, 중복을 제거한 후 다시 합쳐서 반환하는 함수.
     * 주로 textarea에서 사용자가 입력한 금지어나 차단 도메인을 정리할 때 사용
     * @param string $text
     * @return string
     */
    public static function removeDuplicateLines(string $text) {
        $lines = explode("\n", $text);
        $trimmed_lines = array_map('trim', $lines); // 각 줄에서 공백 제거
        $unique_lines = array_unique(array_filter($trimmed_lines)); // 중복 제거 및 빈 줄 필터링

        // 오름차순 정렬
        sort($unique_lines);
        
        return implode("\n", $unique_lines);
    }
}