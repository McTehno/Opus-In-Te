document.addEventListener('DOMContentLoaded', function() {
    const servicesTableBody = document.getElementById('servicesTableBody');
    
    // Stats Elements
    const mostProfitableName = document.getElementById('mostProfitableName');
    const mostProfitableAmount = document.getElementById('mostProfitableAmount');
    const mostCommonName = document.getElementById('mostCommonName');
    const mostCommonCount = document.getElementById('mostCommonCount');
    const leastCommonName = document.getElementById('leastCommonName');
    const leastCommonCount = document.getElementById('leastCommonCount');

    // Modals
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    const closeButtons = document.querySelectorAll('.close-modal, .close-modal-btn');

    // Edit Form
    const editServiceForm = document.getElementById('editServiceForm');
    const editServiceId = document.getElementById('editServiceId');
    const editName = document.getElementById('editName');
    const editPrice = document.getElementById('editPrice');
    const editDuration = document.getElementById('editDuration');

    // Delete Actions
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let serviceToDeleteId = null;

    // Initial Fetch
    fetchServices();

    // --- Functions ---

    function fetchServices() {
        fetch('backend/admin_fetch_services.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderStats(data.stats);
                    renderTable(data.services);
                } else {
                    alert('Error fetching services: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function renderStats(stats) {
        if (stats.most_profitable) {
            mostProfitableName.textContent = stats.most_profitable.name;
            mostProfitableAmount.textContent = formatCurrency(stats.most_profitable.total_income);
        } else {
            mostProfitableName.textContent = '-';
            mostProfitableAmount.textContent = '-';
        }

        if (stats.most_common) {
            mostCommonName.textContent = stats.most_common.name;
            mostCommonCount.textContent = stats.most_common.appointment_count + ' termina';
        } else {
            mostCommonName.textContent = '-';
            mostCommonCount.textContent = '-';
        }

        if (stats.least_common) {
            leastCommonName.textContent = stats.least_common.name;
            leastCommonCount.textContent = stats.least_common.appointment_count + ' termina';
        } else {
            leastCommonName.textContent = '-';
            leastCommonCount.textContent = '-';
        }
    }

    function renderTable(services) {
        servicesTableBody.innerHTML = '';

        if (services.length === 0) {
            servicesTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Nema usluga.</td></tr>';
            return;
        }

        services.forEach(service => {
            const tr = document.createElement('tr');
            
            const durationText = service.duration ? service.duration + ' min' : 'Neodredjeno';

            tr.innerHTML = `
                <td>${escapeHtml(service.name)}</td>
                <td>${formatCurrency(service.price)}</td>
                <td>${durationText}</td>
                <td>${service.appointment_count}</td>
                <td>${formatCurrency(service.total_income)}</td>
                <td>
                    <button class="action-btn edit-btn" data-id="${service.idAppointment_Type}" title="Uredi"><i class="fa-solid fa-pen"></i></button>
                    <button class="action-btn delete-btn" data-id="${service.idAppointment_Type}" title="Obriši"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;

            // Attach event listeners to buttons
            const editBtn = tr.querySelector('.edit-btn');
            editBtn.addEventListener('click', () => openEditModal(service));

            const deleteBtn = tr.querySelector('.delete-btn');
            deleteBtn.addEventListener('click', () => openDeleteModal(service));

            servicesTableBody.appendChild(tr);
        });
    }

    function openEditModal(service) {
        editServiceId.value = service.idAppointment_Type;
        editName.value = service.name;
        editPrice.value = service.price;
        editDuration.value = service.duration !== null ? service.duration : 'null';
        
        editModal.classList.add('active');
    }

    function openDeleteModal(service) {
        serviceToDeleteId = service.idAppointment_Type;
        document.getElementById('deleteServiceName').textContent = service.name;
        deleteModal.classList.add('active');
    }

    // --- Event Listeners ---

    // Close Modals
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.classList.remove('active');
            deleteModal.classList.remove('active');
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target == editModal) editModal.classList.remove('active');
        if (e.target == deleteModal) deleteModal.classList.remove('active');
    });

    // Handle Edit Submit
    editServiceForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            id: editServiceId.value,
            name: editName.value,
            price: editPrice.value,
            duration: editDuration.value
        };

        fetch('backend/admin_update_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editModal.classList.remove('active');
                fetchServices(); // Refresh data
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Handle Delete Confirm
    confirmDeleteBtn.addEventListener('click', function() {
        if (!serviceToDeleteId) return;

        fetch('backend/admin_delete_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: serviceToDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteModal.classList.remove('active');
                fetchServices(); // Refresh data
            } else {
                alert('Error: ' + data.message);
                deleteModal.classList.remove('active');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Helpers
    function formatCurrency(amount) {
        return parseFloat(amount).toFixed(2) + ' KM';
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
