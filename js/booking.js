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
            case 0: return true;
            case 1: return !!bookingDetails.location;
            case 2: return !!bookingDetails.service;
            case 3: return !!bookingDetails.date && !!bookingDetails.time;
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

// Step 2: Calendar & Time (New Two-Panel Logic)
const calendarGrid = document.querySelector('.calendar-grid');
const monthYearEl = document.getElementById('monthYear');
const prevMonthBtn = document.getElementById('prevMonth');
const nextMonthBtn = document.getElementById('nextMonth');
const timeSlotsList = document.getElementById('timeSlotsList');
const timeSlotsTitle = document.getElementById('timeSlotsTitle');
const bosnianMonths = [
        "Januar", "Februar", "Mart", "April", "Maj", "Juni",
        "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"
    ];

let currentDate = new Date(2025, 8, 10); // Today is Sep 10, 2025

const renderCalendar = () => {
    calendarGrid.innerHTML = '';
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
        let classes = ['calendar-day'];
        if (loopDate < today) {
            classes.push('disabled');
        }
        if (loopDate.getTime() === today.getTime()) {
            classes.push('today');
        }
        calendarGrid.insertAdjacentHTML('beforeend', `<div class="${classes.join(' ')}" data-day="${day}">${day}</div>`);
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
    if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {
        const currentSelection = document.querySelector('.calendar-day.selected');
        if (currentSelection) {
            currentSelection.classList.remove('selected');
        }
        e.target.classList.add('selected');
        
        const day = e.target.dataset.day;
        const selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        bookingDetails.date = selectedDate.toLocaleDateString('bs-BA');
        
        timeSlotsTitle.textContent = `Termini za ${bookingDetails.date}`;

        // MOCK DATA: In a real app, you would fetch this from a server
        const appointments = [
            { time: '09:00', available: true }, { time: '10:00', available: false },
            { time: '11:00', available: true }, { time: '12:00', available: true },
            { time: '14:00', available: true }, { time: '15:00', available: false },
            { time: '16:00', available: true },
        ];
        
        timeSlotsList.innerHTML = ''; // Clear placeholder or previous list
        
        if (appointments.filter(a => a.available).length > 0) {
             appointments.forEach(app => {
                const slotHTML = `
                    <div class="appointment-slot ${!app.available ? 'disabled' : ''}" data-time="${app.time}">
                        <div class="therapist-photo">
                            <img src="img/vanjapic/indexpic.jpg" alt="Vanja Dejanović">
                        </div>
                        <div class="therapist-details">
                            <span>Vanja Dejanović</span>
                        </div>
                        <div class="appointment-time">${app.time}</div>
                    </div>
                `;
                timeSlotsList.insertAdjacentHTML('beforeend', slotHTML);
            });
        } else {
             timeSlotsList.innerHTML = `<p style="text-align: center; padding: 20px;">Nažalost, nema slobodnih termina za izabrani dan.</p>`;
        }
    }
});

timeSlotsList.addEventListener('click', e => {
    const slot = e.target.closest('.appointment-slot');
    if (slot && !slot.classList.contains('disabled')) {
        bookingDetails.time = slot.dataset.time;
        setTimeout(() => navigateToStep(4), 300);
    }
});

renderCalendar(); // Initial render
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
            navigateToStep(3);
        });
    });

    // Step 4: Details
document.querySelector('.form-next-btn').addEventListener('click', () => {
    const name = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    if(name && email) {
        bookingDetails.name = name;
        bookingDetails.email = email;
        navigateToStep(5);
    } else {
        alert('Molimo Vas popunite obavezna polja.');
    }
});

    

    // Initialize first step
    updateProgressBar();
});