<?php
/**
 *
 * This is template for Access Gate, so you can change styles here.
 *
 * @package WordPress
 *
 */

get_header(); ?>
<style>
    #gate-form-container {
        font-family: 'Open Sans', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        max-width: 30%;
        color: #fff;
        padding: 0;
        border: 0;
        font-size: 100%;
        font: inherit;
        vertical-align: baseline;
        border-color: #eeeeee;
        display: block;
    }

    #gate-form-container a {
        color: #fff;
        font-size: 13px;
    }

    #gate-form-container em {
        line-height: 17px;
        font-size: 12px;
        font-style: normal;
        line-height: 16px !important;
        line-height: 16px;
        right: 1px;
        text-align: justify;
        display: block;
    }

    #gate-form-container h1 {
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: inherit;
        font-size: 34px;
        line-height: 1.1em;
        margin-bottom: 14px;
        font-weight: 600;
        margin: 0;
        padding: 0;
        border: 0;
        vertical-align: baseline;
    }

    #gate-form-container h3 {
        color: #fff;
        font-family: inherit;
        font-size: 20px;
        line-height: 1.1em;
        margin-bottom: 8px;
        font-weight: 600;
        margin: 0;
        padding: 0;
        border: 0;
        vertical-align: baseline;
    }

    .ref-form-label{
        font-weight: bold;
        font-size: 0.92em;
        color: inherit;
        display: block;
        font-weight: 600;
        margin-bottom: 7px;
        position: relative;
        visibility: visible;
    }

    .ref-code-input{
        color: #fff !important;
        background-color: #007eb8 !important;
        border-color: #fff;
        border-width: 2px !important;
        border-radius: 2px;
        display: inline;
        margin-bottom: 0;
        min-width: 50px;
        padding: 13px !important;
        width: 100%;
        -webkit-appearance: none;
        border: 1px solid #e1e1e1;
        padding: 8px 6px;
        outline: none;
        font: 1em "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #777;
        margin: 0;
        width: 100%;
        height: 46px;
        display: block;
        margin-bottom: 20px;
        background: #fff;
        border-radius: 0px;
        transition: all 0.3s ease-in-out 0s;
    }

    .ref-code-button{
        border: 2px solid !important;
        border-color: #fff !important;
        color: #fff;
        border-width: 2px !important;
        background-color: #007eb8 !important;
        padding: 13px 10px 14px !important;
        min-width: 0;
        width: 100%;
    }

    .ref-code-button:hover{
        background-color: #aaaaaa !important;
    }

    .form-left{
        width: 74.5%;
        float: left;
    }

    .form-right{
        width: 24.5%;
        float: right;
    }

    .ref-code-gate-form{
        margin-bottom: -40px !important;
    }
</style>
<div id="gate-form-container" class="container">

    <?php

$args = array( 'post_type' => 'refcodegatepost', 'posts_per_page' => '1');
$loop = new WP_Query( $args );

$counter = 1;
     if( $loop->have_posts() ):

        while( $loop->have_posts() ): $loop->the_post(); global $post;
?>

    <?php the_content(); ?>

    <?php endwhile; else: ?>
      <p><?php _e('Sorry, gate is closed. Please come back later.'); ?></p>
    <?php endif; ?>

</div><!-- .content-area -->

<?php get_footer(); ?>
<?php exit(); ?>
