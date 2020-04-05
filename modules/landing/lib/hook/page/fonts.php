<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;

class Fonts extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'CODE' => new Field\Textarea('CODE', array())
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return trim($this->fields['CODE']->getValue()) != '';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		// @fix for 101643
		$this->fields['CODE'] = str_replace(
			'onl oad',
			'onload',
			$this->fields['CODE']
		);
		\Bitrix\Landing\Manager::setPageView(
			'BeforeHeadClose',
			$this->fields['CODE']
		);
	}
}
