@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('menu.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.advertisement.index') }}">{{ __('menu.advertisements') }}</a></li>
                    <li class="breadcrumb-item active">Edit Advertisement</li>
                </ol>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">Edit Advertisement / Banner Block</h3>
                </div>
                <div class="db-card-body">
                    <form action="{{ route('admin.advertisement.update', $advertisement) }}" method="POST"
                        enctype="multipart/form-data" id="adForm">
                        @csrf
                        @method('PUT')

                        {{-- Title --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title required">Title</label>
                            <input type="text" name="title" id="adTitle"
                                class="db-field-control @error('title') invalid @enderror"
                                value="{{ old('title', $advertisement->title) }}"
                                placeholder="e.g. Summer Sale Banner">
                            @error('title')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title">Description <span class="text-gray-400 text-xs">(optional subtitle text)</span></label>
                            <input type="text" name="description" id="adDescription"
                                class="db-field-control @error('description') invalid @enderror"
                                value="{{ old('description', $advertisement->description) }}"
                                placeholder="Short tagline shown on the banner">
                            @error('description')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Link --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title">Click URL <span class="text-gray-400 text-xs">(optional)</span></label>
                            <input type="text" name="link" id="adLink"
                                class="db-field-control @error('link') invalid @enderror"
                                value="{{ old('link', $advertisement->link) }}"
                                placeholder="https://example.com/offer">
                            @error('link')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Position --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title required">Display Position</label>
                            <div class="db-field-down-arrow">
                                <select name="position" id="adPosition"
                                    class="db-field-control appearance-none @error('position') invalid @enderror">
                                    <option value="">— Select Position —</option>
                                    @foreach ($positions as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('position', $advertisement->position) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('position')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title required">{{ __('levels.status') }}</label>
                            <div class="db-field-down-arrow">
                                <select name="status"
                                    class="db-field-control appearance-none @error('status') invalid @enderror">
                                    @foreach (trans('statuses') as $key => $status)
                                        <option value="{{ $key }}"
                                            {{ old('status', $advertisement->status) == $key ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('status')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Image upload --}}
                        <div class="form-col-12 mb-4">
                            <label class="db-field-title" for="adImage">Banner Image</label>
                            <p class="text-xs text-gray-500 mb-2">Leave blank to keep the current image. Max 5MB.</p>

                            {{-- Current image --}}
                            @if ($advertisement->getFirstMediaUrl('advertisement'))
                                <div class="mb-3">
                                    <p class="text-xs text-gray-500 mb-1">Current image:</p>
                                    <img id="imagePreview" src="{{ $advertisement->image }}" alt="current"
                                        class="w-full max-w-sm rounded border border-gray-200 object-cover max-h-40">
                                </div>
                            @else
                                <img id="imagePreview" src="#" alt="preview"
                                    class="w-full max-w-sm rounded border border-gray-200 object-cover max-h-40 hidden">
                            @endif

                            <input name="image" type="file" id="adImage"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="db-field-control @error('image') invalid @enderror"
                                onchange="previewAdImage(this)">
                            @error('image')
                                <small class="db-field-alert">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-col-12">
                            <button type="submit" class="db-btn text-white bg-primary">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Update Advertisement</span>
                            </button>
                            <a href="{{ route('admin.advertisement.index') }}" class="db-btn ml-2 text-gray-600 bg-gray-100">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live Preview Panel --}}
        <div class="col-12 col-lg-5">
            <div class="db-card sticky top-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <i class="fa-solid fa-eye mr-2 text-primary"></i>Live Position Preview
                    </h3>
                    <span class="text-xs text-gray-500">Shows existing active ads at selected position</span>
                </div>
                <div class="db-card-body p-4">

                    {{-- Current ad being edited --}}
                    <div id="currentAdPreview" class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Ad:</p>
                        <div class="ad-preview-card border-2 border-dashed border-primary rounded-lg overflow-hidden bg-gray-50">
                            <div id="previewImageWrap" class="{{ $advertisement->getFirstMediaUrl('advertisement') ? '' : 'hidden' }}">
                                <img id="previewBannerImg"
                                    src="{{ $advertisement->getFirstMediaUrl('advertisement') ? $advertisement->image : '#' }}"
                                    alt="preview" class="w-full object-cover max-h-40">
                            </div>
                            <div class="p-3">
                                <p id="previewTitle" class="font-semibold text-sm text-gray-800">{{ $advertisement->title }}</p>
                                <p id="previewDesc" class="text-xs text-gray-500 mt-1">{{ $advertisement->description }}</p>
                                <p id="previewLink" class="text-xs text-primary mt-1 break-all">{{ $advertisement->link }}</p>
                                <span id="previewPosition" class="inline-block mt-2 text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700">
                                    {{ \App\Models\Advertisement::positions()[$advertisement->position] ?? $advertisement->position }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Existing ads at selected position --}}
                    <div id="existingAdsSection" class="hidden">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Other ads at this position:
                        </p>
                        <div id="existingAdsList" class="space-y-2"></div>
                    </div>

                    <div id="noPositionMsg" class="hidden text-center text-gray-400 py-8">
                        <i class="fa-solid fa-rectangle-ad text-3xl mb-2 block"></i>
                        <p class="text-sm">Select a position to see a live preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const CURRENT_AD_ID = {{ $advertisement->id }};
        window._adPreviewUrl = "{{ route('admin.advertisement.preview') }}";
    </script>
    <script src="{{ asset('js/advertisement/edit.js') }}"></script>
@endpush
