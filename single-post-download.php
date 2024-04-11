<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package revenue
 */

get_header(); 

if ( function_exists( 'revenue_set_post_views' ) ) :
	revenue_set_post_views(get_the_ID());
endif;
?>
		<?php
		if ( have_posts() ) : the_post();

			?>

			<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package revenue
 */	
// echo $post->post_name;
// echo '<BR>';
// echo $post->post_title;
/*
$actualPost = get_posts([
	'name' => get_query_var('pagename'),
	// 'post_title' => $post->post_title,
	'post_type' => 'post',
	// 'post_status' => 'publish',
	'numberposts' => 1,
]);
*/
$actualPost = new WP_Query([
	'post_type' => 'post',
	'name' => $post->post_name,
	'posts_per_page' => 1,
	// 'orderby' => 'post_name',
]);
$actualPost = $actualPost->posts[0];
// var_dump($actualPost);
$post = $actualPost;
setup_postdata($post);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="http://schema.org/Article">
Put The wp template design for the child page here ...

</article><!-- #post-## -->


		<?php endif; // End of the loop.
		?>

<?php get_footer(); ?>
