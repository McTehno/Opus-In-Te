document.addEventListener('DOMContentLoaded', () => {
    // Select the elements needed for the animation
    const loginSection = document.querySelector('.login-section');
    const submitBtn = document.querySelector('.login-btn');
    const backBtn = document.querySelector('.back-btn');

    // Check if the elements exist to avoid errors on other pages
    if (loginSection && submitBtn && backBtn) {

        // --- Animate gradient on Submit Button hover ---
        submitBtn.addEventListener('mouseenter', () => {
            loginSection.classList.add('animate-gradient');
        });
        submitBtn.addEventListener('mouseleave', () => {
            loginSection.classList.remove('animate-gradient');
        });

        // --- Show dark overlay on Back Button hover ---
        backBtn.addEventListener('mouseenter', () => {
            loginSection.classList.add('show-overlay');
        });
        backBtn.addEventListener('mouseleave', () => {
            loginSection.classList.remove('show-overlay');
        });
    }
    // Common elements
    const emailInput = document.getElementById('email');
    const registerLink = document.querySelector('.register-link a');
    
    // Logic for Login Page: Update Register link with email
    if (document.title.includes('Prijava') && emailInput && registerLink) {
        emailInput.addEventListener('input', () => {
            const email = encodeURIComponent(emailInput.value);
            registerLink.href = `Register.php?email=${email}`;
        });
    }

    // Logic for Register Page: Prefill email and Animate
    if (document.title.includes('Registracija')) {
        // Prefill Email
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        if (email && emailInput) {
            emailInput.value = email;
            emailInput.style.borderColor = 'var(--soft-gold)';
        }

        // Animation
        const wrapper = document.querySelector('.login-form-wrapper');
        const newFields = document.querySelectorAll('.form-group.new-field');
        
        if (wrapper && newFields.length > 0) {
            // Set initial state for animation
            newFields.forEach(field => {
                field.style.opacity = '0';
                field.style.transform = 'translateY(-20px)';
                field.style.transition = 'all 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
                field.style.height = '0';
                field.style.margin = '0';
                field.style.overflow = 'hidden';
            });

            // Trigger animation after a short delay
            setTimeout(() => {
                newFields.forEach((field, index) => {
                    setTimeout(() => {
                        field.style.height = '90px'; // Approximate height
                        field.style.margin = '0 0 25px 0'; // Restore margin
                        field.style.opacity = '1';
                        field.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 300);
        }
    }
});