<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Robots extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_ROBOTS_USE')
			)),
			'CONTENT' => new Field\Textarea('CONTENT', array(
				'title' => Loc::getMessage('LANDING_HOOK_ROBOTS_CONTENT'),
				'placeholder' => 'User-agent: Google
Allow: /folder1/
Disallow: /file1.html
Host: www.site.com

User-agent: *
Disallow: /document.php'
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_ROBOTS_NAME');
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
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return string
	 */
	public function exec()
	{
		return $this->fields['CONTENT']->getValue();
	}
}
