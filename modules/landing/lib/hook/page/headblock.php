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
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CODE_HELP2'),
				'placeholder' => '<script>
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];
</script>'
			)),
			'CSS_CODE' => new Field\Textarea('CSS_CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE'),
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CSS_CODE_HELP'),
				'placeholder' => '* {display: none;}'
			)),
			'CSS_FILE' => new Field\Textarea('CSS_FILE', array(
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
		$code = trim($this->fields['CODE']);
		$cssCode = trim($this->fields['CSS_CODE']);
		$cssFile = trim($this->fields['CSS_FILE']);

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
		if ($cssFile != '')
		{
			echo '<link href="' . \htmlspecialcharsbx($cssFile) . '" type="text/css"  rel="stylesheet" />';
		}
	}
}
