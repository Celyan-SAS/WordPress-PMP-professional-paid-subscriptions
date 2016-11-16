<?php
/**
 * 
 */

class Ydcomptepro {
  
  public function __construct() {
    
    //IT'S FOR ALERTS USERS
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
    //get alerts
    add_action('transition_post_status', array($this,'post_published_alertsender'), 10, 3 );
    
    //PRO CODE
    add_action('wp_enqueue_scripts', array($this, 'subuser_scripts'));
    
    add_action( 'profile_update', array($this,'procode_profile_update'), 10, 2 );
    
    add_action('wp_ajax_deletesubuser', array($this, 'deletesubuser'));
    add_action('wp_ajax_nopriv_deletesubuser', array($this, 'deletesubuser'));
  }
  
  //PRO CODE/////////////////////////////////////////////////////////////////////////////
  public function procode_profile_update( $user_id, $old_user_data ) {
    
    //if procodepaye ok && code_generated is empty
    $paye = get_user_meta( $user_id, 'procodepaye',true);    
    $codegenerated = get_user_meta( $user_id, 'code_generated',true);
    
    if($paye && !$codegenerated):
      $current_user = wp_get_current_user();
      $codegenerated = md5($current_user->user_login.time());
      update_field('code_generated',$codegenerated,'user_'.$user_id);
    endif;
  }
  
  public function deletesubuser(){
    header( "Content-Type: application/json; charset=utf-8" );
    
    update_user_meta( $_POST['iduser'], 'link_id_morale', 0);
    
    echo json_encode(true);
    wp_die();
  }
  
  public function subuser_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    //wp_register_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
  
    wp_enqueue_script('subuser', plugins_url('/js/subuser.js', dirname(__FILE__)), array(), '1.0.0', false);
    wp_localize_script( 'subuser', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
  }
  //END PRO CODE/////////////////////////////////////////////////////////////////////////
  
   //ALERTS /////////////////////////////////////////////////////////////////////////////
  public function post_published_alertsender_mandrill_on(){
    $nl2br = false; 
    return $nl2br; 
  }
  
  public function post_published_alertsender_mandrill_off(){
    $nl2br = true; 
    return $nl2br; 
  }
    
  public function post_published_alertsender($new_status, $old_status, $post ) {
    
    if (!current_user_can( 'manage_options' ) ) {
      return;
    }
    
     //same status do not do mail
    if($new_status == $old_status):
      return;
    endif;
    
    //only mail if we pass in published
    if($new_status != "publish"):
      return;
    endif;
    
    $listRegex = array();
    //get city from post
    $cityTerms = get_the_terms($post->ID, 'ville');
    if(isset($cityTerms) && count($cityTerms)>0):
      foreach($cityTerms as $term):
        $listRegex[] = $term->name;
      endforeach;
    endif;
    
    //get post_tag from post
    $tagTerms = get_the_terms($post->ID, 'post_tag');
    if(isset($tagTerms) && count($tagTerms)>0):
      foreach($tagTerms as $term):
        $listRegex[] = $term->name;
      endforeach;
    endif;
    
    //get category from post
    $categoriesTerms = get_the_category($post->ID);
    if(isset($categoriesTerms)):
      foreach($categoriesTerms as $term):
        $listRegex[] = $term->name;
      endforeach;
    endif;
    
    //get users that has alerts with these terms
    $args = array();
    $args['meta_query'][] = 
      array(
      'key' => 'expressions_alertes',
      'value'   => implode('|',$listRegex),
      'compare' => 'REGEXP'
      );
    $user_query = new WP_User_Query( $args );

    // User Loop
    $listUsersEmails = array();
    if ( ! empty( $user_query->results ) ) :
      foreach ( $user_query->results as $user ):
        $listUsersEmails[] = $user->user_email;
      endforeach;
    endif;
    
    if(count($listUsersEmails)>0):
      //get textes
      $loginpage = 118477; //login page id
      if(preg_match('#dev94#', $_SERVER['HTTP_HOST'])):
        $loginpage = 118153; //dev
      endif;
      $titleText = get_field('titre_mail_pour_alert',$loginpage);
      $coreText = get_field('texte_mail_pour_alert',$loginpage);
            
      global $newsletterData;
      $newsletterData['thepost'] = $post;
      $newsletterData['onlypdf'] = false;
      $newsletterData['widthColumn'] = 700;
      $articleHtml = $this->load_template_part_to_var('content', 'alertemailpost' ); 

      $coreTextModified = str_replace("%article%", $articleHtml, $coreText);
      
      $headers = array('Content-Type: text/html; charset=UTF-8','From: 94 Citoyens <contact@citoyens.com');
      
      //only for this mails
      add_filter('mandrill_nl2br', array($this,'post_published_alertsender_mandrill_on'));
        
      foreach($listUsersEmails as $emailTo):
        
        if($emailTo == "silver@celyan.com"):
          $emailTo = "silver.celyan@gmail.com";
          //$emailTo = "yann@abc.fr,silver.celyan@gmail.com";
        endif;
        
        // send email
        wp_mail($emailTo, $titleText, $coreTextModified,$headers);  
      endforeach;
      
      add_filter('mandrill_nl2br', array($this,'post_published_alertsender_mandrill_off'));
      
    endif;
  }

  private function load_template_part_to_var($template_name, $part_name=null) {
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

  public function alertsuser_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    //wp_register_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
  
    wp_enqueue_script('alertuser', plugins_url('/js/alertnewsuser.js', dirname(__FILE__)), array(), '1.0.0', false);
    wp_localize_script( 'alertuser', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
  }
  
  public function savealertforuser(){
    header( "Content-Type: application/json; charset=utf-8" );
    
    //get terms of the user to test the limit to 3
    $user_id = get_current_user_id();
    $list = get_user_meta($user_id, 'expressions_alertes');
    $list = json_decode($list[0],true);
    
    if(count($list)<3):
      $tosave = $_POST['savetheterm'];
      $list[$tosave] = $tosave;
      update_user_meta( $user_id, 'expressions_alertes', json_encode($list,JSON_UNESCAPED_UNICODE));  
    endif;
    
    echo json_encode(true);
    wp_die();
  }
  
  public function deletealertforuser(){
    header( "Content-Type: application/json; charset=utf-8" );
    
    $user_id = get_current_user_id();
    $list = get_user_meta($user_id, 'expressions_alertes');
    $list = json_decode($list[0],true);
    unset($list[$_POST['deletetheterm']]);
    update_user_meta( $user_id, 'expressions_alertes', json_encode($list,JSON_UNESCAPED_UNICODE));
        
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

//    $data['taxonomy'] = 'category';
//    $categoryTerms = get_terms($data);
//
//    $data['taxonomy'] = 'ville';
//    $villeTerms = get_terms($data);

    $data['number'] = 10;
    $data['orderby'] = 'count';
    $data['order'] = 'DESC';
    $data['taxonomy'] = 'post_tag';
    $tagsTerms = get_terms($data);
   
   $arrayResult = array();
   
   if(count($categoryTerms)>0):
     foreach($categoryTerms as $key=>$val):
      $arrayResult[] = $val;
     endforeach;
   endif;
   
   if(count($tagsTerms)>0):
     foreach($tagsTerms as $key=>$val):
      $arrayResult[] = $val;
     endforeach;
   endif;
   
   if(count($villeTerms)>0):
     foreach($villeTerms as $key=>$val):
      $arrayResult[] = $val;
     endforeach;
   endif;
   
    echo json_encode($arrayResult);
    wp_die();
  }
  //END ALERTS /////////////////////////////////////////////////////////////////////////////

}