<?php

namespace Bitrix\Seo\LeadAds;

use Bitrix\Main\ArgumentNullException;

/**
 * Class Mapper.
 * Form fields Mapper from crm to ads, ads to crm.
 *
 * @package Bitrix\Seo\LeadAds
 */
class Mapper
{
	protected $map = [];

	/**
	 * @throws ArgumentNullException
	 */
	public function __construct(array $items = [])
	{
		$this->setItems($items);
	}

	/**
	 * Get crm name.
	 *
	 * @param string $adsName Ads name.
	 *
	 * @return string|null
	 */
	public function getCrmName($adsName)
	{
		$item = $this->getMapItem(null, $adsName);
		return empty($item) ? null : $item['CRM_NAME'];
	}

	/**
	 * Get ads name.
	 *
	 * @param string $crmName Crm name.
	 *
	 * @return string|null
	 */
	public function getAdsName($crmName)
	{
		$item = $this->getMapItem($crmName, null);
		return empty($item) ? null : $item['ADS_NAME'];
	}

	/**
	 * Set map items.
	 *
	 * @param array $items Map items.
	 * @return $this
	 * @throws ArgumentNullException
	 */
	public function setItems(array $items = [])
	{
		$this->map = [];
		foreach ($items as $item)
		{
			if (empty($item['CRM_NAME']))
			{
				throw new ArgumentNullException('CRM_NAME');
			}
			if (empty($item['ADS_NAME']))
			{
				throw new ArgumentNullException('ADS_NAME');
			}

			$this->addItem($item['CRM_NAME'], $item['ADS_NAME']);
		}

		return $this;
	}

	/**
	 * Add map item
	 *
	 * @param string $crmName Crm name.
	 * @param string $adsName Ads name.
	 *
	 * @return $this
	 * @throws ArgumentNullException
	 */
	public function addItem($crmName, string $adsName)
	{
		if (empty($crmName))
		{
			throw new ArgumentNullException('$crmName');
		}
		if (empty($adsName))
		{
			throw new ArgumentNullException('$adsName');
		}

		$this->map[] = [
			'CRM_NAME' => $crmName,
			'ADS_NAME' => $adsName,
		];

		return $this;
	}

	protected function getMapItem($crmName = null, $adsName = null)
	{
		if (empty($crmName) && empty($adsName))
		{
			return null;
		}

		foreach ($this->map as $item)
		{
			if ($crmName && $item['CRM_NAME'] === $crmName)
			{
				return $item;
			}

			if ($adsName && $item['ADS_NAME'] === $adsName)
			{
				return $item;
			}
		}

		return null;
	}
}