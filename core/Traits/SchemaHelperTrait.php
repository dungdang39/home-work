<?php

namespace Core\Traits;

use Exception;

/**
 * API Request/Response Class 처리 트레이트
 */
trait SchemaHelperTrait
{
    /**
     * 객체를 배열로 변환
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * 주어진 데이터를 클래스 속성에 매핑
     * 
     * @param object $object 데이터를 매핑할 클래스 객체
     * @param array $data 입력 데이터 배열
     */
    protected function mapDataToProperties(object &$object, array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($object, $key)) {
                if (!$value) { // [], null, 0 등 빈값 일 때 기본 속성값을 유지
                    continue;
                }

                // isset()을 사용하여 초기화 여부 확인
                if (isset($object->$key)) {
                    $property_type = gettype($object->$key);
                    switch ($property_type) {
                        case 'boolean':
                            $object->$key = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            break;
                        case 'integer':
                            $object->$key = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                            break;
                        case 'double':
                            $object->$key = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                            break;
                        case 'array':
                            $object->$key = is_array($value) ? $value : array($value);
                            break;
                        case 'string':
                            $object->$key = (string) $value;
                            break;
                        default:
                            $object->$key = $value;
                    }
                } else {
                    $object->$key = $value;
                }
            }
        }
    }

    /**
     * 예외를 던지는 유틸리티 메서드
     * 
     * @param string $message 예외 메시지
     * @throws Exception 예외 발생
     */
    protected function throwException(string $message): void
    {
        throw new Exception($message, 422);
    }
}
