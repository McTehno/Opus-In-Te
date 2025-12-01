  // Accordion Logic
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        header.addEventListener('click', () => {
            // Close other open items
            accordionItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            // Toggle the clicked item
            item.classList.toggle('active');
        });
    });

    // Modal Logic
    const modal = document.getElementById('priceListModal');
    const openBtn = document.getElementById('openPriceListBtn');
    const closeBtn = document.querySelector('.close-button');

    openBtn.onclick = function() {
        modal.style.display = 'flex';
    }
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }