<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            color: #18181b;
            padding: 40px 16px;
            font-size: 16px;
        }
        .wrapper {
            max-width: 640px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .header img {
            height: 56px;
        }
        .header .app-name {
            font-size: 28px;
            font-weight: 700;
            color: #18181b;
            text-decoration: none;
        }
        .card {
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .card-accent {
            height: 5px;
            width: 100%;
        }
        .accent-info    { background: #3b82f6; }
        .accent-success { background: #22c55e; }
        .accent-warning { background: #f59e0b; }
        .accent-danger  { background: #ef4444; }
        .card-body {
            padding: 44px 48px;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #18181b;
            line-height: 1.3;
        }
        .line {
            font-size: 18px;
            line-height: 1.8;
            color: #3f3f46;
            margin-bottom: 16px;
        }
        .action-wrap {
            margin: 34px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 16px 36px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            color: #ffffff !important;
        }
        .btn-info    { background: #3b82f6; }
        .btn-success { background: #22c55e; }
        .btn-warning { background: #f59e0b; }
        .btn-danger  { background: #ef4444; }
        .divider {
            border: none;
            border-top: 1px solid #e4e4e7;
            margin: 28px 0;
        }
        .footer-text {
            font-size: 15px;
            color: #a1a1aa;
            text-align: center;
            line-height: 1.7;
        }
        .footer-text a {
            color: #6366f1;
            text-decoration: none;
        }
        .meta {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #a1a1aa;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        {{-- Header / Logo --}}
        <div class="header">
            @if(config('app.logo'))
                <img src="{{ config('app.logo') }}" alt="{{ config('app.name') }}">
            @else
                <span class="app-name">{{ config('app.name') }}</span>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="card">
            <div class="card-accent accent-{{ $type ?? 'info' }}"></div>
            <div class="card-body">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
        <div class="meta">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
        </div>

    </div>
</body>
</html>