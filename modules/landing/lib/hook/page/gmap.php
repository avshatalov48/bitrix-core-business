<?php
namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\Help;
use Bitrix\Landing\Hook\Page;
use Bitrix\Landing\Internals\SiteTable;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

class GMap extends Page
{
	private $lang;
	private $siteId;

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
		$lang = $this->getLang();
		$assets->addString(
			'<script defer src="https://maps.googleapis.com/maps/api/js?key='
			. $code
			. '&region='
			. $lang
			. '&language='
			. $lang
			. '&callback=onGoogleMapApiLoaded"></script>'
		);
	}

	/**
	 * Save current site id
	 * @param int $siteId SiteId.
	 * @return void
	 */
	public function setSiteId(int $siteId): void
	{
		$this->siteId = $siteId;
	}

	/**
	 * Get current site language
	 * @return string
	 */
	protected function getLang(): string
	{
		$lang = null;
		if ($this->siteId > 0)
		{
			$res = SiteTable::getList([
				'select' => [
					'LANG'
				],
				'filter' => [
					'=ID' => $this->siteId
				]
			])->fetch();
			if ($res['LANG'])
			{
				$lang = $res['LANG'];
				if ($lang === 'tc')
				{
					$lang = 'zh';
				}
				if ($lang === 'vn')
				{
					$lang = 'vi';
				}
			}
		}
		return $lang ?: Manager::getZone();
	}
}
