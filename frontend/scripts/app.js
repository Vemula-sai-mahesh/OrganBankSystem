import { login, logout, register, getUserRole } from "./auth.js";
import { loadProfile } from "../donor/donor.js";
import { loadDonationEvents, loadProcuredOrgans } from "../opo/opo.js";
import { searchOrgans, loadOrganDetails, logTransplantOutcome } from "../transplant/transplant.js";
import { loadOrganizations, loadAnalytics, loadAuditLogs, loadApiKeys } from "../admin/admin.js";

const config = {
  API_BASE_URL: "http://localhost/OrganBankSystem/backend/api/",
  INDEX_PAGE: "index.html",
  LOGIN_PAGE: "login.html",
  REGISTER_PAGE: "register.html",
};

const menuItems = {
  "System Administrator": [
    { label: "Organizations", href: "organizations.html" },
    { label: "Analytics", href: "analytics.html" },
    { label: "Audit Logs", href: "audit-logs.html" },
    { label: "API Keys", href: "api-keys.html" },
    { label: "Profile", href: "profile.html" },
    { label: "Users", href: "users.html" },
  ],
  "OPO Coordinator": [
    { label: "Donation Events", href: "donation-events.html" },
    { label: "Profile", href: "profile.html" },
  ],
  "Transplant Coordinator": [
    { label: "Search Organs", href: "search-organs.html" },
    { label: "Profile", href: "profile.html" },
  ],
  Donor: [{ label: "Profile", href: "profile.html" }],
  "Organization Admin": [{ label: "Users", href: "users.html" }, { label: "Profile", href: "profile.html" }],
  null: [{ label: "Home", href: config.INDEX_PAGE }],
};

// Function to load the menu
function loadMenu() {
  const userRole = getUserRole(); // Get the user role
  const nav = document.querySelector("nav");
  nav.innerHTML = ""; // Clear existing menu

  // Get the menu items for the user's role, or default to null role if not found
  const currentMenuItems = menuItems[userRole] || menuItems[null];

  // Generate the menu
  const menuHTML = currentMenuItems.map((item) => `<li><a href="${item.href}">${item.label}</a></li>`).join("");
  nav.innerHTML = `<ul>${menuHTML}</ul>`;

  // Add event listeners to the new navigation links
  const navLinks = document.querySelectorAll("#main-nav a");
  navLinks.forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      loadPage(link.getAttribute("href"));
    });
  });
}

// Function to load a page
async function loadPage(path) {
  const userRole = getUserRole(); // Get the user role
  const mainContent = document.getElementById("main-content");
  if (!mainContent) {
    console.error("Main content area not found");
    return;
  }
  try {    
    const response = await fetch(path);
    if (!response.ok) {
      throw new Error(`Failed to load page: ${path}`);
    }
    const html = await response.text();
    mainContent.innerHTML = html;
    
    switch(path){
      case "profile.html": loadProfile(); break;
      case "donation-events.html": loadDonationEvents(); break;
      case "procured-organs.html": loadProcuredOrgans(window.eventId); break;
      case "search-organs.html": searchOrgans(); break;
      case "organ-details.html": loadOrganDetails(window.organId); break;
      case "transplants.html": logTransplantOutcome(window.organId); break;
      case "organizations.html": loadOrganizations(); break;
      case "analytics.html": loadAnalytics(); break;
      case "audit-logs.html": loadAuditLogs(); break;
      case "api-keys.html": loadApiKeys(); break;
      case "users.html": loadOrganizations(); break;
    }

    if (userRole && path !== config.INDEX_PAGE && path !== config.LOGIN_PAGE && path !== config.REGISTER_PAGE) {
      loadMenu();
    } else {
      loadMenu();
    }
  } catch (error) {
    console.error("Error loading page:", error);
    mainContent.innerHTML = "<p>Error loading page</p>";
  }
}

// Function to set up event listeners
function setupEventListeners() {
  const mainContent = document.getElementById("main-content");

  // Event listener for logout button
  const logoutButton = document.getElementById("logoutButton");
  if(logoutButton) logoutButton.addEventListener("click", logout);

  // Event listener for login form
  mainContent.addEventListener("submit", function (event) {
    const loginForm = event.target.closest("form#loginForm");
    if (loginForm) {
      event.preventDefault();
      const email = loginForm.querySelector("#loginEmail").value;
      const password = loginForm.querySelector("#loginPassword").value;
      login(email, password);
    }
  });

  // Event listener for register form
  mainContent.addEventListener("submit", function (event) {
    const registerForm = event.target.closest("form#registerForm");
    if (registerForm) {
      event.preventDefault();
      const firstName = registerForm.querySelector("#registerFirstName").value;
      const lastName = registerForm.querySelector("#registerLastName").value;
      const email = registerForm.querySelector("#registerEmail").value;
      const password = registerForm.querySelector("#registerPassword").value;
      register(firstName, lastName, email, password);
    }
  });
}

// Initialize the application
window.loadMenu = loadMenu;
window.addEventListener("DOMContentLoaded", () => {
  const path = window.location.pathname.split("/").pop() || config.INDEX_PAGE;
  loadPage(path);
  setupEventListeners();
});