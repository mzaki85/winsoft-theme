<?php
/**
* Plugin Name: Files Download Page
* Plugin URI: gramfile.com
* Description: Provides permalinks for download page.
* Version: 1.0
* Author: Mostafa Mohsen
* Author URI: https://gramfile.com/
**/

register_activation_hook( __FILE__, 'files10_files_download_page_install' );

function files10_files_download_page_install(){
	flush_rewrite_rules();
}



// Add new Custom Post Type 'post-download' //
function files10_new_custom_post_type(){
	register_post_type( 'post-download', [
		'labels' => [
			'name' => 'Posts Download',
			'singular_name' => 'Post Download'
		],
		'public'      => true,
		'has_archive' => false,
		'exclude_from_search' => true,
		'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
		'rewrite' =>  [
			'slug' => '/download',
			'with_front'          => false,
        'pages'               => false,
        'feeds'               => false,
		]
	] );
}
add_action('init', 'files10_new_custom_post_type');


// Remove existing permalinks to add the new one. //
add_action( "after_setup_theme", 'files10_custom_remove_permalink', 1);
function files10_custom_remove_permalink(){
    remove_action( "after_setup_theme", 'active_permalinks');
}

// Custom Active Permalinks //
add_action( "after_setup_theme", 'files10_custom_active_permalinks');

function files10_custom_active_permalinks() {
	global $wp_rewrite;
	$rewrite_args = array();
	$rewrite_args['walk_dirs'] = false;
	add_rewrite_rule( '([^/]+)/download/?$', 'index.php?post_type=post-download&name=$matches[1]', 'top' );
}


// Change the 'post-download' permalink //
function files10_permalink( $post_link, $id = 0, $leavename ) {
    global $wp_rewrite;
    $post = &get_post( $id );
    if($post->post_type != 'post-download'){
    	return $post;
    }
    if ( is_wp_error( $post ) ){
        return $post;
    }
    $newlink = home_url( user_trailingslashit( $post->post_name . '/download' ) );
    
    return $newlink;
}

add_filter('post_type_link', 'files10_permalink', 1, 3);

// Add new 'post-download' post after a new post. //
add_action('new_to_publish', 'files10_add_download_post_after_post_publish', 10, 1);
add_action('draft_to_publish', 'files10_add_download_post_after_post_publish', 10, 1);
add_action('auto-draft_to_publish', 'files10_add_download_post_after_post_publish', 10, 1);
add_action('pending_to_publish', 'files10_add_download_post_after_post_publish', 10, 1);
function files10_add_download_post_after_post_publish($post) {

		if(!isset($post)){
			return;
		}

    // Only set for post_type = post!
    if ( 'post' !== $post->post_type ) {
        return;
    }
    $newPost = $post;

    if($post->status != 'publish'){
    	// return;
    }
    // insert corresponding post-download cpt.
    if($newPost){
    			// TODO: Check if exists, then update
			// Create post object
			$postDownload = [
				'post_type'     => 'post-download',
			  'post_title'    =>  $post->post_title,
			  'post_name'    =>  $post->post_name,
			  'post_content'  => 'This is a duplicate of '. $post->post_name ,
			  'post_status'   => 'publish',
			];
			// Insert the post into the database
			wp_insert_post( $postDownload );
		}
}


add_action( 'admin_menu', 'files10_download_plugin_admin_menu' );
function files10_download_plugin_admin_menu() {
    add_options_page( __('Files Download Page'), __('Files Download Page', 'textdomain' ), 'manage_options', 'files-download-page', 'files10_download_option_page' );
}

/* 
 * THE ACTUAL PAGE 
 * */
function files10_download_option_page() {
?>
  <div class="wrap">
      <h2><?php _e('My Plugin Options', 'textdomain'); ?></h2>
      <form action="options.php" method="POST">
      	<h3>Sync Old Posts</h3>
        <h4>( Use these when sync is not working (Times out) - <b style='color:darkgreen;'>Delete -> Add</b> )<br><br></h4>
        <a href="<?=admin_url('/options-general.php?page=files-download-page&add_old_posts_download_page=delete');?>"  class="button button-primary">Delete Old Posts</a>
        <a href="<?=admin_url('/options-general.php?page=files-download-page&add_old_posts_download_page=add');?>"  class="button button-primary">Add from Old Posts</a>
        <br><br>
        <h4>(Requires high server resources when posts count is large)<br><br></h4>
        <a href="<?=admin_url('/options-general.php?page=files-download-page&add_old_posts_download_page=sync');?>"  class="button button-primary">Sync Old Posts</a>

      </form>
  </div>
<?php }


function files10_download_add_old_posts_download(){
	ini_set('max_execution_time', 0); 
	if(!is_admin()){return;}

	if(!isset($_GET['add_old_posts_download_page'])){
		return;
	}
	
	if($_GET['add_old_posts_download_page'] == 'delete' || $_GET['add_old_posts_download_page'] == 'sync'){
		// Delete existing posts..
		$allPosts = new WP_Query(['post_type' => 'post-download','posts_per_page' => -1]);
		while($allPosts->have_posts()):
			$allPosts->the_post();
			wp_delete_post( get_the_ID(), true);
		endwhile;
	}
	
	if($_GET['add_old_posts_download_page'] == 'add' || $_GET['add_old_posts_download_page'] == 'sync'){
		// Add new posts
		$allPosts = new WP_Query(
			['post_type' => 'post','posts_per_page' => -1]
		);

		while($allPosts->have_posts()):
			$allPosts->the_post();
			$allPost = get_post(get_the_ID());

			$postDownload = [
						'post_type'     => 'post-download',
					  'post_title'    =>  $allPost->post_title,
					  'post_name'     => $allPost->post_name,
					  'post_content'  => 'This is a duplicate of '. $allPost->post_name ,
					  'post_status'   => 'publish',
					];

					// Insert the post into the database
			wp_insert_post( $postDownload );
		endwhile;
	}
	
}
add_action('init', 'files10_download_add_old_posts_download');
