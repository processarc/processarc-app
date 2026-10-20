@extends('layouts.app')
@section('title', 'Activity log')
@section('breadcrumb', 'Activity log')
@section('content')
<div class="page-heading"><div><div class="eyebrow">TRACEABLE OPERATIONS</div><h1>Activity log</h1><p>A chronological record of process and improvement workspace changes.</p></div></div>
<section class="panel full-activity-panel"><div class="panel-heading"><div><h2>Recent changes</h2><p>{{ $activities->total() }} recorded events</p></div></div><div class="full-activity-list">@forelse($activities as $activity)<article class="full-activity-row"><span class="activity-avatar">{{ strtoupper(substr($activity->user?->name ?? 'P', 0, 1)) }}</span><div><b>{{ $activity->user?->name ?? 'ProcessArc' }}</b><span class="activity-event">{{ $activity->event }}</span><p>{{ $activity->description }}</p></div><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('M j, Y · g:i a') }}</time></article>@empty<div class="empty-state">Workspace changes will be recorded here.</div>@endforelse</div><div class="pagination-wrap">{{ $activities->links() }}</div></section>
@endsection
