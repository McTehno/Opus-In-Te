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
// Animations for Kontakt.html