import { createFilterSection } from './filters.js';
import { createSortSection } from './sorting.js';
import { createPaginationSection } from './pagination.js';

export async function loadSection(section, filters = {}, sort = null, direction = null, page = null, perPage = null) {
    const API_BASE_URL = window.API_BASE_URL;
    const contentSection = document.getElementById('content');
    contentSection.innerHTML = ''; // Clear previous content
    try {
        let url = `${API_BASE_URL}${section}`;
        const queryParams = [];

        // Add filters
        for (const key in filters) {
            queryParams.push(`filter[${key}]=${filters[key]}`);
        }

        // Add sort
        if (sort) {
            queryParams.push(`sort=${sort}`);
        }
        // Add direction
        if (direction) {
            queryParams.push(`direction=${direction}`)
        }
        // Add pagination
        if (page) {
            queryParams.push(`page=${page}`);
        }
        if (perPage) {
            queryParams.push(`per_page=${perPage}`);
        }

        if (queryParams.length > 0) {
            url += `?${queryParams.join('&')}`;
        }
        const token = localStorage.getItem('token');
        const response = await fetch(url, {
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
            const data = await response.json();
            contentSection.appendChild(createTable(section, data[section.replace(/-/g, '_')], data.total_count, filters, sort, direction, page, perPage));
            if (isAdmin && section == 'users'){
                addAdminButtonsToUsers(section);
            }
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        contentSection.innerHTML = '<p>Error loading data.</p>';
    }
}

function createTable(section, data, totalCount, filters, sort, direction, page, perPage) {
    const tableContainer = document.createElement('div');

    //Create Button
    const createButton = document.createElement('button');
    createButton.textContent = `Create ${section.replace(/-/g, ' ').toUpperCase()}`;
    createButton.classList.add('create-button');
    createButton.addEventListener('click', () => showCreateModal(section));
    tableContainer.appendChild(createButton);

    //Filters
    const filterSection = createFilterSection(section, filters);
    tableContainer.appendChild(filterSection);

    // Sort
    const sortSection = createSortSection(section, sort, direction);
    tableContainer.appendChild(sortSection);

    // Table
    const table = document.createElement('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');

    const firstItem = data.length > 0 ? data[0] : {};

    if (section === 'users' || section === 'organizations' || section === 'donation-events' || section === 'procured-organs' || section === 'medical-markers' || section === 'organ-transplants') {
        Object.keys(firstItem).forEach(key => {
            if (key != 'password') {
                const th = document.createElement('th');
                th.textContent = key.replace(/_/g, ' ').toUpperCase();
                th.style.border = '1px solid #ddd';
                th.style.padding = '8px';
                th.style.textAlign = 'left';
                headerRow.appendChild(th);
            }
        });
        //Add headers for view, edit and delete
        const thView = document.createElement('th');
        thView.textContent = "VIEW";
        headerRow.appendChild(thView);
        const thEdit = document.createElement('th');
        thEdit.textContent = "EDIT";
        headerRow.appendChild(thEdit);
        const thDelete = document.createElement('th');
        thDelete.textContent = "DELETE";
        headerRow.appendChild(thDelete);
    }

    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    if (section === 'users' || section === 'organizations' || section === 'donation-events' || section === 'procured-organs' || section === 'medical-markers' || section === 'organ-transplants') {
        data.forEach(item => {
            const row = document.createElement('tr');
            Object.keys(item).forEach(key => {
                if (key != 'password') {
                    const td = document.createElement('td');
                    td.textContent = item[key];
                    td.style.border = '1px solid #ddd';
                    td.style.padding = '8px';
                    row.appendChild(td);
                }
            });
            //Add buttons for view, edit and delete
            const tdView = document.createElement('td');
            const viewButton = document.createElement('button');
            viewButton.classList.add('view-details');
            viewButton.textContent = "View";
            viewButton.addEventListener('click', () => showDetails(section, item.id));
            tdView.appendChild(viewButton);
            row.appendChild(tdView);
            const tdEdit = document.createElement('td');
            const editButton = document.createElement('button');
            editButton.classList.add('edit-button');
            editButton.textContent = "Edit";
            editButton.addEventListener('click', () => showEditModal(section, item.id));
            tdEdit.appendChild(editButton);
            row.appendChild(tdEdit);
            const tdDelete = document.createElement('td');
            const deleteButton = document.createElement('button');
            deleteButton.classList.add('delete-button');
            deleteButton.textContent = "Delete";
            deleteButton.addEventListener('click', () => showDeleteModal(section, item.id));
            tdDelete.appendChild(deleteButton);
            row.appendChild(tdDelete);

            tbody.appendChild(row);
        });
    }
    table.appendChild(tbody);
    tableContainer.appendChild(table);

    // Pagination
    const paginationSection = createPaginationSection(section, totalCount, filters, sort, direction, page, perPage);
    tableContainer.appendChild(paginationSection);

    return tableContainer;
}

export async function showDetails(section, id) {
    const API_BASE_URL = window.API_BASE_URL;
    const contentSection = document.getElementById('content');
    contentSection.innerHTML = '';

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
            const detailsDiv = document.createElement('div');

            const backButton = document.createElement('button');
            backButton.textContent = 'Back';
            backButton.addEventListener('click', () => {
                loadSection(section);
            });
            detailsDiv.appendChild(backButton);

            for (const key in item) {
                if (key != 'password') {
                    const p = document.createElement('p');
                    p.innerHTML = `<b>${key.replace(/_/g, ' ').toUpperCase()}:</b> ${item[key]}`;
                    detailsDiv.appendChild(p);
                }
            }
            contentSection.appendChild(detailsDiv);
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        contentSection.innerHTML = '<p>Error loading data.</p>';
    }
}