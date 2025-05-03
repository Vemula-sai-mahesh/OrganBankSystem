
import { loadPage } from '../scripts/data.js';

async function loadOrganizations() {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'organizations', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        displayOrganizations(data);
    } catch (error) {
        console.error('Error loading organizations:', error);
        alert('Failed to load organizations.');
    }
}

function displayOrganizations(organizations) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = '';
    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');
    organizations.forEach(org => {
        const row = tbody.insertRow();
        row.insertCell().textContent = org.id;
        row.insertCell().textContent = org.name;
        row.insertCell().textContent = org.type;
        const actionsCell = row.insertCell();
        actionsCell.innerHTML = `
            <button class="view-btn" data-id="${org.id}">View</button>
            <button class="edit-btn" data-id="${org.id}">Edit</button>
            <button class="delete-btn" data-id="${org.id}">Delete</button>
        `;
    });
    contentSection.appendChild(table);

    // Add event listeners for the buttons
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`View organization details for ID: ${button.dataset.id}`);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`Edit organization with ID: ${button.dataset.id}`);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`Delete organization with ID: ${button.dataset.id}`);
        });
    });
}



// Organization management
function handleOrganizationCreation() {
    const organizationData = {
        name: prompt('Enter organization name:'),
        type: prompt('Enter organization type:'),
    };
    if (organizationData.name && organizationData.type) {
        createOrganization(organizationData);
    }
}

async function createOrganization(organizationData) {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'organizations/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(organizationData)
        })
        if (response.ok) {
            const data = await response.json();
            alert('Organization created successfully.');
            loadOrganizations();
        }
    }
    catch (error) {
        console.error('Error:', error);
        alert('Failed to create organization.');
    }
}

// System analytics
async function loadAnalytics() {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'admin/analytics/', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        // Logic to display analytics
        displayAnalytics(data);
    } catch (error) {
        console.error('Error loading analytics:', error);
        alert('Failed to load analytics.');
    }
}
function displayAnalytics(data) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = '';

    const analyticsContainer = document.createElement('div');
    analyticsContainer.innerHTML = `
    <h2>System Analytics</h2>
    <p>Total Users: ${data.totalUsers}</p>
    <p>Total Organizations: ${data.totalOrganizations}</p>
    <p>Total Donation Events: ${data.totalDonationEvents}</p>
    <p>Total Procured Organs: ${data.totalProcuredOrgans}</p>
    <p>Total Transplants: ${data.totalTransplants}</p>
`;

    contentSection.appendChild(analyticsContainer);
    }

// Audit logs
async function loadAuditLogs() {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'admin/audit-log/', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        // Logic to display audit logs
        displayAuditLogs(data);
    } catch (error) {
        console.error('Error loading audit logs:', error);
        alert('Failed to load audit logs.');
    }
}

function displayAuditLogs(auditLogs) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = '';
    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User ID</th>
                <th>Action</th>
                <th>Entity Type</th>
                <th>Entity ID</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');
    auditLogs.forEach(log => {
        const row = tbody.insertRow();
        row.insertCell().textContent = log.timestamp;
        row.insertCell().textContent = log.user_id;
        row.insertCell().textContent = log.action;
        row.insertCell().textContent = log.entity_type;
        row.insertCell().textContent = log.entity_id;
    });
    contentSection.appendChild(table);
}

// Api Keys
async function loadApiKeys() {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'api-keys', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        displayApiKeys(data);
    } catch (error) {
        console.error('Error loading api keys:', error);
        alert('Failed to load api keys.');
    }
}

function displayApiKeys(apiKeys) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = '';
    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>ID</th>
                <th>Organization ID</th>
                <th>Role Name</th>
                <th>Created At</th>
                <th>Expires At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    const tbody = table.querySelector('tbody');
    apiKeys.forEach(apiKey => {
        const row = tbody.insertRow();
        row.insertCell().textContent = apiKey.id;
        row.insertCell().textContent = apiKey.organization_id;
        row.insertCell().textContent = apiKey.role_name;
        row.insertCell().textContent = apiKey.created_at;
        row.insertCell().textContent = apiKey.expires_at;
        const actionsCell = row.insertCell();
        actionsCell.innerHTML = `
            <button class="view-btn" data-id="${apiKey.id}">View</button>
            <button class="edit-btn" data-id="${apiKey.id}">Edit</button>
            <button class="delete-btn" data-id="${apiKey.id}">Delete</button>
        `;
    });
    contentSection.appendChild(table);

    // Add event listeners for the buttons
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`View api key details for ID: ${button.dataset.id}`);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`Edit api key with ID: ${button.dataset.id}`);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', () => {
            alert(`Delete api key with ID: ${button.dataset.id}`);
        });
    });
}

function handleApiKeyCreation() {
    const apiKeyData = {
        organization_id: prompt('Enter organization id:'),
        role_name: prompt('Enter api key role:'),
    };
    if (apiKeyData.organization_id && apiKeyData.role_name) {
        createApiKey(apiKeyData);
    }
}

async function createApiKey(apiKeyData) {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(API_BASE_URL + 'api-keys', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(apiKeyData)
        })
        if (response.ok) {
            const data = await response.json();
            alert('Api key created successfully.');
            loadApiKeys();
        }
    }
    catch (error) {
        console.error('Error:', error);
        alert('Failed to create api key.');
    }
}

export { loadOrganizations, createOrganization, loadAnalytics, loadAuditLogs, handleOrganizationCreation, loadApiKeys, handleApiKeyCreation };