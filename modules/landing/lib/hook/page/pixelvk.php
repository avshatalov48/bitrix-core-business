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
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_PLACEHOLDER2')
			))
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			Manager::setPageView('AfterHeadOpen',
								 '<script type="text/javascript" data-skip-moving="true">
					!function(){
						var t=document.createElement("script");
						t.type="text/javascript",
						t.async=!0,
						t.src="https://vk.com/js/api/openapi.js?160",
						t.onload=function(){VK.Retargeting.Init("' . $counter . '"),
						VK.Retargeting.Hit()},document.head.appendChild(t)
					}();
				</script>'
			);
			Manager::setPageView(
				'AfterBodyOpen',
				'<noscript>
					<img src="https://vk.com/rtrg?p=' . $counter . '" style="position:fixed; left:-999px;" alt=""/>
				</noscript>'
			);
		}
	}
}
