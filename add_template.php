<?php
/*
 * Template Name: JUNN SYNC
 * Description: A Page Template with a darker design.
 */
//$result['code'] = $_POST['cmd'];

function __Update_post_meta( $post_id, $field_name, $value = '' )
{
    if ( empty( $value ) OR ! $value )
    {
        delete_post_meta( $post_id, $field_name );
    }
    elseif ( ! get_post_meta( $post_id, $field_name ) )
    {
        add_post_meta( $post_id, $field_name, $value );
    }
    else
    {
        update_post_meta( $post_id, $field_name, $value );
    }
}

function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}

function varDumpToString ($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}
function GmtTimeToLocalTime($time) {
	$tz = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $new_date = new DateTime($time);
    $new_date->setTimeZone($tz);
    return $new_date;/*->format('Y-m-d h:i:s')*/
}

if($_POST['json_data'] != null)
{	
	
	/*
	$result['post_id'] = wp_insert_post($new_post); 
	$result['user_login'] = $user->user_login;
	$result['user_email'] = $user->user_email;
	$result['json_data_return'] = $_POST['json_data'];
	$result['json_data'] = count($decoded2);
	$result['result'] = "success";
	*/
	$json_array = $_POST['json_data'];	//이미 JSON이다
	$insert_count = 0;
	$update_count = 0;
	$insert_error_count = 0;
	$update_error_count = 0;

	foreach($json_array as $post)
	{
		/*$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
		    'meta_query' => array(
		        'key' => 'steem_url',
		        'value' => $post['steem_url'],
		        'compare' => 'LIKE'
		    )
		);*/
		$wp_posts = $wpdb->get_results("SELECT * FROM `wp_postmeta` WHERE `meta_key` = 'steem_url' AND `meta_value` = '{$post['steem_url']}'");
		
		$post_id = 0;
		$result["upc2"] = count($wp_posts);
		foreach ($wp_posts as $wp_post) {
			$result["upc3"] = $wp_post->post_id;
			$post_id = $wp_post->post_id;
		}
		if ($post_id > 0) //postMeta를 검색해서 동일하면 업데이트로,
		{
			//$wp_post = get_post( intval( $post_id ) );
			//setup_postdata( $wp_post );
			$last_update_of_post = get_the_modified_date('Y-m-d h:i:s',$post_id);


			$date = new DateTime($post['last_update']);
			$strGMT0 = $date->format('Y-m-d H:i:s');
			$strGMT9 = date( "Y-m-d H:i:s", strtotime( $date->format('Y-m-d h:i:s') ) + 9 * 3600 );
			$result["upc3"] = $strGMT9;
			$result["upc4"] = $last_update_of_post;
			$result["upc5"] = $post_id;
			
			if($strGMT9 > $last_update_of_post)
			{
				/*$this_post = array(
					'ID' => $post_id,
					'post_title' => $post['title'],
					'post_content' => $post['body'],
					//'post_modified' => $strGMT9,
					'post_modified' => $strGMT9,
					'post_category' => array(0)
				);
				wp_update_post($new_post);*/
				$wpdb->query( "UPDATE `$wpdb->posts` SET 
									`post_title` = '".$post['title']."' 
									`post_contnet` = '".$post['body']."' 
                                    `post_modified` = '".$strGMT9."'
                                    WHERE `ID` = '".$postID."'" );
				
				$update_count += 1;
				wp_set_post_tags( $postId, $post['tags'], false );	//false > rewrite
				Generate_Featured_Image( $post['first_image'],   $post_id );
				
			}
				
		}
		else //없으면 신규
		{	
			$user = get_user_by( 'login', $post['author']);
			$date = new DateTime($post['created_at']);
			$strGMT0 = $date->format('Y-m-d H:i:s');
			$strGMT9 = date( "Y-m-d H:i:s", strtotime( $date->format('Y-m-d h:i:s') ) + 9 * 3600 );

			$modified_date = new DateTime($post['last_update']);
			$modified_strGMT9 = date( "Y-m-d H:i:s", strtotime( $modified_date->format('Y-m-d h:i:s') ) + 9 * 3600 );
			/*$result['author'] = $post['author'];
			$result['title'] = $post['title'];
			$result['time'] = $strGMT0;
			$result['time2'] = $strGMT9;*/
			//$result['return'] = $post;
			$new_post = array(
				'post_author' => $user->ID,
				'post_title' => $post['title'],
				'post_content' => $post['body'],
				'post_name' => $post['author']."-".$post['permlink'],	//permlink
				'post_status' => 'publish',
				'post_date' => $strGMT9,
				'post_modified' => $modified_strGMT9,
				'post_type' => 'post',
				'post_category' => array(0)
			);/*
			$postId = wp_insert_post($new_post);
			if(!is_wp_error($post_id))
			{
				$insert_count += 1;
				wp_set_post_tags( $postId, $post['tags'], false );	//false > rewrite
				__Update_post_meta( $postId, 'steem_url', $post['steem_url'] );
				Generate_Featured_Image( $post['first_image'],   $post_id );
			}
			else
			{
				$insert_error_count += 1;
			}*/
		}
	}
	$result['insert_error_count'] = $insert_error_count;
	$result['insert_count'] = $insert_count;
	$result['update_error_count'] = $update_error_count;
	$result['update_count'] = $update_count;
	$result['result'] = 'success';
}
echo json_encode($result);