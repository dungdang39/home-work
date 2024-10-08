<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class CreateMainpageRequest
{
    use SchemaHelperTrait;

    public string $section = '';
    public string $section_title = '';
    public ?int $display_count = 1;
    public ?int $hide_title = 0;
    public ?int $auto_swipe = 0;
    public ?int $max_item = 0;
    public ?int $row_item = 0;
    public ?int $is_enabled = 0;
    public ?string $display_boards = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateType();
        $this->validateTitle();
    }

    private function validateType()
    {
        if (!Validator::required($this->section)) {
            $this->throwException('섹션을 입력해주세요.');
        }
    }

    private function validateTitle()
    {
        if (!Validator::required($this->section_title)) {
            $this->throwException('섹션 타이틀을 입력해주세요.');
        }
    }
}
