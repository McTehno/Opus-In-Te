document.addEventListener('DOMContentLoaded', () => {
    // Select all elements with the .fade-in class
    const fadeInElements = document.querySelectorAll('.fade-in');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // --- NEW: Staggered Animation Logic ---
                const delay = entry.target.dataset.delay;
                if (delay) {
                    // If a 'data-delay' attribute exists, apply it as a transition-delay
                    entry.target.style.transitionDelay = `${delay * 100}ms`;
                }
                // --- End of New Logic ---

                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);

    fadeInElements.forEach(el => {
        observer.observe(el);
    });

    // --- Map and Contact Page Scripts from before ---

    // Map Logic
    if (document.getElementById('map-container')) {
        const mapCoordinates = [44.773338, 17.190143];
        const map = L.map('map-container', { scrollWheelZoom: false }).setView(mapCoordinates, 17);
        L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
            maxZoom: 20,
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>'
        }).addTo(map);
        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: `<div class="marker-pin"></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42]
        });
        L.marker(mapCoordinates, { icon: customIcon }).addTo(map)
            .bindPopup('<b>Opus in te</b><br>Vidovdanska Ulica 2, Banja Luka');
    }

    // Smooth Scroll for Contact Page H1
    const contactPageLink = document.querySelector('.page-title-link');
    if (contactPageLink) {
        contactPageLink.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Interactive Lines for Contact Page Header
    const contactButton = document.querySelector('.page-title-link');
    const contactSection = document.querySelector('.page-title-section-kontakt');
    if (contactButton && contactSection) {
        contactButton.addEventListener('mouseenter', () => {
            contactSection.classList.add('lines-active');
        });
        contactButton.addEventListener('mouseleave', () => {
            contactSection.classList.remove('lines-active');
        });
    }
});