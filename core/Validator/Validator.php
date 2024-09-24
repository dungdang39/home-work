<?php

namespace Core\Validator;

/**
 * 유효성 검사 함수 클래스
 */
class Validator
{
    /**
     * 값이 비어있는지 검증
     * @param mixed $value
     * @return bool
     */
    public static function required($value): bool
    {
        $value = is_string($value) ? trim($value) : $value;
        return !empty($value);
    }

    /**
     * 값이 알파벳 또는 숫자로 이루어져 있는지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isAlnum($value): bool
    {
        return ctype_alnum($value);
    }

    /**
     * 값의 길이가 최소값 이상인지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isMinLength($value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    /**
     * 값의 길이가 최대값 이하인지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isMaxLength($value, int $max_length): bool
    {
        return mb_strlen($value) <= $max_length;
    }

    /**
     * 값의 길이가 최소값 이상, 최대값 이하인지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isBetweenLength($value, int $min_length, int $max_length): bool
    {
        return self::isMinLength($value, $min_length) && self::isMaxLength($value, $max_length);
    }

    /**
     * 값이 금지어를 포함하고 있는지 검증
     * @param mixed $value
     * @param array $prohibit_words
     */
    public static function isProhibitedWord($value, array $prohibit_words): bool
    {
        return in_array($value, $prohibit_words);
    }

    /**
     * 값이 UTF-8 문자열인지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isUtf8String($value): bool
    {
        return mb_detect_encoding($value, 'UTF-8', true) === 'UTF-8';
    }

    /**
     * 값이 영문, 한글, 숫자로 이루어져 있는지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isAlnumko($value): bool
    {
        return preg_match('/^[a-zA-Z0-9가-힣]+$/', $value);
    }

    /**
     * 값이 비어있지 않고 숫자인지 확인
     * @param mixed $value
     * @return bool
     */
    public static function isNotEmptyAndNumeric($value): bool
    {
        return !empty($value) && is_numeric($value);
    }

    /**
     * 값이 이메일 형식인지 검증
     * @param mixed $value
     * @return bool
     */
    public static function isValidEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 휴대폰 번호 형식 검사
     * @param string $number 휴대폰 번호
     * @return bool 휴대폰 번호 형식이면 true, 아니면 false
     */
    public static function isValidPhoneNumber(string $number): bool
    {
        $cleaned_number = preg_replace('/[^0-9]/', '', $number);

        return preg_match('/^01[0-9]{8,9}$/', $cleaned_number) === 1;
    }

    /**
     * 이메일 도메인이 금지 도메인인지 검증
     * @param mixed $value
     * @param array $prohibit_domains
     * @return bool
     */
    public static function isProhibitedDomain($value, array $prohibit_domains): bool
    {
        $email_array = explode('@', $value);
        if (!isset($email_array[1])) {
            return false;
        }
        $email_domain = strtolower($email_array[1]);

        $prohibit_domains = array_map(function ($domain) {
            return strtolower(trim($domain));
        }, $prohibit_domains);

        return !in_array($email_domain, $prohibit_domains);
    }
}
