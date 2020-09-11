<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class PixelVk extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('PIXEL');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_PLACEHOLDER2'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
				  			Loc::getMessage('LANDING_HOOK_PIXEL_VK_HELP') .
				  		'</a>'
					: ''
			))
		);
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return bool
	 */
	public function enabledInEditMode()
	{
		return false;
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		if ($this->execCustom())
		{
			return;
		}

		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			Cookies::addCookieScript(
				'vkp',
				'!function(){
					var t=document.createElement("script");
					t.type="text/javascript",
					t.async=!0,
					t.src="https://vk.com/js/api/openapi.js?160",
					t.onload=function(){VK.Retargeting.Init("' . $counter . '"),
					VK.Retargeting.Hit()},document.head.appendChild(t)
				}();'
			);
			Manager::setPageView(
				'Noscript',
				'<noscript>
					<img src="https://vk.com/rtrg?p=' . $counter . '" style="position:fixed; left:-999px;" alt=""/>
				</noscript>'
			);
		}
	}
}
