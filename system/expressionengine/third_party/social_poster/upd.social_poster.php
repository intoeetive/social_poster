<?php

/*
=====================================================
 Social Poster
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2013-2014 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'social_poster/config.php';

class Social_poster_upd {

    var $version = SOCIAL_POSTER_ADDON_VERSION;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function install() { 
  
		$this->EE->load->dbforge(); 
        
        //----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}
        
        $settings = array();

        $data = array( 'module_name' => 'Social_poster' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'n', 'settings'=> serialize($settings) ); 
        $this->EE->db->insert('modules', $data); 
        
        $data = array( 'class' => 'Social_poster' , 'method' => 'save_permissions' ); 
        $this->EE->db->insert('actions', $data); 
        
        $data = array( 'class' => 'Social_poster' , 'method' => 'save_default_permissions' ); 
        $this->EE->db->insert('actions', $data); 
        
        if ($this->EE->db->field_exists('social_poster_permissions', 'members') == FALSE)
		{
			$this->EE->dbforge->add_column('members', array('social_poster_permissions' => array('type' => 'TEXT') ) );
		}
        
        if ($this->EE->db->field_exists('social_poster_permissions_detailed', 'members') == FALSE)
		{
			$this->EE->dbforge->add_column('members', array('social_poster_permissions_detailed' => array('type' => 'TEXT') ) );
		}
        
        return TRUE; 
        
    } 
    
    function uninstall() { 

        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Social_poster')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Social_poster'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Social_poster'); 
        $this->EE->db->delete('actions'); 
        
        return TRUE; 
    } 
    
    function update($current='') 
	{ 
        if ($current < 1.1) 
        { 
           
        } 
        return TRUE; 
    } 
	

}
/* END */
?>