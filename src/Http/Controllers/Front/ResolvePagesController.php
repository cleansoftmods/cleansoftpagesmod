<?php namespace WebEd\Base\Pages\Http\Controllers\Front;

use WebEd\Base\Core\Http\Controllers\BaseFrontController;
use WebEd\Base\Pages\Models\Contracts\PageModelContract;
use WebEd\Base\Pages\Models\EloquentPage;
use WebEd\Base\Pages\Repositories\Contracts\PageContract;
use WebEd\Base\Pages\Repositories\PageRepository;

class ResolvePagesController extends BaseFrontController
{
    /**
     * @var PageContract|PageRepository
     */
    protected $repository;

    /**
     * SlugWithoutSuffixController constructor.
     * @param PageRepository $repository
     */
    public function __construct(PageContract $repository)
    {
        parent::__construct();

        $this->themeController = themes_management()->getThemeController('Page');

        $this->repository = $repository;
    }

    public function handle($slug = null)
    {
        if(!$slug) {
            $page = $this->repository
                ->where('id', '=', do_filter('front.default-homepage.get', get_settings('default_homepage')))
                ->where('status', '=', 'activated')
                ->first();
        } else {
            $page = $this->repository
                ->where('slug', '=', $slug)
                ->where('status', '=', 'activated')
                ->first();
        }

        if(!$page) {
            if ($slug === null) {
                echo '<h2>You need to setup your default homepage. Create a page then go through to Admin Dashboard -> Configuration -> Settings</h2>';
                die();
            } else {
                abort(404);
            }
        }

        $page = do_filter('front.web.resolve-pages.get', $page);

        /**
         * Update view count
         */
        increase_view_count($page, $page->id);

        \AdminBar::registerLink('Edit this page', route('admin::pages.edit.get', ['id' => $page->id]));

        $this->setPageTitle($page->title);

        $this->dis['object'] = $page;

        if($this->themeController) {
            return $this->themeController->handle($page, $this->dis);
        }

        $this->getMenu('page', $page->id);

        $happyMethod = '_template_' . studly_case($page->page_template);

        if(method_exists($this, $happyMethod)) {
            return $this->$happyMethod($page);
        }

        return $this->defaultTemplate($page);
    }

    /**
     * @param EloquentPage $page
     * @return mixed
     */
    protected function defaultTemplate(PageModelContract $page)
    {
        return $this->view('front.page-templates.default');
    }
}
