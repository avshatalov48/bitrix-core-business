<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Main;
use Bitrix\Sale\Exchange;
use Bitrix\Main\Type;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\PriceMaths;

/**
 * Class DocumentImport
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class DocumentImport
{
	/** @var Fields */
	protected $fields;

	function __construct()
	{
		$this->fields = new Fields();
	}

	/**
     * @return array
     */
    protected static function getMessage()
    {
        return Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php');
    }

    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::UNDEFINED;
    }

	/**
	 * @param array $values
	 * @internal param array $fields
	 */
	public function setFields(array $values)
	{
		foreach ($values as $key=>$value)
		{
			$this->setField($key, $value);
		}
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields->set($name, $value);
	}

	/**
	 * @param $name
	 * @return null|string
	 */
	public function getField($name)
	{
		return $this->fields->get($name);
	}

	/**
	 * @return array
	 */
	public function getFieldValues()
	{
		return $this->fields->getValues();
	}

    /**
     * @return int
     */
    public function getId()
    {
		if($this->getField('ID'))
        {
            return $this->getField('ID');
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getExternalId()
    {
		if($this->getField('ID_1C'))
        {
            return $this->getField('ID_1C');
        }

        return null;
    }

    /**
     * @param array $document
     * @return int
     */
    public static function resolveDocumentTypeId(array $document)
    {
        $typeId = DocumentType::UNDEFINED;

        $message = self::getMessage();

        $operation = $document['#'][$message['CC_BSC1_OPERATION']][0]['#'];
        if(!empty($operation))
        {
            if($operation == $message["CC_BSC1_ORDER"])
            {
                $typeId = DocumentType::ORDER;
            }
            elseif($operation == $message["CC_BSC1_PAYMENT_C"])
            {
                $typeId = DocumentType::PAYMENT_CASH;
            }
            elseif($operation == $message["CC_BSC1_PAYMENT_B"])
            {
                $typeId = DocumentType::PAYMENT_CASH_LESS;
            }
            elseif($operation == $message["CC_BSC1_PAYMENT_A"])
            {
                $typeId = DocumentType::PAYMENT_CARD_TRANSACTION;
            }
            elseif($operation == $message["CC_BSC1_SHIPMENT"])
            {
                $typeId = DocumentType::SHIPMENT;
            }
            else
                $typeId = DocumentType::UNDEFINED;
        }

        return $typeId;
    }

    /**
     * @param $value
     * @param $fieldName
     * @param array $fieldsInfo
     * @param array $document
     * @return null
     */
    public static function resolveItemsArrayParams($value, $fieldName, array $fieldsInfo, array $document)
    {
        $result = null;

        switch($fieldName)
        {
            case 'REK_VALUES':
                $result = self::resolveTraitsParams($value, $fieldsInfo);
                break;
            case 'TAXES':
                $result = self::resolveTaxParams($value, $fieldsInfo);
                break;
            case 'ITEM_UNIT':
                $result = self::resolveUnitParams($value, $fieldsInfo);
                break;
			case 'DISCOUNTS':
				$result = self::resolveDiscountsParams($value, $fieldsInfo);
				break;
        }
        return $result;
    }

    /**
     * @param array $document
     * @param array $fieldsInfo
     * @return array|null
     */
    protected static function resolveItemsParams(array $document, array $fieldsInfo)
    {
        $result = null;
        $message = self::getMessage();

        if (is_array($document["#"][$message["CC_BSC1_ITEMS"]][0]["#"]) &&
            is_array($document["#"][$message["CC_BSC1_ITEMS"]][0]["#"][$message["CC_BSC1_ITEM"]]))
        {
            $items = $document["#"][$message["CC_BSC1_ITEMS"]][0]["#"][$message["CC_BSC1_ITEM"]];

            foreach ($items as $val)
            {
                $fields = array();
                if(is_array($val))
                {
                    foreach($fieldsInfo['FIELDS'] as $name => $info)
                    {
                        if(!empty($val["#"][$message["CC_BSC1_".$name]]))
                        {
                            if($info['TYPE'] === 'array')
                            {
                                $value = self::resolveItemsArrayParams($val, $name, $info, $document);
                            }
                            else
                            {
                                $value = $val["#"][$message["CC_BSC1_".$name]][0]["#"];

                                self::internalizeFields($value, $info);
                            }
                            $fields[$name] = $value;
                        }
                    }
                }
                $result[] = $fields;
            }
        }

        return $result;
    }

	/**
	 * @param array $value
	 * @param array $fieldsInfo
	 * @return null
	 */
	protected static function resolveCashBoxCheksParams(array $value, array $fieldsInfo)
	{
		$result = null;
		$message = self::getMessage();

		if (isset($value["#"][$message["CC_BSC1_CASHBOX_CHECKS"]][0]["#"][$message["CC_BSC1_CASHBOX_CHECK"]][0]['#'])
			&& !empty($value["#"][$message["CC_BSC1_CASHBOX_CHECKS"]][0]["#"][$message["CC_BSC1_CASHBOX_CHECK"]][0]['#']))
		{
			$properties = array();
			$val = $value["#"][$message["CC_BSC1_CASHBOX_CHECKS"]][0]["#"][$message["CC_BSC1_CASHBOX_CHECK"]][0];

			$id = $val["#"][$message["CC_BSC1_ID"]][0]["#"];
			self::internalizeFields($id, $fieldsInfo);
			$result['ID'] = $id;

			if (isset($val["#"][$message["CC_BSC1_PROPERTY_VALUES"]][0]["#"][$message["CC_BSC1_PROPERTY_VALUE"]]) &&
				isset($val["#"][$message["CC_BSC1_PROPERTY_VALUES"]][0]["#"][$message["CC_BSC1_PROPERTY_VALUE"]][0]['#']) &&
				!empty($val["#"][$message["CC_BSC1_PROPERTY_VALUES"]][0]["#"][$message["CC_BSC1_PROPERTY_VALUE"]][0]['#']))
			{
				foreach($val["#"][$message["CC_BSC1_PROPERTY_VALUES"]][0]["#"][$message["CC_BSC1_PROPERTY_VALUE"]] as $property)
				{
					$propertyName = $property["#"][$message["CC_BSC1_ID"]][0]["#"];
					$propertyValue = $property["#"][$message["CC_BSC1_VALUE"]][0]["#"];
					if(strlen($propertyValue)>0)
						$properties[$propertyName] = $propertyValue;
				}
			}

			foreach($fieldsInfo['PROPERTIES'] as $name => $fieldInfo)
			{
				if(!empty($properties[$message["CC_BSC1_".$name]]))
				{
					$fieldValue = $properties[$message["CC_BSC1_".$name]];

					self::internalizeFields($fieldValue, $fieldInfo);

					$result[$name] = $fieldValue;
				}
			}

			return $result;
		}
	}

    /**
     * @param array $value
     * @param array $fieldsInfo
     * @return null
     */
    protected static function resolveTraitsParams(array $value, array $fieldsInfo)
    {
        $result = null;
        $message = self::getMessage();

        if (is_array($value["#"][$message["CC_BSC1_REK_VALUES"]][0]["#"][$message["CC_BSC1_REK_VALUE"]])
            && !empty($value["#"][$message["CC_BSC1_REK_VALUES"]][0]["#"][$message["CC_BSC1_REK_VALUE"]]))
        {
            $traits = array();
            foreach($value["#"][$message["CC_BSC1_REK_VALUES"]][0]["#"][$message["CC_BSC1_REK_VALUE"]] as $val)
            {
                $traitName = $val["#"][$message["CC_BSC1_NAME"]][0]["#"];
                $traitValue = $val["#"][$message["CC_BSC1_VALUE"]][0]["#"];
                if(strlen($traitValue)>0)
                    $traits[$traitName] = $traitValue;
            }

            foreach($fieldsInfo['FIELDS'] as $name => $fieldInfo)
            {
                $fieldValue = '';
                if($name == 'PROP_BASKET')
                {
                    foreach($traits as $k=>$v)
                    {
                        $namePropertyBaslet = $message["CC_BSC1_".$name];
                        if (strpos($k, $namePropertyBaslet."#") === 0)
                        {
                            $position = strpos($k, $namePropertyBaslet."#");
                            $idBasketProperty = substr($k, $position + strlen($namePropertyBaslet."#"));

                            self::internalizeFields($v);

                            $result[$name][$idBasketProperty] = $v;
                        }
                    }
                }
                elseif($name == "ITEM_TYPE")
                {
                    if($traits[$message["CC_BSC1_".$name]] == $message["CC_BSC1_ITEM"])
                        $fieldValue = Exchange\ImportBase::ITEM_ITEM;
                    elseif($traits[$message["CC_BSC1_".$name]] == $message["CC_BSC1_SERVICE"])
                        $fieldValue = Exchange\ImportBase::ITEM_SERVICE;

                    self::internalizeFields($fieldValue, $fieldInfo);

                    $result[$name] = $fieldValue;
                }
                elseif(!empty($traits[$message["CC_BSC1_".$name]]))
                {
                    $fieldValue = $traits[$message["CC_BSC1_".$name]];

                    self::internalizeFields($fieldValue, $fieldInfo);

                    $result[$name] = $fieldValue;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $value
     * @param array $fieldsInfo
     * @return null
     */
    protected static function resolveTaxParams(array $value, array $fieldsInfo)
    {
        $result = null;
        $message = self::getMessage();

        if (is_array($value["#"][$message["CC_BSC1_TAXES"]][0]["#"][$message["CC_BSC1_TAX"]])
            && !empty($value["#"][$message["CC_BSC1_TAXES"]][0]["#"][$message["CC_BSC1_TAX"]]))
        {
            $field = $value["#"][$message["CC_BSC1_TAXES"]][0]["#"][$message["CC_BSC1_TAX"]];
            foreach($fieldsInfo['FIELDS'] as $name => $info)
            {
                if(!empty($field[0]["#"][$message["CC_BSC1_".$name]][0]["#"]))
                {
                    $fieldValue = $field[0]["#"][$message["CC_BSC1_".$name]][0]["#"];
                    self::internalizeFields($fieldValue, $info);

                    $result[$name] = $fieldValue;
                }
            }
        }
        return $result;
    }

	/**
	 * @param array $value
	 * @param array $fieldsInfo
	 * @return null
	 */
	protected static function resolveDiscountsParams(array $value, array $fieldsInfo)
	{
		$result = null;
		$message = self::getMessage();

		if (is_array($value["#"][$message["CC_BSC1_DISCOUNTS"]][0]["#"][$message["CC_BSC1_DISCOUNT"]])
			&& !empty($value["#"][$message["CC_BSC1_DISCOUNTS"]][0]["#"][$message["CC_BSC1_DISCOUNT"]]))
		{
			$field = $value["#"][$message["CC_BSC1_DISCOUNTS"]][0]["#"][$message["CC_BSC1_DISCOUNT"]];
			foreach($fieldsInfo['FIELDS'] as $name => $info)
			{
				if(!empty($field[0]["#"][$message["CC_BSC1_".$name]][0]["#"]))
				{
					$fieldValue = $field[0]["#"][$message["CC_BSC1_".$name]][0]["#"];
					self::internalizeFields($fieldValue, $info);

					$result[$name] = $fieldValue;
				}
			}
		}
		return $result;
	}


    /**
     * @param $value
     * @param array $fieldsInfo
     * @return null
     */
    protected static function resolveUnitParams($value, array $fieldsInfo)
    {
        $result = null;
        $message = self::getMessage();

        if (is_array($value["#"][$message["CC_BSC1_ITEM_UNIT"]])
            && !empty($value["#"][$message["CC_BSC1_ITEM_UNIT"]]))
        {
            $field = $value["#"][$message["CC_BSC1_ITEM_UNIT"]];
            foreach($fieldsInfo['FIELDS'] as $name => $info)
            {
                if(!empty($field[0]["#"][$message["CC_BSC1_".$name]][0]["#"]))
                {
                    $fieldValue = $field[0]["#"][$message["CC_BSC1_".$name]][0]["#"];
                    self::internalizeFields($fieldValue, $info);

                    $result[$name] = $fieldValue;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $fields
     * @return array|null
     */
    protected static function fillItemsFields(array $fields)
    {
        $result = null;

        $basketItems = array();
        foreach($fields['ITEMS_FIELDS'] as $item)
        {
            $priceone = $item['PRICE_PER_UNIT'];
            if (DoubleVal($priceone) <= 0)
                $priceone = $item["PRICE_ONE"];


            $discountPrice = "";
            if (doubleval($item['QUANTITY']) > 0)
            {
            	$price = PriceMaths::roundPrecision($item['SUMM'] / $item['QUANTITY']);
				$priceone = PriceMaths::roundPrecision($priceone);

                if(isset($item['DISCOUNTS']['SUMM']) && $item['DISCOUNTS']['SUMM']<>'')
				{
					if ($priceone != $price)
						$discountPrice = DoubleVal($priceone - $price);
				}
				else
					$price = $priceone;

                $basketItems = Array(
                    'ID' => $item['ID'],
                    'NAME' => $item['NAME'],
                    'PRICE' => $price,
                    'PRICE_ONE' => $priceone,
                    'QUANTITY' => $item['QUANTITY'],
                    'TYPE' => $item['REK_VALUES']['ITEM_TYPE'],
                    'MEASURE_CODE' => !empty($item['ITEM_UNIT']) ? $item['ITEM_UNIT']['ITEM_UNIT_CODE']:null,
                    'MEASURE_NAME' => !empty($item['ITEM_UNIT']) ? $item['ITEM_UNIT']['ITEM_UNIT_NAME']:null,
                    'ATTRIBUTES' => !empty($item['REK_VALUES']['PROP_BASKET']) ? $item['REK_VALUES']['PROP_BASKET']:null,
                    'TAX' => array(
                        'VAT_RATE' => !empty($item['TAXES']['TAX_VALUE']) ? $item['TAXES']['TAX_VALUE']/100:null,
                        'VAT_INCLUDED' => !empty($item['TAXES']['IN_PRICE']) ? $item['TAXES']['IN_PRICE']:'Y'//if tax is null then always included by default
                    ),
                    'DISCOUNT' => array(
                        'PRICE' => $discountPrice
                    )

                );
            }
            $result[][$item['ID']] = $basketItems;
        }
        return $result;
    }

    /**
     * @param $value
     * @param null $fieldInfo
     */
    protected static function internalizeFields(&$value, $fieldInfo=null)
    {
        switch($fieldInfo['TYPE'])
        {
            case 'datetime':
                $date = str_replace("T", " ", $value);
                $value = new Type\DateTime(\CAllDatabase::FormatDate($date, "YYYY-MM-DD HH:MI:SS", \CAllSite::GetDateFormat("FULL", LANG)));
                break;
            case 'bool':
                $value = $value == "true" ? 'Y':'N';
                break;
            case 'float':
                $value = self::toFloat($value);
                break;
            case 'int':
                $value = self::toInt($value);
                break;
        }
    }

    /**
     * @param $value
     * @return float
     */
    protected static function toFloat($value)
    {
        $saleOrderLoader = new \CSaleOrderLoader();
        return $saleOrderLoader->ToFloat($value);
    }

    /**
     * @param $value
     * @return int
     */
    protected static function toInt($value)
    {
        $saleOrderLoader = new \CSaleOrderLoader();
        return $saleOrderLoader->ToInt($value);
    }

    /**
     * @param array $document
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\NotSupportedException
     */
    static public function prepareFieldsData(array $document)
    {
        $message = self::getMessage();
        $fields = array();

        foreach(static::getFieldsInfo() as $k=>$v)
        {
            switch($k)
            {
                case 'ID':
                    $value = $document["#"][$message["CC_BSC1_NUMBER"]][0]["#"];
                    self::internalizeFields($value, $v);
                    $fields[$k] = $value;
                    break;
                case 'ID_1C':
                    $value = $document["#"][$message["CC_BSC1_ID"]][0]["#"];
                    self::internalizeFields($value, $v);
                    $fields[$k] = $value;
                    break;
                case 'ORDER_ID':
                    $value = $document["#"][$message["CC_BSC1_NUMBER_BASE"]][0]["#"];
                    self::internalizeFields($value, $v);
                    $fields[$k] = $value;
                    break;
                case 'VERSION_1C':
                case 'COMMENT':
                case 'CANCELED':
                case '1C_DATE':
					$value = $document["#"][$message["CC_BSC1_".$k]][0]["#"];
					self::internalizeFields($value, $v);
					$fields[$k] = $value;
					break;
                case '1C_TIME':
                	$date1C = $document["#"][$message["CC_BSC1_1C_DATE"]][0]["#"];
                	if($date1C >0)
					{
						$value = $date1C." ".$document["#"][$message["CC_BSC1_".$k]][0]["#"];
						self::internalizeFields($value, $v);
						$fields[$k] = $value;
					}
                    break;
                case 'OPERATION':
                    $typeId = self::resolveDocumentTypeId($document);
                    $fields[$k] = DocumentType::resolveName($typeId);
                    break;
                case 'AMOUNT':
                    $value = $document["#"][$message["CC_BSC1_SUMM"]][0]["#"];
                    self::internalizeFields($value, $v);
                    $fields[$k] = $value;
                    break;
				case 'CASH_BOX_CHECKS':
					$fields[$k] = self::resolveCashBoxCheksParams($document, $v);
					break;
                case 'REK_VALUES':
                    $fields[$k] = self::resolveTraitsParams($document, $v);
                    break;
                case 'ITEMS':
                    $fields['ITEMS_FIELDS'] = self::resolveItemsParams($document, $v);

                    if(!empty($fields['ITEMS_FIELDS']))
                        $fields[$k] = self::fillItemsFields($fields);
                    break;
                case 'TAXES':
                    $taxValue = 0;
                    $taxName = '';
                    if(!empty($fields['ITEMS_FIELDS']))
                    {
                        foreach($fields['ITEMS_FIELDS'] as $items)
                        {
                            foreach ($items as $item)
                            {
								$taxValueTmp = isset($item['TAX_VALUE']) ? $item['TAX_VALUE']:0;

								if (IntVal($taxValueTmp) > IntVal($taxValue))
								{
									$taxName = $item['NAME'];
									$taxValue = $taxValueTmp;
								}
                            }
                        }
                    }

                    if(IntVal($taxValue)>0)
                    {
                        $fields[$k] = self::resolveTaxParams($document, $v);
                        $fields[$k]['VALUE'] = $taxValue;
                        $fields[$k]['NAME'] = $taxName;
                    }
                    break;
                case 'AGENT':
                    /* includes document profile */
                    $documentProfile = new UserProfileDocument();
                    $mess = Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/general/export.php');

                    if(is_array($document["#"][$mess["SALE_EXPORT_CONTRAGENTS"]][0]["#"][$mess["SALE_EXPORT_CONTRAGENT"]][0]["#"]))
					{
						$fields[$k] = $documentProfile::prepareFieldsData($document["#"][$mess["SALE_EXPORT_CONTRAGENTS"]][0]["#"][$mess["SALE_EXPORT_CONTRAGENT"]][0]["#"]);
					}

                    break;
            }
        }
        return $fields;
    }

    /**
     * @return array
     * @throws Main\ArgumentException
     */
    protected static function getFieldsInfo()
    {
        throw new Main\ArgumentException('The method is not implemented.');
    }

}