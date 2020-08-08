<?

use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\BusinessValueConsumer1C;
use Bitrix\Sale;

IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_EXPORT"] = Array();

final class ExportOneCCRM extends CSaleExport
{
	static protected function getParentEntityTypeId()
	{
		return \Bitrix\Sale\Exchange\EntityType::INVOICE;
	}

    static protected function load($id)
	{
	    return \Bitrix\Crm\Invoice\Invoice::load($id);
	}

	static public function getParentEntityTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\InvoiceTable();
	}

	static protected function getPaymentTable()
    {
		return new \Bitrix\Crm\Invoice\Internals\PaymentTable();
    }

	static protected function getShipmentTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\ShipmentTable();
	}

	static protected function getBasketTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\BasketTable();
	}

	static protected function getEntityChangeTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\InvoiceChangeTable();
	}

	static protected function getEntityMarker()
	{
		return new \Bitrix\Crm\Invoice\EntityMarker();
	}

	static protected function getPersonType()
	{
		return \Bitrix\Crm\Invoice\PersonType::class;
	}

    static public function normalizeExternalCode($xml)
	{
		static $sales = null;

		list($originatorId, $productXmlId) = explode("#", $xml, 2);
		if($productXmlId<>'')
		{
			if($sales === null)
				$sales = CCrmExternalSaleHelper::PrepareListItems();

			if(isset($sales[$originatorId]))
			{
				$xml = $productXmlId;
			}
		}

		return parent::normalizeExternalCode($xml);
	}

	static protected function getUserTimeStapmX(array $arOrder)
	{
		return new \Bitrix\Main\Type\DateTime(\CAllDatabase::FormatDate($arOrder["CRM_INVOICE_INTERNALS_INVOICE_USER_TIMESTAMP_X"]));
	}

	static protected function getUserXmlId(array $arOrder, array $arProp)
	{
		if($arOrder["CRM_INVOICE_INTERNALS_INVOICE_USER_XML_ID"] <> '')
		{
			$xmlId = htmlspecialcharsbx($arOrder["CRM_INVOICE_INTERNALS_INVOICE_USER_XML_ID"]);
		}
		else
		{
			$xmlId = static::updateEmptyUserXmlId($arOrder, $arProp);
		}

		return $xmlId;
	}

	static protected function resolveEntityTypeId($typeDocument, array $document)
	{
		$typeEntityId = \Bitrix\Sale\Exchange\EntityType::UNDEFINED;
		switch ($typeDocument)
		{
			case 'Order':
				$typeEntityId = \Bitrix\Sale\Exchange\EntityType::INVOICE;
				break;
			case 'Payment':
				$psType = \Bitrix\Sale\PaySystem\Manager::getPsType($document['PAY_SYSTEM_ID']);

				if($psType == 'A')
					$typeEntityId = \Bitrix\Sale\Exchange\EntityType::INVOICE_PAYMENT_CARD_TRANSACTION;
                elseif($psType == 'N')
					$typeEntityId = \Bitrix\Sale\Exchange\EntityType::INVOICE_PAYMENT_CASH_LESS;
				else
					$typeEntityId = \Bitrix\Sale\Exchange\EntityType::INVOICE_PAYMENT_CASH;
				break;
			case 'Shipment':
				$typeEntityId = \Bitrix\Sale\Exchange\EntityType::INVOICE_SHIPMENT;
				break;
		}

		return $typeEntityId;
	}

	static protected function getStatusInfoByStatusId($id)
	{
		$result = [];

		$res = \Bitrix\Crm\Invoice\InvoiceStatus::getList(['filter'=>['ID'=>$id]]);
		if($status = $res->fetch())
			$result = $status;

		return $result;
	}

}

class CSaleExport
{
	const DEFAULT_VERSION = 2.05;
	const PARTIAL_VERSION = 2.1;
	const CONTAINER_VERSION = 3;

	const LAST_ORDER_PREFIX = 'LAST_ORDER_ID';

	const DIVIDER_NUMBER_POSITION = 100000;

	static $versionSchema;
	static $crmMode;
	static $currency;
	static $measures;
	static $orderTax;

	static $arResultStat = array();
	static $xmlVersion = "1.0";
	static $xmlEncoding = "windows-1251";
	static $xmlRootName = "<?xml version=\"#version#\" encoding=\"#encoding#\"?>";

	static $typeDocument = "";
	static $deliveryAdr = "";

	static $siteNameByOrder = "";

	static $documentsToLog;

	protected static $lid = null;

	static protected function getParentEntityTypeId()
	{
		return \Bitrix\Sale\Exchange\EntityType::ORDER;
	}

	static protected function load($id)
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		return $orderClass::load($id);
	}

	static public function getParentEntityTable()
	{
		return new \Bitrix\Sale\Internals\OrderTable();
	}

	static protected function getPaymentTable()
	{
		return new \Bitrix\Sale\Internals\PaymentTable();
	}

	static protected function getShipmentTable()
    {
        return new \Bitrix\Sale\Internals\ShipmentTable();
    }

	static protected function getBasketTable()
    {
        return new \Bitrix\Sale\Internals\BasketTable();
	}

	static protected function getEntityChangeTable()
    {
		return new \Bitrix\Sale\Internals\OrderChangeTable();
    }

    static protected function getEntityMarker()
    {
		return new \Bitrix\Sale\EntityMarker();
    }

    static protected function getPersonType()
    {
		return \Bitrix\Sale\PersonType::class;
    }

    static protected function getUserTimeStapmX(array $arOrder)
    {
		return new \Bitrix\Main\Type\DateTime(\CAllDatabase::FormatDate($arOrder["SALE_INTERNALS_ORDER_USER_TIMESTAMP_X"]));
    }

    static protected function getUserXmlId(array $arOrder, array $arProp)
	{
		if($arOrder["SALE_INTERNALS_ORDER_USER_XML_ID"] <> '')
		{
			$xmlId = htmlspecialcharsbx($arOrder["SALE_INTERNALS_ORDER_USER_XML_ID"]);
		}
		else
		{
			$xmlId = static::updateEmptyUserXmlId($arOrder, $arProp);
		}

		return $xmlId;
	}

	static protected function updateEmptyUserXmlId(array $arOrder, array $arProp)
    {
		$xmlId = htmlspecialcharsbx(mb_substr($arOrder["USER_ID"]."#".$arProp["USER"]["LOGIN"]."#".$arProp["USER"]["LAST_NAME"]." ".$arProp["USER"]["NAME"]." ".$arProp["USER"]["SECOND_NAME"], 0, 40));
		\Bitrix\Sale\Exchange\Entity\UserImportBase::updateEmptyXmlId($arOrder["USER_ID"], $xmlId);

		return $xmlId;
    }

    static protected function resolveEntityTypeId($typeDocument, array $document)
    {
        $typeEntityId = \Bitrix\Sale\Exchange\EntityType::UNDEFINED;
        switch ($typeDocument)
        {
            case 'Order':
                $typeEntityId = \Bitrix\Sale\Exchange\EntityType::ORDER;
                break;
            case 'Payment':
                $psType = \Bitrix\Sale\PaySystem\Manager::getPsType($document['PAY_SYSTEM_ID']);

                if($psType == 'A')
                    $typeEntityId = \Bitrix\Sale\Exchange\EntityType::PAYMENT_CARD_TRANSACTION;
                elseif($psType == 'N')
                    $typeEntityId = \Bitrix\Sale\Exchange\EntityType::PAYMENT_CASH_LESS;
                else
                    $typeEntityId = \Bitrix\Sale\Exchange\EntityType::PAYMENT_CASH;
                break;
            case 'Shipment':
                $typeEntityId = \Bitrix\Sale\Exchange\EntityType::SHIPMENT;
                break;
        }

        return $typeEntityId;
    }

    static protected function getStatusInfoByStatusId($id)
    {
		return CSaleStatus::GetLangByID($id);
    }


	/**
	 * @param $value
	 * @return string
	 */
	protected static function toText($value)
	{
		$value = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $value);
		$value = preg_replace('/<blockquote[^>]*>.*?<\/blockquote>/is', '', $value);
		$value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);

		return html_entity_decode(
			strip_tags(
				preg_replace('/(<br[^>]*>)+/is'.BX_UTF_PCRE_MODIFIER, "\n", $value)
			)
		);
	}

	static public function getNumberBasketPosition($basketId)
	{
	    return intval($basketId) % self::DIVIDER_NUMBER_POSITION;
	}

	public static function setLanguage($value)
	{
		static::$lid = $value;
	}

    static function setXmlEncoding($encoding)
    {
        self::$xmlEncoding = $encoding;
    }

    static function getXmlRootName()
    {
        return str_replace(array("#version#","#encoding#"),array(self::$xmlVersion,self::$xmlEncoding),self::$xmlRootName);
    }

	static function getCmrXmlRootNameParams()
	{
		return CSaleExport::getTagName("SALE_EXPORT_SHEM_VERSION")."=\"".self::getVersionSchema()."\" ".CSaleExport::getTagName("SALE_EXPORT_SHEM_DATE_CREATE")."=\"".date("Y-m-d")."T".date("G:i:s")."\" ".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT")."=\"".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_DF")."=yyyy-MM-dd; ".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_DLF")."=DT\" ".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_DATETIME")."=\"".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_DF")."=".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_TIME")."; ".CSaleExport::getTagName("SALE_EXPORT_DATE_FORMAT_DLF")."=T\" ".CSaleExport::getTagName("SALE_EXPORT_DEL_DT")."=\"T\" ".CSaleExport::getTagName("SALE_EXPORT_FORM_SUMM")."=\"".CSaleExport::getTagName("SALE_EXPORT_FORM_CC")."=18; ".CSaleExport::getTagName("SALE_EXPORT_FORM_CDC")."=2; ".CSaleExport::getTagName("SALE_EXPORT_FORM_CRD")."=.\" ".CSaleExport::getTagName("SALE_EXPORT_FORM_QUANT")."=\"".CSaleExport::getTagName("SALE_EXPORT_FORM_CC")."=18; ".CSaleExport::getTagName("SALE_EXPORT_FORM_CDC")."=2; ".CSaleExport::getTagName("SALE_EXPORT_FORM_CRD")."=.\"";
	}

	static function getDeliveryAddress()
	{
		return self::$deliveryAdr;
	}
	static function setDeliveryAddress($deliveryAdr)
	{
		self::$deliveryAdr = $deliveryAdr;
	}
	static function setVersionSchema($versionSchema=false)
	{
		self::$versionSchema = $versionSchema;
	}
	static function setCrmMode($crmMode)
	{
		self::$crmMode = $crmMode;
	}
	static function setCurrencySchema($currency)
	{
		self::$currency = $currency;
	}
	static function getVersionSchema()
	{
		return doubleval(str_replace(" ", "", str_replace(",", ".", (!empty(self::$versionSchema) ? self::$versionSchema : self::DEFAULT_VERSION))));
	}

	/**
	 * @return int|null
	 */
	public static function getCashBoxOneCId()
    {
		static $cashBoxOneCId = null;

		if($cashBoxOneCId === null)
        {
			$cashBoxOneCId = \Bitrix\Sale\Cashbox\Cashbox1C::getId();
        }

        return $cashBoxOneCId;
    }

	static function isExportFromCRM($arOptions)
	{
		return (isset($arOptions["EXPORT_FROM_CRM"]) && $arOptions["EXPORT_FROM_CRM"] === "Y");
	}
	static function getEndTime($time_limit)
	{	//This is an optimization. We assume than no step can take more than one year.
		if($time_limit > 0)
			$end_time = time() + $time_limit;
		else
			$end_time = time() + 365*24*3600; // One year

		return $end_time;
	}
	static function checkTimeIsOver($time_limit,$end_time)
	{
		if(intval($time_limit) > 0 && time() > $end_time )
			return true;
		else
			return false;
	}
	static function getOrderPrefix()
	{
		return self::LAST_ORDER_PREFIX;
	}

	function getAccountNumberShopPrefix()
	{
		static $accountNumberShopPrefix = null;
		if($accountNumberShopPrefix === null)
		    $accountNumberShopPrefix = COption::GetOptionString("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");

		return $accountNumberShopPrefix;
	}
	function getSalePaySystem()
	{
		$paySystems = array();
		$dbPaySystem = CSalePaySystem::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
		while($arPaySystem = $dbPaySystem -> Fetch())
			$paySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];

		return $paySystems;
	}
	function getSaleDelivery()
	{
		$delivery = array();
		$dbDeliveryList = \Bitrix\Sale\Delivery\Services\Table::GetList();
		while($service = $dbDeliveryList->fetch())
		{
		    $deliveryObj = Bitrix\Sale\Delivery\Services\Manager::createObject($service);
            $delivery[$deliveryObj->GetId()] = ($deliveryObj->isProfile() ? $deliveryObj->getNameWithParent():$deliveryObj->getName());
		}
		return $delivery;
	}
	static function getCatalogStore()
	{
		$arStore = array();
		if(CModule::IncludeModule("catalog"))
		{
			$dbList = CCatalogStore::GetList(
				array("SORT" => "DESC", "ID" => "ASC"),
				array("ACTIVE" => "Y", "ISSUING_CENTER" => "Y"),
				false,
				false,
				array("ID", "SORT", "TITLE", "ADDRESS", "DESCRIPTION", "PHONE", "EMAIL", "XML_ID")
			);
			while ($arStoreTmp = $dbList->Fetch())
			{
				if($arStoreTmp["XML_ID"] == '')
					$arStoreTmp["XML_ID"] = $arStoreTmp["ID"];
				$arStore[$arStoreTmp["ID"]] = $arStoreTmp;
			}
		}
		return $arStore;
	}
	static function getOrderDeliveryItem($arOrder, $bVat, $vatRate, $vatSum)
    {
        if(floatval($arOrder["PRICE_DELIVERY"])<=0)
             return;
        ?>
        <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>ORDER_DELIVERY</<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ORDER_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <?
            if(self::getVersionSchema() > self::DEFAULT_VERSION)
            {
                ?>
                <<?=CSaleExport::getTagName("SALE_EXPORT_UNIT")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>>796</<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>><?=htmlspecialcharsbx(self::$measures[796]['MEASURE_TITLE'])?></<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>>
                </<?=CSaleExport::getTagName("SALE_EXPORT_UNIT")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_KOEF")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_KOEF")?>>
            <?
            }
            else
            {
                ?>
                <<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?> <?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>="796" <?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=CSaleExport::getTagName("SALE_EXPORT_SHTUKA")?>" <?=CSaleExport::getTagName("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=CSaleExport::getTagName("SALE_EXPORT_RCE")?>"><?=CSaleExport::getTagName("SALE_EXPORT_SHT")?></<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?>>
                <?
            }
            ?>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SERVICE")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SERVICE")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
            <?if($bVat)
            {
                $deliveryTax = roundEx((($arOrder["PRICE_DELIVERY"] / ($vatRate+1)) * $vatRate), 2);
                if(self::$orderTax > $vatSum && self::$orderTax == roundEx($vatSum + $deliveryTax, 2))
                {
                    ?>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>><?=$vatRate * 100?></<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
                    </<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$deliveryTax?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
                    </<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
                    <?
                }
            }?>
        </<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
        <?
    }

    static function getCatalogMeasure()
	{
		$arMeasures = array();
		if(CModule::IncludeModule("catalog"))
		{
			$dbList = CCatalogMeasure::getList(array(), array(), false, false, array("CODE", "MEASURE_TITLE","SYMBOL_LETTER_INTL", "SYMBOL_RUS"));
			while($arList = $dbList->Fetch())
			{
				$arMeasures[$arList["CODE"]] = $arList;
			}
		}
		if(empty($arMeasures))
			$arMeasures[796] = array('NAME'=>CSaleExport::getTagName("SALE_EXPORT_SHTUKA"));

		return $arMeasures;
	}
    static function setCatalogMeasure($arMeasures)
	{
		self::$measures = $arMeasures;
	}
	static function setOrderSumTaxMoney($orderTax)
	{
		self::$orderTax = $orderTax;

	}
    static function getSaleExport()
	{
		$export = new CSaleExport();
	    $arAgent = array();

		$dbExport = $export->GetList();
		while($arExport = $dbExport->Fetch())
		{
			$arAgent[$arExport["PERSON_TYPE_ID"]] = unserialize($arExport["VARS"]);
		}
		return $arAgent;
	}

	static function prepareSaleProperty($arOrder, $bExportFromCrm, $bCrmModuleIncluded, $paySystems, $delivery, &$locationStreetPropertyValue, \Bitrix\Sale\Order $order)
	{
		$arProp = Array();
		$arProp["ORDER"] = $arOrder;

		if (intval($arOrder["USER_ID"]) > 0)
		{
			$dbUser = CUser::GetByID($arOrder["USER_ID"]);
			if ($arUser = $dbUser->Fetch())
				$arProp["USER"] = $arUser;
		}

		if ($bExportFromCrm)
		{
			$arProp["CRM"] = array();
			$companyID = isset($arOrder["UF_COMPANY_ID"]) ? intval($arOrder["UF_COMPANY_ID"]) : 0;
			$contactID = isset($arOrder["UF_CONTACT_ID"]) ? intval($arOrder["UF_CONTACT_ID"]) : 0;
			if ($companyID > 0)
			{
				$arProp["CRM"]["CLIENT_ID"] = "CRMCO".$companyID;
			}
			else
			{
				$arProp["CRM"]["CLIENT_ID"] = "CRMC".$contactID;
			}

			$clientInfo = array(
				"LOGIN" => "",
				"NAME" => "",
				"LAST_NAME" => "",
				"SECOND_NAME" => ""
			);

			if ($bCrmModuleIncluded)
			{
				if ($companyID > 0)
				{
					$arCompanyFilter = array('=ID' => $companyID);
					$dbCompany = CCrmCompany::GetListEx(
						array(), $arCompanyFilter, false, array("nTopCount" => 1),
						array("TITLE")
					);
					$arCompany = $dbCompany->Fetch();
					unset($dbCompany, $arCompanyFilter);
					if (is_array($arCompany))
					{
						if (isset($arCompany["TITLE"]))
							$clientInfo["NAME"] = $arCompany["TITLE"];
					}
					unset($arCompany);
				}
				else if ($contactID > 0)
				{
					$arContactFilter = array('=ID' => $contactID);
					$dbContact = CCrmContact::GetListEx(
						array(), $arContactFilter, false, array("nTopCount" => 1),
						array("NAME", "LAST_NAME", "SECOND_NAME")
					);
					$arContact = $dbContact->Fetch();
					unset($dbContact, $arContactFilter);
					if (is_array($arContact))
					{
						if (isset($arContact["NAME"]))
							$clientInfo["NAME"] = $arContact["NAME"];
						if (isset($arContact["LAST_NAME"]))
							$clientInfo["LAST_NAME"] = $arContact["LAST_NAME"];
						if (isset($arContact["SECOND_NAME"]))
							$clientInfo["SECOND_NAME"] = $arContact["SECOND_NAME"];
					}
					unset($arContact);
				}
			}

			$arProp["CRM"]["CLIENT"] = $clientInfo;
			unset($clientInfo);
		}

		if(intval($arOrder["PAY_SYSTEM_ID"]) > 0)
			$arProp["ORDER"]["PAY_SYSTEM_NAME"] = $paySystems[$arOrder["PAY_SYSTEM_ID"]];
		if($arOrder["DELIVERY_ID"] <> '')
			$arProp["ORDER"]["DELIVERY_NAME"] = $delivery[$arOrder["DELIVERY_ID"]];


		$propertyCollection = $order->getPropertyCollection();
		$locationStreetPropertyValue = '';
        foreach ($propertyCollection as $prop)
		{
            if($prop->getType() == 'Y/N')
			{
				if ($prop->getValue() == "Y")
					$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = "true";
				else
					$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = "false";
			}
			elseif ($prop->getType() == 'STRING')
			{
				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = $prop->getValue();
			}
			elseif ($prop->getType() == 'ENUM')
			{
				if($prop->getProperty()['MULTIPLE'] == 'Y')
                {
					$curVal = explode(",", $prop->getValue());
					foreach($curVal as $vm)
					{
						$arVal = CSaleOrderPropsVariant::GetByValue($prop->getField('ORDER_PROPS_ID'), $vm);
						$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] .=  ", ".$arVal["NAME"];
					}
					$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = mb_substr($arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')], 2);
                }
                else
                {
					$arVal = CSaleOrderPropsVariant::GetByValue($prop->getField('ORDER_PROPS_ID'), $prop->getValue());
					$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = $arVal["NAME"];
                }
			}
			elseif ($prop->getType() == "LOCATION")
			{
				$arVal = CSaleLocation::GetByID($prop->getValue(), LANGUAGE_ID);

				if(CSaleLocation::isLocationProEnabled())
				{
					if(intval($arVal['ID']))
					{
						try
						{
							$res = \Bitrix\Sale\Location\LocationTable::getPathToNode($arVal['ID'], array('select' => array('LNAME' => 'NAME.NAME', 'TYPE_ID'), 'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)));
							$types = \Bitrix\Sale\Location\Admin\TypeHelper::getTypeCodeIdMapCached();
							$path = array();
							while($item = $res->fetch())
							{
								// copy street to STREET property
								if($types['ID2CODE'][$item['TYPE_ID']] == 'STREET')
									$locationStreetPropertyValue = $item['LNAME'];
								$path[] = $item['LNAME'];
							}

							$locationString = implode(' - ', $path);
						}
						catch(\Bitrix\Main\SystemException $e)
						{
							$locationString = '';
						}
					}
					else
						$locationString = '';
				}
				else
					$locationString =  ($arVal["COUNTRY_NAME"].(($arVal["COUNTRY_NAME"] == '' || $arVal["REGION_NAME"] == '') ? "" : " - ").$arVal["REGION_NAME"].(($arVal["COUNTRY_NAME"] == '' || $arVal["CITY_NAME"] == '') ? "" : " - ").$arVal["CITY_NAME"]);

				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = $locationString;

				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')."_CITY"] = $arVal["CITY_NAME"];
				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')."_COUNTRY"] = $arVal["COUNTRY_NAME"];
				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')."_REGION"] = $arVal["REGION_NAME"];
			}
			else
			{
				$arProp["PROPERTY"][$prop->getField('ORDER_PROPS_ID')] = $prop->getValue();

			}
		}

		return $arProp;
	}

	static function prepareSalePropertyRekv(\Bitrix\Sale\IBusinessValueProvider $entity, $agentParams, $arProp, $locationStreetPropertyValue = '')
	{
	    if(!($entity instanceof \Bitrix\Sale\Order))
        {
			/** @var \Bitrix\Sale\PaymentCollection|\Bitrix\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();
			$order = $collection->getOrder();
        }
        else
			$order = $entity;

		$providersInstance = self::getProvidersInstanceByEntity($entity);

        $personTypeId = $order->getPersonTypeId();

		$personTypes = BusinessValue::getPersonTypes();

		if (! $personType = $personTypes[$personTypeId])
		{
			self::logError($order->getId(), 'Undefined DOMAIN for person type id "'.$personTypeId.'"');
			return false;
		}

		$systemCodes1C = array_flip(self::$systemCodes[$personType['DOMAIN']]);
		
		foreach($agentParams as $k => $v)
		{
			if(mb_strpos($k, "REKV_") !== false)
			{//params
				if(!is_array($v))
				{
					$agent["REKV"][$k] = $v;
				}
				else
				{
					if($v["TYPE"] == '')
						$agent["REKV"][$k] = $v["VALUE"];//code
					else
					{
					    switch($v["TYPE"])
					    {
					        case 'CRM':
					            $agent["REKV"][$k] = $arProp[$v["TYPE"]][$v["VALUE"]];//value
					            break;
					        default:

                                if (! ($codeKey = $systemCodes1C[$k])
                                    && mb_substr($k, 0, 5) === 'REKV_'
                                    && ($codeIndex = mb_substr($k, 5)) !== ''
                                    && ($codeKey = BusinessValueConsumer1C::getRekvCodeKey($order->getPersonTypeId(), $codeIndex))
                                    && ($providerInstance = $providersInstance[$v["TYPE"]])
                                    && is_set($providerInstance))
                                {
									$bsValue = Bitrix\Sale\BusinessValue::getValueFromProvider($providerInstance, $codeKey, BusinessValueConsumer1C::CONSUMER_KEY);
									$agent["REKV"][$k] = (is_array($bsValue) ? implode(',', $bsValue):$bsValue);
                                }
					    }
					}
				}
			}
			else
			{
				if(!is_array($v))
				{
					$agent[$k] = $v;
				}
				else
				{
					if($v["TYPE"] == '')
						$agent[$k] = $v["VALUE"];
					else
					{
                        switch($v["TYPE"])
                        {
                            case 'CRM':
                                $agent[$k] = $arProp[$v["TYPE"]][$v["VALUE"]];
                                break;
                            default:
                                if (($codeKey = $systemCodes1C[$k])
                                    && ($providerInstance = $providersInstance[$v["TYPE"]])
                                    && is_set($providerInstance))
                                {
                                    $bsValue = Bitrix\Sale\BusinessValue::getValueFromProvider($providerInstance, $codeKey, BusinessValueConsumer1C::CONSUMER_KEY);
                                    $agent[$k] = (is_array($bsValue) ? implode(',', $bsValue):$bsValue);
                                }
                        }
					}

					if($k == 'STREET' && mb_strlen($locationStreetPropertyValue))
						$agent[$k] = $locationStreetPropertyValue.($agent[$k] <> ''? ', ' : '').$agent[$k];
				}
			}
		}

		return $agent;
	}
	
	static function getSite()
	{
		$arCharSets = array();
		$dbSitesList = CSite::GetList(($b=""), ($o=""));
		while ($arSite = $dbSitesList->Fetch())
			$arCharSets[$arSite["ID"]] = $arSite["CHARSET"];

		return $arCharSets;
	}
	static function setSiteNameByOrder($arOrder)
	{
		$dbSite = CSite::GetByID($arOrder["LID"]);
		$arSite = $dbSite->Fetch();
		self::$siteNameByOrder = $arSite["NAME"];
	}
	static function getPayment($arOrder)
	{
		$result = array();
		$entity = static::getPaymentTable();

		$PaymentParam['select'] =
			array(
				"ID",
				"ID_1C",
				"PAID",
				"DATE_BILL",
				"ORDER_ID",
				"CURRENCY",
				"SUM",
				"COMMENTS",
				"DATE_PAID",
				"PAY_SYSTEM_ID",
				"PAY_SYSTEM_NAME",
				"IS_RETURN",
				"PAY_RETURN_COMMENT",
				"PAY_VOUCHER_NUM",
				"PAY_VOUCHER_DATE",

			);


		$PaymentParam['filter']['ORDER_ID'] = $arOrder['ID'];
		$PaymentParam['filter']['!=EXTERNAL_PAYMENT'] = 'F';
		$innerPS = 0;
		$limit = 0;
		$inc = 0;

		if(self::getVersionSchema() < self::PARTIAL_VERSION)
		{
			$innerPS = \Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId();
			$limit = 1;
		}

		$resPayment = $entity::getList($PaymentParam);

		while($arPayment = $resPayment->fetch())
		{
			foreach($arPayment as $field=>$value)
			{
			    if(self::isFormattedDateFields('Payment', $field))
			    {
			        $arPayment[$field] = self::getFormatDate($value);
			    }
			}

            $result['paySystems'][$arPayment['PAY_SYSTEM_ID']] = $arPayment['PAY_SYSTEM_NAME'];

			if($innerPS == 0 || $innerPS!=$arPayment['PAY_SYSTEM_ID'])
			{
			    if($limit == 0 || $inc < $limit)
			        $result['payment'][] = $arPayment;

			    $inc++;
			}
		}
		return $result;
	}
	static function getShipment($arOrder)
	{
		$result = array();
		$entity = static::getShipmentTable();

		$ShipmentParams['select'] =
			array(
				"ID",
				"ID_1C",
				"DATE_INSERT",
				"CURRENCY",
				"PRICE_DELIVERY",
				"COMMENTS",
				"DATE_ALLOW_DELIVERY",
				"STATUS_ID",
				"DEDUCTED",
				"DATE_DEDUCTED",
				"REASON_UNDO_DEDUCTED",
				"RESERVED",
				"DELIVERY_ID",
				"DELIVERY_NAME",
				"CANCELED",
				"DATE_CANCELED",
				"REASON_CANCELED",
				"REASON_MARKED",
				"ORDER_ID",
                "TRACKING_NUMBER"
			);

		$ShipmentParams['filter']['ORDER_ID'] = $arOrder['ID'];
		$ShipmentParams['filter']['=SYSTEM'] = 'N';
		$limit = 0;
		$inc = 0;

		if(self::getVersionSchema() < self::PARTIAL_VERSION )
		    $limit = 1;

		$resShipment = $entity::getList($ShipmentParams);
		while($arShipment = $resShipment->fetch())
		{
			foreach($arShipment as $field=>$value)
			{
			    if(self::isFormattedDateFields('Shipment', $field))
			    {
			        $arShipment[$field] = self::getFormatDate($value);
			    }
			}

			$result['deliveryServices'][$arShipment['DELIVERY_ID']] = $arShipment['DELIVERY_NAME'];

            if($limit == 0 || $inc < $limit)
                $result['shipment'][] = $arShipment;

            $inc++;
		}

		return $result;
	}

	protected static function getLastOrderExported($timeUpdate)
	{
		$result = array();

		if($timeUpdate <> '')
		{
			$r = \Bitrix\Sale\Exchange\Internals\ExchangeLogTable::getList(array(
				'select' => array(
					'ENTITY_ID',
					'ENTITY_DATE_UPDATE'
				),
				'filter' => array(
					'ENTITY_TYPE_ID'=>static::getParentEntityTypeId(),
					'=ENTITY_DATE_UPDATE'=>$timeUpdate,
					'=DIRECTION'=>\Bitrix\Sale\Exchange\ManagerExport::getDirectionType()
				),
				'order'=>array('ID'=>'ASC'),

			));
			while ($order = $r->fetch())
				$result[$order['ENTITY_DATE_UPDATE']->toString()][]=$order['ENTITY_ID'];
		}
		return $result;
	}

	protected static function exportedLastExport($arOrder, array $lastDateUpdateOrders)
    {
		$dateUpdate = $arOrder["DATE_UPDATE"]->toString();

		$result = (isset($lastDateUpdateOrders[$dateUpdate]) &&
			in_array($arOrder['ID'], $lastDateUpdateOrders[$dateUpdate]));

		return $result;
    }

	/**
	 * @return array
	 */
    protected static function prepareFilter($arFilter=array())
    {
		if(intval($_SESSION["BX_CML2_EXPORT"][self::getOrderPrefix()]) > 0)
		{
			$arFilter[">=DATE_UPDATE"] = ConvertTimeStamp($_SESSION["BX_CML2_EXPORT"][self::getOrderPrefix()], "FULL");
		}
		return $arFilter;
    }

	/**
	 * @param array $arOrder
	 */
    protected static function saveExportParams(array $arOrder)
    {
		$_SESSION["BX_CML2_EXPORT"][self::getOrderPrefix()] = MakeTimeStamp($arOrder["DATE_UPDATE"], CSite::GetDateFormat("FULL"));
    }

	static protected function getLastDateUpdateByParams(array $params)
    {
		$params = static::prepareFilter($params);
		return isset($params[">=DATE_UPDATE"])? $params[">=DATE_UPDATE"]:'';
    }

	/**
	 * @param array $params
	 * @return \Bitrix\Sale\Result
	 */
	public function export(array $params)
    {
        $result = new \Bitrix\Sale\Result();

        $filter = $params['filter'];
		$timeLimit = isset($params['limit'])? intval($params['limit']):0;

		\Bitrix\Sale\Exchange\ExportOneCSubordinateSale::configuration();
		$export = \Bitrix\Sale\Exchange\ExportOneCSubordinateSale::getInstance();

		$xml = $export->outputXmlCMLHeader();

        $end_time = self::getEndTime($timeLimit);

		$timeUpdate = static::getLastDateUpdateByParams($filter);
		$lastDateUpdateOrders = static::getLastOrderExported($timeUpdate);

		if($timeUpdate<>'')
		    $filter['>=DATE_UPDATE'] = $timeUpdate;
		$filter['RUNNING'] = 'N';

		$entity = static::getParentEntityTable();

		$list = $entity::getList([
			'select' => ["*"],
			'filter' => $filter,
			'order'  => ["DATE_UPDATE" => "ASC", "ID"=>"ASC"],
		]);

		while($orderFields = $list->Fetch())
        {
			if(static::exportedLastExport($orderFields, $lastDateUpdateOrders))
			{
				continue;
			}

			$r = $export->proccess(['ORDER_ID'=>$orderFields['ID']]);
			if($r->isSuccess())
                $xml .= $r->getData()[0];

			static::saveExportParams($orderFields);

            if(self::checkTimeIsOver($timeLimit, $end_time))
			{
				break;
			}
        }

		$xml .= $export->outputXmlCMLFooter();

		return $result->setData([$xml]);
	}

    static function ExportOrders2Xml($arFilter = Array(), $nTopCount = 0, $currency = "", $crmMode = false, $time_limit = 0, $version = false, $arOptions = Array())
	{
		$lastOrderPrefix = '';
		$arCharSets = array();
		$lastDateUpdateOrders = array();
		$entityMarker = static::getEntityMarker();

	    self::setVersionSchema($version);
		self::setCrmMode($crmMode);
		self::setCurrencySchema($currency);

		$count = false;
		if(intval($nTopCount) > 0)
			$count = Array("nTopCount" => $nTopCount);

		$end_time = self::getEndTime($time_limit);

		if(intval($time_limit) > 0)
		{
			if(self::$crmMode)
			{
				$lastOrderPrefix = md5(serialize($arFilter));
				if(!empty($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && intval($nTopCount) > 0)
					$count["nTopCount"] = $count["nTopCount"]+count($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]);
			}
		}

		if(!self::$crmMode)
        {
			$arFilter = static::prepareFilter($arFilter);
			$timeUpdate = isset($arFilter[">=DATE_UPDATE"])? $arFilter[">=DATE_UPDATE"]:'';
            $lastDateUpdateOrders = static::getLastOrderExported($timeUpdate);
        }

		self::$arResultStat = array(
			"ORDERS" => 0,
			"CONTACTS" => 0,
			"COMPANIES" => 0,
		);

		$bExportFromCrm = self::isExportFromCRM($arOptions);

		$arStore = self::getCatalogStore();
		$arMeasures = self::getCatalogMeasure();
		self::setCatalogMeasure($arMeasures);
		$arAgent = self::getSaleExport();

		if (self::$crmMode)
		{
			self::setXmlEncoding("UTF-8");
			$arCharSets = self::getSite();
		}

		echo self::getXmlRootName();?>

<<?=CSaleExport::getTagName("SALE_EXPORT_COM_INFORMATION")?> <?=self::getCmrXmlRootNameParams()?>><?

		$arOrder = array("DATE_UPDATE" => "ASC", "ID"=>"ASC");

		$arSelect = array(
			"ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID",
			"PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE",
			"SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION",
			"ADDITIONAL_INFO",
			"COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID", "ACCOUNT_NUMBER", "SUM_PAID", "DELIVERY_DOC_DATE", "DELIVERY_DOC_NUM", "TRACKING_NUMBER", "STORE_ID",
			"ID_1C", "VERSION",
			"USER.XML_ID", "USER.TIMESTAMP_X"
		);

		$bCrmModuleIncluded = false;
		if ($bExportFromCrm)
		{
			$arSelect[] = "UF_COMPANY_ID";
			$arSelect[] = "UF_CONTACT_ID";
			if (IsModuleInstalled("crm") && CModule::IncludeModule("crm"))
				$bCrmModuleIncluded = true;
		}

		$arFilter['RUNNING'] = 'N';

		$filter = array(
			'select' => $arSelect,
			'filter' => $arFilter,
			'order'  => $arOrder,
			'limit'  => $count["nTopCount"]
		);

		if (!empty($arOptions['RUNTIME']) && is_array($arOptions['RUNTIME']))
		{
			$filter['runtime'] = $arOptions['RUNTIME'];
		}

		$entity = static::getParentEntityTable();

        $dbOrderList = $entity::getList($filter);

		while($arOrder = $dbOrderList->Fetch())
		{
		    if(!self::$crmMode && static::exportedLastExport($arOrder, $lastDateUpdateOrders))
            {
				continue;
            }

		    static::$documentsToLog = array();
			$contentToLog = '';

		    $order = static::load($arOrder['ID']);
			$arOrder['DATE_STATUS'] = $arOrder['DATE_STATUS']->toString();
		    $arOrder['DATE_INSERT'] = $arOrder['DATE_INSERT']->toString();
		    $arOrder['DATE_UPDATE'] = $arOrder['DATE_UPDATE']->toString();

			foreach($arOrder as $field=>$value)
			{
			    if(self::isFormattedDateFields('Order', $field))
			    {
			        $arOrder[$field] = self::getFormatDate($value);
			    }
			}

			if (self::$crmMode)
			{
				if(self::getVersionSchema() > self::DEFAULT_VERSION && is_array($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && in_array($arOrder["ID"], $_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && empty($arFilter["ID"]))
					continue;
				ob_start();
			}

			self::$arResultStat["ORDERS"]++;

			$agentParams = (array_key_exists($arOrder["PERSON_TYPE_ID"], $arAgent) ? $arAgent[$arOrder["PERSON_TYPE_ID"]] : array() );

            $arResultPayment = self::getPayment($arOrder);
            $paySystems = $arResultPayment['paySystems'];
            $arPayment = $arResultPayment['payment'];

			$arResultShipment = self::getShipment($arOrder);
			$arShipment = $arResultShipment['shipment'];
			$delivery = $arResultShipment['deliveryServices'];

			self::setDeliveryAddress('');
			self::setSiteNameByOrder($arOrder);

			$arProp = self::prepareSaleProperty($arOrder, $bExportFromCrm, $bCrmModuleIncluded, $paySystems, $delivery, $locationStreetPropertyValue, $order);
			$agent = self::prepareSalePropertyRekv($order, $agentParams, $arProp, $locationStreetPropertyValue);

			$arOrderTax = CSaleExport::getOrderTax($order);
			$xmlResult['OrderTax'] = self::getXMLOrderTax($arOrderTax);
			self::setOrderSumTaxMoney(self::getOrderSumTaxMoney($arOrderTax));

			$xmlResult['Contragents'] = self::getXmlContragents($arOrder, $arProp, $agent, $bExportFromCrm ? array("EXPORT_FROM_CRM" => "Y") : array());
			$xmlResult['OrderDiscount'] = self::getXmlOrderDiscount($arOrder);
			$xmlResult['SaleStoreList'] = $arStore;
			$xmlResult['ShipmentsStoreList'] = self::getShipmentsStoreList($order);
			// self::getXmlSaleStoreBasket($arOrder,$arStore);
			$basketItems = self::getXmlBasketItems('Order', $arOrder, array('ORDER_ID'=>$arOrder['ID']), array(), $arShipment);

            $numberItems = array();
            foreach($basketItems['result'] as $basketItem)
            {
                $number = self::getNumberBasketPosition($basketItem["ID"]);

                if(in_array($number, $numberItems))
                {
					$r = new \Bitrix\Sale\Result();
					$r->addWarning(new \Bitrix\Main\Error(GetMessage("SALE_EXPORT_REASON_MARKED_BASKET_PROPERTY").'1C_Exchange:Order.export.basket.properties', 'SALE_EXPORT_REASON_MARKED_BASKET_PROPERTY'));
					$entityMarker::addMarker($order, $order, $r);
					$order->setField('MARKED','Y');
					$order->setField('DATE_UPDATE',null);
					$order->save();
                    break;
                }
                else
                {
                    $numberItems[] = $number;
                }
            }

			$xmlResult['BasketItems'] = $basketItems['outputXML'];
			$xmlResult['SaleProperties'] = self::getXmlSaleProperties($arOrder, $arShipment, $arPayment, $agent, $agentParams, $bExportFromCrm);
			$xmlResult['RekvProperties'] = self::getXmlRekvProperties($agent, $agentParams);


			if(self::getVersionSchema() >= self::CONTAINER_VERSION)
            {
                ob_start();
				echo '<'.CSaleExport::getTagName("SALE_EXPORT_CONTAINER").'>';
            }

			self::OutputXmlDocument('Order', $xmlResult, $arOrder);

			if(self::getVersionSchema() >= self::PARTIAL_VERSION)
			{
				self::OutputXmlDocumentsByType('Payment',$xmlResult, $arOrder, $arPayment, $order, $agentParams, $arProp, $locationStreetPropertyValue);
				self::OutputXmlDocumentsByType('Shipment',$xmlResult, $arOrder, $arShipment, $order, $agentParams, $arProp, $locationStreetPropertyValue);
				self::OutputXmlDocumentRemove('Shipment',$arOrder);
			}

			if(self::getVersionSchema() >= self::CONTAINER_VERSION)
			{
				echo '</'.CSaleExport::getTagName("SALE_EXPORT_CONTAINER").'>';
				$contentToLog = ob_get_contents();
				ob_end_clean();
				echo $contentToLog;
			}

			if (self::$crmMode)
			{
				$c = ob_get_clean();
				$c = CharsetConverter::ConvertCharset($c, $arCharSets[$arOrder["LID"]], "utf-8");
				echo $c;
				$_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix][] = $arOrder["ID"];
			}
			else
			{
				static::saveExportParams($arOrder);
			}

			ksort(static::$documentsToLog);

			foreach (static::$documentsToLog as $entityTypeId=>$documentsToLog)
			{
				foreach ($documentsToLog as $documentToLog)
				{
					$fieldToLog = $documentToLog;
					$fieldToLog['ENTITY_TYPE_ID'] = $entityTypeId;
					if(self::getVersionSchema() >= self::CONTAINER_VERSION)
					{
						if($entityTypeId == \Bitrix\Sale\Exchange\EntityType::ORDER )
							$fieldToLog['MESSAGE'] = $contentToLog;
					}
					static::log($fieldToLog);
				}
			}

			if(self::checkTimeIsOver($time_limit, $end_time))
			{
				break;
			}
		}
		?>

	</<?=CSaleExport::getTagName("SALE_EXPORT_COM_INFORMATION")?>><?

		return self::$arResultStat;
	}

	function UnZip($file_name, $last_zip_entry = "", $interval = 0)
	{
		global $APPLICATION;
		$start_time = time();

		$io = CBXVirtualIo::GetInstance();

		//Function and securioty checks
		if(!function_exists("zip_open"))
			return false;
		$dir_name = mb_substr($file_name, 0, mb_strrpos($file_name, "/") + 1);
		if(mb_strlen($dir_name) <= mb_strlen($_SERVER["DOCUMENT_ROOT"]))
			return false;

		$hZip = zip_open($file_name);
		if(!$hZip)
			return false;
		//Skip from last step
		if($last_zip_entry)
		{
			while($entry = zip_read($hZip))
				if(zip_entry_name($entry) == $last_zip_entry)
					break;
		}

		$io = CBXVirtualIo::GetInstance();
		//Continue unzip
		while($entry = zip_read($hZip))
		{
			$entry_name = zip_entry_name($entry);
			//Check for directory
			zip_entry_open($hZip, $entry);
			if(zip_entry_filesize($entry))
			{

				$file_name = trim(str_replace("\\", "/", trim($entry_name)), "/");
				$file_name = $APPLICATION->ConvertCharset($file_name, "cp866", LANG_CHARSET);

				$bBadFile = HasScriptExtension($file_name)
					|| IsFileUnsafe($file_name)
					|| !$io->ValidatePathString("/".$file_name)
				;

				if(!$bBadFile)
				{
					$file_name =  $io->GetPhysicalName($dir_name.rel2abs("/", $file_name));
					CheckDirPath($file_name);
					$fout = fopen($file_name, "wb");
					if(!$fout)
						return false;
					while($data = zip_entry_read($entry, 102400))
					{
						$data_len = function_exists('mb_strlen')? mb_strlen($data, 'latin1') : mb_strlen($data);
						$result = fwrite($fout, $data);
						if($result !== $data_len)
							return false;
					}
				}
			}
			zip_entry_close($entry);

			//Jump to next step
			if($interval > 0 && (time()-$start_time) > ($interval))
			{
				zip_close($hZip);
				return $entry_name;
			}
		}
		zip_close($hZip);
		return true;
	}
	static function getOrderTax(\Bitrix\Sale\Order $order)
	{
		$arResult = array();
		if($order->getId()>0)
		{
			$tax = $order->getTax();
			$arResult = $tax->getTaxList();
		}

		return $arResult;
	}

	static function getOrderSumTaxMoney($arOrderTaxAll)
	{
		$orderTax = 0;
		if(is_array($arOrderTaxAll) && count($arOrderTaxAll)>0)
		{
			foreach ($arOrderTaxAll as $arOrderTax )
			{
				$arOrderTax["VALUE_MONEY"] = roundEx($arOrderTax["VALUE_MONEY"], 2);
				$orderTax += $arOrderTax["VALUE_MONEY"];
			}
		}
		return $orderTax;
	}

	static function getXmlOrderTax($arOrderTaxAll)
	{
		$strResult = "";
		if(is_array($arOrderTaxAll) && count($arOrderTaxAll)>0)
		{
			$orderTax = 0;
			$strResult .= "<".CSaleExport::getTagName("SALE_EXPORT_TAXES").">";
			foreach ($arOrderTaxAll as $arOrderTax )
			{
				$arOrderTax["VALUE_MONEY"] = roundEx($arOrderTax["VALUE_MONEY"], 2);
				$orderTax += $arOrderTax["VALUE_MONEY"];

				$strResult .= "<".CSaleExport::getTagName("SALE_EXPORT_TAX").">".
					"<".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">".htmlspecialcharsbx($arOrderTax["TAX_NAME"])."</".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">".
					"<".CSaleExport::getTagName("SALE_EXPORT_IN_PRICE").">".(($arOrderTax["IS_IN_PRICE"]=="Y") ? "true" : "false")."</".CSaleExport::getTagName("SALE_EXPORT_IN_PRICE").">".
					"<".CSaleExport::getTagName("SALE_EXPORT_AMOUNT").">".$arOrderTax["VALUE_MONEY"]."</".CSaleExport::getTagName("SALE_EXPORT_AMOUNT").">".
				"</".CSaleExport::getTagName("SALE_EXPORT_TAX").">";
			}
			$strResult .= "</".CSaleExport::getTagName("SALE_EXPORT_TAXES").">";
		}

		return $strResult;
	}
	static function getXmlOrderDiscount($arOrder)
	{
		$strResult='';
		if(DoubleVal($arOrder["DISCOUNT_VALUE"]) > 0)
		{
			$strResult = "<".CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS").">
						<".CSaleExport::getTagName("SALE_EXPORT_DISCOUNT").">
							<".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">".CSaleExport::getTagName("SALE_EXPORT_ORDER_DISCOUNT")."</".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">
							<".CSaleExport::getTagName("SALE_EXPORT_AMOUNT").">".$arOrder["DISCOUNT_VALUE"]."</".CSaleExport::getTagName("SALE_EXPORT_AMOUNT").">
							<".CSaleExport::getTagName("SALE_EXPORT_IN_PRICE").">false</".CSaleExport::getTagName("SALE_EXPORT_IN_PRICE").">
						</".CSaleExport::getTagName("SALE_EXPORT_DISCOUNT").">
					</".CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS").">";
		}
		return $strResult;
	}

   static function getShipmentsStoreList(Bitrix\Sale\Order $order)
    {
        $result = array();

        $shipmentCollection = $order->getShipmentCollection();

        if($shipmentCollection->count()>0)
        {
            /** @var \Bitrix\Sale\Shipment $shipment */
            foreach($shipmentCollection as $shipment)
            {
			    if ($shipment->isSystem())
				    continue;

                $storeId = $shipment->getStoreId();

                if($storeId>0)
                    $result[$shipment->getId()] = $storeId;
            }
        }
        return $result;
    }

	static function getXmlSaleStore($arShipmentStore, $arStore)
	{
		$bufer = '';
		if(count($arShipmentStore)>0)
		{
		    ob_start();

    	    foreach($arShipmentStore as $shipmentStoreId)
		    {
		        if(intval($shipmentStoreId) > 0 && !empty($arStore[$shipmentStoreId]))
                {
                    ?>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_STORY")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=$arStore[$shipmentStoreId]["XML_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arStore[$shipmentStoreId]["TITLE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>

                            <<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialcharsbx($arStore[$shipmentStoreId]["ADDRESS"])?></<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
                                    <<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_STREET")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
                                    <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arStore[$shipmentStoreId]["ADDRESS"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                                </<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
                            </<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>

                            <<?=CSaleExport::getTagName("SALE_EXPORT_CONTACTS")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
                                    <<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=(self::getVersionSchema() > self::DEFAULT_VERSION ? CSaleExport::getTagName("SALE_EXPORT_WORK_PHONE_NEW") : CSaleExport::getTagName("SALE_EXPORT_WORK_PHONE"))?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
                                    <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arStore[$shipmentStoreId]["PHONE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                                </<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
                            </<?=CSaleExport::getTagName("SALE_EXPORT_CONTACTS")?>>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_STORY")?>>
                    <?
                }
		    }
		    $bufer = ob_get_clean();
		}
		if($bufer <> '')
            $bufer = "<".CSaleExport::getTagName("SALE_EXPORT_STORIES").">".$bufer."</".CSaleExport::getTagName("SALE_EXPORT_STORIES").">";

		return $bufer;
	}
	function getXmlSaleStoreBasket($arOrder,$arStore)
	{
		ob_start();
		$storeBasket = "
			<".CSaleExport::getTagName("SALE_EXPORT_STORIES").">
				<".CSaleExport::getTagName("SALE_EXPORT_STORY").">
					<".CSaleExport::getTagName("SALE_EXPORT_ID").">".$arStore[$arOrder["STORE_ID"]]["XML_ID"]."</".CSaleExport::getTagName("SALE_EXPORT_ID").">
					<".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">".htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["TITLE"])."</".CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME").">
				</".CSaleExport::getTagName("SALE_EXPORT_STORY").">
			</".CSaleExport::getTagName("SALE_EXPORT_STORIES").">
			";
		//$bufer = ob_get_clean();
		ob_get_clean();
		return $storeBasket;
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public static function normalizeExternalCode($code)
    {
		$xml_id = $code;
		list($productXmlId, $offerXmlId) = explode("#", $xml_id, 2);
		if ($productXmlId === $offerXmlId)
			$xml_id = $productXmlId;

		return $xml_id;
	}
	
	protected static function outputXmlMarkingCodeGroup($arBasket)
	{
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_CODE_GROUP")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>><?=$arBasket["MARKING_CODE_GROUP"]?></<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>>
		</<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_CODE_GROUP")?>>
		<?
	}
	
	protected static function outputXmlMarkingCode($shipmentItemId, $order)
	{
		$list = [];
		if($order instanceof \Bitrix\Sale\Order)
		{
			$shipmentCollection = $order->getShipmentCollection();

			if($shipmentCollection->count()>0)
			{
				/** @var \Bitrix\Sale\Shipment $shipment */
				foreach($shipmentCollection as $shipment)
				{
					if ($shipment->isSystem())
						continue;

					$shipmentItemCollection = $shipment->getShipmentItemCollection();

					/** @var ShipmentItem $shipmentItem */
					foreach ($shipmentItemCollection as $shipmentItem)
					{
						if($shipmentItem->getId() == $shipmentItemId)
						{
							$basketItem = $shipmentItem->getBasketItem();
							if ($basketItem->isSupportedMarkingCode())
							{								
								$storeCollection = $shipmentItem->getShipmentItemStoreCollection();
								
								for ($i = $shipmentItem->getQuantity(); $i > 0; $i--)
								{
									$markingCode = '';

									/** @var ShipmentItemStore $itemStore */
									if ($itemStore = $storeCollection->current())
									{
										$code = $itemStore->getMarkingCode();
										if($code <> '')
										{
											$list[] = $code;
										}											
										
										$storeCollection->next();
									}
								}
							}
							break 2;
						}
					}
				}
			}
		}
		if(count($list)>0)
		{			
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_MARKINGS")?>>
			<?
				foreach($list as $code)
				{
					?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_MARKING")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_BARCODE")?>><?=$code?></<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_BARCODE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_MARKING")?>>
			<?
				}
			?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_MARKING_MARKINGS")?>>
		<?			
		}
	}

	protected static function outputXmlUnit($arBasket)
	{
		if(self::getVersionSchema() > self::DEFAULT_VERSION)
		{
			if(intval($arBasket["MEASURE_CODE"]) <= 0)
				$arBasket["MEASURE_CODE"] = 796;
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_UNIT")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>><?=$arBasket["MEASURE_CODE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>><?=htmlspecialcharsbx(self::$measures[$arBasket["MEASURE_CODE"]]['MEASURE_TITLE'])?></<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_UNIT")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_KOEF")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_KOEF")?>>
			<?
		}
		else
		{
			if($arBasket["MEASURE_CODE"] == 796)
            {
				?>
                <<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?> <?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>="796" <?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=CSaleExport::getTagName("SALE_EXPORT_SHTUKA")?>" <?=CSaleExport::getTagName("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=CSaleExport::getTagName("SALE_EXPORT_RCE")?>"><?=CSaleExport::getTagName("SALE_EXPORT_SHT")?></<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?>>
				<?
            }
            else
            {
				?>
                <<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?> <?=CSaleExport::getTagName("SALE_EXPORT_CODE")?>="<?=$arBasket["MEASURE_CODE"]?>" <?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=htmlspecialcharsbx(self::$measures[$arBasket["MEASURE_CODE"]]['MEASURE_TITLE'])?>" <?=CSaleExport::getTagName("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=self::$measures[$arBasket["MEASURE_CODE"]]["SYMBOL_LETTER_INTL"]?>"><?=self::$measures[$arBasket["MEASURE_CODE"]]["SYMBOL_RUS"]?></<?=CSaleExport::getTagName("SALE_EXPORT_BASE_UNIT")?>>
				<?
            }

		}
	}

	static function getXmlBasketItems($type, $arOrder, $arFilter, $arSelect=array(), $arShipment=array(), $order=null)
	{
		$result = array();
		$entity = static::getBasketTable();

		ob_start();
		?><<?=CSaleExport::getTagName("SALE_EXPORT_ITEMS")?>><?

		$select = array("ID", "NOTES", "PRODUCT_XML_ID", "CATALOG_XML_ID", "NAME", "PRICE", "QUANTITY", "DISCOUNT_PRICE", "VAT_RATE", "MEASURE_CODE", "SET_PARENT_ID", "TYPE", "VAT_INCLUDED", "MARKING_CODE_GROUP");
		if(count($arSelect)>0)
		    $select = array_merge($arSelect, $select);

		$dbBasket = $entity::getList(array(
			'select' => $select,
			'filter' => $arFilter,
			'order' => array("NAME" => "ASC")
		));

		$basketSum = 0;
		$priceType = "";
		$bVat = false;
		$vatRate = 0;
		$vatSum = 0;
		while ($arBasket = $dbBasket->fetch())
		{
			if(strval($arBasket['TYPE'])!='' && $arBasket['TYPE']== \Bitrix\Sale\BasketItem::TYPE_SET)
			    continue;

			$result[] = $arBasket;

			if($priceType == '')
				$priceType = $arBasket["NOTES"];
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=htmlspecialcharsbx(static::normalizeExternalCode($arBasket["PRODUCT_XML_ID"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_CATALOG_ID")?>><?=htmlspecialcharsbx($arBasket["CATALOG_XML_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_CATALOG_ID")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arBasket["NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<?

            	static::outputXmlUnit($arBasket);
            	
				if($type == 'Order')
				{
					static::outputXmlMarkingCodeGroup($arBasket);
				}
				elseif($type == 'Shipment')
				{	
					static::outputXmlMarkingCode($arBasket['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_ID'], $order);					
				}
				
				if(DoubleVal($arBasket["DISCOUNT_PRICE"]) > 0)
			 	{
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNT")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_DISCOUNT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["DISCOUNT_PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNT")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_DISCOUNTS")?>>
					<?
				}
				?>
				<?if(self::getVersionSchema() >= self::PARTIAL_VERSION && $type == 'Shipment')
				{?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>><?=$arBasket["SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["PRICE"]*$arBasket["SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?}
				else{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_PRICE_PER_ITEM")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>><?=$arBasket["QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_QUANTITY")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$arBasket["PRICE"]*$arBasket["QUANTITY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?}?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>

					<?
					$number = self::getNumberBasketPosition($arBasket["ID"]);
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_BASKET_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$number?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<?
					$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arBasket["ID"]), false, false, array("NAME", "SORT", "VALUE", "CODE"));
					while($arPropBasket = $dbProp->Fetch())
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE_BASKET")?>#<?=($arPropBasket["CODE"] != "" ? $arPropBasket["CODE"]:htmlspecialcharsbx($arPropBasket["NAME"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPropBasket["VALUE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					?>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
				<?if(DoubleVal($arBasket["VAT_RATE"]) > 0)
				{
					$bVat = true;
					$vatRate = DoubleVal($arBasket["VAT_RATE"]);
					$basketVatSum = (($arBasket["PRICE"] / ($arBasket["VAT_RATE"]+1)) * $arBasket["VAT_RATE"]);
					$vatSum += roundEx($basketVatSum * $arBasket["QUANTITY"], 2);
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>><?=$arBasket["VAT_RATE"] * 100?></<?=CSaleExport::getTagName("SALE_EXPORT_RATE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_TAX_RATES")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_VAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>><?=$arBasket["VAT_INCLUDED"]=="Y"?'true':'false'?></<?=CSaleExport::getTagName("SALE_EXPORT_IN_PRICE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=roundEx($basketVatSum, 2)?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_TAX")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_TAXES")?>>
					<?
				}
				?>
				<?//=self::getXmlSaleStoreBasket($arOrder,$arStore)?>
			</<?=CSaleExport::getTagName("SALE_EXPORT_ITEM")?>>
			<?
			$basketSum += $arBasket["PRICE"]*$arBasket["QUANTITY"];
		}

        if(self::getVersionSchema() >= self::PARTIAL_VERSION)
        {
            if(count($arShipment)>0)
            {
                foreach($arShipment as $shipment)
                {
                    self::getOrderDeliveryItem($shipment, $bVat, $vatRate, $vatSum);
                }
            }
        }
        else
		    self::getOrderDeliveryItem($arOrder, $bVat, $vatRate, $vatSum);

		?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_ITEMS")?>><?

		$bufer = ob_get_clean();
		return array('outputXML'=>$bufer,'result'=>$result);
	}
	static function getXmlSaleProperties($arOrder, $arShipment, $arPayment, $agent, $agentParams, $bExportFromCrm)
	{
		ob_start();

        $arShipment = $arShipment[0];
        $arPayment = $arPayment[0];

		?><<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>><?
		if($arOrder["DATE_PAYED"] <> '')
		{
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_PAYED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}

		if(self::getVersionSchema() < self::PARTIAL_VERSION || $bExportFromCrm) // #version# < 2.10      ? || $bExportFromCrm
		{
			if($arPayment["PAY_VOUCHER_NUM"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_VOUCHER_NUM"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			if($arShipment["DATE_ALLOW_DELIVERY"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arShipment["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
		}
		else
		{
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY_LAST")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>><?

		}

		if($arShipment["DELIVERY_ID"] <> '')
		{
			?>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_SERVICE")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arShipment["DELIVERY_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arShipment["DELIVERY_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}

		if(intval($arPayment["PAY_SYSTEM_ID"])>0)
		{
			?>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_SYSTEM_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_SYSTEM_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arPayment["PAY_SYSTEM_ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?
		}
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ORDER_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["PAYED"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
		<?
		if(self::getVersionSchema() < self::PARTIAL_VERSION || $bExportFromCrm)
		{
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arShipment["ALLOW_DELIVERY"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>><?
		}
		?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["CANCELED"]=="Y")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_FINAL_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($arOrder["STATUS_ID"]=="F")?"true":"false";?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_ORDER_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?$arStatus = static::getStatusInfoByStatusId($arOrder["STATUS_ID"]); echo htmlspecialcharsbx("[".$arOrder["STATUS_ID"]."] ".$arStatus["NAME"]);?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_STATUS_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["STATUS_ID"]);?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?if($arOrder["DATE_CANCELED"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_CANCEL")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["REASON_CANCELED"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			if($arOrder["DATE_STATUS"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_STATUS"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			if($arOrder["USER_DESCRIPTION"] <> '')
			{
				?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_USER_DESCRIPTION")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["USER_DESCRIPTION"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
				</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<?
			}
			self::OutputXmlSiteName($arOrder);

			self::OutputXmlRekvProperties($agent, $agentParams);

			self::OutputXmlDeliveryAddress();

			?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
	<?
	$bufer = ob_get_clean();
	return $bufer;
}

    static function getXmlRekvProperties($agent, $agentParams)
    {
		ob_start();
		self::OutputXmlRekvProperties($agent, $agentParams);
		$bufer = ob_get_clean();
		return $bufer;
    }

    static function OutputXmlRekvProperties($agent, $agentParams)
    {
		if(!empty($agent["REKV"]))
		{
			foreach($agent["REKV"] as $k => $v)
			{
				if($agentParams[$k]["NAME"] <> '' && $v <> '')
				{
					?>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($agentParams[$k]["NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($v)?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                    </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
					<?
				}
			}
		}
    }

	static function getXmlContragents($arOrder = array(), $arProp = array(), $agent = array(), $arOptions = array())
	{
		ob_start();
		self::ExportContragents($arOrder, $arProp, $agent, $arOptions);
		$ec_bufer = ob_get_clean();
		return $ec_bufer;
	}
	static function OutputXmlDocumentsByType($typeDocument, $xmlResult, $arOrder, $documents, \Bitrix\Sale\Order $order=null, $agentParams, $arProp, $locationStreetPropertyValue)
	{
		if(is_array($documents) && count($documents)>0)
		{
			foreach($documents as $document)
			{
				$document['LID'] = $arOrder['LID'];
				$document['VERSION'] = $arOrder['VERSION'];

				switch($typeDocument)
				{
					case 'Payment':

					    if($document['DATE_BILL']=='')
					        $document['DATE_BILL'] = $arOrder['DATE_INSERT_FORMAT'];

						if(($paymentCollection = $order->getPaymentCollection()))
                        {
							foreach($paymentCollection as $payment)
							{
								if($payment->getId() == $document['ID'])
								{
									$agent = self::prepareSalePropertyRekv($payment, $agentParams, $arProp, $locationStreetPropertyValue);
									$xmlResult['RekvProperties'] = self::getXmlRekvProperties($agent, $agentParams);
									break;
								}
							}
                        }

						self::OutputXmlDocument('Payment',$xmlResult, $document);
					break;
					case 'Shipment':

						if(($shipmentCollection = $order->getShipmentCollection()))
                        {
							foreach($shipmentCollection as $shipment)
							{
								if($shipment->getId() == $document['ID'])
								{
									$agent = self::prepareSalePropertyRekv($shipment, $agentParams, $arProp, $locationStreetPropertyValue);
									$xmlResult['RekvProperties'] = self::getXmlRekvProperties($agent, $agentParams);
									break;
								}
							}
                        }

					    $basketItems = self::getXmlBasketItems('Shipment', $document, array(
							'ORDER_ID'=>$document['ORDER_ID'],
							'SHIPMENT_ITEM.ORDER_DELIVERY_ID'=>$document['ID'],
							),
							array(
							'SHIPMENT_ITEM.QUANTITY',
							'SHIPMENT_ITEM.ID'
							),
							array(
							    array('PRICE_DELIVERY'=>$document['PRICE_DELIVERY'])
							),
							$order
						);
						$xmlResult['BasketItems'] = $basketItems['outputXML'];
                        $document['BasketResult'] = $basketItems['result'];

						self::OutputXmlDocument('Shipment',$xmlResult, $document);
					break;
				}
			}
		}
	}
	static function OutputXmlSiteName($arOrder)
	{
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_SITE_NAME")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>[<?=$arOrder["LID"]?>] <?=htmlspecialcharsbx(self::$siteNameByOrder)?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
		</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
		<?
	}
	static function OutputXmlDeliveryAddress()
	{
		if(self::getDeliveryAddress() <> '')
		{
			?>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_ADDRESS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx(self::getDeliveryAddress())?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>

			<?
		}
	}
	static function OutputXmlDocumentRemove($typeDocument, $document)
    {
        global $DB;
		$entity = static::getEntityChangeTable();

        switch($typeDocument)
        {
            case 'Shipment':
                if($document['ID']>0)
                {
                    $result = $entity::getList(
                            array(
                                'filter'=>array('ORDER_ID'=>$document['ID'], 'ENTITY' => 'SHIPMENT', 'TYPE' => 'SHIPMENT_REMOVED'),
                                'order'=>array('ID'=>'DESC')
                            )
                    );

                    while($resultChange = $result->fetch())
                    {?>
                       <<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=$resultChange["ENTITY_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>><?=$resultChange["ENTITY_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($resultChange["DATE_CREATE"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_SHIPMENT")?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SELLER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(mb_substr($document["CURRENCY"], 0, 3))?></<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>><?=$resultChange['ORDER_ID']?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_REMOVED")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_REMOVED")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>></<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENTS")?>></<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENTS")?>>
                            <<?=GetMessage("CC_BSC1_ITEMS")?>></<?=GetMessage("CC_BSC1_ITEMS")?>>
                       </<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>>
                    <?}
                }

            break;
        }
    }
	static function OutputXmlDocument($typeDocument,$xmlResult, $document=array())
	{
		global $DB;
		?>
		<?ob_start();?>
	<<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>><?
		switch($typeDocument)
		{
			case 'Order':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=$document["ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>><?=self::getAccountNumberShopPrefix();?><?=$document["ACCOUNT_NUMBER"]?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_INSERT_FORMAT"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_ORDER")?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SELLER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(((self::$currency <> '')? mb_substr(self::$currency, 0, 3) : mb_substr($document["CURRENCY"], 0, 3)))?></<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$document["PRICE"]?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
				<?
				if(self::getVersionSchema() > self::DEFAULT_VERSION)
				{
					?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?=(intval($document["VERSION"]) > 0 ? $document["VERSION"] : 0)?></<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?
					if($document["ID_1C"] <> '')
					{
						?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID_1C")?>><?=htmlspecialcharsbx($document["ID_1C"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID_1C")?>><?
					}
				}
				if (self::$crmMode)
				{
			?><DateUpdate><?=$DB->FormatDate($document["DATE_UPDATE"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS");?></DateUpdate><?
				}
				echo $xmlResult['Contragents'];
			?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_INSERT_FORMAT"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>><?=htmlspecialcharsbx(self::toText($document["COMMENTS"]))?></<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>>
			<?	echo $xmlResult['OrderTax'];
				echo $xmlResult['OrderDiscount'];
				echo self::getXmlSaleStore(array_unique($xmlResult['ShipmentsStoreList'], SORT_NUMERIC), $xmlResult['SaleStoreList']);
				//$storeBasket = self::getXmlSaleStoreBasket($document,$arStore);
				echo $xmlResult['BasketItems'];
				echo $xmlResult['SaleProperties'];
			break;

			case 'Payment':
			case 'Shipment':
			?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=($document["ID_1C"] <> '' ? $document["ID_1C"]:$document["ID"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>><?=$document["ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER")?>>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_BILL"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_PAYMENT_".\Bitrix\Sale\PaySystem\Manager::getPsType($document['PAY_SYSTEM_ID']))?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<?		break;
				case 'Shipment':?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($document["DATE_INSERT"], CSite::GetDateFormat("FULL"), "YYYY-MM-DD")?></<?=CSaleExport::getTagName("SALE_EXPORT_DATE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_ITEM_SHIPMENT")?></<?=CSaleExport::getTagName("SALE_EXPORT_HOZ_OPERATION")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SELLER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(((self::$currency <> '')? mb_substr(self::$currency, 0, 3) : mb_substr($document["CURRENCY"], 0, 3)))?></<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=CSaleExport::getTagName("SALE_EXPORT_CURRENCY_RATE")?>>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$document['SUM']?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
		<?		break;
				case 'Shipment':
                    $price = 0;
                    if(count($document['BasketResult'])>0)
                    {
                        foreach($document['BasketResult'] as $basketItem)
                        {
                            $price = $price + $basketItem['PRICE'] * $basketItem['SALE_INTERNALS_BASKET_SHIPMENT_ITEM_QUANTITY'];
                        }
                    }
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>><?=$price+intval($document['PRICE_DELIVERY'])?></<?=CSaleExport::getTagName("SALE_EXPORT_AMOUNT")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>><?=(intval($document["VERSION"]) > 0 ? $document["VERSION"] : 0)?></<?=CSaleExport::getTagName("SALE_EXPORT_VERSION")?>>
		<<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>><?=$document['ORDER_ID']?></<?=CSaleExport::getTagName("SALE_EXPORT_NUMBER_BASE")?>>
		<?echo $xmlResult['Contragents'];?>
		<?	switch($typeDocument)
			{
				case 'Payment':
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_BILL"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<?		break;
				case 'Shipment':?>
				<?=$xmlResult['OrderTax'];?>
				<?
				if(isset($xmlResult['ShipmentsStoreList'][$document["ID"]]))
				{
				    $storId = $xmlResult['ShipmentsStoreList'][$document["ID"]];
				    echo self::getXmlSaleStore(array($document["ID"]=>$storId), $xmlResult['SaleStoreList']);
				}?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($document["DATE_INSERT"], CSite::GetDateFormat("FULL"), "HH:MI:SS")?></<?=CSaleExport::getTagName("SALE_EXPORT_TIME")?>>
		<?		break;
			}?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>><?=htmlspecialcharsbx($document["COMMENTS"])?></<?=CSaleExport::getTagName("SALE_EXPORT_COMMENTS")?>>

		<?	switch($typeDocument)
			{
				case 'Payment':

					$checkData = false;
				    $cashBoxOneCId = self::getCashBoxOneCId();
					if(isset($cashBoxOneCId) && $cashBoxOneCId>0)
                    {
                        $checks = \Bitrix\Sale\Cashbox\CheckManager::getPrintableChecks(array($cashBoxOneCId), array($document['ORDER_ID']));
						foreach($checks as $checkId=>$check)
                        {
							if($check['PAYMENT_ID']==$document["ID"])
                            {
								$checkData = $check;
                                break;
                            }
                        }
                    }
		?>
        <?
             if($checkData)
             {
        ?>
                 <<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECKS")?>>
                    <<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECK")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=($checkData['ID'])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                        <<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUES")?>>
                            <<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUE")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>PRINT_CHECK</<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
                                <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>true</<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
                            </<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUE")?>>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_PROP_VALUES")?>>
                    </<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECK")?>>
                 </<?=CSaleExport::getTagName("SALE_EXPORT_CASHBOX_CHECKS")?>>
        <?
             }
        ?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_PAID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["CANCELED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_SYSTEM_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_SYSTEM")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_SYSTEM_NAME"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_PAID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["PAID"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_RETURN")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["IS_RETURN"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PAY_RETURN_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["PAY_RETURN_COMMENT"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<?self::OutputXmlSiteName($document);?>
            <?if(isset($xmlResult['RekvProperties']) && $xmlResult['RekvProperties'] <> '') echo $xmlResult['RekvProperties'];?>
		</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<?	break;

				case 'Shipment':
			?>

			<?
			echo $xmlResult['BasketItems'];
			?>

		<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
		    <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_PRICE_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["PRICE_DELIVERY"] <> ''? $document["PRICE_DELIVERY"]:"0.0000")?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_ALLOW_DELIVERY"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_LOCATION")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_LOCATION"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_STATUS")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["STATUS_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["DEDUCTED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DATE_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_DEDUCTED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_REASON_UNDO_DEDUCTED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_UNDO_DEDUCTED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_RESERVED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["RESERVED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_ID")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_ID"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DELIVERY_NAME"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_CANCELED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=($document["CANCELED"]=='Y'? 'true':'false')?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_DELIVERY_DATE_CANCEL")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["DATE_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_CANCELED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_REASON_MARKED")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["REASON_MARKED"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
            <<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=CSaleExport::getTagName("SALE_EXPORT_TRACKING_NUMBER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
                <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=$document["TRACKING_NUMBER"]?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
            </<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTY_VALUE")?>>

            <?self::OutputXmlSiteName($document);?>
			<?self::OutputXmlDeliveryAddress();?>

			<?if(isset($xmlResult['RekvProperties']) && $xmlResult['RekvProperties'] <> '') echo $xmlResult['RekvProperties'];?>
	</<?=CSaleExport::getTagName("SALE_EXPORT_PROPERTIES_VALUES")?>>
			<?
				break;
			}
		}
		?>
	</<?=CSaleExport::getTagName("SALE_EXPORT_DOCUMENT")?>>
		<?$c = ob_get_contents();
		ob_end_clean();
		echo $c;

		$typeEntityId = static::resolveEntityTypeId($typeDocument, $document);

		if(intval($typeEntityId)>0)
		{
			$filedsTolog = array(
				'ENTITY_ID' => $document["ID"],
				'XML_ID' => $document["ID_1C"]
			);

			if(self::getVersionSchema() < self::CONTAINER_VERSION)
			    $filedsTolog['MESSAGE'] = $c;

			switch ($typeDocument)
			{
                case 'Order':
					$filedsTolog['ENTITY_DATE_UPDATE'] = new \Bitrix\Main\Type\DateTime(\CAllDatabase::FormatDate($document['DATE_UPDATE']));
					if(self::getVersionSchema() >= self::CONTAINER_VERSION)
						$filedsTolog['PARENT_ID'] = $document["ID"];
                    break;
				case 'Payment':
				case 'Shipment':
				    $filedsTolog['OWNER_ENTITY_ID'] = $document["ORDER_ID"];

				    if(self::getVersionSchema() >= self::CONTAINER_VERSION)
				        $filedsTolog['PARENT_ID'] = $document["ORDER_ID"];
					break;
			}

			static::$documentsToLog[$typeEntityId][] = $filedsTolog;
		}
	}


	static function ExportContragents($arOrder = array(), $arProp = array(), $agent = array(), $arOptions = array())
	{
		$bExportFromCrm = (isset($arOptions["EXPORT_FROM_CRM"]) && $arOptions["EXPORT_FROM_CRM"] === "Y");
		?>
		<<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENTS")?>>
			<<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENT")?>>
		<?
		if ($bExportFromCrm):
			$xmlId = htmlspecialcharsbx(mb_substr($arProp["CRM"]["CLIENT_ID"]."#".$arProp["CRM"]["CLIENT"]["LOGIN"]."#".$arProp["CRM"]["CLIENT"]["LAST_NAME"]." ".$arProp["CRM"]["CLIENT"]["NAME"]." ".$arProp["CRM"]["CLIENT"]["SECOND_NAME"], 0, 40));
		else:
			$xmlId = static::getUserXmlId($arOrder, $arProp);
		endif; ?>
                <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=$xmlId?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>

				<<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($agent["AGENT_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
				<?
				self::setDeliveryAddress($agent["ADDRESS_FULL"]);

				//region address
				$address = "";
				if($agent["ADDRESS_FULL"] <> '')
				{
				    $address .= "<".CSaleExport::getTagName("SALE_EXPORT_PRESENTATION").">".htmlspecialcharsbx($agent["ADDRESS_FULL"])."</".CSaleExport::getTagName("SALE_EXPORT_PRESENTATION").">";
				}
				else
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")."></".CSaleExport::getTagName("SALE_EXPORT_PRESENTATION").">";
				}
				if($agent["INDEX"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_POST_CODE")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["INDEX"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["COUNTRY"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
									<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_COUNTRY")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
									<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["COUNTRY"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
								</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["REGION"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_REGION")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["REGION"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["STATE"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_STATE")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["STATE"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["TOWN"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_SMALL_CITY")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["TOWN"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["CITY"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_CITY")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["CITY"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["STREET"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_STREET")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["STREET"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["HOUSE"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_HOUSE")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["HOUSE"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["BUILDING"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_BUILDING")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["BUILDING"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				if($agent["FLAT"] <> '')
				{
					$address .= "<".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">
								<".CSaleExport::getTagName("SALE_EXPORT_TYPE").">".CSaleExport::getTagName("SALE_EXPORT_FLAT")."</".CSaleExport::getTagName("SALE_EXPORT_TYPE").">
								<".CSaleExport::getTagName("SALE_EXPORT_VALUE").">".htmlspecialcharsbx($agent["FLAT"])."</".CSaleExport::getTagName("SALE_EXPORT_VALUE").">
							</".CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD").">";
				}
				//endregion

				if($agent["IS_FIZ"]=="Y")
				{
					self::$arResultStat["CONTACTS"]++;
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME")?>><?=htmlspecialcharsbx($agent["FULL_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_FULL_NAME")?>>
					<?
					if($agent["SURNAME"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_SURNAME")?>><?=htmlspecialcharsbx($agent["SURNAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_SURNAME")?>><?
					}
					if($agent["NAME"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_NAME")?>><?=htmlspecialcharsbx($agent["NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_NAME")?>><?
					}
					if($agent["SECOND_NAME"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_MIDDLE_NAME")?>><?=htmlspecialcharsbx($agent["SECOND_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_MIDDLE_NAME")?>><?
					}
					if($agent["BIRTHDAY"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_BIRTHDAY")?>><?=htmlspecialcharsbx($agent["BIRTHDAY"])?></<?=CSaleExport::getTagName("SALE_EXPORT_BIRTHDAY")?>><?
					}
					if($agent["MALE"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_SEX")?>><?=htmlspecialcharsbx($agent["MALE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_SEX")?>><?
					}
					if($agent["INN"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_INN")?>><?=htmlspecialcharsbx($agent["INN"])?></<?=CSaleExport::getTagName("SALE_EXPORT_INN")?>><?
					}
					if($agent["KPP"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_KPP")?>><?=htmlspecialcharsbx($agent["KPP"])?></<?=CSaleExport::getTagName("SALE_EXPORT_KPP")?>><?
					}
					if($address <> '')
                    {
						?><<?=CSaleExport::getTagName("SALE_EXPORT_REGISTRATION_ADDRESS")?>>
						<?=$address?>
                        </<?=CSaleExport::getTagName("SALE_EXPORT_REGISTRATION_ADDRESS")?>>
						<?
                    }
				}
				else
				{
					self::$arResultStat["COMPANIES"]++;
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_OFICIAL_NAME")?>><?=htmlspecialcharsbx($agent["FULL_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OFICIAL_NAME")?>>
					<?
					if($address <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_UR_ADDRESS")?>>
						<?=$address?>
						</<?=CSaleExport::getTagName("SALE_EXPORT_UR_ADDRESS")?>><?
					}
					if($agent["INN"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_INN")?>><?=htmlspecialcharsbx($agent["INN"])?></<?=CSaleExport::getTagName("SALE_EXPORT_INN")?>><?
					}
					if($agent["KPP"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_KPP")?>><?=htmlspecialcharsbx($agent["KPP"])?></<?=CSaleExport::getTagName("SALE_EXPORT_KPP")?>><?
					}
					if($agent["EGRPO"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_EGRPO")?>><?=htmlspecialcharsbx($agent["EGRPO"])?></<?=CSaleExport::getTagName("SALE_EXPORT_EGRPO")?>><?
					}
					if($agent["OKVED"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKVED")?>><?=htmlspecialcharsbx($agent["OKVED"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKVED")?>><?
					}
					if($agent["OKDP"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKDP")?>><?=htmlspecialcharsbx($agent["OKDP"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKDP")?>><?
					}
					if($agent["OKOPF"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKOPF")?>><?=htmlspecialcharsbx($agent["OKOPF"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKOPF")?>><?
					}
					if($agent["OKFC"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKFC")?>><?=htmlspecialcharsbx($agent["OKFC"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKFC")?>><?
					}
					if($agent["OKPO"] <> '')
					{
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKPO")?>><?=htmlspecialcharsbx($agent["OKPO"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKPO")?>><?
						?><<?=CSaleExport::getTagName("SALE_EXPORT_OKPO_CODE")?>><?=htmlspecialcharsbx($agent["OKPO"])?></<?=CSaleExport::getTagName("SALE_EXPORT_OKPO_CODE")?>><?
					}
					if($agent["ACCOUNT_NUMBER"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_MONEY_ACCOUNTS")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_MONEY_ACCOUNT")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ACCOUNT_NUMBER")?>><?=htmlspecialcharsbx($agent["ACCOUNT_NUMBER"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ACCOUNT_NUMBER")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_BANK")?>>
						  <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($agent["B_NAME"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
						  <<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>
						    <<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialcharsbx($agent["B_ADDRESS_FULL"])?></<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>>
						<?
						if($agent["B_INDEX"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_POST_CODE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_INDEX"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_COUNTRY"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_COUNTRY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_COUNTRY"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_REGION"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_REGION")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_REGION"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_STATE"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_STATE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_STATE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_TOWN"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SMALL_CITY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_TOWN"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_CITY"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_CITY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_CITY"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_STREET"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_STREET")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_STREET"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_HOUSE"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_HOUSE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_HOUSE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_BUILDING"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_BUILDING")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_BUILDING"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						if($agent["B_FLAT"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_FLAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
							<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["B_FLAT"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
							</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>><?
						}
						?>
						    </<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>
						<?
						if($agent["B_BIK"] <> '')
						{
							?><<?=CSaleExport::getTagName("SALE_EXPORT_BIC")?>><?=htmlspecialcharsbx($agent["B_BIK"])?></<?=CSaleExport::getTagName("SALE_EXPORT_BIC")?>><?
						}
						?>
						</<?=CSaleExport::getTagName("SALE_EXPORT_BANK")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_MONEY_ACCOUNT")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_MONEY_ACCOUNTS")?>>
					<?
					}
				}

				if($agent["F_ADDRESS_FULL"] <> '')
				{
					self::setDeliveryAddress($agent["F_ADDRESS_FULL"]);
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>
					<<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialcharsbx($agent["F_ADDRESS_FULL"])?></<?=CSaleExport::getTagName("SALE_EXPORT_PRESENTATION")?>>
					<?
					if($agent["F_INDEX"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_POST_CODE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_INDEX"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_COUNTRY"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_COUNTRY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_COUNTRY"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_REGION"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_REGION")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_REGION"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_STATE"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_STATE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_STATE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_TOWN"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_SMALL_CITY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_TOWN"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_CITY"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_CITY")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_CITY"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_STREET"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_STREET")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_STREET"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_HOUSE"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_HOUSE")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_HOUSE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_BUILDING"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_BUILDING")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_BUILDING"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					if($agent["F_FLAT"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=CSaleExport::getTagName("SALE_EXPORT_FLAT")?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["F_FLAT"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS_FIELD")?>>
					<?
					}
					?>
					</<?=CSaleExport::getTagName("SALE_EXPORT_ADDRESS")?>>
				<?
				}

				if($agent["PHONE"] <> '' || $agent["EMAIL"] <> '')
				{
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_CONTACTS")?>>
					<?
					if($agent["PHONE"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
						  <<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=(self::getVersionSchema() > self::DEFAULT_VERSION ? CSaleExport::getTagName("SALE_EXPORT_WORK_PHONE_NEW") : CSaleExport::getTagName("SALE_EXPORT_WORK_PHONE"))?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						  <<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["PHONE"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
					<?
					}
					if($agent["EMAIL"] <> '')
					{
						?>
						<<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>><?=(self::getVersionSchema() > self::DEFAULT_VERSION ? CSaleExport::getTagName("SALE_EXPORT_MAIL_NEW") : CSaleExport::getTagName("SALE_EXPORT_MAIL"))?></<?=CSaleExport::getTagName("SALE_EXPORT_TYPE")?>>
						<<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($agent["EMAIL"])?></<?=CSaleExport::getTagName("SALE_EXPORT_VALUE")?>>
						</<?=CSaleExport::getTagName("SALE_EXPORT_CONTACT")?>>
					<?
					}
					?>
					</<?=CSaleExport::getTagName("SALE_EXPORT_CONTACTS")?>>
				<?
				}
				if($agent["CONTACT_PERSON"] <> '')
				{
					?>
					<<?=CSaleExport::getTagName("SALE_EXPORT_REPRESENTATIVES")?>>
					  <<?=CSaleExport::getTagName("SALE_EXPORT_REPRESENTATIVE")?>>
					    <<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENT")?>>
					      <<?=CSaleExport::getTagName("SALE_EXPORT_RELATION")?>><?=CSaleExport::getTagName("SALE_EXPORT_CONTACT_PERSON")?></<?=CSaleExport::getTagName("SALE_EXPORT_RELATION")?>>
					      <<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>><?=md5($agent["CONTACT_PERSON"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ID")?>>
					      <<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($agent["CONTACT_PERSON"])?></<?=CSaleExport::getTagName("SALE_EXPORT_ITEM_NAME")?>>
					    </<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENT")?>>
					  </<?=CSaleExport::getTagName("SALE_EXPORT_REPRESENTATIVE")?>>
					</<?=CSaleExport::getTagName("SALE_EXPORT_REPRESENTATIVES")?>>
				<?
				}?>
				<<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>><?=CSaleExport::getTagName("SALE_EXPORT_BUYER")?></<?=CSaleExport::getTagName("SALE_EXPORT_ROLE")?>>
			</<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENT")?>>
		</<?=CSaleExport::getTagName("SALE_EXPORT_CONTRAGENTS")?>>
		<?

		$filedsTolog = array(
			'ENTITY_ID' => $arOrder["USER_ID"],
			'PARENT_ID' => $arOrder['ID'],
			'ENTITY_DATE_UPDATE' => static::getUserTimeStapmX($arOrder),
			'XML_ID' => $xmlId
		);

		static::$documentsToLog[\Bitrix\Sale\Exchange\EntityType::USER_PROFILE][] = $filedsTolog;
	}

    public static function getFormatDate($value)
    {
        if(!is_set($value))
            return null;

        $setValue = $value;

        if (($value instanceof DateTime)
			|| ($value instanceof \Bitrix\Main\Type\Date))
		{
			$setValue = $value->toString();
		}

        /** @var \Bitrix\Main\Type\DateTime $time */
        $time = new Bitrix\Main\Type\DateTime($setValue);
        if(self::getVersionSchema() >= self::PARTIAL_VERSION )
            $format = 'Y-m-d\TH:i:s';
        else
            $format = 'd.m.Y H:i:s';

        return $time->format($format);
    }
    public static function isFormattedDateFields($type, $field)
    {
        $formattedDateFields = self::getFormattedDateFields();
        return in_array($field, $formattedDateFields[$type]);
    }

    public static function getFormattedDateFields()
    {
        return array(
            'Order'     =>  array(
                'DATE_PAYED',
		        'DATE_CANCELED',
		        'DATE_STATUS',
		        'DATE_ALLOW_DELIVERY',

            ),
            'Shipment'  =>  array(
                'DATE_ALLOW_DELIVERY',
                'DATE_DEDUCTED',
                'DATE_CANCELED',
            ),
            'Payment'   =>  array(
                'DATE_PAID',
            ),
        );
    }

	/** @deprecated */
	private static $systemCodes = array(
		// !!! Make sure these codes are in sync with system codes in BusinessValueConsumer1C !!!
		//  'new bizval name'            => 'old 1c name'
		BusinessValue::INDIVIDUAL_DOMAIN => array(
			'BUYER_PERSON_NAME'          => 'FULL_NAME'     ,
			'BUYER_PERSON_NAME_FIRST'    => 'NAME'          ,
			'BUYER_PERSON_NAME_SECOND'   => 'SECOND_NAME'   ,
			'BUYER_PERSON_NAME_LAST'     => 'SURNAME'       ,
			'BUYER_PERSON_NAME_AGENT'    => 'AGENT_NAME'    ,
			'BUYER_PERSON_NAME_CONTACT'  => 'CONTACT_PERSON',
			'BUYER_PERSON_BIRTHDAY'      => 'BIRTHDAY'      ,
			'BUYER_PERSON_GENDER'        => 'MALE'          ,
			'BUYER_PERSON_INN'           => 'INN'           ,
			'BUYER_PERSON_KPP'           => 'KPP'           ,
			'BUYER_PERSON_ADDRESS'       => 'ADDRESS_FULL'  ,
			'BUYER_PERSON_ZIP'           => 'INDEX'         ,
			'BUYER_PERSON_COUNTRY'       => 'COUNTRY'       ,
			'BUYER_PERSON_REGION'        => 'REGION'        ,
			'BUYER_PERSON_STATE'         => 'STATE'         ,
			'BUYER_PERSON_TOWN'          => 'TOWN'          ,
			'BUYER_PERSON_CITY'          => 'CITY'          ,
			'BUYER_PERSON_STREET'        => 'STREET'        ,
			'BUYER_PERSON_HOUSING'       => 'BUILDING'      ,
			'BUYER_PERSON_BUILDING'      => 'HOUSE'         ,
			'BUYER_PERSON_APARTMENT'     => 'FLAT'          ,
			'BUYER_PERSON_PHONE'         => 'PHONE'         ,
			'BUYER_PERSON_EMAIL'         => 'EMAIL'         ,
			'BUYER_PERSON_F_ADDRESS_FULL'=> 'F_ADDRESS_FULL',
			'BUYER_PERSON_F_INDEX'		 => 'F_INDEX'		,
			'BUYER_PERSON_F_COUNTRY'	 => 'F_COUNTRY'		,
			'BUYER_PERSON_F_REGION'		 => 'F_REGION'		,
			'BUYER_PERSON_F_STATE'		 => 'F_STATE'		,
			'BUYER_PERSON_F_TOWN'		 => 'F_TOWN'		,
			'BUYER_PERSON_F_CITY'		 => 'F_CITY'		,
			'BUYER_PERSON_F_STREET'		 => 'F_STREET'		,
			'BUYER_PERSON_F_BUILDING'	 => 'F_BUILDING'	,
			'BUYER_PERSON_F_HOUSE'		 => 'F_HOUSE'		,
			'BUYER_PERSON_F_FLAT'		 => 'F_FLAT'		,
		),
		BusinessValue::ENTITY_DOMAIN => array(
			'BUYER_COMPANY_NAME'         => 'FULL_NAME'     ,
			'BUYER_COMPANY_NAME_AGENT'   => 'AGENT_NAME'    ,
			'BUYER_COMPANY_NAME_CONTACT' => 'CONTACT_PERSON',
			'BUYER_COMPANY_INN'          => 'INN'           ,
			'BUYER_COMPANY_KPP'          => 'KPP'           ,
			'BUYER_COMPANY_ADDRESS'      => 'ADDRESS_FULL'  ,
			'BUYER_COMPANY_ZIP'          => 'INDEX'         ,
			'BUYER_COMPANY_COUNTRY'      => 'COUNTRY'       ,
			'BUYER_COMPANY_REGION'       => 'REGION'        ,
			'BUYER_COMPANY_STATE'        => 'STATE'         ,
			'BUYER_COMPANY_TOWN'         => 'TOWN'          ,
			'BUYER_COMPANY_CITY'         => 'CITY'          ,
			'BUYER_COMPANY_STREET'       => 'STREET'        ,
			'BUYER_COMPANY_HOUSING'      => 'BUILDING'      ,
			'BUYER_COMPANY_BUILDING'     => 'HOUSE'         ,
			'BUYER_COMPANY_APARTMENT'    => 'FLAT'          ,
			'BUYER_COMPANY_PHONE'        => 'PHONE'         ,
			'BUYER_COMPANY_EMAIL'        => 'EMAIL'         ,
			'BUYER_COMPANY_EGRPO'        => 'EGRPO'         ,
			'BUYER_COMPANY_OKVED'        => 'OKVED'         ,
			'BUYER_COMPANY_OKDP'         => 'OKDP'          ,
			'BUYER_COMPANY_OKOPF'        => 'OKOPF'         ,
			'BUYER_COMPANY_OKFC'         => 'OKFC'          ,
			'BUYER_COMPANY_OKPO'         => 'OKPO'          ,
			'BUYER_COMPANY_BANK_ACCOUNT' => 'ACCOUNT_NUMBER',
			'BUYER_COMPANY_BANK_NAME'    => 'B_NAME',
			'BUYER_COMPANY_BANK_BIK'     => 'B_BIK',
			'BUYER_COMPANY_BANK_ADDRESS_FULL' => 'B_ADDRESS_FULL',
			'BUYER_COMPANY_BANK_INDEX'   => 'B_INDEX',
			'BUYER_COMPANY_BANK_COUNTRY' => 'B_COUNTRY',
			'BUYER_COMPANY_BANK_REGION'  => 'B_REGION',
			'BUYER_COMPANY_BANK_STATE'   => 'B_STATE',
			'BUYER_COMPANY_BANK_TOWN'    => 'B_TOWN',
			'BUYER_COMPANY_BANK_CITY'    => 'B_CITY',
			'BUYER_COMPANY_BANK_STREET'  => 'B_STREET',
			'BUYER_COMPANY_BANK_BUILDING' => 'B_BUILDING',
			'BUYER_COMPANY_BANK_HOUSE'   => 'B_HOUSE',
			'BUYER_COMPANY_F_ADDRESS_FULL'=> 'F_ADDRESS_FULL',
			'BUYER_COMPANY_F_INDEX'		 => 'F_INDEX'		,
			'BUYER_COMPANY_F_COUNTRY'	 => 'F_COUNTRY'		,
			'BUYER_COMPANY_F_REGION'	 => 'F_REGION'		,
			'BUYER_COMPANY_F_STATE'		 => 'F_STATE'		,
			'BUYER_COMPANY_F_TOWN'		 => 'F_TOWN'		,
			'BUYER_COMPANY_F_CITY'		 => 'F_CITY'		,
			'BUYER_COMPANY_F_STREET'	 => 'F_STREET'		,
			'BUYER_COMPANY_F_BUILDING'	 => 'F_BUILDING'	,
			'BUYER_COMPANY_F_HOUSE'		 => 'F_HOUSE'		,
			'BUYER_COMPANY_F_FLAT'		 => 'F_FLAT'		,
		),
	);
	function GetList($order = Array("ID" => "DESC"), $filter = Array(), $group = false, $arNavStartParams = false, $select = array())
	{
		if (! ($select && is_array($select)))
			$select = array("ID", "PERSON_TYPE_ID", "VARS");

		$select = array_flip($select);

		$personTypes = BusinessValue::getPersonTypes();

		if ($filter && is_array($filter))
		{
			if ($filter['PERSON_TYPE_ID'])
			{
				if (! is_array($filter['PERSON_TYPE_ID']))
					$filter['PERSON_TYPE_ID'] = array($filter['PERSON_TYPE_ID']);

				$personTypes = array_intersect_key($personTypes, array_flip($filter['PERSON_TYPE_ID']));
			}

			if (isset($filter['ID']))
			{
				$personTypes = isset($personTypes[$filter['ID']])
					? array($filter['ID'] => $personTypes[$filter['ID']])
					: array();
			}
		}

		$rows = array();

		if ($personTypes
			&& ($consumers = BusinessValue::getConsumers())
			&& ($consumer = $consumers[BusinessValueConsumer1C::CONSUMER_KEY])
			&& is_array($consumer)
			&& ($codes = $consumer['CODES'])
			&& is_array($codes))
		{
			foreach ($personTypes as $personTypeId => $personType)
			{
				$systemCodes = self::$systemCodes[$personType['DOMAIN']];
				$vars = array();

				foreach ($codes as $codeKey => $code)
				{
					if ($mapping = BusinessValue::getMapping($codeKey, BusinessValueConsumer1C::CONSUMER_KEY, $personTypeId, array('GET_VALUE' => array('PROPERTY' => 'BY_ID'))))
					{
						$mapping1C = array('VALUE' => $mapping['PROVIDER_VALUE']);

						switch ($mapping['PROVIDER_KEY'])
						{
							case 'VALUE':
								$mapping1C['TYPE'] = '';
								break;

							case 'USER':
							case 'ORDER':
							case 'PROPERTY':
							case 'COMPANY':
							case 'PAYMENT':
							case 'SHIPMENT':
								$mapping1C['TYPE'] = $mapping['PROVIDER_KEY'];
								break;

							default: continue 2; // other types aren't present in old version
						}

						if (isset($code['CODE_INDEX']))
						{
							$codeKey1C = 'REKV_'.$code['CODE_INDEX'];
							$mapping1C['NAME'] = $code['NAME'];
						}
						else
						{
							$codeKey1C = $systemCodes[$codeKey];
						}

						$vars[$codeKey1C] = $mapping1C;
					}
				}

				if ($vars)
				{
					$vars['IS_FIZ'] = $personTypes[$personTypeId]['DOMAIN'] === BusinessValue::INDIVIDUAL_DOMAIN ? 'Y' : 'N';

					$rows []= array_intersect_key(array(
						'ID'             => $personTypeId,
						'PERSON_TYPE_ID' => $personTypeId,
						'VARS'           => serialize($vars),
					), $select);
				}
			}
		}

		if (! $group && is_array($group))
		{
			return count($rows);
		}
		else
		{
			$result = new CDBResult();
			$result->InitFromArray($rows);
			return $result;
		}
	}

	/**
	 * @param \Bitrix\Sale\IBusinessValueProvider $entity
	 * @return array
	 */
	static protected function getProvidersInstanceByEntity(\Bitrix\Sale\IBusinessValueProvider $entity)
	{
        $providersInstance = array(
            'ORDER'     =>  self::getProviderInstanceByProviderCode($entity, 'ORDER'     ),
            'USER'      =>  self::getProviderInstanceByProviderCode($entity, 'USER'      ),
            'COMPANY'   =>  self::getProviderInstanceByProviderCode($entity, 'COMPANY'   ),
            'SHIPMENT'  =>  self::getProviderInstanceByProviderCode($entity, 'SHIPMENT'  ),
            'PAYMENT'   =>  self::getProviderInstanceByProviderCode($entity, 'PAYMENT'   ),
            'PROPERTY'  =>  self::getProviderInstanceByProviderCode($entity, 'PROPERTY'  ),
        );

		return $providersInstance;
	}

	/**
     * @deprecated
	 * @param \Bitrix\Sale\Order $order
	 * @return array
	 */
	protected function getProvidersInstanceByOrder(Bitrix\Sale\Order $order)
    {
        static $providersInstance = array();

        if(! is_set($providersInstance, $order->getId()))
        {
            $providersInstance[$order->getId()] = self::getProvidersInstanceByEntity($order);
        }

        return $providersInstance;
    }

    static protected function getProviderInstanceByProviderCode(\Bitrix\Sale\IBusinessValueProvider $entity, $providerCode)
	{
		$providerInstance = null;
		$order = null;
		/** @var \Bitrix\Sale\Order $order */
		if($entity instanceof \Bitrix\Sale\Order)
			$order = $entity;
		else
		{
			/** @var \Bitrix\Sale\PaymentCollection|\Bitrix\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();
			$order = $collection->getOrder();
		}

		switch($providerCode)
		{
			case 'ORDER':
			case 'USER':
			case 'PROPERTY':
			    $providerInstance = $order;
				break;
			case 'COMPANY':
				$providerInstance = $entity;
				break;
			case 'PAYMENT':
				if($order instanceof \Bitrix\Sale\Order)
                {
					$collection = $order->getPaymentCollection();
					foreach($collection as $payment)
					{
						$providerInstance = $payment;
						break;
					}
                }
                else
					$providerInstance = $entity;

				break;
			case 'SHIPMENT':
				if($order instanceof \Bitrix\Sale\Order)
				{
					$collection = $order->getShipmentCollection();
					foreach($collection as $shipment)
					{
						$providerInstance = $shipment;
						break;
					}
				}
				else
					$providerInstance = $entity;
				break;
		}

        return $providerInstance;
	}
	function GetByID($ID)
	{
		$ID = intval($ID);

		if (isset($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]) && is_array($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]) && is_set($GLOBALS["SALE_EXPORT_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID];
		}
		else
		{
			$dbResult = self::GetList(array(), array('ID' => $ID));

			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID] = $arResult;
				return $arResult;
			}
		}

		return False;
	}

	/** @deprecated */
	private static function logError($itemId, $message, Bitrix\Main\Result $result = null)
	{
		if ($result)
			$message .= "\n".implode("\n", $result->getErrorMessages());

		CEventLog::Add(array(
			'SEVERITY' => 'ERROR',
			'AUDIT_TYPE_ID' => 'SALE_1C_TO_BUSINESS_VALUE_ERROR',
			'MODULE_ID' => 'sale',
			'ITEM_ID' => $itemId,
			'DESCRIPTION' => $message,
		));
	}

	/** @deprecated */
	private static function setMap($personTypeId, array $map1C, $itemId)
	{
		BusinessValue::INDIVIDUAL_DOMAIN; // make sure BusinessValueCode1CTable loaded since it in the same file as BusinessValue
		BusinessValueConsumer1C::getConsumers(); // initialize 1C codes

		$personTypes = BusinessValue::getPersonTypes();

		if (! $personType = $personTypes[$personTypeId])
		{
			self::logError($itemId, 'Undefined DOMAIN for person type id "'.$personTypeId.'"');
			return;
		}

		$systemCodes1C = array_flip(self::$systemCodes[$personType['DOMAIN']]);

		foreach ($map1C as $codeKey1C => $mapping1C)
		{
			if ($codeKey1C && is_array($mapping1C))
			{
				if (! $mapping1C['VALUE'])
					continue; // TODO maybe??

				$mapping = array('PROVIDER_VALUE' => $mapping1C['VALUE']);

				if (! ($codeKey = $systemCodes1C[$codeKey1C])
					&& mb_substr($codeKey1C, 0, 5) === 'REKV_'
					&& ($codeIndex = mb_substr($codeKey1C, 5)) !== ''
					&& $mapping1C['NAME'])
				{
					$codeKey = BusinessValueConsumer1C::getRekvCodeKey($personTypeId, $codeIndex);
					$mapping['NAME'] = $mapping1C['NAME'];
				}

				if (! $codeKey)
					continue;

				switch ($mapping1C['TYPE'])
				{
					case '':
						$mapping['PROVIDER_KEY'] = 'VALUE';
						break;

					case 'USER':
					case 'ORDER':
					case 'PROPERTY':
						$mapping['PROVIDER_KEY'] = $mapping1C['TYPE'];
						break;

					default: continue 2; // other types should not be there
				}

				$r = BusinessValueConsumer1C::setMapping($codeKey, $personTypeId, $mapping);

				if (! $r->isSuccess())
					self::logError($itemId, 'Cannot set mapping with code key "'.$codeKey.'"', $r);
			}
		}
	}

	/** @deprecated */
	public static function migrateToBusinessValues()
	{
		$allPersonTypes = BusinessValue::getPersonTypes(true);

		Bitrix\Main\Application::getConnection()->query('DELETE FROM b_sale_bizval_code_1c');

		$result = Bitrix\Main\Application::getConnection()->query('SELECT * FROM b_sale_export');

		while ($row = $result->fetch())
		{
			if (! (($map1C = unserialize($row['VARS'])) && is_array($map1C)))
				continue;

			$personTypeId = $row['PERSON_TYPE_ID'];
			$domain = $map1C['IS_FIZ'] === 'Y' ? BusinessValue::INDIVIDUAL_DOMAIN : BusinessValue::ENTITY_DOMAIN;
			unset($map1C['IS_FIZ']);

			if (! isset($allPersonTypes[$personTypeId]))
			{
				self::logError($row['ID'], 'Undefined person type "'.$personTypeId.'"');
				continue;
			}
			elseif (isset($allPersonTypes[$personTypeId]['DOMAIN']))
			{
				if ($allPersonTypes[$personTypeId]['DOMAIN'] !== $domain)
				{
					self::logError($row['ID'], 'Person type "'.$personTypeId.'" domain is "'.$allPersonTypes[$personTypeId]['DOMAIN'].'", but in 1C is "'.$domain.'"');
					continue;
				}
			}
			else
			{
				$r = Bitrix\Sale\Internals\BusinessValuePersonDomainTable::add(array(
					'PERSON_TYPE_ID' => $personTypeId,
					'DOMAIN'         => $domain,
				));

				if ($r->isSuccess())
				{
					$allPersonTypes[$personTypeId]['DOMAIN'] = $domain;
					BusinessValue::getPersonTypes(true, $allPersonTypes);
				}
				else
				{
					self::logError($row['ID'], 'Unable to set person type "'.$personTypeId.'" domain', $r);
					continue;
				}
			}

			self::setMap($personTypeId, $map1C, 'Migrate:'.$personTypeId.':'.$row['ID']);
		}
	}

	static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && intval($arFields["PERSON_TYPE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SALE_EXPORT_NO_PERSON_TYPE_ID"), "EMPTY_PERSON_TYPE_ID");
			return false;
		}

		if (is_set($arFields, "PERSON_TYPE_ID"))
		{
			/** @var \Bitrix\Sale\PersonType $personType */
			$personType = static::getPersonType();
			$dbRes = $personType::getList([
				'filter' => [
					'=ID' => $arFields["PERSON_TYPE_ID"]
				]
			]);
			if (!$dbRes->fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PERSON_TYPE_ID"], GetMessage("SALE_EXPORT_ERROR_PERSON_TYPE_ID")), "ERROR_NO_PERSON_TYPE_ID");
				return false;
			}
		}

		return True;
	}

	static function Add($arFields)
	{
		if (! static::CheckFields('ADD', $arFields))
			return false;

		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (($map1C = unserialize($arFields['VARS'])) && is_array($map1C))
		{
			self::setMap($arFields['PERSON_TYPE_ID'], $map1C, 'Add:'.$arFields['PERSON_TYPE_ID']);
		}

		return $arFields['PERSON_TYPE_ID'];
	}

	static function Update($ID, $arFields)
	{
		$ID = intval($ID);

		if (! static::CheckFields('UPDATE', $arFields, $ID))
			return false;

		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (($map1C = unserialize($arFields['VARS'])) && is_array($map1C))
		{
			self::setMap($arFields['PERSON_TYPE_ID'], $map1C, 'Update:'.$arFields['PERSON_TYPE_ID'].':'.$ID);
		}

		return $arFields['PERSON_TYPE_ID'];
	}

	static function deleteREKV($typeId)
    {
        $r = new \Bitrix\Main\Result();

        $res = \Bitrix\Sale\Internals\BusinessValueCode1CTable::getList(array(
                'select'=>array('CODE_INDEX', 'PERSON_TYPE_ID'),
                'filter'=>array('PERSON_TYPE_ID'=>$typeId)
        ));
        while($row=$res->fetch())
        {
			$r = \Bitrix\Sale\Internals\BusinessValueCode1CTable::delete(array(
				'PERSON_TYPE_ID' => $row['PERSON_TYPE_ID'],
				'CODE_INDEX'     => $row['CODE_INDEX'],
			));

			if($r->isSuccess())
            {
                $r = \Bitrix\Sale\Internals\BusinessValueTable::delete(array(
                    'CODE_KEY'       => BusinessValueConsumer1C::getRekvCodeKey($row['PERSON_TYPE_ID'], $row['CODE_INDEX']),
                    'CONSUMER_KEY'   => BusinessValueConsumer1C::CONSUMER_KEY,
                    'PERSON_TYPE_ID' => $row['PERSON_TYPE_ID'],
                ));
			}
        }
		return $r;
    }

	function Delete($ID)
	{
		$ID = intval($ID);

		unset($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]);

		BusinessValue::INDIVIDUAL_DOMAIN; // make sure BusinessValueCode1CTable loaded since it in the same file as BusinessValue
		$consumers = BusinessValueConsumer1C::getConsumers(); // initialize 1C codes
		$consumer = $consumers[BusinessValueConsumer1C::CONSUMER_KEY];

		if (is_array($consumer['CODES']))
		{
			foreach ($consumer['CODES'] as $codeKey => $code)
			{
				if(!isset($code['CODE_INDEX']))
                {
					BusinessValueConsumer1C::setMapping($codeKey, $ID, array());
                }
			}
		}

		return new CDBResult();
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public static function getTagName($name)
	{
		if (static::$lid === null)
		{
			static::setLanguage(LANGUAGE_ID);
		}

		static $lang = array();

		if (empty($lang[static::$lid]))
		{
			$lang[static::$lid] = \Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/general/export.php', static::$lid);
		}

		if (array_key_exists($name, $lang[static::$lid]) && strval(trim($lang[static::$lid][$name])) !== '')
		{
			$value = $lang[static::$lid][$name];
		}
		else
		{
			$value = \Bitrix\Main\Localization\Loc::getMessage($name);
		}

		return $value;
	}

	/**
	 * @param array $fields
	 * @return \Bitrix\Main\Entity\AddResult
     * @deprecated
	 */
	static public function log(array $fields)
	{
		$params['ENTITY_ID'] = $fields['ENTITY_ID'];
		$params['ENTITY_TYPE_ID'] = $fields['ENTITY_TYPE_ID'];
		$params['DIRECTION'] = \Bitrix\Sale\Exchange\ManagerExport::getDirectionType();

		if ($fields['XML_ID'] <> '')
			$params['XML_ID'] = $fields['XML_ID'];

		if ($fields['ENTITY_DATE_UPDATE'] <> '')
			$params['ENTITY_DATE_UPDATE'] = $fields['ENTITY_DATE_UPDATE'];

		if (intval($fields['PARENT_ID'])>0)
			$params['PARENT_ID'] = $fields['PARENT_ID'];

		if (intval($fields['OWNER_ENTITY_ID'])>0)
			$params['OWNER_ENTITY_ID'] = $fields['OWNER_ENTITY_ID'];

		if ($fields['MARKED'] <> '')
		    $params['MARKED'] = $fields['MARKED'];

		$params['MESSAGE'] = \Bitrix\Sale\Exchange\Internals\LoggerDiag::isOn()? $fields['MESSAGE']:null;

		$params['DATE_INSERT'] = new \Bitrix\Main\Type\DateTime();

		return \Bitrix\Sale\Exchange\Internals\ExchangeLogTable::add($params);
    }
}
