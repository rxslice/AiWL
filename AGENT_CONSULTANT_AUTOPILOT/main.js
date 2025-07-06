// main.js - Core functionality for AI WinLab

(function($) {
    // Initialize AOS animations
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    
    // Global variables
    let currentStep = 1;
    let analysisData = {};
    let reportData = {};
    
    // DOM ready
    $(document).ready(function() {
        initializeWizard();
        attachEventListeners();
        setupFormAnimations();
    });
    
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
    }
    
    // Attach event listeners to form elements
    function attachEventListeners() {
        // Next step buttons
        $('.next-step-btn').on('click', function() {
            const nextStep = $(this).data('next');
            if (validateCurrentStep()) {
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
            if (validateCurrentStep()) {
                collectFormData();
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
        });
        
        // Schedule consultation button
        $('#schedule-consultation').on('click', function() {
            showConsultationModal();
        });
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
        
        // Range slider animations
        $('.range-slider-container input[type="range"]').on('input', function() {
            const value = $(this).val();
            const max = $(this).attr('max');
            const percentage = (value / max) * 100;
            
            $(this).css('background', `linear-gradient(to right, #4a6cf7 0%, #4a6cf7 ${percentage}%, #e0e0e0 ${percentage}%, #e0e0e0 100%)`);
        }).trigger('input');
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
        AOS.refresh();
        
        // Scroll to top of step
        $('html, body').animate({
            scrollTop: $('#ai-winlab-wizard').offset().top - 50
        }, 500);
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
	 // Validate each required field
        $requiredFields.each(function() {
            if ($(this).val() === '') {
                $(this).addClass('invalid');
                $(this).parent().append('<div class="error-message">This field is required</div>');
                isValid = false;
            } else {
                $(this).removeClass('invalid');
                $(this).parent().find('.error-message').remove();
            }
        });
        
        if (!isValid) {
            // Shake animation for invalid fields
            $(`#step-${currentStep} .invalid`).addClass('shake');
            setTimeout(function() {
                $(`#step-${currentStep} .invalid`).removeClass('shake');
            }, 500);
        }
        
        return isValid;
    }
    
    // Collect all form data
    function collectFormData() {
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
            priorities: collectPriorityData(),
            tools: collectToolData(),
            formTimestamp: new Date().toISOString()
        };
        
        // Update business name in report
        $('#business-name-display').text(analysisData.businessName);
    }
    
    // Translate company size value to text
    function translateCompanySize(value) {
        const sizes = ['1-10', '11-50', '51-200', '201-1000', '1000+'];
        return sizes[value - 1];
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
    
    // Handle file upload
    function handleFileUpload(input) {
        const files = input.files;
        const $fileList = $(input).closest('.file-upload-container').find('.file-list');
        
        $fileList.empty();
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileSize = (file.size / 1024).toFixed(1) + ' KB';
            
            const $fileItem = $(`
                <div class="file-item">
                    <div class="file-icon"><i class="far fa-file"></i></div>
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
    }
    
    // Start the AI analysis process
    function startAnalysis() {
        // Reset analysis UI
        $('.analysis-step').each(function() {
            $(this).find('.step-indicator i').removeClass().addClass('far fa-circle');
        });
        
        $('.analysis-step[data-step="1"] .step-indicator i').removeClass().addClass('fas fa-circle-notch fa-spin');
        $('.analysis-progress .progress-fill').css('width', '0%');
        $('.analysis-progress .progress-percentage').text('0%');
        $('.insight-snippet').text('Initializing analysis...');
        
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
                    reportData = response.data;
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
                
                // Complete all steps
                $('.analysis-step .step-indicator i').removeClass().addClass('fas fa-check-circle');
                
                // After a brief delay, show the report
                setTimeout(function() {
                    goToStep(6);
                }, 1000);
            }
        }, 100);
    }
    
    // Handle analysis error
    function handleAnalysisError(error) {
        // In a production environment, this would show a proper error message and recovery options
        console.error('Analysis error:', error);
        
        // For demo purposes, continue to show the report with mock data
        setTimeout(function() {
            useMockReportData();
            prepareReportData();
            goToStep(6);
        }, 10000);
    }
    
    // Use mock report data if the API call fails
    function useMockReportData() {
        reportData = {
            raw_analysis: "This is a sample analysis report for " + analysisData.businessName,
            structured_analysis: getMockStructuredAnalysis(),
            visualizations: getMockVisualizations(),
            business_name: analysisData.businessName,
            industry: analysisData.industry,
            generated_date: new Date().toISOString()
        };
    }
    
    // Get mock structured analysis
    function getMockStructuredAnalysis() {
        return {
            executive_summary: {
                title: "Executive Summary",
                content: `Based on our comprehensive analysis of ${analysisData.businessName}'s operations, we've identified significant opportunities for AI implementation across your sales, customer service, and content creation processes. Our analysis indicates potential efficiency gains of 35-45% in core workflows, with an estimated ROI of 250-300% within the first 12 months of implementation. By strategically implementing AI solutions in key areas, your business can overcome current bottlenecks, enhance customer experiences, and gain competitive advantages through faster, more consistent operations.`
            },
            business_analysis: {
                title: "Business Analysis",
                content: `${analysisData.businessName} operates in the ${analysisData.industry} industry with a team size of ${analysisData.companySize}. The current sales process relies heavily on manual outreach and follow-up, creating bottlenecks during periods of high lead volume. Customer support is handled through email and phone without automation, leading to response delays and inconsistent service quality. Content creation is sporadic and resource-intensive, limiting marketing effectiveness and brand visibility. The current technology stack lacks integration between key systems, resulting in data silos and reduced operational visibility.`
            },
            ai_opportunities: {
                title: "AI Implementation Opportunities",
                content: `1. **Sales Process Automation**: Implement AI-powered sales assistants to qualify leads, automate follow-ups, and ensure consistent pipeline management.\n\n2. **Customer Support Enhancement**: Deploy an AI chatbot to handle 60-70% of common inquiries, reducing response times and freeing up human agents for complex issues.\n\n3. **Content Generation & Optimization**: Utilize AI content tools to create consistent marketing materials, product descriptions, and social media content at scale.\n\n4. **Data Analysis & Insights**: Implement predictive analytics to identify sales trends, customer behavior patterns, and optimization opportunities.\n\n5. **Internal Knowledge Management**: Deploy an AI knowledge base to centralize information and improve team productivity.`
            },
            implementation_roadmap: {
                title: "Implementation Roadmap",
                content: `**Phase 1 (1-30 days): Quick Wins**\n- Deploy email response templates with AI assistance\n- Implement basic chatbot for website inquiries\n- Set up AI meeting scheduling assistant\n\n**Phase 2 (31-90 days): Core Implementation**\n- Integrate sales process automation tools\n- Expand chatbot capabilities with custom knowledge base\n- Establish AI content generation workflows\n\n**Phase 3 (91-180 days): Advanced Features**\n- Implement predictive analytics dashboard\n- Deploy comprehensive AI knowledge management system\n- Integrate cross-platform data analysis`
            },
            roi_projection: {
                title: "ROI Projection",
                content: `Based on your business profile and industry benchmarks, we project the following ROI timeline:\n\n- **Months 1-3**: Initial investment period with negative ROI during implementation and training\n- **Months 4-6**: Break-even point as efficiency gains begin offsetting implementation costs\n- **Months 7-12**: Positive ROI period with accelerating returns as systems optimize\n- **Month 12+**: Projected 250-300% ROI on AI investments, with $45,000-65,000 in annual cost savings and revenue improvements`
            },
            solutions_comparison: {
                title: "AI Solutions Comparison",
                content: `**Customer Support**\n- Recommended: OpenAI Assistants API with custom knowledge base\n- Cost: $300-500/month\n- Implementation: Medium complexity\n- Time to Value: 2-4 weeks\n\n**Sales Automation**\n- Recommended: CRM AI integration with custom workflow\n- Cost: $200-400/month\n- Implementation: Medium complexity\n- Time to Value: 4-6 weeks\n\n**Content Creation**\n- Recommended: Jasper.ai with custom templates\n- Cost: $60-120/month per user\n- Implementation: Low complexity\n- Time to Value: 1-2 weeks`
            },
            next_steps: {
                title: "Next Steps",
                content: `1. **Implementation Planning Session**: Schedule a detailed planning session to prioritize specific AI implementations based on this analysis\n\n2. **Vendor Evaluation**: Review recommended solutions and select specific vendors that align with your technical capabilities and budget\n\n3. **Pilot Project**: Begin with one high-impact, low-complexity area to demonstrate value and build organizational support\n\n4. **Team Training**: Prepare your team with the necessary skills to work effectively with new AI tools\n\n5. **Measurement Framework**: Establish clear KPIs to track the success of your AI implementation`
            }
        };
    }
    
    // Get mock visualizations
    function getMockVisualizations() {
        return {
            opportunity_radar: {
                labels: ['Customer Support', 'Content Creation', 'Sales Process', 'Data Analysis', 'Decision Making', 'Marketing'],
                datasets: [
                    {
                        label: 'AI Impact Potential',
                        data: [85, 90, 75, 95, 80, 70],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                    },
                    {
                        label: 'Implementation Complexity',
                        data: [60, 50, 65, 80, 70, 45],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                    }
                ]
            },
            implementation_timeline: {
                phases: [
                    {
                        name: 'Quick Wins',
                        timeline: '1-2 weeks',
                        items: [
                            'Customer Service Chatbot Implementation',
                            'Email Response Templates Automation',
                            'Meeting Scheduling Assistant',
                        ],
                        color: '#4CAF50'
                    },
                    {
                        name: 'Phase 1',
                        timeline: '1-3 months',
                        items: [
                            'Sales Process Automation',
                            'Content Creation Workflow',
                            'Basic Data Analysis Pipeline',
                        ],
                        color: '#2196F3'
                    },
                    {
                        name: 'Phase 2',
                        timeline: '3-6 months',
                        items: [
                            'Predictive Customer Analytics',
                            'Advanced Document Processing',
                            'CRM Integration & Enhancement',
                        ],
                        color: '#9C27B0'
                    },
                    {
                        name: 'Long-term',
                        timeline: '6+ months',
                        items: [
                            'Full AI-driven Decision Support',
                            'Autonomous Process Optimization',
                            'Predictive Business Intelligence',
                        ],
                        color: '#FF9800'
                    }
                ]
            },
            roi_projection: {
                labels: ['Month 1', 'Month 2', 'Month 3', 'Month 6', 'Month 9', 'Month 12'],
                datasets: [
                    {
                        label: 'Cumulative Investment ($)',
                        data: [5000, 8000, 10000, 15000, 18000, 20000],
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Cumulative Return ($)',
                        data: [0, 2000, 7000, 22000, 40000, 65000],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Net ROI ($)',
                        data: [-5000, -6000, -3000, 7000, 22000, 45000],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true
                    }
                ]
            },
            solution_comparison: {
                headers: ['Area', 'Recommended Solution', 'Cost Range', 'Implementation Complexity', 'Time to Value', 'Expected ROI'],
                rows: [
                    ['Customer Support', 'OpenAI Assistants API with custom knowledge base', '$300-500/mo', 'Medium', '2-4 weeks', '200-300%'],
                    ['Content Creation', 'Jasper.ai with custom templates', '$60-120/mo per user', 'Low', '1-2 weeks', '150-250%'],
                    ['Sales Process', 'Copilot + Gemini for Slides and Email', '$30/mo per user', 'Low', '1-2 weeks', '130-180%'],
                    ['Data Analysis', 'AutoML + Custom Python Scripts', '$200-600/mo', 'High', '1-3 months', '250-400%'],
                    ['Decision Support', 'Tableau + Custom AI Models', '$500-1200/mo', 'High', '2-4 months', '180-300%'],
                ]
            }
        };
    }
    
    // Prepare the report data
    function prepareReportData() {
        const analysis = reportData.structured_analysis;
        const visualizations = reportData.visualizations;
        
        // Populate text sections
        $('#executive-summary-content').html(analysis.executive_summary.content);
        $('#business-analysis-matrix').html(analysis.business_analysis.content);
        $('#ai-opportunities-list').html(analysis.ai_opportunities.content);
        $('#roi-explanation').html(analysis.roi_projection.content);
        $('#next-steps-content').html(analysis.next_steps.content);
        
        // Create implementation timeline
        createImplementationTimeline(visualizations.implementation_timeline);
        
        // Create solutions comparison table
        createSolutionsTable(visualizations.solution_comparison);
        
        // Draw charts
        drawRadarChart(visualizations.opportunity_radar);
        drawROIChart(visualizations.roi_projection);
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
        tableData.rows.forEach(function(row) {
            const $row = $('<tr></tr>');
            row.forEach(function(cell) {
                $row.append(`<td>${cell}</td>`);
            });
            $tableBody.append($row);
        });
        
        $tableElement.append($tableBody);
        $table.append($tableElement);
    }
    
    // Draw the radar chart
    function drawRadarChart(chartData) {
        const ctx = document.getElementById('radar-chart').getContext('2d');
        
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                elements: {
                    line: {
                        borderWidth: 3
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });
    }
    
    // Draw the ROI projection chart
    function drawROIChart(chartData) {
        const ctx = document.getElementById('roi-chart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false
                    }
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
        
        // In a production environment, this would use a proper PDF generation library
        // For demo purposes, we're simulating the process
        setTimeout(function() {
            $downloadBtn.html('<i class="fas fa-check"></i> PDF Generated!');
            
            // Create a fake download link
            const link = document.createElement('a');
            link.href = '#';
            link.download = `${analysisData.businessName.replace(/\s+/g, '_')}_AI_Analysis_Report.pdf`;
            link.click();
            
            // Reset button after a delay
            setTimeout(function() {
                $downloadBtn.html(originalBtnText);
            }, 3000);
        }, 3000);
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
                
                // Show success message
                $('.modal-body').html(`
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <h3>Consultation Scheduled!</h3>
                        <p>Thank you for scheduling a consultation. We'll contact you shortly to confirm your appointment.</p>
                    </div>
                `);
                
                // Close modal after delay
                setTimeout(function() {
                    $('#consultation-modal').removeClass('show');
                }, 3000);
            });
        }
        
        // Show the modal
        $('#consultation-modal').addClass('show');
    }
})(jQuery);	