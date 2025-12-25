document.addEventListener('DOMContentLoaded', function() {
    const usersTableBody = document.getElementById('usersTableBody');
    
    // Stats Elements
    const totalUsers = document.getElementById('totalUsers');
    const usersWithAccount = document.getElementById('usersWithAccount');
    const usersWithoutAccount = document.getElementById('usersWithoutAccount');

    // Modals
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    const closeButtons = document.querySelectorAll('.close-modal, .close-modal-btn');
    const modalTitle = document.getElementById('modalTitle');

    // Edit Form
    const editUserForm = document.getElementById('editUserForm');
    const editUserId = document.getElementById('editUserId');
    const editName = document.getElementById('editName');
    const editLastName = document.getElementById('editLastName');
    const editPhone = document.getElementById('editPhone');
    const editEmail = document.getElementById('editEmail');

    // Delete Actions
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let userToDeleteId = null;

    // Initial Fetch
    fetchUsers();

    // --- Functions ---

    function fetchUsers() {
        fetch('backend/admin_fetch_users.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderStats(data.stats);
                    renderTable(data.users);
                } else {
                    alert('Error fetching users: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function renderStats(stats) {
        totalUsers.textContent = stats.total_users;
        usersWithAccount.textContent = stats.users_with_account;
        usersWithoutAccount.textContent = stats.users_without_account;
    }

    function renderTable(users) {
        usersTableBody.innerHTML = '';

        if (users.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Nema korisnika.</td></tr>';
            return;
        }

        users.forEach(user => {
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
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
        editName.value = user.name;
        editLastName.value = user.last_name;
        editPhone.value = user.phone;
        editEmail.value = user.email;
        
        editModal.classList.add('active');
    }

    function openDeleteModal(user) {
        userToDeleteId = user.idUser;
        document.getElementById('deleteUserName').textContent = user.name + ' ' + user.last_name;
        deleteModal.classList.add('active');
    }

    function closeModal() {
        editModal.classList.remove('active');
        deleteModal.classList.remove('active');
        userToDeleteId = null;
    }

    // --- Event Listeners ---

    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === editModal || e.target === deleteModal) {
            closeModal();
        }
    });

    // Handle Edit Form Submit
    editUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(editUserForm);

        fetch('backend/admin_update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                fetchUsers(); // Refresh table
                // Optional: Show success message
            } else {
                alert('Greška: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Handle Delete Confirmation
    confirmDeleteBtn.addEventListener('click', function() {
        if (!userToDeleteId) return;

        fetch('backend/admin_delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userToDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                fetchUsers(); // Refresh table
            } else {
                alert('Greška: ' + data.message);
                closeModal();
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
