import { loadPage, loadData } from '../data/data.js';

let isLoggedIn = false;
let token = null;
let user_id = null;
let userRole = null;

export async function register(firstName, lastName, email, password, organization) {
    const API_BASE_URL = window.API_BASE_URL;
    const registerData = {
      first_name: firstName,
        last_name: lastName,
        email: email,
        password: password,
        organization_id: organization,
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
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(registerData)
        });

        const data = await response.json();
        if (!response.ok) {
          const errorData = await response.json();
          let errorMessage = 'Registration failed: ';
          if (errorData.errors) {
              for (const field in errorData.errors) {
                  errorMessage += `${field}: ${errorData.errors[field].join(', ')}. `;
              }
          } else {
              errorMessage += data.message || 'Unknown error';
          }
          alert(errorMessage);
          return;
        } else{
        }
    } catch (error) {
        console.error('Registration error:', error);
        alert('Registration failed: An unexpected error occurred. Please try again.');
    }
}

export async function login(email, password) {
    const API_BASE_URL = window.API_BASE_URL;
    const loginData = {
        email: email,
        password: password
    };

    try {
        const response = await fetch(API_BASE_URL + 'login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(loginData)
        });
    
        const data = await response.json();
    
        if (response.ok) {
            isLoggedIn = true;
            token = data.token;
            user_id = data.user_id;
            const userRoles = await loadData('users/' + user_id + '/roles');
            if (userRoles.length > 0) {
                userRole = userRoles[0].role_name;
            }
            else{
                alert('Login failed: user not found')
                return;
            }
            
            loadPage('dashboard.html');
            
        } else {
            alert('Login failed: ' + data.message);
            return;
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Login failed: ' + data.message);
        return;
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
        userRole = null;
        localStorage.removeItem('userRole');
        loadPage('index.html');
        alert('Logout successful');
    } else {
        const data = await response.json();
        alert('Logout failed: ' + data.message);
    }
}