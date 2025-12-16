document.addEventListener('DOMContentLoaded', () => {
    const successModal = document.getElementById('success-modal');
    const closeModalButtons = document.querySelectorAll('.close-modal-btn, .modal-close-btn');

    if (successModal && closeModalButtons.length > 0) {
        if (window.showProfileSuccessModal) {
            successModal.style.display = 'flex';
        }

        closeModalButtons.forEach(button => {
            button.addEventListener('click', () => {
                successModal.style.display = 'none';
            });
        });

        window.addEventListener('click', (event) => {
            if (event.target === successModal) {
                successModal.style.display = 'none';
            }
        });
    }
});