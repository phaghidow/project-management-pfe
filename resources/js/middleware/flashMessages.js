// resources/js/middleware/flashMessages.js
import axios from 'axios'

// Add interceptor to capture flash messages from Laravel backend
axios.interceptors.response.use(
  response => {
    // Laravel stores flash messages in response headers for AJAX requests
    const successMessage = response.headers['x-flash-success'] || response.data?.flash?.success
    const errorMessage = response.headers['x-flash-error'] || response.data?.flash?.error
    
    if (successMessage || errorMessage) {
      window.flashMessages = window.flashMessages || {}
      if (successMessage) window.flashMessages.success = successMessage
      if (errorMessage) window.flashMessages.error = errorMessage
      
      // Trigger custom event for Vue components to listen
      window.dispatchEvent(new CustomEvent('flash-message', {
        detail: { success: successMessage, error: errorMessage }
      }))
    }
    
    return response
  },
  error => {
    // Handle validation errors from Laravel
    if (error.response?.status === 422 && error.response?.data?.errors) {
      window.dispatchEvent(new CustomEvent('validation-errors', {
        detail: error.response.data.errors
      }))
    }
    
    // Handle error responses
    const errorMessage = error.response?.data?.message ||
                        error.response?.headers['x-flash-error'] ||
                        error.message ||
                        'Une erreur est survenue'

    window.dispatchEvent(new CustomEvent('ajax-error', {
      detail: {
        message: errorMessage,
        status: error.response?.status || 0,
        errors: error.response?.data?.errors || null
      }
    }))
    
    window.dispatchEvent(new CustomEvent('flash-message', {
      detail: { error: errorMessage }
    }))
    
    return Promise.reject(error)
  }
)

export default axios
