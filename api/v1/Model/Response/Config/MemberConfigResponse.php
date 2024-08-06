<?php

namespace API\v1\Model\Response\Config;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 회원가입 설정 조회 응답 모델",
 * )
 */
class MemberConfigResponse
{
    use SchemaHelperTrait;

    /**
     * 메일인증 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_email_certify = 0;

    /**
     * 전화번호 입력 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_tel = 0;

    /**
     * 전화번호 입력 필수 여부
     * @OA\Property(example=0)
     */
    public int $cf_req_tel = 0;

    /**
     * 휴대폰 번호 입력 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_hp = 0;

    /**
     * 휴대폰 번호 입력 필수 여부
     * @OA\Property(example=0)
     */
    public int $cf_req_hp = 0;

    /**
     * 주소 입력 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_addr = 0;

    /**
     * 주소 입력 필수 여부
     * @OA\Property(example=0)
     */
    public int $cf_req_addr = 0;

    /**
     * 서명 입력 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_signature = 0;

    /**
     * 서명 입력 필수 여부
     * @OA\Property(example=0)
     */
    public int $cf_req_signature = 0;

    /**
     * 회원아이콘 업로드 권한 제한
     * @OA\Property(example=2)
     */
    public int $cf_icon_level = 0;

    /**
     * 회원 이미지 너비
     * @OA\Property(example=60)
     */
    public int $cf_member_img_width = 0;

    /**
     * 회원 이미지 높이
     * @OA\Property(example=60)
     */
    public int $cf_member_img_height = 0;

    /**
     * 회원 이미지 크기
     * @OA\Property(example=50000)
     */
    public int $cf_member_img_size = 0;

    /**
     * 회원 아이콘 너비
     * @OA\Property(example=22)
     */
    public int $cf_member_icon_width = 0;

    /**
     * 회원 아이콘 높이
     * @OA\Property(example=22)
     */
    public int $cf_member_icon_height = 0;

    /**
     * 회원 아이콘 크기
     * @OA\Property(example=5000)
     */
    public int $cf_member_icon_size = 0;

    /**
     * 회원 정보공개 제한 일
     * @OA\Property(example=0)
     */
    public int $cf_open_modify = 0;

    /**
     * 추천인 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_use_recommend = 0;

    public function __construct(array $config = [])
    {
        $this->mapDataToProperties($this, $config);
    }
}
