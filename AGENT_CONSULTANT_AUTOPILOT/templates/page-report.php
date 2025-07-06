<?php
// templates/page-report.php

/**
 * Template for displaying single reports
 */

get_header();
?>

<main id="primary" class="site-main">

    <?php
    while (have_posts()) : the_post();
        
        // Get report content
        echo aiwinlab_get_report_template();
        
        // If comments are open or we have at least one comment, load up the comment template.
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        
    endwhile; // End of the loop.
    ?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();