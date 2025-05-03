class Data {
    constructor() {
        this.baseUrl = 'https://api.organbank.org/v1/';
        this.token = localStorage.getItem('token');
        this.headers = {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.token}`
        };
    }

    async registerUser(userData) {
        const response = await fetch(`${this.baseUrl}users`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(userData)
        });
        return await this.handleResponse(response);
    }

    async login(email, password) {
        const response = await fetch(`${this.baseUrl}auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await this.handleResponse(response);
        if (data.token) {
            localStorage.setItem('token', data.token);
            this.token = data.token;
            this.headers['Authorization'] = `Bearer ${this.token}`;
        }
        return data;
    }

    async logout() {
        localStorage.removeItem('token');
        this.token = null;
        this.headers['Authorization'] = null;
        return { success: true };
    }

    async getUserProfile(userId) {
        const response = await fetch(`${this.baseUrl}users/${userId}`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async updateUserProfile(userId, userData) {
        const response = await fetch(`${this.baseUrl}users/${userId}`, {
            method: 'PUT',
            headers: this.headers,
            body: JSON.stringify(userData)
        });
        return await this.handleResponse(response);
    }

    async getPlatformIntent(userId) {
        const response = await fetch(`${this.baseUrl}users/${userId}/platform-intent`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async updatePlatformIntent(userId, intentData) {
        const response = await fetch(`${this.baseUrl}users/${userId}/platform-intent`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(intentData)
        });
        return await this.handleResponse(response);
    }

    async getDonationEvents() {
        const response = await fetch(`${this.baseUrl}donation-events`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async createDonationEvent(eventData) {
        const response = await fetch(`${this.baseUrl}donation-events`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(eventData)
        });
        return await this.handleResponse(response);
    }

    async getProcuredOrgans(eventId = null) {
        let url = `${this.baseUrl}procured-organs`;
        if (eventId) {
            url = `${this.baseUrl}donation-events/${eventId}/organs`;
        }
        const response = await fetch(url, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async addProcuredOrgan(eventId, organData) {
        const response = await fetch(`${this.baseUrl}donation-events/${eventId}/organs`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(organData)
        });
        return await this.handleResponse(response);
    }

    async updateOrganStatus(organId, statusData) {
        const response = await fetch(`${this.baseUrl}procured-organs/${organId}/status-log`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(statusData)
        });
        return await this.handleResponse(response);
    }

    async searchOrgans(criteria = {}) {
        const queryParams = new URLSearchParams(criteria);
        const response = await fetch(`${this.baseUrl}procured-organs?${queryParams}`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async getOrganDetails(organId) {
        const response = await fetch(`${this.baseUrl}procured-organs/${organId}`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async logTransplantOutcome(transplantData) {
        const response = await fetch(`${this.baseUrl}transplants`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(transplantData)
        });
        return await this.handleResponse(response);
    }

    async getOrganizations() {
        const response = await fetch(`${this.baseUrl}organizations`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async createOrganization(organizationData) {
        const response = await fetch(`${this.baseUrl}organizations`, {
            method: 'POST',
            headers: this.headers,
            body: JSON.stringify(organizationData)
        });
        return await this.handleResponse(response);
    }

    async getAnalytics() {
        const response = await fetch(`${this.baseUrl}admin/analytics`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async getAuditLogs() {
        const response = await fetch(`${this.baseUrl}admin/audit-log`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async generatePdf(type, id) {
        const response = await fetch(`${this.baseUrl}utilities/pdf/${type}/${id}`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }
    
    async getOrganTypes() {
        const response = await fetch(`${this.baseUrl}organ-types`, {
            method: 'GET',
            headers: this.headers
        });
        return await this.handleResponse(response);
    }

    async getMedicalMarkerTypes() {
      const response = await fetch(`${this.baseUrl}medical-marker-types`, {
          method: 'GET',
          headers: this.headers
      });
      return await this.handleResponse(response);
  }
  
  async getMedicalMarkerValues() {
      const response = await fetch(`${this.baseUrl}medical-marker-values`, {
          method: 'GET',
          headers: this.headers
      });
      return await this.handleResponse(response);
  }
  
  async getApiKeys() {
      const response = await fetch(`${this.baseUrl}api-keys`, {
          method: 'GET',
          headers: this.headers
      });
      return await this.handleResponse(response);
  }
  
  async createApiKey(apiKeyData) {
      const response = await fetch(`${this.baseUrl}api-keys`, {
          method: 'POST',
          headers: this.headers,
          body: JSON.stringify(apiKeyData)
      });
      return await this.handleResponse(response);
  }
  
  async deleteApiKey(apiKeyId) {
      const response = await fetch(`${this.baseUrl}api-keys/${apiKeyId}`, {
          method: 'DELETE',
          headers: this.headers
      });
      return await this.handleResponse(response);
  }
  async handleResponse(response) {
        if (response.ok) {
            if (response.headers.get('Content-Length') === '0') {
                return { success: true };
            }
            return await response.json();
        } else {
            const error = await response.json();
            throw new Error(error.message || `Request failed with status ${response.status}`);
        }
    }
}

const data = new Data();