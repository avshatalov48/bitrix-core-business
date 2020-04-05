<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UiButtonPanel
 */
class UiButtonPanel extends CBitrixComponent
{
	/**
	 * Execute component.
	 *
	 * @return void
	 */
	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}