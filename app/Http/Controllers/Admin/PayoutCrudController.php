<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PayoutCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Payout::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/payout');
        CRUD::setEntityNameStrings('payout', 'payouts');
    }

    protected function setupListOperation()
    {
        CRUD::column('transaction_id');
        CRUD::column('merchant')->type('select')->entity('merchant')->attribute('name');
        CRUD::column('amount');
        CRUD::column('status');
        CRUD::column('created_at');

        // Filters bar is PRO-only, so filtering via plain query params instead —
        // /admin/payout?status=PENDING&merchant_id=1&from=2026-01-01&to=2026-01-31
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