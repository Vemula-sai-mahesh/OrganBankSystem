import { loadPage, getMedicalMarkerValues, getOrganTypes } from "../data/data.js";

export function setupTransplantPage() {
    const searchButton = document.getElementById('searchOrgansButton');
    if (searchButton) {
        searchButton.addEventListener('click', handleSearchOrgans);
    }
}
export async function setupTransplantSearch() {
    await loadOrganTypes();
    await loadBloodTypes();
}
async function loadBloodTypes() {
    const bloodTypes = await getMedicalMarkerValues("Blood Type");
    const bloodTypeSelect = document.getElementById("bloodType");
    bloodTypes.forEach(type => {
        const option = document.createElement("option");
        option.value = type.id;
        option.text = type.value;
        bloodTypeSelect.appendChild(option);
    });
}
async function loadOrganTypes() {
    const organTypes = await getOrganTypes();
    const organTypeSelect = document.getElementById("organType");
    organTypes.forEach(type => {
        const option = document.createElement("option");
        option.value = type.id;
        option.text = type.name;
        organTypeSelect.appendChild(option);
    });
}

async function handleSearchOrgans() {
    const searchCriteria = {
        organ_type_id: document.getElementById('organType').value,
        blood_type_id: document.getElementById('bloodType').value,
    };
    const searchResults = await searchOrgans(searchCriteria);
    displaySearchResults(searchResults);
}
function displaySearchResults(organs) {
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = ''; 

    if (organs.length === 0) {
        resultsContainer.textContent = 'No organs found matching the criteria.';
        return;
    }
    const ul = document.createElement('ul');
    organs.forEach(organ => {
        const li = document.createElement('li');
        li.textContent = `Organ ID: ${organ.id}, Type: ${organ.organ_type.name}, Blood Type: ${organ.blood_type}, Current Organization: ${organ.current_organization.name}`;
        li.addEventListener('click', () => loadOrganDetailsPage(organ.id));
        ul.appendChild(li);
    });
    resultsContainer.appendChild(ul);
}

export async function searchOrgans(criteria) {  
    try {
        const queryParams = new URLSearchParams();
        for (const key in criteria) {
            if (criteria[key]) {
                queryParams.append(key, criteria[key]);
            }
        }
        const response = await fetch(`${window.API_BASE_URL}procured-organs?${queryParams.toString()}`, {
            method: 'GET',
             headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error searching for organs:', error);
        alert('Failed to search for organs. Check console for details.');
        return null;
    }
}

function loadOrganDetailsPage(organId) {
    loadPage('transplant/organ-details.html');
    setTimeout(() => {
        loadOrganDetails(organId);
    }, 500);
}

export async function loadOrganDetails(organId) {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(`${API_BASE_URL}procured-organs/${organId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        const detailsContainer = document.getElementById('organDetails');
        detailsContainer.innerHTML = '';

        if (data) {
            const ul = document.createElement("ul");
            const fieldsToShow = ["id","organ_type", "donation_event","current_organization", "organ_external_id", "procurement_timestamp","preservation_timestamp", "estimated_warm_ischemia_time_minutes",
                "estimated_cold_ischemia_time_minutes", "expiry_timestamp","status", "description","blood_type","clinical_notes","packaging_details"];
            fieldsToShow.forEach(key => {
                    if(data[key]){
                        const li = document.createElement("li");
                        if(typeof data[key] === 'object'){
                            li.textContent = `${key}: ${data[key].name || data[key].id }`;
                        }else{
                            li.textContent = `${key}: ${data[key]}`;
                        }
                        ul.appendChild(li);
                    }
                });

            detailsContainer.appendChild(ul);
        } else {
            detailsContainer.textContent = 'Organ details not found.';
        }

    } catch (error) {
        console.error('Error loading organ details:', error);
        alert('Failed to load organ details. Check console for details.');
        return null;
    }
}
export function setupTransplantLogPage() {
    const logTransplantButton = document.getElementById('logTransplantOutcomeButton');
    if (logTransplantButton) {
        logTransplantButton.addEventListener('click', handleLogTransplant);
    }
}
  
async function handleLogTransplant(){
    const organId = document.getElementById('organId').value; 
    const outcome = document.getElementById('outcome').value;
    logTransplantOutcome(organId, { outcome });
}
export async function logTransplantOutcome(organId, outcomeData) {
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(`${API_BASE_URL}transplants`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ ...outcomeData, procured_organ_id: organId })
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        alert('Transplant outcome logged successfully.');
        return data;
    } catch (error) {
        console.error('Error logging transplant outcome:', error);
        alert('Failed to log transplant outcome. Check console for details.');
        return null;
    }
}