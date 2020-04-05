<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use	Bitrix\Sale;

CBitrixComponent::includeComponentClass("bitrix:sale.basket.basket");

class CBitrixBasketBasketSmallMailComponent extends CBitrixBasketComponent
{
	protected $userId;

	public function onPrepareComponentParams($params)
	{
		$columnList = $params['COLUMNS_LIST'];
		$params = parent::onPrepareComponentParams($params);
		$this->columns = $columnList;

		$this->userId = (int)$params["USER_ID"];

		if ((int)($params["FUSER_ID"]) > 0)
		{
			$this->fUserId = (int)$params["FUSER_ID"];
		}
		else
		{
			$this->fUserId = Sale\Fuser::getIdByUserId($this->userId);
		}

		$params['COMPATIBLE_MODE'] = 'Y';

		if (!$this->getSiteId())
		{
			$siteId = isset($params["LID"]) ? $params["LID"] : \CSite::GetDefSite();
			$this->setSiteId($siteId);
		}

		return $params;
	}

	protected function sortItemsByTabs(&$result)
	{
		$result['ITEMS'] = array(
			'AnDelCanBuy' => array(),
			'DelDelCanBuy' => array(),
			'nAnCanBuy' => array(),
			'ProdSubscribe' => array()
		);

		if (!empty($this->basketItems))
		{
			foreach ($this->basketItems as $item)
			{
				if ($item['CAN_BUY'] === 'Y' && $item['DELAY'] !== 'Y')
				{
					$result['ITEMS']['AnDelCanBuy'][] = $item;
				}
				elseif ($this->arParams['SHOW_DELAY'] === 'Y' && $item['CAN_BUY'] === 'Y' && $item['DELAY'] === 'Y')
				{
					$result['ITEMS']['DelDelCanBuy'][] = $item;
				}
				elseif ($this->arParams['SHOW_SUBSCRIBE'] === 'Y' && $item['CAN_BUY'] !== 'Y' && $item['SUBSCRIBE'] === 'Y')
				{
					$result['ITEMS']['ProdSubscribe'][] = $item;
				}
				elseif ($this->arParams['SHOW_NOTAVAIL'] === 'Y')
				{
					$result['ITEMS']['nAnCanBuy'][] = $item;
				}
			}
		}

		$result['ShowReady'] = !empty($result['ITEMS']['AnDelCanBuy']) ? 'Y' : 'N';
		$result['ShowDelay'] = !empty($result['ITEMS']['DelDelCanBuy']) ? 'Y' : 'N';
		$result['ShowNotAvail'] = !empty($result['ITEMS']['nAnCanBuy']) ? 'Y' : 'N';
		$result['ShowSubscribe'] = !empty($result['ITEMS']['ProdSubscribe']) ? 'Y' : 'N';
	}
}
?>