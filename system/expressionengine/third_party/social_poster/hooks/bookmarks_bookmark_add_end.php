<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bookmarks_bookmark_add_end_sp_hook extends Sp_hook {
    
   	public function __construct($data = array(), $debug = FALSE)
	{
		parent::__construct($data);
	}
    
    function template()
    {
        $template = "I like {title}.";
        return $template;
    }
    
    function vars()
    {
        $vars = array(
            'title'         => 'Entry title',
            'channel_name'  => 'Channel name',
            'type'          => 'Data type (comment|entry|member|category)'
        );
        return serialize($vars);
    }
    
    function link()
    {
        $link = '{site_url}';
        return $link;
    }
    
    function bookmarks_bookmark_add_end($args_array)
    {
        $tmpl = $this->get_tmpl('bookmarks_bookmark_add_end');
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
        
        
        $vars = $args_array[0];
        
        if ($vars['type']=='entry')
        {
            $this->EE->db->select("exp_channel_titles.*, exp_channels.channel_url, exp_channels.comment_url, exp_channels.channel_title")
                ->from('channel_titles')
                ->join('exp_channels', 'exp_channel_titles.channel_id = exp_channels.channel_id', 'left')
                ->where("exp_channel_titles.entry_id", $vars['data_id']);
            $q = $this->EE->db->get();
            $vars = array_merge($vars, $q->row_array());
        }
        
        $this->EE->load->library('template');
        
        $link = $this->EE->template->parse_variables_row($link, $vars);
        $link = $this->EE->template->parse_globals($link);
        $link = $this->EE->template->simple_conditionals($link);
        $link = $this->EE->template->advanced_conditionals($link);

        $message = $this->EE->template->parse_variables_row($message, $vars);
        $message = $this->EE->template->parse_globals($message);
        $message = $this->EE->template->simple_conditionals($message);
        $message = $this->EE->template->advanced_conditionals($message);
        

        parent::post($message, $link);
    }
    
}

?>