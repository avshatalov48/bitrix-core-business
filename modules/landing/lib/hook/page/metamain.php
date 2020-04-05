<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MetaMain extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_USE')
			)),
			'TITLE' => new Field\Text('TITLE', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_TITLE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAMAIN_TITLE_PLACEHOLDER'),
				'maxlength' => 75
			)),
			'DESCRIPTION' => new Field\Textarea('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_DESCRIPTION_TITLE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAMAIN_DESCRIPTION_PLACEHOLDER'),
				'maxlength' => 200
			)),
			'KEYWORDS' => new Field\Text('KEYWORDS', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_KEYWORDS_TITLE'),
				'maxlength' => 250
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_METAMAIN_NAME');
	}

	/**
	 * Description of Hook, if you want.
	 * @return string
	 */
	public function getDescription()
	{
		return Loc::getMessage('LANDING_HOOK_METAMAIN_DESCRIPTION');
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
		$title = \htmlspecialcharsbx(trim($this->fields['TITLE']));
		$description = \htmlspecialcharsbx(trim($this->fields['DESCRIPTION']));
		$keywords = \htmlspecialcharsbx(trim($this->fields['KEYWORDS']));
		if ($title != '')
		{
			\Bitrix\Landing\Manager::getApplication()->setTitle($title);
		}
		if ($description != '')
		{
			\Bitrix\Main\Page\Asset::getInstance()->addString(
				'<meta name="description" content="' . $description . '" />'
			);
		}
		if ($keywords != '')
		{
			\Bitrix\Main\Page\Asset::getInstance()->addString(
				'<meta name="keywords" content="' . $keywords . '" />'
			);
		}
	}
}
