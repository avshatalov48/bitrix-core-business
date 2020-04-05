<?php

namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Field;
use \Bitrix\Landing\Help;
use \Bitrix\Landing\Landing;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Speed extends \Bitrix\Landing\Hook\Page
{
	const LAZYLOAD_EXTENSION_NAME = 'landing_lazyload';
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = Help::getHelpUrl('SPEED');
		
		return array(
			'ASSETS' => new Field\Text('ASSETS', array()),
			'USE_LAZY' => new Field\Checkbox('USE_LAZY', array(
				'title' => Loc::getMessage('LANDING_HOOK_SPEED_USE_LAZY'),
			)),
			'USE_WEBPACK' => new Field\Checkbox('USE_WEBPACK', array(
				'title' => ($mess = Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBPACK2'))
							? $mess
							: Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBPACK'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
					Loc::getMessage('LANDING_HOOK_SPEED_HELP') .
					'</a>'
					: '',
			)),
			'USE_WEBP' => new Field\Checkbox('USE_WEBP', array(
				'title' => Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBP'),
			)),
		);
	}
	
	/**
	 * Hook title.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_SPEED_TTILE');
	}
	
	/**
	 * Add data to serialize array
	 * @param $field - name of hook field
	 * @param $data - array of data
	 * @return string
	 */
	public function addData($field, $data)
	{
		if (!is_array($data))
		{
			$data = [$data];
		}
		
		if (
			$this->fields[$field] &&
			($hookData = $this->fields[$field]->getValue())
		)
		{
			$mergedData = array_unique(array_merge(unserialize($hookData), $data));
		}
		else
		{
			$mergedData = $data;
		}
		
		return serialize($mergedData);
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
		
		if ($this->isPage())
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		if (Landing::getEditMode())
		{
			$this->disableSpeedConversions();
		}
		else
		{
			$this->computeWebpackActivity();
			$this->computeLazyloadActivity();
		}
	}
	
	protected function disableSpeedConversions()
	{
		$assets = Assets\Manager::getInstance();
		$assets->setStandartMode();
	}
	
	protected function computeWebpackActivity()
	{
		$assets = Assets\Manager::getInstance();
		if ($this->fields['USE_WEBPACK']->getValue() == 'Y')
		{
			$assets->setWebpackMode();
		}
		else
		{
			$assets->setStandartMode();
		}
	}
	
	protected function computeLazyloadActivity()
	{
		if ($this->fields['USE_LAZY']->getValue() == 'Y')
		{
			$assets = Assets\Manager::getInstance();
			$assets->addAsset(self::LAZYLOAD_EXTENSION_NAME);
		}
	}
}
