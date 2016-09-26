<?php 
/*
Plugin Name: My Contibutors Plugin
Plugin URI: http://www.example.com
Description: This plugin is used to dispay the contributors on single post.
Version: 1.0
Author: Chaitali Kothari
Author URI: http://www.authorurl.com
License: GPL2
*/


/* 
* Function the meta box on posts in admin area.
*/
function prfx_custom_meta() {
    add_meta_box( 'prfx_meta', __( 'Contributors', 'prfx-textdomain' ), 'prfx_meta_callback', 'post' );
}
add_action( 'add_meta_boxes', 'prfx_custom_meta' );

/* 
* Outputs the content of the meta box 
*/
function prfx_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
	$se_arr = $prfx_stored_meta['contributors_name'];
	$unse_arr = unserialize($se_arr[0]);
	if(empty($unse_arr)){
		$unse_arr=array();
	}
	$blogusers = get_users( 'blog_id=$GLOBALS[blog_id]' );
	foreach ( $blogusers as $user ) {
		?>
		<input type="checkbox" name="contributors[]" id="<?php echo $user->ID; ?>" value="<?php echo $user->ID; ?>" <?php if (in_array($user->ID, $unse_arr) ) echo "checked"; ?> /><?php echo esc_html( $user->display_name ); ?>
	<?php
	}
}

/* 
* Saves the custom meta input 
*/
function prfx_meta_save( $post_id ) {
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	if( isset( $_POST[ 'contributors' ] ) ) {
		$array = $_POST[ 'contributors' ];
		update_post_meta($post_id, 'contributors_name', $array);
	}	
}
add_action( 'save_post', 'prfx_meta_save' );

/*
* Adds the content at the end of post.
*/
function add_after_post_content($content) {
	if(is_single()) {
		$se_arr = get_post_meta( get_the_ID(), 'contributors_name', true );
		if($se_arr)
		{
			$content .= "<h3>Contributors</h3>";
			foreach($se_arr as $sarr)
			{
				$user_info = get_userdata($sarr);
				$content .= "<div class=contriclass style='display: inline-block;margin-right: 30px;width: 25%;'>";
				$content .= "<a href=".get_author_posts_url( $sarr )." style='text-align: center;'>";
				$content .= "<img src=".get_avatar_url( $user_info->user_email )." style='border-radius:55px;margin: 0 auto;'>";
				$content .= "<p>".$user_info->display_name."</p>";
				$content .= "</a>";
				$content .= "</div>";
			}
		}
	}
	return $content;
}
add_filter('the_content', 'add_after_post_content');

/*
*
*/
?>