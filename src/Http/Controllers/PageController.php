<?php namespace WebEd\Base\Pages\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Pages\Actions\CreatePageAction;
use WebEd\Base\Pages\Actions\DeletePageAction;
use WebEd\Base\Pages\Actions\RestorePageAction;
use WebEd\Base\Pages\Actions\UpdatePageAction;
use WebEd\Base\Pages\Http\DataTables\PagesListDataTable;
use WebEd\Base\Pages\Http\Requests\CreatePageRequest;
use WebEd\Base\Pages\Http\Requests\UpdatePageRequest;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;
use Yajra\Datatables\Engines\BaseEngine;

class PageController extends BaseAdminController
{
    protected $module = WEBED_PAGES;

    /**
     * @param \WebEd\Base\Pages\Repositories\PageRepository $pageRepository
     */
    public function __construct(PageRepositoryContract $pageRepository)
    {
        parent::__construct();

        $this->repository = $pageRepository;

        $this->middleware(function (Request $request, $next) {
            $this->breadcrumbs->addLink(trans($this->module . '::base.page_title'), route('admin::pages.index.get'));

            $this->getDashboardMenu($this->module);

            return $next($request);
        });
    }

    /**
     * Show index page
     * @method GET
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PagesListDataTable $pagesListDataTable)
    {
        $this->setPageTitle(trans($this->module . '::base.page_title'));

        $this->dis['dataTable'] = $pagesListDataTable->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'index.get', $pagesListDataTable)->viewAdmin('index');
    }

    /**
     * @param PagesListDataTable|BaseEngine $pagesListDataTable
     * @return mixed
     */
    public function postListing(PagesListDataTable $pagesListDataTable)
    {
        $data = $pagesListDataTable->with($this->groupAction());

        return do_filter(BASE_FILTER_CONTROLLER, $data, WEBED_PAGES, 'index.post', $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    protected function groupAction()
    {
        $data = [];
        if ($this->request->input('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['edit-pages'])) {
                return [
                    'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->input('id', []);
            $actionValue = $this->request->input('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['delete-pages'])) {
                        return [
                            'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete pages
                     */
                    $action = app(DeletePageAction::class);
                    foreach ($ids as $id) {
                        $this->postDelete($action, $id);
                    }
                    break;
                case 1:
                case 0:
                    $action = app(UpdatePageAction::class);

                    foreach ($ids as $id) {
                        $action->run($id, [
                            'status' => $actionValue,
                        ]);
                    }
                    break;
                default:
                    return [
                        'customActionMessage' => trans('webed-core::errors.' . \Constants::METHOD_NOT_ALLOWED . '.message'),
                        'customActionStatus' => 'danger'
                    ];
                    break;
            }
            $data['customActionMessage'] = trans('webed-core::base.form.request_completed');
            $data['customActionStatus'] = 'success';
        }
        return $data;
    }

    /**
     * Update page status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus(UpdatePageAction $action, $id, $status)
    {
        $result = $action->run($id, [
            'status' => $status
        ]);

        return response()->json($result, $result['response_code']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_PAGES, 'create.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans($this->module . '::base.form.create_page'));
        $this->breadcrumbs->addLink(trans($this->module . '::base.form.create_page'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'create.get')->viewAdmin('create');
    }

    public function postCreate(CreatePageRequest $request, CreatePageAction $action)
    {
        $data = $this->parseData($request);

        $result = $action->run($data);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error']) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::pages.edit.get', ['id' => $result['data']['id']]));
        }

        return redirect()->to(route('admin::pages.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $item = $this->repository->find($id);

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_PAGES, 'edit.get');

        if (!$item) {
            flash_messages()
                ->addMessages(trans($this->module . '::base.form.page_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans($this->module . '::base.form.edit_page') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans($this->module . '::base.form.edit_page'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'edit.get', $id)->viewAdmin('edit');
    }

    /**
     * @param UpdatePageRequest $request
     * @param UpdatePageAction $action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdatePageRequest $request, UpdatePageAction $action, $id)
    {
        $data = $this->parseData($request);

        $result = $action->run($id, $data);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error'] || $this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::pages.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDelete(DeletePageAction $action, $id)
    {
        $result = $action->run($id, false);

        return response()->json($result, $result['response_code']);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postForceDelete(DeletePageAction $action, $id)
    {
        $result = $action->run($id, true);

        return response()->json($result, $result['response_code']);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRestore(RestorePageAction $action, $id)
    {
        $result = $action->run($id);

        return response()->json($result, $result['response_code']);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function parseData(Request $request)
    {
        $data = $request->input('page', []);
        if (!$data['slug']) {
            $data['slug'] = str_slug($data['title']);
        }
        return $data;
    }
}
