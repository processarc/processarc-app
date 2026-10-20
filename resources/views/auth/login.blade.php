<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in to ProcessArc</title><meta name="description" content="Sign in to the ProcessArc operations demo workspace."><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-body">
    <a class="login-back" href="{{ route('home') }}">← <span>Back to ProcessArc</span></a>
    <div class="login-layout">
        <section class="login-panel">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 23V9h9.5a5 5 0 0 1 0 10H10"/><path d="M17 23l5-7 5 7"/><path d="M20.1 18.7h3.8"/></svg></span><span>process<span class="brand-light">arc</span></span></a>
            <div class="login-copy"><span class="eyebrow">YOUR OPERATIONS, IN FOCUS</span><h1>Welcome<br><em>back.</em></h1><p>Sign in to continue exploring your ProcessArc workspace.</p></div>
            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <label for="email">Work email</label><input id="email" type="email" name="email" value="{{ old('email', 'demo@processarc.net') }}" required autofocus autocomplete="username">
                <label for="password">Password</label><input id="password" type="password" name="password" value="ProcessArcDemo!2026" required autocomplete="current-password">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
                <label class="remember-option"><input type="checkbox" name="remember"> Keep me signed in</label>
                <button type="submit" class="button button-dark login-submit">Sign in to the demo <span>↗</span></button>
            </form>
            <div class="demo-access"><span>DEMO ACCESS</span><p>Use <b>demo@processarc.net</b> · <b>ProcessArcDemo!2026</b></p><small>Demo data is sample information. Do not enter confidential business records.</small></div>
        </section>
        <aside class="login-visual"><div class="login-visual-copy"><span class="eyebrow eyebrow-green">PROCESSARC OPERATIONS CONSOLE</span><h2>Turn operational<br>complexity into<br><em>clear next steps.</em></h2><p>One structured workspace for the processes, people and improvements that keep work moving.</p></div><svg class="login-visual-svg" viewBox="0 0 500 250" aria-hidden="true"><path d="M20 180C95 180 90 65 170 65s85 125 165 125 65-77 145-77" fill="none" stroke="#8ee6c3" stroke-width="2"/><path d="M20 180c78 0 85-42 155-42s90 73 160 73 85-49 145-49" fill="none" stroke="#8393f2" stroke-width="2" stroke-dasharray="6 8"/><g fill="#17262d" stroke="#97a8b0"><circle cx="20" cy="180" r="10"/><circle cx="170" cy="65" r="10"/><circle cx="350" cy="190" r="10"/><circle cx="480" cy="113" r="10"/></g></svg><div class="login-visual-foot"><span>DISCOVER</span><span>MAP</span><span>IMPROVE</span></div></aside>
    </div>
</body>
</html>
