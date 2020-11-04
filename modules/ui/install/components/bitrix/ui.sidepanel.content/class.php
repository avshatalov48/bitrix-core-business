<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Loader;

/**
 * Class UISliderContentComponent
 */
class UISliderContentComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}
