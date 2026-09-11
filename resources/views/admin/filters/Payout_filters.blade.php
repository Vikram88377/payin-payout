<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="PENDING" @selected(request('status') == 'PENDING')>Pending</option>
                    <option value="SUCCESS" @selected(request('status') == 'SUCCESS')>Success</option>
                    <option value="FAILED" @selected(request('status') == 'FAILED')>Failed</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Merchant</label>
                <select name="merchant_id" class="form-select">
                    <option value="">All</option>
                    @foreach(\App\Models\Merchant::orderBy('name')->get() as $merchant)
                        <option value="{{ $merchant->id }}" @selected(request('merchant_id') == $merchant->id)>
                            {{ $merchant->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>