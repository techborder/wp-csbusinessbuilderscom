<?php
/*
 * Template Name: 3 Column Portfolio
*/

get_header();
?>


<!-- ============================================== -->


<!-- Default Portfolio View Variable for Footer -->					
<?php 					
if(get_custom_field('portfolio_view') == 'Hybrid') : 
	$GLOBALS[ 'portfolio_view' ] = 'Hybrid';
elseif(get_custom_field('portfolio_view') == 'List') :
	$GLOBALS[ 'portfolio_view' ] = 'List';					
else :			
	$GLOBALS[ 'portfolio_view' ] = 'Grid';
endif; 
?>					
<!-- End Default Portfolio View - Variable will be used in the footer -->


<!-- Super Container -->
<div class="super-container full-width main-content-area portfolio-3 isotope" id="section-content">

	<!-- 960 Container -->
	<div class="container">		
					
		<!-- CONTENT -->
		
			<!-- Page Title -->
			<?php if(get_custom_field('hide_title') == 'Yes') : else : ?>
			<div class="sixteen columns content">			
				<h1 class="title"><span><?php the_title(); ?></span></h1>	
			</div>
			<?php endif; ?>
			
			<!-- Page Content (if it exists) -->
			<?php while ( have_posts() ) : the_post(); ?>	
			<div class="sixteen columns content">
				<?php the_content(); ?>			
			</div>	
			<?php endwhile; ?>	
			
	
			<!-- Filter Navigation -->
			<div class="sixteen columns portfolio-nav">
				<p class="portfolio-filters" id="portfolio-filter">
					<span><?php _e('Filters:', 'skeleton') ?></span>					
					
					<a class="button" href="#" data-filter="*">All</a>
					
					<!-- Grab just the category slugs and list them using our markup -->
					<?php 
					 					
					if(get_post_custom_values('category_filter')) :     // If the category filter exists on this page...
					
					$cats = get_custom_field( 'category_filter', $option_tree ); // Returns an array of cat-slugs from the custom field.					
					
					foreach ( $cats as $cat ) {		
						$cat = get_cat_slug($cat);	// Leverages the functions.php get_cat_slug() function.
						$catsluglink = '<a class="button" href="#" data-filter=".'.$cat.'">'.$cat = str_replace('-',' ',$cat).'</a> ';  // Create a link using our markup now
						$acats[] = $catsluglink; 								// Turn the list of ID's into an ARRAY, $acats[]
					}			
				    
					$cat_string = join(' ', $acats);					// Join the ARRAY into a space-separated STRING 
					echo $cat_string;	
					endif;							
			
					?>
						
				</p>
					
				<p class="portfolio-view">
					<span><?php _e('Layout:', 'skeleton') ?></span>
					<span class="grid_btn 3-col-grid"><img src="<?php echo WP_THEME_URL; ?>/assets/images/theme/btn_grid.png" alt="Grid View" /></span> 
					<span class="hybrid_btn 3-col-hybrid"><img src="<?php echo WP_THEME_URL; ?>/assets/images/theme/btn_hybrid.png" alt="Hybrid View" /></span>
					<span class="list_btn 3-col-list"><img src="<?php echo WP_THEME_URL; ?>/assets/images/theme/btn_list.png" alt="List View" /></span>
				</p>
				<br /><br />
				<hr class="half-bottom" />
			</div> 			
			
			</div>
    
    		<div class="container portfolio-container">         
     	
     	
			<!-- Portfolio List-->  
			<div id="portfolio-list" class="content">
		
			<!-- CATEGORY QUERY + START OF THE LOOP -->
			<?php get_template_part( 'element', 'categoryfilterquery' ); ?>
			<?php while (have_posts()) : the_post(); ?>
				
				
				<!-- THE POST LOOP -->				
				
				<!-- ============================================ -->
			
				<!-- Grab the image path and set our variables -->
					<?php if (has_post_thumbnail( $post->ID )) {
		 								
						// Grab the URL for the thumbnail (featured image)
						$thumb = get_post_thumbnail_id(); 
						$image_full = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
						
							if (ot_get_option('default_image_height')) : 
								$imgheight = ot_get_option('default_image_height', $theme_options, false, true, 0 );
								$image = vt_resize( $thumb, '', 700, $imgheight, true );	
							else : 
								$image = vt_resize( $thumb, '', 700, 2000, false );	
							endif;
														
						// Check for a lightbox link, if it exists, use that as the value. 
						// If it doesn't, use the featured image URL from above.
						if(get_custom_field('lightbox_link')) { 							
							$lightbox_link = get_custom_field('lightbox_link'); 							
						} else {							
							$lightbox_link = $image_full[0];							
						}
					
					?>
			
						<!-- Begin Portfolio Module Container -->
						<div class="one-third column module-container 				
							
							<?php //FILTERS: Here's where we add in the individual category slugs for each individual post
							
								//Declare our post slug - we'll use it later for the lightbox gallery hook
								$post_slug = str_replace(" ", "-",$post->post_name);
											
								$postcats = get_the_category();
								if ($postcats) {
								  foreach($postcats as $cat) {
									echo $cat->slug . ' '; 
								  }
								}
							?>">
							
							<!-- Begin Module -->
							<div class="module" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>	
						
								<!-- Begin Module Image -->
								<div class="module-img">							
									<a href="<?php if (get_option_tree('open_as_lightbox') == 'Yes') { echo $lightbox_link; } else { the_permalink(); } ?>" <?php if (get_option_tree('open_as_lightbox') == 'Yes') { ?>data-rel="prettyPhoto[<?php echo $post_slug; ?>]"<?php } ?>>
										<img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" alt="<?php the_title(); ?>" />
										<span></span>
									</a>							
									<div class="lightboxLink">
										<a class="popLink boxLink" href="<?php echo $lightbox_link; ?>" data-rel="prettyPhoto[<?php the_title(); ?>]" title="<?php the_title(); ?>"></a>
									</div>						    
									<div class="thumbLink">
										<a class="popLink" href="<?php the_permalink(); ?>" title="Full Post"></a>
									</div>						    
								</div>
								<!-- End Module Image -->
								
								<!-- Begin Module Meta -->
								<div class="module-meta">
									<h5><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>	
									<hr class="half-bottom" />
									<?php echo excerpt(25); ?>						
											
									<!-- Invisible Lightbox Gallery Links -->
									<?php //Loop through the posts image attachment for the lightbox gallery
										if (get_option_tree('open_as_lightbox') == 'Yes') { ?>
										<!-- Display our invisible gallery links if they exist - for the lightbox -->
										<div style="display: none;" class="gallery_links">								
											<?php 								
											$args = array(
												'order'          => 'ASC',
												'post_type'      => 'attachment',
												'post_parent'    => $post->ID,
												'post_mime_type' => 'image',
												'post_status'    => null,
												'exclude' => get_post_thumbnail_id(),
												'numberposts'    => -1
											);
											
											$attachments = get_posts($args);			
											$post_slug = str_replace(" ", "-",$post->post_name);											
											
											if ($attachments) {
												foreach ($attachments as $attachment) {
													$attachment_url = wp_get_attachment_url($attachment->ID, 'full');
													echo '<a data-rel="prettyPhoto['.$post_slug.']" href="'.$attachment_url.'"></a>';
												}
											}
											?>
										</div>	
									<?php } ?>
									<!-- End Invisible Lightbox Gallery Links -->
									
								</div>	
								<!-- End Module Meta -->
								
							</div>
							<!-- End Module -->
							
						</div>
						<!-- End Module Container -->
				
				<?php } ?>
			
				<!-- ============================================ -->
						
			<?php endwhile; ?>
			<!-- /POST LOOP -->
			
		
		</div>	
		<!-- /CONTENT -->
		

	</div>
	<!-- /End 960 Container -->
	
	
	<!-- Portfolio Template Pagination Fix - For Isotype Plugin -->
	<div class="super-container full-width main-content-area portfolio-pag-fix" id="section-content">
		<div class="container">		
			<?php get_template_part( 'element', 'pagination' ); ?>	
		</div>
	</div>
		
		
				

	</div>
	<!-- /End 960 Container -->
	
</div>
<!-- /End Super Container -->


<!-- ============================================== -->


<?php get_footer(); ?>