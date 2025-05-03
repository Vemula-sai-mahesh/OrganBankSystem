export function createPaginationSection(section, totalCount, filters, sort, direction, currentPage, perPage) {
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