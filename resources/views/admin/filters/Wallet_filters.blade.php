<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
            <div class="col-md-4">
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

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>