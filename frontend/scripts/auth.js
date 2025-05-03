let isLoggedIn = false;
let token = null;
let user_id = null;
let isAdmin = false;

export function showLoginModal() {
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.id = 'loginModal';

    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content');

    const title = document.createElement('h2');
    title.textContent = 'Login';

    const emailLabel = document.createElement('label');
    emailLabel.textContent = 'Email:';
    emailLabel.htmlFor = 'loginEmail';
    const emailInput = document.createElement('input');
    emailInput.type = 'email';
    emailInput.id = 'loginEmail';

    const passwordLabel = document.createElement('label');
    passwordLabel.textContent = 'Password:';
    passwordLabel.htmlFor = 'loginPassword';
    const passwordInput = document.createElement('input');
    passwordInput.type = 'password';
    passwordInput.id = 'loginPassword';

    const buttonContainer = document.createElement('div');
    buttonContainer.classList.add('modal-buttons');

    const loginButton = document.createElement('button');
    loginButton.textContent = 'Login';
    loginButton.addEventListener('click', () => {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        login(email, password, modal);
    });

    const closeButton = document.createElement('button');
    closeButton.classList.add('close-button');
    closeButton.textContent = 'Close';
    closeButton.addEventListener('click', () => {
        modal.remove();
    });

    modalContent.appendChild(title);
    modalContent.appendChild(emailLabel);
    modalContent.appendChild(emailInput);
    modalContent.appendChild(document.createElement('br'));
    modalContent.appendChild(passwordLabel);
    modalContent.appendChild(passwordInput);
    modalContent.appendChild(document.createElement('br'));
    buttonContainer.appendChild(loginButton);
    buttonContainer.appendChild(closeButton);
    modalContent.appendChild(buttonContainer);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
}

export function showRegisterModal() {
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.id = 'registerModal';

    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content');

    const title = document.createElement('h2');
    title.textContent = 'Register';

    const firstNameLabel = document.createElement('label');
    firstNameLabel.textContent = 'First Name:';
    firstNameLabel.htmlFor = 'registerFirstName';
    const firstNameInput = document.createElement('input');
    firstNameInput.type = 'text';
    firstNameInput.id = 'registerFirstName';

    const lastNameLabel = document.createElement('label');
    lastNameLabel.textContent = 'Last Name:';
    lastNameLabel.htmlFor = 'registerLastName';
    const lastNameInput = document.createElement('input');
    lastNameInput.type = 'text';
    lastNameInput.id = 'registerLastName';

    const emailLabel = document.createElement('label');
    emailLabel.textContent = 'Email:';
    emailLabel.htmlFor = 'registerEmail';
    const emailInput = document.createElement('input');
    emailInput.type = 'email';
    emailInput.id = 'registerEmail';

    const passwordLabel = document.createElement('label');
    passwordLabel.textContent = 'Password (min 8 chars):';
    passwordLabel.htmlFor = 'registerPassword';
    const passwordInput = document.createElement('input');
    passwordInput.type = 'password';
    passwordInput.id = 'registerPassword';

    const buttonContainer = document.createElement('div');
    buttonContainer.classList.add('modal-buttons');

    const registerButton = document.createElement('button');
    registerButton.textContent = 'Register';
    registerButton.addEventListener('click', () => {
        const firstName = document.getElementById('registerFirstName').value;
        const lastName = document.getElementById('registerLastName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        register(firstName, lastName, email, password, modal);
    });

    const closeButton = document.createElement('button');
    closeButton.classList.add('close-button');
    closeButton.textContent = 'Close';
    closeButton.addEventListener('click', () => {
        modal.remove();
    });

    modalContent.appendChild(title);
    modalContent.appendChild(firstNameLabel);
    modalContent.appendChild(firstNameInput);
    modalContent.appendChild(document.createElement('br'));
    modalContent.appendChild(lastNameLabel);
    modalContent.appendChild(lastNameInput);
    modalContent.appendChild(document.createElement('br'));
    modalContent.appendChild(emailLabel);
    modalContent.appendChild(emailInput);
    modalContent.appendChild(document.createElement('br'));
    modalContent.appendChild(passwordLabel);
    modalContent.appendChild(passwordInput);
    modalContent.appendChild(document.createElement('br'));
    buttonContainer.appendChild(registerButton);
    buttonContainer.appendChild(closeButton);
    modalContent.appendChild(buttonContainer);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
}

export async function register(firstName, lastName, email, password, modal) {
    const API_BASE_URL = window.API_BASE_URL;
    const registerData = {
        first_name: firstName,
        last_name: lastName,
        email: email,
        password: password
    };

    if (!firstName || !lastName || !email || !password) {
        alert('Registration failed: Please fill in all fields.');
        return;
    }
    if (password.length < 8) {
        alert('Registration failed: Password must be at least 8 characters long.');
        return;
    }
    if (!/\S+@\S+\.\S+/.test(email)) {
         alert('Registration failed: Please enter a valid email address.');
         return;
    }

    try {
        const response = await fetch(API_BASE_URL + 'users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(registerData)
        });

        const data = await response.json();

        if (response.ok) {
            alert('Registration successful! You can now log in.');
            modal.remove();
        } else {
            alert('Registration failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
         console.error('Registration error:', error);
         alert('Registration failed: An unexpected error occurred. Please try again.');
    }
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
        const registerButton = document.getElementById('registerButton');
        const logoutButton = document.getElementById('logoutButton');
        loginButton.style.display = 'none';
        registerButton.style.display = 'none';
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
        const registerButton = document.getElementById('registerButton');
        const logoutButton = document.getElementById('logoutButton');
        loginButton.style.display = 'inline-block';
        registerButton.style.display = 'inline-block';
        logoutButton.style.display = 'none';
        const userButton = document.querySelector('nav ul li:first-child a');
        userButton.textContent = 'Users';
        alert('Logout successful');
    } else {
        const data = await response.json();
        alert('Logout failed: ' + data.message);
    }
}