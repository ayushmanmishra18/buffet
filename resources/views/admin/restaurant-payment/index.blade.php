@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Restaurant Payment Settings</li>
            </ol>
        </div>
    </div>

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">India Payment Settings per Hotel/Restaurant</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Configure UPI IDs, QR codes, advance booking deposits and commission rates for each hotel.
                </p>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th font-bold">Hotel / Restaurant</th>
                            <th class="db-table-head-th font-bold">UPI ID</th>
                            <th class="db-table-head-th font-bold">QR Codes</th>
                            <th class="db-table-head-th font-bold">Advance %</th>
                            <th class="db-table-head-th font-bold">Commission</th>
                            <th class="db-table-head-th font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body">
                        @forelse ($restaurants as $restaurant)
                            @php $s = $restaurant->paymentSetting; @endphp
                            <tr class="db-table-body-tr">
                                <td class="db-table-body-td">
                                    <p class="font-semibold text-sm">{{ $restaurant->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $restaurant->user->email ?? '—' }}</p>
                                </td>
                                <td class="db-table-body-td text-sm">
                                    @if ($s->upi_id)
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $s->upi_id }}</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Not set</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td">
                                    <div class="flex gap-2">
                                        @if ($s->accept_phonepe_qr && $s->phonepe_qr_image)
                                            <span class="db-table-badge bg-purple-100 text-purple-700">PhonePe ✓</span>
                                        @endif
                                        @if ($s->accept_paytm_qr && $s->paytm_qr_image)
                                            <span class="db-table-badge bg-blue-100 text-blue-700">Paytm ✓</span>
                                        @endif
                                        @if (!$s->accept_phonepe_qr && !$s->accept_paytm_qr)
                                            <span class="text-gray-400 text-xs">UPI only</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="db-table-body-td text-sm">
                                    @if ($s->advance_booking_percent > 0)
                                        <span class="db-table-badge bg-orange-100 text-orange-700">
                                            {{ $s->advance_booking_percent }}%
                                            @if ($s->per_head_estimate > 0)
                                                <small>(₹{{ number_format($s->per_head_estimate) }}/head)</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">None</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td text-sm">
                                    @if ($s->commission_rate > 0)
                                        <span class="db-table-badge bg-green-100 text-green-700">{{ $s->commission_rate }}%</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Global default</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td">
                                    <a href="{{ route('admin.restaurant-payment.edit', $restaurant) }}"
                                       class="db-table-action edit" title="Edit">
                                        <i class="fa-solid fa-pencil"></i>
                                        <span class="db-tooltip">Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="db-table-body-td text-center py-10 text-gray-400">
                                    No active restaurants found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
