<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Market') }}</title>
    
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <style>
        /* Fallback mobile-first styles to satisfy "no heavy CSS" and ensure independence */
        * {
            pointer-events: auto !important;
        }
        :root {
            --primary-color: #10b981;
            --text-color: #1f2937;
            --muted-color: #6b7280;
            --bg-color: #f3f4f6;
            --surface-color: #ffffff;
            --border-color: #e5e7eb;
        }
        body { 
            margin: 0; 
            font-family: system-ui, -apple-system, sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            padding-bottom: 70px;
            padding-top: 150px; /* Accounts for extended header and nav */
        }
        .u-flex { display: flex; }
        .u-justify-between { justify-content: space-between; }
        .u-items-center { align-items: center; }
        .u-p-4 { padding: 1rem; }
        .u-bg-white { background-color: var(--surface-color); }
        .u-shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .u-fixed { position: fixed; }
        .u-bottom-0 { bottom: 0; }
        .u-top-0 { top: 0; }
        .u-w-full { width: 100%; box-sizing: border-box; }
        .u-z-50 { z-index: 50; }
        .u-text-center { text-align: center; }
        .u-text-sm { font-size: 0.75rem; margin-top: 4px; }
        .u-nav-link { 
            text-decoration: none; 
            color: var(--muted-color); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            flex: 1;
        }
        .u-nav-link.active { color: var(--primary-color); }
        .u-icon { width: 24px; height: 24px; }
        .u-header-title { font-weight: 600; font-size: 1rem; }
        .u-logo { font-weight: bold; font-size: 1.125rem; color: var(--text-color); text-decoration: none; }
    </style>
</head>
<body>
    @include('user.partials.header')
    <main id="main-content" class="u-w-full">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
</body>
</html>
