<?php
use Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

class CatalogProductsSubscribeListComponent extends \CCatalogViewedProductsComponent
{
	const ACTION_SUBSCRIBER_IDENTIFICATION = 'subscriberIdentification';
	const ACTION_ACCESS_CODE_VERIFICATION = 'accessCodeVerification';
	const ACTION_UNSUBSCRIBE = 'unSubscribe';

	/**
	 * @var integer
	 */
	protected $userId = 0;

	/**
	 * List of product ids which will be showed.
	 * @var array
	 */
	protected $listProductId = array();
	protected $codeList = array();

	/**
	 * Event called from includeComponent before component execution.
	 *
	 * <p>Takes component parameters as argument and should return it formatted as needed.</p>
	 * @param array[string]mixed $arParams
	 * @return array[string]mixed
	 *
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if(!isset($params['LINE_ELEMENT_COUNT']))
			$params['LINE_ELEMENT_COUNT'] = 3;
		$params['LINE_ELEMENT_COUNT'] = intval($params['LINE_ELEMENT_COUNT']);
		if($params['LINE_ELEMENT_COUNT'] < 2 || $params['LINE_ELEMENT_COUNT'] > 5)
			$params['LINE_ELEMENT_COUNT'] = 3;

		if(Main\Loader::includeModule('catalog'))
		{
			global $USER, $DB;
			if(is_object($USER) && $USER->isAuthorized())
				$this->userId = $USER->getId();

			$filter = array(
				'=SITE_ID' => SITE_ID,
				array(
					'LOGIC' => 'OR',
					array('=DATE_TO' => false),
					array('>DATE_TO' => date($DB->dateFormatToPHP(\CLang::getDateFormat('FULL')), time()))
				)
			);
			if($this->userId)
			{
				$filter['USER_ID'] = $this->userId;
				$params['GUEST_ACCESS'] = true;
			}
			else
			{
				if(!empty($_SESSION['SUBSCRIBE_PRODUCT']['TOKEN']) && !empty($_SESSION['SUBSCRIBE_PRODUCT']['USER_CONTACT']))
				{
					$filter['=Bitrix\Catalog\SubscribeAccessTable:SUBSCRIBE.TOKEN'] =
						$_SESSION['SUBSCRIBE_PRODUCT']['TOKEN'];
					$filter['=Bitrix\Catalog\SubscribeAccessTable:SUBSCRIBE.USER_CONTACT'] =
						$_SESSION['SUBSCRIBE_PRODUCT']['USER_CONTACT'];

					$params['GUEST_ACCESS'] = true;
				}
				else
				{
					return $params;
				}
			}

			$resultObject = Catalog\SubscribeTable::getList(
				array(
					'select' => array(
						'ID',
						'ITEM_ID',
						'TYPE' => 'PRODUCT.TYPE',
						'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
					),
					'filter' => $filter,
				)
			);
			$listIblockId = array();
			while($item = $resultObject->fetch())
			{
				$params['SHOW_PRODUCTS'][$item['IBLOCK_ID']] = true;
				$params['LIST_SUBSCRIPTIONS'][$item['ITEM_ID']][] = $item['ID'];

				$listIblockId[$item['ITEM_ID']] = $item['IBLOCK_ID'];
			}

			$params['NEED_VALUES'] = array();
			$listSubscribeItemId = array();
			foreach($listIblockId as $itemId => $iblockId)
			{
				$sku = CCatalogSKU::getInfoByProductIBlock($iblockId);
				if(!empty($sku) && is_array($sku))
				{
					$this->prepareItemData($itemId, $sku, $params);
					$this->listProductId[] = $itemId;
					$listSubscribeItemId[] = $itemId;
				}
				else
				{
					$parent = CCatalogSKU::getProductList($itemId);
					if(!empty($parent))
					{
						$parentItemId = $parent[$itemId]['ID'];
						$parentIblockId = $parent[$itemId]['IBLOCK_ID'];
					}
					else
					{
						$parentItemId = $itemId;
						$parentIblockId = $iblockId;
					}
					$offerSku = CCatalogSKU::getInfoByOfferIBlock($iblockId);
					if(!empty($offerSku) && is_array($offerSku))
					{
						$this->prepareItemData($parentItemId, $offerSku, $params, $itemId);
						$params['SHOW_PRODUCTS'][$parentIblockId] = true;
					}
					if(!in_array($parentItemId, $this->listProductId))
						$this->listProductId[] = $parentItemId;
					$listSubscribeItemId[] = $itemId;
				}
			}

			if(!empty($listSubscribeItemId))
			{
				$subscribeManager = new Catalog\Product\SubscribeManager;
				foreach($listSubscribeItemId as $itemId)
					$subscribeManager->setSessionOfSibscribedProducts($itemId);
			}
			if(!empty($this->codeList))
			{
				foreach($this->codeList as $iblockId => $code)
					$params['PROPERTY_CODE'][$iblockId] = $code;
			}
		}

		return $params;
	}

	protected function prepareData()
	{
		parent::prepareData();
	}

	/**
	 * Returns list of product ids which will be showed.
	 *
	 * @return array
	 */
	protected function getProductIds()
	{
		return $this->listProductId;
	}

	protected function prepareItemData($itemId, array $sku, &$params, $offerId = 0)
	{
		$offersTreeProps = array();
		$propertyValue = array();
		if (!array_key_exists($itemId, $params['NEED_VALUES']))
			$params['NEED_VALUES'][$itemId] = array();
		$codeList = $this->getPropertyCodeList($sku);
		$offersList = CCatalogSKU::getOffersList($itemId, 0,
			array('ACTIVE' => 'Y'), array(), array('CODE' => $codeList));
		if(!empty($offersList))
		{
			foreach($offersList[$itemId] as $offersId => &$offers)
			{
				if($offerId && $offersId != $offerId)
					continue;

				foreach($offers['PROPERTIES'] as $propertiesCode => $properties)
				{
					if($properties['ID'] == $sku['SKU_PROPERTY_ID'] || empty($properties['VALUE']))
						continue;

					if(!is_array($propertyValue[$propertiesCode]))
						$propertyValue[$propertiesCode] = array();

					if(!in_array($properties['VALUE'],$propertyValue[$propertiesCode]))
					{
						if (!array_key_exists($properties['ID'], $params['NEED_VALUES'][$itemId]))
							$params['NEED_VALUES'][$itemId][$properties['ID']] = array();
						$valueId = ($properties['PROPERTY_TYPE'] == \Bitrix\Iblock\PropertyTable::TYPE_LIST
							? $properties['VALUE_ENUM_ID'] : $properties['VALUE']
						);
						$params['NEED_VALUES'][$itemId][$properties['ID']][$valueId] = $valueId;
						$propertyValue[$propertiesCode][] = $properties['VALUE'];
					}

					$offersTreeProps[] = $propertiesCode;
				}
			}
		}

		$params['OFFER_TREE_PROPS'][$itemId] = array_unique($offersTreeProps);
		if(!empty($params['PROPERTY_VALUE'][$itemId]))
		{
			$params['PROPERTY_VALUE'][$itemId] = array_merge_recursive($params['PROPERTY_VALUE'][$itemId], $propertyValue);
			foreach($params['PROPERTY_VALUE'][$itemId] as &$property)
				$property = array_unique($property);
		}
		else
		{
			$params['PROPERTY_VALUE'][$itemId] = $propertyValue;
		}
	}

	protected function getPropertyCodeList(array $sku)
	{
		$codeList = array();
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'USER_TYPE', 'IBLOCK_ID'),
			'filter' => array('=IBLOCK_ID' => $sku['IBLOCK_ID'], '=ACTIVE' => 'Y')
		));
		while ($property = $propertyIterator->fetch())
		{
			if($property['MULTIPLE'] == 'Y' || $property['ID'] == $sku['SKU_PROPERTY_ID'])
				continue;

			$property['USER_TYPE'] = (string)$property['USER_TYPE'];
			if (empty($property['CODE']))
				$property['CODE'] = $property['ID'];

			if (
				$property['PROPERTY_TYPE'] == 'L'
				|| $property['PROPERTY_TYPE'] == 'E'
				|| ($property['PROPERTY_TYPE'] == 'S' && $property['USER_TYPE'] == 'directory')
			)
			{
				$codeList[] = $property['CODE'];
				$this->codeList[$property['IBLOCK_ID']][] = $property['CODE'];
			}
		}
		return $codeList;
	}

	protected function formatResult()
	{
		parent::formatResult();

		$this->arResult['USER_ID'] = $this->userId;
		$this->arResult['CONTACT_TYPES'] = Catalog\SubscribeTable::getContactTypes();
	}

	protected function doActionsList()
	{
		$this->runSubscriberIdentification();
		$this->authorizeSubscriber();
		$this->unSubscribe();
		parent::doActionsList();
	}

	protected function runSubscriberIdentification()
	{
		if(empty($_REQUEST[static::ACTION_SUBSCRIBER_IDENTIFICATION]))
			return;

		$subscribeManager = new Catalog\Product\SubscribeManager;
		$result = $subscribeManager->runSubscriberIdentification($_REQUEST);
		if($result)
		{
			$message = Loc::getMessage('CPSL_REQUEST_IDENTIFICATION_SUCCESS');
			$stringParams = 'result=identificationOk&message='.urlencode($message).
				'&contact='.urlencode($_REQUEST['userContact']);
		}
		else
		{
			$errorObject = current($subscribeManager->getErrors());
			$message = $errorObject ? $errorObject->getMessage() : Loc::getMessage('CPSL_REQUEST_DEFAULT_ERROR');
			$stringParams = 'result=identificationFail&message='.urlencode($message);
		}

		global $APPLICATION;
		$cleanedParams = array('result', 'contact', 'message', static::ACTION_SUBSCRIBER_IDENTIFICATION);
		LocalRedirect($APPLICATION->getCurPageParam($stringParams, $cleanedParams));
	}

	protected function authorizeSubscriber()
	{
		if(empty($_REQUEST[static::ACTION_ACCESS_CODE_VERIFICATION]))
			return;

		$subscribeManager = new Catalog\Product\SubscribeManager;
		$result = $subscribeManager->authorizeSubscriber($_REQUEST);
		$stringParams = '';
		if(!$result)
		{
			$errorObject = current($subscribeManager->getErrors());
			$message = $errorObject ? $errorObject->getMessage() : Loc::getMessage('CPSL_REQUEST_DEFAULT_ERROR');
			$stringParams = 'result=authorizeFail&message='.urlencode($message);
		}

		global $APPLICATION;
		$cleanedParams = array('result', 'message',
			static::ACTION_ACCESS_CODE_VERIFICATION, 'userContact', 'subscribeToken');
		LocalRedirect($APPLICATION->getCurPageParam($stringParams, $cleanedParams));
	}

	protected function unSubscribe()
	{
		if(empty($_REQUEST[static::ACTION_UNSUBSCRIBE]))
			return;

		$subscribeManager = new Catalog\Product\SubscribeManager;
		$result = $subscribeManager->unSubscribe($_REQUEST);
		if($result)
		{
			$stringParams = 'result=unSubscribeOk&message='.urlencode(Loc::getMessage('CPSL_REQUEST_UNSUBSCRIBE_SUCCESS'));
		}
		else
		{
			$errorObject = current($subscribeManager->getErrors());
			$message = $errorObject ? $errorObject->getMessage() : Loc::getMessage('CPSL_REQUEST_DEFAULT_ERROR');
			$stringParams = 'result=unSubscribeFail&message='.urlencode($message);
		}

		global $APPLICATION;
		$cleanedParams = array('subscribeId', 'message', 'userContact', 'productId', static::ACTION_UNSUBSCRIBE);
		LocalRedirect($APPLICATION->getCurPageParam($stringParams, $cleanedParams));

	}
}