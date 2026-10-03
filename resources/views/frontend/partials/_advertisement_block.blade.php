{{--
    Advertisement block partial.
    Variables:
      $ads      — Collection of Advertisement models for this position
      $position — Position slug string (top, after_hero, middle, bottom)
--}}
@if (!blank($ads) && $ads->isNotEmpty())
    <section class="ad-block ad-block--{{ $position }}">
        <div class="container">
            @if ($ads->count() === 1)
                {{-- Single banner: full-width --}}
                @php $ad = $ads->first(); @endphp
                <div class="ad-single">
                    @if ($ad->link)
                        <a href="{{ $ad->link }}" target="_blank" rel="noopener" class="ad-single__link">
                    @endif
                        <figure class="ad-single__figure">
                            <img src="{{ $ad->image }}"
                                 alt="{{ $ad->title }}"
                                 class="ad-single__img"
                                 loading="lazy">
                            @if ($ad->title || $ad->description)
                                <figcaption class="ad-single__caption">
                                    @if ($ad->title)
                                        <span class="ad-single__title">{{ $ad->title }}</span>
                                    @endif
                                    @if ($ad->description)
                                        <span class="ad-single__desc">{{ $ad->description }}</span>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    @if ($ad->link)
                        </a>
                    @endif
                </div>

            @elseif ($ads->count() === 2)
                {{-- Two banners: side by side --}}
                <div class="ad-grid ad-grid--2">
                    @foreach ($ads as $ad)
                        <div class="ad-grid__item">
                            @if ($ad->link)
                                <a href="{{ $ad->link }}" target="_blank" rel="noopener" class="ad-grid__link">
                            @endif
                                <figure class="ad-grid__figure">
                                    <img src="{{ $ad->image }}"
                                         alt="{{ $ad->title }}"
                                         class="ad-grid__img"
                                         loading="lazy">
                                    @if ($ad->title || $ad->description)
                                        <figcaption class="ad-grid__caption">
                                            @if ($ad->title)
                                                <span class="ad-grid__title">{{ $ad->title }}</span>
                                            @endif
                                            @if ($ad->description)
                                                <span class="ad-grid__desc">{{ $ad->description }}</span>
                                            @endif
                                        </figcaption>
                                    @endif
                                </figure>
                            @if ($ad->link)
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

            @else
                {{-- Three or more: horizontal scroll / auto-sliding carousel --}}
                <div class="ad-carousel swiper ad-swiper--{{ $position }}">
                    <div class="swiper-wrapper">
                        @foreach ($ads as $ad)
                            <div class="swiper-slide ad-carousel__slide">
                                @if ($ad->link)
                                    <a href="{{ $ad->link }}" target="_blank" rel="noopener" class="ad-carousel__link">
                                @endif
                                    <figure class="ad-carousel__figure">
                                        <img src="{{ $ad->image }}"
                                             alt="{{ $ad->title }}"
                                             class="ad-carousel__img"
                                             loading="lazy">
                                        @if ($ad->title || $ad->description)
                                            <figcaption class="ad-carousel__caption">
                                                @if ($ad->title)
                                                    <span class="ad-carousel__title">{{ $ad->title }}</span>
                                                @endif
                                                @if ($ad->description)
                                                    <span class="ad-carousel__desc">{{ $ad->description }}</span>
                                                @endif
                                            </figcaption>
                                        @endif
                                    </figure>
                                @if ($ad->link)
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination ad-swiper-pagination"></div>
                </div>
            @endif
        </div>
    </section>

    @once
        @push('js')
        <script>
        (function () {
            // Boot all ad Swipers after the page Swiper is ready
            function initAdSwipers() {
                document.querySelectorAll('.ad-carousel').forEach(function (el) {
                    if (el._adSwiper) return; // already inited
                    el._adSwiper = new Swiper(el, {
                        loop          : true,
                        autoplay      : { delay: 4000, disableOnInteraction: false },
                        slidesPerView : 1,
                        spaceBetween  : 0,
                        pagination    : {
                            el        : el.querySelector('.ad-swiper-pagination'),
                            clickable : true
                        }
                    });
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAdSwipers);
            } else {
                initAdSwipers();
            }
        }());
        </script>
        @endpush
    @endonce
@endif
