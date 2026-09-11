<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        CRUD::column('transaction_id');
        CRUD::column('merchant')->type('select')->entity('merchant')->attribute('name');
        CRUD::column('amount');
        CRUD::column('status');
        CRUD::column('created_at');

        // Backpack's built-in Filters bar is a PRO-only feature, so we filter
        // using plain query string params instead — e.g.
        // /admin/payin?status=PENDING&merchant_id=1&from=2026-01-01&to=2026-01-31
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