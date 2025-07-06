<?php
// templates/reports-list.php
?>
<div class="aiwinlab-reports-container">
    <?php if ($show_filters): ?>
    <div class="reports-filters">
        <form method="get" action="">
            <div class="filter-group">
                <label for="industry-filter"><?php _e('Filter by Industry', 'aiwinlab'); ?></label>
                <select id="industry-filter" name="industry">
                    <option value=""><?php _e('All Industries', 'aiwinlab'); ?></option>
                    <?php
                    // Get all used industries
                    global $wpdb;
                    $industries = $wpdb->get_col("
                        SELECT DISTINCT meta_value
                        FROM {$wpdb->postmeta}
                        WHERE meta_key = 'industry'
                        AND post_id IN (
                            SELECT ID FROM {$wpdb->posts}
                            WHERE post_type = 'aiwinlab_report'
                            AND post_status = 'publish'
                        )
                        ORDER BY meta_value ASC
                    ");
                    
                    foreach ($industries as $industry) {
                        $selected = isset($_GET['industry']) && $_GET['industry'] === $industry ? 'selected' : '';
                        echo '<option value="' . esc_attr($industry) . '" ' . $selected . '>' . esc_html($industry) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="category-filter"><?php _e('Filter by Category', 'aiwinlab'); ?></label>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'business_category',
                    'hide_empty' => true,
                ));
                
                if (!empty($categories) && !is_wp_error($categories)):
                ?>
                <select id="category-filter" name="category">
                    <option value=""><?php _e('All Categories', 'aiwinlab'); ?></option>
                    <?php
                    foreach ($categories as $category) {
                        $selected = isset($_GET['category']) && $_GET['category'] === $category->slug ? 'selected' : '';
                        echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
                <?php endif; ?>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="filter-btn"><?php _e('Apply Filters', 'aiwinlab'); ?></button>
                <a href="<?php echo esc_url(remove_query_arg(array('industry', 'category'))); ?>" class="reset-btn"><?php _e('Reset', 'aiwinlab'); ?></a>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <div class="reports-list">
        <?php if ($reports_query->have_posts()): ?>
            <?php while ($reports_query->have_posts()): $reports_query->the_post(); ?>
                <?php
                $report_id = get_the_ID();
                $business_name = get_post_meta($report_id, 'business_name', true);
                $industry = get_post_meta($report_id, 'industry', true);
                $report_data = get_post_meta($report_id, 'analysis_result', true);
                
                $executive_summary = '';
                if (!empty($report_data) && isset($report_data['structured_analysis']) && isset($report_data['structured_analysis']['executive_summary'])) {
                    $executive_summary = wp_trim_words($report_data['structured_analysis']['executive_summary']['content'], 30, '...');
                }
                ?>
                <div class="report-card">
                    <div class="report-header">
                        <h3 class="report-title">
                            <a href="<?php the_permalink(); ?>"><?php echo esc_html($business_name); ?> - <?php _e('AI Analysis Report', 'aiwinlab'); ?></a>
                        </h3>
                        <div class="report-meta">
                            <span class="report-date"><i class="far fa-calendar-alt"></i> <?php echo get_the_date(); ?></span>
                            <?php if ($industry): ?>
                            <span class="report-industry"><i class="far fa-building"></i> <?php echo esc_html($industry); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="report-summary">
                        <?php if ($executive_summary): ?>
                        <p><?php echo esc_html($executive_summary); ?></p>
                        <?php else: ?>
                        <p><?php _e('AI analysis report for business optimization and AI implementation opportunities.', 'aiwinlab'); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="report-actions">
                        <a href="<?php the_permalink(); ?>" class="view-report-btn"><?php _e('View Full Report', 'aiwinlab'); ?></a>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php
            // Pagination
            $pagination = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo; Previous', 'aiwinlab'),
                'next_text' => __('Next &raquo;', 'aiwinlab'),
                'total' => $reports_query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
            ));
            
            if ($pagination) {
                echo '<div class="reports-pagination">' . $pagination . '</div>';
            }
            ?>
            
        <?php else: ?>
            <div class="no-reports">
                <div class="no-reports-icon"><i class="far fa-folder-open"></i></div>
                <h3><?php _e('No Reports Found', 'aiwinlab'); ?></h3>
                <p><?php _e('No AI analysis reports match your criteria. Try adjusting your filters or create a new analysis.', 'aiwinlab'); ?></p>
                <a href="<?php echo esc_url(get_permalink(get_option('aiwinlab_wizard_page_id'))); ?>" class="create-report-btn"><?php _e('Create New Analysis', 'aiwinlab'); ?></a>
            </div>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </div>
</div>