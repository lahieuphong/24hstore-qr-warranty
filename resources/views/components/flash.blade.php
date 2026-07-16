@if (session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
        {{ session('warning') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
        {{ session('error') }}
    </div>
@endif
