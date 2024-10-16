<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdateMemberListRequest
{
    use SchemaHelperTrait;

    public ?array $chk = [];
    public ?array $mb_level = [];
    public ?array $mb_status = [];

    public array $members = [];

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);

        // chk에 있는 mb_id값으로 배열 변환
        $this->members = $this->convertChkToMembers($this->chk);
    }

    /**
     * chk에 있는 mb_id값으로 배열 변환
     * 
     * @param array|null $chk
     * @return array
     */
    private function convertChkToMembers(?array $chk): array
    {
        if (empty($chk)) {
            return [];
        }

        $members = [];
        foreach ($chk as $mb_id) {
            $members[$mb_id] = ['mb_level' => $this->mb_level[$mb_id] ?? 1];

            if ($this->mb_status[$mb_id] === 'leave') {
                $members[$mb_id]['mb_leave_date'] = date('Y-m-d H:i:s');
                $members[$mb_id]['mb_intercept_date'] = null;
            } else if ($this->mb_status[$mb_id] === 'intercept') {
                $members[$mb_id]['mb_leave_date'] = null;
                $members[$mb_id]['mb_intercept_date'] = date('Y-m-d H:i:s');
            } else {
                $members[$mb_id]['mb_leave_date'] = null;
                $members[$mb_id]['mb_intercept_date'] = null;
            }
        }

        return $members;
    }
}
