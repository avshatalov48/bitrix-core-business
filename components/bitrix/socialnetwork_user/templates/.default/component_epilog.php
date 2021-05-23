<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
if ($this->__buffer_template === true)
{
	if (!in_array($this->__template->__page, array("user_files_menu", "group_files_menu")))
	{
		$this->__template_html = ob_get_clean();
		$this->IncludeComponentTemplate(mb_strpos($this->__template->__page, "user_files") !== false ? "user_files_menu" : "group_files_menu");
	}
	else
	{
		echo $this->__template_html; 
	}
}
?>