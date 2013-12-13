<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sp_hook {
 		
	public function __construct($params = array())
	{
		$this->EE =& get_instance();        
	}	
    
    public function get_tmpl($hook)
	{
        $q = $this->EE->db->select("tmpl_body, tmpl_link")
                ->from('social_poster_templates')
                ->where('hook', $hook)
                ->where('site_id', $this->EE->config->item('site_id'))
                ->get();
                
        if ($q->num_rows()==0) return false;
        
        $tmpl = array(
            'link'  => $q->row('tmpl_link'),
            'message'   => $q->row('tmpl_body')
        );
        
        return $tmpl;
    }
    
    
    function template()
    {
        $template = "{site_name}";
        return $template;
    }
    
    function vars()
    {
        $vars = array(
            'site_name'         => 'Site name'
        );
        return serialize($vars);
    }
    
    function link()
    {
        $link = '{site_url}';
        return $link;
    }
    
    
    public function get_keys()
	{

        $keys_arr = array();

        $site_id = $this->EE->config->item('site_id');
        
        //first, keys
        
        //then, permissions
        
        $this->EE->db->select('social_login_keys, social_poster_permissions')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_login_keys')=='') return false;
        if ($q->row('social_login_keys')!='')
        {
            $user_keys = unserialize($q->row('social_login_keys'));
            $user_permissions = unserialize($q->row('social_poster_permissions'));
            
            if (empty($user_keys)) return false;
            foreach ($user_keys as $provider => $keys)
            {
                if (isset($user_permissions[$site_id][$provider]))
                {
                    if ($user_permissions[$site_id][$provider]=='y')
                    {
                        $keys_arr[$provider] = $keys;
                    }
                }
                else
                {
                    if (!isset($settings))
                    {
                        $settings_query = $this->EE->db->select("settings")
                                            ->from('extensions')
                                            ->where('class', 'Social_poster_ext')
                                            ->where('settings != ', '')
                                            ->limit('1')
                                            ->get();
                        $settings = unserialize($settings_query->row('settings')); 
                    }
                    if ($settings[$site_id]['post_by_default'][$provider]=='y')
                    {
                        $keys_arr[$provider] = $keys;
                    }
                }
            }

        }

        return $keys_arr;
    }
    
    
	public function post($message, $link = '')
	{
        
        $keys_arr = $this->get_keys();
        
        if ($keys_arr==false || empty($keys_arr)) return false;
        
        if ( ! class_exists('Social_login_pro_ext'))
    	{
    		require_once PATH_THIRD.'social_login_pro/ext.social_login_pro.php';
    	}
    	
    	$SLP = new Social_login_pro_ext();
        
        $slp_settings_q = $this->EE->db->select('settings')
                            ->from('modules')
                            ->where('module_name','Social_login_pro')
                            ->limit(1)
                            ->get(); 
        if ($slp_settings_q->num_rows()==0) return false;

        $slp_settings = unserialize($slp_settings_q->row('settings'));
        
        $site_id = $this->EE->config->item('site_id');
        
        foreach ($keys_arr as $provider=>$keys)
        {
        

            if (!isset($keys['oauth_token']) || $keys['oauth_token']=='')
            {
                continue;
            }
            if ($slp_settings[$site_id][$provider]['app_id']=='' || $slp_settings[$site_id][$provider]['app_secret']=='' || $slp_settings[$site_id][$provider]['custom_field']=='')
            {
                continue;
            }
    
            if (!isset($slp_settings[$site_id][$provider]['enable_posts']) || $slp_settings[$site_id][$provider]['enable_posts']=='y')
            {
                if ( ! class_exists('Shorteen'))
            	{
            		require_once PATH_THIRD.'shorteen/mod.shorteen.php';
            	}
            	
            	$SHORTEEN = new Shorteen();
                
                $link = $SHORTEEN->process($slp_settings[$site_id]['url_shortening_service'], $link, true);

                //too long? truncate the message
                if (strlen($message.' '.$link) > $SLP->maxlen[$provider])
                {
                    $len = $SLP->maxlen[$provider] - strlen($shorturl) - 1;
                    $message = $SLP->_char_limit($message, $len);
                }
                
                if ($provider!='facebook') $message .= ' '.$link;
                
                
                //all is ready! post the message
                $lib = $provider.'_oauth';
                $params = array('key'=>$slp_settings[$site_id]["$provider"]['app_id'], 'secret'=>$slp_settings[$site_id]["$provider"]['app_secret']);
                
    			$this->EE->load->add_package_path(PATH_THIRD.'social_login_pro/');
    			$this->EE->load->library($lib, $params);
                if ($provider=='yahoo')
                {
                    $this->EE->$lib->post($message, $link, $keys['oauth_token'], $keys['oauth_token_secret'], array('guid'=>$keys['guid']));
                }
                else
                {
                    $this->EE->$lib->post($message, $link, $keys['oauth_token'], $keys['oauth_token_secret']);   
                }
                $this->EE->load->remove_package_path(PATH_THIRD.'social_login_pro/');
            }
        }
    }
}