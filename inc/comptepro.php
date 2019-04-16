<?php

/**
 * 
 */
class Ydcomptepro {

	public function __construct() {

		//IT'S FOR ALERTS USERS///////////////////////////////////////////////////
		//to save alerts for user
		/** include script and css files * */
		add_action('wp_enqueue_scripts', array($this, 'alertsuser_scripts'));
		/** ajax to alerts for user * */
		add_action('wp_ajax_searchinallterms', array($this, 'searchinallterms'));
		add_action('wp_ajax_nopriv_searchinallterms', array($this, 'searchinallterms'));

		add_action('wp_ajax_savealertforuser', array($this, 'savealertforuser'));
		add_action('wp_ajax_nopriv_savealertforuser', array($this, 'savealertforuser'));

		add_action('wp_ajax_deletealertforuser', array($this, 'deletealertforuser'));
		add_action('wp_ajax_nopriv_deletealertforuser', array($this, 'deletealertforuser'));
		//get alerts for post
		add_action('transition_post_status', array($this, 'post_published_alertsender'), 10, 3);

		//PRO CODE////////////////////////////////////////////////////////////////
		add_action('wp_enqueue_scripts', array($this, 'subuser_scripts'));

		//special hook only for promembership cancel master account
		add_filter('pre_post_update', array($this, 'do_pre_save_procode_paidmembershippro'), 10, 2);

		//save code generated automaticly and paidmembership pro new user
		add_filter('acf/save_post', array($this, 'do_pre_save_procode_admin'), 10, 1);

		add_action('wp_ajax_deletesubuser', array($this, 'deletesubuser'));
		add_action('wp_ajax_nopriv_deletesubuser', array($this, 'deletesubuser'));

		//deal with the post
		add_action('init', array($this, 'process_post_procode'));

		//only for admin
		if (!is_admin()) {
			return;
		}
		add_filter('acf/load_value/name=code_generated', array($this, 'autoadd_code_generated'), 10, 3);
		
		add_action('add_meta_boxes', array($this, 'list_users_link_to_account_metabox'));
		
		add_action ( 'manage_client_pro_posts_custom_column', array( $this, 'pmppro_admincolumn_changes' ), 10, 2 );
		add_action ( 'manage_client_pro_posts_custom_column', array( $this, 'pmppro_admincolumn_changes_after' ), 1000, 2 );
		
		add_action( 'manage_posts_extra_tablenav', array( $this, 'register_pmppro_infos' ),100,1 );
		
		add_action( 'acf/render_field', array($this,'pmppro_change_visual_acffields'), 10, 1 );
	}
	
	public function pmppro_change_visual_acffields($field){
		
		if(($field['key'] == 'field_5ca61b158cafb' || $field['key'] == 'field_5ca61bdfb1580')
			&& $field['type'] == 'url' 
			&& $field['value']!=''){
			echo '<a href="'.$field['value'].'">Facture</a>';
		}
	}
	
	public function register_pmppro_infos($which){
		if($which == 'top'){
			$nbr_comptes_souscripts_payed = 0;
			$nbr_comptes_souscripts = 0;
			$nbr_comptes_activés = 0;
			
			$comptepromodel_o = new Ydcomptepromodel();
			$listPeople = $comptepromodel_o->getAllUsersSubAccounts_notnull();
			if($listPeople){
				$nbr_comptes_activés = count($listPeople);
			}
			
			$listPayed_query = $comptepromodel_o->getAllAccountsPayed();
			if(isset($listPayed_query->posts) && count($listPayed_query->posts)>0){
				foreach($listPayed_query->posts as $payed){
					$nombre_de_sub_comptes = get_field('nombre_de_sub_comptes',$payed->ID);
					if($nombre_de_sub_comptes){
						$nbr_comptes_souscripts_payed = $nbr_comptes_souscripts_payed+intval($nombre_de_sub_comptes);
					}
				}
			}
			
			$listAll_query = $comptepromodel_o->getAllAccounts();
			if(isset($listAll_query->posts) && count($listAll_query->posts)>0){
				foreach($listAll_query->posts as $all){
					$nombre_de_sub_comptes = get_field('nombre_de_sub_comptes',$all->ID);
					if($nombre_de_sub_comptes){
						$nbr_comptes_souscripts = $nbr_comptes_souscripts+intval($nombre_de_sub_comptes);
					}
				}
			}
			
			echo '<div style="  display: inline-block;position: absolute;left: 32%;">'
				. 'Nombre de comptes souscrits : '
				. '<br>'
				. ''.$nbr_comptes_souscripts.' (dont '.$nbr_comptes_souscripts_payed.' payés)'
				. '</div>';
			echo '<div style="  display: inline-block;position: absolute;left: 46%;">'
				. 'Nombre de comptes activés'
				. '<br>'
				. ''.$nbr_comptes_activés
				. '</div>';	
		}
	}
		 
	public function pmppro_admincolumn_changes($column, $post_id){
				
		if($_SERVER['REMOTE_ADDR'] == '176.159.13.228'){
			//echo $column;
		}
		
//		if($column == '5ca621e074521' || 5ca62c479b4303){ //total subs
//		
//			
//		}
		
		if($column == '5ca621e074f16' || $column == '5ca62c479bc2f'){ //number actual subs		
			$comptepromodel_o = new Ydcomptepromodel();
			$listPeople = $comptepromodel_o->getAllUsersSubAccounts($post_id);
			if($listPeople){
				echo count($listPeople);
			}else{
				echo 0;
			}
		}
		
		if($column == '5ca621e075032' || $column == '5ca62c479bd50'){ //date fin abonnement
			$date_field = get_field('date_de_fin_dabonnement',$post_id);
			echo $date_field;
		}		
		
		$list = array(
		  //'5ca621e074521'=>1,
		  '5ca621e074f16'=>1,
		  '5ca621e075032'=>1,
		  '5ca62c479bc2f'=>1,
		  '5ca62c479bd50'=>1,
		  );
		
		if( 1 == $list[$column] ){
			/** masquer la valeur de base **/
			echo '<span style="display:none;">';
		}
		
		
//		if( 'id_du_vol' == $column || '5c09187cf320a' == $column || '5c0a3d818d8c8' == $column  ) {
//			$revervation_query = get_more_rescent_reservation_by_personid($post_id);
//			if(isset($revervation_query->posts) && count($revervation_query->posts)>0){
//				foreach($revervation_query->posts as $reservation){					
//					$url = site_url().'/wp-admin/post.php?post='.$reservation->ID.'&amp;action=edit';
//					$unique_id = get_field("unique_resa_id",$reservation->ID);
//					if($unique_id){
//						echo '<a href="'.$url.'">';
//							echo $unique_id;
//						echo '</a>';
//					}
//				}
//			}
//
//			/** masquer la valeur de base **/
//			echo '<span style="display:none;">';
//		}
	}
	
	public function pmppro_admincolumn_changes_after($column, $post_id){
		$list = array(
		  //'5ca621e074521'=>1,
		  '5ca621e074f16'=>1,
		  '5ca621e075032'=>1,
		  '5ca62c479bc2f'=>1,
		  '5ca62c479bd50'=>1,
		  );
		
		if( 1 == $list[$column] ){
			echo '</span>';
		}
	}


	public function list_users_link_to_account_metabox() {
		add_meta_box( 
			'metabox_users_account_linked', 
			'Liste des utilisateurs', 
			array($this,'list_users_link_to_account'), 
			'client_pro',
			'advanced',
			'high');
	}
	
	public function list_users_link_to_account($userMoralFound) {
				
		$comptepromodel_o = new Ydcomptepromodel();
		$listPeople = $comptepromodel_o->getAllUsersSubAccounts($userMoralFound->ID);
				
		$nbr_users = 0;
		if($listPeople){
			$nbr_users = count($listPeople);
		}
		
		$html = '';
		
		$master_account = get_field('master_account',$userMoralFound->ID);
		if(!$master_account){
			$html.= "<div>"
				. "<span>"
				. "Aucun utilisateur ayant le privilège d'administration pour ce compte."
				. "</span>"
				. "</div>";
		}else{
			$html.= "<div>"
				. "<span>"
				. "Utilisateur administrateur de ce compte : "
				. "<a href='/wp-admin/user-edit.php?user_id=".$master_account['ID']."'>"
				. "".$master_account['display_name']." - ".$master_account['user_email'].""
				. "</a>"
				. "</span>"
				. "</div>";
		}
		
		$html.= '<div>';
		$html.= "<div>"
			. "<span>"
			. "Utilisateurs activés : ".$nbr_users.""
			. "</span>"
			. "</div>";		
			
		if($listPeople){
			$html.= '<table>';
				foreach($listPeople as $people){
					$html.= '<tr>';
						/** line **/
						$html.= '<td style="min-width: 140px;">';
							$html.= '<a href="/wp-admin/user-edit.php?user_id='.$people->ID.'">';
								$html.= $people->user_nicename;
							$html.= '</a>';								
						$html.= '</td>';
						$html.= '<td style="min-width: 140px;">';
							$html.= $people->user_email;
						$html.= '</td>';

						$time_activated = get_field('link_id_morale_date','user_'.$people->ID);
						if($time_activated){
							$html.= '<td style="min-width: 140px;">';
								$html.= date('d/m/Y',$time_activated);
							$html.= '</td>';
						}else{
							$html.= '<td style="min-width: 140px;">';
								$html.= 'activé avant le 04/04/2019';
							$html.= '</td>';
						}
					$html.= '</tr>';
				}
			$html.= '</table>';
		}
			
		$html.= '</div>';
		
		echo $html;
	}

	public function autoadd_code_generated($value, $post_id, $field) {
		$screen = get_current_screen();
		if ($screen->action == "add" && $value == "") {
			$value = Ydcomptepro::pmppro_generate_code();
		}
		return $value;
	}
	
	public static function pmppro_generate_code(){
		return substr(md5(date('dmyhis')), -8);
	}

	//PRO CODE/////////////////////////////////////////////////////////////////////////////
	public function process_post_procode() {
		if (isset($_POST['code_user'])):
			//test if the code 
			$comptepromodel_o = new Ydcomptepromodel();
			$userMoralFound = $comptepromodel_o->getMoralePersoneByCode($_POST['code_user']);
			if (isset($userMoralFound->ID)):

				global $erroremessagecomptepro;
				//do not save if to much users attached to it
				$listPeople = $comptepromodel_o->getAllUsersSubAccounts($userMoralFound->ID);
				$nbrSubUSers = get_field('nombre_de_sub_comptes', $userMoralFound->ID);
				if (count($listPeople) >= $nbrSubUSers):
					$erroremessagecomptepro = "tomuchusers";
					return;
				endif;

				//pass user as paid membership pro
				$user_id = get_current_user_id();
				pmpro_changeMembershipLevel(3, $user_id);
				//need to save the id of morale account
				update_user_meta($user_id, 'link_id_morale', $userMoralFound->ID);
				update_user_meta($user_id, 'link_id_morale_date', time());
			endif;

		endif;
	}

	public function do_pre_save_procode_paidmembershippro($post_id, $post_data) {
		//bail if it's not an procode
		if (get_post_type($post_id) !== 'client_pro') :
			return;
		endif;

		//pass old user to cancel paid membership pro
		//var_dump($post_id);
		$idmasteraccount = get_post_meta($post_id, 'master_account', true);
		//var_dump($idmasteraccount);
		if ($idmasteraccount && $idmasteraccount != 0):
			pmpro_changeMembershipLevel(0, $idmasteraccount);
		endif;
	}

	public function do_pre_save_procode_admin($post_id) {
		//bail if it's not an procode
		if (get_post_type($post_id) !== 'client_pro') :
			return;
		endif;

		if (function_exists('w3tc_objectcache_flush')):
			w3tc_objectcache_flush();
		endif;

		//if a master user is choosen => pass user to paid membership pro
		$idmasteraccount = get_post_meta($post_id, 'master_account', true);
		if ($idmasteraccount && $idmasteraccount != 0):
			pmpro_changeMembershipLevel(3, $idmasteraccount);
		endif;

//    if procodepaye ok && code_generated is empty
		$paye = get_post_meta($post_id, 'procodepaye', true);
		$codegenerated = get_post_meta($post_id, 'code_generated', true);

		if ($paye && !$codegenerated){
//			$name = get_the_title($post_id);
//			$codegenerated = md5($name . time());
//			update_field('code_generated', $codegenerated, $post_id);
			$codegenerated = Ydcomptepro::pmppro_generate_code();
			update_field('code_generated', $codegenerated, $post_id);
		}
	}

	public function deletesubuser() {
		header("Content-Type: application/json; charset=utf-8");

		pmpro_changeMembershipLevel(0, $_POST['iduser']);
		update_user_meta($_POST['iduser'], 'link_id_morale', false);

		//regenerate a new code when a user is kicked
//		$codegenerated = "";
//		$name = get_the_title($_POST['idproaccount']);
//		$codegenerated = md5($name . time());
		$codegenerated = Ydcomptepro::pmppro_generate_code();
		$test = update_post_meta($_POST['idproaccount'], 'code_generated', $codegenerated);

		echo json_encode($codegenerated);
		wp_die();
	}

	public function subuser_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-autocomplete');
		//wp_register_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );

		wp_enqueue_script('subuser', plugins_url('/js/subuser.js', dirname(__FILE__)), array(), '1.0.0', false);
		wp_localize_script('subuser', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	//END PRO CODE/////////////////////////////////////////////////////////////////////////
	//ALERTS /////////////////////////////////////////////////////////////////////////////
	public function post_published_alertsender_mandrill_on() {
		$nl2br = false;
		return $nl2br;
	}

	public function post_published_alertsender_mandrill_off() {
		$nl2br = true;
		return $nl2br;
	}

	public function post_published_alertsender($new_status, $old_status, $post) {

		if (!current_user_can('manage_options')) {
			return;
		}

		if (get_post_type($post->ID) !== 'evenement' && get_post_type($post->ID) !== 'post') :
			return;
		endif;

		//same status do not do mail
		if ($new_status == $old_status):
			return;
		endif;

		//only mail if we pass in published
		if ($new_status != "publish"):
			return;
		endif;

		$listRegex = array();
		//get city from post
		$cityTerms = get_the_terms($post->ID, 'ville');
		if (isset($cityTerms) && count($cityTerms) > 0):
			foreach ($cityTerms as $term):
				$listRegex[] = $term->name;
			endforeach;
		endif;

		//get post_tag from post
		$tagTerms = get_the_terms($post->ID, 'post_tag');
		if (isset($tagTerms) && count($tagTerms) > 0):
			foreach ($tagTerms as $term):
				$listRegex[] = $term->name;
			endforeach;
		endif;

		//get category from post
		$categoriesTerms = get_the_category($post->ID);
		if (isset($categoriesTerms)):
			foreach ($categoriesTerms as $term):
				$listRegex[] = $term->name;
			endforeach;
		endif;

		//get users that has alerts with these terms
		$args = array();
		$args['meta_query'][] = array(
			  'key' => 'expressions_alertes',
			  'value' => implode('|', $listRegex),
			  'compare' => 'REGEXP'
		);
		$user_query = new WP_User_Query($args);

		// User Loop
		$listUsersEmails = array();
		if (!empty($user_query->results)) :
			foreach ($user_query->results as $user):

				//test if user is member
				$userlevel = pmpro_getMembershipLevelForUser($user->ID);
				if ($userlevel != null):
					$listUsersEmails[] = $user->user_email;
				endif;
			endforeach;
		endif;

		if (count($listUsersEmails) > 0):
			//get textes
			$loginpage = 118477; //login page id
			if (preg_match('#dev94#', $_SERVER['HTTP_HOST'])):
				$loginpage = 118153; //dev
			endif;
			$titleText = get_field('titre_mail_pour_alert', $loginpage);
			$titleTextmodified = str_replace("%title%", get_the_title($post->ID), $titleText);

			$coreText = get_field('texte_mail_pour_alert', $loginpage);

			global $newsletterData;
			$newsletterData['thepost'] = $post;
			$newsletterData['onlypdf'] = false;
			$newsletterData['widthColumn'] = 700;
			$articleHtml = $this->load_template_part_to_var('content', 'alertemailpost');

			$coreTextModified = str_replace("%article%", $articleHtml, $coreText);

			$headers = array('Content-Type: text/html; charset=UTF-8', 'From: 94 Citoyens <contact@citoyens.com');

			//only for this mails
			add_filter('mandrill_nl2br', array($this, 'post_published_alertsender_mandrill_on'));

			foreach ($listUsersEmails as $emailTo):

				if ($emailTo == "silver@celyan.com"):
					$emailTo = "silver.celyan@gmail.com";
				//$emailTo = "yann@abc.fr,silver.celyan@gmail.com";
				endif;

				// send email
				wp_mail($emailTo, $titleTextmodified, $coreTextModified, $headers);
			endforeach;

			add_filter('mandrill_nl2br', array($this, 'post_published_alertsender_mandrill_off'));

		endif;
	}

	private function load_template_part_to_var($template_name, $part_name = null) {
		ob_start();
		get_template_part($template_name, $part_name);
		$var = ob_get_contents();
		ob_end_clean();
		return $var;
	}

	public function alertsuser_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-autocomplete');

		wp_enqueue_script('alertuser', plugins_url('/js/alertnewsuser.js', dirname(__FILE__)), array(), '1.2.0', false);
		wp_localize_script('alertuser', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	public function savealertforuser() {
		header("Content-Type: application/json; charset=utf-8");

		//get terms of the user to test the limit to 3
		$user_id = get_current_user_id();
		$list = get_user_meta($user_id, 'expressions_alertes');
		$list = json_decode($list[0], true);

		if (count($list) < 3):
			$tosave = $_POST['savetheterm'];
			$list[$tosave] = $tosave;
			update_user_meta($user_id, 'expressions_alertes', json_encode($list, JSON_UNESCAPED_UNICODE));
		endif;

		echo json_encode(true);
		wp_die();
	}

	public function deletealertforuser() {
		header("Content-Type: application/json; charset=utf-8");

		$user_id = get_current_user_id();
		$list = get_user_meta($user_id, 'expressions_alertes');
		$list = json_decode($list[0], true);
		unset($list[$_POST['deletetheterm']]);
		update_user_meta($user_id, 'expressions_alertes', json_encode($list, JSON_UNESCAPED_UNICODE));

		echo json_encode(true);
		wp_die();
	}

	public function searchinallterms() {

		$theseach = $_POST['searchtext'];

		//get all terms
		$data = array();
		$data['hide_empty'] = false;
		$data['name__like'] = $theseach;
		$data['fields'] = 'id=>name';

		$data['taxonomy'] = 'category';
		$categoryTerms = get_terms($data);

		$data['taxonomy'] = 'ville';
		$villeTerms = get_terms($data);

		$data['number'] = 10;
		$data['orderby'] = 'count';
		$data['order'] = 'DESC';
		$data['taxonomy'] = 'post_tag';
		$tagsTerms = get_terms($data);

		$arrayResult = array();

		if (count($categoryTerms) > 0):
			foreach ($categoryTerms as $key => $val):
				$arrayResult[] = $val;
			endforeach;
		endif;

		if (count($tagsTerms) > 0):
			foreach ($tagsTerms as $key => $val):
				$arrayResult[] = $val;
			endforeach;
		endif;

		if (count($villeTerms) > 0):
			foreach ($villeTerms as $key => $val):
				$arrayResult[] = $val;
			endforeach;
		endif;

		echo json_encode($arrayResult);
		wp_die();
	}

	//END ALERTS /////////////////////////////////////////////////////////////////////////////
}
