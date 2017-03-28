<?php namespace WebEd\Base\Pages\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Pages\Http\DataTables\PagesListDataTable;
use WebEd\Base\Pages\Http\Requests\CreatePageRequest;
use WebEd\Base\Pages\Http\Requests\UpdatePageRequest;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;
use Yajra\Datatables\Engines\BaseEngine;

class PageController extends BaseAdminController
{
    protected $module = 'webed-pages';

    /**
     * @param \WebEd\Base\Pages\Repositories\PageRepository $pageRepository
     */
    public function __construct(PageRepositoryContract $pageRepository)
    {
        parent::__construct();

        $this->repository = $pageRepository;

        $this->middleware(function (Request $request, $next) {
            $this->breadcrumbs->addLink(trans('webed-pages::base.page_title'), route('admin::pages.index.get'));

            return $next($request);
        });

        $this->getDashboardMenu($this->module);
    }

    /**
     * Show index page
     * @method GET
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PagesListDataTable $pagesListDataTable)
    {
        $this->setPageTitle(trans('webed-pages::base.page_title'));

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
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['edit-pages'])) {
                return [
                    'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

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
                    $result = $this->deleteDelete($ids);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ], true);
                    break;
                default:
                    $result = [
                        'messages' => trans('webed-base::errors.' . \Constants::METHOD_NOT_ALLOWED . '.message'),
                        'error' => true
                    ];
                    break;
            }
            $data['customActionMessage'] = $result['messages'];
            $data['customActionStatus'] = $result['error'] ? 'danger' : 'success';

        }
        return $data;
    }

    /**
     * Update page status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus($id, $status)
    {
        $data = [
            'status' => $status
        ];
        $result = $this->repository->updatePage($id, $data);
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

        $this->setPageTitle(trans('webed-pages::base.form.create_page'));
        $this->breadcrumbs->addLink(trans('webed-pages::base.form.create_page'));

        $this->dis['object'] = $this->repository->getModel();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'create.get')->viewAdmin('create');
    }

    public function postCreate(CreatePageRequest $request)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_PAGES, 'create.post');

        $data = $this->parseDataUpdate($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createPage($data);

        do_action(BASE_ACTION_AFTER_CREATE, WEBED_PAGES, $result);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error']) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::pages.edit.get', ['id' => $result['data']->id]));
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

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-pages::base.form.page_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_PAGES, 'edit.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('webed-pages::base.form.edit_page') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans('webed-pages::base.form.edit_page'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'edit.get', $id)->viewAdmin('edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdatePageRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-pages::base.form.page_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_PAGES, 'edit.post');

        $data = $this->parseDataUpdate($request);

        $result = $this->repository->updatePage($item, $data);

        do_action(BASE_ACTION_AFTER_UPDATE, WEBED_PAGES, $id, $result);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::pages.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, WEBED_PAGES);

        $result = $this->repository->deletePage($id);

        do_action(BASE_ACTION_AFTER_DELETE, WEBED_PAGES, $id, $result);

        return response()->json($result, $result['response_code']);
    }

    protected function parseDataUpdate(Request $request)
    {
        return [
            'page_template' => $request->get('page_template', null),
            'status' => $request->get('status'),
            'title' => $request->get('title'),
            'slug' => ($request->get('slug') ? str_slug($request->get('slug')) : str_slug($request->get('title'))),
            'keywords' => $request->get('keywords'),
            'description' => $request->get('description'),
            'content' => $request->get('content'),
            'thumbnail' => $request->get('thumbnail'),
            'updated_by' => $this->loggedInUser->id,
            'order' => $request->get('order'),
        ];
    }
}
