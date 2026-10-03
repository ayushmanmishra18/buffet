@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('menu.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('menu.advertisements') }}</li>
                </ol>
            </div>
        </div>

        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ __('menu.advertisements') }}</h3>
                    <div class="db-card-filter">
                        @can('advertisement_create')
                            <a href="{{ route('admin.advertisement.create') }}" class="db-btn h-[38px] text-white bg-primary">
                                <i class="fa-solid fa-circle-plus"></i>
                                <span>{{ __('levels.add') }} {{ __('menu.advertisement') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- Position legend --}}
                <div class="px-4 pb-3 flex flex-wrap gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-100 text-blue-700 font-medium">
                        <i class="fa-solid fa-circle-dot"></i> Top (Below Navbar)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-purple-100 text-purple-700 font-medium">
                        <i class="fa-solid fa-circle-dot"></i> After Hero Banner
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-orange-100 text-orange-700 font-medium">
                        <i class="fa-solid fa-circle-dot"></i> Middle (Between Sections)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-green-100 text-green-700 font-medium">
                        <i class="fa-solid fa-circle-dot"></i> Bottom (Above Footer)
                    </span>
                </div>

                <div class="db-table-responsive">
                    <table class="db-table stripe" id="advertisement-table">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th"><i class="fas fa-th"></i></th>
                                <th class="db-table-head-th font-bold">{{ __('levels.image') }}</th>
                                <th class="db-table-head-th font-bold">{{ __('levels.title') }}</th>
                                <th class="db-table-head-th font-bold">Position</th>
                                <th class="db-table-head-th font-bold">Link</th>
                                <th class="db-table-head-th font-bold">{{ __('levels.status') }}</th>
                                @if (auth()->user()->can('advertisement_edit') || auth()->user()->can('advertisement_delete'))
                                    <th class="db-table-head-th font-bold">{{ __('levels.actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="db-table-body" id="advertisement-sortable" data-url="{{ route('admin.advertisement.sort') }}">
                            @forelse ($advertisements->flatten() as $ad)
                                <tr class="db-table-body-tr" data-id="{{ $ad->id }}">
                                    <td class="db-table-body-td">
                                        <div class="sort-handler cursor-grab">
                                            <i class="fas fa-th text-gray-400"></i>
                                        </div>
                                    </td>
                                    <td class="db-table-body-td">
                                        @if ($ad->getFirstMediaUrl('advertisement'))
                                            <img src="{{ $ad->image }}" alt="{{ $ad->title }}" class="w-24 h-14 object-cover rounded">
                                        @else
                                            <div class="w-24 h-14 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">No image</div>
                                        @endif
                                    </td>
                                    <td class="db-table-body-td">
                                        <p class="font-medium text-sm">{{ Str::limit($ad->title, 50, '...') }}</p>
                                        @if ($ad->description)
                                            <p class="text-xs text-gray-500">{{ Str::limit($ad->description, 60, '...') }}</p>
                                        @endif
                                    </td>
                                    <td class="db-table-body-td">
                                        @php
                                            $positionBadges = [
                                                'top'        => 'bg-blue-100 text-blue-700',
                                                'after_hero' => 'bg-purple-100 text-purple-700',
                                                'middle'     => 'bg-orange-100 text-orange-700',
                                                'bottom'     => 'bg-green-100 text-green-700',
                                            ];
                                            $positionLabels = \App\Models\Advertisement::positions();
                                            $badgeClass = $positionBadges[$ad->position] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="db-table-badge {{ $badgeClass }}">
                                            {{ $positionLabels[$ad->position] ?? $ad->position }}
                                        </span>
                                    </td>
                                    <td class="db-table-body-td">
                                        @if ($ad->link)
                                            <a href="{{ $ad->link }}" target="_blank" class="text-primary text-xs underline break-all">
                                                {{ Str::limit($ad->link, 40, '...') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="db-table-body-td">
                                        @if ($ad->status == 5)
                                            <span class="db-table-badge text-green-600 bg-green-100">Active</span>
                                        @else
                                            <span class="db-table-badge text-red-600 bg-red-100">Inactive</span>
                                        @endif
                                    </td>
                                    @if (auth()->user()->can('advertisement_edit') || auth()->user()->can('advertisement_delete'))
                                        <td class="db-table-body-td">
                                            @can('advertisement_edit')
                                                <a href="{{ route('admin.advertisement.edit', $ad) }}"
                                                    class="db-table-action edit" title="Edit">
                                                    <i class="fa-solid fa-pencil"></i>
                                                    <span class="db-tooltip">Edit</span>
                                                </a>
                                            @endcan
                                            @can('advertisement_delete')
                                                <form class="inline-block"
                                                    action="{{ route('admin.advertisement.destroy', $ad) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Delete this advertisement?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="db-table-action delete" title="Delete">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                        <span class="db-tooltip">Delete</span>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="db-table-body-td text-center text-gray-400 py-8">
                                        <i class="fa-solid fa-rectangle-ad text-4xl mb-2 block"></i>
                                        No advertisements yet. <a href="{{ route('admin.advertisement.create') }}" class="text-primary underline">Add one now.</a>
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

@push('js')
    <script src="{{ asset('backend/lib/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/advertisement/table.js') }}"></script>
@endpush
