import { login, logout, getUserRole } from "./auth.js";
import { loadProfile } from "../donor/donor.js";
import { loadDonationEvents, loadProcuredOrgans } from "../opo/opo.js";
import { searchOrgans, loadOrganDetails, logTransplantOutcome } from "../transplant/transplant.js";
import { loadOrganizations, loadAnalytics, loadAuditLogs, loadApiKeys, loadUsers } from "../admin/admin.js";

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
      case "users.html": loadUsers(); break;
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

// Function to set up global event listeners
function setupEventListeners() {
  const mainContent = document.getElementById("main-content");

  // Event listener for logout button
  const logoutButton = document.getElementById("logoutButton");
  if(logoutButton) logoutButton.addEventListener("click", logout);

  // Event listener for login form
  mainContent.addEventListener("submit", async function (event) {
    const loginForm = event.target.closest("form#loginForm");
    if (loginForm) {
      event.preventDefault();
      // Get the form data.
      const formData = new FormData(loginForm);
      const data = Object.fromEntries(formData.entries());
      await login(data.email, data.password);
    }
    
  });

  // Event listener for register form
  mainContent.addEventListener("submit", function (event) {
    const registerForm = event.target.closest("form#registerForm");
    if (registerForm) {
      event.preventDefault();
     await handleRegistration(registerForm);
    }
  });
}

// Function to get all organizations
async function getOrganizations() {
  try {
    const response = await fetch(config.API_BASE_URL + "organizations");
    const organizations = await response.json();
    return organizations;
  } catch (error) {
    console.error("Error fetching organizations:", error);
    return [];
  }
}

// Function to get all roles
async function getRoles() {
  // For simplicity, we'll hardcode roles here. In a real application, you'd fetch these from the backend.
  return ["System Administrator", "OPO Coordinator", "Transplant Coordinator", "Donor", "Organization Admin"];
}

// Function to handle user registration
async function handleRegistration(registerForm) {
  // Get the form data
  const formData = new FormData(registerForm);
  const data = Object.fromEntries(formData.entries());
  
  // Check if the user is a donor
  if (data.isDonor === "on") {
    data.organization_id = "None";
    data.role_name = "Donor";
  }
  
  // Remove the isDonor flag
  delete data.isDonor;
  
  try {
      // Send the data to the backend
      const response = await fetch(config.API_BASE_URL + "users", {
          method: "POST",
          headers: {
              "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
      });

      // Check if the response is ok
      if (response.ok) {
          alert("Registration successful!");
           // Redirect to the login page.
           loadPage(config.LOGIN_PAGE);
      } else {
          const errorData = await response.json();
          alert("Registration failed: " + errorData.error);
      }
  } catch (error) {
      console.error("Error during registration:", error);
      alert("An error occurred during registration.");
  }

}

// Initialize the application
window.loadMenu = loadMenu;
window.addEventListener("DOMContentLoaded", () => {
  const path = window.location.pathname.split("/").pop() || config.INDEX_PAGE;
  loadPage(path);
  setupEventListeners();
});

// Function to populate organizations dropdown
async function populateOrganizations() {
  const organizations = await getOrganizations();
  const organizationSelect = document.getElementById("organization_id");

  // Clear existing options
  organizationSelect.innerHTML = "";

  // Add default "None" option
  const noneOption = document.createElement("option");
  noneOption.value = "None";
  noneOption.text = "None";
  organizationSelect.appendChild(noneOption);

  // Add fetched organizations
  organizations.forEach(org => {
    const option = document.createElement("option");
    option.value = org.id;
    option.text = org.name;
    organizationSelect.appendChild(option);
  });
}

// Function to populate roles dropdown
async function populateRoles() {
  const roles = await getRoles();
  const roleSelect = document.getElementById("role_name");

  // Clear existing options
  roleSelect.innerHTML = "";

  // Add fetched roles
  roles.forEach(role => {
    const option = document.createElement("option");
    option.value = role;
    option.text = role;
    roleSelect.appendChild(option);
  });
}

// Call the function to populate the organizations dropdown
window.populateOrganizations = populateOrganizations;
window.populateRoles = populateRoles;