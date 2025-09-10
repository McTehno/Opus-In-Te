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

// This code finds the map container and builds the map
document.addEventListener('DOMContentLoaded', () => {

    if (document.getElementById('map-container')) {
        // The coordinates for Jevrejska 56, Banja Luka
        const mapCoordinates = [44.7742, 17.1915];

        // 1. Initialize map and set the view
        const map = L.map('map-container', {
            scrollWheelZoom: false // Prevents accidental zooming while scrolling the page
        }).setView(mapCoordinates, 17); // 17 is a good zoom level

        // 2. Add our styled tile layer from Stadia Maps
        L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
            maxZoom: 20,
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 3. Create a custom, on-brand map marker icon
        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: `<div class="marker-pin"></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42]
        });

        // 4. Add the marker to the map
        L.marker(mapCoordinates, {icon: customIcon}).addTo(map)
            .bindPopup('<b>Opus in te</b><br>Jevrejska 56, Banja Luka');
    }
});

// --- Smooth Scroll for Contact Page H1 Button ---
document.addEventListener('DOMContentLoaded', () => {
    const contactPageLink = document.querySelector('.page-title-link');

    // Check if the link exists on the current page
    if (contactPageLink) {
        contactPageLink.addEventListener('click', function(e) {
            // Prevent the default instant jump
            e.preventDefault();

            // Get the ID of the target section from the link's href
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            // Smoothly scroll to the target section
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start' // Aligns the top of the section to the top of the viewport
                });
            }
        });
    }
});

// --- Interactive Lines for Contact Page Header ---
document.addEventListener('DOMContentLoaded', () => {
    const contactButton = document.querySelector('.page-title-link');
    const contactSection = document.querySelector('.page-title-section-kontakt');

    // Check if both elements exist on the page to avoid errors
    if (contactButton && contactSection) {
        
        // When the mouse enters the button, add the active class to the section
        contactButton.addEventListener('mouseenter', () => {
            contactSection.classList.add('lines-active');
        });

        // When the mouse leaves the button, remove the class
        contactButton.addEventListener('mouseleave', () => {
            contactSection.classList.remove('lines-active');
        });
    }
});
// Animations for Kontakt.html