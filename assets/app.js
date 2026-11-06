document.addEventListener('DOMContentLoaded', () => {
  // Loading state pada tombol submit
  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('button[type="submit"]');
      if (button && !button.dataset.noLoading) {
        button.dataset.originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Memproses...';
      }
    });
  });

  // Tombol mata (tampilkan/sembunyikan) untuk semua kolom kata sandi
  const iconEye = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  const iconEyeOff = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a19.8 19.8 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a19.7 19.7 0 0 1-3.17 4.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  document.querySelectorAll('input[type="password"]').forEach((input) => {
    const wrap = document.createElement('div');
    wrap.className = 'password-field';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'password-toggle';

    const render = () => {
      const visible = input.type === 'text';
      toggle.innerHTML = visible ? iconEyeOff : iconEye;
      toggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      toggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
      toggle.title = visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
    };

    toggle.addEventListener('click', () => {
      input.type = input.type === 'password' ? 'text' : 'password';
      render();
      input.focus();
    });

    render();
    wrap.appendChild(toggle);
  });

  // Kembalikan ke mode tersembunyi jika halaman dimuat ulang dari cache (tombol Back)
  window.addEventListener('pagehide', () => {
    document.querySelectorAll('.password-field input[type="text"]').forEach((i) => { i.type = 'password'; });
  });
});
