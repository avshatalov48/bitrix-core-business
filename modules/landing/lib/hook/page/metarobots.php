<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MetaRobots extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'INDEX' => new Field\Checkbox('INDEX', array(
				'title' => Loc::getMessage('LANDING_HOOK_MRINDEX')
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_MR_NAME');
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return true;//always
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return boolean
	 */
	public function enabledInEditMode()
	{
		return false;
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

		if (\Bitrix\Landing\Landing::getPreviewMode())
		{
			$use = 'N';
		}
		else
		{
			$use = $this->fields['INDEX']->getValue();
		}
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta name="robots" content="' . ($use != 'N' ? 'all' : 'noindex') . '" />'
		);
	}
}
