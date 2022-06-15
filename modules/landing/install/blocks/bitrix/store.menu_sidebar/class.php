<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Main\Loader;
use Bitrix\Crm;
use Bitrix\Catalog;

class StoreMenuSidebar extends \Bitrix\Landing\LandingBlock
{
	protected $catalogIncluded;
	protected $crmIncluded;

	protected $userAuthorized = false;

	protected $userId;

	protected $userName = '';

	/**
	 * Method, which will be called once time.
	 * @param array Params array.
	 * @return void
	 */
	public function init(array $params = [])
	{
		$this->catalogIncluded = Loader::includeModule('catalog');
		$this->crmIncluded = Loader::includeModule('crm');
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);
		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		$this->initCurrentUser();
	}

	/**
	 *  Method, which executes just before block.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @return void
	 */
	public function beforeView(\Bitrix\Landing\Block $block): void
	{
		$this->params['ADDITIONAL_COUNT_ELEMENTS_FILTER'] = 'additionalCountFilter';
		$this->params['HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS'] = 'Y';

		$this->setElementListFilter();
	}

	public function getUserInformation(): ?array
	{
		if (!$this->userAuthorized)
		{
			return null;
		}

		return [
			'USER_NAME' => $this->userName,
			'LAST_ORDER_URL' => $this->getLastOrderUrl()
		];
	}

	protected function initCurrentUser(): void
	{
		global $USER;

		if (isset($USER) && $USER instanceof \CUser && $USER->IsAuthorized())
		{
			$this->userAuthorized = true;
			$this->userId = (int)$USER->GetID();
			$this->userName = $USER->GetFormattedName();
		}
	}

	protected function getLastOrderUrl(): string
	{
		$orderId = $this->getLastOrderId();
		if ($orderId === null)
		{
			return '#system_order';
		}
		return '';
	}

	protected function getLastOrderId(): ?int
	{
		$result = null;
		if ($this->crmIncluded && $this->userAuthorized)
		{
			$iterator = Crm\Order\TradeBindingCollection::getList([
				'select' => [
					'ORDER_ID'
				],
				'filter' => [
					'=TRADING_PLATFORM.CODE' => 'landing_'.$this->params['SITE_ID'],
					'=ORDER.USER_ID' => $this->userId
				],
				'order' => ['ORDER_ID' => 'DESC'],
				'limit' => 1
			]);
			$row = $iterator->fetch();
			if (!empty($row))
			{
				$result = (int)$row['ORDER_ID'];
			}
		}

		return $result;
	}

	private function setFilter(string $name, array $filter): void
	{
		$currentFilter = $GLOBALS[$name] ?? [];
		if (!is_array($currentFilter))
		{
			$currentFilter = [];
		}

		$GLOBALS[$name] = array_merge(
			$currentFilter,
			$filter
		);
	}

	private function setElementListFilter(): void
	{
		$filterName = $this->get('ADDITIONAL_COUNT_ELEMENTS_FILTER');
		if ($filterName === null || $filterName === '')
		{
			return;
		}

		$listFilter = [];

		if ($this->catalogIncluded)
		{
			if (class_exists('\Bitrix\Catalog\Product\SystemField\ProductMapping'))
			{
				$listFilter = Catalog\Product\SystemField\ProductMapping::getExtendedFilterByArea(
					$listFilter,
					Catalog\Product\SystemField\ProductMapping::MAP_LANDING
				);
			}
		}

		if (!empty($listFilter))
		{
			$this->setFilter($filterName, $listFilter);
		}
	}
}
