<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Operations console') · ProcessArc</title>
    <meta name="description" content="ProcessArc operations console for process visibility, workflow management and continuous improvement.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    <aside class="app-sidebar">
        <a class="brand app-brand" href="{{ route('app.dashboard') }}"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 23V9h9.5a5 5 0 0 1 0 10H10"/><path d="M17 23l5-7 5 7"/><path d="M20.1 18.7h3.8"/></svg></span><span>process<span class="brand-light">arc</span></span></a>
        <div class="workspace-switch"><span class="workspace-avatar">P</span><span><b>ProcessArc</b><small>Operations workspace</small></span><span class="workspace-chevron">⌄</span></div>
        <span class="sidebar-label">WORKSPACE</span>
        <nav class="app-nav" aria-label="Application navigation">
            <a href="{{ route('app.dashboard') }}" class="{{ request()->routeIs('app.dashboard') ? 'active' : '' }}"><span>◫</span>Overview</a>
            <a href="{{ route('app.processes.index') }}" class="{{ request()->routeIs('app.processes.*') ? 'active' : '' }}"><span>▤</span>Process registry</a>
            <a href="{{ route('app.workflows.index') }}" class="{{ request()->routeIs('app.workflows.*') ? 'active' : '' }}"><span>⌘</span>Workflows</a>
            <a href="{{ route('app.analysis') }}" class="{{ request()->routeIs('app.analysis') ? 'active' : '' }}"><span>⌁</span>Analysis</a>
            <a href="{{ route('app.optimizations.index') }}" class="{{ request()->routeIs('app.optimizations.*') ? 'active' : '' }}"><span>✳</span>Optimizations <span class="nav-count">{{ \App\Models\OptimizationOpportunity::whereNotIn('status', ['completed'])->count() }}</span></a>
            <a href="{{ route('app.activity.index') }}" class="{{ request()->routeIs('app.activity.*') ? 'active' : '' }}"><span>◷</span>Activity log</a>
        </nav>
        <div class="sidebar-spacer"></div>
        <div class="sidebar-help"><span class="help-icon">?</span><div><b>Need a hand?</b><a href="mailto:hello@processarc.net">Talk to our team</a></div></div>
        <div class="sidebar-user"><span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span><b>{{ auth()->user()->name }}</b><small>{{ ucfirst(auth()->user()->role) }}</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-button" type="submit" aria-label="Sign out" title="Sign out">↗</button></form></div>
    </aside>
    <div class="app-main">
        <header class="app-topbar"><button class="app-menu-toggle" type="button" aria-label="Open navigation">☰</button><div class="breadcrumb">Workspace <span>/</span> @yield('breadcrumb', 'Overview')</div><div class="topbar-right"><span class="environment-chip"><i></i> DEMO ENVIRONMENT</span><button class="topbar-avatar" type="button" aria-label="Signed in as {{ auth()->user()->name }}">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</button></div></header>
        <main class="app-content">
            @if(session('success'))<div class="flash flash-success" role="status">{{ session('success') }}<button type="button" class="flash-close" aria-label="Dismiss">×</button></div>@endif
            @if(session('error'))<div class="flash flash-error" role="alert">{{ session('error') }}<button type="button" class="flash-close" aria-label="Dismiss">×</button></div>@endif
            @if($errors->any())<div class="flash flash-error" role="alert">{{ $errors->first() }}<button type="button" class="flash-close" aria-label="Dismiss">×</button></div>@endif
            @yield('content')
        </main>
    </div>
    @include('layouts.assistant')
</body>
</html>
