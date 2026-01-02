// Wait until the DOM content is loaded, but before images/stylesheets fully load
document.addEventListener('DOMContentLoaded', () => {
    const loadingScreen = document.getElementById('loading-screen');
    if (!loadingScreen) {
        console.warn('Loading screen element (#loading-screen) not found.');
        return;
    }

    const hideLoadingScreen = () => {
        loadingScreen.classList.add('loading-hidden');
        setTimeout(() => {
            if (loadingScreen && loadingScreen.parentNode) {
                loadingScreen.parentNode.removeChild(loadingScreen);
            }
        }, 1000);
    };

    window.addEventListener('load', () => {
        const gate = typeof window.loadingScreenGate === 'function' ? window.loadingScreenGate() : null;

        if (gate && typeof gate.then === 'function') {
            gate
                .catch((error) => console.error('Loading gate rejected:', error))
                .finally(hideLoadingScreen);
        } else {
            hideLoadingScreen();
        }
    });
});