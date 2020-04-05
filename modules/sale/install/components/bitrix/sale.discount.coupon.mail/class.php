<?php
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\SystemException as SystemException;
use \Bitrix\Main\Loader as Loader;
use \Bitrix\Sale\Internals\DiscountCouponTable;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CSaleDiscountCouponMailComponent extends CBitrixComponent
{
	const DAY_LIMIT_TYPE = 'days';
	const WEEK_LIMIT_TYPE = 'weeks';
	const MONTH_LIMIT_TYPE = 'months';
	protected $isNewDiscount = false;
	/**
	 * @param $params
	 * @override
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params["CACHE_TIME"] = 0;
		$params["DETAIL_URL"] = trim($params["DETAIL_URL"]);

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
	 * @param $type
	 *
	 * @return bool
	 */
	protected function checkTypeLimit($type)
	{
		return in_array($type, [
			self::DAY_LIMIT_TYPE,
			self::WEEK_LIMIT_TYPE,
			self::MONTH_LIMIT_TYPE,
		]);
	}

	/**
	 * @override
	 * @throws Exception
	 */
	protected function prepareData()
	{
		if ($this->arParams['USE_DISCOUNT_ID'] === 'Y')
		{
			$saleDiscountId = (int)$this->arParams['DISCOUNT_ID'];
		}
		else
		{
			$saleDiscountId = $this->getDiscountId();
		}

		$type = ($this->arParams['COUPON_TYPE'] === 'Basket') ? DiscountCouponTable::TYPE_BASKET_ROW : DiscountCouponTable::TYPE_ONE_ORDER;
		$this->arResult['COUPON'] = '';
		if ($saleDiscountId)
		{
			$coupon = DiscountCouponTable::generateCoupon(true);
			$addFields = [
				'DISCOUNT_ID' => $saleDiscountId,
				'COUPON' => $coupon,
				'TYPE' => $type,
				'MAX_USE' => 1,
				'USER_ID' => 0,
				'DESCRIPTION' => $this->arParams['COUPON_DESCRIPTION'],
			];

			if ($this->arParams['COUPON_IS_LIMITED'] === 'Y'
				&& (int)$this->arParams['COUPON_LIMIT_VALUE'] >= 0
				&& $this->checkTypeLimit($this->arParams['COUPON_LIMIT_TYPE'])
			)
			{
				$today = new \Bitrix\Main\Type\DateTime;
				$addFields['ACTIVE_FROM'] = clone($today);
				$addFields['ACTIVE_TO'] = $today->add((int)$this->arParams['COUPON_LIMIT_VALUE']." ".$this->arParams['COUPON_LIMIT_TYPE']);
			}

			$addDb = DiscountCouponTable::add($addFields);
			if ($addDb->isSuccess())
			{
				$this->arResult['COUPON'] = $coupon;
				if ($this->isNewDiscount)
				{
					CSaleDiscount::Update($saleDiscountId, array('ACTIVE' => 'Y'));
				}
			}
		}
	}

	/**
	 * @return int
	 */
	protected function getDiscountId()
	{
		$discountId = null;
		$xmlId = $this->arParams['DISCOUNT_XML_ID'];
		$saleDiscountValue = (float) $this->arParams['DISCOUNT_VALUE'];
		$saleDiscountUnit = (string) $this->arParams['DISCOUNT_UNIT'];
		$siteId = $this->getSiteId();
		if (strlen($xmlId) <= 0 && $saleDiscountValue > 0 && strlen($saleDiscountUnit) > 0)
		{
			$xmlId = "generatedCouponMail_".$saleDiscountValue."_".$saleDiscountUnit;
		}

		$fieldsAdd = [
			'LID' => $siteId ? $siteId : CSite::GetDefSite(),
			'NAME' => Loc::getMessage("CVP_DISCOUNT_NAME"),
			'ACTIVE' => 'Y',
			'ACTIVE_FROM' => '',
			'ACTIVE_TO' => '',
			'PRIORITY' => 1,
			'SORT' => 100,
			'LAST_DISCOUNT' => 'Y',
			'XML_ID' => $xmlId,
			'USER_GROUPS' => [2],
			'ACTIONS' => [
				'CLASS_ID' => 'CondGroup',
				'DATA' => [ 'All' => 'AND' ],
				'CHILDREN' => [
					[
						'CLASS_ID' => 'ActSaleBsktGrp',
						'DATA' => [
							'Type' => 'Discount',
							'Value' => $saleDiscountValue,
							'Unit' => $saleDiscountUnit,
							'All' => 'AND',
							'Max' => '0',
							'True' => 'True'
						],
						'CHILDREN' => []
					]
				]
			],
			'CONDITIONS' => [
				'CLASS_ID' => 'CondGroup',
				'DATA' => [
					'All' => 'AND',
					'True' => 'True',
				],
				'CHILDREN' => []
			]
		];

		if(strlen($xmlId) <= 0)
		{
			return null;
		}

		$fields = [
			'XML_ID' => $xmlId,
			'ACTIVE' => 'Y'
		];
		$saleDiscountData = \Bitrix\Sale\Internals\DiscountTable::getList([
			'filter' => $fields,
			'select' => ['ID', 'ACTIONS', 'CONDITIONS']
		]);
		$serializedAction = serialize($fieldsAdd['ACTIONS']);
		$serializedCondition = serialize($fieldsAdd['CONDITIONS']);
		while ($saleDiscount = $saleDiscountData->fetch())
		{
			if($saleDiscount['ACTIONS'] == $serializedAction && $saleDiscount['CONDITIONS'] == $serializedCondition)
			{
				$discountId = $saleDiscount['ID'];
			}
		}

		if (!$discountId)
		{
			$fieldsAdd['ACTIVE'] = 'N';
			$discountId = CSaleDiscount::Add($fieldsAdd);
			$this->isNewDiscount = true;
		}

		return $discountId;
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