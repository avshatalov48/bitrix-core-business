<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CssBlock extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_CSSLOCK_USE')
			)),
			'CODE' => new Field\Textarea('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE'),
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE_HELP2'),
				'placeholder' => '* {display: none;}'
			)),
			'FILE' => new Field\Textarea('FILE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_FILE')
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_CSSBLOCK_NAME');
	}

	/**
	 * Get sort of block (execute order).
	 * @return int
	 */
	public function getSort()
	{
		return 500;
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

		$cssCode = trim($this->fields['CODE']);
		$cssFile = trim($this->fields['FILE']);

		if ($cssCode != '')
		{
			echo '<style type="text/css">' . $cssCode . '</style>';
		}
		if ($cssFile != '')
		{
			echo '<link href="' . \htmlspecialcharsbx($cssFile) . '" type="text/css"  rel="stylesheet" />';
		}
	}
}
