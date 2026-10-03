@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('menu.dashboard') }}</a></li>
                <li class="breadcrumb-item active">Plans</li>
            </ol>
        </div>
    </div>

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">Subscription &amp; Commission Plans</h3>
                <div class="db-card-filter">
                    <a href="{{ route('admin.plan.create') }}" class="db-btn h-[38px] text-white bg-primary">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Add Plan</span>
                    </a>
                </div>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th font-bold">Name</th>
                            <th class="db-table-head-th font-bold">Type</th>
                            <th class="db-table-head-th font-bold">Pricing</th>
                            <th class="db-table-head-th font-bold">Trial</th>
                            <th class="db-table-head-th font-bold">Featured</th>
                            <th class="db-table-head-th font-bold">Status</th>
                            <th class="db-table-head-th font-bold">{{ __('levels.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body">
                        @forelse ($plans as $plan)
                            <tr class="db-table-body-tr">
                                <td class="db-table-body-td">
                                    <p class="font-semibold text-sm">{{ $plan->name }}</p>
                                    @if ($plan->description)
                                        <p class="text-xs text-gray-500">{{ Str::limit($plan->description, 60) }}</p>
                                    @endif
                                </td>
                                <td class="db-table-body-td">
                                    @if ($plan->billing_type == 5)
                                        <span class="db-table-badge bg-blue-100 text-blue-700">Subscription</span>
                                    @else
                                        <span class="db-table-badge bg-purple-100 text-purple-700">Commission</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td font-semibold">
                                    @if ($plan->billing_type == 5)
                                        {{ setting('currency_symbol') ?? '₹' }}{{ number_format($plan->price, 2) }} / mo
                                    @else
                                        {{ $plan->commission_rate }}%
                                    @endif
                                </td>
                                <td class="db-table-body-td text-sm">
                                    {{ $plan->trial_days > 0 ? $plan->trial_days . ' days' : '—' }}
                                </td>
                                <td class="db-table-body-td">
                                    @if ($plan->is_featured)
                                        <span class="db-table-badge text-orange-600 bg-orange-100"><i class="fas fa-star mr-1"></i>Yes</span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td">
                                    @if ($plan->status)
                                        <span class="db-table-badge text-green-600 bg-green-100">Active</span>
                                    @else
                                        <span class="db-table-badge text-red-600 bg-red-100">Inactive</span>
                                    @endif
                                </td>
                                <td class="db-table-body-td">
                                    <a href="{{ route('admin.plan.edit', $plan) }}" class="db-table-action edit" title="Edit">
                                        <i class="fa-solid fa-pencil"></i>
                                        <span class="db-tooltip">Edit</span>
                                    </a>
                                    <form class="inline-block" action="{{ route('admin.plan.destroy', $plan) }}" method="POST"
                                          onsubmit="return confirm('Delete this plan? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="db-table-action delete" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span class="db-tooltip">Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="db-table-body-td text-center py-10 text-gray-400">
                                    <i class="fas fa-tags text-3xl block mb-2"></i>
                                    No plans yet. <a href="{{ route('admin.plan.create') }}" class="text-primary underline">Create your first plan.</a>
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
