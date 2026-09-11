<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

class WalletCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Wallet::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/wallet');
        CRUD::setEntityNameStrings('wallet', 'wallets');
    }

    protected function setupListOperation()
    {
        // Filters bar is PRO-only, so we build our own — but only show it on
        // the actual list page, not when this method is reused for Show.
        if (CRUD::getCurrentOperation() === 'list') {
            Widget::add()->to('before_content')->type('view')->view('admin.filters.wallet_filters');
        }

        CRUD::column('merchant')->type('select')->entity('merchant')->attribute('name');
        CRUD::column('balance');
        CRUD::column('updated_at')->label('Last updated');

        // Filters bar is PRO-only, so filtering via plain query param —
        // /admin/wallet?merchant_id=1
        if (request()->filled('merchant_id')) {
            CRUD::addClause('where', 'merchant_id', request()->get('merchant_id'));
        }
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
}