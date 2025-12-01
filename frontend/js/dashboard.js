document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYearEl = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const appointmentList = document.getElementById('appointment-list');
    const detailsPanelTitle = document.getElementById('details-panel-title');
    const welcomeMessage = document.getElementById('welcome-message');
    const profileNameEl = document.getElementById('profile-name');
    const profileEmailEl = document.getElementById('profile-email');
    const profilePhoneEl = document.getElementById('profile-phone');
    const calendarView = document.querySelector('.calendar-view');
    const appointmentListView = document.querySelector('.appointment-list-view');
    const backToCalendarBtn = document.querySelector('.back-to-calendar-btn');

    // --- Placeholder Data ---
    const user = { // Encapsulate user data
        firstName: "Vanja",
        lastName: "Dejanović",
        email: "vanja.d@opusinte.ba", // Placeholder
        phone: "+387 65 123 456" // Placeholder
    };

    // Populate Welcome & Profile
    welcomeMessage.textContent = `Dobrodošli, ${user.firstName}!`;
    profileNameEl.textContent = `${user.firstName} ${user.lastName}`;
    profileEmailEl.textContent = user.email;
    profilePhoneEl.textContent = user.phone || 'Nije unešeno'; // Handle missing phone

    // Placeholder Appointments (Date format: YYYY-MM-DD)
    const userAppointments = {
        "2025-10-28": [
            { time: "10:00", service: "Individualna psihoterapija", id: 1 },
            { time: "14:00", service: "Psihološko savjetovanje", id: 2 }
        ],
        "2025-11-05": [
            { time: "11:00", service: "Individualna psihoterapija", id: 3 }
        ],
         "2025-11-15": [
            { time: "09:00", service: "Online psihoterapija", id: 4 }
        ]
    };

    // --- Calendar Logic (Mostly Unchanged) ---
    const bosnianMonths = [
        "Januar", "Februar", "Mart", "April", "Maj", "Juni",
        "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"
    ];
    let currentDate = new Date(2025, 9, 23); // Current date: Oct 23, 2025

    const renderCalendar = () => {
        calendarGrid.innerHTML = ''; // Clear previous grid
        const dayNames = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        dayNames.forEach(day => {
            calendarGrid.insertAdjacentHTML('beforeend', `<div class="day-name">${day}</div>`);
        });

        const month = currentDate.getMonth();
        const year = currentDate.getFullYear();
        monthYearEl.textContent = `${bosnianMonths[month]} ${year}`;

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
            const dateString = loopDate.toISOString().split('T')[0];

            let classes = ['calendar-day'];
            if (loopDate < today) {
                classes.push('disabled');
            }
             if (loopDate.getTime() === today.getTime()) {
                classes.push('today');
            }
            if (userAppointments[dateString]) {
                classes.push('has-appointment');
            }

            calendarGrid.insertAdjacentHTML('beforeend',
                `<div class="${classes.join(' ')}" data-date="${dateString}">${day}</div>`
            );
        }
    };

    const changeMonth = (offset) => {
        calendarGrid.classList.add('fading');
        setTimeout(() => {
            currentDate.setMonth(currentDate.getMonth() + offset);
            renderCalendar();
            // --- Reset to Calendar View when month changes ---
            switchToCalendarView();
            // --- ---
            calendarGrid.classList.remove('fading');
        }, 300);
    };

    prevMonthBtn.addEventListener('click', () => changeMonth(-1));
    nextMonthBtn.addEventListener('click', () => changeMonth(1));

    // --- View Switching Logic ---
    const switchToAppointmentView = () => {
        calendarView.classList.remove('active-view');
        appointmentListView.classList.add('active-view');
    };

   const switchToCalendarView = () => {
        // 1. Start the transition by removing the active class from the list view
        appointmentListView.classList.remove('active-view');
        // 2. Add the active class to the calendar view so it starts sliding in
        calendarView.classList.add('active-view');

        // 3. IMPORTANT: Delay clearing the list content and title until *after*
        //    the transition duration (500ms as defined in CSS).
        setTimeout(() => {
            appointmentList.innerHTML = `<p class="no-appointments">Izaberite dan na kalendaru sa označenim terminom.</p>`;
            detailsPanelTitle.textContent = `Termini za Izabrani Dan`;
        }, 500); // Match this duration to your CSS transition time

        // 4. Remove selection highlight from calendar (can happen immediately)
        const currentSelection = document.querySelector('.calendar-day.selected');
        if (currentSelection) {
            currentSelection.classList.remove('selected');
        }
    };

    backToCalendarBtn.addEventListener('click', switchToCalendarView);

    // --- Appointment Display & Transition Trigger ---
    calendarGrid.addEventListener('click', e => {
        // Only proceed if a non-disabled day with appointments is clicked
        if (e.target.classList.contains('calendar-day') &&
            !e.target.classList.contains('disabled') &&
            e.target.classList.contains('has-appointment')) { // Added check for appointments

            const currentSelection = document.querySelector('.calendar-day.selected');
            if (currentSelection) {
                currentSelection.classList.remove('selected');
            }
            e.target.classList.add('selected');

            const selectedDateStr = e.target.dataset.date;
            const selectedDateObj = new Date(selectedDateStr + 'T00:00:00');
            // --- Manual Date Formatting ---
            const day = String(selectedDateObj.getDate()).padStart(2, '0'); // Get day with leading zero
            const monthIndex = selectedDateObj.getMonth(); // Get month index (0-11)
            const year = selectedDateObj.getFullYear();
            const formattedDate = `${day}. ${bosnianMonths[monthIndex]} ${year}`; // Construct the string
            // --- End Manual Date Formatting ---

            detailsPanelTitle.textContent = `Termini za ${formattedDate}`;

            const appointments = userAppointments[selectedDateStr];
            appointmentList.innerHTML = ''; // Clear previous

            if (appointments && appointments.length > 0) {
                appointments.forEach((app, index) => {
                    const appointmentHTML = `
                        <div class="appointment-item" style="animation-delay: ${index * 100}ms" data-id="${app.id}">
                            <div class="appointment-info">
                                <span class="app-time">${app.time}</span>
                                <span class="app-service">${app.service}</span>
                            </div>
                            <button class="remove-appointment-btn" aria-label="Otkaži termin">Otkaži</button>
                        </div>
                    `;
                    appointmentList.insertAdjacentHTML('beforeend', appointmentHTML);
                });
                // --- Switch to the appointment list view ---
                switchToAppointmentView();
                // --- ---
            } else {
                 // Should not happen due to the 'has-appointment' check, but good fallback
                appointmentList.innerHTML = `<p class="no-appointments">Nema zakazanih termina za ovaj dan.</p>`;
                // Don't switch view if there are no appointments to show
                 switchToAppointmentView(); // Or maybe keep calendar view? Your choice. Added for consistency.
            }
        } else if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {
            // Handle clicking a day *without* appointments (optional)
            // Maybe just highlight it briefly or clear the details panel if it was open?
            const currentSelection = document.querySelector('.calendar-day.selected');
            if (currentSelection) {
                currentSelection.classList.remove('selected');
            }
             e.target.classList.add('selected'); // Select the day even if no appointments
            // If the appointment list view is active, switch back
            if (appointmentListView.classList.contains('active-view')) {
                 switchToCalendarView();
            }
             // Optionally display a message in the (hidden) appointment list area
             appointmentList.innerHTML = `<p class="no-appointments">Nema zakazanih termina za ovaj dan.</p>`;
             detailsPanelTitle.textContent = `Termini za ${new Date(e.target.dataset.date + 'T00:00:00').toLocaleDateString('bs-BA', { day: 'numeric', month: 'long', year: 'numeric' })}`;


        }
    });

    // --- Appointment Removal Logic (Unchanged) ---
    appointmentList.addEventListener('click', e => {
        if (e.target.classList.contains('remove-appointment-btn')) {
            const appointmentItem = e.target.closest('.appointment-item');
            const appointmentId = appointmentItem.dataset.id;

            if (confirm("Da li ste sigurni da želite otkazati ovaj termin?")) {
                // TODO: Add backend call here later
                appointmentItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease, max-height 0.3s ease 0.3s, margin 0.3s ease 0.3s, padding 0.3s ease 0.3s';
                appointmentItem.style.opacity = '0';
                appointmentItem.style.transform = 'scale(0.95)';
                appointmentItem.style.maxHeight = '0';
                appointmentItem.style.padding = '0 20px';
                appointmentItem.style.marginBottom = '0';

                console.log(`Placeholder: Remove appointment with ID: ${appointmentId}`);

                setTimeout(() => {
                    appointmentItem.remove();
                    if (appointmentList.children.length === 0) {
                        appointmentList.innerHTML = `<p class="no-appointments">Nema više termina za ovaj dan.</p>`;
                        // Optional: Go back to calendar if list becomes empty
                        // switchToCalendarView();
                    }
                }, 600);
            }
        }
    });

    // --- Initial Render ---
    renderCalendar();
    // Ensure Calendar view is active on load
    calendarView.classList.add('active-view');
    appointmentListView.classList.remove('active-view');


});