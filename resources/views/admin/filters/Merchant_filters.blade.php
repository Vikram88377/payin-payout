<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="ACTIVE" @selected(request('status') == 'ACTIVE')>Active</option>
                    <option value="INACTIVE" @selected(request('status') == 'INACTIVE')>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>