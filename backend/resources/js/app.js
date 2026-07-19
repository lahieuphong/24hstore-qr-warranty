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
