<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Up extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'SHOW' => new Field\Checkbox('SHOW', array(
				'title' => Loc::getMessage('LANDING_HOOK_UP_SHOW')
			))
		);
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

		return $this->fields['SHOW']->getValue() == 'Y';
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

		$assets = Assets\Manager::getInstance();
		$assets->addAsset('landing_upper');
	}
}
