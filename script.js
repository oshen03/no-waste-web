document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const signinForm = document.getElementById('signinForm');
    const signupForm = document.getElementById('signupForm');
    const switchToSignupLink = document.getElementById('switchToSignup');
    const switchToSigninLink = document.getElementById('switchToSignin');
    
    // Initially show signin form
    signinForm.style.display = 'block';
    signupForm.style.display = 'none';

    // Form toggle
    function showSigninForm() {
        signinForm.style.display = 'block';
        signupForm.style.display = 'none';
        hideAlert();
    }
    
    function showSignupForm() {
        signinForm.style.display = 'none';
        signupForm.style.display = 'block';
        hideAlert();
    }
    
    // Event listeners for form toggle
    switchToSignupLink.addEventListener('click', function(e) {
        e.preventDefault();
        showSignupForm();
    });
    
    switchToSigninLink.addEventListener('click', function(e) {
        e.preventDefault();
        showSigninForm();
    });
    
    // Utility functions
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    function hideAlert() {
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = '';
    }

    function setButtonLoading(button, isLoading) {
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner-border');
        
        if (isLoading) {
            button.disabled = true;
            btnText.style.display = 'none';
            spinner.classList.remove('d-none');
        } else {
            button.disabled = false;
            btnText.style.display = 'inline';
            spinner.classList.add('d-none');
        }
    }
    
    // Sign In Form Submission
    signinForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        
        const email = document.getElementById('signin_email').value.trim();
        const password = document.getElementById('signin_password').value;
        const signinBtn = document.getElementById('signinBtn');
        
        // Validation
        if (!email || !password) {
            showAlert('Please fill in all fields');
            return;
        }
        
        // Set loading state
        setButtonLoading(signinBtn, true);
        
        // Send AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'auth_handler.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            setButtonLoading(signinBtn, false);
            
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    window.location.href = 'home.php';
                } else {
                    showAlert(response.message || 'Login failed');
                }
            } catch (e) {
                showAlert('Invalid server response');
            }
        };
        
        xhr.onerror = function() {
            setButtonLoading(signinBtn, false);
            showAlert('Network error. Please try again.');
        };
        
        xhr.send(`action=signin&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`);
    });
    
    // Sign Up Form Submission
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        
        // Get form data
        const email = document.getElementById('signup_email').value.trim();
        const password = document.getElementById('signup_password').value;
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const confirmPassword = document.getElementById('password2').value;
        const privacyChecked = document.getElementById('privacy').checked;
        const termsChecked = document.getElementById('terms').checked;
        const signupBtn = document.getElementById('signupBtn');
        
        // Validation
        let errors = [];
        
        if (!email || !password || !firstName || !lastName || !confirmPassword) {
            errors.push('Please fill in all fields');
        }
        
        if (password.length < 6) {
            errors.push('Password must be at least 6 characters');
        }
        
        if (password !== confirmPassword) {
            errors.push('Passwords do not match');
        }
        
        if (!privacyChecked || !termsChecked) {
            errors.push('Please agree to terms and privacy policy');
        }
        
        if (errors.length > 0) {
            showAlert(errors.join('<br>'));
            return;
        }
        
        // Set loading state
        setButtonLoading(signupBtn, true);
        
        // Send AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'auth_handler.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            setButtonLoading(signupBtn, false);
            
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    window.location.href = 'home.php';
                } else {
                    showAlert(response.message || 'Registration failed');
                }
            } catch (e) {
                showAlert('Invalid server response');
            }
        };
        
        xhr.onerror = function() {
            setButtonLoading(signupBtn, false);
            showAlert('Network error. Please try again.');
        };
        
        const data = new URLSearchParams();
        data.append('action', 'signup');
        data.append('email', email);
        data.append('password', password);
        data.append('first_name', firstName);
        data.append('last_name', lastName);
        
        xhr.send(data.toString());
    });
    
    // Real-time password match
    document.getElementById('password2').addEventListener('input', function() {
        const password = document.getElementById('signup_password').value;
        if (this.value && password !== this.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
});

// Navbar scroll effect
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 100) {
        navbar.classList.add('nav-scrolled');
    } else {
        navbar.classList.remove('nav-scrolled');
    }
});

// Mobile menu toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on nav links
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));

// Search functionality
const searchBtn = document.getElementById('searchBtn');
const searchInput = document.getElementById('searchInput');

searchBtn.addEventListener('click', () => {
    const searchTerm = searchInput.value.trim();
    if (searchTerm) {
        alert(`Searching for: ${searchTerm}`);
        // Here you would typically send the search query to your backend
        console.log('Search term:', searchTerm);
    }
});

// Search on Enter key press
searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        searchBtn.click();
    }
});

// Smooth scrolling for any internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }
});

// Benefit animations
document.addEventListener('DOMContentLoaded', () => {
    const benefits = document.querySelectorAll('.benefit');
    benefits.forEach((benefit, index) => {
        setTimeout(() => {
            benefit.style.opacity = '1';
        }, index * 500);
    });
});

// Banner content repeat
document.addEventListener("DOMContentLoaded", () => {
    const banner = document.querySelector('.banner');
    const bannerContent = banner.innerHTML;
    banner.innerHTML += bannerContent + bannerContent;
});

// Animate statistics counting up
function animateStats() {
    const stat1 = document.getElementById('stat1');
    const stat2 = document.getElementById('stat2');
    const stat3 = document.getElementById('stat3');

    const target1 = 460;
    const target2 = 1500;
    const target3 = 250;

    const duration = 2000;
    const frameRate = 50;
    const totalFrames = duration / (1000 / frameRate);

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function animateStat(element, target, suffix = '+') {
        let frame = 0;
        const start = 0;
        const increment = target / totalFrames;
        let current = start;

        const timer = setInterval(() => {
            frame++;
            current += increment;

            if (frame >= totalFrames) {
                clearInterval(timer);
                current = target;
            }

            element.textContent = formatNumber(Math.floor(current)) + (frame >= totalFrames ? suffix : '');
        }, 1000 / frameRate);
    }

    setTimeout(() => animateStat(stat1, target1, '+'), 100);
    setTimeout(() => animateStat(stat2, target2, '+'), 300);
    setTimeout(() => animateStat(stat3, target3), 500);
}

// Start animation when page loads
window.addEventListener('load', animateStats);

// Footer animations
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1
    };
    
    const footerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    const animatedElements = document.querySelectorAll('.footer-logo, .links-column, .footer-bottom');
    animatedElements.forEach(el => {
        footerObserver.observe(el);
    });
});