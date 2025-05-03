const API_BASE_URL = 'http://localhost/OrganBankSystem/backend/api/';

let isLoggedIn = false;
let token = null;
let user_id = null;
let isAdmin = false;

const loginButton = document.getElementById('loginButton');
const logoutButton = document.getElementById('logoutButton');

loginButton.addEventListener('click', showLoginModal);
logoutButton.addEventListener('click', logout);

function showLoginModal() {
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

async function login(email, password, modal) {
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
        loginButton.style.display = 'none';
        logoutButton.style.display = 'inline-block';
        if(isAdmin){
            const userButton = document.querySelector('nav ul li:first-child a');
            userButton.textContent = 'Users (ADMIN)';
        }
    } else {
        alert('Login failed: ' + data.message);
    }
}

async function logout() {
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

async function loadSection(section, filters = {}, sort = null, direction = null, page = null, perPage = null) {
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

function createFilterSection(section, filters) {
    const filterSection = document.createElement('div');
    filterSection.classList.add('filters');

    // Add a filter input for each filterable field in the section
    let filterableFields = [];
    switch (section) {
        case 'users':
            filterableFields = ['id', 'name', 'email'];
            break;
        case 'organizations':
            filterableFields = ['id', 'name', 'type', 'email', 'website_url', 'phone'];
            break;
        case 'donation-events':
            filterableFields = ['id', 'donation_type', 'donor_external_id', 'status', 'cause_of_death', 'clinical_summary', 'notes'];
            break;
        case 'procured-organs':
            filterableFields = ['id', 'organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'packaging_details'];
            break;
        case 'medical-markers':
            filterableFields = ['id', 'marker_type', 'marker_value', 'notes'];
            break;
        case 'organ-transplants':
            filterableFields = ['id', 'transplant_timestamp', 'post_transplant_status', 'notes'];
            break;
    }

    filterableFields.forEach(field => {
        const filterLabel = document.createElement('label');
        filterLabel.textContent = `${field.replace(/_/g, ' ').toUpperCase()}:`;
        const filterInput = document.createElement('input');
        filterInput.type = 'text';
        filterInput.id = `filter-${section}-${field}`;
        filterInput.value = filters[field] || ''; // Pre-fill with current filter value
        filterSection.appendChild(filterLabel);
        filterSection.appendChild(filterInput);
    });

    const filterButton = document.createElement('button');
    filterButton.textContent = 'Filter';
    filterButton.addEventListener('click', () => {
        const newFilters = {};
        filterableFields.forEach(field => {
            const filterValue = document.getElementById(`filter-${section}-${field}`).value;
            if (filterValue) {
                newFilters[field] = filterValue;
            }
        });
        loadSection(section, newFilters);
    });
    filterSection.appendChild(filterButton);

    return filterSection;
}

function createSortSection(section, sort, direction) {
    const sortSection = document.createElement('div');
    sortSection.classList.add('sort');

    let sortableFields = [];
    switch (section) {
        case 'users':
            sortableFields = ['id', 'name', 'email'];
            break;
        case 'organizations':
            sortableFields = ['id', 'name', 'type', 'email', 'website_url', 'phone'];
            break;
        case 'donation-events':
            sortableFields = ['id', 'donation_type', 'donor_external_id', 'status', 'cause_of_death'];
            break;
        case 'procured-organs':
            sortableFields = ['id', 'organ_external_id', 'status', 'blood_type'];
            break;
        case 'medical-markers':
            sortableFields = ['id', 'marker_type', 'marker_value'];
            break;
        case 'organ-transplants':
            sortableFields = ['id', 'transplant_timestamp', 'post_transplant_status'];
            break;
    }

    if (sortableFields.length > 0) {
        const sortLabel = document.createElement('label');
        sortLabel.textContent = 'Sort By:';
        sortSection.appendChild(sortLabel);
        const sortSelect = document.createElement('select');
        sortSelect.id = `sort-${section}`;
        sortableFields.forEach(field => {
            const option = document.createElement('option');
            option.value = field;
            option.textContent = field.replace(/_/g, ' ').toUpperCase();
            sortSelect.appendChild(option);
        });
        sortSelect.value = sort || sortableFields[0]; // Set default value or current sort
        sortSection.appendChild(sortSelect);

        // Add direction
        const directionLabel = document.createElement('label');
        directionLabel.textContent = 'Direction:';
        sortSection.appendChild(directionLabel);

        const directionSelect = document.createElement('select');
        directionSelect.id = `direction-${section}`;
        const ascOption = document.createElement('option');
        ascOption.value = 'asc';
        ascOption.textContent = 'Ascending';
        const descOption = document.createElement('option');
        descOption.value = 'desc';
        descOption.textContent = 'Descending';
        directionSelect.appendChild(ascOption);
        directionSelect.appendChild(descOption);
        directionSelect.value = direction || 'asc'; // Set default value or current direction
        sortSection.appendChild(directionSelect);

        const sortButton = document.createElement('button');
        sortButton.textContent = 'Sort';
        sortButton.addEventListener('click', () => {
            const newSort = document.getElementById(`sort-${section}`).value;
            const newDirection = document.getElementById(`direction-${section}`).value;
            const filters = getFiltersFromSection(section);
            loadSection(section, filters, newSort, newDirection);
        });
        sortSection.appendChild(sortButton);
    }

    return sortSection;
}

function getFiltersFromSection(section) {
    const filters = {};
    let filterableFields = [];
    switch (section) {
        case 'users':
            filterableFields = ['id', 'name', 'email'];
            break;
        case 'organizations':
            filterableFields = ['id', 'name', 'type', 'email', 'website_url', 'phone'];
            break;
        case 'donation-events':
            filterableFields = ['id', 'donation_type', 'donor_external_id', 'status', 'cause_of_death', 'clinical_summary', 'notes'];
            break;
        case 'procured-organs':
            filterableFields = ['id', 'organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'packaging_details'];
            break;
        case 'medical-markers':
            filterableFields = ['id', 'marker_type', 'marker_value', 'notes'];
            break;
        case 'organ-transplants':
            filterableFields = ['id', 'transplant_timestamp', 'post_transplant_status', 'notes'];
            break;
    }
    filterableFields.forEach(field => {
        const filterValue = document.getElementById(`filter-${section}-${field}`).value;
        if (filterValue) {
            filters[field] = filterValue;
        }
    });
    return filters;
}

function createPaginationSection(section, totalCount, filters, sort, direction, currentPage, perPage) {
    const paginationSection = document.createElement('div');
    paginationSection.classList.add('pagination');

    // Default values
    const defaultPerPage = 10;
    perPage = perPage || defaultPerPage;
    currentPage = currentPage || 1;

    const totalPages = Math.ceil(totalCount / perPage);

    // Previous button
    if (currentPage > 1) {
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Previous';
        prevButton.addEventListener('click', () => {
            loadSection(section, filters, sort, direction, currentPage - 1, perPage);
        });
        paginationSection.appendChild(prevButton);
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement('button');
        pageButton.textContent = i;
        if (i === currentPage) {
            pageButton.disabled = true; // Disable the current page button
        }
        pageButton.addEventListener('click', () => {
            loadSection(section, filters, sort, direction, i, perPage);
        });
        paginationSection.appendChild(pageButton);
    }

    // Next button
    if (currentPage < totalPages) {
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Next';
        nextButton.addEventListener('click', () => {
            loadSection(section, filters, sort, direction, currentPage + 1, perPage);
        });
        paginationSection.appendChild(nextButton);
    }

    // Per page selector
    const perPageLabel = document.createElement('label');
    perPageLabel.textContent = 'Items per page:';
    paginationSection.appendChild(perPageLabel);
    const perPageSelect = document.createElement('select');
    perPageSelect.id = `per-page-${section}`;
    [10, 20, 50].forEach(num => {
        const option = document.createElement('option');
        option.value = num;
        option.textContent = num;
        perPageSelect.appendChild(option);
    });
    perPageSelect.value = perPage;
    perPageSelect.addEventListener('change', () => {
        const newPerPage = Number(perPageSelect.value);
        loadSection(section, filters, sort, direction, 1, newPerPage);
    });
    paginationSection.appendChild(perPageSelect);

    return paginationSection;
}

async function showDetails(section, id) {
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

function showCreateModal(section) {
    const modal = document.createElement('div');
    modal.classList.add('form-modal');

    const form = createForm(section, modal);
    modal.appendChild(form);

    document.body.appendChild(modal);
}

function createForm(section, modal, item = null) {
    const form = document.createElement('form');
    form.id = `${section}-form`;

    let formFields = [];
    switch (section) {
        case 'users':
            formFields = ['name', 'email', 'password'];
            break;
        case 'organizations':
            formFields = ['name', 'type', 'email', 'website_url', 'phone'];
            break;
        case 'donation-events':
            formFields = ['donation_type', 'donor_external_id', 'status', 'cause_of_death', 'clinical_summary', 'notes'];
            break;
        case 'procured-organs':
            formFields = ['organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'packaging_details'];
            break;
        case 'medical-markers':
            formFields = ['marker_type', 'marker_value', 'notes'];
            break;
        case 'organ-transplants':
            formFields = ['transplant_timestamp', 'post_transplant_status', 'notes'];
            break;
    }

    formFields.forEach(field => {
        const label = document.createElement('label');
        label.textContent = `${field.replace(/_/g, ' ').toUpperCase()}:`;
        form.appendChild(label);
        if (field === 'clinical_summary' || field === 'notes' || field == 'description') {
            const textarea = document.createElement('textarea');
            textarea.id = `${section}-${field}`;
            textarea.value = item ? item[field] || '' : '';
            form.appendChild(textarea);
        } else {
            const input = document.createElement('input');
            input.type = field === 'email' ? 'email' : field === 'password' ? 'password' : 'text';
            input.id = `${section}-${field}`;
            input.value = item ? item[field] || '' : '';
            form.appendChild(input);
        }
    });

    const submitButton = document.createElement('button');
    submitButton.type = 'button';
    submitButton.textContent = item ? 'Update' : 'Create';
    submitButton.addEventListener('click', () => {
        if(item){
            updateItem(section, item.id, modal);
        }else{
            createItem(section, modal);
        }
    });
    form.appendChild(submitButton);

    const closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.addEventListener('click', () => {
        modal.remove();
    });
    form.appendChild(closeButton);

    return form;
}

async function createItem(section, modal) {
    const formData = {};
    let formFields = [];
    switch (section) {
        case 'users':
            formFields = ['name', 'email', 'password'];
            break;
        case 'organizations':
            formFields = ['name', 'type', 'email', 'website_url', 'phone'];
            break;
        case 'donation-events':
            formFields = ['donation_type', 'donor_external_id', 'status', 'cause_of_death', 'clinical_summary', 'notes'];
            break;
        case 'procured-organs':
            formFields = ['organ_external_id', 'status', 'description', 'blood_type', 'clinical_notes', 'packaging_details'];
            break;
        case 'medical-markers':
            formFields = ['marker_type', 'marker_value', 'notes'];
            break;
        case 'organ-transplants':
            formFields = ['transplant_timestamp', 'post_transplant_status', 'notes'];
            break;
    }

    formFields.forEach(field => {
        formData[field] = document.getElementById(`${section}-${field}`).value;
    });

    try {
        const response = await fetch(`${API_BASE_URL}${section}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            modal.remove();
            loadSection(section);
        } else {
            const errorData = await response.json();
            alert(`Error creating ${section.replace(/-/g, ' ')}: ${errorData.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('Error creating item:', error);
        alert(`Error creating ${section.replace(/-/g, ' ')}: Unknown error`);
    }
}

async function showEditModal(section, id) {
    try {
        const response = await fetch(`${API_BASE_URL}${section}/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token
