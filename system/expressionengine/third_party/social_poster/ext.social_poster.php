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
        
        $this->EE->load->dbforge(); 
        
        //exp_social_poster_templates
        $fields = array(
			'template_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'auto_increment' => TRUE),
            'site_id'		    => array('type' => 'INT',		'unsigned' => TRUE),
			'hook'			    => array('type' => 'VARCHAR',	'constraint'=> 150,	'default' => ''), 	
			'tmpl_body'		    => array('type' => 'TEXT',		'default' => ''),
            'tmpl_link'		    => array('type' => 'TEXT',		'default' => '')
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
                $name = str_replace('.php', '', $file);
                $hooks[] = array(
        			'hook'		=> $name,
        			'method'	=> $name,
        			'priority'	=> 10
        		);

                require_once PATH_THIRD.'social_poster/hooks/'.$file;
                $class_name = ucfirst($name).'_sp_hook';
                $SL_HOOK = new $class_name();
                $tmpl_data = array(
                    'site_id'       => $this->EE->config->item('site_id'),
                    'hook'          => $name,
                    'tmpl_body'     => $SL_HOOK->template(),
                    'tmpl_link'     => $SL_HOOK->link()
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
        
        $this->EE->load->library('sp_hook');
        
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
                    
                }
                
                $check = $this->EE->db->select('template_id')
                        ->from('social_poster_templates')
                        ->where('hook', $hook)
                        ->where('site_id', $this->EE->config->item('site_id'))
                        ->get();
                if ($check->num_rows()==0)
                {
                    require_once PATH_THIRD.'social_poster/hooks/'.$hook.'.php';
                    $class_name = ucfirst($hook).'_sp_hook';
                    $SL_HOOK = new $class_name();
                    $tmpl_data = array(
                        'site_id'       => $this->EE->config->item('site_id'),
                        'hook'          => str_replace('.php', '', $file),
                        'tmpl_body'     => $SL_HOOK->template(),
                        'tmpl_link'     => $SL_HOOK->link()
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
        
        $this->EE->load->dbforge(); 
        $this->EE->dbforge->drop_table('social_poster_templates');
            
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
        
        $slp_settings_q = $this->EE->db->select('settings')
                            ->from('modules')
                            ->where('module_name','Social_login_pro')
                            ->limit(1)
                            ->get(); 
        if ($slp_settings_q->num_rows()==0) return false;

        $slp_settings = unserialize($slp_settings_q->row('settings'));
        
        $this->EE->load->library('sp_hook');

        $vars = array();
        foreach ($SLP->providers as $provider)
        {
            if ($slp_settings[$site_id][$provider]['enable_posts']=='y')
            {
                $enabled_by_default = (isset($settings[$site_id]['post_by_default'][$provider]))?$settings[$site_id]['post_by_default'][$provider]:'y';
                $vars['settings'][$provider] = form_radio("permissions[$provider]", 'y', ($enabled_by_default=='y')?true:false, 'id="'.$provider.'_y"').form_label(lang('yes'), $provider.'_y').
                    NBS.
                    form_radio("permissions[$provider]", 'n', ($enabled_by_default=='n')?true:false, 'id="'.$provider.'_n"').form_label(lang('no'), $provider.'_n');
            }
        }
        
        //list all hooks
        
        $hooks_q = $this->EE->db->select('extension_id, extensions.hook, enabled, tmpl_body, tmpl_link')
                        ->from('extensions')
                        ->join('social_poster_templates', 'extensions.hook=social_poster_templates.hook', 'left')
                        ->where('class', __CLASS__)
                        ->where('extensions.hook != ', 'dummy')
                        ->get();
        foreach ($hooks_q->result_array() as $row)
        {
            require_once PATH_THIRD.'social_poster/hooks/'.$row['hook'].'.php';
            $class_name = ucfirst($row['hook']).'_sp_hook';
            $SL_HOOK = new $class_name();
            
            $vars['hooks'][$row['hook']] = form_radio("hooks[{$row['hook']}]", 'y', ($row['enabled']=='y')?true:false, 'id="'.$row['hook'].'_y"').form_label(lang('yes'), $row['hook'].'_y').
                NBS.
                form_radio("hooks[{$row['hook']}]", 'n', ($row['enabled']=='n')?true:false, 'id="'.$row['hook'].'_n"').form_label(lang('no'), $row['hook'].'_n');
            $vars['templates'][$row['hook']] = array(
                'vars'      => '',
                'template'  => form_textarea("templates[{$row['hook']}]", (isset($row['tmpl_body']) && $row['tmpl_body']!='')?$row['tmpl_body']:$SL_HOOK->template()).
                                BR.
                                form_input("links[{$row['hook']}]", (isset($row['tmpl_link']) && $row['tmpl_link']!='')?$row['tmpl_link']:$SL_HOOK->link())
            );
            
            
            $tmpl_vars = unserialize($SL_HOOK->vars());
            foreach ($tmpl_vars as $key=>$val)
            {
                $vars['templates'][$row['hook']]['vars'] .= $key.' &mdash; '.$val.BR;
            }
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
            $settings = unserialize($q->row('settings'));
        }
        
        $site_id = $this->EE->config->item('site_id');

        foreach ($_POST['permissions'] as $key=>$val)
        {
            $settings[$site_id]['post_by_default'][$key] = $val;
        }        
        $upd_data = array('settings' => serialize($settings));

        $this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update('extensions', $upd_data);
        
        
        foreach ($_POST['templates'] as $key=>$val)
        {
            $where = array(
                'site_id'   => $site_id,
                'hook'      => $key
            );
            $upd_data = array(
                'tmpl_body' => $val,
                'tmpl_link' => $_POST['links'][$key]
            );
            $this->EE->db->select('template_id');
            $this->EE->db->where($where);
            $q = $this->EE->db->get('social_poster_templates');
            if ($q->num_rows()>0)
            {
                $this->EE->db->where('template_id', $q->row('template_id'));
                $this->EE->db->update('social_poster_templates', $upd_data);
            }
            else
            {
                $upd_data = array_merge($upd_data, $where);
                $this->EE->db->insert('social_poster_templates', $upd_data);
            }
        }         
        
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
        //
    }
    
    


}
// END CLASS
