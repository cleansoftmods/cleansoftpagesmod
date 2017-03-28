<?php namespace WebEd\Base\Pages\Repositories;

use WebEd\Base\Repositories\Eloquent\EloquentBaseRepositoryCacheDecorator;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;

class PageRepositoryCacheDecorator extends EloquentBaseRepositoryCacheDecorator implements PageRepositoryContract
{
    /**
     * @param $data
     * @param array|null $dataTranslate
     * @return array
     */
    public function createPage($data, $dataTranslate = null)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param $id
     * @param $data
     * @param array|null $dataTranslate
     * @return array
     */
    public function updatePage($id, $data, $dataTranslate = null)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param int|array $id
     * @return array
     */
    public function deletePage($id)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }
}
