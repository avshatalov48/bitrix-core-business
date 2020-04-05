<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Landing\Seo;
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
				'maxlength' => 140,
				'searchable' => true
			)),
			'DESCRIPTION' => new Field\Textarea('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_DESCRIPTION_TITLE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAMAIN_DESCRIPTION_PLACEHOLDER'),
				'maxlength' => 300,
				'searchable' => true
			)),
			'KEYWORDS' => new Field\Text('KEYWORDS', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAMAIN_KEYWORDS_TITLE'),
				'maxlength' => 250,
				'searchable' => true
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

		$title = \htmlspecialcharsbx(Seo::processValue('title', $this->fields['TITLE']));
		$description = Seo::processValue('description', $this->fields['DESCRIPTION']);
		$keywords = Seo::processValue('keywords', $this->fields['KEYWORDS']);

		if ($title != '')
		{
			Manager::setPageTitle($title);
		}
		if ($description != '')
		{
			Manager::getApplication()->setPageProperty(
				'description',
				$description
			);
		}
		if ($keywords != '')
		{
			Manager::getApplication()->setPageProperty(
				'keywords',
				$keywords
			);
		}
	}
}
