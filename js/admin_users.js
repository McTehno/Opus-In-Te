document.addEventListener('DOMContentLoaded', function() {
    const usersTableBody = document.getElementById('usersTableBody');
    
    // Stats Elements
    const workersCount = document.getElementById('workersCount');
    const usersWithAccount = document.getElementById('usersWithAccount');
    const usersWithoutAccount = document.getElementById('usersWithoutAccount');

    // Modals
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    const createWorkerModal = document.getElementById('createWorkerModal');
    const closeButtons = document.querySelectorAll('.close-modal, .close-modal-btn');
    const modalTitle = document.getElementById('modalTitle');

    // Create Worker Form
    const addWorkerBtn = document.getElementById('addWorkerBtn');
    const createWorkerForm = document.getElementById('createWorkerForm');

    // Edit Form
    const editUserForm = document.getElementById('editUserForm');
    const editUserId = document.getElementById('editUserId');
    const editUserRole = document.getElementById('editUserRole');
    const editName = document.getElementById('editName');
    const editLastName = document.getElementById('editLastName');
    const editPhone = document.getElementById('editPhone');
    const editEmail = document.getElementById('editEmail');
    const editPictureGroup = document.getElementById('editPictureGroup');

    // Delete Actions
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let userToDeleteId = null;

    // Initial Fetch
    fetchUsers();

    // --- Event Listeners ---
    
    if (addWorkerBtn) {
        addWorkerBtn.addEventListener('click', () => {
            createWorkerModal.classList.add('active');
        });
    }

    if (createWorkerForm) {
        createWorkerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/backend/admin_create_worker.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAllModals();
                    createWorkerForm.reset();
                    fetchUsers();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Greška pri kreiranju radnika.', 'error');
            });
        });
    }

    // Global close modal function
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    };

    function closeAllModals() {
        if (editModal) editModal.classList.remove('active');
        if (deleteModal) deleteModal.classList.remove('active');
        if (createWorkerModal) createWorkerModal.classList.remove('active');
        userToDeleteId = null;
    }

    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === editModal || e.target === deleteModal || e.target === createWorkerModal) {
            closeAllModals();
        }
    });

    // --- Functions ---

    function fetchUsers() {
        fetch('/backend/admin_fetch_users.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderStats(data.stats);
                    renderTable(data.users);
                } else {
                    showNotification('Error fetching users: ' + data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function renderStats(stats) {
        if (workersCount) workersCount.textContent = stats.workers_count;
        if (usersWithAccount) usersWithAccount.textContent = stats.users_with_account;
        if (usersWithoutAccount) usersWithoutAccount.textContent = stats.users_without_account;
    }

    function renderTable(users) {
        usersTableBody.innerHTML = '';

        if (users.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Nema korisnika.</td></tr>';
            return;
        }

        users.forEach(user => {
            const tr = document.createElement('tr');
            
            let typeBadge = '';
            if (user.Role_idRole == 2) {
                typeBadge = '<span class="status-badge status-confirmed">Radnik</span>';
            } else if (user.pass === null) {
                typeBadge = '<span class="status-badge status-cancelled">Bez naloga</span>';
            } else {
                typeBadge = '<span class="status-badge status-completed">Korisnik</span>';
            }

            tr.innerHTML = `
                <td>${typeBadge}</td>
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.last_name)}</td>
                <td>${escapeHtml(user.phone)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${user.appointment_count}</td>
                <td>
                    <button class="services-action-btn services-edit-btn" data-id="${user.idUser}" title="Uredi"><i class="fa-solid fa-pen"></i></button>
                    <button class="services-action-btn services-delete-btn" data-id="${user.idUser}" title="Obriši"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            
            // Attach event listeners to buttons
            const editBtn = tr.querySelector('.services-edit-btn');
            editBtn.addEventListener('click', () => openEditModal(user));

            const deleteBtn = tr.querySelector('.services-delete-btn');
            deleteBtn.addEventListener('click', () => openDeleteModal(user));

            usersTableBody.appendChild(tr);
        });
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function openEditModal(user) {
        editUserId.value = user.idUser;
        editUserRole.value = user.Role_idRole;
        editName.value = user.name;
        editLastName.value = user.last_name;
        editPhone.value = user.phone;
        editEmail.value = user.email;
        
        if (user.Role_idRole == 2) {
            editPictureGroup.style.display = 'block';
        } else {
            editPictureGroup.style.display = 'none';
        }

        editModal.classList.add('active');
    }

    function openDeleteModal(user) {
        userToDeleteId = user.idUser;
        document.getElementById('deleteUserName').textContent = user.name + ' ' + user.last_name;
        deleteModal.classList.add('active');
    }

    // Handle Edit Form Submit
    editUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(editUserForm);

        fetch('/backend/admin_update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAllModals();
                fetchUsers(); // Refresh table
                showNotification('Korisnik uspješno ažuriran.', 'success');
            } else {
                showNotification('Greška: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    });

    // Handle Delete Confirmation
    confirmDeleteBtn.addEventListener('click', function() {
        if (!userToDeleteId) return;

        fetch('/backend/admin_delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userToDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAllModals();
                fetchUsers(); // Refresh table
                showNotification('Korisnik uspješno obrisan.', 'success');
            } else {
                showNotification('Greška: ' + data.message, 'error');
                closeAllModals();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    });
});
