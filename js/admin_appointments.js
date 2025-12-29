document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filtersSidebar = document.getElementById('filtersSidebar');
    const toggleFiltersBtn = document.getElementById('toggleFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const appointmentsList = document.getElementById('appointmentsList');
    const resultCount = document.getElementById('resultCount');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // Toggle Filters on Mobile
    if (toggleFiltersBtn) {
        toggleFiltersBtn.addEventListener('click', () => {
            filtersSidebar.classList.toggle('active');
        });
    }

    // Event Listeners for Filters
    const filterInputs = document.querySelectorAll('.filter-location, .filter-status, .filter-type, .filter-duration, .filter-price');
    filterInputs.forEach(input => {
        input.addEventListener('change', fetchAppointments);
    });

    // Search Input (Debounced)
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchAppointments, 300);
    });

    // Reset Filters
    resetFiltersBtn.addEventListener('click', () => {
        filterInputs.forEach(input => input.checked = false);
        searchInput.value = '';
        fetchAppointments();
    });

    // Initial Fetch
    fetchAppointments();

    function fetchAppointments() {
        // Show Spinner
        appointmentsList.style.opacity = '0.5';
        loadingSpinner.style.display = 'block';

        // Collect Filter Data
        const filters = {
            search: searchInput.value,
            locations: getCheckedValues('.filter-location'),
            statuses: getCheckedValues('.filter-status'),
            types: getCheckedValues('.filter-type'),
            durations: getCheckedValues('.filter-duration'),
            prices: getCheckedValues('.filter-price')
        };

        fetch('backend/admin_fetch_appointments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(filters)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAppointments(data.data);
                resultCount.innerText = `Pronađeno rezultata: ${data.count}`;
            } else {
                console.error('Error:', data.message);
                appointmentsList.innerHTML = '<li class="no-results">Greška pri učitavanju podataka.</li>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            appointmentsList.innerHTML = '<li class="no-results">Greška u komunikaciji sa serverom.</li>';
        })
        .finally(() => {
            appointmentsList.style.opacity = '1';
            loadingSpinner.style.display = 'none';
        });
    }

    function getCheckedValues(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(input => input.value);
    }

    function renderAppointments(appointments) {
        appointmentsList.innerHTML = '';

        if (appointments.length === 0) {
            appointmentsList.innerHTML = '<li class="no-results">Nema termina koji odgovaraju kriterijumima.</li>';
            return;
        }

        appointments.forEach(appt => {
            const li = document.createElement('li');
            li.className = 'appointment-item';
            
            // Doctor Name & Pic (Placeholder logic similar to dashboard)
            const docName = appt.doc_name ? `${appt.doc_name} ${appt.doc_lastname}` : 'Nepoznato';
            const patName = appt.pat_name ? `${appt.pat_name} ${appt.pat_lastname}` : 'Nepoznato';
            
            li.innerHTML = `
                <div class="col-doctor">
                    <div class="doctor-details">
                        <span class="doctor-name">${docName}</span>
                    </div>
                </div>
                <div class="col-patient">
                    <i class="fa-regular fa-user" style="margin-right: 5px; color: #C5A76A;"></i>
                    ${patName}
                </div>
                <div class="col-info">
                    <div class="info-row"><i class="fa-solid fa-tag"></i> ${appt.price} KM</div>
                    <div class="info-row"><i class="fa-regular fa-clock"></i> ${appt.duration ? appt.duration + ' min' : '/'}</div>
                    <div class="info-row type-name">${appt.type_name}</div>
                    <div class="info-row date-time">
                        <i class="fa-regular fa-calendar"></i> ${appt.formatted_date} 
                        <span style="margin-left: 5px; color: #999;">${appt.formatted_time}</span>
                    </div>
                </div>
                <div class="col-location">
                    <i class="fa-solid fa-map-marker-alt" style="color: #C5A76A;"></i> ${appt.location_display}
                </div>
                <div class="col-status">
                    <span class="status-badge status-${appt.status_name}">${appt.status_display}</span>
                </div>
                <div class="col-actions">
                    <button class="action-btn btn-edit" onclick="editAppointment(${appt.idAppointment})" title="Uredi">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteAppointment(${appt.idAppointment})" title="Obriši">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;
            appointmentsList.appendChild(li);
        });
    }

    // --- Modal Logic ---
    let currentDeleteId = null;
    let currentEditId = null;
    let currentEditDuration = 60; // Default
    let currentEditDate = null;

    // Expose functions to global scope
    window.deleteAppointment = function(id) {
        currentDeleteId = id;
        openModal('deleteModal');
    };

    window.editAppointment = function(id) {
        currentEditId = id;
        openModal('editModal');
        loadAppointmentDetails(id);
    };

    window.closeModal = function(modalId) {
        document.getElementById(modalId).classList.remove('active');
    };

    window.openModal = function(modalId) {
        document.getElementById(modalId).classList.add('active');
    };

    // Delete Confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!currentDeleteId) return;
        
        fetch('backend/admin_delete_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('deleteModal');
                fetchAppointments(); // Refresh list
                showNotification('Termin uspješno obrisan.', 'success');
            } else {
                showNotification('Greška pri brisanju: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    });

    // --- Edit Logic ---
    
    // Save Confirmation
    document.getElementById('saveEditBtn').addEventListener('click', function(e) {
        e.preventDefault();
        // Validate
        if (!document.getElementById('editDate').value || !document.getElementById('editTime').value) {
            showNotification('Molimo izaberite datum i vrijeme.', 'warning');
            return;
        }
        openModal('saveConfirmModal');
    });

    document.getElementById('confirmSaveBtn').addEventListener('click', function() {
        const data = {
            id: currentEditId,
            typeId: document.getElementById('editType').value,
            statusId: document.getElementById('editStatus').value,
            locationId: document.getElementById('editLocation').value,
            date: document.getElementById('editDate').value,
            time: document.getElementById('editTime').value
        };

        fetch('backend/admin_update_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('saveConfirmModal');
                closeModal('editModal');
                fetchAppointments();
                showNotification('Termin uspješno ažuriran.', 'success');
            } else {
                showNotification('Greška pri čuvanju: ' + data.message, 'error');
                closeModal('saveConfirmModal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    });

    // Load Details
    function loadAppointmentDetails(id) {
        fetch('backend/admin_get_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const appt = data.data;
                
                // Set Status
                document.getElementById('editStatus').value = appt.Appointment_Status_idAppointment_Status;
                
                // Set Location
                const locVal = appt.Address_idAddress === null ? 'NULL' : appt.Address_idAddress;
                document.getElementById('editLocation').value = locVal;

                // Set Type & Filter Types
                const currentDuration = parseInt(appt.duration) || 0;
                populateTypeSelect(currentDuration, appt.Appointment_Type_idAppointment_Type);
                
                // Set Date & Time
                currentEditDate = new Date(appt.date);
                
                // Set hidden inputs BEFORE rendering calendar so it picks up the selection
                document.getElementById('editDate').value = appt.date;
                document.getElementById('editTime').value = appt.time;
                
                // Update Title
                document.getElementById('timeSlotsTitle').textContent = `Termini za ${appt.date}`;

                renderCalendar();
                
                // Load slots for that day
                loadTimeSlots(appt.date, currentDuration, appt.time);

            } else {
                showNotification('Greška: ' + data.message, 'error');
                closeModal('editModal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    }

    function populateTypeSelect(maxDuration, selectedId) {
        const select = document.getElementById('editType');
        select.innerHTML = '';
        
        // Filter types: same or smaller duration (or null duration if logic implies)
        // User said: "picking between the ones with the same or smaller (can be null) duration"
        // Assuming "can be null" means if the type has NULL duration, it's allowed? Or if maxDuration is null?
        // Let's assume we filter types where type.duration <= maxDuration OR type.duration is null
        
        // Note: appointmentTypes is a global variable from PHP
        appointmentTypes.forEach(type => {
            const typeDur = type.duration ? parseInt(type.duration) : 0;
            // If maxDuration is 0 (e.g. was null), maybe allow all? Or if typeDur is 0 (null)?
            // Let's stick to: if typeDur <= maxDuration (if maxDuration > 0).
            // If maxDuration is 0 (undefined duration), maybe we shouldn't restrict?
            // Let's allow if typeDur <= maxDuration OR typeDur === 0
            
            if (maxDuration === 0 || typeDur <= maxDuration || typeDur === 0) {
                const option = document.createElement('option');
                option.value = type.idAppointment_Type;
                option.textContent = `${type.name} (${type.duration ? type.duration + ' min' : '/'})`;
                if (type.idAppointment_Type == selectedId) option.selected = true;
                select.appendChild(option);
            }
        });
        
        // Update current duration when type changes (to refresh slots?)
        // Actually, if we change type to a shorter one, slots might still be valid.
        // But if we change type, we might want to refresh slots if the duration matters for slot availability.
        select.onchange = function() {
            const newTypeId = this.value;
            const newType = appointmentTypes.find(t => t.idAppointment_Type == newTypeId);
            if (newType) {
                currentEditDuration = newType.duration ? parseInt(newType.duration) : 60;
                // Refresh slots with new duration
                const dateVal = document.getElementById('editDate').value;
                if (dateVal) {
                    loadTimeSlots(dateVal, currentEditDuration, document.getElementById('editTime').value);
                }
            }
        };
    }

    // --- Calendar Logic ---
    const calendarGrid = document.getElementById('calendarGrid');
    const monthYearEl = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const bosnianMonths = ["Januar", "Februar", "Mart", "April", "Maj", "Juni", "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"];
    
    // Initialize calendar date if not set
    if (!currentEditDate) currentEditDate = new Date();

    function renderCalendar() {
        calendarGrid.innerHTML = '';
        const dayNames = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        dayNames.forEach(day => {
            calendarGrid.insertAdjacentHTML('beforeend', `<div class="day-name">${day}</div>`);
        });

        const year = currentEditDate.getFullYear();
        const month = currentEditDate.getMonth();
        monthYearEl.textContent = `${bosnianMonths[month]} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Adjust for Monday start (0=Sun, 1=Mon...)
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < dayOffset; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        const today = new Date();
        today.setHours(0,0,0,0);
        
        // Check selected date from hidden input
        const selectedDateStr = document.getElementById('editDate').value;

        for (let day = 1; day <= daysInMonth; day++) {
            const loopDate = new Date(year, month, day);
            // Fix: Construct date string manually to avoid UTC shift from toISOString()
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            let classes = ['calendar-day'];
            // if (loopDate < today) classes.push('disabled'); // Allow picking past dates for admin? Maybe not.
            
            if (selectedDateStr === dateStr) classes.push('selected');
            if (loopDate.getTime() === today.getTime()) classes.push('today');

            const dayEl = document.createElement('div');
            dayEl.className = classes.join(' ');
            dayEl.textContent = day;
            dayEl.dataset.date = dateStr;
            
            dayEl.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;
                
                document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                
                document.getElementById('editDate').value = this.dataset.date;
                document.getElementById('timeSlotsTitle').textContent = `Termini za ${this.dataset.date}`;
                
                loadTimeSlots(this.dataset.date, currentEditDuration);
            });

            calendarGrid.appendChild(dayEl);
        }
    }

    prevMonthBtn.addEventListener('click', () => {
        currentEditDate.setMonth(currentEditDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        currentEditDate.setMonth(currentEditDate.getMonth() + 1);
        renderCalendar();
    });

    // --- Create Modal Logic ---
    let currentCreateDate = new Date();
    let currentCreateDuration = 60;

    window.openCreateModal = function() {
        openModal('createModal');
        renderCreateCalendar();
    };

    const createCalendarGrid = document.getElementById('createCalendarGrid');
    const createMonthYearEl = document.getElementById('createMonthYear');
    const createPrevMonthBtn = document.getElementById('createPrevMonth');
    const createNextMonthBtn = document.getElementById('createNextMonth');

    function renderCreateCalendar() {
        createCalendarGrid.innerHTML = '';
        const dayNames = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        dayNames.forEach(day => {
            createCalendarGrid.insertAdjacentHTML('beforeend', `<div class="day-name">${day}</div>`);
        });

        const year = currentCreateDate.getFullYear();
        const month = currentCreateDate.getMonth();
        createMonthYearEl.textContent = `${bosnianMonths[month]} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < dayOffset; i++) {
            createCalendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        const today = new Date();
        today.setHours(0,0,0,0);
        const selectedDateStr = document.getElementById('createDate').value;

        for (let day = 1; day <= daysInMonth; day++) {
            const loopDate = new Date(year, month, day);
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            let classes = ['calendar-day'];
            if (selectedDateStr === dateStr) classes.push('selected');
            if (loopDate.getTime() === today.getTime()) classes.push('today');

            const dayEl = document.createElement('div');
            dayEl.className = classes.join(' ');
            dayEl.textContent = day;
            dayEl.dataset.date = dateStr;
            
            dayEl.addEventListener('click', function() {
                document.querySelectorAll('#createCalendarGrid .calendar-day').forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                
                document.getElementById('createDate').value = this.dataset.date;
                document.getElementById('createTimeSlotsTitle').textContent = `Termini za ${this.dataset.date}`;
                
                const workerId = document.getElementById('createWorker').value;
                loadTimeSlots(this.dataset.date, currentCreateDuration, null, 'createTimeSlotsList', 'createTime', workerId);
            });

            createCalendarGrid.appendChild(dayEl);
        }
    }

    createPrevMonthBtn.addEventListener('click', () => {
        currentCreateDate.setMonth(currentCreateDate.getMonth() - 1);
        renderCreateCalendar();
    });

    createNextMonthBtn.addEventListener('click', () => {
        currentCreateDate.setMonth(currentCreateDate.getMonth() + 1);
        renderCreateCalendar();
    });

    // Create Form Listeners
    document.getElementById('createType').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const duration = selectedOption.getAttribute('data-duration');
        currentCreateDuration = duration ? parseInt(duration) : 60;
        
        const dateVal = document.getElementById('createDate').value;
        const workerId = document.getElementById('createWorker').value;
        if (dateVal) {
            loadTimeSlots(dateVal, currentCreateDuration, null, 'createTimeSlotsList', 'createTime', workerId);
        }
    });

    document.getElementById('createWorker').addEventListener('change', function() {
        const dateVal = document.getElementById('createDate').value;
        if (dateVal) {
            loadTimeSlots(dateVal, currentCreateDuration, null, 'createTimeSlotsList', 'createTime', this.value);
        }
    });

    document.getElementById('saveCreateBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const data = {
            workerId: document.getElementById('createWorker').value,
            typeId: document.getElementById('createType').value,
            patientName: document.getElementById('createName').value,
            patientEmail: document.getElementById('createEmail').value,
            patientPhone: document.getElementById('createPhone').value,
            locationId: document.getElementById('createLocation').value,
            statusId: document.getElementById('createStatus').value,
            date: document.getElementById('createDate').value,
            time: document.getElementById('createTime').value
        };

        if (!data.workerId || !data.typeId || !data.patientName || !data.patientEmail || !data.date || !data.time) {
            showNotification('Molimo popunite sva obavezna polja.', 'warning');
            return;
        }

        fetch('backend/admin_create_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('createModal');
                fetchAppointments();
                // Reset form
                document.getElementById('createForm').reset();
                document.getElementById('createDate').value = '';
                document.getElementById('createTime').value = '';
                document.getElementById('createTimeSlotsList').innerHTML = '';
                showNotification('Termin uspješno kreiran.', 'success');
            } else {
                showNotification('Greška: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Došlo je do greške.', 'error');
        });
    });

    function loadTimeSlots(date, duration, preselectedTime = null, listId = 'timeSlotsList', inputId = 'editTime', workerId = null) {
        const list = document.getElementById(listId);
        list.innerHTML = '<div style="grid-column: 1/-1; text-align: center;"><i class="fas fa-spinner fa-spin"></i></div>';
        
        let url = `backend/get_slots.php?date=${date}&duration=${duration}`;
        if (workerId) url += `&worker_id=${workerId}`;

        fetch(url)
            .then(res => res.json())
            .then(slots => {
                list.innerHTML = '';
                if (slots.length === 0) {
                    list.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #999;">Nema slobodnih termina.</div>';
                    return;
                }

                slots.forEach(slot => {
                    const btn = document.createElement('div');
                    btn.className = 'time-slot-btn';
                    btn.textContent = slot.time;
                    
                    let isSelected = false;
                    if (preselectedTime && slot.time === preselectedTime) {
                        isSelected = true;
                        btn.classList.add('selected');
                        slot.available = true; 
                    }
                    
                    if (!slot.available && !isSelected) {
                        btn.classList.add('disabled');
                    } else {
                        btn.addEventListener('click', function() {
                            list.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
                            this.classList.add('selected');
                            document.getElementById(inputId).value = slot.time;
                        });
                    }
                    
                    list.appendChild(btn);
                });
            });
    }
});

