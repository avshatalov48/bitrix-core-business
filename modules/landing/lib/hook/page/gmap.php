<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

class GMap extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('GMAP_EDIT');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE_USE')
			)),
			'CODE' => new Field\Text('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_MAPCODE_PLACEHOLDER'),
				'help' => $helpUrl
							? '<a href="' . $helpUrl . '" target="_blank">' .
						  			Loc::getMessage('LANDING_HOOK_MAPCODE_HELP') .
						  		'</a>'
							: ''
			))
		);
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
		else
		{
			return \CJSCore::isExtensionLoaded('landing_google_maps_new') ||
				$this->fields['USE']->getValue() == 'Y';
		}
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

		$code = '';
		if ($this->fields['USE']->getValue() == 'Y')
		{
			$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		}
		Asset::getInstance()->addString(
			"<script defer>
				(function(){
					'use strict';
					//fake function, if API will loaded fasten than blocks
					window.onGoogleMapApiLoaded = function(){}
				})();
			</script>"
		);
		Asset::getInstance()->addString(
			'<script defer src="https://maps.googleapis.com/maps/api/js?key=' . $code . '&callback=onGoogleMapApiLoaded"></script>'
		);
	}
}
