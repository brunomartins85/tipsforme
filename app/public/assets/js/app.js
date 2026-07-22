document.addEventListener('DOMContentLoaded', () => {
    const passwordToggle = document.querySelector('[data-password-toggle]');

    if (passwordToggle) {
        passwordToggle.addEventListener('click', () => {
            const inputId = passwordToggle.getAttribute('data-password-toggle');
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            passwordToggle.textContent = isPassword
                ? passwordToggle.getAttribute('data-hide-label')
                : passwordToggle.getAttribute('data-show-label');
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const selectAllButton = document.querySelector('[data-select-all]');

    if (selectAllButton) {
        selectAllButton.addEventListener('click', () => {
            const checkboxes = Array.from(
                document.querySelectorAll('[data-employee-checkbox]:not(:disabled)')
            );
            const shouldSelect = checkboxes.some((checkbox) => !checkbox.checked);

            checkboxes.forEach((checkbox) => {
                checkbox.checked = shouldSelect;
            });
        });
    }
});

// Módulo 03: sidebar responsiva e prévia do lançamento.
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const close = document.querySelector('[data-sidebar-close]');

    const setSidebar = (open) => {
        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.toggle('sidebar--open', open);
        backdrop.classList.toggle('sidebar-backdrop--visible', open);
        document.body.classList.toggle('sidebar-lock', open);

        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    };

    toggle?.addEventListener('click', () => setSidebar(true));
    close?.addEventListener('click', () => setSidebar(false));
    backdrop?.addEventListener('click', () => setSidebar(false));

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            setSidebar(false);
        }
    });

    const form = document.querySelector('[data-tip-form]');

    if (!form) {
        return;
    }

    const cashInput = form.querySelector('[data-cash-input]');
    const cardInput = form.querySelector('[data-card-input]');
    const shiftSelect = form.querySelector('[data-shift-select]');
    const feePercentage = Number(form.getAttribute('data-fee-percentage') || 0);
    const participantSingular = form.getAttribute('data-participant-singular') || 'participant';
    const participantPlural = form.getAttribute('data-participant-plural') || 'participants';
    const locale = document.documentElement.lang === 'en' ? 'en-IE' : 'pt-PT';
    const formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'EUR',
    });

    const parseMoney = (rawValue) => {
        const cleaned = String(rawValue || '').trim().replace(/[^0-9,.-]/g, '');

        if (!cleaned || cleaned.includes('-')) {
            return 0;
        }

        const lastComma = cleaned.lastIndexOf(',');
        const lastDot = cleaned.lastIndexOf('.');
        const decimalPosition = Math.max(lastComma, lastDot);
        let normalized;

        if (decimalPosition >= 0 && cleaned.length - decimalPosition - 1 <= 2) {
            const integerPart = cleaned.slice(0, decimalPosition).replace(/\D/g, '') || '0';
            const decimalPart = cleaned.slice(decimalPosition + 1).replace(/\D/g, '');
            normalized = `${integerPart}.${decimalPart}`;
        } else {
            normalized = cleaned.replace(/\D/g, '');
        }

        const value = Number(normalized);

        return Number.isFinite(value) ? value : 0;
    };

    const getEmployeeCount = () => {
        if (shiftSelect) {
            return Number(shiftSelect.selectedOptions[0]?.dataset.employeeCount || 0);
        }

        const participants = form.querySelector('[data-preview-participants]');
        const match = participants?.textContent?.match(/\d+/);

        return Number(match?.[0] || 0);
    };

    const updateShiftSummary = () => {
        if (!shiftSelect) {
            return;
        }

        const option = shiftSelect.selectedOptions[0];
        const count = Number(option?.dataset.employeeCount || 0);
        const names = option?.dataset.employeeNames || '';
        const namesElement = form.querySelector('[data-shift-employee-names]');
        const countElement = form.querySelector('[data-shift-employee-count]');

        if (namesElement) {
            namesElement.textContent = names;
        }

        if (countElement) {
            countElement.textContent = `${count} ${count === 1 ? participantSingular : participantPlural}`;
        }
    };

    const setText = (selector, value) => {
        const element = form.querySelector(selector);

        if (element) {
            element.textContent = value;
        }
    };

    const updatePreview = () => {
        const cash = parseMoney(cashInput?.value);
        const cardGross = parseMoney(cardInput?.value);
        const fee = cardGross * (feePercentage / 100);
        const cardNet = cardGross - fee;
        const total = cash + cardNet;
        const employeeCount = getEmployeeCount();
        const perEmployee = employeeCount > 0 ? total / employeeCount : 0;

        setText('[data-preview-cash]', formatter.format(cash));
        setText('[data-preview-card-gross]', formatter.format(cardGross));
        setText('[data-preview-fee]', `- ${formatter.format(fee)}`);
        setText('[data-preview-card-net]', formatter.format(cardNet));
        setText('[data-preview-total]', formatter.format(total));
        setText('[data-preview-person]', formatter.format(perEmployee));
        setText(
            '[data-preview-participants]',
            `${employeeCount} ${employeeCount === 1 ? participantSingular : participantPlural}`
        );
    };

    [cashInput, cardInput].forEach((input) => {
        input?.addEventListener('input', updatePreview);
    });

    shiftSelect?.addEventListener('change', () => {
        updateShiftSummary();
        updatePreview();
    });

    updateShiftSummary();
    updatePreview();
});

// Módulo 09: acabamento, acessibilidade e suporte instalável.
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !sidebar?.classList.contains('sidebar--open')) {
            return;
        }

        sidebar.classList.remove('sidebar--open');
        backdrop?.classList.remove('sidebar-backdrop--visible');
        document.body.classList.remove('sidebar-lock');
        document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', 'false');
    });

    document.querySelectorAll('.alert').forEach((alert) => {
        alert.setAttribute('role', alert.classList.contains('alert--error') ? 'alert' : 'status');

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'alert__close';
        closeButton.setAttribute('aria-label', document.documentElement.lang === 'en' ? 'Close message' : 'Fechar mensagem');
        closeButton.textContent = '×';
        closeButton.addEventListener('click', () => alert.remove());
        alert.append(closeButton);
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented || !form.checkValidity()) {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            if (!submitButton || submitButton.dataset.keepEnabled === 'true') {
                return;
            }

            window.setTimeout(() => {
                submitButton.disabled = true;
                submitButton.classList.add('button--loading');
                submitButton.setAttribute('aria-busy', 'true');
            }, 0);
        });
    });

    const showConnectionToast = (online) => {
        const previous = document.querySelector('[data-connection-toast]');
        previous?.remove();

        const toast = document.createElement('div');
        toast.className = `connection-toast ${online ? 'connection-toast--online' : 'connection-toast--offline'}`;
        toast.dataset.connectionToast = 'true';
        toast.setAttribute('role', 'status');
        toast.textContent = document.documentElement.lang === 'en'
            ? (online ? 'Connection restored' : 'You are offline')
            : (online ? 'Ligação restabelecida' : 'Sem ligação à internet');
        document.body.append(toast);

        if (online) {
            window.setTimeout(() => toast.remove(), 3500);
        }
    };

    window.addEventListener('offline', () => showConnectionToast(false));
    window.addEventListener('online', () => showConnectionToast(true));

    const manifest = document.querySelector('link[rel="manifest"]');
    if ('serviceWorker' in navigator && manifest) {
        const serviceWorkerUrl = new URL('service-worker.js', manifest.href);
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(serviceWorkerUrl.href, {
                scope: new URL('./', manifest.href).pathname,
            }).catch(() => {
                // O app continua funcional mesmo quando o navegador bloqueia PWA.
            });
        });
    }
});

// Módulo 10: copiar dados de apoio sem depender de bibliotecas externas.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const targetId = button.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;

            if (!target) {
                return;
            }

            const value = target.textContent?.trim() || '';

            try {
                await navigator.clipboard.writeText(value);
            } catch {
                const temporary = document.createElement('textarea');
                temporary.value = value;
                temporary.setAttribute('readonly', '');
                temporary.style.position = 'fixed';
                temporary.style.opacity = '0';
                document.body.append(temporary);
                temporary.select();
                document.execCommand('copy');
                temporary.remove();
            }

            const previousLabel = button.textContent;
            button.textContent = button.getAttribute('data-copy-success') || 'Copied';
            window.setTimeout(() => {
                button.textContent = previousLabel;
            }, 2200);
        });
    });
});

// Patch v1.1.1: dia da semana e hora local do restaurante no cabeçalho.
document.addEventListener('DOMContentLoaded', () => {
    const clock = document.querySelector('[data-live-clock]');
    const value = clock?.querySelector('[data-live-clock-value]');

    if (!clock || !value) {
        return;
    }

    const locale = clock.getAttribute('data-locale') || 'pt-PT';
    const timeZone = clock.getAttribute('data-timezone') || 'Europe/Lisbon';

    const capitalize = (text) => text.charAt(0).toUpperCase() + text.slice(1);

    const updateClock = () => {
        const now = new Date();
        let weekday = new Intl.DateTimeFormat(locale, {
            weekday: 'long',
            timeZone,
        }).format(now);

        if (locale.toLowerCase().startsWith('pt')) {
            weekday = weekday.replace(/-feira$/i, '');
        }

        weekday = capitalize(weekday);

        const time = new Intl.DateTimeFormat(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone,
        }).format(now);

        value.textContent = `${weekday}, ${time}'`;
    };

    updateClock();
    window.setInterval(updateClock, 30_000);
});

