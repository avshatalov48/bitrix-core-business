<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Rights;
use Bitrix\Landing\TemplateRef;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\PlacementTable;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSettingsComponent extends LandingBaseComponent
{
	protected const PAGE_URL_PREFIX = 'PAGE_URL_';

	protected const AVAILABLE_PAGES = [
		'SITE_EDIT' => self::PAGE_SITE_EDIT,
		'SITE_DESIGN' => self::PAGE_SITE_DESIGN,
		'LANDING_EDIT' => self::PAGE_LANDING_EDIT,
		'LANDING_DESIGN' => self::PAGE_LANDING_DESIGN,
		'CATALOG_EDIT' => self::PAGE_CATALOG_EDIT,
	];
	protected const PAGE_SITE_EDIT = 'SITE_EDIT';
	protected const PAGE_SITE_DESIGN = 'SITE_DESIGN';
	protected const PAGE_LANDING_EDIT = 'LANDING_EDIT';
	protected const PAGE_LANDING_DESIGN = 'LANDING_DESIGN';
	protected const PAGE_CATALOG_EDIT = 'CATALOG_EDIT';

	protected const REPLACER_PAGE_CATALOG = 'SITE_EDIT';
	protected const PAGES_FOR_LANDING = [
		'LANDING_EDIT',
		'LANDING_DESIGN',
	];

	/**
	 * Base executable method.
	 *
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('LANDING_ID', 0);
		$this->checkParam('TYPE', '');

		$this->arResult['ITEMS'] = [];

		foreach (self::AVAILABLE_PAGES as $code)
		{
			$pageCode = self::PAGE_URL_PREFIX . $code;
			if ($code === self::PAGE_CATALOG_EDIT)
			{
				if ($this->arParams['TYPE'] !== 'STORE')
				{
					continue;
				}
				$pageCode = self::PAGE_URL_PREFIX . self::REPLACER_PAGE_CATALOG;
			}
			elseif (!isset($this->arParams['PAGES'][$pageCode]))
			{
				continue;
			}

			if (
				!$this->arParams['LANDING_ID']
				&& in_array($code, self::PAGES_FOR_LANDING, true)
			)
			{
				continue;
			}

			$isAreaPage = TemplateRef::landingIsArea($this->arParams['LANDING_ID']);
			if ($isAreaPage && $code === self::PAGE_LANDING_DESIGN)
			{
				continue;
			}

			$pageData = [
				'page' => $code,
				'name' => $this->getMessageType('LANDING_SITE_SETTINGS_' . $code),
			];

			$link = str_replace(
				['#site_edit#', '#site_show#', '#landing_edit#'],
				[$this->arParams['SITE_ID'], $this->arParams['SITE_ID'], $this->arParams['LANDING_ID']],
				$this->arParams['PAGES'][$pageCode]
			);
			$uri = new Uri($link);
			$uri->addParams([
				'IFRAME' => 'Y',
			]);
			if ($code === self::PAGE_CATALOG_EDIT && $this->arParams['TYPE'] === 'STORE')
			{
				$uri->addParams(['tpl' => 'catalog']);
			}
			$pageData['link'] = $uri->getUri();

			$uri->addParams(['action' => 'save']);
			$uri->addParams(['actionType' => 'json']);
			$pageData['linkToSave'] = $uri->getUri();

			$this->arResult['ITEMS'][$code] = $pageData;
		}

		$this->addPlacementsItems();
		$this->checkItems();

		// check active page
		$request = Application::getInstance()->getContext()->getRequest();
		$currentPage = $request->get('PAGE');
		if ($currentPage && isset($this->arResult['ITEMS'][$currentPage]))
		{
			$this->arResult['ITEMS'][$currentPage]['current'] = true;
		}
		else
		{
			$this->arResult['ITEMS'][array_keys($this->arResult['ITEMS'])[0]]['current'] = true;
		}

		parent::executeComponent();
	}

	protected function addPlacementsItems(): void
	{
		if (Loader::includeModule('rest'))
		{
			$res = PlacementTable::getList([
				'select' => [
					'ID', 'APP_ID', 'PLACEMENT', 'TITLE',
					'APP_NAME' => 'REST_APP.APP_NAME',
				],
				'filter' => [
					'=PLACEMENT' => 'LANDING_SETTINGS',
				],
				'order' => [
					'ID' => 'DESC',
				],
			]);
			while ($row = $res->fetch())
			{
				$this->arResult['ITEMS']['placement_' . $row['ID']] = [
					'name' => $row['TITLE'],
					'placementId' => $row['ID'],
					'appId' => $row['APP_ID'],
					'placement' => $row['PLACEMENT'],
				];
			}
		}
	}

	/**
	 * Prepare items list by perms and other conditions
	 * @return void
	 */
	protected function checkItems(): void
	{
		if (!Rights::hasAccessForSite($this->arParams['SITE_ID'], Rights::ACCESS_TYPES['sett']))
		{
			$this->addError('LANDING_ERROR_SETTINGS_ACCESS_DENIED_MSGVER_1', '', true);

			foreach (self::AVAILABLE_PAGES as $code)
			{
				unset(
					$this->arResult['ITEMS'][$code]['link'],
					$this->arResult['ITEMS'][$code]['linkToSave'],
					$this->arResult['ITEMS'][$code]['page']
				);
			}
		}

		// check is form editor
		$landing = \Bitrix\Landing\Landing::createInstance($this->arParams['LANDING_ID']);
		if (
			$landing->exist()
			&& $this->getSpecialTypeSiteByLanding($landing) === 'crm_forms'
		)
		{
			unset($this->arResult['ITEMS'][self::PAGE_SITE_DESIGN]);
		}
	}
}
