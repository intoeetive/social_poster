<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sp_hook {
 		
	public function __construct($params = array())
	{
		//parent::__construct($params);
	}	
    
    public function get_providers()
	{

        $providers = array();

        $site_id = $this->EE->config->item('site_id');
        
        $this->EE->db->select('social_poster_permissions')
            ->from('members')
            ->where('member_id', $this->EE->session->userdata('member_id'));
        $q = $this->EE->db->get();
        if ($q->row('social_poster_permissions')!='')
        {
            $user_permissions = unserialize($q->row('social_poster_permissions'));
            if (isset($user_permissions[$site_id]))
            {
                $providers = $user_permissions[$site_id];
                return $providers;
            }
        }
        
        $settings_query = $this->EE->db->select("settings")->from('extensions')->where('class', 'Social_poster_ext')->where('settings != ', '')->limit('1');
        $settings = unserialize($settings_query->row('settings')); 
        
        foreach ($settings[$site_id]['post_by_default'] as $provider=>$enabled)
        {
            if ($enabled=='')
            {
                $providers[] = $provider;
            }
        }
        
        return $providers;
    }
    
    
	public function post($message, $link = '')
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