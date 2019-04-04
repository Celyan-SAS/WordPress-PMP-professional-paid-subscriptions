<?php

class Ydcomptepromodel {

	public function __construct() {
		
	}

	public function getMoralePersoneByCode($code) {

		$args = array();
		$args['post_type'] = 'client_pro';
		$args['post_status'] = 'publish';
		$args['meta_query']['relation'] = 'AND';
		$args['meta_query'][] = array(
			  'key' => 'code_generated',
			  'value' => $code,
			  'compare' => '='
		);
//    $args['meta_query'][] =
//    array(
//        'key' => 'date_de_fin_dabonnement',
//        'value'   => date('Y-m-d'), //now
//        'type'    => 'DATE',
//        'compare' => '<'
//        );

		$results = get_posts($args);
		$result = false;
		if ($results && count($results) > 0):
			$result = $results[0];
		endif;
		return $result;
	}

	public function findIfUserIsMasterAccount($user_id) {

		$args = array();
		$args['post_type'] = 'client_pro';
		$args['post_status'] = 'publish';
		$args['meta_query']['relation'] = 'AND';
		$args['meta_query'][] = array(
			  'key' => 'master_account',
			  'value' => $user_id,
			  'compare' => '='
		);
		
		$results = get_posts($args);
		$result = false;
		if ($results && count($results) > 0):
			$result = $results[0];
		endif;
		return $result;
	}

	public function getAllUsersSubAccounts($masterAcctountId) {
		$args = array();
		$args['meta_key'] = 'link_id_morale';
		$args['meta_value'] = $masterAcctountId;

		$user_query = new WP_User_Query($args);

		$results = $user_query->get_results();

		return $results;
	}

	public function getAllUsersSubAccounts_notnull() {
		$args = array();
		$args['meta_query'] = array(
		  array(
			'key' => 'link_id_morale',
			'value'   => array(''),
			'type'    => 'numeric',
			'compare' => 'NOT IN'
			),
		);

		$user_query = new WP_User_Query($args);

		$results = $user_query->get_results();

		return $results;
	}

	public function getAllAccountsPayed() {
		$args = array();
		$args['post_type'] = 'client_pro';
		$args['post_status'] = 'publish';
		$args['posts_per_page'] = -1;
		$args['meta_query'] = array(
		  array(
			'key' => 'procodepaye',
			'value'   => 1,
			'compare' => '='
			),
		);

		$query = new WP_Query($args);
		//$results = $query->get_results();
		return $query;
	}
		
}

?>