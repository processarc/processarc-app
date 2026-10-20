import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('#site-navigation');

    menuToggle?.addEventListener('click', () => {
        const isOpen = navigation?.classList.toggle('is-open') ?? false;
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('menu-open', isOpen);
    });

    const closeMenus = () => {
        window.clearTimeout(hoverCloseTimer);
        document.querySelectorAll('[data-mega-item].is-open').forEach((item) => {
            item.classList.remove('is-open');
            item.querySelector('.mega-trigger')?.setAttribute('aria-expanded', 'false');
            const panel = item.querySelector('.mega-panel');
            if (panel) panel.hidden = true;
        });
    };

    let hoverCloseTimer;
    document.querySelectorAll('[data-mega-item]').forEach((item) => {
        const trigger = item.querySelector('.mega-trigger');
        const panel = item.querySelector('.mega-panel');
        if (!trigger || !panel) return;

        const setOpen = (open) => {
            window.clearTimeout(hoverCloseTimer);
            closeMenus();
            if (open) {
                item.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                panel.hidden = false;
                window.dispatchEvent(new CustomEvent('processarc:analytics', { detail: { event: 'mega_menu_open', menu: trigger.textContent.trim() } }));
            }
        };

        trigger.addEventListener('click', () => setOpen(trigger.getAttribute('aria-expanded') !== 'true'));
        item.addEventListener('mouseenter', () => {
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                window.clearTimeout(hoverCloseTimer);
                setOpen(true);
            }
        });
        item.addEventListener('mouseleave', () => {
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                hoverCloseTimer = window.setTimeout(() => {
                    if (!document.querySelector('[data-mega-item]:hover')) setOpen(false);
                }, 220);
            }
        });
        panel.addEventListener('mouseenter', () => window.clearTimeout(hoverCloseTimer));
        panel.addEventListener('mouseleave', () => {
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                hoverCloseTimer = window.setTimeout(() => {
                    if (!document.querySelector('[data-mega-item]:hover')) setOpen(false);
                }, 220);
            }
        });
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setOpen(true);
                panel.querySelector('a')?.focus();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-mega-item]')) closeMenus();
        if (event.target.closest('.site-nav a') && navigation?.classList.contains('is-open')) {
            navigation.classList.remove('is-open');
            menuToggle?.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-open');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenus();
            navigation?.classList.remove('is-open');
            menuToggle?.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-open');
            closeAssistant();
        }
    });

    const siteHeader = document.querySelector('.site-header');
    const updateScrollChrome = () => {
        siteHeader?.classList.toggle('is-scrolled', window.scrollY > 12);
        document.querySelector('.back-to-top')?.classList.toggle('is-visible', window.scrollY > 500);
    };
    window.addEventListener('scroll', updateScrollChrome, { passive: true });
    updateScrollChrome();

    const stickyCta = document.querySelector('#sticky-cta');
    const hero = document.querySelector('.hero-section, .inner-hero');
    if (stickyCta && hero && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            const visible = !entry.isIntersecting;
            stickyCta.classList.toggle('is-visible', visible);
            stickyCta.setAttribute('aria-hidden', String(!visible));
            stickyCta.inert = !visible;
        }).observe(hero);
    }

    document.querySelector('.back-to-top')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    const launcher = document.querySelector('.assistant-launcher');
    const assistantPanel = document.querySelector('#assistant-panel');
    const greeting = document.querySelector('[data-assistant-greeting]');
    const assistantMessages = document.querySelector('.assistant-messages');
    const contactForm = document.querySelector('.assistant-contact');
    const assistantInput = document.querySelector('#assistant-message');

    function openAssistant() {
        if (!assistantPanel || !launcher) return;
        assistantPanel.hidden = false;
        launcher.setAttribute('aria-expanded', 'true');
        greeting && (greeting.hidden = true);
        assistantInput?.focus();
        window.dispatchEvent(new CustomEvent('processarc:analytics', { detail: { event: 'chatbot_open' } }));
    }

    function closeAssistant() {
        if (!assistantPanel || !launcher) return;
        assistantPanel.hidden = true;
        launcher.setAttribute('aria-expanded', 'false');
    }

    launcher?.addEventListener('click', () => assistantPanel?.hidden ? openAssistant() : closeAssistant());
    document.querySelector('.assistant-close')?.addEventListener('click', closeAssistant);
    document.querySelector('.assistant-minimize')?.addEventListener('click', closeAssistant);

    if (greeting && !sessionStorage.getItem('processarc-assistant-greeted')) {
        window.setTimeout(() => {
            if (!assistantPanel?.hidden) return;
            greeting.hidden = false;
            sessionStorage.setItem('processarc-assistant-greeted', '1');
        }, 8000);
    }

    function appendAssistantMessage(text, role) {
        if (!assistantMessages) return;
        const message = document.createElement('div');
        message.className = `assistant-message assistant-message-${role}`;
        message.textContent = text;
        assistantMessages.append(message);
        assistantMessages.scrollTop = assistantMessages.scrollHeight;
    }

    async function sendAssistantMessage(message) {
        if (!message.trim()) return;
        openAssistant();
        appendAssistantMessage(message.trim(), 'user');
        window.dispatchEvent(new CustomEvent('processarc:analytics', { detail: { event: 'chatbot_message' } }));
        const typing = document.createElement('div');
        typing.className = 'assistant-message assistant-message-bot assistant-typing';
        typing.setAttribute('aria-label', 'Assistant is typing');
        typing.innerHTML = '<i></i><i></i><i></i>';
        assistantMessages?.append(typing);
        if (assistantMessages) assistantMessages.scrollTop = assistantMessages.scrollHeight;

        try {
            const response = await fetch('/api/assistant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            typing.remove();
            if (!response.ok) throw new Error(data.message ?? 'The assistant could not respond. Please try again.');
            appendAssistantMessage(data.reply, 'bot');
        } catch (error) {
            typing.remove();
            appendAssistantMessage(error.message || 'The assistant is temporarily unavailable. Please try again or email hello@processarc.net.', 'bot');
        }
    }

    document.querySelector('[data-assistant-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!assistantInput) return;
        const message = assistantInput.value;
        assistantInput.value = '';
        void sendAssistantMessage(message);
    });

    document.querySelectorAll('.assistant-quick-replies button').forEach((button) => {
        button.addEventListener('click', () => {
            if (/human/i.test(button.textContent)) {
                openAssistant();
                if (contactForm) contactForm.hidden = false;
                assistantInput?.focus();
                return;
            }
            void sendAssistantMessage(button.textContent);
        });
    });
    document.querySelector('.assistant-human-link')?.addEventListener('click', () => {
        openAssistant();
        if (contactForm) contactForm.hidden = !contactForm.hidden;
    });

    document.querySelectorAll('form[data-async-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const status = form.querySelector('.form-status');
            const submit = form.querySelector('button[type="submit"]');
            if (submit) submit.disabled = true;
            if (status) status.textContent = 'Sending…';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                    body: new FormData(form),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message ?? Object.values(data.errors ?? {}).flat()[0] ?? 'Could not submit this request.');
                if (status) status.textContent = data.message;
                form.reset();
            } catch (error) {
                if (status) status.textContent = error.message || 'Could not submit this request. Please email hello@processarc.net.';
            } finally {
                if (submit) submit.disabled = false;
            }
        });
    });

    document.querySelectorAll('[data-cta]').forEach((element) => {
        element.addEventListener('click', () => {
            window.dispatchEvent(new CustomEvent('processarc:analytics', { detail: { event: 'cta_click', cta: element.dataset.cta } }));
        });
    });

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        if (!('IntersectionObserver' in window)) {
            element.classList.add('is-revealed');
            return;
        }
        new IntersectionObserver(([entry], observer) => {
            if (entry.isIntersecting) {
                element.classList.add('is-revealed');
                observer.disconnect();
            }
        }, { threshold: 0.12 }).observe(element);
    });

    const analyzer = document.querySelector('[data-process-analyzer]');
    const analyzerFile = analyzer?.querySelector('input[type="file"]');
    const analyzerError = document.querySelector('[data-analyzer-error]');
    const analyzerLoading = document.querySelector('[data-analyzer-loading]');
    const analyzerEmpty = document.querySelector('.analyzer-empty');
    const analyzerResult = document.querySelector('[data-analyzer-result]');
    const analyzerChart = document.querySelector('[data-analyzer-chart]');
    const analyzerInsight = document.querySelector('[data-analyzer-insight]');
    const analyzerStatus = document.querySelector('[data-analyzer-status]');
    const analyzerEngine = document.querySelector('[data-analyzer-engine]');

    function setAnalyzerFile(file) {
        if (!file || !analyzerFile) return;
        if (file.size > 200 * 1024) {
            if (analyzerError) analyzerError.textContent = 'This file is larger than 200 KB. Please choose a smaller CSV.';
            analyzerFile.value = '';
            return;
        }
        if (!file.name.toLowerCase().endsWith('.csv')) {
            if (analyzerError) analyzerError.textContent = 'Please select a .csv file.';
            analyzerFile.value = '';
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        analyzerFile.files = transfer.files;
        const fileLabel = document.querySelector('[data-analyzer-dropzone] b');
        if (fileLabel) fileLabel.textContent = file.name;
        if (analyzerError) analyzerError.textContent = '';
    }

    analyzerFile?.addEventListener('change', () => setAnalyzerFile(analyzerFile.files?.[0]));
    const dropzone = document.querySelector('[data-analyzer-dropzone]');
    dropzone?.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragover');
    });
    dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
    dropzone?.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragover');
        setAnalyzerFile(event.dataTransfer?.files?.[0]);
    });

    async function runAnalyzer(useSample) {
        if (!analyzer || !analyzerLoading || !analyzerResult) return;
        if (!useSample && !analyzerFile?.files?.length) {
            if (analyzerError) analyzerError.textContent = 'Choose a CSV file or use the sample data.';
            return;
        }

        if (analyzerError) analyzerError.textContent = '';
        if (analyzerEmpty) analyzerEmpty.hidden = true;
        analyzerResult.hidden = true;
        analyzerLoading.hidden = false;
        const formData = new FormData();
        if (useSample) formData.append('sample', '1');
        else if (analyzerFile?.files?.[0]) formData.append('file', analyzerFile.files[0]);

        try {
            const response = await fetch(analyzer.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? Object.values(data.errors ?? {}).flat()[0] ?? 'Could not analyze this CSV.');
            renderAnalyzerResult(data);
            window.dispatchEvent(new CustomEvent('processarc:analytics', { detail: { event: 'mini_tool_run', sample: useSample } }));
        } catch (error) {
            if (analyzerError) analyzerError.textContent = error.message || 'The analyzer could not complete. Please try again.';
            if (analyzerEmpty) analyzerEmpty.hidden = false;
        } finally {
            analyzerLoading.hidden = true;
        }
    }

    function renderAnalyzerResult(data) {
        if (!analyzerChart || !analyzerInsight || !analyzerResult) return;
        analyzerChart.replaceChildren();
        const maximum = Math.max(1, ...data.stages.map((stage) => Number(stage.average_hours)));
        data.stages.forEach((stage) => {
            const item = document.createElement('div');
            item.className = `analyzer-bar${stage.stage === data.bottleneck ? ' analyzer-bar-bottleneck' : ''}`;
            item.style.setProperty('--bar-height', `${Math.max(8, (Number(stage.average_hours) / maximum) * 100)}%`);
            item.setAttribute('aria-label', `${stage.stage}: ${stage.average_hours} hours average across ${stage.event_count} events`);
            const hours = document.createElement('b');
            hours.textContent = `${stage.average_hours}h`;
            const bar = document.createElement('i');
            const label = document.createElement('span');
            label.textContent = stage.stage;
            item.append(hours, bar, label);
            analyzerChart.append(item);
        });
        analyzerInsight.textContent = data.insight;
        document.querySelector('[data-analyzer-count]').textContent = `${data.event_count} events`;
        analyzerStatus.textContent = data.engine.includes('fallback') || data.engine.includes('Fallback')
            ? `Using fallback engine · ${data.engine}`
            : `Analysis engine · ${data.engine}`;
        analyzerEngine.textContent = data.nvidia_integrated ? 'Built with NVIDIA NIM' : 'Prototype · NVIDIA option proposed';
        analyzerEngine.classList.toggle('analyzer-badge-nvidia', Boolean(data.nvidia_integrated));
        document.querySelector('[data-approval-status]').textContent = '';
        const approveButton = document.querySelector('[data-analyzer-approve]');
        approveButton.disabled = false;
        approveButton.textContent = 'Approve suggestion';
        analyzerResult.hidden = false;
    }

    document.querySelector('[data-analyzer-sample]')?.addEventListener('click', () => void runAnalyzer(true));
    analyzer?.addEventListener('submit', (event) => {
        event.preventDefault();
        void runAnalyzer(false);
    });
    document.querySelector('[data-analyzer-approve]')?.addEventListener('click', (event) => {
        event.currentTarget.disabled = true;
        event.currentTarget.textContent = 'Approved in this demo';
        document.querySelector('[data-approval-status]').textContent = 'This demo approval is not saved to a process record.';
    });
    document.querySelector('[data-analyzer-copy]')?.addEventListener('click', async () => {
        const summary = analyzerInsight?.textContent;
        if (!summary) return;
        try {
            await navigator.clipboard.writeText(summary);
            document.querySelector('[data-approval-status]').textContent = 'Summary copied.';
        } catch {
            document.querySelector('[data-approval-status]').textContent = 'Copy unavailable in this browser. Select the insight text to copy it.';
        }
    });

    const capabilityTabs = [...document.querySelectorAll('.capability-tabs [role="tab"]')];
    function activateCapabilityTab(tab, moveFocus = false) {
        if (!tab) return;
        capabilityTabs.forEach((item) => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
            const panel = document.getElementById(item.getAttribute('aria-controls'));
            if (panel) panel.hidden = !selected;
        });
        if (moveFocus) tab.focus();
    }
    function activateTabFromHash() {
        const tab = capabilityTabs.find((item) => `#${item.id}` === window.location.hash);
        if (tab) activateCapabilityTab(tab);
    }
    capabilityTabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateCapabilityTab(tab));
        tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const target = event.key === 'Home' ? 0 : event.key === 'End' ? capabilityTabs.length - 1
                : (index + (event.key === 'ArrowRight' ? 1 : -1) + capabilityTabs.length) % capabilityTabs.length;
            activateCapabilityTab(capabilityTabs[target], true);
        });
    });
    window.addEventListener('hashchange', activateTabFromHash);
    activateTabFromHash();

    const audienceTabs = [...document.querySelectorAll('.audience-tabs [role="tab"]')];
    audienceTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            audienceTabs.forEach((item) => {
                const selected = item === tab;
                item.setAttribute('aria-selected', String(selected));
                const panel = document.getElementById(item.getAttribute('aria-controls'));
                if (panel) panel.hidden = !selected;
            });
        });
    });

    const roiInputs = document.querySelectorAll('[data-roi-input]');
    const roiOutput = document.querySelector('[data-roi-output]');
    function updateRoi() {
        if (!roiOutput || roiInputs.length < 3) return;
        const [processInput, hoursInput, improvementInput] = roiInputs;
        const savedHours = Math.round(Number(processInput.value) * Number(hoursInput.value) * (Number(improvementInput.value) / 100) * 4.33);
        roiOutput.textContent = savedHours.toLocaleString();
        document.querySelector('[data-roi-process-count]').textContent = processInput.value;
        document.querySelector('[data-roi-hours]').textContent = hoursInput.value;
        document.querySelector('[data-roi-improvement]').textContent = improvementInput.value;
    }
    roiInputs.forEach((input) => input.addEventListener('input', updateRoi));
    updateRoi();

    const videoModal = document.querySelector('[data-video-modal]');
    let previousModalFocus = null;
    document.querySelectorAll('[data-video-open]').forEach((button) => button.addEventListener('click', () => {
        if (videoModal) {
            previousModalFocus = button;
            videoModal.hidden = false;
            videoModal.querySelector('button')?.focus();
        }
    }));
    function closeVideoModal() {
        if (!videoModal) return;
        videoModal.hidden = true;
        previousModalFocus?.focus();
    }
    videoModal?.querySelectorAll('[data-video-close]').forEach((button) => button.addEventListener('click', closeVideoModal));
    document.addEventListener('keydown', (event) => {
        if (!videoModal || videoModal.hidden) return;
        if (event.key === 'Escape') closeVideoModal();
        if (event.key === 'Tab') {
            const focusable = [...videoModal.querySelectorAll('a[href], button:not([disabled])')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    document.querySelectorAll('[data-compare]').forEach((button) => {
        button.addEventListener('click', () => {
            const isImproved = button.dataset.compare === 'with';
            document.querySelectorAll('[data-compare]').forEach((item) => item.classList.toggle('is-selected', item === button));
            const result = document.querySelector('[data-compare-result]');
            if (result) result.textContent = isImproved
                ? 'A shared process record connects ownership, workflow context and a tracked improvement action.'
                : 'A process view scattered across documents, updates and team memory.';
        });
    });

    const bottleneckMessage = document.querySelector('[data-bottleneck-message]');
    const bottleneckMessages = ['Review adds 14h', 'Approval queue: 8 cases', 'Three handoffs to review'];
    if (bottleneckMessage && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let messageIndex = 0;
        window.setInterval(() => {
            messageIndex = (messageIndex + 1) % bottleneckMessages.length;
            bottleneckMessage.textContent = bottleneckMessages[messageIndex];
        }, 4000);
    }
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.hero-graphic animateMotion').forEach((animation) => animation.remove());
    }

    document.querySelector('.app-menu-toggle')?.addEventListener('click', () => {
        document.querySelector('.app-sidebar')?.classList.toggle('is-open');
    });

    document.querySelectorAll('.flash-close').forEach((button) => {
        button.addEventListener('click', () => button.closest('.flash')?.remove());
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
});
