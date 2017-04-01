<?php namespace WebEd\Base\Pages\Repositories;

use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Pages\Models\Page;
use WebEd\Base\Repositories\Eloquent\EloquentBaseRepository;

use WebEd\Base\Caching\Services\Contracts\CacheableContract;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;

class PageRepository extends EloquentBaseRepository implements PageRepositoryContract, CacheableContract
{
    use Cacheable;

    /**
     * @param array $data
     * @return int
     */
    public function createPage(array $data)
    {
        return $this->create($data, true);
    }

    /**
     * @param Page|int $id
     * @param array $data
     * @return int
     */
    public function updatePage($id, array $data)
    {
        return $this->update($id, $data);
    }

    /**
     * @param int|array $ids
     * @param bool $force
     * @return bool
     */
    public function deletePage($ids, $force = false)
    {
        return $this->delete((array)$ids, $force);
    }
}
