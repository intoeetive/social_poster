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
            'title',
            'channel_name'
        );
        return serialize($vars);
    }
    
    function bookmarks_bookmark_add_end($args_array)
    {
        $link = '';
        $message = '';
        
        parent::post($link, $message);
    }
    
}

?>