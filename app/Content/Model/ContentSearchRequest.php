<?php

namespace App\Content\Model;

use Core\Model\PageParameters;

class ContentSearchRequest extends PageParameters
{
    public ?string $search_field;

    public ?string $search_text;
}
