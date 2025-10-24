// Wait until the DOM content is loaded, but before images/stylesheets fully load
document.addEventListener('DOMContentLoaded', () => {
    const loadingScreen = document.getElementById('loading-screen');

    // Ensure the loading screen element exists
    if (loadingScreen) {
        // Use window.onload to wait for all resources (images, styles)
        window.onload = () => {
            // Add the 'hidden' class to trigger the fade-out CSS transition
            loadingScreen.classList.add('hidden');

            // Optional: Remove the loading screen from the DOM after the transition
            // This prevents it from interfering with anything later, though usually not necessary
            // Adjust the timeout (700ms) to match your CSS transition duration
            setTimeout(() => {
                // Check if the element still exists before trying to remove
                if (loadingScreen.parentNode) {
                    loadingScreen.parentNode.removeChild(loadingScreen);
                }
            }, 700); // Matches the transition duration in CSS
        };
    } else {
        console.warn('Loading screen element (#loading-screen) not found.');
    }
});