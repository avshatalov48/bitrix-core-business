<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class View extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Return view types.
	 * @return array
	 */
	public function getItems()
	{
		static $items = array();
		if (empty($items))
		{
			$items = array(
				'no' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_NO'),
				'ltr' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_LTR'),
				'all' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_ALL'),
				'mobile' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_MOBILE')
			);
		}
		return $items;
	}

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_VIEW_USE')
			)),
			'TYPE' => new Field\Select('TYPE', array(
				'title' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE'),
				'options' => $this->getItems()
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_VIEW_NAME');
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
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		if ($this->execCustom())
		{
			return;
		}

		$type = trim($this->fields['TYPE']);

		if ($type == 'ltr')
		{
			Manager::setPageView(
				'MainClass',
				'g-pt-6 g-px-10 g-pt-30--md g-px-50--md'
			);
		}
		elseif ($type == 'all')
		{
			Manager::setPageView(
				'MainClass',
				'g-py-6 g-px-10 g-py-30--md g-px-50--md'
			);
		}
		elseif ($type == 'mobile')
		{
			Manager::setPageView(
				'MainClass',
				'g-max-width-768--md mx-md-auto'
			);
		}
	}
}