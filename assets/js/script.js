// Main JavaScript functionality for the survey system

document.addEventListener("DOMContentLoaded", () => {
  // Initialize all components
  initializeFormValidation()
  initializeProgressBars()
  initializeTooltips()
  initializeConfirmDialogs()
})

// Form validation
function initializeFormValidation() {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault()
      }
    })
  })
}

function validateForm(form) {
  let isValid = true
  const requiredFields = form.querySelectorAll("[required]")

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      showFieldError(field, "This field is required")
      isValid = false
    } else {
      clearFieldError(field)
    }

    // Email validation
    if (field.type === "email" && field.value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(field.value)) {
        showFieldError(field, "Please enter a valid email address")
        isValid = false
      }
    }
  })

  return isValid
}

function showFieldError(field, message) {
  clearFieldError(field)

  const errorDiv = document.createElement("div")
  errorDiv.className = "field-error"
  errorDiv.textContent = message
  errorDiv.style.color = "#dc2626"
  errorDiv.style.fontSize = "14px"
  errorDiv.style.marginTop = "4px"

  field.parentNode.appendChild(errorDiv)
  field.style.borderColor = "#dc2626"
}

function clearFieldError(field) {
  const existingError = field.parentNode.querySelector(".field-error")
  if (existingError) {
    existingError.remove()
  }
  field.style.borderColor = ""
}

// Progress bar animations
function initializeProgressBars() {
  const progressBars = document.querySelectorAll(".progress-fill")

  progressBars.forEach((bar) => {
    const width = bar.style.width
    bar.style.width = "0%"

    setTimeout(() => {
      bar.style.width = width
    }, 500)
  })
}

// Tooltip functionality
function initializeTooltips() {
  const tooltipElements = document.querySelectorAll("[data-tooltip]")

  tooltipElements.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

function showTooltip(e) {
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = e.target.getAttribute("data-tooltip")
  tooltip.style.cssText = `
        position: absolute;
        background: #1a202c;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
    `

  document.body.appendChild(tooltip)

  const rect = e.target.getBoundingClientRect()
  tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + "px"

  setTimeout(() => {
    tooltip.style.opacity = "1"
  }, 10)

  e.target._tooltip = tooltip
}

function hideTooltip(e) {
  if (e.target._tooltip) {
    e.target._tooltip.remove()
    delete e.target._tooltip
  }
}

// Confirmation dialogs
function initializeConfirmDialogs() {
  const confirmButtons = document.querySelectorAll("[data-confirm]")

  confirmButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const message = this.getAttribute("data-confirm")
      if (!confirm(message)) {
        e.preventDefault()
      }
    })
  })
}

// Utility functions
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.textContent = message
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `

  // Set background color based on type
  const colors = {
    info: "#3b82f6",
    success: "#10b981",
    warning: "#f59e0b",
    error: "#ef4444",
  }
  notification.style.backgroundColor = colors[type] || colors.info

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 10)

  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      notification.remove()
    }, 300)
  }, 5000)
}

// AJAX helper function
function makeRequest(url, options = {}) {
  const defaultOptions = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  }

  const config = { ...defaultOptions, ...options }

  return fetch(url, config)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .catch((error) => {
      console.error("Request failed:", error)
      showNotification("An error occurred. Please try again.", "error")
      throw error
    })
}

// Auto-save functionality for forms
function initializeAutoSave(formSelector, saveUrl) {
  const form = document.querySelector(formSelector)
  if (!form) return

  let saveTimeout
  const inputs = form.querySelectorAll("input, textarea, select")

  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      clearTimeout(saveTimeout)
      saveTimeout = setTimeout(() => {
        autoSaveForm(form, saveUrl)
      }, 2000) // Save after 2 seconds of inactivity
    })
  })
}

function autoSaveForm(form, saveUrl) {
  const formData = new FormData(form)
  const data = Object.fromEntries(formData.entries())

  makeRequest(saveUrl, {
    method: "POST",
    body: JSON.stringify(data),
  })
    .then(() => {
      showNotification("Draft saved", "success")
    })
    .catch(() => {
      showNotification("Failed to save draft", "error")
    })
}

// Search functionality
function initializeSearch(inputSelector, itemsSelector) {
  const searchInput = document.querySelector(inputSelector)
  const items = document.querySelectorAll(itemsSelector)

  if (!searchInput || !items.length) return

  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase()

    items.forEach((item) => {
      const text = item.textContent.toLowerCase()
      const shouldShow = text.includes(query)
      item.style.display = shouldShow ? "" : "none"
    })
  })
}

// Export functions for global use
window.SurveySystem = {
  showNotification,
  makeRequest,
  initializeAutoSave,
  initializeSearch,
}
