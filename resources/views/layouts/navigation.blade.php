<header class="site-header" id="site-header">
    <a class="brand" href="{{ route('home') }}" aria-label="ProcessArc home">
        <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 23V9h9.5a5 5 0 0 1 0 10H10"/><path d="M17 23l5-7 5 7"/><path d="M20.1 18.7h3.8"/></svg></span>
        <span>process<span class="brand-light">arc</span></span>
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">Menu</button>
    <nav class="site-nav" id="site-navigation" aria-label="Main navigation">
        <div class="mega-item" data-mega-item>
            <button class="mega-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="mega-product">Product <span aria-hidden="true">⌄</span></button>
            <div class="mega-panel mega-product" id="mega-product" hidden>
                <div class="mega-column"><span class="eyebrow">CAPABILITIES</span><a href="{{ route('product') }}#registry">Process Registry</a><a href="{{ route('product') }}#analysis">Process Analysis</a><a href="{{ route('product') }}#improvements">Improvement Workspace</a><a href="{{ route('product') }}#governance">Approvals &amp; Activity History</a></div>
                <div class="mega-column"><span class="eyebrow">AI LAYER</span><a href="{{ route('product') }}#ai-insights">AI Insights</a><a href="{{ route('product') }}#technology">Open-model option · Qwen via Ollama</a><a href="{{ route('product') }}#governance">Human-in-the-loop review</a><a href="{{ route('product') }}#technology">Technical showcase</a></div>
                <div class="mega-column"><span class="eyebrow">TRY IT</span><a class="mega-primary-link" href="{{ route('product') }}#analyzer">Try the Process Insight Tool <span class="new-badge">NEW</span></a><a href="{{ route('login') }}">Open demo workspace</a><a href="{{ route('product') }}#analyzer">Process Insight Analyzer ↗</a></div>
                <a href="{{ route('login') }}" class="mega-feature"><span class="mega-feature-art" aria-hidden="true"><svg viewBox="0 0 190 90"><rect x="3" y="5" width="184" height="80" rx="8" fill="#fff" stroke="#dce5df"/><rect x="3" y="5" width="32" height="80" rx="8" fill="#17272c"/><path d="M15 25h10M15 38h10M15 51h10" stroke="#a7e8c9" stroke-width="3" stroke-linecap="round"/><rect x="48" y="17" width="59" height="21" rx="4" fill="#f0f4f0"/><rect x="114" y="17" width="59" height="21" rx="4" fill="#f0f4f0"/><path d="M50 69c18-4 23-25 41-14s25 9 42-4 25-3 39-14" fill="none" stroke="#727fd1" stroke-width="3"/></svg></span><span class="eyebrow">SEE IT IN ACTION</span><strong>Explore the demo</strong><span>See the operations workspace ↗</span></a>
            </div>
        </div>
        <div class="mega-item" data-mega-item>
            <button class="mega-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="mega-platform">Platform <span aria-hidden="true">⌄</span></button>
            <div class="mega-panel mega-platform" id="mega-platform" hidden>
                <div class="mega-column"><span class="eyebrow">USE CASES</span><a href="{{ route('home') }}#use-cases">Enterprise operations</a><a href="{{ route('home') }}#use-cases">Business process teams</a><a href="{{ route('home') }}#use-cases">Automation &amp; transformation</a></div>
                <div class="mega-column"><span class="eyebrow">CAPABILITIES</span><a href="{{ route('home') }}#platform">Know what runs</a><a href="{{ route('home') }}#platform">Find where it slows</a><a href="{{ route('home') }}#platform">Make progress visible</a></div>
                <a href="{{ route('home') }}#platform" class="mega-feature mega-quote"><span class="eyebrow">A BETTER OPERATIONAL RHYTHM</span><strong>From “we think”<br>to “we know.”</strong><span>Discover the platform ↗</span></a>
            </div>
        </div>
        <div class="mega-item" data-mega-item>
            <button class="mega-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="mega-architecture">Architecture <span aria-hidden="true">⌄</span></button>
            <div class="mega-panel mega-compact" id="mega-architecture" hidden><div class="mega-column"><span class="eyebrow">PLATFORM DESIGN</span><a href="{{ route('architecture') }}#data-model">Data model</a><a href="{{ route('architecture') }}#integrations">Illustrative integrations</a><a href="{{ route('architecture') }}#governance">Security &amp; governance</a><a href="{{ route('architecture') }}#deployment">Open models &amp; deployment</a></div><a href="{{ route('architecture') }}" class="mega-feature mega-quote"><span class="eyebrow">LARAVEL · SQL · ROLE-AWARE</span><strong>Structured at the core.<br>Flexible at the edges.</strong><span>Explore architecture ↗</span></a></div>
        </div>
        <div class="mega-item" data-mega-item>
            <button class="mega-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="mega-resources">Resources <span aria-hidden="true">⌄</span></button>
            <div class="mega-panel mega-compact" id="mega-resources" hidden><div class="mega-column"><span class="eyebrow">LEARN &amp; PLAN</span><a href="{{ route('resources') }}#docs">Documentation</a><a href="{{ route('resources') }}#guides">Guides</a><a href="{{ route('resources') }}#blog">Blog</a><a href="{{ route('resources') }}#changelog">Changelog</a><a href="{{ route('resources') }}#faq">FAQ &amp; roadmap</a></div><a href="{{ route('resources') }}" class="mega-feature mega-quote"><span class="eyebrow">PROCESS IMPROVEMENT NOTES</span><strong>Practical ideas<br>for better operations.</strong><span>Explore resources ↗</span></a></div>
        </div>
        <div class="mega-item" data-mega-item>
            <button class="mega-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="mega-company">Company <span aria-hidden="true">⌄</span></button>
            <div class="mega-panel mega-compact" id="mega-company" hidden><div class="mega-column"><span class="eyebrow">PROCESSARC</span><a href="{{ route('company') }}#about">About</a><a href="{{ route('company') }}#founder">Founder</a><a href="{{ route('company') }}#contact">Contact</a><a href="{{ route('company') }}#locations">Sri Lanka &amp; United States</a><a href="{{ route('company') }}#early-access">Early access</a></div><a href="{{ route('company') }}#contact" class="mega-feature mega-quote"><span class="eyebrow">LET'S TALK</span><strong>Make the work visible.<br>Make the next move count.</strong><span>Meet ProcessArc ↗</span></a></div>
        </div>
    </nav>
    <div class="header-actions">
        <a class="login-link" href="{{ route('login') }}">Sign in</a>
        <a class="button button-dark button-small" href="{{ route('login') }}" data-cta="header-demo">Explore the demo <span aria-hidden="true">↗</span></a>
        <a class="button button-green button-small header-try" href="{{ route('product') }}#analyzer" data-cta="header-try-product">Try product</a>
    </div>
</header>
