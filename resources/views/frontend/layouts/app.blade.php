<!DOCTYPE html>
<html>
@include('frontend.partials._head')

<body  @stack('body-data')>
    <audio id="myAudio1">
        <source src="{{asset('beep.mp3')}}" type="audio/mpeg">
    </audio>
    
    <div id="main-wrapper">
        @include('frontend.partials._nav')

        @yield('main-content')
        @includeUnless(request()->is(['login', 'register']), 'frontend.partials._footer')

    </div>

    {{-- Mobile Bottom Navigation Bar --}}
    @auth
    <nav class="mobile-bottom-nav">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Home</span>
        </a>
        <a href="{{ route('search') }}" class="{{ request()->routeIs('search') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span>Search</span>
        </a>
        @php
            $cartQty = !blank(session()->get('cart')) ? session()->get('cart')['totalQty'] : 0;
            $cartHref = !blank(session()->get('session_cart_restaurant'))
                ? route('restaurant.show', [session()->get('session_cart_restaurant')])
                : route('home');
        @endphp
        <a href="{{ $cartHref }}" class="">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            @if($cartQty > 0)
                <span class="nav-badge">{{ $cartQty }}</span>
            @endif
            <span>Cart</span>
        </a>
        <a href="{{ route('account.order') }}" class="{{ request()->routeIs('account.order*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="2"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="13" y2="16"/>
            </svg>
            <span>Orders</span>
        </a>
        <a href="{{ route('account.profile') }}" class="{{ request()->routeIs('account.profile*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Profile</span>
        </a>
    </nav>
    @else
    <nav class="mobile-bottom-nav">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Home</span>
        </a>
        <a href="{{ route('search') }}" class="{{ request()->routeIs('search') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span>Search</span>
        </a>
        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span>Sign In</span>
        </a>
        <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            <span>Register</span>
        </a>
    </nav>
    @endauth

    @include('frontend.partials._scripts')

</body>

</html>
