<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GMap extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE_USE')
			)),
			'CODE' => new Field\Text('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE')
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
		$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<script defer src="https://maps.googleapis.com/maps/api/js?key=' . $code . '"></script>'
		);
	}
}
