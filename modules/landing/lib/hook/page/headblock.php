<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class HeadBlock extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_USE')
			)),
			'CODE' => new Field\Textarea('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CODE'),
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CODE_HELP'),
				'placeholder' => '<script>
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];
</script>'
			)),
			'CSS_CODE' => new Field\Textarea('CSS_CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE'),
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE_HELP'),
				'placeholder' => '* {display: none;}'
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_HEADBLOCK_NAME');
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
	 * Exec or not hook in edit mode.
	 * @return true
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
		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$use = $this->fields['USE']->getValue() == 'Y';
		$code = trim($this->fields['CODE']);
		$cssCode = trim($this->fields['CSS_CODE']);

		if ($code != '')
		{
			$code = str_replace(
				'<script',
				'<script data-skip-moving="true"', $code
			);
			\Bitrix\Main\Page\Asset::getInstance()->addString($code);
		}
		if ($cssCode != '')
		{
			echo '<style type="text/css">' . $cssCode . '</style>';
		}
	}
}
