// assets/js/wizard.js

(function($) {
    'use strict';
    
    // Global variables
    let currentStep = 1;
    let analysisData = {};
    let reportData = {};
    let isAnalysisInProgress = false;
    
    // DOM ready
    $(document).ready(function() {
        initializeWizard();
        attachEventListeners();
        setupFormAnimations();
        initializeAOS();
    });
    
    // Initialize AOS animations
    function initializeAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false,
                disable: 'mobile'
            });
        }
    }
    
    // Initialize the wizard interface
    function initializeWizard() {
        updateProgressBar();
        
        // Set current date for report
        const currentDate = new Date();
        $('#report-date').text(currentDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }));
        
        // Generate a random report ID
        const reportId = 'AW' + Math.floor(100000 + Math.random() * 900000);
        $('#report-id').text(reportId);
        
        // Check for any prepopulated fields from URL parameters
        populateFromUrlParams();
        
        // Check browser compatibility
        checkBrowserCompatibility();
    }
    
    // Check browser compatibility for modern features
    function checkBrowserCompatibility() {
        // Check for features we need
        const hasPromise = typeof Promise !== 'undefined';
        const hasFetch = typeof fetch !== 'undefined';
        const hasLocalStorage = typeof localStorage !== 'undefined';
        
        if (!hasPromise || !hasFetch || !hasLocalStorage) {
            // Show compatibility warning
            $('#ai-winlab-wizard').prepend(`
                <div class="compatibility-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Your browser may not support all features of AI WinLab. For the best experience, please use a modern browser like Chrome, Firefox, Safari, or Edge.</p>
                    <button class="close-warning"><i class="fas fa-times"></i></button>
                </div>
            `);
            
            // Add close button functionality
            $('.close-warning').on('click', function() {
                $('.compatibility-warning').slideUp(300, function() {
                    $(this).remove();
                });
            });
        }
    }
    
    // Populate fields from URL parameters
    function populateFromUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Map URL parameters to form field IDs
        const paramMap = {
            'name': 'businessName',
            'email': 'businessEmail',
            'website': 'websiteUrl',
            'industry': 'industry'
        };
        
        // Fill in fields if parameters exist
        for (const [param, fieldId] of Object.entries(paramMap)) {
            if (urlParams.has(param)) {
                const value = urlParams.get(param);
                $('#' + fieldId).val(value).trigger('change');
                
                // Mark field as focused if it has a value
                if (value) {
                    $('#' + fieldId).parent().addClass('focused');
                }
            }
        }
    }
    
    // Attach event listeners to form elements
    function attachEventListeners() {
        // Next step buttons
        $('.next-step-btn').on('click', function() {
            const nextStep = $(this).data('next');
            if (validateCurrentStep()) {
                // Track step completion for analytics
                trackEvent('step_completed', { step: currentStep });
                goToStep(nextStep);
            }
        });
        
        // Previous step buttons
        $('.prev-step-btn').on('click', function() {
            const prevStep = $(this).data('prev');
            goToStep(prevStep);
        });
        
        // Start analysis button
        $('#start-analysis').on('click', function() {
            if (isAnalysisInProgress) {
                return;
            }
            
            if (validateCurrentStep()) {
                collectFormData();
                trackEvent('analysis_started', { 
                    business_name: analysisData.businessName,
                    industry: analysisData.industry
                });
                goToStep(5);
                startAnalysis();
            }
        });
        
        // File upload handling
        $('#supportingDocs').on('change', function() {
            handleFileUpload(this);
        });
        
        // Download report button
        $('#download-report').on('click', function() {
            generatePDF();
            trackEvent('report_downloaded', { 
                business_name: analysisData.businessName
            });
        });
        
        // Schedule consultation button
        $('#schedule-consultation').on('click', function() {
            showConsultationModal();
            trackEvent('consultation_opened', { 
                business_name: analysisData.businessName
            });
        });
        
        // Auto-save form data to localStorage
        $('input, textarea, select').on('change', function() {
            if (typeof localStorage !== 'undefined') {
                const fieldId = $(this).attr('id');
                const fieldValue = $(this).val();
                
                if (fieldId && fieldValue) {
                    localStorage.setItem('aiwinlab_' + fieldId, fieldValue);
                }
            }
        });
        
        // Add form validation on input
        $('input, textarea, select').on('input', function() {
            validateField($(this));
        });
    }
    
    // Track events for analytics
    function trackEvent(eventName, eventData = {}) {
        // Store in browser for backend collection on next AJAX call
        if (typeof localStorage !== 'undefined') {
            const events = JSON.parse(localStorage.getItem('aiwinlab_events') || '[]');
            events.push({
                event: eventName,
                data: eventData,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('aiwinlab_events', JSON.stringify(events));
        }
		 // Send event to backend if possible
        if (typeof aiWinLabData !== 'undefined') {
            $.ajax({
                url: aiWinLabData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiwinlab_track_event',
                    nonce: aiWinLabData.nonce,
                    event: eventName,
                    data: eventData
                },
                success: function(response) {
                    // Clear localStorage events if they were successfully tracked
                    if (response.success && typeof localStorage !== 'undefined') {
                        localStorage.removeItem('aiwinlab_events');
                    }
                }
            });
        }
    }
    
    // Setup form animations
    function setupFormAnimations() {
        // Add animation to form fields when focused
        $('.form-field input, .form-field textarea, .form-field select').on('focus', function() {
            $(this).parent().addClass('focused');
        }).on('blur', function() {
            if ($(this).val() === '') {
                $(this).parent().removeClass('focused');
            }
        });
        
        // Check for pre-filled fields
        $('.form-field input, .form-field textarea, .form-field select').each(function() {
            if ($(this).val() !== '') {
                $(this).parent().addClass('focused');
            }
        });
        
        // Try to restore saved data from localStorage
        if (typeof localStorage !== 'undefined') {
            $('input, textarea, select').each(function() {
                const fieldId = $(this).attr('id');
                if (fieldId) {
                    const savedValue = localStorage.getItem('aiwinlab_' + fieldId);
                    if (savedValue && $(this).val() === '') {
                        $(this).val(savedValue).trigger('change');
                        $(this).parent().addClass('focused');
                    }
                }
            });
        }
        
        // Range slider animations
        $('.range-slider-container input[type="range"]').on('input', function() {
            const value = $(this).val();
            const max = $(this).attr('max');
            const percentage = (value / max) * 100;
            
            $(this).css('background', `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) ${percentage}%, #e0e0e0 ${percentage}%, #e0e0e0 100%)`);
        }).trigger('input');
        
        // Add confetti effect to consultation submit button
        $('.submit-btn').on('mouseenter', function() {
            $(this).addClass('animated-btn');
        }).on('mouseleave', function() {
            $(this).removeClass('animated-btn');
        });
    }
    
    // Navigate to a specific step
    function goToStep(stepNumber) {
        $('.wizard-step').removeClass('active');
        $(`#step-${stepNumber}`).addClass('active');
        
        $('.progress-steps .step').removeClass('active completed');
        
        // Mark steps as active or completed
        for (let i = 1; i <= 6; i++) {
            if (i < stepNumber) {
                $(`.progress-steps .step[data-step="${i}"]`).addClass('completed');
            } else if (i === parseInt(stepNumber)) {
                $(`.progress-steps .step[data-step="${i}"]`).addClass('active');
            }
        }
        
        currentStep = parseInt(stepNumber);
        updateProgressBar();
        
        // Trigger AOS animations for the new step
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
        
        // Scroll to top of step
        $('html, body').animate({
            scrollTop: $('#ai-winlab-wizard').offset().top - 50
        }, 500);
        
        // Save current step to localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('aiwinlab_current_step', currentStep);
        }
    }
    
    // Update the progress bar
    function updateProgressBar() {
        const progress = ((currentStep - 1) / 5) * 100;
        $('.wizard-progress .progress-indicator').css('width', `${progress}%`);
    }
    
    // Validate the current step
    function validateCurrentStep() {
        let isValid = true;
        
        // Get all required fields in the current step
        const $requiredFields = $(`#step-${currentStep} [required]`);

        // Clear previous error messages
        $(`#step-${currentStep} .error-message`).remove();
        $(`#step-${currentStep} .invalid`).removeClass('invalid');
        
        // Validate each required field
        $requiredFields.each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        // Industry-specific validation for step 2
        if (currentStep === 2 && isValid) {
            // Make sure sales process has enough detail if we're in step 2
            const salesProcess = $('#salesProcess').val();
            if (salesProcess && salesProcess.length < 50) {
                $('#salesProcess').addClass('invalid');
                $('#salesProcess').parent().append('<div class="error-message">Please provide more detail about your sales process (at least 50 characters).</div>');
                isValid = false;
            }
        }
        
        if (!isValid) {
            // Shake animation for invalid fields
            $(`#step-${currentStep} .invalid`).addClass('shake');
            setTimeout(function() {
                $(`#step-${currentStep} .invalid`).removeClass('shake');
            }, 500);
            
            // Focus the first invalid field
            $(`#step-${currentStep} .invalid`).first().focus();
        }
        
        return isValid;
    }
    
    // Validate a single field
    function validateField($field) {
        // Skip validation for non-required fields that are empty
        if (!$field.prop('required') && $field.val() === '') {
            return true;
        }
        
        // Get field value
        const value = $field.val();
        const fieldType = $field.attr('type');
        const fieldName = $field.attr('name');
        
        // Basic required validation
        if ($field.prop('required') && value === '') {
            $field.addClass('invalid');
            $field.parent().find('.error-message').remove();
            $field.parent().append('<div class="error-message">This field is required</div>');
            return false;
        }
        
        // Email validation
        if (fieldType === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                $field.addClass('invalid');
                $field.parent().find('.error-message').remove();
                $field.parent().append('<div class="error-message">Please enter a valid email address</div>');
                return false;
            }
        }
        
        // URL validation
        if (fieldType === 'url' && value !== '') {
            try {
                new URL(value);
            } catch (e) {
                $field.addClass('invalid');
                $field.parent().find('.error-message').remove();
                $field.parent().append('<div class="error-message">Please enter a valid URL (include http:// or https://)</div>');
                return false;
            }
        }
        
        // Field is valid
        $field.removeClass('invalid');
        $field.parent().find('.error-message').remove();
        return true;
    }
    
    // Collect all form data
    function collectFormData() {
        // Basic information
        analysisData = {
            businessName: $('#businessName').val(),
            businessEmail: $('#businessEmail').val(),
            websiteUrl: $('#websiteUrl').val(),
            industry: $('#industry').val(),
            companySize: translateCompanySize($('#companySize').val()),
            revenueModel: $('#revenueModel').val(),
            salesProcess: $('#salesProcess').val(),
            businessProcesses: $('#businessProcesses').val(),
            painPoints: $('#painPoints').val(),
            currentTechnology: $('#currentTechnology').val(),
            technicalExpertise: $('#technicalExpertise').val(),
            
            // Advanced data
            priorities: collectPriorityData(),
            tools: collectToolData(),
            acquisitionChannels: collectAcquisitionData(),
            salesCycle: $('input[name="salesCycle"]:checked').val() || '',
            formTimestamp: new Date().toISOString()
        };
        
        // Update business name in report
        $('#business-name-display').text(analysisData.businessName);
    }
    
    // Translate company size value to text
    function translateCompanySize(value) {
        const sizes = ['1-10', '11-50', '51-200', '201-1000', '1000+'];
        return sizes[value - 1] || '1-10';
    }
    
    // Collect priority matrix data
    function collectPriorityData() {
        const priorities = {};
        
        $('[name^="priority_"]').each(function() {
            if ($(this).is(':checked')) {
                const priorityName = $(this).attr('name').replace('priority_', '');
                priorities[priorityName] = $(this).val();
            }
        });
        
        return priorities;
    }
    
    // Collect tools data
    function collectToolData() {
        const tools = {
            crm: $('input[name="crm"]:checked').val() || 'none',
            otherTools: []
        };
        
        $('input[name="tools[]"]:checked').each(function() {
            tools.otherTools.push($(this).val());
        });
        
        return tools;
    }
    
    // Collect acquisition channel data
    function collectAcquisitionData() {
        const channels = [];
        
        $('input[name="acquisition[]"]:checked').each(function() {
            channels.push($(this).val());
        });
        
        return channels;
    }
    
    // Handle file upload
    function handleFileUpload(input) {
        const files = input.files;
        const $fileList = $(input).closest('.file-upload-container').find('.file-list');
        
        $fileList.empty();
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileSize = (file.size / 1024).toFixed(1) + ' KB';
            let iconClass = 'fa-file';
            
            // Determine file icon based on type
            if (file.type.startsWith('image/')) {
                iconClass = 'fa-file-image';
            } else if (file.type.includes('pdf')) {
                iconClass = 'fa-file-pdf';
            } else if (file.type.includes('word') || file.type.includes('document')) {
                iconClass = 'fa-file-word';
            } else if (file.type.includes('excel') || file.type.includes('sheet')) {
                iconClass = 'fa-file-excel';
            } else if (file.type.includes('powerpoint') || file.type.includes('presentation')) {
                iconClass = 'fa-file-powerpoint';
            }
            
            const $fileItem = $(`
                <div class="file-item" data-aos="fade-left" data-aos-delay="${i * 100}">
                    <div class="file-icon"><i class="far ${iconClass}"></i></div>
                    <div class="file-info">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${fileSize}</div>
                    </div>
                    <div class="file-remove" data-index="${i}"><i class="fas fa-times"></i></div>
                </div>
            `);
            
            $fileList.append($fileItem);
        }
        
        // Add remove file functionality
        $('.file-remove').on('click', function() {
            // Remove file from FileList (would need a custom solution in production)
            // For the demo, just remove the visual element
            $(this).closest('.file-item').remove();
        });
        
        // Trigger AOS refresh for new elements
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    // Start the AI analysis process
    function startAnalysis() {
        isAnalysisInProgress = true;
        
        // Reset analysis UI
        $('.analysis-step').each(function() {
            $(this).find('.step-indicator i').removeClass().addClass('far fa-circle');
        });
        
        $('.analysis-step[data-step="1"] .step-indicator i').removeClass().addClass('fas fa-circle-notch fa-spin');
        $('.analysis-progress .progress-fill').css('width', '0%');
        $('.analysis-progress .progress-percentage').text('0%');
        $('.insight-snippet').text(aiWinLabData.messages.analyzing || 'Initializing analysis...');
        
        // Simulate analysis progress
        simulateAnalysisProgress();
        
        // Make AJAX call to start actual analysis
        $.ajax({
            url: aiWinLabData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'aiwinlab_analyze',
                nonce: aiWinLabData.nonce,
                analysisData: analysisData
            },
            success: function(response) {
                if (response.success) {
                    isAnalysisInProgress = false;
                    reportData = response.data;
                    trackEvent('analysis_completed', { 
                        business_name: analysisData.businessName,
                        report_id: response.report_id
                    });
                    prepareReportData();
                } else {
                    handleAnalysisError(response.data);
                }
            },
            error: function(xhr, status, error) {
                handleAnalysisError(error);
            }
        });
    }
    
    // Simulate analysis progress for better UX
    function simulateAnalysisProgress() {
        const steps = 7;
        let currentAnalysisStep = 1;
        let progress = 0;
        
        const insights = [
            "Analyzing business model structure...",
            "Mapping sales process workflow...",
            "Evaluating technology stack compatibility...",
            "Identifying AI integration opportunities...",
            "Calculating potential ROI for AI implementations...",
            "Generating custom implementation roadmap...",
            "Compiling visualization data...",
            "Finalizing comprehensive report...",
            "Identifying optimal AI tools for your specific needs...",
            "Evaluating workflow bottlenecks...",
            "Analyzing customer interaction touchpoints...",
            "Mapping data flow processes...",
            "Evaluating automation potential in your sales funnel...",
            "Calculating efficiency improvements with AI implementation..."
        ];
        
        const insightInterval = setInterval(function() {
            const randomInsight = insights[Math.floor(Math.random() * insights.length)];
            $('.insight-snippet').fadeOut(300, function() {
                $(this).text(randomInsight).fadeIn(300);
            });
        }, 3000);
        
        const progressInterval = setInterval(function() {
            progress += 1;
            
            if (progress % 15 === 0 && currentAnalysisStep < steps) {
                // Complete current step
                $(`.analysis-step[data-step="${currentAnalysisStep}"] .step-indicator i`).removeClass().addClass('fas fa-check-circle');
                
                // Start next step
                currentAnalysisStep++;
                $(`.analysis-step[data-step="${currentAnalysisStep}"] .step-indicator i`).removeClass().addClass('fas fa-circle-notch fa-spin');
            }
            
            $('.analysis-progress .progress-fill').css('width', `${progress}%`);
            $('.analysis-progress .progress-percentage').text(`${progress}%`);
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                clearInterval(insightInterval);
                
                // If actual response hasn't come back yet, keep last step spinning
                if (isAnalysisInProgress) {
                    $('.insight-snippet').text("Finalizing your personalized AI implementation report...");
                    $(`.analysis-step[data-step="${steps}"] .step-indicator i`).removeClass().addClass('fas fa-circle-notch fa-spin');
                } else {
                    // Complete all steps
                    $('.analysis-step .step-indicator i').removeClass().addClass('fas fa-check-circle');
                    
                    // After a brief delay, show the report
                    setTimeout(function() {
                        goToStep(6);
                    }, 1000);
                }
            }
        }, 100);
    }
    
    // Handle analysis error
    function handleAnalysisError(error) {
        isAnalysisInProgress = false;
        console.error('Analysis error:', error);
        
        // Update UI to show error
        $('.analyzing-title').text(aiWinLabData.messages.error || 'Analysis failed');
        $('.insight-snippet').text(error || 'Please try again or contact support if the problem persists.');
        $('.analysis-step .step-indicator i').removeClass().addClass('far fa-circle');
        $('.analysis-step[data-step="1"] .step-indicator i').removeClass().addClass('fas fa-times-circle');
        
        // Track error event
        trackEvent('analysis_error', { 
            business_name: analysisData.businessName,
            error: error
        });
        
        // Add a retry button
        $('.analysis-status').append(`
            <div class="analysis-retry-container">
                <button class="retry-analysis-btn">
                    <i class="fas fa-redo"></i> Retry Analysis
                </button>
            </div>
        `);
        
        // Add retry functionality
        $('.retry-analysis-btn').on('click', function() {
            // Remove retry button
            $('.analysis-retry-container').remove();
            
            // Reset analysis title
            $('.analyzing-title').text('AI Analysis in Progress');
            
            // Restart analysis
            startAnalysis();
        });
    }
    
    // Prepare the report data for display
    function prepareReportData() {
        const analysis = reportData.structured_analysis;
        const visualizations = reportData.visualizations;
        
        // Populate text sections
        $('#executive-summary-content').html(analysis.executive_summary.content);
        $('#business-analysis-matrix').html(analysis.business_analysis.content);
        $('#ai-opportunities-list').html(analysis.ai_opportunities.content);
        $('#roi-explanation').html(analysis.roi_projection.content);
        $('#next-steps-content').html(analysis.next_steps.content);
        
        // Wait for DOM update to complete, then create visualizations
        setTimeout(() => {
            // Create implementation timeline
            createImplementationTimeline(visualizations.implementation_timeline);
            
            // Create solutions comparison table
            createSolutionsTable(visualizations.solution_comparison);
            
            // Draw charts
            drawRadarChart(visualizations.opportunity_radar);
            drawROIChart(visualizations.roi_projection);
            
            // Create business analysis matrix
            if (typeof AIWinLabVisualizations !== 'undefined' && AIWinLabVisualizations.createBusinessAnalysisMatrix) {
                AIWinLabVisualizations.createBusinessAnalysisMatrix('business-analysis-matrix', {
                    businessName: analysisData.businessName,
                    content: analysis.business_analysis.content
                });
            }
            
            // Show "completed" message
            $('.analysis-step .step-indicator i').removeClass().addClass('fas fa-check-circle');
            $('.analyzing-title').text(aiWinLabData.messages.success || 'Analysis complete!');
        }, 500);
    }
    
    // Create the implementation timeline visualization
    function createImplementationTimeline(timelineData) {
        const $timeline = $('#implementation-timeline');
        $timeline.empty();
        
        timelineData.phases.forEach(function(phase, index) {
            const $phase = $(`
                <div class="timeline-phase" data-aos="fade-up" data-aos-delay="${index * 100}">
                    <div class="phase-header" style="background-color: ${phase.color}">
                        <h3>${phase.name}</h3>
                        <div class="phase-timeline">${phase.timeline}</div>
                    </div>
                    <div class="phase-items">
                        <ul>
                            ${phase.items.map(item => `<li>${item}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `);
            
            $timeline.append($phase);
        });
        
        // Refresh AOS
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    // Create the solutions comparison table
    function createSolutionsTable(tableData) {
        const $table = $('#solutions-table');
        $table.empty();
        
        const $tableElement = $('<table class="solutions-table"></table>');
        
        // Create header row
        const $headerRow = $('<tr></tr>');
        tableData.headers.forEach(function(header) {
            $headerRow.append(`<th>${header}</th>`);
        });
        
        const $tableHead = $('<thead></thead>').append($headerRow);
        $tableElement.append($tableHead);
        
        // Create body rows
        const $tableBody = $('<tbody></tbody>');
        tableData.rows.forEach(function(row, rowIndex) {
            const $row = $(`<tr data-aos="fade-up" data-aos-delay="${rowIndex * 100}"></tr>`);
            
            row.forEach(function(cell, cellIndex) {
                // Apply special styling to the first column (area name)
                if (cellIndex === 0) {
                    $row.append(`<td><strong>${cell}</strong></td>`);
                } 
                // Format the cost column
                else if (cellIndex === 2) {
                    $row.append(`<td>${cell}</td>`);
                }
                // Format the implementation complexity column
                else if (cellIndex === 3) {
                    let complexityClass = '';
                    if (cell.toLowerCase().includes('low')) {
                        complexityClass = 'complexity-low';
                    } else if (cell.toLowerCase().includes('medium')) {
                        complexityClass = 'complexity-medium';
                    } else if (cell.toLowerCase().includes('high')) {
                        complexityClass = 'complexity-high';
                    }
                    
                    $row.append(`<td class="${complexityClass}">${cell}</td>`);
                }
                // Format the ROI column
                else if (cellIndex === 5) {
                    $row.append(`<td class="roi-cell">${cell}</td>`);
                }
                // Standard cell formatting
                else {
                    $row.append(`<td>${cell}</td>`);
                }
            });
            
            $tableBody.append($row);
        });
        
        $tableElement.append($tableBody);
        $table.append($tableElement);
        
        // Add CSS for special formatting
        if (!$('#solutions-table-styles').length) {
            $('head').append(`
                <style id="solutions-table-styles">
                    .complexity-low { color: #4CAF50; }
                    .complexity-medium { color: #FF9800; }
                    .complexity-high { color: #F44336; }
                    .roi-cell { font-weight: 600; color: var(--primary-color); }
                </style>
            `);
        }
        
        // Refresh AOS
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    // Draw the radar chart
    function drawRadarChart(chartData) {
        const ctx = document.getElementById('radar-chart').getContext('2d');
        
        // If chart already exists, destroy it
        if (window.opportunityRadarChart) {
            window.opportunityRadarChart.destroy();
        }
        
        // Get colors from CSS variables if available
        let primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#4a6cf7';
        let secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim() || '#8e44ad';
        
        if (!primaryColor.startsWith('#') && !primaryColor.startsWith('rgb')) {
            primaryColor = '#4a6cf7';
        }
        
        if (!secondaryColor.startsWith('#') && !secondaryColor.startsWith('rgb')) {
            secondaryColor = '#8e44ad';
        }
        
        // Create datasets with our color scheme
        const datasets = [
            {
                label: 'AI Impact Potential',
                data: chartData.datasets[0].data,
                backgroundColor: hexToRgba(primaryColor, 0.2),
                borderColor: primaryColor,
                borderWidth: 2,
                pointBackgroundColor: primaryColor,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: primaryColor,
                pointRadius: 4,
                pointHoverRadius: 6
            },
            {
                label: 'Implementation Complexity',
                data: chartData.datasets[1].data,
                backgroundColor: hexToRgba(secondaryColor, 0.2),
                borderColor: secondaryColor,
                borderWidth: 2,
                pointBackgroundColor: secondaryColor,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: secondaryColor,
                pointRadius: 4,
                pointHoverRadius: 6
            }
        ];
        
        // Create new chart
        window.opportunityRadarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        pointLabels: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            backdropColor: 'transparent',
                            color: 'rgba(0, 0, 0, 0.7)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 8
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    
    // Draw the ROI projection chart
    function drawROIChart(chartData) {
        const ctx = document.getElementById('roi-chart').getContext('2d');
        
        // If chart already exists, destroy it
        if (window.roiProjectionChart) {
            window.roiProjectionChart.destroy();
        }
        
        // Get colors from CSS variables if available
        let primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#4a6cf7';
        let secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim() || '#8e44ad';
        let accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent-1').trim() || '#00c9a7';
        
        if (!primaryColor.startsWith('#') && !primaryColor.startsWith('rgb')) {
            primaryColor = '#4a6cf7';
        }
        
        if (!secondaryColor.startsWith('#') && !secondaryColor.startsWith('rgb')) {
            secondaryColor = '#8e44ad';
        }
        
        if (!accentColor.startsWith('#') && !accentColor.startsWith('rgb')) {
            accentColor = '#00c9a7';
        }
        
        // Create gradient fills for datasets
        const investmentGradient = ctx.createLinearGradient(0, 0, 0, 400);
        investmentGradient.addColorStop(0, hexToRgba('#ff6b6b', 0.5));
        investmentGradient.addColorStop(1, hexToRgba('#ff6b6b', 0.0));
        
        const returnGradient = ctx.createLinearGradient(0, 0, 0, 400);
        returnGradient.addColorStop(0, hexToRgba(primaryColor, 0.5));
        returnGradient.addColorStop(1, hexToRgba(primaryColor, 0.0));
        
        const roiGradient = ctx.createLinearGradient(0, 0, 0, 400);
        roiGradient.addColorStop(0, hexToRgba(accentColor, 0.5));
        roiGradient.addColorStop(1, hexToRgba(accentColor, 0.0));
        
        // Process datasets with gradients
        const datasets = [
            {
                label: 'Cumulative Investment ($)',
                data: chartData.datasets[0].data,
                borderColor: '#ff6b6b',
                backgroundColor: investmentGradient,
                borderWidth: 3,
                pointBackgroundColor: '#ff6b6b',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#ff6b6b',
                pointRadius: 5,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Cumulative Return ($)',
                data: chartData.datasets[1].data,
                borderColor: primaryColor,
                backgroundColor: returnGradient,
                borderWidth: 3,
                pointBackgroundColor: primaryColor,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: primaryColor,
                pointRadius: 5,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Net ROI ($)',
                data: chartData.datasets[2].data,
                borderColor: accentColor,
                backgroundColor: roiGradient,
                borderWidth: 3,
                pointBackgroundColor: accentColor,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: accentColor,
                pointRadius: 5,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            }
        ];
        
       // Create new chart
        window.roiProjectionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    
    // Generate PDF of the report
    function generatePDF() {
        // Show loading indicator
        const $downloadBtn = $('#download-report');
        const originalBtnText = $downloadBtn.html();
        $downloadBtn.html('<i class="fas fa-spinner fa-spin"></i> Generating PDF...');
        
        // Create a clean version of the report for PDF export
        const $reportClone = $('.report-content').clone();
        
        // Enhance formatting for PDF
        $reportClone.find('a').css('color', 'inherit');
        $reportClone.find('*').css('font-family', "'Poppins', Arial, sans-serif");
        
        // Add header information
        const reportHeader = `
            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h1 style="color: #4a6cf7; margin-bottom: 5px;">AI Implementation Analysis Report</h1>
                <h2 style="font-weight: normal; margin-top: 0;">${analysisData.businessName}</h2>
                <div style="color: #666; font-size: 14px;">Generated on ${new Date().toLocaleDateString()}</div>
            </div>
        `;
        
        $reportClone.prepend(reportHeader);
        
        // Add footer information
        const reportFooter = `
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
                <p>Generated by AI WinLab™ | www.aiwinlab.com</p>
                <p>This report provides AI implementation recommendations based on the information provided.</p>
            </div>
        `;
        
        $reportClone.append(reportFooter);
        
        // Convert canvas charts to images
        const canvasPromises = [];
        
        $reportClone.find('canvas').each(function(index) {
            const canvas = document.getElementById($(this).attr('id'));
            if (canvas) {
                const imgPromise = new Promise((resolve) => {
                    const img = new Image();
                    img.src = canvas.toDataURL('image/png');
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.onload = () => resolve(img);
                });
                
                canvasPromises.push(imgPromise);
                $(this).replaceWith(`<div id="pdf-chart-${index}" style="text-align: center;"></div>`);
            }
        });
        
        // Replace charts with images once they're ready
        Promise.all(canvasPromises).then(images => {
            images.forEach((img, index) => {
                $reportClone.find(`#pdf-chart-${index}`).append(img);
            });
            
            // Wait for images to be properly rendered
            setTimeout(() => {
                // Configure PDF options
                const opt = {
                    margin: [15, 15, 15, 15],
                    filename: `${analysisData.businessName.replace(/\s+/g, '_')}_AI_Analysis_Report.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: 'avoid-all' }
                };
                
                // Generate the PDF
                html2pdf()
                    .set(opt)
                    .from($reportClone[0])
                    .save()
                    .then(() => {
                        $downloadBtn.html('<i class="fas fa-check"></i> PDF Generated!');
                        
                        // Reset button after a delay
                        setTimeout(function() {
                            $downloadBtn.html(originalBtnText);
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('PDF generation error:', error);
                        $downloadBtn.html('<i class="fas fa-exclamation-triangle"></i> Error');
                        
                        // Reset button after a delay
                        setTimeout(function() {
                            $downloadBtn.html(originalBtnText);
                        }, 3000);
                    });
            }, 500);
        });
    }
    
    // Show consultation scheduling modal
    function showConsultationModal() {
        // Create modal if it doesn't exist
        if ($('#consultation-modal').length === 0) {
            const $modal = $(`
                <div id="consultation-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Schedule Your Implementation Consultation</h2>
                            <span class="close-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Let's schedule a personalized consultation with our AI implementation experts to discuss your custom roadmap.</p>
                            
                            <form id="consultation-form">
                                <div class="form-field">
                                    <label for="consultName">Your Name</label>
                                    <input type="text" id="consultName" name="consultName" required value="${analysisData.businessName ? analysisData.businessName : ''}">
                                </div>
                                
                                <div class="form-field">
                                    <label for="consultEmail">Email Address</label>
                                    <input type="email" id="consultEmail" name="consultEmail" required value="${analysisData.businessEmail ? analysisData.businessEmail : ''}">
                                </div>
                                
                                <div class="form-field">
                                    <label for="consultPhone">Phone Number</label>
                                    <input type="tel" id="consultPhone" name="consultPhone">
                                </div>
                                
                                <div class="form-field">
                                    <label for="consultDate">Preferred Date</label>
                                    <input type="date" id="consultDate" name="consultDate" required min="${new Date().toISOString().split('T')[0]}">
                                </div>
                                
                                <div class="form-field">
                                    <label for="consultTime">Preferred Time</label>
                                    <select id="consultTime" name="consultTime" required>
                                        <option value="">Select a time</option>
                                        <option value="morning">Morning (9am - 12pm)</option>
                                        <option value="afternoon">Afternoon (1pm - 5pm)</option>
                                        <option value="evening">Evening (6pm - 8pm)</option>
                                    </select>
                                </div>
                                
                                <div class="form-field">
                                    <label for="consultNotes">Additional Notes</label>
                                    <textarea id="consultNotes" name="consultNotes" rows="3" placeholder="Any specific topics you'd like to discuss?"></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="submit-btn">Schedule Consultation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            
            // Close modal event
            $('.close-modal').on('click', function() {
                $('#consultation-modal').removeClass('show');
            });
            
            // Submit form event
            $('#consultation-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $submitBtn = $form.find('.submit-btn');
                const originalBtnText = $submitBtn.text();
                
                $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                
                const formData = {
                    action: 'aiwinlab_schedule_consultation',
                    nonce: aiWinLabData.nonce,
                    consultationData: {
                        reportId: reportData.report_id || '',
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
                            // Track event
                            trackEvent('consultation_scheduled', {
                                business_name: analysisData.businessName,
                                consultation_date: formData.consultationData.consultDate
                            });
                            
                            // Show success message
                            $('.modal-body').html(`
                                <div class="success-message">
                                    <i class="fas fa-check-circle"></i>
                                    <h3>Consultation Scheduled!</h3>
                                    <p>Thank you for scheduling a consultation. We'll contact you shortly to confirm your appointment.</p>
                                </div>
                            `);
                            
                            // Add confetti effect
                            showConfetti();
                            
                            // Close modal after delay
                            setTimeout(function() {
                                $('#consultation-modal').removeClass('show');
                            }, 5000);
                        } else {
                            // Show error message
                            alert('Error scheduling consultation: ' + response.data);
                            $submitBtn.text(originalBtnText);
                        }
                    },
                    error: function() {
                        // Show error message
                        alert('An error occurred. Please try again.');
                        $submitBtn.text(originalBtnText);
                    }
                });
            });
        }
        
        // Show the modal
        $('#consultation-modal').addClass('show');
        
        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $('#consultDate').val(tomorrow.toISOString().split('T')[0]);
        
        // Focus the first field
        setTimeout(() => {
            $('#consultName').focus();
        }, 300);
    }
    
    // Show confetti effect
    function showConfetti() {
        const canvas = document.createElement('canvas');
        canvas.id = 'confetti-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '9999';
        document.body.appendChild(canvas);
        
        const confetti = {
            canvas: canvas,
            ctx: canvas.getContext('2d'),
            pieces: [],
            colors: ['#4a6cf7', '#8e44ad', '#00c9a7', '#ff6b6b', '#ffd166'],
            
            init: function() {
                this.canvas.width = window.innerWidth;
                this.canvas.height = window.innerHeight;
                this.createPieces();
                this.animate();
                
                setTimeout(() => {
                    canvas.remove();
                }, 3000);
            },
            
            createPieces: function() {
                for (let i = 0; i < 100; i++) {
                    this.pieces.push({
                        x: Math.random() * this.canvas.width,
                        y: Math.random() * -this.canvas.height,
                        width: Math.random() * 10 + 5,
                        height: Math.random() * 10 + 5,
                        speed: Math.random() * 5 + 3,
                        color: this.colors[Math.floor(Math.random() * this.colors.length)],
                        rotation: Math.random() * 360,
                        rotationSpeed: Math.random() * 5 - 2.5
                    });
                }
            },
            
            animate: function() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                for (let i = 0; i < this.pieces.length; i++) {
                    const piece = this.pieces[i];
                    
                    piece.y += piece.speed;
                    piece.rotation += piece.rotationSpeed;
                    
                    this.ctx.save();
                    this.ctx.translate(piece.x, piece.y);
                    this.ctx.rotate(piece.rotation * Math.PI / 180);
                    this.ctx.fillStyle = piece.color;
                    this.ctx.fillRect(-piece.width / 2, -piece.height / 2, piece.width, piece.height);
                    this.ctx.restore();
                    
                    if (piece.y > this.canvas.height) {
                        piece.y = -piece.height;
                        piece.x = Math.random() * this.canvas.width;
                    }
                }
                
                window.requestAnimationFrame(this.animate.bind(this));
            }
        };
        
        confetti.init();
    }
    
    // Helper function: Convert hex color to rgba
    function hexToRgba(hex, alpha = 1) {
        if (!hex) return `rgba(0, 0, 0, ${alpha})`;
        
        // Check if already in rgba format
        if (hex.startsWith('rgba')) {
            return hex;
        }
        
        // Remove the # if present
        hex = hex.replace('#', '');
        
        // Parse the hex values
        let r, g, b;
        if (hex.length === 3) {
            r = parseInt(hex[0] + hex[0], 16);
            g = parseInt(hex[1] + hex[1], 16);
            b = parseInt(hex[2] + hex[2], 16);
        } else {
            r = parseInt(hex.substring(0, 2), 16);
            g = parseInt(hex.substring(2, 4), 16);
            b = parseInt(hex.substring(4, 6), 16);
        }
        
        // Return rgba color
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
})(jQuery);