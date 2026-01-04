$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    let bookingData = {
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
        if (step === 2) {
            if (!bookingData.serviceId) {
                alert('Molimo izaberite uslugu.');
                return false;
            }
            return true;
        }
        if (step === 3) {
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
            if (i===1 && (!$('#clientName').val() || !$('#clientEmail').val())) canNavigate = false;
            if (i===2 && !bookingData.serviceId) canNavigate = false;
            if (i===3 && (!bookingData.date || !bookingData.time)) canNavigate = false;
        }
        
        if (canNavigate) {
            showStep(step);
        }
    });


    // --- Step 1: Client Input (Handled in validation) ---
    // No specific JS needed for input fields other than validation
    // Button is enabled by default, validation happens on click


    // --- Step 2: Service Selection ---

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
        
        $(`.booking-step[data-step="2"] .btn-next`).prop('disabled', false);
    });


    // --- Step 3: Calendar & Slots ---

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
                $(`.booking-step[data-step="3"] .btn-next`).prop('disabled', true);
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

    function loadSlots(date) {
        const slotsContainer = $('#timeSlots');
        slotsContainer.html('<div class="loading-spinner"></div>');
        
        $.ajax({
            url: 'backend/worker_get_slots.php',
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
                    const slot = $(`<div class="time-slot">${time}</div>`);
                    slot.click(function() {
                        $('.time-slot').removeClass('selected');
                        $(this).addClass('selected');
                        bookingData.time = time;
                        $(`.booking-step[data-step="3"] .btn-next`).prop('disabled', false);
                    });
                    slotsContainer.append(slot);
                });
            },
            error: function() {
                slotsContainer.html('<p>Greška pri učitavanju termina.</p>');
            }
        });
    }


    // --- Step 4: Finish ---

    function updateSummary() {
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
            url: 'backend/worker_book_appointment.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(bookingData),
            success: function(response) {
                if (response.success) {
                    alert('Termin uspješno zakazan!');
                    window.location.href = 'WorkerDashboard.php';
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


