<?php
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\DiscountGroupTable;
use Bitrix\Sale\Internals\DiscountTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CSaleGiftMainProductsComponent extends CBitrixComponent
{
	protected $componentId = '';

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->componentId = $this->request->isAjaxRequest()? randString(7) : $this->randString();
	}

	public function getComponentId()
	{
		return $this->componentId;
	}

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
		global $APPLICATION;

		$params = parent::onPrepareComponentParams($params);

		if (isset($params['CUSTOM_SITE_ID']))
		{
			$this->setSiteId($params['CUSTOM_SITE_ID']);
		}

		// remember src params for further ajax query
		if (!isset($params['SGMP_CUR_BASE_PAGE']))
		{
			$params['SGMP_CUR_BASE_PAGE'] = $APPLICATION->GetCurPage();
		}

		$this->arResult['_ORIGINAL_PARAMS'] = $params;

		$offerId = $this->request->getPost('offerId');
		if($offerId)
		{
			$params['OFFER_ID'] = $offerId;
		}

		$state = $this->request->getPost('mainProductState');
		if($state)
		{
			$params['MAIN_PRODUCT_STATE'] = $state;
		}

		if(empty($params['SECTION_ID']))
		{
			if($params['ELEMENT_ID'])
			{
				$params['SECTION_ID'] = $this->getSectionIdByElement($params['ELEMENT_ID'], $params['IBLOCK_ID']);
			}
			else
			{
				$params['ELEMENT_ID'] = $this->getElementIdByOffer($params['OFFER_ID']);
				$params['SECTION_ID'] = $this->getSectionIdByElement($params['ELEMENT_ID'], $params['IBLOCK_ID']);
			}
		}

		$this->makeMagicWithPageNavigation();

		return $params;
	}

	private function makeMagicWithPageNavigation()
	{
		if(!$this->request->isAjaxRequest())
		{
			return;
		}
		foreach($this->request->getQueryList() as $name => $value)
		{
			if(preg_match("%^PAGEN_(\d+)$%", $name, $m))
			{
				global $NavNum;
				$NavNum = (int)$m[1] - 1;
				return;
			}
		}
	}

	private function getElementIdByOffer($offerId)
	{
		if(!\Bitrix\Main\Loader::includeModule('catalog'))
		{
			return 0;
		}
		$offerId = (int)$offerId;
		$productInfo = CCatalogSKU::GetProductInfo($offerId);

		return !empty($productInfo['ID'])? $productInfo['ID'] : 0;
	}

	private function getSectionIdByElement($elementId, $iblockId)
	{
		if(!\Bitrix\Main\Loader::includeModule('iblock'))
		{
			return 0;
		}
		$sectionId = 0;
		$elementId = (int)$elementId;
		$filter = array('=IBLOCK_ID' => $iblockId);

		if($elementId > 0)
		{
			$filter['=ID'] = $elementId;
		}
		else
		{
			return $sectionId;
		}

		$itemIterator = \Bitrix\Iblock\ElementTable::getList(array(
			'select' => array('ID', 'IBLOCK_SECTION_ID'),
			'filter' => $filter
		));
		if($item = $itemIterator->fetch())
		{
			$sectionId = (int)$item['IBLOCK_SECTION_ID'];
		}

		return $sectionId;
	}

	public function executeComponent()
	{
		if(!\Bitrix\Main\Loader::includeModule('sale') || !\Bitrix\Main\Loader::includeModule('catalog'))
		{
			return;
		}
		list($elementIds, $sectionIds) = $this->getDataMainProducts();
		$elementIds = $this->removeSku($elementIds);

		$this->arResult['RCM_TEMPLATE'] = $this->getTemplateName();
		$this->arResult['HAS_MAIN_PRODUCTS'] = !empty($elementIds) || !empty($sectionIds);
		$this->arResult['MAIN_ELEMENT_IDS'] = $elementIds;
		$this->arResult['MAIN_SECTION_IDS'] = $sectionIds;

		$this->arResult['MAIN_PRODUCT_STATE'] = $this->getState($elementIds, $sectionIds);
		$this->arResult['REQUEST_ITEMS'] = $this->request->isAjaxRequest();

		if(!$this->request->isAjaxRequest() || ($this->arParams['MAIN_PRODUCT_STATE'] !== $this->arResult['MAIN_PRODUCT_STATE']))
		{
			$this->includeComponentTemplate();
		}
	}

	private function removeSku(array $elementIds = array())
	{
		foreach($elementIds as $i => $id)
		{
			$productInfo = CCatalogSKU::GetProductInfo($id);
			if($productInfo['ID'])
			{
				unset($elementIds[$i]);
			}
		}
		unset($id);

		return $elementIds;
	}

	private function getDataMainProducts()
	{
		$elementIds = array();
		$sectionIds = array();

		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Sale\Discount\Gift\RelatedDataTable::getEntity());

		$offerId = $this->arParams['OFFER_ID'];
		$elementId = $this->arParams['ELEMENT_ID'];
		$sectionId = $this->arParams['SECTION_ID'];

		$query->addFilter(null, array(
			'LOGIC' => 'OR',
			array(
				'@ELEMENT_ID' => array_unique(array($elementId, $offerId)),
			),
			array(
				'SECTION_ID' => $sectionId,
			),
		));

		$referenceField2 = new ReferenceField(
			'D',
			DiscountTable::getEntity(),
			array('=this.DISCOUNT_ID' => 'ref.ID'),
			array('join_type' => 'INNER')
		);
		$query->registerRuntimeField('', $referenceField2);

		$query->addSelect('D.ID', 'ID2');
		$query->addSelect('D.XML_ID', 'XML_ID');
		$query->addSelect('D.LID', 'LID');
		$query->addSelect('D.NAME', 'NAME');
		$query->addSelect('D.PRICE_FROM', 'PRICE_FROM');
		$query->addSelect('D.PRICE_TO', 'PRICE_TO');
		$query->addSelect('D.CURRENCY', 'CURRENCY');
		$query->addSelect('D.DISCOUNT_VALUE', 'DISCOUNT_VALUE');
		$query->addSelect('D.DISCOUNT_TYPE', 'DISCOUNT_TYPE');
		$query->addSelect('D.ACTIVE', 'ACTIVE');
		$query->addSelect('D.SORT', 'SORT');
		$query->addSelect('D.ACTIVE_FROM', 'ACTIVE_FROM');
		$query->addSelect('D.ACTIVE_TO', 'ACTIVE_TO');
		$query->addSelect('D.TIMESTAMP_X', 'TIMESTAMP_X');
		$query->addSelect('D.MODIFIED_BY', 'MODIFIED_BY');
		$query->addSelect('D.DATE_CREATE', 'DATE_CREATE');
		$query->addSelect('D.CREATED_BY', 'CREATED_BY');
		$query->addSelect('D.PRIORITY', 'PRIORITY');
		$query->addSelect('D.LAST_DISCOUNT', 'LAST_DISCOUNT');
		$query->addSelect('D.VERSION', 'VERSION');
		$query->addSelect('D.CONDITIONS_LIST', 'CONDITIONS_LIST');
		$query->addSelect('D.CONDITIONS', 'CONDITIONS');
		$query->addSelect('D.UNPACK', 'UNPACK');
		$query->addSelect('D.ACTIONS_LIST', 'ACTIONS_LIST');
		$query->addSelect('D.ACTIONS', 'ACTIONS');
		$query->addSelect('D.APPLICATION', 'APPLICATION');
		$query->addSelect('D.USE_COUPONS', 'USE_COUPONS');
		$query->addSelect('D.EXECUTE_MODULE', 'EXECUTE_MODULE');

		global $USER;
		$query->addFilter('=DISCOUNT_GROUP.ACTIVE', 'Y');
		$query->addFilter('DISCOUNT_GROUP.GROUP_ID', $USER->getUserGroupArray());
		$query->addFilter('=D.LID', $this->getSiteId());

		\CTimeZone::Disable();
		$currentDatetime = new DateTime();
		$query->addFilter(
			null,
			[
				'LOGIC' => 'OR',
				'>=D.ACTIVE_TO' => $currentDatetime,
				'=D.ACTIVE_TO' => null,
			]
		);
		$query->addFilter(
			null,
			[
				'LOGIC' => 'OR',
				'<=D.ACTIVE_FROM' => $currentDatetime,
				'=D.ACTIVE_FROM' => null,
			]
		);

		$discounts = array();
		$dbResult = $query->exec();

		while($row = $dbResult->fetch())
		{
			$row['ID'] = $row['ID2'];
			unset($row['ID2']);
			$discounts[$row['ID']] = $row;

			list($productElementIds, $productSectionIds) = Bitrix\Sale\Discount\Gift\RelatedDataTable::getProductsData($discounts[$row['ID']]);
			$elementIds = array_merge($elementIds, $productElementIds);
			$sectionIds = array_merge($sectionIds, $productSectionIds);
		}

		\CTimeZone::Enable();

		return array(array_unique($elementIds), array_unique($sectionIds));
	}

	/**
	 * @param $elementIds
	 * @param $sectionIds
	 * @return string
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	private function getState($elementIds, $sectionIds)
	{
		sort($elementIds);
		sort($sectionIds);
		$sign = new \Bitrix\Main\Security\Sign\Signer;
		$state = $sign->sign(base64_encode(serialize(array(
			$elementIds,
			$sectionIds
		))));

		return $state;
	}
}