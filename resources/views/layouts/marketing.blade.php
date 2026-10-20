<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ProcessArc | See the work behind the work')</title>
    <meta name="description" content="@yield('description', 'ProcessArc helps enterprise operations teams map business processes, spot bottlenecks and manage measurable improvements.')">
    <meta name="theme-color" content="#f8f9f7">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ProcessArc">
    <meta property="og:title" content="@yield('title', 'ProcessArc | See the work behind the work')">
    <meta property="og:description" content="@yield('description', 'A clearer view of how work moves, where it slows and what to improve next.')">
    <meta property="og:url" content="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="marketing-body">
    @include('layouts.navigation')
    <main>@yield('content')</main>
    <footer class="site-footer" id="company">
        <div class="footer-top">
            <div>
                <a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 23V9h9.5a5 5 0 0 1 0 10H10"/><path d="M17 23l5-7 5 7"/><path d="M20.1 18.7h3.8"/></svg></span><span>process<span class="brand-light">arc</span></span></a>
                <p>Make the work visible.<br>Make the next move count.</p>
            </div>
            <div class="footer-links"><span class="eyebrow">Product</span><a href="{{ route('product') }}">Product overview</a><a href="{{ route('product') }}#analyzer">Process Insight Analyzer</a><a href="{{ route('login') }}">Demo workspace</a><a href="{{ route('product') }}#technology">Technical showcase</a></div>
            <div class="footer-links"><span class="eyebrow">Platform</span><a href="{{ route('home') }}#platform">Capabilities</a><a href="{{ route('home') }}#use-cases">Use cases</a><a href="{{ route('architecture') }}">Architecture</a><a href="{{ route('company') }}#contact">Contact sales</a></div>
            <div class="footer-links"><span class="eyebrow">Resources</span><a href="{{ route('resources') }}#docs">Docs &amp; guides</a><a href="{{ route('resources') }}#blog">Blog</a><a href="{{ route('resources') }}#changelog">Changelog</a><a href="{{ route('resources') }}#faq">FAQ</a></div>
            <div class="footer-links"><span class="eyebrow">Company</span><a href="{{ route('company') }}#about">About ProcessArc</a><a href="{{ route('company') }}#founder">Founder · Pirashanth Mahendran</a><a href="{{ route('company') }}#locations">Sri Lanka &amp; United States</a><a href="mailto:hello@processarc.net">hello@processarc.net</a></div>
        </div>
        <div class="footer-newsletter"><div><span class="eyebrow">OCCASIONAL NOTES FOR BETTER OPERATIONS</span><h2>Get process improvement tips.</h2></div><form action="{{ route('api.newsletter') }}" method="POST" data-async-form="newsletter">@csrf<label class="sr-only" for="newsletter-email">Email address</label><input type="email" name="email" id="newsletter-email" autocomplete="email" placeholder="Your work email" required><label class="newsletter-consent"><input type="checkbox" name="consent" value="1" required>I agree to receive ProcessArc updates. See our <a href="{{ route('legal.privacy') }}">privacy notice</a>.</label><button class="button button-green button-small" type="submit">Sign me up <span>↗</span></button><p class="form-status" aria-live="polite"></p></form></div>
        <div class="footer-social"><span>© {{ date('Y') }} ProcessArc Systems. All rights reserved.</span><span class="footer-legal"><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a><a href="{{ route('legal.security') }}">Security</a><a href="{{ route('legal.ai-governance') }}">AI governance</a></span></div>
        <p class="footer-technology-note">Technology relationship notice: NVIDIA NeMo, NIM, RAPIDS and Triton are proposed options only and do not indicate integration, endorsement, certification or partnership. <a href="{{ route('product') }}#technology">Read the notice</a>.</p>
    </footer>
    @include('layouts.assistant')
    <div class="sticky-cta" id="sticky-cta" aria-hidden="true" inert><span>Make your next improvement easier to see.</span><a href="{{ route('product') }}#analyzer" class="button button-green button-small" data-cta="sticky-try">Try ProcessArc <span>↗</span></a><a href="{{ route('company') }}#contact" class="sticky-call">Book a call</a></div>
    <button class="back-to-top" type="button" aria-label="Back to top">↑</button>
</body>
</html>
