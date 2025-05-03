 import { loadPage } from '../scripts/app.js';
import { fetchOrganTypes } from '../data/data.js';

async function loadProfile() {
    const userId = localStorage.getItem('user_id');
    const token = localStorage.getItem('token');
    const API_BASE_URL = window.API_BASE_URL;

    try {
        const response = await fetch(`${API_BASE_URL}users/${userId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const user = await response.json();

        document.getElementById('firstName').value = user.first_name || '';
        document.getElementById('lastName').value = user.last_name || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('phoneNumber').value = user.phone_number || '';
        document.getElementById('streetAddress').value = user.street_address || '';
        document.getElementById('city').value = user.city || '';
        document.getElementById('stateProvince').value = user.state_province || '';
        document.getElementById('country').value = user.country || '';
        document.getElementById('postalCode').value = user.postal_code || '';
        document.getElementById('dateOfBirth').value = user.date_of_birth || '';
        document.getElementById('gender').value = user.gender || '';
        document.getElementById('preferredLanguage').value = user.preferred_language || '';

        const organTypes = await fetchOrganTypes();
            const organTypeSelect = document.getElementById('organType');
            organTypeSelect.innerHTML = ''; 
            organTypes.forEach(organType => {
                const option = document.createElement('option');
                option.value = organType.id;
                option.text = organType.name;
                organTypeSelect.appendChild(option);
            });
    } catch (error) {
        console.error('Error loading profile:', error);
        alert('Failed to load profile. Please try again later.');
    }
}

async function updateProfile() {
    const userId = localStorage.getItem('user_id');
    const token = localStorage.getItem('token');
    const API_BASE_URL = window.API_BASE_URL;

    const updatedUserData = {
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone_number: document.getElementById('phoneNumber').value,
        street_address: document.getElementById('streetAddress').value,
        city: document.getElementById('city').value,
        state_province: document.getElementById('stateProvince').value,
        country: document.getElementById('country').value,
        postal_code: document.getElementById('postalCode').value,
        date_of_birth: document.getElementById('dateOfBirth').value,
    };

    try {
        const response = await fetch(`${API_BASE_URL}users/${userId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updatedUserData),
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        alert('Profile updated successfully!');
        loadProfile();
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Failed to update profile. Please try again.');
    }
}

async function manageDonationIntent() {
    const userId = localStorage.getItem('user_id');
    const token = localStorage.getItem('token');
    const API_BASE_URL = window.API_BASE_URL;

    try {
         const response = await fetch(`${API_BASE_URL}users/${userId}/platform-intent`, {
             method: 'GET',
             headers: {
                 'Authorization': `Bearer ${token}`,
                 'Content-Type': 'application/json',
             },
         });
         if (response.ok){
            const intents = await response.json();
            if (Array.isArray(intents) && intents.length > 0) {
                const donationIntent = intents[0];

                const organTypeId = donationIntent.organ_type_id;
                const intentNotes = donationIntent.notes;
                const intentId = donationIntent.id;
        
                document.getElementById('organType').value = organTypeId || '';
                document.getElementById('intentNotes').value = intentNotes || '';
                document.getElementById('intentId').value = intentId || '';
            } else {
                document.getElementById('organType').value = '';
                document.getElementById('intentNotes').value = '';
                document.getElementById('intentId').value = '';
             }
         } else if (response.status === 404) {
             
             document.getElementById('organType').value = '';
             document.getElementById('intentNotes').value = '';
             document.getElementById('intentId').value = '';
         }
         else {
             console.error('Error:', response.status, response.statusText);
             
             document.getElementById('organType').value = '';
             document.getElementById('intentNotes').value = '';
             document.getElementById('intentId').value = '';
         }

       
    } catch (error) {
        console.error('Error loading donation intent:', error);
    }

  
}

async function updateDonationIntent() {
     const userId = localStorage.getItem('user_id');
     const token = localStorage.getItem('token');
     const API_BASE_URL = window.API_BASE_URL;

     const updatedIntentData = {
         organ_type_id: document.getElementById('organType').value,
         notes: document.getElementById('intentNotes').value
     };
     const intentId = document.getElementById('intentId').value;

    try {
        const method = intentId ? 'PUT' : 'POST';  
         const url = `${API_BASE_URL}users/${userId}/platform-intent`;
        const response = await fetch(url, {
            method: method, 
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updatedIntentData),  
         });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();

        if (method === 'POST') {
            alert('Donation intent created successfully!');
            document.getElementById('intentId').value = data.id;
        } else {
            alert('Donation intent updated successfully!');
        }

    } catch (error) {
        console.error('Error updating donation intent:', error);
        alert('Failed to manage donation intent. Please try again.');
    }
}

async function generatePdf() {
    const userId = localStorage.getItem('user_id');
    const token = localStorage.getItem('token');
    const intentId = document.getElementById('intentId').value;
     const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(`${API_BASE_URL}utilities/pdf/platform-intent/${intentId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                 },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'donation-intent.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();

    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Failed to generate PDF. Please try again.');
    }
}
loadPage;
window.loadProfile = loadProfile;
window.updateProfile = updateProfile;
window.updateDonationIntent = updateDonationIntent;
window.manageDonationIntent = manageDonationIntent;
window.generatePdf = generatePdf;
