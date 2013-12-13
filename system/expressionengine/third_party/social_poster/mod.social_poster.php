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


class Social_poster {

    var $return_data	= ''; 	
    
    var $settings = array();

    var $perpage = 25;
    
    var $max_length = 760;

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
		$this->EE->lang->loadfile('social_poster');  
    }
    /* END */


    function default_permissions()
    {
        if ($this->EE->session->userdata('member_id')==0)
        {
            return $this->EE->TMPL->no_results();
        }
        
        $site_id = $this->EE->config->item('site_id');
        $tagdata = $this->EE->TMPL->tagdata;
        
        $settings_query = $this->EE->db->select("settings")->from('extensions')->where('class', 'Social_poster_ext')->where('settings != ', '')->limit('1')->get();
        $settings = unserialize($settings_query->row('settings')); 
        
        $this->EE->lang->loadfile('social_login_pro');
        
        $permissions = array();
        $enabled_by_default = array();
        foreach ($settings[$site_id]['post_by_default'] as $provider=>$allowed)
        {
            $enabled_by_default[$provider] = $allowed;
        }
        
        $this->EE->db->select('social_login_keys, social_poster_permissions')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_login_keys')!='')
        {
            $keys = unserialize($q->row('social_login_keys'));
            $existing_permissions = ($q->row('social_poster_permissions')!='')?unserialize($q->row('social_poster_permissions')):array();
            foreach ($keys as $provider_name=>$provider_data)
            {
                if (!empty($existing_permissions) && isset($existing_permissions[$site_id]) && isset($existing_permissions[$site_id][$provider_name]))
                {
                    $enabled = $existing_permissions[$site_id][$provider_name];
                }
                else
                {
                    $enabled = $enabled_by_default[$provider_name];
                }
                $permissions[$provider] = $enabled;
            }
        }
        
        if (preg_match("/".LD."permissions".RD."(.*?)".LD.'\/'."permissions".RD."/s", $tagdata, $match))
		{
			$tmpl = $match['1'];
            $rows = '';
            
            foreach ($permissions as $key=>$val)
            {
                $row = $tmpl;
                $row = $this->EE->TMPL->swap_var_single('field_name', 'permissions['.$key.']', $row);
                $row = $this->EE->TMPL->swap_var_single('field_label', lang($key), $row);
                if ($val=='y')
                {
                    $row = $this->EE->TMPL->swap_var_single('selected', ' selected="selected"', $row);
                    $row = $this->EE->TMPL->swap_var_single('checked', ' checked="checked"', $row);
                }
                else
                {
                    $row = $this->EE->TMPL->swap_var_single('selected', '', $row);
                    $row = $this->EE->TMPL->swap_var_single('checked', '', $row);
                }
                $rows .= $row;
            }
            
            $tagdata = preg_replace ("/".LD."permissions".RD.".*?".LD.'\/'."permissions".RD."/s", $rows, $tagdata);
		}
              
        $data['action'] = $this->EE->functions->fetch_site_index();

        $data['hidden_fields']['ACT'] = $this->EE->functions->fetch_action_id('Social_poster', 'save_permissions');   
        if ($this->EE->TMPL->fetch_param('return')=='')
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->functions->fetch_site_index()),'/');
        }
        else if ($this->EE->TMPL->fetch_param('return')=='SAME_PAGE')
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->functions->fetch_current_uri()),'/');
        }
        else if (strpos($this->EE->TMPL->fetch_param('return'), "http://")!==FALSE || strpos($this->EE->TMPL->fetch_param('return'), "https://")!==FALSE)
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->TMPL->fetch_param('return')),'/');
        }
        else
        {
            $return = $this->EE->TMPL->fetch_param('return');
        }
        $data['hidden_fields']['RET'] = $return;            
		$data['name']		= ($this->EE->TMPL->fetch_param('name')!='') ? $this->EE->TMPL->fetch_param('name') : 'social_poster_permissions';
        $data['id']		= ($this->EE->TMPL->fetch_param('id')!='') ? $this->EE->TMPL->fetch_param('id') : 'social_poster_permissions';
        $data['class']		= ($this->EE->TMPL->fetch_param('class')!='') ? $this->EE->TMPL->fetch_param('class') : 'social_poster_permissions';
        if ($this->EE->TMPL->fetch_param('ajax')=='yes') $data['hidden_fields']['ajax'] = 'yes';

        $out = $this->EE->functions->form_declaration($data)."\n".
                $tagdata."\n".
                "</form>";
        
        return $out;
    }
    
    
    function save_default_permissions()
    {
        if ($this->EE->session->userdata('member_id') == 0)
		{
			if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('not_authorized');
                exit();
            }
            $this->EE->output->show_user_error('submission', $this->EE->lang->line('not_authorized'));
            return;
		}	
        
        $site_id = $this->EE->config->item('site_id');
        
        $this->EE->db->select('social_poster_permissions')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_poster_permissions')!='')
        {
            $permissions = unserialize($q->row('social_poster_permissions'));
        }

        foreach ($_POST['permissions'] as $key=>$val)
        {
            $permissions[$site_id][$key] = $val;
        }        
        
        $upd_data = array('social_login_permissions' => serialize($permissions));
        
        $this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
        $this->EE->db->update('members', $upd_data);
        
        if ($this->EE->input->get_post('ajax')=='yes')
        {
            echo $this->EE->lang->line('preferences_updated');
            exit();
        }
        
        $data = array(	'title' 	=> $this->EE->lang->line('thank_you'),
						'heading'	=> $this->EE->lang->line('thank_you'),
						'content'	=> $this->EE->lang->line('preferences_updated'),
						'redirect'	=> $this->EE->input->post('RET'),							
						'link'		=> array($this->EE->input->post('RET'), $this->EE->lang->line('click_if_no_redirect')),
						'rate'		=> 5
					 );
					
		$this->EE->output->show_message($data);
    }











    function permissions()
    {
        if ($this->EE->session->userdata('member_id')==0)
        {
            return $this->EE->TMPL->no_results();
        }
        
        $site_id = $this->EE->config->item('site_id');
        $tagdata = $this->EE->TMPL->tagdata;
        $this->EE->lang->loadfile('social_login_pro');
        
        $this->EE->db->select('social_poster_permissions, social_poster_permissions_detailed')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_poster_permissions')!='')
        {
            $global_permissions = unserialize($q->row('social_poster_permissions'));
        }
        else
        {
            $settings_query = $this->EE->db->select("settings")->from('extensions')->where('class', 'Social_poster_ext')->where('settings != ', '')->limit('1')->get();
            $settings = unserialize($settings_query->row('settings')); 
            $global_permissions = $settings[$site_id]['post_by_default'];
        }
        
        $hooks_q = $this->EE->db->select('hook')
                        ->from('extensions')
                        ->where('class', __CLASS__)
                        ->where('extensions.hook != ', 'dummy')
                        ->where('enabled', 'y')
                        ->get();
        if ($hooks_q->num_rows()==0 || empty($global_permissions))
        {
            return $this->EE->TMPL->no_results();
        }
        
        
        if (preg_match("/".LD."permissions".RD."(.*?)".LD.'\/'."permissions".RD."/s", $tagdata, $match))
		{
			$tmpl = $match['1'];
            $rows = '';
            
            foreach ($provider as $provider=>$enabled)
            {
                if ($enabled=='y')
                {
                    foreach ($hooks_q->result_array() as $hook_row)
                    {
                        $row = $tmpl;
                        $row = $this->EE->TMPL->swap_var_single('field_name', "permissions[$provider][{$hook_row['hook']}]", $row);
                        $row = $this->EE->TMPL->swap_var_single('field_label', lang('post_to').' '.lang($provider).' '.lang('when').' '.$action, $row);
                        if ($val=='y')
                        {
                            $row = $this->EE->TMPL->swap_var_single('selected', ' selected="selected"', $row);
                            $row = $this->EE->TMPL->swap_var_single('checked', ' checked="checked"', $row);
                        }
                        else
                        {
                            $row = $this->EE->TMPL->swap_var_single('selected', '', $row);
                            $row = $this->EE->TMPL->swap_var_single('checked', '', $row);
                        }
                        $rows .= $row;
                    }
                }
            }
            
            $tagdata = preg_replace ("/".LD."permissions".RD.".*?".LD.'\/'."permissions".RD."/s", $rows, $tagdata);
		}
              
        $data['action'] = $this->EE->functions->fetch_site_index();

        $data['hidden_fields']['ACT'] = $this->EE->functions->fetch_action_id('Social_poster', 'save_permissions');   
        if ($this->EE->TMPL->fetch_param('return')=='')
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->functions->fetch_site_index()),'/');
        }
        else if ($this->EE->TMPL->fetch_param('return')=='SAME_PAGE')
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->functions->fetch_current_uri()),'/');
        }
        else if (strpos($this->EE->TMPL->fetch_param('return'), "http://")!==FALSE || strpos($this->EE->TMPL->fetch_param('return'), "https://")!==FALSE)
        {
            $return = '/'.ltrim(str_replace($this->EE->config->item('site_url'), '', $this->EE->TMPL->fetch_param('return')),'/');
        }
        else
        {
            $return = $this->EE->TMPL->fetch_param('return');
        }
        $data['hidden_fields']['RET'] = $return;            
		$data['name']		= ($this->EE->TMPL->fetch_param('name')!='') ? $this->EE->TMPL->fetch_param('name') : 'social_poster_permissions';
        $data['id']		= ($this->EE->TMPL->fetch_param('id')!='') ? $this->EE->TMPL->fetch_param('id') : 'social_poster_permissions';
        $data['class']		= ($this->EE->TMPL->fetch_param('class')!='') ? $this->EE->TMPL->fetch_param('class') : 'social_poster_permissions';
        if ($this->EE->TMPL->fetch_param('ajax')=='yes') $data['hidden_fields']['ajax'] = 'yes';

        $out = $this->EE->functions->form_declaration($data)."\n".
                $tagdata."\n".
                "</form>";
        
        return $out;
    }
    
    
    function save_permissions()
    {
        if ($this->EE->session->userdata('member_id') == 0)
		{
			if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('not_authorized');
                exit();
            }
            $this->EE->output->show_user_error('submission', $this->EE->lang->line('not_authorized'));
            return;
		}	
        
        $site_id = $this->EE->config->item('site_id');
        
        $this->EE->db->select('social_poster_permissions')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_poster_permissions')!='')
        {
            $permissions = unserialize($q->row('social_poster_permissions'));
        }

        foreach ($_POST['permissions'] as $key=>$val)
        {
            $permissions[$site_id][$key] = $val;
        }        
        
        if ( ! class_exists('Social_login_pro_ext'))
    	{
    		require_once PATH_THIRD.'social_login_pro/ext.social_login_pro.php';
    	}
    	$SLP = new Social_login_pro_ext();
        $settings_query = $this->EE->db->select("settings")->from('extensions')->where('class', 'Social_poster_ext')->where('settings != ', '')->limit('1')->get();
        $settings = unserialize($settings_query->row('settings')); 

        foreach ($SLP->providers as $provider)
        {
            if (!isset($permissions[$site_id][$provider]))
            {
                $permissions[$site_id][$provider] = (isset($settings[$site_id]['post_by_default'][$provider]))?$settings[$site_id]['post_by_default'][$provider]:'n';
            }
        }
        
        $upd_data = array('social_login_permissions' => serialize($permissions));
        
        $this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
        $this->EE->db->update('members', $upd_data);
        
        if ($this->EE->input->get_post('ajax')=='yes')
        {
            echo $this->EE->lang->line('preferences_updated');
            exit();
        }
        
        $data = array(	'title' 	=> $this->EE->lang->line('thank_you'),
						'heading'	=> $this->EE->lang->line('thank_you'),
						'content'	=> $this->EE->lang->line('preferences_updated'),
						'redirect'	=> $this->EE->input->post('RET'),							
						'link'		=> array($this->EE->input->post('RET'), $this->EE->lang->line('click_if_no_redirect')),
						'rate'		=> 5
					 );
					
		$this->EE->output->show_message($data);
    }
    
    
    
    
    function _slp_post($provider, $message, $slp_settings, $keys)
    {
        if ( ! class_exists('Social_login_pro_ext'))
    	{
    		require_once PATH_THIRD.'social_login_pro/ext.social_login_pro.php';
    	}
    	
    	$SLP = new Social_login_pro_ext();
        
        $site_id = $this->EE->config->item('site_id');

        if (!isset($keys["$provider"]['oauth_token']) || $keys["$provider"]['oauth_token']=='')
        {
            return;
        }
        if ($slp_settings[$site_id][$provider]['app_id']=='' || $slp_settings[$site_id][$provider]['app_secret']=='' || $slp_settings[$site_id][$provider]['custom_field']=='')
        {
            return;
        }

        if (!isset($slp_settings[$site_id][$provider]['enable_posts']) || $slp_settings[$site_id][$provider]['enable_posts']=='y')
        {
            $msg = $message;
            if (strlen($msg)>$SLP->maxlen[$provider])
            {
                if ( ! class_exists('Shorteen'))
            	{
            		require_once PATH_THIRD.'shorteen/mod.shorteen.php';
            	}
            	
            	$SHORTEEN = new Shorteen();
                
                preg_match_all('/https?:\/\/[^:\/\s]{3,}(:\d{1,5})?(\/[^\?\s]*)?([\?#][^\s]*)?/i', $msg, $matches);

                foreach ($matches as $match)
                {
                    if (!empty($match) && strpos($match[0], 'http')===0)
                    {
                        //truncate urls
                        $longurl = $match[0];
                        if (strlen($longurl)>$SLP->max_link_length)
                        {
                            $shorturl = $SHORTEEN->process($slp_settings[$site_id]['url_shortening_service'], $longurl, true);
                            if ($shorturl!='')
                            {
                                $msg = str_replace($longurl, $shorturl, $msg);
                            }
                        }
                    }
                }
            }
            //still too long? truncate the message
            //at least one URL should always be included
            if (strlen($msg)>$SLP->maxlen[$provider])
            {
                if ($shorturl!='')
                {
                    $len = $SLP->maxlen[$provider] - strlen($shorturl) - 1;
                    $msg = $SLP->_char_limit($msg, $len);
                    $msg .= ' '.$shorturl;
                }
                else
                {
                    $msg = $SLP->_char_limit($msg, $SLP->maxlen[$provider]);
                }
            }
            
            //all is ready! post the message
            $lib = $provider.'_oauth';
            $params = array('key'=>$slp_settings[$site_id]["$provider"]['app_id'], 'secret'=>$slp_settings[$site_id]["$provider"]['app_secret']);
            
			$this->EE->load->add_package_path(PATH_THIRD.'social_login_pro/');
			$this->EE->load->library($lib, $params);
            if ($provider=='yahoo')
            {
                $this->EE->$lib->post($msg, $shorturl, $keys["$provider"]['oauth_token'], $keys["$provider"]['oauth_token_secret'], array('guid'=>$keys["$provider"]['guid']));
            }
            else
            {
                $this->EE->$lib->post($msg, $shorturl, $keys["$provider"]['oauth_token'], $keys["$provider"]['oauth_token_secret']);    
            }
            $this->EE->load->remove_package_path(PATH_THIRD.'social_login_pro/');
        }
    }


}
/* END */
?>