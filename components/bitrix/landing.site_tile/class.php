<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Domain;
use Bitrix\Landing\Connector;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Restriction;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteTileComponent extends LandingBaseComponent
{
	/**
	 * Domain available statuses.
	 */
	private const DOMAIN_STATUS = [
		'success' => 'success',// everything all rights
		'alert' => 'alert',// something need attention
		'danger' => 'danger',// something need super attention
		'unknown' => 'unknown',// other status
		'clock' => 'clock'// wait activation
	];

	/**
	 * Returns site's phone by site id.
	 * @param int $siteId Site id.
	 * @return string|null
	 */
	protected function getSitePhone(int $siteId): ?string
	{
		return Connector\Crm::getContacts(
			$siteId
		)['PHONE'] ?? null;
	}

	/**
	 * Returns order's counts for each site has created orders.
	 * @param int[] $siteIds Site id.
	 * @return int[]
	 */
	protected function getSiteOrdersCount(array $siteIds): array
	{
		$return = array_fill_keys($siteIds, 0);

		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			$filter = ['=TRADING_PLATFORM.CODE' => []];
			foreach ($siteIds as $siteId)
			{
				$filter['=TRADING_PLATFORM.CODE'][] = 'landing_' . $siteId;
			}
			$res = \Bitrix\Crm\Order\TradeBindingCollection::getList([
				'select' => [
					'CNT', 'TRADING_PLATFORM_CODE' => 'TRADING_PLATFORM.CODE'
				],
				'filter' => $filter,
				'group' => 'TRADING_PLATFORM.CODE',
				'runtime' => array(
					new \Bitrix\Main\Entity\ExpressionField(
						'CNT', 'COUNT(*)'
					)
				)
			]);
			while ($row = $res->fetch())
			{
				if ($row['TRADING_PLATFORM_CODE'])
				{
					[, $siteId] = explode('_', $row['TRADING_PLATFORM_CODE']);
					$return[$siteId] = $row['CNT'];
				}
			}
		}

		return $return;
	}

	/**
	 * Replaces link's template with real data.
	 * @param string $path Link's template.
	 * @param array $item Data item.
	 * @return string
	 */
	protected function replaceLink(string $path, array $item = []): string
	{
		return str_replace(
			['#site_show#', '#site_edit#', '#landing_edit#'],
			[$item['ID'], $item['ID'], 0],
			$path
		);
	}

	/**
	 * Prepares links for use in sidepanel.
	 * @param array $sidepanel Links array.
	 * @return array
	 */
	protected function prepareSideLink(array $sidepanel): array
	{
		if (empty($sidepanel))
		{
			return $sidepanel;
		}

		$sidepanel = array_unique($sidepanel);
		foreach ($sidepanel as &$path)
		{
			$path = str_replace('/?', '/\?', $path);
			$path = preg_replace('/(#[a-z_]+#)/', '(\d+)', $path);
		}
		return array_values(array_unique($sidepanel));
	}

	/**
	 * Prepares item for transfer to js.
	 * @param array $items Item's array.
	 * @param array $menuItems Menu item's array.
	 * @param array &$sidepanel Link's templates for sidepanel.
	 * @param array &$sidepanelShort Link's templates for sidepanel (short format).
	 * @return array
	 */
	protected function prepareItems(array $items, array $menuItems, array &$sidepanel = [], array &$sidepanelShort = []): array
	{
		$newItems = [];
		$menuItemsOrig = $menuItems;
		$orderCounts = $this->getSiteOrdersCount(array_keys($items));

		if (!$items)
		{
			foreach ($menuItems as &$menuItem)
			{
				if ($menuItem['sidepanel'] ?? false)
				{
					$sidepanel[] = $menuItem['href'];
				}
				if ($menuItem['shortsidepanel'] ?? false)
				{
					$sidepanelShort[] = $menuItem['href'];
				}
			}
			$sidepanel = $this->prepareSideLink($sidepanel);
			$sidepanelShort = $this->prepareSideLink($sidepanelShort);
		}

		foreach ($items as $item)
		{
			if (!($item['ID'] ?? null) || !($item['TITLE'] ?? null))
			{
				continue;
			}

			$item['ACTIVE'] = $item['ACTIVE'] ?? null;
			$item['PREVIEW'] = $item['PREVIEW'] ?? null;
			$item['DOMAIN_NAME'] = $item['DOMAIN_NAME'] ?? null;
			$item['PUBLIC_URL'] = $item['PUBLIC_URL'] ?? null;

			// can delete?
			$item['ACCESS_DELETE'] = 'Y';
			if (is_array($this->arParams['DELETE_LOCKED']) && in_array($item['ID'], $this->arParams['DELETE_LOCKED']))
			{
				$item['ACCESS_DELETE'] = 'N';
			}

			$published = $item['ACTIVE'] === 'Y' && $item['DELETED'] === 'N';
			$deleted = $item['DELETED'] === 'Y';

			// check paths for sidepanel
			$menuBottomItems = [];
			$menuItems = $menuItemsOrig;
			foreach ($menuItems as $i => &$menuItem)
			{
				if ($menuItem['sidepanel'] ?? false)
				{
					$sidepanel[] = $menuItem['href'];
				}
				if ($menuItem['shortsidepanel'] ?? false)
				{
					$sidepanelShort[] = $menuItem['href'];
				}
				if (isset($menuItem['href']))
				{
					$menuItem['href'] = $this->replaceLink($menuItem['href'], $item);
				}
				if ($menuItem['bottom'] ?? false)
				{
					$menuBottomItems[] = $menuItem;
					unset($menuItems[$i]);
				}
				if (!($menuItem['text'] ?? null) && !isset($menuItem['delimiter']))
				{
					unset($menuItems[$i]);
				}
			}
			unset($menuItem);
			$sidepanel = $this->prepareSideLink($sidepanel);
			$sidepanelShort = $this->prepareSideLink($sidepanelShort);

			// domain status
			$domainStatusMessage = null;
			$domainStatus = $this::DOMAIN_STATUS['unknown'];
			if ($item['DOMAIN_PROVIDER'] ?? null)
			{
				$tariffTtl = Restriction\Site::getFreeDomainSuspendedTime();
				if ($tariffTtl)
				{
					if ($tariffTtl <= time())
					{
						$domainStatus = $this::DOMAIN_STATUS['danger'];
						$domainStatusMessage = Loc::getMessage('LANDING_CMP_DOMAIN_NEED_PAY');
					}
					else
					{
						$domainStatus = $this::DOMAIN_STATUS['alert'];
						$domainStatusMessage = Loc::getMessage('LANDING_CMP_DOMAIN_NEED_PAY_SOON_UNTIL', [
							'#DATE#' => Date::createFromTimestamp($tariffTtl)
						]);
					}
				}
				if (!$domainStatusMessage)
				{
					if (Domain\Register::isDomainActive($item['DOMAIN_NAME']))
					{
						$domainStatus = $this::DOMAIN_STATUS['success'];
					}
					else
					{
						$domainStatus = $this::DOMAIN_STATUS['clock'];
						$domainStatusMessage = Loc::getMessage('LANDING_CMP_DOMAIN_WAIT_ACTIVATION');
					}
				}
			}
			else
			{
				if (!$item['DOMAIN_PREV'])
				{
					$domainStatus = $this::DOMAIN_STATUS['alert'];
					$domainStatusMessage = Loc::getMessage('LANDING_CMP_DOMAIN_CREATE_DOMAIN_NAME');
				}
				else if ($published)
				{
					$domainStatus = $this::DOMAIN_STATUS['success'];
				}
			}

			$newItems[] = [
				'id' => $item['ID'],
				'title' => $item['TITLE'],
				'url' => $item['DOMAIN_NAME'] ?: ' ',
				'phone' => $this->getSitePhone($item['ID']),
				'ordersCount' => $orderCounts[$item['ID']],
				'preview' => $item['PREVIEW'] ?: '',
				'published' => $published,
				'deleted' => $deleted,
				'domainStatus' => $domainStatus,
				'domainStatusMessage' => $domainStatusMessage,
				'fullUrl' => $item['PUBLIC_URL'] ?: '',
				'domainProvider' => $item['DOMAIN_PROVIDER'],
				'domainUrl' => $this->replaceLink($this->arParams['PAGE_URL_DOMAIN'], $item),
				'contactsUrl' => $this->replaceLink($this->arParams['PAGE_URL_CONTACTS'], $item),
				'pagesUrl' => $this->replaceLink($this->arParams['PAGE_URL_SITE'], $item),
				'ordersUrl' => $this->replaceLink($this->arParams['PAGE_URL_CRM_ORDERS'], $item),
				'menuItems' => array_values($menuItems),
				'menuBottomItems' => $menuBottomItems,
				'access' => [
					'edit' => $item['ACCESS_EDIT'] === 'Y',
					'settings' => $item['ACCESS_SETTINGS'] === 'Y',
					'publication' => $item['ACCESS_PUBLICATION'] === 'Y',
					'delete' => $item['ACCESS_DELETE'] === 'Y',
					'site_new' => $item['ACCESS_SITE_NEW'] === 'Y'
				]
			];
		}

		return $newItems;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TYPE', '');
		$this->checkParam('FEEDBACK_CODE', '');
		$this->checkParam('PAGE_URL_SITE_ADD', '');
		$this->checkParam('PAGE_URL_SITE', '');
		$this->checkParam('PAGE_URL_DOMAIN', '');
		$this->checkParam('PAGE_URL_SITE_DOMAIN_SWITCH', '');
		$this->checkParam('PAGE_URL_CRM_ORDERS', '');
		$this->checkParam('ITEMS', []);
		$this->checkParam('MENU_ITEMS', []);
		$this->checkParam('~AGREEMENT', []);
		$this->checkParam('DELETE_LOCKED', []);

		if (Manager::isB24())
		{
			$this->arResult['AGREEMENT'] = $this->arParams['~AGREEMENT'];
		}
		else
		{
			$this->arResult['AGREEMENT'] = [];
		}

		$this->arResult['SIDE_PANEL'] = [];
		$this->arResult['SIDE_PANEL_SHORT'] = [];
		$this->arParams['ITEMS'] = $this->prepareItems(
			$this->arParams['ITEMS'],
			$this->arParams['MENU_ITEMS'],
			$this->arResult['SIDE_PANEL'],
			$this->arResult['SIDE_PANEL_SHORT']
		);

		parent::executeComponent();
	}
}
