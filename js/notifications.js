function showNotification(message, type = 'info') {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Icon based on type
    let icon = '';
    if (type === 'success') {
        icon = '<i class="fa-solid fa-check-circle"></i>';
    } else if (type === 'error') {
        icon = '<i class="fa-solid fa-circle-exclamation"></i>';
    } else {
        icon = '<i class="fa-solid fa-circle-info"></i>';
    }

    notification.innerHTML = `
        <div class="notification-content">
            ${icon}
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

    // Add to container
    container.appendChild(notification);

    // Trigger animation
    requestAnimationFrame(() => {
        notification.classList.add('show');
    });

    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        removeNotification(notification);
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
        removeNotification(notification);
    }, 5000);
}

function removeNotification(notification) {
    notification.classList.remove('show');
    notification.addEventListener('transitionend', () => {
        notification.remove();
    });
}
