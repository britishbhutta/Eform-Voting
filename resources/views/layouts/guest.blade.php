<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config('app.name', 'eform') }}</title>

        <!-- Font (optional) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Page-specific styles pushed from child views -->
        @stack('styles')

        <!-- Inline guest layout CSS (keeps everything self-contained) -->
        <style>
            html, body { height: 100%; margin:0; }
            body { font-family: "Figtree", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
            .guest-fullpage {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: #f3f4f6; /* same as gray-100 */
            }
            .guest-content { width: 100%; max-width: 760px; box-sizing: border-box; }
        </style>
    </head>
    <body>
        <div class="guest-fullpage">
            <div class="guest-content">
                {{ $slot }}
            </div>
        </div>

        <!-- Page-specific scripts pushed from child views -->
        @stack('scripts')
    </body>
</html>
