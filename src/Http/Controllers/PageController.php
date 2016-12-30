<?php namespace WebEd\Base\Pages\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Base\Pages\Http\DataTables\PagesListDataTable;
use WebEd\Base\Pages\Http\Requests\CreatePageRequest;
use WebEd\Base\Pages\Http\Requests\UpdatePageRequest;
use WebEd\Base\Pages\Repositories\Contracts\PageContract;
use Yajra\Datatables\Engines\BaseEngine;

class PageController extends BaseAdminController
{
    protected $module = 'webed-pages';

    /**
     * @param \WebEd\Base\Pages\Repositories\PageRepository $pageRepository
     */
    public function __construct(PageContract $pageRepository)
    {
        parent::__construct();

        $this->repository = $pageRepository;

        $this->breadcrumbs->addLink('Pages', route('admin::pages.index.get'));

        $this->getDashboardMenu($this->module);
    }

    /**
     * Show index page
     * @method GET
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PagesListDataTable $pagesListDataTable)
    {
        $this->setPageTitle('CMS pages', 'All available cms pages');

        $this->dis['dataTable'] = $pagesListDataTable->run();

        return do_filter('pages.index.get', $this, $pagesListDataTable)->viewAdmin('index');
    }

    /**
     * @param PagesListDataTable|BaseEngine $pagesListDataTable
     * @return mixed
     */
    public function postListing(PagesListDataTable $pagesListDataTable)
    {
        $data = $pagesListDataTable->with($this->groupAction());

        return do_filter('datatables.pages.index.post', $data, $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    private function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, 'edit-pages')) {
                return [
                    'customActionMessage' => 'You do not have permission',
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, 'delete-pages')) {
                        return [
                            'customActionMessage' => 'You do not have permission',
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete pages
                     */
                    $result = $this->repository->delete($ids);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ], true);
                    break;
                default:
                    $result = [
                        'messages' => 'Method not allowed',
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
        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle('Create page');
        $this->breadcrumbs->addLink('Create page');

        $this->dis['object'] = $this->repository->getModel();
        $this->dis['currentId'] = 0;

        $oldInputs = old();
        if ($oldInputs) {
            foreach ($oldInputs as $key => $row) {
                $this->dis['object']->$key = $row;
            }
        }

        return do_filter('pages.create.get', $this)->viewAdmin('create');
    }

    public function postCreate(CreatePageRequest $request)
    {
        $data = $this->parseDataUpdate($request);

        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createPage($data);

        do_action('pages.after-create.post', $result, $this);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
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
        $id = do_filter('pages.before-edit.get', $id);

        $item = $this->repository->find($id);

        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This page not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle('Edit page', $item->title);
        $this->breadcrumbs->addLink('Edit page');

        $this->dis['object'] = $item;
        $this->dis['currentId'] = $id;

        return do_filter('pages.edit.get', $this, $id)->viewAdmin('edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdatePageRequest $request, $id)
    {
        $id = do_filter('pages.before-edit.post', $id);

        $item = $this->repository->find($id);

        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This page not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $data = $this->parseDataUpdate($request);

        $result = $this->repository->updatePage($id, $data);;

        do_action('pages.after-edit.post', $id, $result, $this);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
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
        $id = do_filter('pages.before-delete.delete', $id);

        $result = $this->repository->deletePage($id);

        do_action('pages.after-delete.delete', $id, $result, $this);

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
