<?php namespace WebEd\Base\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use WebEd\Base\Pages\Criterias\Filters\FilterPagesCriteria;
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
            \AdminBar::registerLink('Page', route('admin::pages.create.get'), 'add-new');

            $this->registerMenu();
            $this->registerMenuDashboard();
            $this->registerSettings();
        });
    }

    private function registerMenuDashboard()
    {
        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-pages',
            'priority' => 1,
            'parent_id' => null,
            'heading' => trans('webed-pages::base.admin_menu.pages.heading'),
            'title' => trans('webed-pages::base.admin_menu.pages.title'),
            'font_icon' => 'icon-notebook',
            'link' => route('admin::pages.index.get'),
            'css_class' => null,
            'permissions' => ['view-pages'],
        ]);
    }

    private function registerMenu()
    {
        /**
         * Register menu widget
         */
        \MenuManagement::registerWidget(trans('webed-pages::base.admin_menu.pages.title'), 'page', function () {
            $repository = app(PageContract::class)
                ->pushCriteria(new FilterPagesCriteria([
                    'status' => 'activated'
                ], [
                    'order' => 'ASC'
                ]))
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
                ->findWhere([
                    'status' => 'activated',
                    'id' => $id,
                ]);
            if (!$page) {
                return null;
            }
            return [
                'model_title' => $page->title,
                'url' => get_page_link($page),
            ];
        });
    }

    private function registerSettings()
    {
        cms_settings()
            ->addSettingField('default_homepage', [
                'group' => 'basic',
                'type' => 'select',
                'priority' => 0,
                'label' => trans('webed-pages::base.settings.default_homepage.label'),
                'helper' => trans('webed-pages::base.settings.default_homepage.helper')
            ], function () {
                /**
                 * @var PageRepository $pageRepo
                 */
                $pageRepo = app(PageContract::class);

                $pages = $pageRepo
                    ->pushCriteria(new FilterPagesCriteria([
                        'status' => 'activated'
                    ], [
                        'order' => 'ASC'
                    ]))
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
