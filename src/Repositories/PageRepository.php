<?php namespace WebEd\Base\Pages\Repositories;

use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Repositories\Eloquent\EloquentBaseRepository;

use WebEd\Base\Caching\Services\Contracts\CacheableContract;
use WebEd\Base\Pages\Repositories\Contracts\PageContract;

class PageRepository extends EloquentBaseRepository implements PageContract, CacheableContract
{
    use Cacheable;

    protected $rules = [
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|alpha_dash',
        'description' => 'string|max:1000|nullable',
        'content' => 'string|nullable',
        'thumbnail' => 'string|max:255|nullable',
        'keywords' => 'string|max:255|nullable',
        'created_by' => 'integer|min:0|required',
        'updated_by' => 'integer|min:0|required',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
    ];

    protected $editableFields = [
        'page_template',
        'title',
        'slug',
        'description',
        'content',
        'thumbnail',
        'keywords',
        'created_by',
        'updated_by',
        'status',
        'order',
    ];

    /**
     * @param $data
     * @return array
     */
    public function createPage($data)
    {
        return $this->editWithValidate(0, $data, true);
    }

    /**
     * @param $id
     * @param $data
     * @return array
     */
    public function updatePage($id, $data)
    {
        $this->unsetEditableFields('created_by');
        return $this->editWithValidate($id, $data, false, true);
    }

    /**
     * @param int|array $ids
     * @return array
     */
    public function deletePage($ids)
    {
        $ids = (array)$ids;

        return $this->delete($ids);
    }
}
