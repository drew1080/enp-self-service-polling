<?php

/*

Template Name: Latest News Template

*/	

?>

<?php get_header(); ?>
<?php get_template_part(THEME_LOOP."loop","start"); ?> 
          <div class="post-title">
		<h1 class="entry-title"><?php the_title();?></h1>
	</div>
    	<div class="workshop">
         <?php query_posts( array( 'post_type'=> 'post', 'cat' => '42' , 'showposts' => '50' , 'order' => 'ASC') ); if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
             <div class="contentloop"><div class="thumbnail">
			 <?php 
			 if ( has_post_thumbnail($thumbnail->ID)) { ?>
      <?php echo get_the_post_thumbnail($page->ID, 'medium') ?>
		<?php } else {?>
        <img src="<?php bloginfo('template_url'); ?>/images/noimg.png"/>
        <?php } ?>
			 </div>
            <div class="content">
        	 <h2><a href="<?php the_permalink() ?>" ><?php echo short_title('...', 30); ?></a></h2>
             	<span class="datestemp"><?php the_field('latest_news_date'); ?></span>
             	<div class="textbox"><?php the_excerpt(); ?></div>
                <a href="<?php the_permalink() ?>" class="readmore">Read More</a>
             </div>
             </div>
        <?php endwhile; else: ?>
        <?php endif; ?>
         </div>
      </div>
      <?php get_template_part(THEME_INCLUDES."sidebar"); ?> 
</div>

<?php get_footer(); ?>