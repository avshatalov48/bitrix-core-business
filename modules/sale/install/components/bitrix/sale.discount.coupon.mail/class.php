<?php
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\SystemException as SystemException;
use \Bitrix\Main\Loader as Loader;
use \Bitrix\Sale\Internals\DiscountCouponTable;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CSaleDiscountCouponMailComponent extends CBitrixComponent
{
	/**
	 * @param $params
	 * @override
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params["CACHE_TIME"] = 0;
		$params["DETAIL_URL"] = trim($params["DETAIL_URL"]);

		if(Loader::includeModule("sale"))
		{

		}

		return $params;
	}

	/**
	 * @override
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if(!Loader::includeModule("sale"))
			throw new SystemException(Loc::getMessage("CVP_SALE_MODULE_NOT_INSTALLED"));
		if(!Loader::includeModule("catalog"))
			throw new SystemException(Loc::getMessage("CVP_CATALOG_MODULE_NOT_INSTALLED"));
	}

	/**
	 * @override
	 * @throws Exception
	 */
	protected function prepareData()
	{
		$saleDiscountId = null;
		$wasAdded = false;
		$xmlId = $this->arParams['DISCOUNT_XML_ID'];
		$saleDiscountValue = (float) $this->arParams['DISCOUNT_VALUE'];
		$saleDiscountUnit = (string) $this->arParams['DISCOUNT_UNIT'];
		$siteId = $this->getSiteId();
		if (strlen($xmlId) <= 0 && $saleDiscountValue > 0 && strlen($saleDiscountUnit) > 0)
			$xmlId = "generatedCouponMail_".$saleDiscountValue."_".$saleDiscountUnit;
		$fieldsAdd = array(
			'LID' => $siteId ? $siteId : CSite::GetDefSite(),
			'NAME' => Loc::getMessage("CVP_DISCOUNT_NAME"),
			'ACTIVE' => 'Y',
			'ACTIVE_FROM' => '',
			'ACTIVE_TO' => '',
			'PRIORITY' => 1,
			'SORT' => 100,
			'LAST_DISCOUNT' => 'Y',
			'XML_ID' => $xmlId,
			'USER_GROUPS' => array(2),
			'ACTIONS' => serialize(Array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => Array
				(
					'All' => 'AND'
				),
				'CHILDREN' => Array(
					Array(
						'CLASS_ID' => 'ActSaleBsktGrp',
						'DATA' => Array(
							'Type' => 'Discount',
							'Value' => $saleDiscountValue,
							'Unit' => $saleDiscountUnit,
							'All' => 'AND',
						),
						'CHILDREN' => Array()
					)
				)
			)),
			'CONDITIONS' => serialize(Array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => Array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => Array()
			))
		);

		if(strlen($xmlId) <= 0)
		{
			return;
		}

		$fields = array(
			'XML_ID' => $xmlId,
			'ACTIVE' => 'Y'
		);
		$saleDiscountDb = CSaleDiscount::GetList(array('DATE_CREATE' => 'DESC'), $fields, false, false, array('ID', 'ACTIONS', 'CONDITIONS'));
		if($saleDiscount = $saleDiscountDb->Fetch())
		{
			if($saleDiscount['ACTIONS'] == $fieldsAdd['ACTIONS'] && $saleDiscount['CONDITIONS'] == $fieldsAdd['CONDITIONS'])
			{
				$saleDiscountId = $saleDiscount['ID'];
			}
		}

		if(!$saleDiscountId)
		{
			$fieldsAdd['ACTIVE'] = 'N';
			$saleDiscountId = CSaleDiscount::Add($fieldsAdd);
			$wasAdded = true;
		}

		$type = ($this->arParams['COUPON_TYPE'] === 'Basket') ? DiscountCouponTable::TYPE_BASKET_ROW : DiscountCouponTable::TYPE_ONE_ORDER;
		$this->arResult['COUPON'] = '';
		if($saleDiscountId)
		{
			$coupon = DiscountCouponTable::generateCoupon(true);
			//$activeFrom = new \Bitrix\Main\Type\DateTime;
			//$activeTo = clone $activeFrom;
			$addDb = DiscountCouponTable::add(array(
				'DISCOUNT_ID' => $saleDiscountId,
				//'ACTIVE_FROM' => $activeFrom,
				//'ACTIVE_TO' => $activeTo->add('+365 days'),
				'COUPON' => $coupon,
				'TYPE' => $type,
				'MAX_USE' => 1,
				'USER_ID' => 0,
				'DESCRIPTION' => $this->arParams['COUPON_DESCRIPTION'],
			));
			if($addDb->isSuccess())
			{
				$this->arResult['COUPON'] = $coupon;
				if($wasAdded)
				{
					CSaleDiscount::Update($saleDiscountId, array('ACTIVE' => 'Y'));
				}
			}
		}
	}

	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->prepareData();
			$this->includeComponentTemplate();
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
	}
}