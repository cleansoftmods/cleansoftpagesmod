<?php namespace WebEd\Base\Pages\Criterias\Filters;

use Illuminate\Database\Eloquent\Builder;
use WebEd\Base\Criterias\AbstractCriteria;
use WebEd\Base\Pages\Models\Page;
use WebEd\Base\Repositories\AbstractBaseRepository;
use WebEd\Base\Repositories\Contracts\AbstractRepositoryContract;

class FilterPagesCriteria extends AbstractCriteria
{
    /**
     * @var array
     */
    protected $condition;

    /**
     * @var array
     */
    protected $orderBy;

    public function __construct(array $condition, array $orderBy)
    {
        $this->condition = $condition;
        $this->orderBy = $orderBy;
    }

    /**
      * @param Page|Builder $model
      * @param AbstractBaseRepository $repository
      * @return mixed
      */
    public function apply($model, AbstractRepositoryContract $repository)
    {
        return $model
            ->where($this->condition)
            ->orderBy($this->orderBy);
    }
}
