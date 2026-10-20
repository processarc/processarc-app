<div class="assistant-widget" data-assistant-widget>
    <div class="assistant-greeting" data-assistant-greeting hidden>Hi 👋 Want a quick tour of ProcessArc?</div>
    <button class="assistant-launcher" type="button" aria-label="Open ProcessArc Assistant" aria-controls="assistant-panel" aria-expanded="false"><svg viewBox="0 0 32 32" role="img" aria-label="Chat"><path d="M5 7.5h22v15H15l-6 4v-4H5z"/><path d="M10 13h12m-12 4h8"/></svg><span class="assistant-pulse"></span></button>
    <section class="assistant-panel" id="assistant-panel" role="dialog" aria-modal="false" aria-labelledby="assistant-title" hidden>
        <header class="assistant-header"><span class="assistant-avatar" aria-hidden="true">P</span><span><b id="assistant-title">ProcessArc Assistant</b><small><i></i> Online · responses are not stored</small></span><button class="assistant-minimize" type="button" aria-label="Minimize assistant">−</button><button class="assistant-close" type="button" aria-label="Close assistant">×</button></header>
        <div class="assistant-messages" aria-live="polite" aria-relevant="additions text"><div class="assistant-message assistant-message-bot">Hi! I can introduce ProcessArc, explain the demo and AI safeguards, or help you reach our team.</div></div>
        <div class="assistant-quick-replies" aria-label="Suggested questions"><button type="button">What is ProcessArc?</button><button type="button">Book a demo</button><button type="button">Show pricing</button><button type="button">How does the AI part work?</button><button type="button">Talk to a human</button></div>
        <form class="assistant-contact" action="{{ route('api.assistant.contact') }}" method="POST" data-async-form="assistant-contact" hidden>
            @csrf
            <p>Leave your email and the ProcessArc team can follow up.</p>
            <label class="sr-only" for="assistant-contact-email">Your email address</label><input id="assistant-contact-email" type="email" name="email" autocomplete="email" placeholder="you@company.com" required>
            <label class="assistant-consent"><input type="checkbox" name="consent" value="1" required>I agree that ProcessArc may use my email only to reply. <a href="{{ route('legal.privacy') }}">Privacy</a>.</label>
            <button class="button button-dark button-small" type="submit">Request a reply</button><p class="form-status" aria-live="polite"></p>
        </form>
        <div class="assistant-footer"><form class="assistant-input-form" action="{{ route('api.assistant') }}" method="POST" data-assistant-form>@csrf<label class="sr-only" for="assistant-message">Ask ProcessArc Assistant</label><input id="assistant-message" name="message" maxlength="1000" autocomplete="off" placeholder="Ask about ProcessArc…" required><button type="submit" aria-label="Send message">↑</button></form><p>Don’t share sensitive data. <a href="{{ route('legal.privacy') }}">Privacy notice</a></p><button type="button" class="assistant-human-link">Leave your email for a reply</button></div>
    </section>
</div>
