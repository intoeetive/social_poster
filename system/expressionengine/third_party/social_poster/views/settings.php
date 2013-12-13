<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=social_poster', array('id'=>'social_poster_settings_form'));?>


<?php 
$this->table->set_template($cp_pad_table_template); 
$this->table->set_heading(
    array('data' => lang('social_network'), 'style' => 'width:50%;'),
    lang('post_by_default')
);


foreach ($settings as $key => $val)
{
	$this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

$this->table->clear();
?>

<p>&nbsp;</p>

<?php 
$this->table->set_template($cp_pad_table_template); 
$this->table->set_heading(
    array('data' => lang('extension_hook'), 'style' => 'width:50%;'),
    lang('enabled')
);


foreach ($hooks as $key => $val)
{
	$this->table->add_row(lang($key, $key), $val);
    $this->table->add_row($templates[$key]['vars'], $templates[$key]['template']);
}

echo $this->table->generate();

$this->table->clear();
?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>

<?php
form_close();

