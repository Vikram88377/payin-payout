<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MerchantRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MerchantCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Merchant::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/merchant');
        CRUD::setEntityNameStrings('merchant', 'merchants');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('email');
        CRUD::column('status');
        CRUD::column('created_at');

        // Filters bar is PRO-only, so filtering via plain query param —
        // /admin/merchant?status=ACTIVE
        if (request()->filled('status')) {
            CRUD::addClause('where', 'status', request()->get('status'));
        }
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(MerchantRequest::class);

        CRUD::field('name');
        CRUD::field('email');
        CRUD::field('api_key')->hint('Give the merchant a unique key to authenticate API calls.');
        CRUD::field('status')->type('select_from_array')->options([
            'ACTIVE' => 'Active',
            'INACTIVE' => 'Inactive',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
}