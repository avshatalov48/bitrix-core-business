<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Copyright extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'SHOW' => new Field\Checkbox('SHOW', array(
				'title' => Manager::isB24()
						? Loc::getMessage('LANDING_HOOK_COPYRIGHT_SHOW')
						: Loc::getMessage('LANDING_HOOK_COPYRIGHT_SHOW_SMN')
			))
		);
	}

	/**
	 * Enable only in high plan.
	 * @return boolean
	 */
	public function isFree()
	{
		return false;
	}

	/**
	 * Locked or not current hook in free plan.
	 * @return bool
	 */
	public function isLocked()
	{
		return !\Bitrix\Landing\Restriction\Manager::isAllowed(
			'limit_sites_powered_by'
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->isLocked())
		{
			return false;
		}

		return $this->fields['SHOW']->getValue() != 'N';
	}

	/**
	 * Exec hook. Show or not any copyright.
	 * @return boolean
	 */
	public function exec()
	{
		return true;
	}
}
