<?php
namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\Help;
use Bitrix\Landing\Hook\Page;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

class GMap extends Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = Help::getHelpUrl('GMAP_EDIT');
		return [
			'USE' => new Field\Checkbox('USE', [
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE_USE'),
			]),
			'CODE' => new Field\Text('CODE', [
				'title' => Loc::getMessage('LANDING_HOOK_MAPCODE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_MAPCODE_PLACEHOLDER'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">'
						. Loc::getMessage('LANDING_HOOK_MAPCODE_HELP')
						. '</a>'
					: '',
			]),
		];
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

		return
			$this->fields['USE']->getValue() === 'Y'
			&& !empty($this->fields['CODE']->getValue())
		;
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

		$code = HtmlFilter::encode(trim($this->fields['CODE']));
		$assets = Asset::getInstance();
		$assets->addString(
			"<script defer>
				(function(){
					'use strict';
					//fake function, if API will loaded fasten than blocks
					window.onGoogleMapApiLoaded = function(){}
				})();
			</script>"
		);
		$assets->addString(
			'<script defer src="https://maps.googleapis.com/maps/api/js?key='
			. $code
			. '&callback=onGoogleMapApiLoaded"></script>'
		);
	}
}
