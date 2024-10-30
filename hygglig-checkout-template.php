<?php
/*
Template Name: No Header/Footer
Template Post Type: post, page
*/
?>

<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>
    <body>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
            get_template_part( 'content', 'page' );
        endwhile;
        endif; ?>
        <?php wp_footer(); ?>
    </body>
</html>
