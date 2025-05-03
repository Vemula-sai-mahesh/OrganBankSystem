export function createSortSection(section, sort, direction) {
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