<?php namespace WebEd\Base\Pages\Http\Requests;

use WebEd\Base\Core\Http\Requests\Request;

class UpdatePageRequest extends Request
{
    public $rules = [
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|alpha_dash',
        'description' => 'string|max:1000',
        'content' => 'string',
        'thumbnail' => 'string|max:255',
        'keywords' => 'string|max:255',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
    ];
}
