document.addEventListener('DOMContentLoaded', () => {
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYear = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const appointmentList = document.getElementById('appointment-list');
    const detailsPanelTitle = document.getElementById('details-panel-title');
    const calendarView = document.querySelector('.calendar-view');
    const appointmentListView = document.querySelector('.appointment-list-view');
    const backToCalendarBtn = document.querySelector('.back-to-calendar-btn');

    let currentDate = new Date(); // Start from current date
    
    // Parse appointments from PHP
    // userAppointments is defined in UserDashboard.php
    const appointments = userAppointments || [];

    const monthNames = [
        "Januar", "Februar", "Mart", "April", "Maj", "Juni",
        "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"
    ];

    const renderCalendar = () => {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthYear.textContent = `${monthNames[month]} ${year}`;

        calendarGrid.innerHTML = '';

        // Days of week header
        const daysOfWeek = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        daysOfWeek.forEach(day => {
            calendarGrid.insertAdjacentHTML('beforeend', `<div class="calendar-day-header">${day}</div>`);
        });

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Adjust for Monday start (0 = Sunday, 1 = Monday, ...)
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < dayOffset; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        for (let day = 1; day <= daysInMonth; day++) {
            const loopDate = new Date(year, month, day);
            let classes = ['calendar-day'];
            
            // Check if there are appointments on this day
            // Fix: Construct date string manually to avoid timezone shifts from toISOString()
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const hasAppointment = appointments.some(app => app.datetime.startsWith(dateString));

            if (hasAppointment) {
                classes.push('has-appointment');
            }

            if (loopDate.getTime() === today.getTime()) {
                classes.push('today');
            }

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

            // Filter appointments for this day
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
                const statusLabel = getStatusLabel(app.status_name);
                
                // Handle duration display
                const durationDisplay = app.duration 
                    ? `<i class="fas fa-hourglass-half"></i> ${app.duration} min` 
                    : `<i class="fas fa-clock"></i> Po dogovoru`;

                const itemHTML = `
                    <div class="appointment-card">
                        <div class="appointment-left">
                             <div class="appointment-time">
                                ${time}
                             </div>
                             <div class="appointment-line"></div>
                        </div>
                        <div class="appointment-right">
                            <h4 class="appointment-service">${app.type_name}</h4>
                            <div class="appointment-meta">
                                <span class="meta-item">${durationDisplay}</span>
                            </div>
                            <div class="appointment-status-wrapper">
                                <span class="status-badge ${statusClass}">${statusLabel}</span>
                            </div>
                        </div>
                    </div>
                `;
                appointmentList.insertAdjacentHTML('beforeend', itemHTML);
            });
        } else {
            appointmentList.innerHTML = `<p class="no-appointments">Nema zakazanih termina za ovaj dan.</p>`;
        }

        // Switch view
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

    const getStatusLabel = (status) => {
        switch(status) {
            case 'potvrđeno': return '<i class="fas fa-check"></i> Potvrđeno';
            case 'nepotvrđeno': return '<i class="fas fa-hourglass-start"></i> Na čekanju';
            case 'otkazano': return '<i class="fas fa-times"></i> Otkazano';
            case 'završeno': return '<i class="fas fa-check-double"></i> Završeno';
            default: return status;
        }
    };

    renderCalendar();
});
