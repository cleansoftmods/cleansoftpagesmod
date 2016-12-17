<?php namespace WebEd\Base\Pages\Http\Requests;

use WebEd\Base\Core\Http\Requests\Request;

class UpdatePageRequest extends Request
{
    public $rules = [
        'title' => 'required|string|max:255',
        'status' => 'string|required|in:activated,disabled',
        'description' => 'string|max:1000|nullable',
        'content' => 'string|nullable',
        'thumbnail' => 'string|max:255|nullable',
        'keywords' => 'string|max:255|nullable',
        'page_template' => 'string|max:255|nullable',
    ];
}
