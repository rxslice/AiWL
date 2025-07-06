<?php
// templates/single-report.php
?>
<div class="aiwinlab-report">
    <div class="report-header">
        <div class="report-logo">
            <img src="<?php echo AIWINLAB_PLUGIN_URL; ?>assets/images/ai-winlab-logo.png" alt="AI WinLab">
        </div>
        <div class="report-title">
            <h1><?php _e('AI Implementation Analysis Report', 'aiwinlab'); ?></h1>
            <h2><?php echo esc_html(get_post_meta(get_the_ID(), 'business_name', true)); ?></h2>
            <div class="report-meta">
                <div class="meta-item"><i class="far fa-calendar-alt"></i> <?php echo get_the_date(); ?></div>
                <div class="meta-item"><i class="far fa-file-alt"></i> <?php _e('Report', 'aiwinlab'); ?> #<?php echo get_the_ID(); ?></div>
            </div>
        </div>
    </div>
    
    <div class="report-navigation">
        <ul>
            <li><a href="#executive-summary"><?php _e('Executive Summary', 'aiwinlab'); ?></a></li>
            <li><a href="#business-analysis"><?php _e('Business Analysis', 'aiwinlab'); ?></a></li>
            <li><a href="#ai-opportunities"><?php _e('AI Opportunities', 'aiwinlab'); ?></a></li>
            <li><a href="#implementation-roadmap"><?php _e('Implementation Roadmap', 'aiwinlab'); ?></a></li>
            <li><a href="#roi-projection"><?php _e('ROI Projection', 'aiwinlab'); ?></a></li>
            <li><a href="#next-steps"><?php _e('Next Steps', 'aiwinlab'); ?></a></li>
        </ul>
    </div>
    
    <div class="report-content">
        <?php
        // Get report data
        $report_data = get_post_meta(get_the_ID(), 'analysis_result', true);
        $structured_analysis = isset($report_data['structured_analysis']) ? $report_data['structured_analysis'] : array();
        $visualizations = isset($report_data['visualizations']) ? $report_data['visualizations'] : array();
        ?>
        
        <!-- Executive Summary Section -->
        <section id="executive-summary" class="report-section">
            <div class="section-header">
                <h2><?php _e('Executive Summary', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content" id="executive-summary-content">
                <?php
                if (isset($structured_analysis['executive_summary']) && !empty($structured_analysis['executive_summary']['content'])) {
                    echo wp_kses_post(wpautop($structured_analysis['executive_summary']['content']));
                } else {
                    _e('Executive summary not available.', 'aiwinlab');
                }
                ?>
            </div>
        </section>
        
        <!-- Business Analysis Section -->
        <section id="business-analysis" class="report-section">
            <div class="section-header">
                <h2><?php _e('Business Analysis', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content">
                <div class="analysis-matrix" id="business-analysis-matrix">
                    <?php
                    if (isset($structured_analysis['business_analysis']) && !empty($structured_analysis['business_analysis']['content'])) {
                        echo wp_kses_post(wpautop($structured_analysis['business_analysis']['content']));
                    } else {
                        _e('Business analysis not available.', 'aiwinlab');
                    }
                    ?>
                </div>
            </div>
        </section>
        
        <!-- AI Opportunities Section -->
        <section id="ai-opportunities" class="report-section">
            <div class="section-header">
                <h2><?php _e('AI Implementation Opportunities', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content">
                <div class="opportunities-container">
                    <div class="opportunity-radar-chart">
                        <canvas id="radar-chart"></canvas>
                    </div>
                    <div class="opportunities-list" id="ai-opportunities-list">
                        <?php
                        if (isset($structured_analysis['ai_opportunities']) && !empty($structured_analysis['ai_opportunities']['content'])) {
                            echo wp_kses_post(wpautop($structured_analysis['ai_opportunities']['content']));
                        } else {
                            _e('AI opportunities analysis not available.', 'aiwinlab');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Implementation Roadmap Section -->
        <section id="implementation-roadmap" class="report-section">
            <div class="section-header">
                <h2><?php _e('Implementation Roadmap', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content">
                <div class="roadmap-timeline" id="implementation-timeline">
                    <?php
                    if (isset($structured_analysis['implementation_roadmap']) && !empty($structured_analysis['implementation_roadmap']['content'])) {
                        echo wp_kses_post(wpautop($structured_analysis['implementation_roadmap']['content']));
                    } else {
                        _e('Implementation roadmap not available.', 'aiwinlab');
                    }
                    ?>
                </div>
            </div>
        </section>
        
        <!-- ROI Projection Section -->
        <section id="roi-projection" class="report-section">
            <div class="section-header">
                <h2><?php _e('ROI Projection', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content">
                <div class="roi-chart-container">
                    <canvas id="roi-chart"></canvas>
                </div>
                <div class="roi-explanation" id="roi-explanation">
                    <?php
                    if (isset($structured_analysis['roi_projection']) && !empty($structured_analysis['roi_projection']['content'])) {
                        echo wp_kses_post(wpautop($structured_analysis['roi_projection']['content']));
                    } else {
                        _e('ROI projection not available.', 'aiwinlab');
                    }
                    ?>
                </div>
            </div>
        </section>
        
        <!-- AI Solutions Comparison Section -->
        <section id="solutions-comparison" class="report-section">
            <div class="section-header">
                <h2><?php _e('Recommended AI Solutions', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content">
                <div class="solutions-table-container" id="solutions-table">
                    <?php
                    if (isset($structured_analysis['solutions_comparison']) && !empty($structured_analysis['solutions_comparison']['content'])) {
                        echo wp_kses_post(wpautop($structured_analysis['solutions_comparison']['content']));
                    } else {
                        _e('Solutions comparison not available.', 'aiwinlab');
                    }
                    ?>
                </div>
            </div>
        </section>
        
        <!-- Next Steps Section -->
        <section id="next-steps" class="report-section">
            <div class="section-header">
                <h2><?php _e('Next Steps', 'aiwinlab'); ?></h2>
            </div>
            <div class="section-content" id="next-steps-content">
                <?php
                if (isset($structured_analysis['next_steps']) && !empty($structured_analysis['next_steps']['content'])) {
                    echo wp_kses_post(wpautop($structured_analysis['next_steps']['content']));
                } else {
                    _e('Next steps recommendations not available.', 'aiwinlab');
                }
                ?>
            </div>
        </section>
    </div>
    
    <div class="report-actions">
        <button id="download-report" class="download-btn">
            <i class="fas fa-download"></i> <?php _e('Download Full Report', 'aiwinlab'); ?>
        </button>
        <button id="schedule-consultation" class="consult-btn">
            <i class="fas fa-calendar-check"></i> <?php _e('Schedule Implementation Consultation', 'aiwinlab'); ?>
        </button>
    </div>
    
    <!-- Consultation Modal -->
    <div id="consultation-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php _e('Schedule Your Implementation Consultation', 'aiwinlab'); ?></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p><?php _e('Let\'s schedule a personalized consultation with our AI implementation experts to discuss your custom roadmap.', 'aiwinlab'); ?></p>
                
                <form id="consultation-form">
                    <?php wp_nonce_field('aiwinlab_nonce', 'consultation_nonce'); ?>
                    <input type="hidden" name="reportId" value="<?php echo get_the_ID(); ?>">
                    
                    <div class="form-field">
                        <label for="consultName"><?php _e('Your Name', 'aiwinlab'); ?></label>
                        <input type="text" id="consultName" name="consultName" required value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'business_name', true)); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="consultEmail"><?php _e('Email Address', 'aiwinlab'); ?></label>
                        <input type="email" id="consultEmail" name="consultEmail" required value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'business_email', true)); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="consultPhone"><?php _e('Phone Number', 'aiwinlab'); ?></label>
                        <input type="tel" id="consultPhone" name="consultPhone">
                    </div>
                    
                    <div class="form-field">
                        <label for="consultDate"><?php _e('Preferred Date', 'aiwinlab'); ?></label>
                        <input type="date" id="consultDate" name="consultDate" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="consultTime"><?php _e('Preferred Time', 'aiwinlab'); ?></label>
                        <select id="consultTime" name="consultTime" required>
                            <option value=""><?php _e('Select a time', 'aiwinlab'); ?></option>
                            <option value="morning"><?php _e('Morning (9am - 12pm)', 'aiwinlab'); ?></option>
                            <option value="afternoon"><?php _e('Afternoon (1pm - 5pm)', 'aiwinlab'); ?></option>
                            <option value="evening"><?php _e('Evening (6pm - 8pm)', 'aiwinlab'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="consultNotes"><?php _e('Additional Notes', 'aiwinlab'); ?></label>
                        <textarea id="consultNotes" name="consultNotes" rows="3" placeholder="<?php _e('Any specific topics you\'d like to discuss?', 'aiwinlab'); ?>"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn"><?php _e('Schedule Consultation', 'aiwinlab'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Initialize visualizations
        <?php if (!empty($visualizations)): ?>
        
        // Create opportunity radar chart
        <?php if (isset($visualizations['opportunity_radar'])): ?>
        AIWinLabVisualizations.createRadarChart('radar-chart', <?php echo json_encode($visualizations['opportunity_radar']); ?>);
        <?php endif; ?>
        
        // Create ROI projection chart
        <?php if (isset($visualizations['roi_projection'])): ?>
        AIWinLabVisualizations.createRoiChart('roi-chart', <?php echo json_encode($visualizations['roi_projection']); ?>);
        <?php endif; ?>
        
        // Create implementation timeline
        <?php if (isset($visualizations['implementation_timeline'])): ?>
        AIWinLabVisualizations.createImplementationTimeline('implementation-timeline', <?php echo json_encode($visualizations['implementation_timeline']); ?>);
        <?php endif; ?>
        
        // Create solutions comparison table
        <?php if (isset($visualizations['solution_comparison'])): ?>
        AIWinLabVisualizations.createSolutionsTable('solutions-table', <?php echo json_encode($visualizations['solution_comparison']); ?>);
        <?php endif; ?>
        
        <?php endif; ?>
        
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
        
        // Download report functionality
        $('#download-report').on('click', function() {
            const $downloadBtn = $(this);
            const originalBtnText = $downloadBtn.html();
            $downloadBtn.html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Generating PDF...', 'aiwinlab'); ?>');
            
            // Use html2pdf.js to generate PDF
            const element = document.querySelector('.report-content');
            const opt = {
                margin:       [10, 20, 10, 20],
                filename:     '<?php echo esc_js(get_post_meta(get_the_ID(), 'business_name', true)) . '_AI_Analysis_Report.pdf'; ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generate the PDF
            html2pdf().set(opt).from(element).save().then(function() {
                $downloadBtn.html('<i class="fas fa-check"></i> <?php _e('PDF Generated!', 'aiwinlab'); ?>');
                
                // Reset button after a delay
                setTimeout(function() {
                    $downloadBtn.html(originalBtnText);
                }, 3000);
            });
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
            
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Submitting...', 'aiwinlab'); ?>');
            
            const formData = {
                action: 'aiwinlab_schedule_consultation',
                nonce: $('#consultation_nonce').val(),
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
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('.modal-body').html(`
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <h3><?php _e('Consultation Scheduled!', 'aiwinlab'); ?></h3>
                                <p><?php _e('Thank you for scheduling a consultation. We\'ll contact you shortly to confirm your appointment.', 'aiwinlab'); ?></p>
                            </div>
                        `);
                        
                        // Close modal after delay
                        setTimeout(function() {
                            $('#consultation-modal').removeClass('show');
                        }, 3000);
                    } else {
                        // Show error message
                        alert('<?php _e('Error scheduling consultation: ', 'aiwinlab'); ?>' + response.data);
                        $submitBtn.text(originalBtnText);
                    }
                },
                error: function() {
                    // Show error message
                    alert('<?php _e('An error occurred. Please try again.', 'aiwinlab'); ?>');
                    $submitBtn.text(originalBtnText);
                }
            });
        });
    });
    </script>
</div>