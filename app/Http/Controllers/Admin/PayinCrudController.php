<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

class PayinCrudController extends CrudController
{
    // Only List + Show — payins are created via API/cron, not manually from admin
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Payin::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/payin');
        CRUD::setEntityNameStrings('payin', 'payins');
    }

    protected function setupListOperation()
    {

        if (CRUD::getCurrentOperation() === 'list') {
            Widget::add()->to('before_content')->type('view')->view('admin.filters.payin_filters');
        }

        CRUD::column('transaction_id');
        CRUD::column('merchant')->type('select')->entity('merchant')->attribute('name');
        CRUD::column('amount');
        CRUD::column('status');
        CRUD::column('created_at');

  
        $this->applyBasicFilters();
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }

    private function applyBasicFilters(): void
    {
        $request = request();

        if ($request->filled('status')) {
            CRUD::addClause('where', 'status', $request->get('status'));
        }

        if ($request->filled('merchant_id')) {
            CRUD::addClause('where', 'merchant_id', $request->get('merchant_id'));
        }

        if ($request->filled('from')) {
            CRUD::addClause('where', 'created_at', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            CRUD::addClause('where', 'created_at', '<=', $request->get('to'));
        }
    }
}