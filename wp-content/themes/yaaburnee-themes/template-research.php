<?php

/*

Template Name: Research Template

*/	

?>
<?php get_header(); ?>


<!-- Container -->
<div class="container">
  <div id="primary-fullwith" class="researchpage">
    <?php if (have_posts()) : ?>
    <!-- Blank page container -->
    <article <?php post_class(); ?>>
      <div class="pdficon">
      <?php $summary_research = get_post_meta($post->ID, 'summary_research_', true);
if ( $summary_research ) { ?>
<a href="<?php the_field('summary_research_'); ?>" class="summaryresearch_btn"></a>
<?php } else { ?> 
<?php } ?>

 <?php $report_here = get_post_meta($post->ID, 'report_here', true);
if ( $report_here ) { ?>
<a href="<?php the_field('report_here'); ?>" class="fullreport_btn"></a>
<?php } else { ?> 
<?php } ?>
      
      </div>
      <?php get_template_part(THEME_SINGLE."page-title"); ?>
      <?php get_template_part(THEME_SINGLE."image"); ?>
      <div class="post-content">
        <?php the_content(); ?>
        <style type="text/css"><?php the_field('project_team_member'); ?>{display:block !important;}</style>

   
    <?php wp_reset_query(); ?>
    
     <div class="clearfix"></div>
     <br />
<hr />
<h3>Project Team Members:</h3>
 <div class="staff">
    <?php
$ids = array(778,779,780,781,1136,1141);
$id = get_the_id();
$my_query = query_posts(array('post_type'=> 'team'));
global $post;
foreach ($my_query as $post) {
   $posts_by_id[$post->ID] = $post;
}
foreach ($ids as $id) {
  if (!$post = $posts_by_id[$id]) continue;
   setup_postdata($post); ?>
   
  
      <div class="loopstaff staff<?php echo $id;?>" style="display:none;">
        <div class="imagebox"><img src="<?php the_field('member_image'); ?>" alt="<?php the_title();?>" width="300" height="300" class="alignnone size-medium wp-image-667" /></div>
        <div class="staffdesc">
          <h2><?php the_title();?></h2>
          <h3><?php the_field('member_designation'); ?></h3>
          <p><?php the_field('member_description'); ?></p>
        </div>
      </div>
   <?php 
}
?></div>
</div>
      </div>
    </article>
    <?php //get_template_part(THEME_SINGLE."share"); ?>
    <?php wp_reset_query(); ?>
    <?php else: ?>
    <p>
      <?php  _e('Sorry, no posts matched your criteria.' , THEME_NAME ); ?>
    </p>
    <?php endif; ?>
  </div>
</div>
<?php get_footer(); ?>
