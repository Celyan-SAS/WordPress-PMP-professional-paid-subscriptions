<?php
/**
 *	@package YD compte pro
 *	@author Celyan
 *	@version 0.0.1
 */
/*
 Plugin Name: YD Compte Pro
 Plugin URI: http://www.yann.com/
 Description: Gestions des infos compte pro
 Version: 0.0.1
 Author: Yann Dubois
 Author URI: http://www.yann.com/
 License: GPL2
 */

include_once(dirname(__FILE__) . '/inc/model_comptepro.php');
include_once(dirname(__FILE__) . '/inc/comptepro.php');

/** Controller Class **/
//if(preg_match('/emploi/', $_SERVER['REQUEST_URI'])):
  global $YD_comptepro_o;
  $YD_comptepro_o = new Ydcomptepro();
//endif;