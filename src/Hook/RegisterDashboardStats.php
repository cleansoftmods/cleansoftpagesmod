<?php namespace WebEd\Base\Pages\Hook;

use WebEd\Base\Pages\Repositories\Contracts\PageContract;
use WebEd\Base\Pages\Repositories\PageRepository;

class RegisterDashboardStats
{
    /**
     * @var PageRepository
     */
    protected $repository;

    public function __construct(PageContract $repository)
    {
        $this->repository = $repository;
    }

    public function handle()
    {
        $count = $this->repository->count();
        echo view('webed-pages::admin.dashboard-stats.stat-box', [
            'count' => $count
        ]);
    }
}
