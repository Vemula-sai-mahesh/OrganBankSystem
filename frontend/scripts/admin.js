export function addAdminButtonsToUsers(){
    const table = document.querySelector('table tbody');
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const lastCell = cells[cells.length - 1];
        const button = document.createElement('button');
        button.textContent = 'Set Admin';
        button.classList.add('set-admin-button');
        const userId = cells[0].textContent; // User ID is in the first cell
        button.addEventListener('click', () => setAdmin(userId));
        lastCell.parentNode.insertBefore(button, lastCell);
    });
}

export async function setAdmin(user_id){
    const API_BASE_URL = window.API_BASE_URL;
    try {
        const response = await fetch(`${API_BASE_URL}users/${user_id}/set-admin`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.ok) {
            alert('User set as admin successfully.');
            loadSection('users');
        } else {
            const errorData = await response.json();
            alert(`Error setting user as admin: ${errorData.message}`);
        }
    } catch (error) {
        console.error('Error setting user as admin:', error);
        alert('Error setting user as admin.');
    }
}