document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerText;
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerText = 'Slanje...';
            submitButton.style.opacity = '0.7';
            submitButton.style.cursor = 'not-allowed';

            // Collect form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email-contact').value,
                phone: document.getElementById('phone').value,
                message: document.getElementById('message').value
            };

            // Send data to backend
            fetch('backend/send_contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message using global notification system
                    if (typeof showNotification === 'function') {
                        showNotification(data.message || 'Vaša poruka je uspješno poslana!', 'success');
                    }
                    contactForm.reset();
                } else {
                    // Show error message using global notification system
                    if (typeof showNotification === 'function') {
                        showNotification(data.message || 'Došlo je do greške. Molimo pokušajte ponovo.', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showNotification === 'function') {
                    showNotification('Došlo je do greške prilikom komunikacije sa serverom.', 'error');
                }
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerText = originalButtonText;
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
            });
        });
    }
});
