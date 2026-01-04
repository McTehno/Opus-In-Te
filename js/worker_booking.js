$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 6;
    
    let bookingData = {
        location: null,
        clientName: '',
        clientEmail: '',
        clientPhone: '',
        serviceId: null,
        serviceName: '',
        serviceDuration: 60,
        date: null,
        time: null,
        statusId: 1
    };

    // --- Navigation & Transitions ---

    function validateStep(step) {
        if (step === 1) {
            if (!bookingData.location) {
                alert('Molimo izaberite lokaciju.');
                return false;
            }
            return true;
        }
        if (step === 2) {
            const name = $('#clientName').val().trim();
            const email = $('#clientEmail').val().trim();
            const phone = $('#clientPhone').val().trim();
            
            if (!name || !email || !phone) {
                alert('Molimo unesite sve podatke o klijentu.');
                return false;
            }
            bookingData.clientName = name;
            bookingData.clientEmail = email;
            bookingData.clientPhone = phone;
            return true;
        }
        if (step === 3) {
            if (!bookingData.serviceId) {
                alert('Molimo izaberite uslugu.');
                return false;
            }
            return true;
        }
        if (step === 4) {
            if (!bookingData.date || !bookingData.time) {
                alert('Molimo izaberite datum i vrijeme.');
                return false;
            }
            return true;
        }
        return true;
    }

    function showStep(step) {
        // Validate before moving forward
        if (step > currentStep) {
            if (!validateStep(currentStep)) return;
        }

        // Update Progress Bar
        $('.step').removeClass('active completed');
        $('.step').each(function() {
            const s = parseInt($(this).data('step'));
            if (s === step) $(this).addClass('active');
            if (s < step) $(this).addClass('completed');
        });

        // Animate Progress Line
        // Calculate percentage: (step - 1) / (totalSteps - 1) * 100
        // But we have 4 steps. 
        // Step 1: 0%
        // Step 2: 33%
        // Step 3: 66%
        // Step 4: 100%
        const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
        gsap.to('.progress-bar-fill', {
            width: `${progressPercentage}%`,
            duration: 0.6, // Match transition speed (0.3s out + 0.3s in = 0.6s total roughly, or just slower for effect)
            ease: "power2.inOut"
        });

        // Animate Steps
        const currentStepEl = $(`.booking-step[data-step="${currentStep}"]`);
        const nextStepEl = $(`.booking-step[data-step="${step}"]`);

        if (step !== currentStep) {
            gsap.to(currentStepEl, {
                opacity: 0,
                x: step > currentStep ? -50 : 50,
                duration: 0.3,
                onComplete: () => {
                    currentStepEl.removeClass('active').hide();
                    nextStepEl.show().css({opacity: 0, x: step > currentStep ? 50 : -50}).addClass('active');
                    gsap.to(nextStepEl, {opacity: 1, x: 0, duration: 0.3});
                    
                    // Special animation for Calendar Step (Step 4)
                    if (step === 4) {
                        // Animate Calendar Grid
                        gsap.fromTo('.calendar-grid', 
                            { opacity: 0, y: 20 },
                            { opacity: 1, y: 0, duration: 0.5, delay: 0.1, ease: "power2.out" }
                        );
                        
                        // Animate Time Slots (Re-trigger animation)
                        const slots = $('.time-slot');
                        if (slots.length > 0) {
                            gsap.fromTo(slots, 
                                { opacity: 0, y: 15 },
                                { opacity: 1, y: 0, duration: 0.4, stagger: 0.05, ease: "power2.out", delay: 0.3 }
                            );
                        }
                    }
                }
            });
        }
        
        currentStep = step;
        updateSummary();
    }

    $('.btn-next').click(function() {
        if (currentStep < totalSteps) showStep(currentStep + 1);
    });

    $('.btn-prev').click(function() {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    // Allow clicking on progress bar steps if already visited or valid
    $('.step').click(function() {
        const step = parseInt($(this).data('step'));
        // Allow going back or forward if data is present
        let canNavigate = true;
        for(let i=1; i<step; i++) {
            // Simple check if data exists for previous steps
            if (i===1 && !bookingData.location) canNavigate = false;
            if (i===2 && (!$('#clientName').val() || !$('#clientEmail').val())) canNavigate = false;
            if (i===3 && !bookingData.serviceId) canNavigate = false;
            if (i===4 && (!bookingData.date || !bookingData.time)) canNavigate = false;
        }
        
        if (canNavigate) {
            showStep(step);
        }
    });


    // --- Step 1: Location Selection ---
    $('.location-btn').click(function() {
        $('.location-btn').css('border-color', '#ddd').css('background', 'white');
        $(this).css('border-color', 'var(--accent-color)').css('background', 'var(--bg-light)');
        
        bookingData.location = $(this).data('location');
        showStep(2);
    });


    // --- Step 2: Client Input (Handled in validation) ---
    // No specific JS needed for input fields other than validation
    // Button is enabled by default, validation happens on click


    // --- Step 3: Service Selection ---

    $('.accordion-header').click(function() {
        const item = $(this).parent();
        item.toggleClass('active');
        item.find('.accordion-content').slideToggle();
        $('.accordion-item').not(item).removeClass('active').find('.accordion-content').slideUp();
    });

    $('.service-card').click(function() {
        $('.service-card').removeClass('selected');
        $(this).addClass('selected');
        
        bookingData.serviceId = $(this).data('id');
        bookingData.serviceDuration = $(this).data('duration');
        bookingData.serviceName = $(this).find('h4').text();
        
        // Reload slots if date is selected (to account for new duration)
        if (bookingData.date) {
            loadSlots(bookingData.date);
        }

        $(`.booking-step[data-step="3"] .btn-next`).prop('disabled', false);
    });


    // --- Step 4: Calendar & Slots ---

    let currentDate = new Date();
    let selectedDate = null;
    const bosnianMonths = [
        "Januar", "Februar", "Mart", "April", "Maj", "Juni",
        "Juli", "Avgust", "Septembar", "Oktobar", "Novembar", "Decembar"
    ];

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        $('#monthYear').text(`${bosnianMonths[month]} ${year}`);
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = (firstDay.getDay() + 6) % 7; // Mon=0
        
        const grid = $('#calendarGrid');
        grid.empty();
        
        // Headers
        const days = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
        days.forEach(d => grid.append(`<div class="calendar-day-header">${d}</div>`));
        
        // Empty slots
        for (let i = 0; i < startingDay; i++) {
            grid.append('<div></div>');
        }
        
        // Days
        for (let i = 1; i <= daysInMonth; i++) {
            const dayDate = new Date(year, month, i);
            const dateStr = dayDate.toLocaleDateString('en-CA'); // YYYY-MM-DD
            
            const dayEl = $(`<div class="calendar-day" data-date="${dateStr}">${i}</div>`);
            
            // Highlight selected
            if (bookingData.date === dateStr) dayEl.addClass('selected');
            
            // Allow past dates (Worker privilege)
            // No restriction on past dates
            
            dayEl.click(function() {
                $('.calendar-day').removeClass('selected');
                $(this).addClass('selected');
                bookingData.date = dateStr;
                bookingData.time = null; // Reset time when date changes
                loadSlots(dateStr);
                $(`.booking-step[data-step="4"] .btn-next`).prop('disabled', true);
            });
            
            grid.append(dayEl);
        }
    }

    $('#prevMonth').click(() => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    $('#nextMonth').click(() => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    renderCalendar(currentDate);

    // Auto-select today's date
    const todayStr = new Date().toLocaleDateString('en-CA');
    bookingData.date = todayStr;
    // We need to wait for the calendar to render, then trigger click or just load slots
    // Since renderCalendar is synchronous, we can just find the element and click it
    $(`.calendar-day[data-date="${todayStr}"]`).click();

    function loadSlots(date) {
        const slotsContainer = $('#timeSlots');
        slotsContainer.html('<div class="loading-spinner"></div>');
        
        $.ajax({
            url: '/backend/worker_get_slots.php',
            method: 'GET',
            data: { 
                date: date,
                duration: bookingData.serviceDuration
            },
            success: function(slots) {
                slotsContainer.empty();
                if (slots.length === 0) {
                    slotsContainer.html('<p>Nema slobodnih termina.</p>');
                    return;
                }
                
                slots.forEach(time => {
                    const slot = $(`<div class="time-slot" style="opacity: 0; transform: translateY(10px);">${time}</div>`);
                    slot.click(function() {
                        $('.time-slot').removeClass('selected');
                        $(this).addClass('selected');
                        bookingData.time = time;
                        $(`.booking-step[data-step="4"] .btn-next`).prop('disabled', false);
                    });
                    slotsContainer.append(slot);
                });

                // Animate slots appearance
                gsap.to('.time-slot', {
                    opacity: 1,
                    y: 0,
                    duration: 0.4,
                    stagger: 0.05,
                    ease: "power2.out"
                });
            },
            error: function() {
                slotsContainer.html('<p>Greška pri učitavanju termina.</p>');
            }
        });
    }


    // --- Step 5: Finish ---

    function updateSummary() {
        $('#summaryLocation').text(bookingData.location);
        $('#summaryClient').text(bookingData.clientName);
        $('#summaryService').text(bookingData.serviceName);
        $('#summaryDate').text(bookingData.date);
        $('#summaryTime').text(bookingData.time);
    }

    $('.btn-finish').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Zakazivanje...');
        
        bookingData.statusId = $('#statusSelect').val();
        // bookingData.notes removed

        $.ajax({
            url: '/backend/worker_book_appointment.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(bookingData),
            success: function(response) {
                if (response.success) {
                    // Show success step
                    showStep(6);
                    
                    // Trigger Confetti
                    const duration = 3 * 1000;
                    const animationEnd = Date.now() + duration;
                    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
                    const brandColors = ['#C5A76A', '#3D3D3D', '#FFFFFF'];

                    function randomInRange(min, max) {
                        return Math.random() * (max - min) + min;
                    }

                    const interval = setInterval(function() {
                        const timeLeft = animationEnd - Date.now();

                        if (timeLeft <= 0) {
                            return clearInterval(interval);
                        }

                        const particleCount = 50 * (timeLeft / duration);
                        confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }, colors: brandColors }));
                        confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }, colors: brandColors }));
                    }, 250);

                } else {
                    alert('Greška: ' + response.message);
                    btn.prop('disabled', false).html('Zakaži Termin <i class="fas fa-check"></i>');
                }
            },
            error: function() {
                alert('Došlo je do greške na serveru.');
                btn.prop('disabled', false).html('Zakaži Termin <i class="fas fa-check"></i>');
            }
        });
    });

});


