<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Class UIFormComponent
 */
class UIFormComponent extends \CBitrixComponent
{
	/**
	 * Execute component.
	 */
	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}