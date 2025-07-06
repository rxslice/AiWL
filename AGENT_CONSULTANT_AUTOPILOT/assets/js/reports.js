// assets/js/reports.js

(function($) {
    'use strict';
    
    // Initialize on document ready
    $(document).ready(function() {
        initReportsPage();
    });
    
    // Initialize reports page functionality
    function initReportsPage() {
        // Initialize AOS animations if available
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-
	            duration: 800,
                easing: 'ease-in-out',
                once: true
            });
        }
        
        // Handle filter form submissions
        $('.reports-filters form').on('submit', function(e) {
            // Prevent empty filter submissions
            const $industryFilter = $('#industry-filter');
            const $categoryFilter = $('#category-filter');
            
            if ($industryFilter.val() === '' && $categoryFilter.val() === '') {
                e.preventDefault();
                window.location.href = window.location.pathname;
            }
        });
        
        // Handle report card hover animations
        $('.report-card').each(function() {
            $(this).on('mouseenter', function() {
                $(this).find('.report-title a').css('color', 'var(--ai-winlab-secondary, #8e44ad)');
            }).on('mouseleave', function() {
                $(this).find('.report-title a').css('color', 'var(--ai-winlab-primary, #4a6cf7)');
            });
        });
        
        // Initialize single report page if we're on one
        if ($('.aiwinlab-report').length) {
            initSingleReportPage();
        }
    }
    
    // Initialize single report page functionality
    function initSingleReportPage() {
        // Navigation scroll behavior
        $('.report-navigation a').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            const $targetElement = $(target);
            
            if ($targetElement.length) {
                $('html, body').animate({
                    scrollTop: $targetElement.offset().top - 100
                }, 500);
                
                $('.report-navigation a').removeClass('active');
                $(this).addClass('active');
            }
        });
        
        // Update active navigation on scroll
        $(window).on('scroll', function() {
            let currentSection = '';
            
            $('.report-section').each(function() {
                const sectionTop = $(this).offset().top - 120;
                const sectionHeight = $(this).height();
                const scrollPosition = $(window).scrollTop();
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    currentSection = '#' + $(this).attr('id');
                }
            });
            
            if (currentSection !== '') {
                $('.report-navigation a').removeClass('active');
                $('.report-navigation a[href="' + currentSection + '"]').addClass('active');
            }
        });
        
        // Consultation modal functionality
        $('#schedule-consultation').on('click', function() {
            $('#consultation-modal').addClass('show');
        });
        
        $('.close-modal').on('click', function() {
            $('#consultation-modal').removeClass('show');
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('#consultation-modal')) {
                $('#consultation-modal').removeClass('show');
            }
        });
        
        // Submit consultation form
        $('#consultation-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('.submit-btn');
            const originalBtnText = $submitBtn.text();
            
            // Validate form fields
            let isValid = true;
            $form.find('[required]').each(function() {
                if ($(this).val() === '') {
                    $(this).addClass('invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('invalid');
                }
            });
            
            if (!isValid) {
                return;
            }
            
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> ' + aiWinLabData.messages.submitting);
            
            const formData = {
                action: 'aiwinlab_schedule_consultation',
                nonce: aiWinLabData.nonce,
                consultationData: {
                    reportId: $form.find('input[name="reportId"]').val(),
                    consultName: $form.find('input[name="consultName"]').val(),
                    consultEmail: $form.find('input[name="consultEmail"]').val(),
                    consultPhone: $form.find('input[name="consultPhone"]').val(),
                    consultDate: $form.find('input[name="consultDate"]').val(),
                    consultTime: $form.find('input[name="consultTime"]').val(),
                    consultNotes: $form.find('textarea[name="consultNotes"]').val()
                }
            };
            
            $.ajax({
                url: aiWinLabData.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('.modal-body').html(`
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <h3>${aiWinLabData.messages.consultSuccess}</h3>
                                <p>${aiWinLabData.messages.consultThankYou}</p>
                            </div>
                        `);
                        
                        // Close modal after delay
                        setTimeout(function() {
                            $('#consultation-modal').removeClass('show');
                        }, 3000);
                    } else {
                        // Show error message
                        alert(aiWinLabData.messages.error + (response.data || ''));
                        $submitBtn.text(originalBtnText);
                    }
                },
                error: function() {
                    // Show error message
                    alert(aiWinLabData.messages.ajaxError);
                    $submitBtn.text(originalBtnText);
                }
            });
        });
    }
})(jQuery);