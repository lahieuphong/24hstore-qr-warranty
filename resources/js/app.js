import './bootstrap';

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.getAttribute('aria-controls'));

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    const eyeOpen = button.querySelector('[data-password-eye-open]');
    const eyeClosed = button.querySelector('[data-password-eye-closed]');

    const setPasswordVisible = (visible) => {
        const label = visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu';

        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-label', label);
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('title', label);
        eyeOpen?.classList.toggle('hidden', visible);
        eyeClosed?.classList.toggle('hidden', !visible);
    };

    button.addEventListener('click', () => {
        setPasswordVisible(input.type === 'password');
        input.focus();
    });
});

document.querySelectorAll('[data-login-form]').forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitButton = form.querySelector('[data-login-submit]');
    const idleLabel = form.querySelector('[data-login-idle]');
    const loadingLabel = form.querySelector('[data-login-loading]');
    const statusRegion = form.querySelector('[data-login-status]');
    let submitting = false;

    const setSubmitting = () => {
        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
        }

        idleLabel?.classList.add('hidden');
        loadingLabel?.classList.remove('hidden');
        loadingLabel?.classList.add('inline-flex');

        if (statusRegion instanceof HTMLElement) {
            statusRegion.textContent = 'Đang xác thực...';
        }
    };

    form.addEventListener('submit', async (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        submitting = true;
        setSubmitting();

        const csrfUrl = form.dataset.csrfUrl;

        if (csrfUrl) {
            const abortController = new AbortController();
            const abortTimer = window.setTimeout(() => abortController.abort(), 5000);

            try {
                const response = await fetch(csrfUrl, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    signal: abortController.signal,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Không thể làm mới phiên đăng nhập.');
                }

                const payload = await response.json();

                if (typeof payload.token !== 'string' || payload.token === '') {
                    throw new Error('Phản hồi CSRF không hợp lệ.');
                }

                const tokenInput = form.querySelector('input[name="_token"]');

                if (tokenInput instanceof HTMLInputElement) {
                    tokenInput.value = payload.token;
                }

                document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', payload.token);
            } catch {
                // Submit normally; the server turns an expired session into a fresh login page.
            } finally {
                window.clearTimeout(abortTimer);
            }
        }

        HTMLFormElement.prototype.submit.call(form);
    });
});

const loginSuccess = document.querySelector('[data-login-success]');

if (loginSuccess instanceof HTMLElement) {
    const continueUrl = loginSuccess.dataset.continueUrl;
    const parsedDelay = Number.parseInt(loginSuccess.dataset.redirectDelay ?? '', 10);
    const redirectDelay = Number.isFinite(parsedDelay) ? parsedDelay : 2000;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (continueUrl && !prefersReducedMotion) {
        window.setTimeout(() => window.location.replace(continueUrl), redirectDelay);
    }
}
