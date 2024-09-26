<?php

namespace Core\Validator;

use Slim\Psr7\UploadedFile;

/**
 * 유효성 검사 함수 클래스
 */
class Validator
{
    public const ERROR_FILE_SIZE = '"%s" 파일의 용량(%s 바이트)이 허용된 용량(%s 바이트)을 초과하였습니다.';
    public const ERROR_FILE_EXT = '"%s" 파일의 확장자가 허용되지 않았으므로 업로드 할 수 없습니다.';
    public const ERROR_FILE_MIME = '"%s" 파일의 MIME 타입이 허용되지 않았으므로 업로드 할 수 없습니다.';

    // 허용된 이미지 확장자 목록
    public static array $extensions = [
        "image" => ['gif', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'apng', 'png'],
        "document" => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt'],
        "audio" => ['mp3', 'wav', 'ogg', 'flac', 'aac'],
        "video" => ['mp4', 'avi', 'mkv', 'webm', 'mov'],
        "archive" => ['zip', 'rar', '7z', 'tar', 'gz'],
        "other" => ['xml', 'json', 'csv']
    ];

    // 허용된 MIME 타입 목록
    public static array $mime_types = [
        "image" => ['image/gif', 'image/jpeg', 'image/jpg', 'image/jpeg', 'image/apng', 'image/png'],
        "document" => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain', 'application/vnd.oasis.opendocument.text'],
        "audio" => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/aac'],
        "video" => ['video/mp4', 'video/x-msvideo', 'video/x-matroska', 'video/webm', 'video/quicktime'],
        "archive" => ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip'],
        "other" => ['application/xml', 'application/json', 'text/csv']
    ];

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
    public static function isValidPhoneNumber($value): bool
    {
        $cleaned_number = preg_replace('/[^0-9]/', '', $value);

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

    /**
     * 파일이 전달 되었는지 확인
     */
    public static function isUploadedFile(?UploadedFile $file): bool
    {
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }

    /**
     * 이미지 파일 유효성 검사
     * @param UploadedFile $file  업로드 파일
     * @return bool
     */
    public static function isImage(UploadedFile $file)
    {
        if (!self::validMimeType($file, self::$mime_types['image'])) {
            return false;
        }
        if (!self::validExtension($file, self::$extensions['image'])) {
            return false;
        }

        return true;
    }

    /**
     * 파일 Mime Type 허용 검사
     */
    public static function allowedMimeType(UploadedFile $file): bool
    {
        $all_mime_types = array_reduce(self::$mime_types, 'array_merge', []);

        return self::validMimeType($file, $all_mime_types);
    }

    /**
     * 파일 Mime Type 유효성 검사
     * @param UploadedFile $file  업로드 파일
     * @param array $allowed  허용 Mime Type
     * @return bool
     */
    public static function validMimeType(UploadedFile $file, array $allowed): bool
    {
        $mime_type = $file->getClientMediaType();

        if (!in_array($mime_type, $allowed)) {
            return false;
        }

        return true;
    }

    /**
     * 파일 확장자 허용 검사
     */
    public static function allowedExtension(UploadedFile $file): bool
    {
        $all_extensions = array_reduce(self::$extensions, 'array_merge', []);

        return self::validExtension($file, $all_extensions);
    }

    /**
     * 파일 확장자 유효성 검사
     * @param UploadedFile $file  업로드 파일
     * @param array $allowed  허용 확장자
     */
    public static function validExtension(UploadedFile $file, array $allowed): bool
    {
        $extension = getExtension($file);

        if (!in_array(strtolower($extension), $allowed)) {
            return false;
        }

        return true;
    }
}
