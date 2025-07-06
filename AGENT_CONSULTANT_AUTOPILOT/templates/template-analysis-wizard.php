<?php
// template-analysis-wizard.php
/**
 * Template Name: AI WinLab Analysis Wizard
 */

get_header();
?>

<div id="ai-winlab-wizard" class="ai-winlab-container">
    <!-- Progress Indicator -->
    <div class="wizard-progress">
        <div class="progress-bar">
            <div class="progress-indicator" style="width: 0%"></div>
        </div>
        <div class="progress-steps">
            <div class="step active" data-step="1">
                <div class="step-icon"><i class="fas fa-building"></i></div>
                <div class="step-label">Business Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                <div class="step-label">Sales Process</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-icon"><i class="fas fa-laptop-code"></i></div>
                <div class="step-label">Tech Stack</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="step-label">Pain Points</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-icon"><i class="fas fa-robot"></i></div>
                <div class="step-label">AI Analysis</div>
            </div>
            <div class="step" data-step="6">
                <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                <div class="step-label">Report</div>
            </div>
        </div>
    </div>

    <!-- Wizard Forms Container -->
    <div class="wizard-forms">
        <!-- Step 1: Business Information -->
        <div class="wizard-step active" id="step-1">
            <div class="step-header">
                <h2 class="step-title" data-aos="fade-up">Tell Us About Your Business</h2>
                <p class="step-description" data-aos="fade-up" data-aos-delay="100">
                    Let's start with some basic information about your company to help us understand your business context.
                </p>
            </div>
            
            <div class="form-container" data-aos="fade-up" data-aos-delay="200">
                <div class="form-field">
                    <label for="businessName">Business Name</label>
                    <input type="text" id="businessName" name="businessName" required placeholder="e.g., Acme Corporation">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="businessEmail">Business Email</label>
                    <input type="email" id="businessEmail" name="businessEmail" required placeholder="e.g., contact@acmecorp.com">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="websiteUrl">Website URL</label>
                    <input type="url" id="websiteUrl" name="websiteUrl" required placeholder="e.g., https://www.acmecorp.com">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="industry">Industry</label>
                    <select id="industry" name="industry" required>
                        <option value="">Select Your Industry</option>
                        <option value="technology">Technology / Software</option>
                        <option value="ecommerce">E-commerce / Retail</option>
                        <option value="finance">Finance / Banking</option>
                        <option value="healthcare">Healthcare / Medical</option>
                        <option value="education">Education / E-learning</option>
                        <option value="manufacturing">Manufacturing</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="professional_services">Professional Services</option>
                        <option value="hospitality">Hospitality / Travel</option>
                        <option value="other">Other</option>
                    </select>
				 <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="companySize">Company Size</label>
                    <div class="range-slider-container">
                        <input type="range" id="companySize" name="companySize" min="1" max="5" step="1" value="2">
                        <div class="range-labels">
                            <span>1-10</span>
                            <span>11-50</span>
                            <span>51-200</span>
                            <span>201-1000</span>
                            <span>1000+</span>
                        </div>
                    </div>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="next-step-btn" data-next="2">Next Step <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Sales Process -->
        <div class="wizard-step" id="step-2">
            <div class="step-header">
                <h2 class="step-title" data-aos="fade-up">Tell Us About Your Sales Process</h2>
                <p class="step-description" data-aos="fade-up" data-aos-delay="100">
                    Please describe your current sales workflow and revenue streams. This helps us identify AI-powered enhancement opportunities.
                </p>
            </div>
            
            <div class="form-container" data-aos="fade-up" data-aos-delay="200">
                <div class="form-field">
                    <label for="revenueModel">Primary Revenue Model</label>
                    <select id="revenueModel" name="revenueModel" required>
                        <option value="">Select Your Revenue Model</option>
                        <option value="subscription">Subscription / Recurring Revenue</option>
                        <option value="transactional">Transactional / One-time Purchases</option>
                        <option value="advertising">Advertising / Affiliate</option>
                        <option value="freemium">Freemium / Upsell</option>
                        <option value="service">Service-based / Consulting</option>
                        <option value="marketplace">Marketplace / Commission</option>
                        <option value="licensing">Licensing / Royalties</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="salesProcess">Describe Your Sales Process</label>
                    <textarea id="salesProcess" name="salesProcess" rows="5" placeholder="Please describe each step of your sales process, from lead generation to closing. Include any tools you currently use."></textarea>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label>How do you primarily acquire customers?</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_social" name="acquisition[]" value="social">
                            <label for="acquisition_social">Social Media</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_ads" name="acquisition[]" value="ads">
                            <label for="acquisition_ads">Paid Advertising</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_seo" name="acquisition[]" value="seo">
                            <label for="acquisition_seo">SEO / Organic</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_referral" name="acquisition[]" value="referral">
                            <label for="acquisition_referral">Referrals</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_outbound" name="acquisition[]" value="outbound">
                            <label for="acquisition_outbound">Outbound Sales</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acquisition_other" name="acquisition[]" value="other">
                            <label for="acquisition_other">Other</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>What is your average sales cycle length?</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="cycle_days" name="salesCycle" value="days">
                            <label for="cycle_days">Days</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="cycle_weeks" name="salesCycle" value="weeks">
                            <label for="cycle_weeks">Weeks</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="cycle_months" name="salesCycle" value="months">
                            <label for="cycle_months">Months</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="cycle_quarters" name="salesCycle" value="quarters">
                            <label for="cycle_quarters">Quarters+</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="prev-step-btn" data-prev="1"><i class="fas fa-arrow-left"></i> Previous</button>
                    <button type="button" class="next-step-btn" data-next="3">Next Step <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Technology Stack -->
        <div class="wizard-step" id="step-3">
            <div class="step-header">
                <h2 class="step-title" data-aos="fade-up">Your Current Technology Stack</h2>
                <p class="step-description" data-aos="fade-up" data-aos-delay="100">
                    Tell us about the tools and technologies you're currently using in your business operations.
                </p>
            </div>
            
            <div class="form-container" data-aos="fade-up" data-aos-delay="200">
                <div class="form-field">
                    <label>What CRM system do you use?</label>
                    <div class="select-tiles">
                        <div class="tile-option">
                            <input type="radio" id="crm_salesforce" name="crm" value="salesforce">
                            <label for="crm_salesforce">
                                <div class="tile-icon"><i class="fab fa-salesforce"></i></div>
                                <div class="tile-label">Salesforce</div>
                            </label>
                        </div>
                        <div class="tile-option">
                            <input type="radio" id="crm_hubspot" name="crm" value="hubspot">
                            <label for="crm_hubspot">
                                <div class="tile-icon"><i class="fas fa-h-square"></i></div>
                                <div class="tile-label">HubSpot</div>
                            </label>
                        </div>
                        <div class="tile-option">
                            <input type="radio" id="crm_zoho" name="crm" value="zoho">
                            <label for="crm_zoho">
                                <div class="tile-icon"><i class="fas fa-z"></i></div>
                                <div class="tile-label">Zoho</div>
                            </label>
                        </div>
                        <div class="tile-option">
                            <input type="radio" id="crm_pipedrive" name="crm" value="pipedrive">
                            <label for="crm_pipedrive">
                                <div class="tile-icon"><i class="fas fa-project-diagram"></i></div>
                                <div class="tile-label">Pipedrive</div>
                            </label>
                        </div>
                        <div class="tile-option">
                            <input type="radio" id="crm_other" name="crm" value="other">
                            <label for="crm_other">
                                <div class="tile-icon"><i class="fas fa-plus"></i></div>
                                <div class="tile-label">Other</div>
                            </label>
                        </div>
                        <div class="tile-option">
                            <input type="radio" id="crm_none" name="crm" value="none">
                            <label for="crm_none">
                                <div class="tile-icon"><i class="fas fa-times"></i></div>
                                <div class="tile-label">None</div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>What other tools do you use?</label>
                    <div class="checkbox-group tool-selection">
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_email" name="tools[]" value="email_marketing">
                            <label for="tool_email">Email Marketing</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_analytics" name="tools[]" value="analytics">
                            <label for="tool_analytics">Analytics Platform</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_social" name="tools[]" value="social_media">
                            <label for="tool_social">Social Media Management</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_project" name="tools[]" value="project_management">
                            <label for="tool_project">Project Management</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_support" name="tools[]" value="customer_support">
                            <label for="tool_support">Customer Support</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="tool_erp" name="tools[]" value="erp">
                            <label for="tool_erp">ERP System</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label for="currentTechnology">Describe Your Current Technology Stack</label>
                    <textarea id="currentTechnology" name="currentTechnology" rows="4" placeholder="Please describe all the software, platforms, and tools you currently use to run your business. Include any custom solutions or integrations."></textarea>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label>How would you rate your team's technical expertise?</label>
                    <div class="rating-slider">
                        <input type="range" id="technicalExpertise" name="technicalExpertise" min="1" max="5" step="1" value="3">
                        <div class="rating-labels">
                            <span>Beginner</span>
                            <span>Basic</span>
                            <span>Intermediate</span>
                            <span>Advanced</span>
                            <span>Expert</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="prev-step-btn" data-prev="2"><i class="fas fa-arrow-left"></i> Previous</button>
                    <button type="button" class="next-step-btn" data-next="4">Next Step <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Step 4: Pain Points -->
        <div class="wizard-step" id="step-4">
            <div class="step-header">
                <h2 class="step-title" data-aos="fade-up">Your Business Challenges</h2>
                <p class="step-description" data-aos="fade-up" data-aos-delay="100">
                    What are the main challenges or bottlenecks in your current business operations?
                </p>
            </div>
            
            <div class="form-container" data-aos="fade-up" data-aos-delay="200">
                <div class="form-field">
                    <label>What areas of your business need the most improvement?</label>
                    <div class="priority-matrix">
                        <div class="matrix-row headers">
                            <div class="matrix-cell"></div>
                            <div class="matrix-cell">Low Priority</div>
                            <div class="matrix-cell">Medium Priority</div>
                            <div class="matrix-cell">High Priority</div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Lead Generation</div>
                            <div class="matrix-cell"><input type="radio" name="priority_leads" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_leads" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_leads" value="high"></div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Sales Conversion</div>
                            <div class="matrix-cell"><input type="radio" name="priority_sales" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_sales" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_sales" value="high"></div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Customer Support</div>
                            <div class="matrix-cell"><input type="radio" name="priority_support" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_support" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_support" value="high"></div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Internal Communication</div>
                            <div class="matrix-cell"><input type="radio" name="priority_communication" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_communication" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_communication" value="high"></div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Content Creation</div>
                            <div class="matrix-cell"><input type="radio" name="priority_content" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_content" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_content" value="high"></div>
                        </div>
                        <div class="matrix-row">
                            <div class="matrix-cell">Data Analysis</div>
                            <div class="matrix-cell"><input type="radio" name="priority_data" value="low"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_data" value="medium"></div>
                            <div class="matrix-cell"><input type="radio" name="priority_data" value="high"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label for="businessProcesses">Describe Your Current Business Processes</label>
                    <textarea id="businessProcesses" name="businessProcesses" rows="4" placeholder="Please describe your day-to-day business operations and workflow."></textarea>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="painPoints">Describe Your Main Pain Points and Challenges</label>
                    <textarea id="painPoints" name="painPoints" rows="4" placeholder="What specific challenges, bottlenecks, or inefficiencies are you experiencing in your business?"></textarea>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label>Upload Supporting Documents (Optional)</label>
                    <div class="file-upload-container">
                        <input type="file" id="supportingDocs" name="supportingDocs" multiple>
                        <label for="supportingDocs" class="file-upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Choose Files</span>
                        </label>
                        <div class="file-list"></div>
                    </div>
                    <p class="field-hint">Upload process diagrams, analytics reports, or any documents that help explain your business challenges.</p>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="prev-step-btn" data-prev="3"><i class="fas fa-arrow-left"></i> Previous</button>
                    <button type="button" class="analyze-btn" id="start-analysis">Start AI Analysis <i class="fas fa-robot"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Step 5: AI Analysis in Progress -->
        <div class="wizard-step" id="step-5">
            <div class="analysis-container">
                <div class="analysis-graphic">
                    <div class="brain-animation">
                        <svg width="300" height="300" viewBox="0 0 100 100">
                            <!-- Brain Animation SVG Elements -->
                            <path class="brain-path" d="..."></path>
                            <!-- More SVG elements for the brain animation -->
                        </svg>
                    </div>
                    <div class="processing-pulses">
                        <div class="pulse pulse-1"></div>
                        <div class="pulse pulse-2"></div>
                        <div class="pulse pulse-3"></div>
                    </div>
                </div>
                
                <div class="analysis-status">
                    <h2 class="analyzing-title">AI Analysis in Progress</h2>
                    <div class="analysis-steps">
                        <div class="analysis-step" data-step="1">
                            <div class="step-indicator"><i class="fas fa-check-circle"></i></div>
                            <div class="step-label">Analyzing Business Model</div>
                        </div>
                        <div class="analysis-step" data-step="2">
                            <div class="step-indicator"><i class="fas fa-circle-notch fa-spin"></i></div>
                            <div class="step-label">Mapping Workflow Processes</div>
                        </div>
                        <div class="analysis-step" data-step="3">
                            <div class="step-indicator"><i class="far fa-circle"></i></div>
                            <div class="step-label">Evaluating Technology Stack</div>
                        </div>
                        <div class="analysis-step" data-step="4">
                            <div class="step-indicator"><i class="far fa-circle"></i></div>
                            <div class="step-label">Identifying AI Opportunities</div>
                        </div>
                        <div class="analysis-step" data-step="5">
                            <div class="step-indicator"><i class="far fa-circle"></i></div>
                            <div class="step-label">Generating Recommendations</div>
                        </div>
                        <div class="analysis-step" data-step="6">
                            <div class="step-indicator"><i class="far fa-circle"></i></div>
                            <div class="step-label">Preparing Visualizations</div>
                        </div>
                        <div class="analysis-step" data-step="7">
                            <div class="step-indicator"><i class="far fa-circle"></i></div>
                            <div class="step-label">Finalizing Report</div>
                        </div>
                    </div>
                    
                    <div class="analysis-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 15%"></div>
                        </div>
                        <div class="progress-percentage">15%</div>
                    </div>
                    
                    <div class="analysis-insight-snippets">
                        <div class="insight-snippet">Identifying optimal AI integration points...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 6: Analysis Report -->
        <div class="wizard-step" id="step-6">
            <div class="report-container">
                <div class="report-header">
                    <div class="report-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ai-winlab-logo.png" alt="AI WinLab">
                    </div>
                    <div class="report-title">
                        <h1>AI Implementation Analysis Report</h1>
                        <h2><span id="business-name-display">Your Business</span></h2>
                        <div class="report-meta">
                            <div class="meta-item"><i class="far fa-calendar-alt"></i> <span id="report-date"></span></div>
                            <div class="meta-item"><i class="far fa-file-alt"></i> Report #<span id="report-id"></span></div>
                        </div>
                    </div>
                </div>
                
                <div class="report-navigation">
                    <ul>
                        <li><a href="#executive-summary">Executive Summary</a></li>
                        <li><a href="#business-analysis">Business Analysis</a></li>
                        <li><a href="#ai-opportunities">AI Opportunities</a></li>
                        <li><a href="#implementation-roadmap">Implementation Roadmap</a></li>
                        <li><a href="#roi-projection">ROI Projection</a></li>
                        <li><a href="#next-steps">Next Steps</a></li>
                    </ul>
                </div>
                
                <div class="report-content">
                    <!-- Executive Summary Section -->
                    <section id="executive-summary" class="report-section">
                        <div class="section-header">
                            <h2>Executive Summary</h2>
                        </div>
                        <div class="section-content" id="executive-summary-content">
                            <!-- This will be populated with AI-generated content -->
                        </div>
                    </section>
                    
                    <!-- Business Analysis Section -->
                    <section id="business-analysis" class="report-section">
                        <div class="section-header">
                            <h2>Business Analysis</h2>
                        </div>
                        <div class="section-content">
                            <div class="analysis-matrix" id="business-analysis-matrix">
                                <!-- This will be populated with AI-generated content -->
                            </div>
                        </div>
                    </section>
                    
                    <!-- AI Opportunities Section -->
                    <section id="ai-opportunities" class="report-section">
                        <div class="section-header">
                            <h2>AI Implementation Opportunities</h2>
                        </div>
                        <div class="section-content">
                            <div class="opportunities-container">
                                <div class="opportunity-radar-chart">
                                    <canvas id="radar-chart"></canvas>
                                </div>
                                <div class="opportunities-list" id="ai-opportunities-list">
                                    <!-- This will be populated with AI-generated content -->
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Implementation Roadmap Section -->
                    <section id="implementation-roadmap" class="report-section">
                        <div class="section-header">
                            <h2>Implementation Roadmap</h2>
                        </div>
                        <div class="section-content">
                            <div class="roadmap-timeline" id="implementation-timeline">
                                <!-- This will be populated with AI-generated content -->
                            </div>
                        </div>
                    </section>
                    
                    <!-- ROI Projection Section -->
                    <section id="roi-projection" class="report-section">
                        <div class="section-header">
                            <h2>ROI Projection</h2>
                        </div>
                        <div class="section-content">
                            <div class="roi-chart-container">
                                <canvas id="roi-chart"></canvas>
                            </div>
                            <div class="roi-explanation" id="roi-explanation">
                                <!-- This will be populated with AI-generated content -->
                            </div>
                        </div>
                    </section>
                    
                    <!-- AI Solutions Comparison Section -->
                    <section id="solutions-comparison" class="report-section">
                        <div class="section-header">
                            <h2>Recommended AI Solutions</h2>
                        </div>
                        <div class="section-content">
                            <div class="solutions-table-container" id="solutions-table">
                                <!-- This will be populated with AI-generated content -->
                            </div>
                        </div>
                    </section>
                    
                    <!-- Next Steps Section -->
                    <section id="next-steps" class="report-section">
                        <div class="section-header">
                            <h2>Next Steps</h2>
                        </div>
                        <div class="section-content" id="next-steps-content">
                            <!-- This will be populated with AI-generated content -->
                        </div>
                    </section>
                </div>
                
                <div class="report-actions">
                    <button id="download-report" class="download-btn">
                        <i class="fas fa-download"></i> Download Full Report
                    </button>
                    <button id="schedule-consultation" class="consult-btn">
                        <i class="fas fa-calendar-check"></i> Schedule Implementation Consultation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>	