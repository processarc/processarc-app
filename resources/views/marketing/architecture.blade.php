@extends('layouts.marketing')

@section('title', 'ProcessArc platform architecture | Laravel & SQL')
@section('description', 'A transparent overview of the ProcessArc Laravel application architecture: responsive Blade UI, role-aware authentication, SQL-backed process records, workflows, analytics and activity history.')

@section('content')
<section class="inner-hero inner-hero-light">
    <div class="inner-hero-copy"><div class="eyebrow">PLATFORM ARCHITECTURE</div><h1>Structured at the core.<br><em>Flexible at the edges.</em></h1><p>A straightforward application foundation for process visibility and improvement — with open interfaces for future analytics and AI.</p><a class="button button-dark" href="{{ route('login') }}">Try the demo workspace <span>↗</span></a></div>
    <div class="architecture-diagram" role="img" aria-label="ProcessArc application layers: browser, Laravel, SQL, model adapter"><div class="diagram-layer"><b>01</b><span><strong>Responsive web experience</strong><small>Laravel Blade · HTML · SVG · CSS</small></span></div><div class="diagram-arrow">↓</div><div class="diagram-layer diagram-highlight"><b>02</b><span><strong>Application &amp; domain logic</strong><small>Laravel routes · validation · authorization</small></span></div><div class="diagram-arrow">↓</div><div class="diagram-split"><div class="diagram-layer"><b>03</b><span><strong>SQL data layer</strong><small>SQLite starter · MySQL ready</small></span></div><div class="diagram-layer"><b>04</b><span><strong>Optional model adapter</strong><small>Ollama · local Qwen model</small></span></div></div></div>
</section>
<section class="section architecture-spec" id="integrations">
    <div class="section-kicker">APPLICATION RESPONSIBILITIES <span>01 / 02</span></div>
    <div class="spec-grid"><article><span>01</span><h3>Presentation</h3><p>Server-rendered Blade views deliver SEO-friendly marketing pages and a responsive operations console. Custom inline SVG graphics keep core visuals lightweight and scalable.</p></article><article><span>02</span><h3>Domain</h3><p>Laravel controllers validate changes and coordinate process records, workflow stages, optimization opportunities and activity events.</p></article><article><span>03</span><h3>Persistence</h3><p>Eloquent models map relational records to SQL migrations. The default local database is SQLite; Laravel's MySQL connection is supported through environment configuration.</p></article><article><span>04</span><h3>Governance</h3><p>Session authentication, role-aware authorization for operational changes, ownership links and activity history establish a foundation for enterprise controls.</p></article></div>
</section>
<section class="section section-data-model" id="data-model"><div class="section-kicker">CORE DATA RELATIONSHIPS <span>02 / 02</span></div><div class="data-model">
    <div class="entity entity-user"><strong>User</strong><span>id · name · email · role</span></div><div class="entity-line line-user"></div>
    <div class="entity entity-process"><strong>Process</strong><span>name · category · status · cycle time</span><small>belongs to an owner</small></div>
    <div class="entity-line line-stages"></div><div class="entity entity-stage"><strong>Workflow stage</strong><span>name · status · position</span><small>belongs to a process</small></div>
    <div class="entity-line line-opportunities"></div><div class="entity entity-opportunity"><strong>Optimization opportunity</strong><span>priority · status · impact estimate</span><small>belongs to a process</small></div>
    <div class="entity entity-activity"><strong>Activity log</strong><span>actor · event · timestamp</span></div><div class="entity-line line-activity"></div>
    <p class="data-model-note">Processes may depend on other processes and can have multiple workflow stages, metric snapshots and improvement opportunities. Activity records are linked to the acting user where available.</p>
</div></section>
<section class="section section-next" id="governance"><span id="deployment" class="anchor-point"></span><div><span class="eyebrow">EXTENSION POINTS</span><h2>Start simple.<br><em>Integrate deliberately.</em></h2><p>Connect approved systems through dedicated adapters when the data model, security controls and operational use case are clear. NVIDIA services remain proposed options on the technical showcase page.</p></div><a class="button button-outline-light" href="{{ route('product') }}#technology">View AI technology options <span>↗</span></a></section>
@endsection
