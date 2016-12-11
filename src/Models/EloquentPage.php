<?php namespace WebEd\Base\Pages\Models;

use WebEd\Base\Core\Models\EloquentBase as BaseModel;
use WebEd\Base\Pages\Models\Contracts\PageModelContract;
use WebEd\Base\Users\Models\EloquentUser;

class EloquentPage extends BaseModel implements PageModelContract
{
    protected $table = 'pages';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'page_template', 'slug', 'description', 'content', 'thumbnail', 'keywords', 'status', 'order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(EloquentUser::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modifier()
    {
        return $this->belongsTo(EloquentUser::class, 'updated_by');
    }

    /**
     * @param $value
     * @return string
     */
    public function getContentAttribute($value)
    {
        if (!is_in_dashboard()) {
            return do_shortcode($value);
        }
        return $value;
    }
}
