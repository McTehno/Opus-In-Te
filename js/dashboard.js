document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYearEl = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const appointmentList = document.getElementById('appointment-list');
    const detailsPanelTitle = document.getElementById('details-panel-title');
    const welcomeMessage = document.getElementById('welcome-message');

    // --- Placeholder Data ---
    const userName = "Vanja Dejanović"; // Replace with actual user data later
    welcomeMessage.textContent = `Dobrodošli, ${userName}!`;

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
        // Add more placeholder dates/appointments as needed
    };

    // --- Calendar Logic ---
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
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1; // Adjust for Monday start

        // Add empty divs for offset
        for (let i = 0; i < dayOffset; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalize today's date

        // Add day elements
        for (let day = 1; day <= daysInMonth; day++) {
            const loopDate = new Date(year, month, day);
            const dateString = loopDate.toISOString().split('T')[0]; // Format YYYY-MM-DD

            let classes = ['calendar-day'];
            if (loopDate < today) {
                classes.push('disabled'); // Mark past dates
            }
             if (loopDate.getTime() === today.getTime()) {
                classes.push('today'); // Mark today
            }
            // Check if there's an appointment on this day
            if (userAppointments[dateString]) {
                classes.push('has-appointment');
            }

            calendarGrid.insertAdjacentHTML('beforeend',
                `<div class="${classes.join(' ')}" data-date="${dateString}">${day}</div>`
            );
        }
    };

    const changeMonth = (offset) => {
        calendarGrid.classList.add('fading'); // For visual transition
        setTimeout(() => {
            currentDate.setMonth(currentDate.getMonth() + offset);
            renderCalendar();
            // Clear appointment details when month changes
            appointmentList.innerHTML = `<p class="no-appointments">Izaberite dan na kalendaru sa označenim terminom.</p>`;
            detailsPanelTitle.textContent = `Termini za Izabrani Dan`;
             // Remove selected state from any previously selected day
            const currentSelection = document.querySelector('.calendar-day.selected');
            if (currentSelection) {
                currentSelection.classList.remove('selected');
            }
            calendarGrid.classList.remove('fading');
        }, 300);
    };

    prevMonthBtn.addEventListener('click', () => changeMonth(-1));
    nextMonthBtn.addEventListener('click', () => changeMonth(1));

    // --- Appointment Display Logic ---
    calendarGrid.addEventListener('click', e => {
        if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {

             // Remove selected state from previously selected day
            const currentSelection = document.querySelector('.calendar-day.selected');
            if (currentSelection) {
                currentSelection.classList.remove('selected');
            }

            // Add selected state to clicked day
            e.target.classList.add('selected');

            const selectedDateStr = e.target.dataset.date;
            const selectedDateObj = new Date(selectedDateStr + 'T00:00:00'); // Ensure correct date object
            const formattedDate = selectedDateObj.toLocaleDateString('bs-BA', { day: 'numeric', month: 'long', year: 'numeric' });

            detailsPanelTitle.textContent = `Termini za ${formattedDate}`;

            const appointments = userAppointments[selectedDateStr];

            appointmentList.innerHTML = ''; // Clear previous list or placeholder

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
            } else {
                appointmentList.innerHTML = `<p class="no-appointments">Nema zakazanih termina za ovaj dan.</p>`;
            }
        }
    });

    // --- Appointment Removal Logic (Frontend Only) ---
    appointmentList.addEventListener('click', e => {
        if (e.target.classList.contains('remove-appointment-btn')) {
            const appointmentItem = e.target.closest('.appointment-item');
            const appointmentId = appointmentItem.dataset.id; // Get the ID

            // Confirmation (optional but recommended)
            if (confirm("Da li ste sigurni da želite otkazati ovaj termin?")) {
                // TODO: Add backend call here later to actually delete

                // --- Frontend removal ---
                appointmentItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease, max-height 0.3s ease 0.3s';
                appointmentItem.style.opacity = '0';
                appointmentItem.style.transform = 'scale(0.95)';
                 appointmentItem.style.maxHeight = '0';
                 appointmentItem.style.padding = '0 20px';
                 appointmentItem.style.marginBottom = '0';


                console.log(`Placeholder: Remove appointment with ID: ${appointmentId}`);

                // Optionally, update the userAppointments object (frontend state)
                // This requires finding the date and filtering the array
                 setTimeout(() => {
                    appointmentItem.remove();
                     // Check if list is now empty
                    if (appointmentList.children.length === 0) {
                         appointmentList.innerHTML = `<p class="no-appointments">Nema više termina za ovaj dan.</p>`;
                         // Optional: remove highlight from calendar day if no appointments left for it
                         // (Requires slightly more complex state management)
                    }
                }, 600); // Wait for animations to finish
            }
        }
    });

    // --- Initial Render ---
    renderCalendar();
});