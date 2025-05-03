// opo.js

import { loadData } from '../scripts/data.js';
import { loadPage } from '../scripts/app.js';

const API_BASE_URL = window.API_BASE_URL;

export async function loadDonationEvents() {
    try {
        const response = await fetch(API_BASE_URL + 'donation-events', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to load donation events');
        }

        // Assuming the server returns an array of donation events
        const donationEvents = data;
        const contentSection = document.getElementById('main-content');
        contentSection.innerHTML = `<h2>Donation Events</h2><button id="createDonationEventBtn">Create New Event</button><div id="donationEventsList"></div>`;
        const donationEventsList = document.getElementById('donationEventsList');
        const createDonationEventBtn = document.getElementById('createDonationEventBtn');
        createDonationEventBtn.addEventListener('click', createDonationEventForm);

        if (donationEvents.length === 0) {
            donationEventsList.innerHTML = '<p>No donation events found.</p>';
        } else {
            console.log(donationEvents)
            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Donation Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');
            donationEvents.forEach(event => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${event.id || ''}</td>
                    <td>${event.donation_type || ''}</td>
                    <td>${event.event_start_timestamp || ''}</td>
                    <td>${event.event_end_timestamp || ''}</td>
                    <td>${event.status}</td>
                    <td>
                        <button class="viewBtn" data-id="${event.id}">View</button>
                        <button class="editBtn" data-id="${event.id}">Edit</button>
                        <button class="deleteBtn" data-id="${event.id}">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
                const viewBtn = row.querySelector('.viewBtn');
                const editBtn = row.querySelector('.editBtn');
                const deleteBtn = row.querySelector('.deleteBtn');

                viewBtn.addEventListener('click', () => {
                    // Handle view logic
                    loadProcuredOrgans(event.id);
                });
                editBtn.addEventListener('click', () => {
                    // Handle edit logic
                    alert('Edit not implemented yet.');
                });
                deleteBtn.addEventListener('click', () => {
                    // Handle delete logic
                    alert('Delete not implemented yet.');
                });
            });
            donationEventsList.appendChild(table);
        }

    } catch (error) {
        console.error('Error loading donation events:', error);
        alert('Failed to load donation events');
    }
}

async function createDonationEventForm() {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = `
    <h2>Create Donation Event</h2>
    <form id="createDonationEventForm">
      <label for="donationType">Donation Type:</label>
      <select id="donationType" name="donationType">
        <option value="Deceased">Deceased</option>
        <option value="Living">Living</option>
      </select><br>
      <label for="sourceOrganizationId">Organization:</label>
        <div id="organization-list"></div><br>
      <label for="donorExternalId">Donor External Id:</label>
        <input type="text" id="donorExternalId" name="donorExternalId" required><br>
      <label for="eventStartTimestamp">Start Date:</label>
      <input type="datetime-local" id="eventStartTimestamp" name="eventStartTimestamp" required><br>
      <input type="datetime-local" id="eventEndTimestamp" name="eventEndTimestamp" required><br>
      <label for="status">Status:</label>
      <select id="status" name="status">
        <option value="Pending">Pending</option>
        <option value="Active Procurement">Active Procurement</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
      </select><br>
      <button type="submit">Create Event</button>
    </form>
  `;
    const form = document.getElementById('createDonationEventForm');
    form.addEventListener('submit', createDonationEvent);

    try {
        const organizations = await loadData('organizations');
        const organizationList = document.getElementById('organization-list');
        const organizationSelect = document.createElement('select');
        organizationSelect.id = 'sourceOrganizationId';
        organizationSelect.name = 'sourceOrganizationId';
        organizations.forEach(organization => {
            const option = document.createElement('option');
            option.value = organization.id;
            option.text = organization.name;
            organizationSelect.appendChild(option);
        });
        organizationList.appendChild(organizationSelect);
    } catch (error) {
        console.error('Error loading organ types:', error);
        alert('Failed to load organ types');
    }
}
export async function createDonationEvent(event) {
    event.preventDefault(); // Prevent the default form submission
    const donationType = document.getElementById('donationType').value;
    const eventStartTimestamp = document.getElementById('eventStartTimestamp').value;
    const eventEndTimestamp = document.getElementById('eventEndTimestamp').value;    
    const status = document.getElementById('status').value;
    const sourceOrganizationId = document.getElementById('sourceOrganizationId').value;
    const donorExternalId = document.getElementById('donorExternalId').value;

    const newEvent = {
        donation_type: donationType,
        event_start_timestamp: eventStartTimestamp,
        event_end_timestamp: eventEndTimestamp,
        status: status,
    };
    if(sourceOrganizationId)
    newEvent.source_organization_id = sourceOrganizationId;
    if(donorExternalId)
        newEvent.donor_external_id = donorExternalId;

    try {
        const response = await fetch(API_BASE_URL + 'donation-events', {
            method: 'POST',
             headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(newEvent)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to create donation event');
        }
        alert('Donation event created successfully!');

        loadDonationEvents();

    } catch (error) {
        console.error('Error creating donation event:', error);
        alert('Failed to create donation event');
    }
}

export async function loadProcuredOrgans(eventId) {
    try {
        const response = await fetch(API_BASE_URL + `donation-events/${eventId}/organs`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            }
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Failed to load procured organs');
        }
        const procuredOrgans = data;
        const contentSection = document.getElementById('main-content');

        contentSection.innerHTML = `<h2>Procured Organs for Event ${eventId}</h2>
        <button id="addProcuredOrganBtn">Add Procured Organ</button>
        <div id="procuredOrgansList"></div>`;
        const procuredOrgansList = document.getElementById('procuredOrgansList');
        const addProcuredOrganBtn = document.getElementById('addProcuredOrganBtn');
        addProcuredOrganBtn.addEventListener('click', () => addProcuredOrganForm(eventId));

        if (procuredOrgans.length == 0) {
            procuredOrgansList.innerHTML = '<p>No procured organs found for this event.</p>';
        } else {
            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Organ Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');
            procuredOrgans.forEach(organ => {
                const row = document.createElement('tr');
                row.innerHTML = ` 
                    <td>${organ.id || ''}</td>
                    <td>${organ.organ_type.name || ''}</td>
                    <td>${organ.status || ''}</td>
                    <td>            
                        <button class="updateStatusBtn" data-id="${organ.id}">Update Status</button>
                    </td>
                `;
                tbody.appendChild(row);
                const updateStatusBtn = row.querySelector('.updateStatusBtn');
                updateStatusBtn.addEventListener('click', () => updateOrganStatusForm(organ.id));
            });
            procuredOrgansList.appendChild(table);
        }
        
        
    } catch (error) {
        console.error('Error loading procured organs:', error);
        alert('Failed to load procured organs');
    }
}
async function addProcuredOrganForm(eventId) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = `
    <h2>Add Procured Organ</h2>
    <form id="addProcuredOrganForm">
    <div id="organ-type-list"></div><br>
    <label for="organExternalId">Organ External Id:</label>
      <input type="text" id="organExternalId" name="organExternalId" required><br>
      <label for="organStatus">Status:</label>
        <input type="text" id="organStatus" name="organStatus" required><br>
      <label for="organDescription">Description:</label>
        <input type="text" id="organDescription" name="organDescription" required><br>
      <button type="submit">Add Organ</button>
    </form>
  `;
    
    try {
        const organTypes = await loadData('organ-types');
        const organTypesList = document.getElementById('organ-type-list');
        const organTypeSelect = document.createElement('select');
        organTypeSelect.id = 'organTypeSelect';
        organTypeSelect.name = 'organTypeSelect';
        organTypes.forEach(organType => {
            const option = document.createElement('option');
            option.value = organType.id;
            option.text = organType.name;
            organTypeSelect.appendChild(option);
        });
        organTypesList.appendChild(organTypeSelect);
    } catch (error) {
        console.error('Error loading organ types:', error);
        alert('Failed to load organ types');
    }
    
    const form = document.getElementById('addProcuredOrganForm');
    form.addEventListener('submit', (event) => addProcuredOrgan(event, eventId));

}
export async function addProcuredOrgan(event, eventId) {
    event.preventDefault();
    const organTypeId = document.getElementById('organTypeSelect').value;  
    const status = document.getElementById('organStatus').value;
    const organExternalId = document.getElementById('organExternalId').value;
    const organDescription = document.getElementById('organDescription').value;
    const newOrgan = {
        organ_type_id: organTypeId,
        status: status,
        description: organDescription,
    const newOrgan = {
        organ_type: organType,
        status: status,
    };
    try {
        const response = await fetch(API_BASE_URL + `donation-events/${eventId}/organs`, {
            
            method: 'POST',
             headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(newOrgan)
        });

            },
            body: JSON.stringify(newOrgan)
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Failed to add procured organ');
        }
        alert('Procured organ added successfully!');
        loadProcuredOrgans(eventId);
    } catch (error) {
        console.error('Error adding procured organ:', error);
        alert('Failed to add procured organ');
    }
}

async function updateOrganStatusForm(organId) {
    const contentSection = document.getElementById('main-content');
    contentSection.innerHTML = `
      <h2>Update Organ Status</h2>
      <form id="updateOrganStatusForm">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" required><br>
        <button type="submit">Update Status</button>
      </form>
    `;
    const form = document.getElementById('updateOrganStatusForm');
    form.addEventListener('submit', (event) => updateOrganStatus(event, organId));
}

export async function updateOrganStatus(event, organId) {
    event.preventDefault();
    const status = document.getElementById('status').value;

    const newStatus = {
        new_status: status
    };
    try {
        const response = await fetch(API_BASE_URL + `procured-organs/${organId}/status-log`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(newStatus)
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to update organ status');
        }
        alert('Organ status updated successfully!');
        const procuredOrgan = await loadData(`procured-organs/${organId}`);
        loadProcuredOrgans(procuredOrgan.donation_event_id);

    } catch (error) {
        console.error('Error updating organ status:', error);
        alert('Failed to update organ status');
    }
}