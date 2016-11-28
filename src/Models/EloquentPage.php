<?php namespace WebEd\Base\Pages\Models;

use WebEd\Base\Core\Models\EloquentBase as BaseModel;
use WebEd\Base\Pages\Models\Contracts\PageModelContract;

class EloquentPage extends BaseModel implements PageModelContract
{
    protected $table = 'pages';

    protected $primaryKey = 'id';

    protected $fillable = [
        'created_by'
    ];
}
