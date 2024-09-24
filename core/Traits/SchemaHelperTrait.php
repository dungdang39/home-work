<?php

namespace Core\Traits;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

/**
 * Request/Response Class 처리 트레이트
 */
trait SchemaHelperTrait
{
    /**
     * 인스턴스의 공개 속성만 배열로 반환.
     * 
     * 외부에서 get_object_vars를 호출하는 방법이 가장 빠르지만, 모든 접근제한자 속성을 가져오므로 맞지 않음.
     * - PHP 7.1 부터 사용 가능
     * - ReflectionClass를 사용한 방법보다 약 3배 빠름.
     * 
     * @todo 적절한 이름인가 고민 필요
     * @see https://stackoverflow.com/questions/13124072
     * @return array
     */
    public function publics(): array
    {
        return \Closure::fromCallable("get_object_vars")->__invoke($this);
    }

    /**
     * Request 객체로부터 속성 설정 및 유효성 검사
     * @param Request $request Request 객체
     * @return void
     * @throws Exception  유효성 검사 실패 시 예외 발생
     */
    protected function initializeFromRequest(Request $request): void
    {
        $query = $request->getQueryParams();
        $body = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $this->mapDataToProperties($this, $query);
        $this->mapDataToProperties($this, $body);
        $this->mapFileToProperties($this, $files);

        $this->beforeValidate();

        $this->validate();

        $this->afterValidate();
    }


    /**
     * 주어진 데이터를 클래스 속성에 매핑
     * 
     * @param object $object 데이터를 매핑할 클래스 객체
     * @param array $data 입력 데이터 배열
     */
    protected function mapDataToProperties(object &$object, array $data = []): void
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
     * 주어진 파일 데이터를 클래스 속성에 매핑
     * 
     * @param object $object 데이터를 매핑할 클래스 객체
     * @param array $files 업로드된 파일 배열
     */
    protected function mapFileToProperties(object &$object, array $files): void
    {
        foreach ($files as $key => $file) {
            if (property_exists($object, $key)) {
                if ($file instanceof UploadedFile) {
                    // UploadedFile 객체에서 파일 관련 정보를 추출하여 클래스 속성에 할당
                    if ($file->getError() === UPLOAD_ERR_OK) {
                        // 파일이 정상적으로 업로드된 경우
                        $object->$key = $file;
                    } else {
                        // 파일 업로드 오류 처리
                        // 예를 들어, 오류를 로그에 기록하거나 기본값을 설정할 수 있습니다.
                        $object->$key = null; // 기본값 설정
                    }
                } else {
                    // 파일이 UploadedFile 객체가 아닌 경우 처리
                    $object->$key = null; // 또는 적절한 기본값 처리
                }
            }
        }
    }

    /**
     * 유효성 검사 전처리
     */
    protected function beforeValidate(): void
    {
    }

    /**
     * 유효성 검사
     */
    protected function validate(): void
    {
    }

    /**
     * 유효성 검사 후처리
     */
    protected function afterValidate(): void
    {
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
