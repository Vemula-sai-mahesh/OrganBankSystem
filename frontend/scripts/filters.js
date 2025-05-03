export function createFilterSection(section, filters) {
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

export function getFiltersFromSection(section) {
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