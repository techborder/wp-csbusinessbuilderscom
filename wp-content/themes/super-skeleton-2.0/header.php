<!DOCTYPE html >
<!--[if lt IE 7 ]> <html class="no-js ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> 
<html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>

	<meta charset="utf-8">	
	
	<title><?php 
		/*
		 * Print the <title> tag based on what is being viewed.
		 */
		global $page, $paged;
	
		wp_title( '|', true, 'right' );
	 
		// Add the blog name.
		bloginfo( 'name' );
	
		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
	
		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( __( 'Page %s', 'skeleton' ), max( $paged, $page ) );
	
		?></title>
	
	
	<?php $theme_options = get_option('option_tree'); ?>
	
	
	<!-- Mobile Specific Metas
  	================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 
	
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<link rel="shortcut icon" href="<?php echo get_option_tree('favicon'); ?>" type="image/gif" />
		
	
	<?php if ( ! isset( $content_width ) ) 
	    $content_width = 960;
	?>	
	
	<?php wp_head(); ?>	
	<?php get_template_part( 'element', 'styleloader' ); ?>
	
</head>


<!-- Start the Markup
================================================== -->
<body <?php body_class(); ?> >

<?php if (get_option_tree('top_hat') == 'No') { } else { ?>	
	<?php get_template_part( 'element', 'tophat' ); ?>
<?php } ?>
	
<!-- Super Container for entire site -->
<div class="super-container full-width" id="section-header">

	<!-- 960 Container -->
	<div class="container">			
		
		<!-- Header -->
		<header>
		<div class="sixteen columns">
			 
			 
			<?php if(get_option_tree('logo_center') == 'Yes') { $logo_center = 'sixteen omega'; ?>
				<style type="text/css">.logospace{text-align: center;}</style>			
			<?php } ?>
			 
			 
			<!-- Branding -->
			<div class="six columns alpha logospace <?php echo $logo_center; ?>">
				<a href="<?php echo home_url(); ?>/" title="<?php echo bloginfo('blog_name'); ?>">
					<h1 id="logo" style="margin-top: 40px; margin-bottom: 30px;">
						<?php $logopath = (get_option_tree('logo')) ? get_option_tree('logo') : WP_THEME_URL . "/assets/images/theme/pacifico-logo/logo$lightdark.png"; ?>    		    		
	        			<img id="logotype" src="<?php echo $logopath; ?>" alt="<?php echo bloginfo('blog_name'); ?>" />
	         		</h1>
				</a>
			</div>
			<!-- /End Branding -->
			
			
			<!-- Promo Space -->
			<?php if(get_option_tree('logo_center') == 'Yes') { } else { ?>
			<div class="ten columns omega">
				<?php if (get_option_tree('header_promo')) : ?>
					<a href="<?php echo get_option_tree('header_promo_url'); ?>" class="header-image">
						<img src="<?php echo get_option_tree('header_promo'); ?>" alt="image" />
					</a>
				<?php endif; ?>
			</div> 
			<?php } ?>
			<!-- /Promo Space -->
			
			
			<hr class="remove-bottom"/>
		</div>
		
		<?php get_template_part( 'element', 'navigation' ); ?>
			
		</header>
		<!-- /End Header -->
	
	</div>
	<!-- End 960 Container -->
	
</div>
<!-- End SuperContainer -->


<!-- ============================================== -->
		
				
<!-- Frontpage Conditionals -->
<?php if ( is_home() ) : ?> 

	<?php if(get_option_tree('frontpage_slider') == 'Yes') { ?>
		<?php get_template_part( 'element', 'flexslider' ); ?>
	<?php } ?>
				
<?php endif; ?>

		
<!-- Page Conditionals -->
<?php if ( is_page() ) : ?>

	<!-- Frontpage Slider Conditional -->		
	<?php if(get_custom_field('frontpage_slider') == 'Yes') :  				
		get_template_part( 'element', 'flexslider' ); 								
	endif; ?>

	<!-- PageSlider Conditional -->
	<?php if(get_custom_field('image_slider') == 'Yes') :				
		get_template_part( 'element', 'pageslider' ); 				
	endif; ?>
				
	<!-- Set a global variable for the page ID that we can use in the footer (outside of the loop) -->
	<?php $GLOBALS[ 'isapage' ] = 'yes'; 
	global $wp_query;
	$GLOBALS[ 'ourpostid' ] = $wp_query->post->ID; ?>

<?php endif; ?>


<!-- Post Conditionals -->
<?php if ( is_single() ) : ?>

	<!-- PageSlider Conditional -->
	<?php if(get_custom_field('image_slider') == 'Yes') {				
		get_template_part( 'element', 'postslider' ); 				
	} else {}; ?>
	
	<!-- Set a global variable for the page ID that we can use in the footer (outside of the loop) -->
	<?php $GLOBALS[ 'isapost' ] = 'yes'; 
	global $wp_query;
	$GLOBALS[ 'ourpostid' ] = $wp_query->post->ID; ?>

<?php endif; ?>
		
		
<!-- ============================================== -->