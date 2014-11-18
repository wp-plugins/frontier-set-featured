<?php
/*
Plugin Name: Frontier Set Featured
Plugin URI: http://wordpress.org/extend/plugins/frontier-set-featured
Description: Set featured image automatically from images in the post. Will respect manually added Featured Image
Author: finnj
Version: 1.0.0
Author URI: http://http://wordpress.org/extend/plugins/frontier-set-featured
*/

define('FRONTIER_SET_FEATURED_VERSION', "1.0"); 
define('FRONTIER_frontier-set-featured_DIR', dirname( __FILE__ )); //an absolute path to this directory

function frontier_set_featured_image($post_id)
	{
	;
	//If no Featured Image (Thumbnail)
	if (!has_post_thumbnail($post_id))
		{
		$tmp_post 		= get_post($post_id);
		$tmp_content 	= $tmp_post->post_content;
		$tmp_posttype 	=  $tmp_post->post_type ? $tmp_post->post_type : "Unknown";
		
		//error_log("Set Featured: Post type: ".$tmp_posttype);
		if ($tmp_posttype != "post")
			{
			return;
			}
		
		//get images from the media library linked to the post (Note: Only on new upload images are linked to post, if image is removed from post, the link is not removed!)
		$linked_images 	= get_children( array("post_parent" => $post_id, "post_type" => "attachment", "post_mime_type" => "image", "numberposts" => -1) );
		
		// get images from post content - Only images from the media library is fetched, as featured images does not allow external images
		$used_images 	= array(); 
		preg_match_all( '/wp-image-([^"]*)"/i', $tmp_content, $used_images ) ;
		
		//Check if any images in post
		if ( count($used_images [1])>0 )
			{
			$used_image_ids = array();
			
			foreach ($used_images [1] as $tmp_image_id)
				{
				// remove trailing / from image IDs
				if (strpos($tmp_image_id, "/")>0)
					$tmp_image_id = substr($tmp_image_id, 0, -1);
				array_unshift($used_image_ids, $tmp_image_id);
				}
				
			//first check the linked images
			if ($linked_images) 
				{
				foreach ($linked_images as $attachment_id => $attachment) 
					{
					//Check if the linked image still is in the post content, and if so set that as featured image.
					if (in_array($attachment_id, $used_image_ids) && !has_post_thumbnail($post_id)) 
						set_post_thumbnail($post_id, $attachment_id);
					}
				}
				
			// If still no featured image, grab the first image from the content
			if (!has_post_thumbnail($post_id))
				set_post_thumbnail($post_id, $used_image_ids [0]);
			}
		}
	}

// add hooks	
add_action('save_post', 			'frontier_set_featured_image');
/*
add_action('draft_to_publish', 		'frontier_set_featured_image');
add_action('new_to_publish', 		'frontier_set_featured_image');
add_action('pending_to_publish',	'frontier_set_featured_image');
add_action('future_to_publish', 	'frontier_set_featured_image');
*/
?>