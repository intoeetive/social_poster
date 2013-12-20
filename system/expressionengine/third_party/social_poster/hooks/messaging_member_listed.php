<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messaging_member_listed_sp_hook extends Sp_hook {
       
    public function __construct($data = array(), $debug = FALSE)
	{
		parent::__construct($data);
        $this->action_name = 'member listed';
	}
    
    function template()
    {
        $template = "I just followed {screen_name}.";
        return $template;
    }
    
    function vars()
    {
        $vars = array(
            'type'               => 'Listing type (buddy/blocked)',
            'screen_name'        => 'Screen name',
            'username'           => 'Username',
            'member_id'          => 'Member ID',
            '...'               => 'All member variables from exp_members table (no custom fields)'
        );
        return serialize($vars);
    }
    
    function link()
    {
        $link = '{site_url}/members/{username}';
        return $link;
    }
    
    function messaging_member_listed($args_array)
    {
        $tmpl = $this->get_tmpl('messaging_member_listed');
        if ($tmpl!=false)
        {
            $link = $tmpl['link'];
            $message = $tmpl['message'];
        }
        else
        {
            $link = $this->link();
            $message = $this->template();
        }

        $vars = array(
            'type'  => $args_array[2]
        );

        $this->EE->db->select("*")
            ->from('members')
            ->where("member_id", $args_array[1]);
        $q = $this->EE->db->get();
        $vars = array_merge($vars, $q->row_array());
        
        $this->EE->load->library('template');
        
        $link = $this->EE->template->parse_variables_row($link, $vars);
        $link = $this->EE->template->parse_globals($link);
        $link = $this->EE->template->simple_conditionals($link);
        $link = $this->EE->template->advanced_conditionals($link);

        $message = $this->EE->template->parse_variables_row($message, $vars);
        $message = $this->EE->template->parse_globals($message);
        $message = $this->EE->template->simple_conditionals($message);
        $message = $this->EE->template->advanced_conditionals($message);
        

        parent::post('messaging_member_listed', $message, $link);
    }
    
}

?>