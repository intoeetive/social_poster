<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//file name is extension hook name (+".php")

class Entry_submission_end_sp_hook extends Sp_hook {
    
    //class name consist of file name + '_sp_hook'
       
    public function __construct($data = array(), $debug = FALSE)
	{
		parent::__construct($data);
        $this->action_name = 'entry submitted'; //action name displayed in settings and in permissions tag
	}
    
    function template() //default message template
    {
        $template = "I just published entry: {title}.";
        return $template;
    }
    
    function vars()
    {
        //array of available variables and their descriptions
        //will be listed on settings page
        $vars = array(
            'title'         => 'Entry title',
            'url_title'           => 'Entry url_title',
            'entry_id'      => 'Entry ID',
            'channel_title'  => 'Channel name',
            'channel_url'  => 'Channel URL',
            'comment_url'  => 'Channel comment page URL',
            '...'       => 'All entry variables submitted'
        );
        return serialize($vars);
    }
    
    function link() //default link template
    {
        $link = '{site_url}/{channel_short_name}/{url_title}';
        return $link;
    }
    
    //'main' function
    //name is file name (which is also name of extension hook that triggers action)
    function entry_submission_end($args_array)
    {
        //get template, caller function name as parameter
        $tmpl = $this->get_tmpl('entry_submission_end'); 
        //'do not modify' section
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
        $vars = array();
        //end of 'do not modify' section
        
        
        //let the fun begin
        //build $vars - variables array
        //$vars[$variable_name] = $variable_value
        $vars['entry_id']  = $args_array[0];
        $vars = array_merge($vars, $args_array[1], $args_array[2]);

        $q = $this->EE->db->select('field_id, field_name')
				->from('channel_fields')
                ->join('channels', 'channels.field_group=channel_fields.group_id', 'left')
				->where('channel_id', $vars['channel_id'])
				->get();
        foreach ($q->result() as $obj)
        {
            $vars[$obj->field_name] = $vars['field_id_'.$obj->field_id];
        }      
        
        $q = $this->EE->db->select('*')
				->from('channels')
				->where('channel_id', $vars['channel_id'])
				->get();
        foreach ($q->result_array() as $row)
        {
            foreach ($row as $key=>$val)
            {
                $vars[$key] = $val;
            }
        }      
        //finished building variables array  
            
        
        
        //'do not modify' section
        $this->EE->load->library('template');
        
        $link = $this->EE->template->parse_variables_row($link, $vars);
        $link = $this->EE->template->parse_globals($link);
        $link = $this->EE->template->simple_conditionals($link);
        $link = $this->EE->template->advanced_conditionals($link);

        $message = $this->EE->template->parse_variables_row($message, $vars);
        $message = $this->EE->template->parse_globals($message);
        $message = $this->EE->template->simple_conditionals($message);
        $message = $this->EE->template->advanced_conditionals($message);
        //end of 'do not modify' section
        
        //trigger post
        //function name to be passed as first parameter, message - second, link - third
        parent::post('entry_submission_end', $message, $link);
    }
    
}

?>