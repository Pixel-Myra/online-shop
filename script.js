// Mobile menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if(hamburger) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
}

// Close mobile menu when clicking a link
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));

// Image preview for product upload
const imageInput = document.getElementById('image');
if(imageInput) {
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(file) {
            // You can add image preview functionality here
            console.log('Image selected:', file.name);
        }
    });
}

// Form validation helpers
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showAlert(message, type = 'error') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insert at the beginning of the form
    const form = document.querySelector('form');
    if(form) {
        form.parentNode.insertBefore(alertDiv, form);
    }
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Price formatting
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading state to buttons
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if(submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Завантаження...';
            submitBtn.disabled = true;
        }
    });
});

// Category filter instant submit
const categorySelect = document.querySelector('.category-select');
if(categorySelect) {
    categorySelect.addEventListener('change', function() {
        this.form.submit();
    });
}

// Confirm delete
function confirmDelete(message = 'Ви впевнені, що хочете видалити цей елемент?') {
    return confirm(message);
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shop website loaded successfully!');
    
    // Add current year to footer
    const yearSpan = document.getElementById('current-year');
    if(yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});