document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.step');
    const bookingSteps = document.querySelectorAll('.booking-step');
    const progressBarLine = document.querySelector('.progress-bar-line');
    let currentStep = 1;

    // --- Data Store ---
    const bookingDetails = {
        location: null,
        date: null,
        time: null,
        service: null,
        price: null,
        name: null,
        email: null,
    };

    const navigateToStep = (stepNumber) => {
        // Prevent going to a future step that isn't the next one
        if (stepNumber > currentStep && !isStepCompleted(currentStep)) return;
        
        currentStep = stepNumber;
        
        // Animate Steps
        bookingSteps.forEach(step => {
            step.classList.remove('active');
        });
        document.querySelector(`.booking-step[data-step="${currentStep}"]`).classList.add('active');
        
        // Update Progress Bar
        updateProgressBar();
    };

    // In js/booking.js

const updateProgressBar = () => {
    steps.forEach((step, index) => {
        const stepNumber = index + 1;
        if (stepNumber < currentStep) {
            step.classList.add('completed');
            step.classList.remove('active');
        } else if (stepNumber === currentStep) {
            step.classList.add('active');
            step.classList.remove('completed');
        } else {
            step.classList.remove('active', 'completed');
        }
    });

    // New, simpler width calculation
    const progressWidth = ((currentStep - 1) / (steps.length - 1)) * 100;

    // Set the CSS variable to trigger the smooth transition
    progressBarLine.style.setProperty('--progress-width', `${progressWidth}%`);
};

    const isStepCompleted = (stepNumber) => {
        switch(stepNumber) {
            case 1: return !!bookingDetails.location;
            case 2: return !!bookingDetails.date && !!bookingDetails.time;
            case 3: return !!bookingDetails.service;
            case 4: return !!bookingDetails.name && !!bookingDetails.email;
            default: return false;
        }
    };

    // --- Event Listeners ---
    steps.forEach(step => {
        step.addEventListener('click', () => {
            const stepToGo = parseInt(step.dataset.step);
            // Allow navigation only to completed steps or the current step
            if (isStepCompleted(stepToGo - 1) || stepToGo === currentStep) {
                navigateToStep(stepToGo);
            }
        });
    });

    // Step 1: Location
    document.querySelectorAll('.location-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            bookingDetails.location = btn.dataset.location;
            navigateToStep(2);
        });
    });

    // Step 2: Calendar & Time (Simplified version)
    // NOTE: A real-world app would use a library like date-fns for robust date logic.
    const calendarGrid = document.querySelector('.calendar-grid');
    const monthYearEl = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const timeSlotsContainer = document.querySelector('.time-slots');

    let currentDate = new Date(2025, 8, 10); // September 10, 2025

    const renderCalendar = () => {
        calendarGrid.innerHTML = `<div class="day-name">Pon</div><div class="day-name">Uto</div><div class="day-name">Sri</div><div class="day-name">Čet</div><div class="day-name">Pet</div><div class="day-name">Sub</div><div class="day-name">Ned</div>`;
        timeSlotsContainer.innerHTML = '<p>Izaberite datum da vidite termine.</p>';
        const month = currentDate.getMonth();
        const year = currentDate.getFullYear();
        
        monthYearEl.textContent = `${currentDate.toLocaleString('bs-BA', { month: 'long' })} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0=Sun, 1=Mon
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1; // Adjust for Monday start

        for(let i = 0; i < dayOffset; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', `<div></div>`);
        }

        for(let day = 1; day <= daysInMonth; day++) {
            const isPast = new Date(year, month, day) < new Date(2025, 8, 10); // Disable days before today
            const dayClass = isPast ? 'disabled' : '';
            calendarGrid.insertAdjacentHTML('beforeend', `<div class="calendar-day ${dayClass}" data-day="${day}">${day}</div>`);
        }
    };
    
    prevMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
    nextMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

    calendarGrid.addEventListener('click', e => {
        if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
            e.target.classList.add('selected');
            const day = e.target.dataset.day;
            const month = currentDate.getMonth() + 1;
            const year = currentDate.getFullYear();
            bookingDetails.date = `${day}.${month}.${year}`;
            
            // Mock time slots
            timeSlotsContainer.innerHTML = `
                <div class="therapist-info">
                    <img src="img/vanjapic/indexpic.jpg" alt="Vanja Dejanović">
                    <span>Vanja Dejanović</span>
                </div>
                <div class="time-slot" data-time="09:00">09:00</div>
                <div class="time-slot" data-time="10:00">10:00</div>
                <div class="time-slot" data-time="11:00">11:00</div>
                <div class="time-slot" data-time="14:00">14:00</div>
            `;
        }
    });

    timeSlotsContainer.addEventListener('click', e => {
        if (e.target.classList.contains('time-slot')) {
            document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
            e.target.classList.add('selected');
            bookingDetails.time = e.target.dataset.time;
            navigateToStep(3);
        }
    });

    renderCalendar();

    // Step 3: Services
    document.querySelectorAll('.service-header').forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            item.classList.toggle('active');
        });
    });

    document.querySelectorAll('.service-option').forEach(option => {
        option.addEventListener('click', () => {
            bookingDetails.service = option.dataset.service;
            bookingDetails.price = option.dataset.price;
            navigateToStep(4);
        });
    });

    // Step 4: Details
    document.querySelector('.form-next-btn').addEventListener('click', () => {
        const name = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        if(name && email) {
            bookingDetails.name = name;
            bookingDetails.email = email;
            populateConfirmation();
            navigateToStep(5);
        } else {
            alert('Molimo Vas popunite obavezna polja.');
        }
    });

    // Step 5: Confirmation
    const populateConfirmation = () => {
        document.getElementById('summary-location').textContent = bookingDetails.location;
        document.getElementById('summary-date').textContent = bookingDetails.date;
        document.getElementById('summary-time').textContent = bookingDetails.time;
        document.getElementById('summary-service').textContent = bookingDetails.service;
        document.getElementById('summary-price').textContent = bookingDetails.price;
        document.getElementById('summary-name').textContent = bookingDetails.name;
        document.getElementById('summary-email').textContent = bookingDetails.email;
    };
    
    document.querySelector('.confirm-btn').addEventListener('click', () => {
        alert('Termin je potvrđen! (Ovo je demo - stvarna funkcionalnost bi slala email/podatke na server)');
        // In a real app, you would submit the form data here.
    });


    // Initialize first step
    updateProgressBar();
});