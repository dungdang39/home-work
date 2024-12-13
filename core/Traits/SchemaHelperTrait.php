<?php

namespace Core\Traits;

use Closure;
use Core\Exception\UploadException;
use Core\Validator\Validator;
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
     * @todo 적절한 이름인가 고민 필요.
     * @todo 파일삭제를 체크하는 체크박스 매개변수는 직접 처리해야하는 문제는 해결 필요.
     * @see https://stackoverflow.com/questions/13124072
     * @return array
     */
    public function publics(): array
    {
        $props = Closure::fromCallable("get_object_vars")->__invoke($this);

        // UploadedFile, null 속성 제거
        return array_filter($props, function ($value) {
            return (!$value instanceof UploadedFile) && !is_null($value);
        });
    }

    /**
     * Request 객체로부터 속성 설정 및 유효성 검사
     * - Request 객체의 Query, Body, File 데이터를 클래스 속성에 매핑
     * - beforeValidate, validate, afterValidate 메서드를 오버라이드하여 유효성 검사 수행
     * - 유효성 검사 실패 시 Exception 발생
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
                 // 빈값 일 경우 기본 속성값을 유지
                if (is_null($value) || $value === '') {
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
                if ($file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        throw new UploadException($file->getError());
                    }
                    if (!Validator::allowedExtension($file)) {
                        throw new \RuntimeException(sprintf(Validator::ERROR_FILE_EXT, $file->getClientFilename()));
                    }
                    if (!Validator::allowedMimeType($file)) {
                        throw new \RuntimeException(sprintf(Validator::ERROR_FILE_MIME, $file->getClientFilename()));
                    }

                    $object->$key = $file;
                } else {
                    // 파일이 UploadedFile 객체가 아닌 경우 처리
                    $object->$key = null;
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
     * 필수 입력 사항 검사
     */
    protected function validateRequired(string $field, string $label): void
    {
        if (!Validator::required($this->$field)) {
            $this->throwException("{$label}은(는) 필수 입력 사항입니다.");
        }
    }

    /**
     * 이메일 유효성 검사
     */
    protected function validateEmail(string $field, string $label): void
    {
        if (!Validator::isValidEmail($this->$field)) {
            $this->throwException("{$label}은(는) 올바르지 않은 이메일 형식입니다.");
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
