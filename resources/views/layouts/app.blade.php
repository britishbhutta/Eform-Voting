<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config('app.name', 'eform') }}</title>

        <!-- Optional: Bootstrap CDN so authenticated UI works with same classes -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font (optional) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Page-specific styles pushed from child views -->
        @stack('styles')

        <!-- Minimal inline base CSS so pages not relying on Tailwind still look okay -->
        <style>
            body { font-family: "Figtree", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; margin:0; }
            .min-h-screen { min-height: 100vh; }
            .bg-gray-100 { background: #f3f4f6; }
            .evoting-logo{
                height: 70px;  
                width: auto;    
                max-width: 200px; 
            }
        </style>
    </head>
    <body>
        <div class="min-h-screen bg-gray-100">
            {{-- NOTE: include the correct file name 'navigate' (you supplied navigate.blade) --}}
            @include('layouts.navigation')

            {{-- Page Heading --}}
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="container py-3">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- Page Content --}}
            <main class="container my-4">
                {{ $slot }}
            </main>
        </div>

        <!-- Bootstrap JS (optional) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        {{-- Page-specific scripts --}}
        @stack('scripts')
    </body>
</html>
