<?php namespace WebEd\Base\Pages\Http\Requests;

use WebEd\Base\Core\Http\Requests\Request;

class UpdatePageRequest extends Request
{
    public $rules = [
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|nullable',
        'description' => 'string|max:1000|nullable',
        'content' => 'string|nullable',
        'thumbnail' => 'string|max:255|nullable',
        'keywords' => 'string|max:255|nullable',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
    ];
}
