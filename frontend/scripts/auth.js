let isLoggedIn = false;
let token = null;
let user_id = null;
let isAdmin = false;

export function showLoginModal() {
    const modal = document.createElement('div');
    modal.classList.add('modal');

    const emailLabel = document.createElement('label');
    emailLabel.textContent = 'Email:';
    const emailInput = document.createElement('input');
    emailInput.type = 'email';
    emailInput.id = 'loginEmail';

    const passwordLabel = document.createElement('label');
    passwordLabel.textContent = 'Password:';
    const passwordInput = document.createElement('input');
    passwordInput.type = 'password';
    passwordInput.id = 'loginPassword';

    const loginButton = document.createElement('button');
    loginButton.textContent = 'Login';
    loginButton.addEventListener('click', () => {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        login(email, password, modal);
    });

    const closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.addEventListener('click', () => {
        modal.remove();
    });

    modal.appendChild(emailLabel);
    modal.appendChild(emailInput);
    modal.appendChild(document.createElement('br'));
    modal.appendChild(passwordLabel);
    modal.appendChild(passwordInput);
    modal.appendChild(document.createElement('br'));
    modal.appendChild(loginButton);
    modal.appendChild(closeButton);
    document.body.appendChild(modal);
}

export async function login(email, password, modal) {
    const API_BASE_URL = window.API_BASE_URL;
    const loginData = {
        email: email,
        password: password
    };

    const response = await fetch(API_BASE_URL + 'login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    });

    const data = await response.json();

    if (response.ok) {
        isLoggedIn = true;
        token = data.token;
        user_id = data.user_id;
        isAdmin = data.is_admin;
        modal.remove();
        const loginButton = document.getElementById('loginButton');
        const logoutButton = document.getElementById('logoutButton');
        loginButton.style.display = 'none';
        logoutButton.style.display = 'inline-block';
        if (isAdmin) {
            const userButton = document.querySelector('nav ul li:first-child a');
            userButton.textContent = 'Users (ADMIN)';
        }
    } else {
        alert('Login failed: ' + data.message);
    }
}

export async function logout() {
    const API_BASE_URL = window.API_BASE_URL;
    const response = await fetch(API_BASE_URL + 'logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
    });
    if (response.ok) {
        isLoggedIn = false;
        token = null;
        user_id = null;
        isAdmin = false;
        const loginButton = document.getElementById('loginButton');
        const logoutButton = document.getElementById('logoutButton');
        loginButton.style.display = 'inline-block';
        logoutButton.style.display = 'none';
        const userButton = document.querySelector('nav ul li:first-child a');
        userButton.textContent = 'Users';
        alert('Logout successful');
    } else {
        const data = await response.json();
        alert('Logout failed: ' + data.message);
    }
}