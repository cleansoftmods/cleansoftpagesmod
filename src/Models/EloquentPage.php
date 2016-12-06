<?php namespace WebEd\Base\Pages\Models;

use WebEd\Base\Core\Models\EloquentBase as BaseModel;
use WebEd\Base\Pages\Models\Contracts\PageModelContract;

class EloquentPage extends BaseModel implements PageModelContract
{
    protected $table = 'pages';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'page_template', 'slug', 'description', 'content', 'thumbnail', 'keywords', 'status', 'order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    public function getContentAttribute($value)
    {
        if (!is_in_dashboard()) {
            return do_shortcode($value);
        }
        return $value;
    }
}
