<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Block;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;

class ThreeColsFixTariffsBlock extends \Bitrix\Landing\LandingBlock
{
	/**
	 * Url to get Bitrix24 prices.
	 */
	const B24_PRICE_URL = 'https://www.1c-bitrix.ru/buy_tmp/b24_catalog.php?currency=RUR&area=ru';

	/**
	 * Available Bitrix24 tariffs.
	 */
	const B24_TARIFF_CODES = [
		'START_20191', 'CRM_20191', 'CRM_20191', 'TEAM_20191', 'COMPANY1'
	];

	/**
	 * Returns actual Bitrix24 prices.
	 * @return array
	 */
	protected function getPrices(): array
	{
		$data = [];

		$http = new HttpClient;
		$res = $http->get($this::B24_PRICE_URL);
		if ($res)
		{
			try
			{
				$res = Json::decode($res);
				foreach ($this::B24_TARIFF_CODES as $code)
				{
					if (isset($res[$code]['PRICE']))
					{
						$data[] = $res[$code]['PRICE'];
					}
					else
					{
						return [];
					}
				}
			}
			catch (\Exception $e){}
		}

		return $data;
	}

	/**
	 * Method, which executes just before block to view.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public function beforeView(Block $block)
	{
		return;

		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return;
		}
		if (\CBitrix24::getLicenseType() != 'nfr')
		{
			return;
		}

		$blockAccess = $block->getAccess();
		if ($blockAccess >= $block::ACCESS_W)
		{
			return;
		}

		$prices = $this->getPrices();
		if (!$prices)
		{
			return;
		}

		$content = $block->getContent();
		$content = preg_replace_callback(
			'#<div class="landing-block-node-price[\s"]+[^"]*"><span[^>]+>([^<]+)</span></div>#is',
			function($item) use($prices)
			{
				static $i = 0;
				return str_replace(
					$item[1],
					$prices[$i++],
					$item[0]
				);
			},
			$content
		);
		$block->setAccess($block::ACCESS_W);
		$block->saveContent($content);
		$block->setAccess($blockAccess);
	}
}