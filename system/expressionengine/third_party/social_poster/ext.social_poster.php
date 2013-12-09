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

class Social_poster_ext {

	var $name	     	= SOCIAL_POSTER_ADDON_NAME;
	var $version 		= SOCIAL_POSTER_ADDON_VERSION;
	var $description	= SOCIAL_POSTER_ADDON_DESC;
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.intoeetive.com/docs/social_poster.html';
    
    var $settings 		= array();

    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();        
        
        $this->EE->lang->loadfile('social_poster');
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        $hooks = array();
        
        //exp_social_poster_templates
        $fields = array(
			'template_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'auto_increment' => TRUE),
            'site_id'		    => array('type' => 'INT',		'unsigned' => TRUE),
			'hook'			    => array('type' => 'VARCHAR',	'constraint'=> 150,	'default' => ''), 	
			'tmpl_body'		    => array('type' => 'TEXT',		'default' => ''),
			'tmpl_vars'		    => array('type' => 'TEXT',		'default' => '')
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('template_id', TRUE);
        $this->EE->dbforge->add_key('site_id');
		$this->EE->dbforge->add_key('hook');
		$this->EE->dbforge->create_table('social_poster_templates', TRUE);
        
        $hooks[] = array(
			'hook'		=> 'dummy',
			'method'	=> 'do_something',
			'priority'	=> 10
		);
        
        $this->EE->load->library('sp_hook');
        
        foreach(scandir(PATH_THIRD.'social_poster/hooks/') as $file) {
            if (is_file(PATH_THIRD.'social_poster/hooks/'.$file)) 
            {
                $hooks[] = array(
        			'hook'		=> str_replace('.php', '', $file),
        			'method'	=> 'do_something',
        			'priority'	=> 10
        		);

                require_once PATH_THIRD.'social_poster/hooks/'.$name;
                $class_name = ucfirst($name).'_sp_hook';
                $SL_HOOK = new $class_name();
                $tmpl_data = array(
                    'site_id'       => $this->EE->config->item('site_id'),
                    'hook'          => str_replace('.php', '', $file),
                    'tmpl_body'     => $SL_HOOK->template(),
                    'tmpl_vars'     => $SL_HOOK->vars(),
                );
                $this->EE->db->insert('social_poster_templates', $tmpl_data);
            }
        }
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	

    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	$hooks = array();
        foreach(scandir(PATH_THIRD.'social_poster/hooks/') as $file) {
            if (is_file(PATH_THIRD.'social_poster/hooks/'.$file)) 
            {
                $hook = str_replace('.php', '', $file);
                $check = $this->EE->db->select('extension_id')
                        ->from('extensions')
                        ->where('class', __CLASS__)
                        ->where('hook', $hook)
                        ->get();
                if ($check->num_rows()==0)
                {
                    $data = array(
                		'class'		=> __CLASS__,
                		'method'	=> 'do_something',
                		'hook'		=> $hook,
                		'settings'	=> '',
                		'priority'	=> 10,
                		'version'	=> $this->version,
                		'enabled'	=> 'y'
                	);
                    $this->EE->db->insert('extensions', $data);
                    
                    require_once PATH_THIRD.'social_poster/hooks/'.$name;
                    $class_name = ucfirst($name).'_sp_hook';
                    $SL_HOOK = new $class_name();
                    $tmpl_data = array(
                        'site_id'       => $this->EE->config->item('site_id'),
                        'hook'          => str_replace('.php', '', $file),
                        'tmpl_body'     => $SL_HOOK->template(),
                        'tmpl_vars'     => $SL_HOOK->vars(),
                    );
                    $this->EE->db->insert('social_poster_templates', $tmpl_data);
                }
            }
        }
        
        if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');        
                    
    }
    
    
    function settings_form($settings)
    {

        $site_id = $this->EE->config->item('site_id');
        
        if ( ! class_exists('Social_login_pro_ext'))
    	{
    		require_once PATH_THIRD.'social_login_pro/ext.social_login_pro.php';
    	}
    	$SLP = new Social_login_pro_ext();
        $this->EE->lang->loadfile('social_login_pro');

        $vars = array();
        foreach ($SLP->providers as $provider)
        {
            $enabled_by_default = (isset($settings[$site_id][$provider]['post_by_default']))?$settings[$site_id][$provider]['post_by_default']:'y';
            $vars['settings'][$provider] = form_radio("permissions[$provider]", 'y', ($enabled_by_default=='y')?true:false, 'id="'.$provider.'_y"').form_label(lang('yes'), $provider.'_y').
                NBS.
                form_radio("permissions[$provider]", 'n', ($enabled_by_default=='n')?true:false, 'id="'.$provider.'_n"').form_label(lang('no'), $provider.'_n');
        }
        
        //list all hooks
        
        $hooks_q = $this->EE->db->select('extension_id, hook, enabled')
                        ->from('extensions')
                        ->where('class', __CLASS__)
                        ->where('hook != ', 'dummy')
                        ->get();
        foreach ($hooks_q->result_array() as $row)
        {
            $vars['hooks'][$row['hook']] = form_radio("hooks[{$row['hook']}]", 'y', ($row['enabled']=='y')?true:false, 'id="'.$row['hook'].'_y"').form_label(lang('yes'), $row['hook'].'_y').
                NBS.
                form_radio("hooks[{$row['hook']}]", 'n', ($row['enabled']=='n')?true:false, 'id="'.$row['hook'].'_n"').form_label(lang('no'), $row['hook'].'_n');
        }

    	return $this->EE->load->view('settings', $vars, TRUE);			
    }
    
    
    
    
    function save_settings()
    {
    	if (empty($_POST))
    	{
    		show_error($this->EE->lang->line('unauthorized_access'));
    	}
        
        $this->EE->db->select('settings')
            ->from('extensions')
            ->where('class', __CLASS__)
            ->limit(1);
        $q = $this->EE->db->get();
        if ($q->row('settings')!='')
        {
            $permissions = unserialize($q->row('settings'));
        }
        
        $site_id = $this->EE->config->item('site_id');

        foreach ($_POST['permissions'] as $key=>$val)
        {
            $permissions[$site_id][$key]['post_by_default'] = $val;
        }        
        $upd_data = array('settings' => serialize($permissions));

        unset($_POST['submit']);
        
        $this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update('extensions', $upd_data);
        
        //make sure we have hook record for each file in hooks folder
        foreach ($_POST['hooks'] as $hook=>$enabled)
        {
            $upd_data = array('enabled' => $enabled);
            $this->EE->db->where('hook', $hook);
            $this->EE->db->where('class', __CLASS__);
            $this->EE->db->update('extensions', $upd_data);
        }        
        
        
    	
    	$this->EE->session->set_flashdata(
    		'message_success',
    	 	$this->EE->lang->line('preferences_updated')
    	);
    }
        
    
    
    public function __call($name, $arguments) {
		
		if ($this->EE->session->userdata('member_id')==0) return false;
        
        $this->EE->load->library('sp_hook');
        
        require_once PATH_THIRD.'social_poster/hooks/'.$name.'.php';
        $class_name = ucfirst($name).'_sp_hook';
        $SL_HOOK = new $class_name();
        if(method_exists($SL_HOOK, $name)) {
			return call_user_func(array($SL_HOOK, $name), $arguments);
		}
		return false;
		
	}
    
    
    
    function do_something($var1=false, $var2=false, $var3=false, $var4=false, $var5=false)
    {
        $d = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        var_dump($d);
        exit();
    }
    
    


}
// END CLASS
