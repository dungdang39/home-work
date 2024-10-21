<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class MemberMemoRequest
{
    use SchemaHelperTrait;

    public ?string $mb_memo = '';
    public ?string $mb_memo_created_at;

    private array $config;

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);
        if (empty($this->mb_memo)) {
            $this->mb_memo_created_at = null;
        }
        $this->mb_memo_created_at = date('Y-m-d H:i:s');
    }
}
