<?php namespace WebEd\Base\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use WebEd\Base\Pages\Repositories\Contracts\PageContract;
use WebEd\Base\Pages\Repositories\PageRepository;

class BootstrapModuleServiceProvider extends ServiceProvider
{
    protected $module = 'WebEd\Base\Pages';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    private function booted()
    {
        \AdminBar::registerLink('Page', route('admin::pages.create.get'), 'add-new');

        $this->registerMenu();
        $this->registerMenuDashboard();
        $this->registerSettings();
    }

    private function registerMenuDashboard()
    {
        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-pages',
            'piority' => 1,
            'parent_id' => null,
            'heading' => 'CMS',
            'title' => 'Pages',
            'font_icon' => 'icon-notebook',
            'link' => route('admin::pages.index.get'),
            'css_class' => null,
        ]);
    }

    private function registerMenu()
    {
        /**
         * Register menu widget
         */
        \MenuManagement::registerWidget('Pages', 'page', function () {
            $repository = app(PageContract::class)
                ->orderBy('created_at', 'DESC')
                ->get();
            $pages = [];
            foreach ($repository as $page) {
                $pages[] = [
                    'id' => $page->id,
                    'title' => $page->title,
                ];
            }
            return $pages;
        });

        /**
         * Register menu link type
         */
        \MenuManagement::registerLinkType('page', function ($id) {
            $page = app(PageContract::class)
                ->where('id', '=', $id)
                ->first();
            if (!$page) {
                return null;
            }
            return [
                'model_title' => $page->title,
                'url' => route('front.resolve-pages.get', ['slug' => $page->slug]),
            ];
        });
    }

    private function registerSettings()
    {
        cms_settings()
            ->addSettingField('default_homepage', [
                'group' => 'basic',
                'type' => 'select',
                'piority' => 0,
                'label' => 'Default homepage',
                'helper' => null
            ], function () {
                /**
                 * @var PageRepository $pages
                 */
                $pages = app(PageContract::class);
                $pages = $pages->where('status', '=', 'activated')
                    ->orderBy('order', 'ASC')
                    ->get();

                $pagesArr = [];

                foreach ($pages as $page) {
                    $pagesArr[$page->id] = $page->title;
                }

                return [
                    'default_homepage',
                    $pagesArr,
                    get_settings('default_homepage'),
                    ['class' => 'form-control']
                ];
            });
    }
}
