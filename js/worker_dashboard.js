document.addEventListener('DOMContentLoaded', function() {
    // --- Modal Logic ---
    const modal = document.getElementById('editModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.btn-cancel');
    const editForm = document.getElementById('editAppointmentForm');
    
    // Function to open modal (will be attached to buttons dynamically)
    window.openEditModal = function(btn) {
        const id = btn.dataset.id;
        const status = btn.dataset.status;
        const type = btn.dataset.type;
        
        document.getElementById('appointmentId').value = id;
        document.getElementById('statusSelect').value = status;
        document.getElementById('typeSelect').value = type;
        
        modal.classList.add('active');
    };

    function closeModal() {
        modal.classList.remove('active');
    }

    if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    if(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    if(editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(editForm);
            const submitBtn = editForm.querySelector('.btn-save');
            const originalText = submitBtn.innerText;
            
            submitBtn.innerText = 'Saving...';
            submitBtn.disabled = true;

            fetch('backend/worker_update_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // --- Calendar Logic ---
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYear = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const appointmentList = document.getElementById('appointment-list');
    const detailsPanelTitle = document.getElementById('details-panel-title');
    const calendarView = document.querySelector('.calendar-view');
    const appointmentListView = document.querySelector('.appointment-list-view');
    const backToCalendarBtn = document.querySelector('.back-to-calendar-btn');

    if (!calendarGrid) return; // Exit if calendar elements are not present

    let currentDate = new Date();
    const appointments = workerAppointments || []; // Defined in PHP

    const monthNames = [
        "Januar", "Februar", "Mart", "April", "Maj", "Juni",
        "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"
    ];

    const renderCalendar = () => {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthYear.textContent = `${monthNames[month]} ${year}`;
        calendarGrid.innerHTML = '';

        const daysOfWeek = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        daysOfWeek.forEach(day => {
            calendarGrid.insertAdjacentHTML('beforeend', `<div class="calendar-day-header">${day}</div>`);
        });

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < dayOffset; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        for (let day = 1; day <= daysInMonth; day++) {
            const loopDate = new Date(year, month, day);
            let classes = ['calendar-day'];
            
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const hasAppointment = appointments.some(app => app.datetime.startsWith(dateString));

            if (hasAppointment) classes.push('has-appointment');
            if (loopDate.getTime() === today.getTime()) classes.push('today');

            calendarGrid.insertAdjacentHTML('beforeend', `<div class="${classes.join(' ')}" data-date="${dateString}">${day}</div>`);
        }
    };

    const changeMonth = (offset) => {
        calendarGrid.classList.add('fading');
        setTimeout(() => {
            currentDate.setMonth(currentDate.getMonth() + offset);
            renderCalendar();
            calendarGrid.classList.remove('fading');
        }, 300);
    };

    prevMonthBtn.addEventListener('click', () => changeMonth(-1));
    nextMonthBtn.addEventListener('click', () => changeMonth(1));

    calendarGrid.addEventListener('click', e => {
        if (e.target.classList.contains('calendar-day')) {
            const selectedDateStr = e.target.dataset.date;
            if (!selectedDateStr) return;

            const [year, month, day] = selectedDateStr.split('-').map(Number);
            const formattedDate = `${day}. ${monthNames[month - 1]} ${year}.`;
            const dayAppointments = appointments.filter(app => app.datetime.startsWith(selectedDateStr));

            showAppointmentDetails(dayAppointments, formattedDate);
        }
    });

    const showAppointmentDetails = (dayAppointments, dateStr) => {
        detailsPanelTitle.textContent = `Termini za ${dateStr}`;
        appointmentList.innerHTML = '';

        if (dayAppointments.length > 0) {
            dayAppointments.forEach(app => {
                const time = app.datetime.split(' ')[1].substring(0, 5);
                const statusClass = getStatusClass(app.status_name);
                
                // Worker specific details
                const clientName = (app.client_name || 'Nepoznato') + ' ' + (app.client_last_name || '');
                const clientPhone = app.client_phone || 'Nema broja';
                const location = app.street ? `${app.street} ${app.street_number}, ${app.city_name}` : 'Online / Nije navedeno';

                const itemHTML = `
                    <div class="appointment-card">
                        <div class="card-header">
                            <h3 class="client-name">${clientName}</h3>
                            <span class="appointment-status ${statusClass}">${app.status_name}</span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <i class="far fa-calendar-alt"></i>
                                <span>${time}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-notes-medical"></i>
                                <span>${app.type_name}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${location}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-phone"></i>
                                <span>${clientPhone}</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="edit-btn" 
                                    onclick="openEditModal(this)"
                                    data-id="${app.idAppointment}"
                                    data-status="${app.idAppointment_Status}"
                                    data-type="${app.idAppointment_Type}">
                                <i class="fas fa-edit"></i> Uredi
                            </button>
                        </div>
                    </div>
                `;
                appointmentList.insertAdjacentHTML('beforeend', itemHTML);
            });
        } else {
            appointmentList.innerHTML = `<p class="no-appointments">Nema zakazanih termina za ovaj dan.</p>`;
        }

        calendarView.classList.remove('active-view');
        appointmentListView.classList.add('active-view');
    };

    backToCalendarBtn.addEventListener('click', () => {
        appointmentListView.classList.remove('active-view');
        calendarView.classList.add('active-view');
    });

    const getStatusClass = (status) => {
        switch(status) {
            case 'potvrđeno': return 'status-potvrđeno';
            case 'nepotvrđeno': return 'status-nepotvrđeno';
            case 'otkazano': return 'status-otkazano';
            case 'završeno': return 'status-završeno';
            default: return '';
        }
    };

    renderCalendar();
});
