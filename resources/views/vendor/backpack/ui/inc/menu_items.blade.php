{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Merchants" icon="la la-question" :link="backpack_url('merchant')" />
<x-backpack::menu-item title="Wallets" icon="la la-question" :link="backpack_url('wallet')" />
<x-backpack::menu-item title="Payins" icon="la la-question" :link="backpack_url('payin')" />
<x-backpack::menu-item title="Payouts" icon="la la-question" :link="backpack_url('payout')" />