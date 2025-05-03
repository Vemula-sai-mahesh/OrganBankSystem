// Import all the js files
import { login, logout, showLoginModal } from './auth.js';
import { loadSection, showDetails } from './data.js';
import { createFilterSection, getFiltersFromSection } from './filters.js';
import { createSortSection } from './sorting.js';
import { createPaginationSection } from './pagination.js';
import { showCreateModal, createItem, showEditModal, updateItem, showDeleteModal } from './crud.js';
import { addAdminButtonsToUsers, setAdmin } from './admin.js';

const API_BASE_URL = 'http://localhost/OrganBankSystem/backend/api/';

window.API_BASE_URL = API_BASE_URL;
window.loadSection = loadSection;

const loginButton = document.getElementById('loginButton');
const logoutButton = document.getElementById('logoutButton');

loginButton.addEventListener('click', showLoginModal);
logoutButton.addEventListener('click', logout);