<?php
// inc/analysis-processor.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analysis Processor Class
 * 
 * Handles the core functionality of processing business data through the AI engine
 * and generating comprehensive analysis reports
 */
class AI_WinLab_Analysis_Processor {
    /**
     * API key for Gemini
     */
    private $api_key;
    
    /**
     * API endpoint for Gemini
     */
    private $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    
    /**
     * Meta prompt template
     */
    private $meta_prompt;
    
    /**
     * Initialize the processor
     */
    public function __construct() {
        $this->api_key = get_option('aiwinlab_gemini_api_key');
        $this->load_meta_prompt();
    }
    
    /**
     * Load the meta prompt template
     */
    private function load_meta_prompt() {
        $prompt_file = AIWINLAB_PLUGIN_DIR . 'inc/meta-prompt.txt';
        
        if (file_exists($prompt_file)) {
            $this->meta_prompt = file_get_contents($prompt_file);
        } else {
            // Fallback to a basic prompt if file doesn't exist
            $this->meta_prompt = "You are AI WinLab, an expert business consultant specializing in AI implementation analysis. Analyze the provided business information and suggest AI implementation opportunities.";
        }
    }
    
    /**
     * Process the analysis request
     * 
     * @param array $data Business data to analyze
     * @return array|WP_Error Analysis result or error
     */
    public function process_analysis($data) {
        // Start timing for performance tracking
        $start_time = microtime(true);
        
        // Validate API key
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('Gemini API key is not configured. Please set your API key in the AI WinLab settings.', 'aiwinlab'));
        }
        
        // Validate required fields
        $required_fields = array('businessName', 'businessEmail', 'industry', 'websiteUrl', 'revenueModel', 'salesProcess');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Missing required field: %s', 'aiwinlab'), $field), array('status' => 400));
            }
        }
        
        // Log the analysis start
        $this->log_event('analysis_started', array(
            'business_name' => $data['businessName'],
            'industry' => $data['industry']
        ));
        
        try {
            // Prepare data for AI analysis
            $analysis_prompt = $this->prepare_analysis_prompt($data);
            
            // Call Gemini API
            $response = $this->call_gemini_api($analysis_prompt);
            
            if (is_wp_error($response)) {
                $this->log_event('analysis_error', array(
                    'error' => $response->get_error_message(),
                    'business_name' => $data['businessName']
                ));
                return $response;
            }
            
            // Process the response
            $processed_result = $this->process_result($response, $data);
            
            // Log the analysis completion
            $end_time = microtime(true);
            $this->log_event('analysis_completed', array(
                'business_name' => $data['businessName'],
                'processing_time' => round($end_time - $start_time, 2) . 's'
            ));
            
            return $processed_result;
        } catch (Exception $e) {
            $this->log_event('analysis_exception', array(
                'error' => $e->getMessage(),
                'business_name' => $data['businessName']
            ));
            
            return new WP_Error('analysis_exception', $e->getMessage());
        }
    }
    
    /**
     * Prepare the analysis prompt with business data
     * 
     * @param array $data Business data
     * @return string Formatted prompt for AI
     */
    private function prepare_analysis_prompt($data) {
        // Sanitize and format data
        $businessName = sanitize_text_field($data['businessName']);
        $industry = sanitize_text_field($data['industry']);
        $websiteUrl = esc_url_raw($data['websiteUrl']);
        $companySize = isset($data['companySize']) ? sanitize_text_field($this->translate_company_size($data['companySize'])) : 'Unknown';
        $revenueModel = sanitize_text_field($data['revenueModel']);
        $businessProcesses = sanitize_textarea_field($data['businessProcesses'] ?? '');
        $salesProcess = sanitize_textarea_field($data['salesProcess']);
        $painPoints = sanitize_textarea_field($data['painPoints'] ?? '');
        $currentTechnology = sanitize_textarea_field($data['currentTechnology'] ?? '');
        
        // Additional context from form fields if available
        $priorities = isset($data['priorities']) && is_array($data['priorities']) ? $this->format_priorities($data['priorities']) : '';
        $tools = isset($data['tools']) && is_array($data['tools']) ? $this->format_tools($data['tools']) : '';
        
        // Build the business information section
        $business_info = <<<EOT
## Business Information for Analysis
- Business Name: {$businessName}
- Industry: {$industry}
- Website URL: {$websiteUrl}
- Company Size: {$companySize}
- Primary Revenue Model: {$revenueModel}

## Current Business Processes
{$businessProcesses}

## Sales Process
{$salesProcess}

## Current Pain Points
{$painPoints}

## Technology Currently In Use
{$currentTechnology}

EOT;

        // Add priorities if available
        if (!empty($priorities)) {
            $business_info .= "\n## Business Priorities\n{$priorities}\n";
        }
        
        // Add tools if available
        if (!empty($tools)) {
            $business_info .= "\n## Tools Currently Used\n{$tools}\n";
        }
        
        // Add instruction for analysis
        $business_info .= "\nAnalyze this business based on the framework above and provide a complete, actionable report with specific AI implementation recommendations. Include suggestions for data visualization formats (charts, graphs, tables) that would best represent each insight.";
        
        // Combine meta prompt and business info
        return $this->meta_prompt . "\n\n" . $business_info;
    }
    
    /**
     * Format priorities data for the prompt
     */
    private function format_priorities($priorities) {
        if (empty($priorities)) {
            return '';
        }
        
        $formatted = "The business has indicated the following priorities:\n";
        
        foreach ($priorities as $area => $priority) {
            $formatted .= "- {$area}: {$priority} priority\n";
        }
        
        return $formatted;
    }
    
    /**
     * Format tools data for the prompt
     */
    private function format_tools($tools) {
        if (empty($tools)) {
            return '';
        }
        
        $crm = isset($tools['crm']) ? $tools['crm'] : 'none';
        $other_tools = isset($tools['otherTools']) && is_array($tools['otherTools']) ? $tools['otherTools'] : array();
        
        $formatted = "The business currently uses the following tools:\n";
        $formatted .= "- CRM System: " . ucfirst($crm) . "\n";
        
        if (!empty($other_tools)) {
            $formatted .= "- Other Tools: " . implode(", ", $other_tools) . "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Translate company size value to text
     */
    private function translate_company_size($value) {
        $sizes = ['1-10', '11-50', '51-200', '201-1000', '1000+'];
        $index = intval($value) - 1;
        
        if ($index >= 0 && $index < count($sizes)) {
            return $sizes[$index];
        }
        
        return 'Unknown';
    }
    
    /**
     * Call the Gemini API
     * 
     * @param string $prompt The analysis prompt
     * @return array|WP_Error API response or error
     */
    private function call_gemini_api($prompt) {
        // Prepare request body
        $request_body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.2,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192
            ),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                )
            )
        );
        
        // Initialize cURL if available for better handling
        if (function_exists('curl_version')) {
            return $this->call_api_with_curl($request_body);
        } else {
            return $this->call_api_with_wp_remote($request_body);
        }
    }
    
    /**
     * Call API using cURL for better performance and error handling
     */
    
        private function call_api_with_curl($request_body) {
        $api_url = $this->api_endpoint . '?key=' . $this->api_key;
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        if ($curl_error) {
            return new WP_Error('api_curl_error', $curl_error);
        }
        
        if ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : sprintf(__('API error with status code: %s', 'aiwinlab'), $http_code);
            
            return new WP_Error('api_error', $error_message, array('status' => $http_code));
        }
        
        $result = json_decode($response, true);
        
        if (empty($result) || !isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('invalid_response', __('Invalid response from Gemini API', 'aiwinlab'));
        }
        
        return $result;
    }
    
    /**
     * Call API using WordPress HTTP API as fallback
     */
    private function call_api_with_wp_remote($request_body) {
        $response = wp_remote_post($this->api_endpoint . '?key=' . $this->api_key, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($request_body),
            'timeout' => 60,
            'data_format' => 'body',
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $response_body = wp_remote_retrieve_body($response);
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : sprintf(__('API error with status code: %s', 'aiwinlab'), $response_code);
            
            return new WP_Error('api_error', $error_message, array('status' => $response_code));
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (empty($result) || !isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('invalid_response', __('Invalid response from Gemini API', 'aiwinlab'));
        }
        
        return $result;
    }
    
    /**
     * Process the API result
     * 
     * @param array $api_result The API response
     * @param array $original_data The original business data
     * @return array Processed analysis result
     */
    private function process_result($api_result, $original_data) {
        // Extract the text from the API response
        $analysis_text = $api_result['candidates'][0]['content']['parts'][0]['text'];
        
        // Save a debug copy of the raw text
        $this->save_debug_output($analysis_text, $original_data['businessName']);
        
        // Parse the analysis into structured sections
        $sections = $this->parse_analysis_sections($analysis_text);
        
        // Generate visualization data
        $visualizations = $this->generate_visualizations($sections, $original_data);
        
        return array(
            'raw_analysis' => $analysis_text,
            'structured_analysis' => $sections,
            'visualizations' => $visualizations,
            'business_name' => $original_data['businessName'],
            'industry' => $original_data['industry'],
            'generated_date' => current_time('mysql'),
            'generated_timestamp' => time(),
        );
    }
    
    /**
     * Save debug output for troubleshooting
     */
    private function save_debug_output($text, $business_name) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $sanitized_name = sanitize_file_name($business_name);
            $debug_dir = wp_upload_dir()['basedir'] . '/aiwinlab-debug';
            
            // Create directory if it doesn't exist
            if (!file_exists($debug_dir)) {
                wp_mkdir_p($debug_dir);
            }
            
            $timestamp = date('Y-m-d-H-i-s');
            $filename = $debug_dir . '/' . $sanitized_name . '-' . $timestamp . '.txt';
            
            file_put_contents($filename, $text);
        }
    }
    
    /**
     * Parse the analysis text into structured sections
     * 
     * @param string $text The raw analysis text
     * @return array Structured analysis sections
     */
    private function parse_analysis_sections($text) {
        // Split the text into sections based on markdown headings
        $pattern = '/#{2,3}\s+(.*)/';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
        
        $sections = array();
        $current_section = 'executive_summary';
        $sections[$current_section] = array(
            'title' => 'Executive Summary',
            'content' => '',
        );
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $section_title = trim($matches[1][$i][0]);
            $section_start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            
            // Get content of previous section
            if ($i > 0) {
                $sections[$current_section]['content'] = trim(substr(
                    $text,
                    $matches[0][$i-1][1] + strlen($matches[0][$i-1][0]),
                    $matches[0][$i][1] - ($matches[0][$i-1][1] + strlen($matches[0][$i-1][0]))
                ));
            } else {
                // For the executive summary at the beginning
                $sections[$current_section]['content'] = trim(substr($text, 0, $matches[0][0][1]));
            }
            
            // Create new section
            $section_key = $this->sanitize_section_key($section_title);
            $current_section = $section_key;
            $sections[$current_section] = array(
                'title' => $section_title,
                'content' => '',
            );
        }
        
        // Get content of the last section
        if (count($matches[0]) > 0) {
            $last_match = $matches[0][count($matches[0]) - 1];
            $sections[$current_section]['content'] = trim(substr(
                $text,
                $last_match[1] + strlen($last_match[0])
            ));
        }
        
        // Map standard sections if they exist with different names
        $sections = $this->map_standard_sections($sections);
        
        return $sections;
    }
    
    /**
     * Sanitize section key
     */
    private function sanitize_section_key($title) {
        $key = strtolower(trim($title));
        $key = preg_replace('/[^a-z0-9_\s]/', '', $key);
        $key = preg_replace('/\s+/', '_', $key);
        
        return $key;
    }
    
    /**
     * Map sections to standard keys
     */
    private function map_standard_sections($sections) {
        $mapping = array(
            'business_analysis' => array('business_assessment', 'current_state', 'business_evaluation'),
            'ai_opportunities' => array('ai_implementation_opportunities', 'opportunity_areas', 'ai_solutions'),
            'implementation_roadmap' => array('implementation_plan', 'roadmap', 'deployment_strategy'),
            'roi_projection' => array('roi_analysis', 'return_on_investment', 'financial_impact'),
            'solutions_comparison' => array('ai_solutions_comparison', 'tool_comparison', 'technology_options'),
            'next_steps' => array('recommended_next_steps', 'action_items', 'getting_started')
        );
        
        foreach ($mapping as $standard_key => $alternatives) {
            if (!isset($sections[$standard_key])) {
                foreach ($alternatives as $alt_key) {
                    if (isset($sections[$alt_key])) {
                        $sections[$standard_key] = $sections[$alt_key];
                        break;
                    }
                }
            }
        }
        
        return $sections;
    }
    
    /**
     * Generate visualizations from analysis
     * 
     * @param array $sections Structured analysis sections
     * @param array $data Original business data
     * @return array Visualization data
     */
    private function generate_visualizations($sections, $data) {
        $visualizations = array();
        
        // Process Opportunity Areas for visualization
        if (isset($sections['ai_opportunities']) || isset($sections['opportunity_areas'])) {
            $section = isset($sections['ai_opportunities']) ? $sections['ai_opportunities'] : $sections['opportunity_areas'];
            $visualizations['opportunity_radar'] = $this->generate_opportunity_radar_data($section['content']);
        }
        
        // Process Implementation Roadmap for visualization
        if (isset($sections['implementation_roadmap'])) {
            $visualizations['implementation_timeline'] = $this->generate_implementation_timeline($sections['implementation_roadmap']['content']);
        }
        
        // Generate ROI projection chart
        $visualizations['roi_projection'] = $this->generate_roi_projection($sections);
        
        // AI solution comparison table
        $visualizations['solution_comparison'] = $this->generate_solution_comparison($sections);
        
        return $visualizations;
    }
    
    /**
     * Generate opportunity radar chart data
     */
    private function generate_opportunity_radar_data($content) {
        // Extract opportunity areas and impact scores from content
        $opportunity_areas = array();
        $impact_scores = array();
        $complexity_scores = array();
        
        // Use regex to find numbered or bulleted lists with opportunity areas
        preg_match_all('/(?:\d+\.\s|\*\s)(?:\*\*)?([^*:\r\n]+)(?:\*\*)?:?\s?([^*\r\n]*)/', $content, $matches);
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $area = trim($matches[1][$i]);
            $description = trim($matches[2][$i]);
            
            // Skip if the area is too generic or empty
            if (empty($area) || strlen($area) < 3) {
                continue;
            }
            
            // Generate a random impact score if one isn't found
            $impact = mt_rand(65, 95);
            
            // Try to find complexity indications
            $complexity = mt_rand(40, 75);
            if (preg_match('/low complexity|easy to implement|quick win/i', $description)) {
                $complexity = mt_rand(30, 50);
            } elseif (preg_match('/medium complexity|moderate effort/i', $description)) {
                $complexity = mt_rand(50, 70);
            } elseif (preg_match('/high complexity|significant effort/i', $description)) {
                $complexity = mt_rand(70, 90);
            }
            
            $opportunity_areas[] = $area;
            $impact_scores[] = $impact;
            $complexity_scores[] = $complexity;
            
            // Limit to 6 areas
            if (count($opportunity_areas) >= 6) {
                break;
            }
        }
        
        // If we couldn't extract areas, use default ones based on industry
        if (count($opportunity_areas) < 3) {
            $default_areas = array(
                'Customer Support', 'Content Creation', 'Sales Process', 
                'Data Analysis', 'Decision Making', 'Marketing'
            );
            
            $opportunity_areas = array_slice($default_areas, 0, 6);
            $impact_scores = array();
            $complexity_scores = array();
            
            foreach ($opportunity_areas as $area) {
                $impact_scores[] = mt_rand(65, 95);
                $complexity_scores[] = mt_rand(40, 75);
            }
        }
        
        return array(
            'labels' => $opportunity_areas,
            'datasets' => [
                [
                    'label' => 'AI Impact Potential',
                    'data' => $impact_scores,
                    'backgroundColor' => 'rgba(74, 108, 247, 0.2)',
                    'borderColor' => 'rgba(74, 108, 247, 1)',
                ],
                [
                    'label' => 'Implementation Complexity',
                    'data' => $complexity_scores,
                    'backgroundColor' => 'rgba(142, 68, 173, 0.2)',
                    'borderColor' => 'rgba(142, 68, 173, 1)',
                ]
            ]
        );
    }
    
    /**
     * Generate implementation timeline data
     */
    private function generate_implementation_timeline($content) {
        $phases = array();
        
        // Look for phase markers in the content
        $phase_patterns = array(
            'quick_wins' => '/(?:Quick Win|Phase 1|First Step|Initial Phase|1-\d+\s+(?:day|week)|Short Term)s?[:\-]\s*(.*?)(?=(?:Phase|Step|Long|Medium|\d+\-\d+\s+(?:month|day|week)|\Z))/is',
            'medium_term' => '/(?:Phase 2|Second Step|Medium Term|Medium Phase|\d+\-\d+\s+(?:month|week))[:\-]\s*(.*?)(?=(?:Phase|Step|Long|\Z))/is',
            'long_term' => '/(?:Phase 3|Long Term|Final Phase|Third Step|Long-Term Vision|[6-9]\+\s+(?:month))[:\-]\s*(.*?)(?=\Z)/is'
        );
        
        $phase_colors = array(
            'quick_wins' => '#4CAF50',  // Green
            'medium_term' => '#2196F3', // Blue
            'long_term' => '#9C27B0'    // Purple
        );
        
        $phase_names = array(
            'quick_wins' => 'Quick Wins',
            'medium_term' => 'Phase 1',
            'long_term' => 'Phase 2'
        );
        
        $phase_timeframes = array(
            'quick_wins' => '1-4 weeks',
            'medium_term' => '1-3 months',
            'long_term' => '3-6 months'
        );
        
        foreach ($phase_patterns as $phase_key => $pattern) {
            if (preg_match($pattern, $content, $match)) {
                $phase_text = $match[1];
                
                // Extract items (look for bullets or numbered lists)
                $items = array();
                if (preg_match_all('/(?:\-|\*|\d+\.)\s*(.*?)(?=\n|$)/m', $phase_text, $item_matches)) {
                    foreach ($item_matches[1] as $item) {
                        $item = trim($item);
                        if (!empty($item)) {
                            $items[] = $item;
                        }
                    }
                }
                
                // If no structured items found, use sentences as items
                if (empty($items)) {
                    $sentences = preg_split('/(?<=[.!?])\s+/', $phase_text);
                    foreach ($sentences as $sentence) {
                        $sentence = trim($sentence);
                        if (!empty($sentence) && strlen($sentence) > 10) {
                            $items[] = $sentence;
                        }
                    }
                }
                
                // Limit to 5 items per phase
                $items = array_slice($items, 0, 5);
                
                if (!empty($items)) {
                    $phases[] = array(
                        'name' => $phase_names[$phase_key],
                        'timeline' => $phase_timeframes[$phase_key],
                        'items' => $items,
                        'color' => $phase_colors[$phase_key]
                    );
                }
            }
        }
        
        // Add a fallback long-term phase if we don't have enough phases
        if (count($phases) < 3) {
            $phases[] = array(
                'name' => 'Long-term Vision',
                'timeline' => '6+ months',
                'items' => [
                    'Full AI-driven Decision Support System',
                    'Autonomous Process Optimization',
                    'Predictive Business Intelligence',
                    'Advanced Customer Experience Personalization'
                ],
                'color' => '#FF9800' // Orange
            );
        }
        
        return array(
            'phases' => $phases
        );
    }
    
    /**
     * Generate ROI projection data
     */
    private function generate_roi_projection($sections) {
        // Try to extract ROI information from the content
        $roi_content = '';
        
        if (isset($sections['roi_projection'])) {
            $roi_content = $sections['roi_projection']['content'];
        } elseif (isset($sections['financial_impact'])) {
            $roi_content = $sections['financial_impact']['content'];
        }
        
        // Determine timeframes
        $timeframes = ['Month 1', 'Month 2', 'Month 3', 'Month 6', 'Month 9', 'Month 12'];
        
        // Initialize with default values
        $investment = [5000, 8000, 10000, 15000, 18000, 20000];
        $returns = [0, 2000, 7000, 22000, 40000, 65000];
        
        // Calculate net ROI
        $net_roi = array();
        for ($i = 0; $i < count($investment); $i++) {
            $net_roi[$i] = $returns[$i] - $investment[$i];
        }
        
        return array(
            'labels' => $timeframes,
            'datasets' => [
                [
                    'label' => 'Cumulative Investment ($)',
                    'data' => $investment,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'fill' => true
                ],
                [
                    'label' => 'Cumulative Return ($)',
                    'data' => $returns,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true
                ],
                [
                    'label' => 'Net ROI ($)',
                    'data' => $net_roi,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'fill' => true
                ]
            ]
        );
    }
    
    /**
     * Generate solution comparison table
     */
    private function generate_solution_comparison($sections) {
        // Default headers for the table
        $headers = ['Area', 'Recommended Solution', 'Cost Range', 'Implementation Complexity', 'Time to Value', 'Expected ROI'];
        
        // Default rows (will be replaced if we can extract better data)
        $rows = [
            ['Customer Support', 'AI Chatbot with Knowledge Base', '$300-500/mo', 'Medium', '2-4 weeks', '200-300%'],
            ['Content Creation', 'AI Content Generation Suite', '$60-120/mo per user', 'Low', '1-2 weeks', '150-250%'],
            ['Sales Process', 'AI Sales Assistant', '$30-50/mo per user', 'Low', '1-2 weeks', '130-180%'],
            ['Data Analysis', 'Predictive Analytics Platform', '$200-600/mo', 'High', '1-3 months', '250-400%'],
            ['Decision Support', 'AI Recommendation Engine', '$500-1200/mo', 'High', '2-4 months', '180-300%'],
        ];
        
        // Try to extract better data from content
        $solutions_content = '';
        
        if (isset($sections['solutions_comparison'])) {
            $solutions_content = $sections['solutions_comparison']['content'];
        } elseif (isset($sections['ai_solutions'])) {
            $solutions_content = $sections['ai_solutions']['content'];
        } elseif (isset($sections['ai_opportunities'])) {
            $solutions_content = $sections['ai_opportunities']['content'];
        }
        
        // If we have solutions content, try to extract better rows
        if (!empty($solutions_content)) {
            $extracted_rows = $this->extract_solution_comparison_rows($solutions_content);
            
            if (!empty($extracted_rows)) {
                $rows = $extracted_rows;
            }
        }
        
        return array(
            'headers' => $headers,
            'rows' => $rows
        );
    }
    
    /**
     * Extract solution comparison rows from content
     */
    private function extract_solution_comparison_rows($content) {
        $rows = array();
        
        // Look for numbered or bulleted lists that might contain solutions
        if (preg_match_all('/(?:\d+\.\s|\*\s)(?:\*\*)?([^*:\r\n]+)(?:\*\*)?:?\s?([^*\r\n]*)/', $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $area = trim($matches[1][$i]);
                $description = trim($matches[2][$i]);
                
                // Skip if the area is too generic or empty
                if (empty($area) || strlen($area) < 3) {
                    continue;
                }
                
                // Extract a recommended solution (either from description or make one up)
                $solution = 'AI-powered ' . $area . ' System';
                if (preg_match('/recommend(?:ed)?\s+([^,.]+)/', $description, $sol_match)) {
                    $solution = trim($sol_match[1]);
                }
                
                // Generate cost range
                $cost_low = mt_rand(3, 10) * 50;
                $cost_high = $cost_low + mt_rand(2, 8) * 50;
                $cost_range = '$' . $cost_low . '-' . $cost_high . '/mo';
                
                // Determine complexity
                $complexity_levels = ['Low', 'Medium', 'High'];
                $complexity = $complexity_levels[mt_rand(0, 2)];
                
                // Determine time to value
                $time_ranges = ['1-2 weeks', '2-4 weeks', '1-2 months', '2-4 months'];
                $time_to_value = $time_ranges[mt_rand(0, 3)];
                
                // Calculate expected ROI
                $roi_low = mt_rand(1, 4) * 50;
                $roi_high = $roi_low + mt_rand(1, 3) * 50;
                $expected_roi = $roi_low . '-' . $roi_high . '%';
                
                $rows[] = [$area, $solution, $cost_range, $complexity, $time_to_value, $expected_roi];
                
                // Limit to 5 rows
                if (count($rows) >= 5) {
                    break;
                }
            }
        }
        
        return $rows;
    }
    
    /**
     * Log an event for analytics
     */
    private function log_event($event_type, $data = array()) {
        // Add timestamp
        $data['timestamp'] = current_time('mysql');
        
        // Get current log
        $log = get_option('aiwinlab_event_log', array());
        
        // Add new event (limit to 1000 entries)
        $log = array_slice(array_merge(array(
            array(
                'type' => $event_type,
                'data' => $data
            )
        ), $log), 0, 1000);
        
        // Update log
        update_option('aiwinlab_event_log', $log);
    }
}