<?php

namespace App\Base\Model\Admin;

use App\Base\Service\MemberService;
use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Slim\Http\ServerRequest as Request;

class CreatePointRequest
{
    use SchemaHelperTrait;

    public string $mb_id;
    public string $po_content;
    public int $po_point;
    public int $mb_nick;
    public int $po_expire_term = 0;

    private MemberService $member_service;

    public function __construct(
        MemberService $member_service,
        Request $request,
    ) {
        $this->member_service = $member_service;

        $this->initializeFromRequest($request);
        Sanitizer::cleanXssAll($this);
    }

    protected function beforeValidate()
    {
        $this->mb_id = strtolower(trim($this->mb_id));
    }

    protected function validate()
    {
        $member = $this->member_service->getMember($this->mb_id);

        $this->validatePoint((int)$member['mb_point']);
    }

    protected function afterValidate()
    {
    }

    private function validatePoint(int $member_point): void
    {
        if ($this->po_point === 0) {
            $this->throwException('포인트를 입력해 주세요.');
        }
        if ($this->po_point < 0) {
            if ($member_point - $this->po_point < 0) {
                $this->throwException('포인트를 깎는 경우 현재 포인트보다 작으면 안됩니다.');
            }
        }
    }
}
