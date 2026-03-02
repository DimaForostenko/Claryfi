<template>
  <div id="app" class="container">
    <div class="calculator-card">
      <h1 class="title">
        <i class="icon">📦</i>
        Shipping Cost Calculator
      </h1>
      
      <div class="card-content">
        <!-- Carrier Selection -->
        <div class="form-group">
          <label for="carrier" class="form-label">
            Shipping Carrier
            <span class="required">*</span>
          </label>
          <select 
            id="carrier"
            v-model="formData.carrier" 
            class="form-control"
            :class="{ 'error': errors.carrier }"
            @change="clearError('carrier')"
          >
            <option value="">Select a carrier...</option>
            <option 
              v-for="carrier in carriers" 
              :key="carrier.slug" 
              :value="carrier.slug"
            >
              {{ carrier.name }}
            </option>
          </select>
          <span v-if="errors.carrier" class="error-message">
            {{ errors.carrier }}
          </span>
        </div>

        <!-- Weight Input -->
        <div class="form-group">
          <label for="weight" class="form-label">
            Weight (kg)
            <span class="required">*</span>
          </label>
          <input 
            id="weight"
            v-model.number="formData.weightKg" 
            type="number" 
            step="0.1"
            min="0.1"
            placeholder="Enter weight in kilograms"
            class="form-control"
            :class="{ 'error': errors.weightKg }"
            @input="clearError('weightKg')"
          />
          <span v-if="errors.weightKg" class="error-message">
            {{ errors.weightKg }}
          </span>
        </div>

        <!-- Calculate Button -->
        <button 
          @click="calculatePrice" 
          class="btn-calculate"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner"></span>
          <span v-else>Calculate Price</span>
        </button>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="spinner-large"></div>
          <p>Calculating shipping cost...</p>
        </div>

        <!-- Success Result -->
        <div v-if="result && !loading" class="result-card success">
          <h2 class="result-title">
            <i class="icon">✓</i>
            Shipping Cost
          </h2>
          <div class="result-details">
            <div class="result-row">
              <span class="label">Carrier:</span>
              <span class="value">{{ getCarrierName(result.carrier) }}</span>
            </div>
            <div class="result-row">
              <span class="label">Weight:</span>
              <span class="value">{{ result.weightKg }} kg</span>
            </div>
            <div class="result-row highlight">
              <span class="label">Total Price:</span>
              <span class="value price">
                {{ result.price }} {{ result.currency }}
              </span>
            </div>
          </div>
        </div>

        <!-- Error Result -->
        <div v-if="errorMessage && !loading" class="result-card error">
          <h2 class="result-title">
            <i class="icon">⚠</i>
            Error
          </h2>
          <p class="error-text">{{ errorMessage }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="card-footer">
        <p class="footer-text">
          Powered by Symfony + Vue.js
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'App',
  
  data() {
    return {
      carriers: [],
      formData: {
        carrier: '',
        weightKg: null
      },
      result: null,
      errorMessage: '',
      loading: false,
      errors: {
        carrier: '',
        weightKg: ''
      }
    };
  },

  created() {
    // Fetch available carriers on component mount
    this.fetchCarriers();
  },

  methods: {
    /**
     * Fetch available carriers from API
     */
    async fetchCarriers() {
      try {
        const response = await axios.get('/api/shipping/carriers');
        this.carriers = response.data.carriers;
      } catch (error) {
        console.error('Failed to fetch carriers:', error);
        this.errorMessage = 'Failed to load carriers. Please refresh the page.';
        
        // Fallback to hardcoded carriers
        this.carriers = [
          { slug: 'transcompany', name: 'TransCompany' },
          { slug: 'packgroup', name: 'PackGroup' }
        ];
      }
    },

    /**
     * Validate form data
     */
    validateForm() {
      let isValid = true;
      this.errors = { carrier: '', weightKg: '' };

      // Validate carrier
      if (!this.formData.carrier) {
        this.errors.carrier = 'Please select a carrier';
        isValid = false;
      }

      // Validate weight
      if (!this.formData.weightKg || this.formData.weightKg <= 0) {
        this.errors.weightKg = 'Weight must be greater than 0';
        isValid = false;
      }

      return isValid;
    },

    /**
     * Calculate shipping price
     */
    async calculatePrice() {
      // Clear previous results
      this.result = null;
      this.errorMessage = '';

      // Validate form
      if (!this.validateForm()) {
        return;
      }

      this.loading = true;

      try {
        const response = await axios.post('/api/shipping/calculate', {
          carrier: this.formData.carrier,
          weightKg: this.formData.weightKg
        });

        this.result = response.data;
      } catch (error) {
        console.error('Calculation error:', error);
        
        if (error.response) {
          // Server responded with error
          const data = error.response.data;
          
          if (data.details) {
            // Validation errors
            const errorMessages = Object.values(data.details).join(', ');
            this.errorMessage = errorMessages;
          } else if (data.error) {
            // General error
            this.errorMessage = data.error;
          } else {
            this.errorMessage = 'An error occurred while calculating the price';
          }
        } else if (error.request) {
          // Request made but no response
          this.errorMessage = 'Server is not responding. Please check if the backend is running.';
        } else {
          // Error in request setup
          this.errorMessage = 'Failed to send request: ' + error.message;
        }
      } finally {
        this.loading = false;
      }
    },

    /**
     * Get carrier name by slug
     */
    getCarrierName(slug) {
      const carrier = this.carriers.find(c => c.slug === slug);
      return carrier ? carrier.name : slug;
    },

    /**
     * Clear error for specific field
     */
    clearError(field) {
      this.errors[field] = '';
      this.errorMessage = '';
    }
  }
};
</script>

<style scoped>
/* Container */
.container {
  max-width: 600px;
  margin: 50px auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Calculator Card */
.calculator-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

/* Title */
.title {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
  margin: 0;
  font-size: 28px;
  font-weight: 600;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.icon {
  font-style: normal;
  font-size: 32px;
}

/* Card Content */
.card-content {
  padding: 30px;
}

/* Form Group */
.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.required {
  color: #e74c3c;
  margin-left: 4px;
}

/* Form Controls */
.form-control {
  width: 100%;
  padding: 12px 16px;
  font-size: 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.3s ease;
  background: #fafafa;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control.error {
  border-color: #e74c3c;
  background: #fff5f5;
}

.form-control.error:focus {
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

/* Error Message */
.error-message {
  display: block;
  margin-top: 6px;
  color: #e74c3c;
  font-size: 13px;
  font-weight: 500;
}

/* Calculate Button */
.btn-calculate {
  width: 100%;
  padding: 14px 24px;
  font-size: 16px;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-calculate:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(102, 126, 234, 0.3);
}

.btn-calculate:active:not(:disabled) {
  transform: translateY(0);
}

.btn-calculate:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Spinner */
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.spinner-large {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(102, 126, 234, 0.2);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Loading State */
.loading-state {
  margin-top: 24px;
  padding: 30px;
  text-align: center;
  color: #666;
}

.loading-state p {
  margin-top: 16px;
  font-size: 14px;
}

/* Result Card */
.result-card {
  margin-top: 24px;
  padding: 24px;
  border-radius: 8px;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.result-card.success {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border: 2px solid #667eea;
}

.result-card.error {
  background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
  border: 2px solid #e74c3c;
}

.result-title {
  margin: 0 0 16px 0;
  font-size: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.result-card.success .result-title {
  color: #667eea;
}

.result-card.error .result-title {
  color: #e74c3c;
}

/* Result Details */
.result-details {
  background: white;
  padding: 16px;
  border-radius: 6px;
}

.result-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.result-row:last-child {
  border-bottom: none;
}

.result-row.highlight {
  margin-top: 8px;
  padding-top: 16px;
  border-top: 2px solid #667eea;
  font-size: 18px;
}

.result-row .label {
  font-weight: 500;
  color: #666;
}

.result-row .value {
  font-weight: 600;
  color: #333;
}

.result-row .price {
  font-size: 24px;
  color: #667eea;
}

/* Error Text */
.error-text {
  margin: 0;
  color: #e74c3c;
  line-height: 1.6;
}

/* Card Footer */
.card-footer {
  background: #f8f9fa;
  padding: 16px 30px;
  text-align: center;
  border-top: 1px solid #e0e0e0;
}

.footer-text {
  margin: 0;
  font-size: 13px;
  color: #666;
}

/* Responsive */
@media (max-width: 600px) {
  .container {
    margin: 20px;
    padding: 0;
  }

  .card-content {
    padding: 20px;
  }

  .title {
    font-size: 24px;
    padding: 24px;
  }

  .result-row .price {
    font-size: 20px;
  }
}
</style>