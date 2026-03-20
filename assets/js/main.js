function showToast(message, type) {
    type = type || 'success';
    var toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className   = 'toast toast-' + type + ' show';

    setTimeout(function () {
        toast.classList.remove('show');
    }, 4000);
}

function confirmDelete(event, message) {
    if (!confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.')) {
        event.preventDefault();
        return false;
    }
    return true;
}

function togglePw(fieldId, btn) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var isText = field.type === 'text';
    field.type = isText ? 'password' : 'text';
    btn.style.opacity = isText ? '0.5' : '1';
}

document.addEventListener('DOMContentLoaded', function () {

    var forms = document.querySelectorAll('form[novalidate]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var firstError = null;

            var required = form.querySelectorAll('[required]');
            required.forEach(function (el) {
                el.classList.remove('input-error');
                if (el.value.trim() === '') {
                    el.classList.add('input-error');
                    if (!firstError) firstError = el;
                }
            });

            var phones = form.querySelectorAll('input[type="tel"]');
            phones.forEach(function (ph) {
                if (ph.value.trim() !== '' && !/^(\+94|0)[0-9]{9}$/.test(ph.value.trim())) {
                    ph.classList.add('input-error');
                    if (!firstError) firstError = ph;
                }
            });

            if (firstError) {
                e.preventDefault();
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('input', function () {
                el.classList.remove('input-error');
            });
        });
    });

    var pw  = document.getElementById('password');
    var con = document.getElementById('confirm');
    if (pw && con) {
        con.addEventListener('blur', function () {
            if (con.value !== '' && pw.value !== con.value) {
                con.classList.add('input-error');
            } else {
                con.classList.remove('input-error');
            }
        });
    }

    var links = document.querySelectorAll('.nav-links a');
    var path  = window.location.pathname;
    links.forEach(function (a) {
        if (path.indexOf(a.getAttribute('href')) !== -1) {
            a.style.background = 'rgba(255,255,255,0.15)';
            a.style.color = '#fff';
        }
    });
});
