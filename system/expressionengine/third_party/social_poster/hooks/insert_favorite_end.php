<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Insert_favorite_end_sp_hook extends Sp_hook {
       
    public function __construct($data = array(), $debug = FALSE)
	{
		parent::__construct($data);
        $this->action_name = 'favorite added';
	}
    
    function template()
    {
        $template = "I like {title}.";
        return $template;
    }
    
    function vars()
    {
        $vars = array(
            'entry_id'         => 'Entry ID',
            'title'         => 'Entry title',
            'channel_title'  => 'Channel name',
            'channel_url'  => 'Channel URL',
            'comment_url'  => 'Channel comment page URL',
            '...'       => 'All entry variables from exp_channel_titles (no custom fields)'
        );
        return serialize($vars);
    }
    
    function link()
    {
        $link = '{site_url}';
        return $link;
    }
    
    function insert_favorite_end($args_array)
    {
        $tmpl = $this->get_tmpl('insert_favorite_end');
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
        
        if ($vars['type']=='entry_id')
        {
            $this->EE->db->select("exp_channel_titles.*, exp_channels.channel_url, exp_channels.comment_url, exp_channels.channel_title")
                ->from('channel_titles')
                ->join('exp_channels', 'exp_channel_titles.channel_id = exp_channels.channel_id', 'left')
                ->where("exp_channel_titles.entry_id", $vars['entry_id']);
            $q = $this->EE->db->get();
            $vars = array_merge($vars, $q->row_array());
        }

        $this->EE->load->library('template');
        if (!isset($this->EE->template)) $this->EE->template = $this->EE->TMPL;
        
        $link = $this->EE->template->parse_variables_row($link, $vars);
        $link = $this->EE->template->parse_globals($link);
        $link = $this->EE->template->simple_conditionals($link);
        $link = $this->EE->template->advanced_conditionals($link);

        $message = $this->EE->template->parse_variables_row($message, $vars);
        $message = $this->EE->template->parse_globals($message);
        $message = $this->EE->template->simple_conditionals($message);
        $message = $this->EE->template->advanced_conditionals($message);
        

        parent::post('insert_favorite_end', $message, $link);
    }
    
}

?>