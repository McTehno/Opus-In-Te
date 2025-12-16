document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.step');
    const bookingSteps = document.querySelectorAll('.booking-step');
    const progressBarLine = document.querySelector('.progress-bar-line');
    let currentStep = 1;

    // --- Data Store ---
    const bookingDetails = {
        location: null,
        date: null, // Display string
        dateISO: null, // YYYY-MM-DD for backend
        time: null,
        service: null,
        serviceId: null,
        price: null,
        duration: null, // minutes
        workerId: null,
        name: null,
        email: null,
        phone: null
    };

    const navigateToStep = (stepNumber) => {
        // Prevent navigation away from the final step
        if (currentStep === 5) return;

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

        // Step 4: Autofill if user is logged in
        if (currentStep === 4 && typeof loggedInUser !== 'undefined' && loggedInUser) {
            const nameInput = document.getElementById('fullName');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');

            if (nameInput && !nameInput.value) nameInput.value = loggedInUser.name + ' ' + loggedInUser.last_name;
            if (emailInput && !emailInput.value) emailInput.value = loggedInUser.email;
            if (phoneInput && !phoneInput.value) phoneInput.value = loggedInUser.phone;
        }

        // --- FINAL FIX: Trigger Confetti Animation ---
        if (currentStep === 5) {
            // A little firework burst to celebrate!
            const duration = 1.5 * 1000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
            const brandColors = ['#C5A76A', '#FDFBF6', '#3D3D3D', '#FFFFFF'];

            function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                return clearInterval(interval);
            }

            const particleCount = 50 * (timeLeft / duration);
            // since particles fall down, start a bit higher than random
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }, colors: brandColors }));
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }, colors: brandColors }));
            }, 250);
        }
    };

    const updateProgressBar = () => {
        steps.forEach((step, index) => {
            const stepNumber = index + 1;

            // NEW: Disable past steps if on the final step
            if (currentStep === 5 && stepNumber < 5) {
                step.classList.add('disabled');
            } else {
                step.classList.remove('disabled');
            }

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

        const progressWidth = ((currentStep - 1) / (steps.length - 1)) * 100;
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
            // NEW: Check if the step is disabled
            if (step.classList.contains('disabled')) return;

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

    // Step 2: Services
    document.querySelectorAll('.service-header').forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            item.classList.toggle('active');
        });
    });

    document.querySelectorAll('.service-option').forEach(option => {
        option.addEventListener('click', () => {
            bookingDetails.service = option.dataset.service;
            bookingDetails.serviceId = option.dataset.id;
            bookingDetails.price = option.dataset.price;
            bookingDetails.duration = parseInt(option.dataset.durationVal) || 60; // Default 60 if missing
            navigateToStep(3);
        });
    });

    // Step 3: Calendar & Time (New Two-Panel Logic)
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

    let currentDate = new Date(); // Start from today

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
            
            // Format for API: YYYY-MM-DD
            const year = selectedDate.getFullYear();
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const dayStr = String(selectedDate.getDate()).padStart(2, '0');
            bookingDetails.dateISO = `${year}-${month}-${dayStr}`;

            timeSlotsTitle.textContent = `Termini za ${bookingDetails.date}`;
            timeSlotsList.innerHTML = '<div class="loading-slots"><i class="fas fa-spinner fa-spin"></i> Učitavanje termina...</div>';

            // Fetch slots from backend
            fetch(`backend/get_slots.php?date=${bookingDetails.dateISO}&duration=${bookingDetails.duration}`)
                .then(response => response.json())
                .then(slots => {
                    timeSlotsList.innerHTML = ''; // Clear loading
                    
                    if (slots.length > 0) {
                        slots.forEach(slot => {
                            const slotHTML = `
                                <div class="appointment-slot ${!slot.available ? 'disabled' : ''}" 
                                     data-time="${slot.time}" 
                                     data-worker-id="${slot.worker_id}">
                                    <div class="therapist-photo">
                                        <img src="${slot.worker_image}" alt="${slot.worker_name}">
                                    </div>
                                    <div class="therapist-details">
                                        <span>${slot.worker_name}</span>
                                    </div>
                                    <div class="appointment-time">${slot.time}</div>
                                </div>
                            `;
                            timeSlotsList.insertAdjacentHTML('beforeend', slotHTML);
                        });
                    } else {
                        timeSlotsList.innerHTML = `<p style="text-align: center; padding: 20px;">Nažalost, nema slobodnih termina za izabrani dan.</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching slots:', error);
                    timeSlotsList.innerHTML = `<p style="text-align: center; padding: 20px; color: red;">Greška prilikom učitavanja termina.</p>`;
                });
        }
    });

    timeSlotsList.addEventListener('click', e => {
        const slot = e.target.closest('.appointment-slot');
        if (slot && !slot.classList.contains('disabled')) {
            bookingDetails.time = slot.dataset.time;
            bookingDetails.workerId = slot.dataset.workerId;
            
            // Visual feedback
            document.querySelectorAll('.appointment-slot').forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');

            setTimeout(() => navigateToStep(4), 300);
        }
    });

    renderCalendar(); // Initial render

    // Step 4: Details
    document.querySelector('.form-next-btn').addEventListener('click', () => {
        const name = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;

        if(name && email && phone) {
            bookingDetails.name = name;
            bookingDetails.email = email;
            bookingDetails.phone = phone;

            // Send booking to backend
            const btn = document.querySelector('.form-next-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch('backend/book_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(bookingDetails)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    navigateToStep(5);
                } else {
                    alert('Greška prilikom rezervacije: ' + (data.message || 'Nepoznata greška'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error booking:', error);
                alert('Došlo je do greške. Molimo pokušajte ponovo.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });

        } else {
            alert('Molimo Vas popunite sva polja.');
        }
    });

    // Initialize first step
    updateProgressBar();
});