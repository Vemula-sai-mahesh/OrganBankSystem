import { loadSection } from './data.js';

export async function showCreateModal(section) {
    const modal = document.createElement('div');
    modal.classList.add('form-modal');

    const form = document.createElement('form');
    form.id = 'create-form';

    const formFields = getFormFields(section);
    formFields.forEach(field => {
        const fieldLabel = document.createElement('label');
        fieldLabel.textContent = field.label;
        form.appendChild(fieldLabel);

        const input = document.createElement(field.tag);
        input.type = field.type || 'text';
        input.id = field.id;
        input.name = field.name;
        if (field.tag === 'select') {
            input.innerHTML = field.options.map(option => `<option value="${option}">${option}</option>`).join('');
        } else if (field.tag === 'textarea') {
             input.textContent = '';
        } else {
             input.value = '';
        }
        
        form.appendChild(input);
    });

    const submitButton = document.createElement('button');
    submitButton.textContent = 'Create';
    submitButton.type = 'button';
    submitButton.addEventListener('click', () => createItem(section, modal));
    form.appendChild(submitButton);

    const closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.type = 'button';
    closeButton.addEventListener('click', () => modal.remove());
    form.appendChild(closeButton);

    modal.appendChild(form);
    document.body.appendChild(modal);
}

export async function createItem(section, modal) {
    const API_BASE_URL = window.API_BASE_URL;
    const form = document.getElementById('create-form');
    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        data[key] = value;
    }

    try {
        const response = await fetch(`${API_BASE_URL}${section}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Item created successfully.');
            modal.remove();
            loadSection(section);
        } else {
            const errorData = await response.json();
            alert(`Error creating item: ${errorData.message}`);
        }
    } catch (error) {
        console.error('Error creating item:', error);
        alert('Error creating item.');
    }
}

export async function showEditModal(section, id) {
    const API_BASE_URL = window.API_BASE_URL;
    const modal = document.createElement('div');
    modal.classList.add('form-modal');

    const form = document.createElement('form');
    form.id = 'edit-form';
    try {
        const response = await fetch(`${API_BASE_URL}${section}/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            if (response.status === 403) {
                contentSection.innerHTML = '<p>You are not authorized to access this resource.</p>';
            } else {
                throw new Error('Network response was not ok');
            }
        } else {
            const item = await response.json();
            const formFields = getFormFields(section);
            formFields.forEach(field => {
                const fieldLabel = document.createElement('label');
                fieldLabel.textContent = field.label;
                form.appendChild(fieldLabel);

                const input = document.createElement(field.tag);
                input.type = field.type || 'text';
                input.id = field.id;
                input.name = field.name;
                if (field.tag === 'select') {
                    input.innerHTML = field.options.map(option => `<option value="${option}">${option}</option>`).join('');
                    input.value = item[field.name];
                } else if (field.tag === 'textarea') {
                    input.textContent = item[field.name];
                } else {
                    input.value = item[field.name];
                }
                form.appendChild(input);
            });
            const submitButton = document.createElement('button');
            submitButton.textContent = 'Update';
            submitButton.type = 'button';
            submitButton.addEventListener('click', () => updateItem(section, id, modal));
            form.appendChild(submitButton);

            const closeButton = document.createElement('button');
            closeButton.textContent = 'Close';
            closeButton.type = 'button';
            closeButton.addEventListener('click', () => modal.remove());
            form.appendChild(closeButton);

            modal.appendChild(form);
            document.body.appendChild(modal);
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        contentSection.innerHTML = '<p>Error loading data.</p>';
    }
}

export async function updateItem(section, id, modal) {
    const API_BASE_URL = window.API_BASE_URL;
    const form = document.getElementById('edit-form');
    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        data[key] = value;
    }

    try {
        const response = await fetch(`${API_BASE_URL}${section}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Item updated successfully.');
            modal.remove();
            loadSection(section);
        } else {
            const errorData = await response.json();
            alert(`Error updating item: ${errorData.message}`);
        }
    } catch (error) {
        console.error('Error updating item:', error);
        alert('Error updating item.');
    }
}

export async function showDeleteModal(section, id) {
    if (confirm('Are you sure you want to delete this item?')) {
        try {
            const response = await fetch(`${API_BASE_URL}${section}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            if (response.ok) {
                alert('Item deleted successfully.');
                loadSection(section);
            } else {
                const errorData = await response.json();
                alert(`Error deleting item: ${errorData.message}`);
            }
        } catch (error) {
            console.error('Error deleting item:', error);
            alert('Error deleting item.');
        }
    }
}

function getFormFields(section) {
    let formFields = [];
    switch (section) {
        case 'users':
            formFields = [
                { tag: 'input', type: 'text', label: 'Name:', name: 'name', id: 'name' },
                { tag: 'input', type: 'email', label: 'Email:', name: 'email', id: 'email' }
            ];
            break;
        case 'organizations':
            formFields = [
                { tag: 'input', type: 'text', label: 'Name:', name: 'name', id: 'name' },
                { tag: 'select', label: 'Type:', name: 'type', id: 'type', options: ['Hospital', 'Clinic', 'Research Institution'] },
                { tag: 'input', type: 'email', label: 'Email:', name: 'email', id: 'email' },
                { tag: 'input', type: 'text', label: 'Website URL:', name: 'website_url', id: 'website_url' },
                { tag: 'input', type: 'tel', label: 'Phone:', name: 'phone', id: 'phone' }
            ];
            break;
        case 'donation-events':
            formFields = [
                { tag: 'select', label: 'Donation Type:', name: 'donation_type', id: 'donation_type', options: ['Deceased Donation', 'Living Donation'] },
                { tag: 'input', type: 'text', label: 'Donor External ID:', name: 'donor_external_id', id: 'donor_external_id' },
                { tag: 'select', label: 'Status:', name: 'status', id: 'status', options: ['Pending', 'Approved', 'Rejected'] },
                { tag: 'input', type: 'text', label: 'Cause of Death:', name: 'cause_of_death', id: 'cause_of_death' },
                { tag: 'textarea', label: 'Clinical Summary:', name: 'clinical_summary', id: 'clinical_summary' },
                { tag: 'textarea', label: 'Notes:', name: 'notes', id: 'notes' }
            ];
            break;
        case 'procured-organs':
            formFields = [
                { tag: 'input', type: 'text', label: 'Organ External ID:', name: 'organ_external_id', id: 'organ_external_id' },
                { tag: 'select', label: 'Status:', name: 'status', id: 'status', options: ['Available', 'Transplanted', 'Discarded'] },
                { tag: 'textarea', label: 'Description:', name: 'description', id: 'description' },
                { tag: 'input', type: 'text', label: 'Blood Type:', name: 'blood_type', id: 'blood_type' },
                { tag: 'textarea', label: 'Clinical Notes:', name: 'clinical_notes', id: 'clinical_notes' },
                { tag: 'textarea', label: 'Packaging Details:', name: 'packaging_details', id: 'packaging_details' }
            ];
            break;
        case 'medical-markers':
            formFields = [
                { tag: 'input', type: 'text', label: 'Marker Type:', name: 'marker_type', id: 'marker_type' },
                { tag: 'input', type: 'text', label: 'Marker Value:', name: 'marker_value', id: 'marker_value' },
                { tag: 'textarea', label: 'Notes:', name: 'notes', id: 'notes' }
            ];
            break;
        case 'organ-transplants':
            formFields = [
                { tag: 'input', type: 'datetime-local', label: 'Transplant Timestamp:', name: 'transplant_timestamp', id: 'transplant_timestamp' },
                { tag: 'select', label: 'Post Transplant Status:', name: 'post_transplant_status', id: 'post_transplant_status', options: ['Stable', 'Complications', 'Rejected'] },
                { tag: 'textarea', label: 'Notes:', name: 'notes', id: 'notes' }
            ];
            break;
    }
    return formFields;
}