<nav class="bg-white border-bottom">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <a href="{{ route('dashboard') }}" class="h5 mb-0 text-decoration-none">{{ config('app.name', 'eform') }}</a>

        <!-- <div class="d-none d-sm-flex align-items-center gap-3">
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
        </div> -->

        <div class="d-none d-sm-flex align-items-center gap-2">
            @auth
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="dropdown-item">Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Log in</a>
            @endguest
        </div>

        <!-- Mobile toggle -->
        <button id="mobileToggle" class="btn btn-outline-secondary d-sm-none">☰</button>
    </div>

    <div id="mobileMenu" class="d-none p-2 border-top">
        <a href="{{ route('dashboard') }}" class="d-block py-1">Dashboard</a>
        @auth
            <a href="{{ route('profile.edit') }}" class="d-block py-1">Profile</a>
            <form method="POST" action="{{ route('logout') }}" class="py-1">
                @csrf
                <button type="submit" class="btn btn-link p-0">Log Out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="d-block py-1">Log in</a>
        @endauth
    </div>

    <script>
        (function(){
            var toggle = document.getElementById('mobileToggle');
            var mobileMenu = document.getElementById('mobileMenu');
            if (toggle && mobileMenu) {
                toggle.addEventListener('click', function () {
                    mobileMenu.classList.toggle('d-none');
                });
            }
        })();
    </script>
</nav>
