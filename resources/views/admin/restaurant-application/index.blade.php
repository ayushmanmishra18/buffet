@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Restaurant Applications</li>
            </ol>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <a href="{{ route('admin.restaurant-application.index', ['status' => 0]) }}"
                   class="block p-4 rounded-xl border-2 {{ $filterStatus === '0' ? 'border-yellow-400 bg-yellow-50' : 'border-gray-100 bg-white' }} transition hover:border-yellow-400">
                    <div class="text-2xl font-bold text-yellow-600">{{ $counts['pending'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Pending Review</div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('admin.restaurant-application.index', ['status' => 1]) }}"
                   class="block p-4 rounded-xl border-2 {{ $filterStatus === '1' ? 'border-green-400 bg-green-50' : 'border-gray-100 bg-white' }} transition hover:border-green-400">
                    <div class="text-2xl font-bold text-green-600">{{ $counts['approved'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Approved</div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('admin.restaurant-application.index', ['status' => 2]) }}"
                   class="block p-4 rounded-xl border-2 {{ $filterStatus === '2' ? 'border-red-400 bg-red-50' : 'border-gray-100 bg-white' }} transition hover:border-red-400">
                    <div class="text-2xl font-bold text-red-600">{{ $counts['rejected'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Rejected</div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">
                    @if ($filterStatus === '0') Pending Applications
                    @elseif ($filterStatus === '1') Approved Applications
                    @elseif ($filterStatus === '2') Rejected Applications
                    @else All Applications
                    @endif
                </h3>
                <div class="db-card-filter">
                    <a href="{{ route('admin.restaurant-application.index') }}" class="db-btn h-[38px] bg-gray-100 text-gray-600">
                        <i class="fas fa-list"></i> <span>All</span>
                    </a>
                </div>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th font-bold">Applicant</th>
                            <th class="db-table-head-th font-bold">Business</th>
                            <th class="db-table-head-th font-bold">Location</th>
                            <th class="db-table-head-th font-bold">Plan</th>
                            <th class="db-table-head-th font-bold">Applied</th>
                            <th class="db-table-head-th font-bold">Status</th>
                            <th class="db-table-head-th font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body">
                        @forelse ($applications as $app)
                            <tr class="db-table-body-tr">
                                <td class="db-table-body-td">
                                    <p class="font-semibold text-sm">{{ $app->owner_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $app->owner_email }}</p>
                                    <p class="text-xs text-gray-400">{{ $app->owner_phone }}</p>
                                </td>
                                <td class="db-table-body-td">
                                    <p class="font-semibold text-sm">{{ $app->business_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $app->business_type }}</p>
                                    @if ($app->cuisine_type)
                                        <p class="text-xs text-gray-400">{{ Str::limit($app->cuisine_type, 30) }}</p>
                                    @endif
                                </td>
                                <td class="db-table-body-td text-sm text-gray-600">
                                    {{ $app->city }}@if($app->country), {{ $app->country }}@endif
                                </td>
                                <td class="db-table-body-td">
                                    @if ($app->plan)
                                        <span class="db-table-badge {{ $app->billing_type == 5 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                            {{ $app->plan->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">No plan</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td text-xs text-gray-500">
                                    {{ $app->created_at->format('d M Y') }}
                                </td>
                                <td class="db-table-body-td">
                                    {!! $app->status_badge !!}
                                </td>
                                <td class="db-table-body-td">
                                    <a href="{{ route('admin.restaurant-application.show', $app) }}"
                                       class="db-table-action edit" title="View Details">
                                        <i class="fas fa-eye"></i>
                                        <span class="db-tooltip">View</span>
                                    </a>

                                    @if ($app->status == 0)
                                        <form class="inline-block"
                                              action="{{ route('admin.restaurant-application.approve', $app) }}"
                                              method="POST"
                                              onsubmit="return confirm('Approve this application and activate the restaurant?')">
                                            @csrf
                                            <button type="submit" class="db-table-action" title="Approve"
                                                    style="color:#16a34a;">
                                                <i class="fas fa-check-circle"></i>
                                                <span class="db-tooltip">Approve</span>
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="db-table-action delete" title="Reject"
                                                onclick="openRejectModal({{ $app->id }})">
                                            <i class="fas fa-times-circle"></i>
                                            <span class="db-tooltip">Reject</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="db-table-body-td text-center py-10 text-gray-400">
                                    <i class="fas fa-store-slash text-3xl block mb-2"></i>
                                    No applications found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applications->hasPages())
                <div class="p-4">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px; width:100%; max-width:480px; margin:0 16px;">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:8px;">Reject Application</h3>
        <p style="font-size:13px; color:#666; margin-bottom:20px;">Please provide a reason. This will be visible to the applicant.</p>
        <form id="rejectForm" method="POST">
            @csrf
            <textarea name="rejection_reason" required
                      style="width:100%; border:1.5px solid #e5e7eb; border-radius:10px; padding:12px; font-size:14px; height:100px; outline:none; resize:vertical;"
                      placeholder="e.g. Incomplete business documents, invalid license number..."></textarea>
            <div style="display:flex; gap:12px; margin-top:16px;">
                <button type="button"
                        style="flex:1; height:44px; border:2px solid #e5e7eb; border-radius:10px; background:#fff; font-size:14px; font-weight:600; cursor:pointer;"
                        onclick="closeRejectModal()">Cancel</button>
                <button type="submit"
                        style="flex:1; height:44px; border:none; border-radius:10px; background:#ef4444; color:#fff; font-size:14px; font-weight:700; cursor:pointer;">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
function openRejectModal(appId) {
    document.getElementById('rejectForm').action = '/admin/restaurant-application/' + appId + '/reject';
    var modal = document.getElementById('rejectModal');
    modal.style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush
