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
});