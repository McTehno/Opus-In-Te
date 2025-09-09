document.addEventListener('DOMContentLoaded', () => {
    // Select all elements with the .fade-in class
    const fadeInElements = document.querySelectorAll('.fade-in');

    // Configuration for the observer
    const observerOptions = {
        root: null, // Observes intersections relative to the viewport
        rootMargin: '0px',
        threshold: 0.1 // Triggers when 10% of the element is visible
    };

    // The callback function to execute when an element is intersecting
    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            // If the element is in the viewport
            if (entry.isIntersecting) {
                // Add the 'visible' class to trigger the animation
                entry.target.classList.add('visible');
                // Stop observing the element so the animation only runs once
                observer.unobserve(entry.target);
            }
        });
    };

    // Create the new Intersection Observer
    const observer = new IntersectionObserver(observerCallback, observerOptions);

    // Observe each of the .fade-in elements
    fadeInElements.forEach(el => {
        observer.observe(el);
    });
}); 

// Animations for Kontakt.html