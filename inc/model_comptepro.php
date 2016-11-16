<?php
class Ydcomptepromodel {

  public function __construct() {
  }
  
  public function getMoralePersoneByCode($code){
    
    $args = array();
    $args['meta_key'] = 'code_generated';
    $args['meta_value'] = $code;
            
    $user_query = new WP_User_Query($args);
    
    $results = $user_query->get_results();
    
    if($results && count($results)>0):
      $result = $results[0];
    endif;
    
    return $result;
  }
  
  public function findIfUserIsMasterAccount($user_id){
    
    $args = array();
    $args['meta_key'] = 'master_account';
    $args['meta_value'] = $user_id;
            
    $user_query = new WP_User_Query($args);
    
    $results = $user_query->get_results();
    
    if($results && count($results)>0):
      $result = $results[0];
    endif;
    
    return $result;
  }
  
  public function getAllUsersSubAccounts($masterAcctountId){
    $args = array();
    $args['meta_key'] = 'link_id_morale';
    $args['meta_value'] = $masterAcctountId;
            
    $user_query = new WP_User_Query($args);
    
    $results = $user_query->get_results();
        
    return $results;
  }
 
}
?>