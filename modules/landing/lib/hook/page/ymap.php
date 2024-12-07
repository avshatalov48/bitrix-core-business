<?php

namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\Help;
use Bitrix\Landing\Hook\Page;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

class YMap extends Page
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
				'title' => Loc::getMessage('LANDING_HOOK_YMAPCODE_USE'),
			]),
			'CODE' => new Field\Text('CODE', [
				'title' => Loc::getMessage('LANDING_HOOK_YMAPCODE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_YMAPCODE_PLACEHOLDER'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">'
						. Loc::getMessage('LANDING_HOOK_YMAPCODE_HELP')
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
			&& !empty($this->fields['CODE']->getValue());
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

		if (!Manager::availableOnlyForZone('ru'))
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
					window.onYandexMapApiLoaded = function(){}
				})();
			</script>"
		);

		// todo: lang=language_region https://yandex.ru/dev/maps/jsapi/doc/2.1/dg/concepts/localization.html
		// todo: load = modules https://yandex.ru/dev/maps/jsapi/doc/2.1/dg/concepts/modules.html
		$assets->addString(
			'<script src="https://api-maps.yandex.ru/2.1/?apikey='
			. $code
			. '&lang=ru_RU&onload=onYandexMapApiLoaded"></script>'
		);
	}
}
