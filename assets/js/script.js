// Simple toggle between login and register forms
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginBtn = document.querySelector('button[onclick="showForm(\'login\')"]');
    const registerBtn = document.querySelector('button[onclick="showForm(\'register\')"]');

    window.showForm = function(type) {
        if (type === 'login') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            loginBtn.classList.add('active');
            registerBtn.classList.remove('active');
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            registerBtn.classList.add('active');
            loginBtn.classList.remove('active');
        }
    }

    // default to login
    showForm('login');
});
