<?php
/*
 * Template Name: Good to Be Bad
 * Description: A Page Template with a darker design.
 */
$result['code'] = $_POST['cmd'];
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
    return $new_date/*->format('Y-m-d h:i:s')*/;
}

if($_POST['json_data'] != null)
{	
	$result['code'] = "100";
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

	foreach($json_array as $post)
	{
		$user = get_user_by( 'login', $post['author']);
		$date = new DateTime($post['created_at']);
		$strGMT0 = $date->format('Y-m-d H:i:s');
		$strGMT9 = date( "Y-m-d H:i:s", strtotime( $date->format('Y-m-d h:i:s') ) + 9 * 3600 );
		$result['author'] = $post['author'];
		$result['title'] = $post['title'];
		$result['time'] = $strGMT0;
		$result['time2'] = $strGMT9;
		//$result['return'] = $post;
		$new_post = array(
			'post_title' => $post['title'],
			'post_content' => $post['body'],
			'post_name' => $post['permlink'],
			'post_status' => 'publish',
			'post_date' => $strGMT9,
			'post_author' => $user->ID,
			'post_type' => 'post',
			'post_category' => array(0)
		);
		$postId = wp_insert_post($new_post);
		if( is_wp_error( $postId ) ) 
		{
			$result['time3'] = '1';
    		$result['error_msg'] =  $postId->get_error_message();
		}
		else
		{
			$result['time3'] = $postId;
			$insert_count++;
		}
		//break;
	}
	$result['insert_count'] = $insert_count;
	$result['result'] = 'success';
}
/*else
{
	$b = $_GET['cmd'];
	$result['code'] = "200";
	$result['msg'] = $b;
	$user = get_user_by( 'login', 'familydoctor');

	/*$new_post = array(
	'post_title' => $a,
	'post_content' => 'Lorem ipsum dolor sit amet...',
	'post_status' => 'publish',
	'post_date' => date('Y-m-d H:i:s'),
	'post_author' => $user->ID,
	'post_type' => 'post',
	'post_category' => array(0)
	);*/
	//$result['post_id'] = wp_insert_post($new_post); 
	/*$result['user'] = $user;
	$result['user_id'] = $user->ID;
	$result['user_login'] = $user->user_login;
	$result['user_email'] = $user->user_email;*/
//}

echo json_encode($result);