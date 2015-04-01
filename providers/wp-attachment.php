<?php
namespace Faker\Provider;

class WP_Attachment extends WP_Post {

	protected static $default = array(
		'ping_status' => array( 'closed', 'open' ),
		'comment_status' => array( 'closed', 'open' ),
	);

	public function post_type(){
		return 'attachment';
	}


}