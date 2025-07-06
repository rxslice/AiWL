<?php
// templates/analysis-wizard.php
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
                <div class="step-label"><?php _e('Business Info', 'aiwinlab'); ?></div>
            </div>
            <div class="step" data-step="2">
                <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                <div class="step-label"><?php _e('Sales Process', 'aiwinlab'); ?></div>
            </div>
            <div class="step" data-step="3">
                <div class="step-icon"><i class="fas fa-laptop-code"></i></div>
                <div class="step-label"><?php _e('Tech Stack', 'aiwinlab'); ?></div>
            </div>
            <div class="step" data-step="4">
                <div class="step-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="step-label"><?php _e('Pain Points', 'aiwinlab'); ?></div>
            </div>
            <div class="step" data-step="5">
                <div class="step-icon"><i class="fas fa-robot"></i></div>
                <div class="step-label"><?php _e('AI Analysis', 'aiwinlab'); ?></div>
            </div>
            <div class="step" data-step="6">
                <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                <div class="step-label"><?php _e('Report', 'aiwinlab'); ?></div>
            </div>
        </div>
    </div>

    <!-- Wizard Forms Container -->
    <div class="wizard-forms">
        <!-- Step 1: Business Information -->
        <div class="wizard-step active" id="step-1">
            <div class="step-header">
                <h2 class="step-title" data-aos="fade-up"><?php _e('Tell Us About Your Business', 'aiwinlab'); ?></h2>
                <p class="step-description" data-aos="fade-up" data-aos-delay="100">
                    <?php _e('Let\'s start with some basic information about your company to help us understand your business context.', 'aiwinlab'); ?>
                </p>
            </div>
            
            <div class="form-container" data-aos="fade-up" data-aos-delay="200">
                <div class="form-field">
                    <label for="businessName"><?php _e('Business Name', 'aiwinlab'); ?></label>
                    <input type="text" id="businessName" name="businessName" required placeholder="<?php _e('e.g., Acme Corporation', 'aiwinlab'); ?>">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="businessEmail"><?php _e('Business Email', 'aiwinlab'); ?></label>
                    <input type="email" id="businessEmail" name="businessEmail" required placeholder="<?php _e('e.g., contact@acmecorp.com', 'aiwinlab'); ?>">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="websiteUrl"><?php _e('Website URL', 'aiwinlab'); ?></label>
                    <input type="url" id="websiteUrl" name="websiteUrl" required placeholder="<?php _e('e.g., https://www.acmecorp.com', 'aiwinlab'); ?>">
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="industry"><?php _e('Industry', 'aiwinlab'); ?></label>
                    <select id="industry" name="industry" required>
                        <option value=""><?php _e('Select Your Industry', 'aiwinlab'); ?></option>
                        <option value="technology"><?php _e('Technology / Software', 'aiwinlab'); ?></option>
                        <option value="ecommerce"><?php _e('E-commerce / Retail', 'aiwinlab'); ?></option>
                        <option value="finance"><?php _e('Finance / Banking', 'aiwinlab'); ?></option>
                        <option value="healthcare"><?php _e('Healthcare / Medical', 'aiwinlab'); ?></option>
                        <option value="education"><?php _e('Education / E-learning', 'aiwinlab'); ?></option>
                        <option value="manufacturing"><?php _e('Manufacturing', 'aiwinlab'); ?></option>
                        <option value="real_estate"><?php _e('Real Estate', 'aiwinlab'); ?></option>
                        <option value="professional_services"><?php _e('Professional Services', 'aiwinlab'); ?></option>
                        <option value="hospitality"><?php _e('Hospitality / Travel', 'aiwinlab'); ?></option>
                        <option value="other"><?php _e('Other', 'aiwinlab'); ?></option>
                    </select>
                    <div class="field-animation"></div>
                </div>
                
                <div class="form-field">
                    <label for="companySize"><?php _e('Company Size', 'aiwinlab'); ?></label>
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
                    <button type="button" class="next-step-btn" data-next="2"><?php _e('Next Step', 'aiwinlab'); ?> <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Remaining wizard steps (2-6) as implemented earlier -->
        <!-- Including the same HTML structure for each step -->
        <!-- ... -->
        
    </div>
</div>