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

        if (CRUD::getCurrentOperation() === 'list') {
            Widget::add()->to('before_content')->type('view')->view('admin.filters.wallet_filters');
        }

        CRUD::column('merchant')->type('select')->entity('merchant')->attribute('name');
        CRUD::column('balance');
        CRUD::column('updated_at')->label('Last updated');


        if (request()->filled('merchant_id')) {
            CRUD::addClause('where', 'merchant_id', request()->get('merchant_id'));
        }
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
}