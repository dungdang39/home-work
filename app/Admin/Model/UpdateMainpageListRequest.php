<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdateMainpageListRequest
{
    use SchemaHelperTrait;

    public ?int $cf_use_mainpage = 0;
    public ?array $ids = [];
    public ?array $section_title = [];

    public array $sections = [];

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);
        $this->convert($this->ids);
    }

    /**
     * 각 섹션별 타이틀을 배열로 변환
     * 
     * @param array|null $ids
     * @return void
     */
    private function convert(?array $ids) : void
    {
        foreach ($ids as $id) {
            $this->sections[$id] = ['section_title' => $this->section_title[$id] ?? ''];
        }
    }
}
