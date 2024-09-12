<?php

namespace Core\Validator;

/**
 * 유효성 검사 클래스
 */
class Validator extends ValidatorFunction
{
    private $rules = [];
    private $errors = [];

    /**
     * 필드에 대한 유효성 검사 규칙 추가
     *
     * @param string $field 필드명
     * @param array $rules 유효성 검사 규칙 배열
     */
    public function addRule(string $field, array $rules): void
    {
        // 필드에 대한 기존 규칙이 없을 경우 초기화
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }

        // 각 규칙을 필드에 추가
        foreach ($rules as $rule) {
            foreach ($rule as $function => $options) {
                $this->rules[$field][] = [
                    'function' => $function,
                    'message' => $options['message'] ?? 'Invalid value ' . $field,
                    'args' => $options['args'] ?? []
                ];
            }
        }
    }

    /**
     * 필드에 대한 유효성 검사 규칙 제거
     */
    public function removeRule(string $field): void
    {
        unset($this->rules[$field]);
    }

    /**
     * 필드 규칙들에 따라 유효성 검사 수행
     *
     * @param array $data 유효성 검사할 데이터 배열
     * @return array 유효성 검사 결과
     */
    public function validate(array $data): array
    {
        foreach ($this->rules as $field => $rules) {
            $value = $data[$field] ?? null;

            foreach ($rules as $rule) {
                $function = $rule['function'];
                $message = $rule['message'];
                $args = $rule['args'];

                // ValidatorFunction 클래스에 정의된 메서드를 호출하여 유효성 검사
                $function_name = 'validate' . str_replace('_', '', ucwords($function, '_'));
                $isValid = call_user_func_array([$this, $function_name], array_merge([$value], $args));
                if (!$isValid) {
                    $this->errors[$field][] = $message;
                }
            }
        }

        return $this->errors;
    }

    /**
     * 유효성 검사 실패 여부 반환
     */
    public function failed(): bool
    {
        return !empty($this->errors);
    }

    /**
     * 유효성 검사 실패 메시지 반환
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $first_field = array_key_first($this->errors);
        return $this->errors[$first_field][0];
    }
}