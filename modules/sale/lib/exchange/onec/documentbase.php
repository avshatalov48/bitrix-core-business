<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Main;
use Bitrix\Sale\Exchange;
use Bitrix\Main\Type;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\PriceMaths;

/**
 * Class DocumentBase
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class DocumentBase
{
	const CML_LANG_ID = 'ru';

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
        return Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php', self::CML_LANG_ID);
    }

	/**
	 * @return array
	 */
	static protected function getMessageExport()
	{
		return array_merge(
			Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php', self::CML_LANG_ID),
			Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/general/export.php', self::CML_LANG_ID)
		);
	}

	/**
	 * @return int
	 */
	public function getTypeId()
	{
		return DocumentType::UNDEFINED;
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
    public static function resolveRawDocumentTypeId(array $document)
    {
        $message = self::getMessage();
        $operation = $document['#'][$message['CC_BSC1_OPERATION']][0]['#'];
        return static::resolveDocumentTypeId($operation);
    }

	/**
	 * @param array string
	 * @return int
	 */
	public static function resolveDocumentTypeId($operation)
	{
		$typeId = DocumentType::UNDEFINED;

		$message = self::getMessage();

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

	public static function resolveDocumentTypeName($typeId)
	{
		if(!DocumentType::IsDefined($typeId))
		{
			throw new Main\ArgumentOutOfRangeException('Is not defined', DocumentType::FIRST, DocumentType::LAST);
		}

		$message = self::getMessage();

		$name = '';
		if($typeId == DocumentType::ORDER)
		{
			$name = $message["CC_BSC1_ORDER"];
		}
		elseif($typeId == DocumentType::PAYMENT_CASH)
		{
			$name = $message["CC_BSC1_PAYMENT_C"];
		}
		elseif($typeId == DocumentType::PAYMENT_CASH_LESS)
		{
			$name = $message["CC_BSC1_PAYMENT_B"];
		}
		elseif($typeId == DocumentType::PAYMENT_CARD_TRANSACTION)
		{
			$name = $message["CC_BSC1_PAYMENT_A"];
		}
		elseif($typeId == DocumentType::SHIPMENT)
		{
			$name = $message["CC_BSC1_SHIPMENT"];
		}

		return $name;
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
			case 'MARKING_GROUP':
				$result = self::resolveMarkingGroupParams($value, $fieldsInfo);
				break;
			case 'MARKINGS':
				$result = self::resolveMarkingParams($value, $fieldsInfo);
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
					if($propertyValue <> '')
						$properties[$propertyName] = $propertyValue;
				}
			}

			foreach($fieldsInfo['PROPERTIES']['FIELDS'] as $name => $fieldInfo)
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
                if($traitValue <> '')
                    $traits[$traitName] = $traitValue;
            }

            foreach($fieldsInfo['FIELDS'] as $name => $fieldInfo)
            {
                $fieldValue = '';
                if($name == 'PROPERTY_VALUE_BASKET')
                {
                    foreach($traits as $k=>$v)
                    {
                        $namePropertyBaslet = $message["CC_BSC1_PROP_BASKET"];
                        if (mb_strpos($k, $namePropertyBaslet."#") === 0)
                        {
							$position = mb_strpos($k, $namePropertyBaslet."#");
							$idBasketProperty = mb_substr($k, $position + mb_strlen($namePropertyBaslet."#"));

                            self::internalizeFields($v);

                            $result['PROP_BASKET'][$idBasketProperty] = $v;	//TODO: check && fix
                        }
                    }
                }
                elseif($name == 'TYPE_OF_NOMENKLATURA')
                {
                    if($traits[$message["CC_BSC1_ITEM_TYPE"]] == $message["CC_BSC1_ITEM"])
                        $fieldValue = Exchange\ImportBase::ITEM_ITEM;
                    elseif($traits[$message["CC_BSC1_ITEM_TYPE"]] == $message["CC_BSC1_SERVICE"])
                        $fieldValue = Exchange\ImportBase::ITEM_SERVICE;

                    self::internalizeFields($fieldValue, $fieldInfo['FIELDS']['VALUE']);

					$result['ITEM_TYPE'] = $fieldValue;	//TODO: check && fix
                }
                elseif(!empty($traits[$message["CC_BSC1_".$name]]))
                {
                    $fieldValue = $traits[$message["CC_BSC1_".$name]];

                    self::internalizeFields($fieldValue, $fieldInfo['FIELDS']['VALUE']);

                    $result[$name] = $fieldValue;	//TODO: check && fix
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
				if(is_array($field[0]["#"]))
				{
					if(!empty($field[0]["#"][$message["CC_BSC1_".$name]][0]["#"]))
					{
						$fieldValue = $field[0]["#"][$message["CC_BSC1_".$name]][0]["#"];
						self::internalizeFields($fieldValue, $info);
						$result[$name] = $fieldValue;
					}
				}
				else
				{
					$fieldValue='';
					if($result==null)
					{
						$fieldValue = $field[0]["#"];
						self::internalizeFields($fieldValue, $info);
					}
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
	protected static function resolveMarkingGroupParams($value, array $fieldsInfo)
	{
		$result = null;
		$message = self::getMessage();

		if (is_array($value["#"][$message["CC_BSC1_MARKING_GROUP"]])
			&& !empty($value["#"][$message["CC_BSC1_MARKING_GROUP"]]))
		{
			$field = $value["#"][$message["CC_BSC1_MARKING_GROUP"]];

			foreach($fieldsInfo['FIELDS'] as $name => $info)
			{
				if(is_array($field[0]["#"]))
				{
					if(!empty($field[0]["#"][$message["CC_BSC1_MARKING_GROUP_".$name]][0]["#"]))
					{
						$fieldValue = $field[0]["#"][$message["CC_BSC1_MARKING_GROUP_".$name]][0]["#"];
						self::internalizeFields($fieldValue, $info);
						$result[$name] = $fieldValue;
					}
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
	protected static function resolveMarkingParams(array $value, array $fieldsInfo)
	{
		$result = [];
		$message = self::getMessage();

		if (is_array($value["#"][$message["CC_BSC1_MARKINGS"]][0]["#"][$message["CC_BSC1_MARKING"]])
			&& !empty($value["#"][$message["CC_BSC1_MARKINGS"]][0]["#"][$message["CC_BSC1_MARKING"]]))
		{
			$fields = $value["#"][$message["CC_BSC1_MARKINGS"]][0]["#"][$message["CC_BSC1_MARKING"]];

			foreach($fields as $k=>$field)
			{
				foreach($fieldsInfo['FIELDS'] as $name => $info)
				{
					if(!empty($field["#"][$message["CC_BSC1_MARKING_".$name]][0]["#"]))
					{
						$fieldValue = $field["#"][$message["CC_BSC1_MARKING_".$name]][0]["#"];
						self::internalizeFields($fieldValue, $info);

						$result[] = $fieldValue;
					}
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
					{
						$discountPrice = DoubleVal($priceone - $price);
					}
				}
				elseif ($priceone > 0)
				{
					$price = $priceone;
				}

				$vatRate = null;
				if (!empty($item['TAXES']['TAX_VALUE']))
				{
					$taxValue = (float)$item['TAXES']['TAX_VALUE'];
					$vatRate = $taxValue / 100;
				}

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
                    'MARKING_GROUP' => !empty($item['MARKING_GROUP']['CODE']) ? $item['MARKING_GROUP']['CODE']:null,
                    'MARKINGS' => !empty($item['MARKINGS']) ? $item['MARKINGS']:null,
                    'TAX' => array(
                        'VAT_RATE' => $vatRate,
                        'VAT_INCLUDED' => !empty($item['TAXES']['IN_PRICE']) ? $item['TAXES']['IN_PRICE'] : 'Y'//if tax is null then always included by default
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
		if($value<>'')
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
                    $typeId = self::resolveRawDocumentTypeId($document);
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

								if (intval($taxValueTmp) > intval($taxValue))
								{
									$taxName = $item['NAME'];
									$taxValue = $taxValueTmp;
								}
                            }
                        }
                    }

                    if(intval($taxValue)>0)
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
	static protected function getFieldsInfo()
    {
        throw new Main\ArgumentException('The method is not implemented.');
    }

	public function openNodeDirectory($level, $name)
	{
		$name = static::getLangByCodeField($name);
		return str_repeat("\t", $level)."<".$name.">\n";
	}

	public function closeNodeDirectory($level, $name)
	{
		$name = static::getLangByCodeField($name);
		return str_repeat("\t", $level)."</".$name.">\n";
	}

	protected function formatXMLNode($level, $name, $value, $parameters=array())
	{
		$params = '';
		if(count($parameters)>0)
		{
			foreach ($parameters as $code=>$v)
				$params .= ' '.static::getLangByCodeField($code).'="'.$v.'" ';
		}

		$name = static::getLangByCodeField($name);
		return str_repeat("\t", $level)."<".$name.$params.">".\CDataXML::xmlspecialchars($value)."</".$name.">\n";
	}

	/**
	 * @param int $level
	 * @return string
	 */
	public function output($level=0)
	{
		$fields = $this->getFieldValues();
		return $this->outputXml($fields, $level);
	}

	/**
	 * @param array $fields
	 * @return string
	 */
	protected function outputXml(array $fields, $level=0)
	{
		$xml = '';
		foreach ($fields as $name=>$value)
		{
			if(is_array($value))
			{
				switch ($name)
				{
					case 'REK_VALUES':
						$xml .= $this->outputXmlRekValue($level, $name, $value);
						break;
					case 'ITEMS':
						$xml .= $this->outputXmlItems($level, $name, $value);
						break;
					case 'AGENT':
						$profile = new ProfileDocument();
						$profile->setFields($value);
						$xml .= $this->openNodeDirectory($level, 'AGENTS');
						$xml .= $this->openNodeDirectory($level+1, $profile->getNameNodeDocument());
						$xml .= $profile->output($level+1);
						$xml .= $this->closeNodeDirectory($level+1, $profile->getNameNodeDocument());
						$xml .= $this->closeNodeDirectory($level, 'AGENTS');
						break;
					case 'STORIES':
						$xml .= $this->outputXmlStories($level, $name, $value);
						break;
					case 'TAXES':
						$xml .= $this->outputXmlTaxes($level, $name, $value);
						break;
					case 'DISCOUNTS':
						$xml .= $this->outputXmlDiscounts($level, $name, $value);
						break;
				}
			}
			else
				$xml .= $this->formatXMLNode($level, $name, $value);

		}
		return $xml;
	}

	/**
	 * @param $code
	 * @return string
	 */
	public static function getLangByCodeField($code)
	{
		$message = static::getMessageExport();

		if(isset($message['CC_BSC1_'.$code]))
		{
			return $message['CC_BSC1_'.$code];
		}
		elseif(isset($message['SALE_EXPORT_'.$code]))
		{
			return $message['SALE_EXPORT_'.$code];
		}
		else
		{
			return $code;
		}
	}

	protected function outputXmlRekValue($level, $name, $value)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, $name);
		foreach ($value as $list)
		{
			$result .= $this->openNodeDirectory($level+1, 'REK_VALUE');
			foreach ($list as $k=>$v)
				$result .= $this->formatXMLNode($level+2, $k, $v);
			$result .= $this->closeNodeDirectory($level+1, 'REK_VALUE');
		}
		$result .= $this->closeNodeDirectory($level+0, $name);
		return $result;
	}

	protected function outputXmlUnits($level, $name, $list)
	{
		$result = '';
		$result .= $this->openNodeDirectory($level+0, $name);
		foreach ($list as $k=>$v)
			$result .= $this->formatXMLNode($level+1, $k, $v);
		$result .= $this->closeNodeDirectory($level+0, $name);

		return $result;
	}

	protected function outputXmlBaseUnit($level, $name, $value)
	{
		return $this->formatXMLNode($level+0, $name, '', array(
			"CODE"=>$value,
			"FULL_NAME_UNIT"=>static::getLangByCodeField("SHTUKA"),
			"INTERNATIONAL_ABR"=>static::getLangByCodeField("RCE")
		));
	}

	protected function outputXmlItems($level, $name, $items)
	{
		$result = '';
		$result .= $this->openNodeDirectory($level+0, $name);

		foreach ($items as $item)
		{
			$result .= $this->openNodeDirectory($level+1, 'ITEM');

			foreach ($item as $code=>$value)
			{
				if(is_array($value))
				{
					switch ($code)
					{
						case 'REK_VALUES':
							$result .= $this->outputXmlRekValue($level+2, $code, $value);
							break;
						case 'ITEM_UNIT':
							$result .= $this->outputXmlUnits($level+2, $code, $value);
							break;
						case 'DISCOUNTS':
							$result .= $this->outputXmlDiscounts($level+2, $code, $value);
							break;
						case 'TAX_RATES':
							$result .= $this->outputXmlTaxRates($level+2, $code, array($value));
							break;
						case 'TAXES':
							$result .= $this->outputXmlTaxes($level+2, $code, array($value));
							break;
					}
				}
				elseif ($code == 'BASE_UNIT')
					$result .= $this->outputXmlBaseUnit($level+2, $code, $value);
				else
					$result .= $this->formatXMLNode($level+2, $code, $value);
			}

			$result .= $this->closeNodeDirectory($level+1, 'ITEM');
		}
		$result .= $this->closeNodeDirectory($level+0, $name);
		return $result;
	}

	protected function outputXmlAddress($level, $addresses)
	{
		$result = '';
		foreach ($addresses as $code=>$address)
		{
			if(is_array($address))
			{
				foreach ($address as $values)
				{
					$result .= $this->openNodeDirectory($level+0, $code);
					foreach ($values as $k=>$v)
						$result .= $this->formatXMLNode($level+1, $k, $v);
					$result .= $this->closeNodeDirectory($level+0, $code);
				}
			}
			else
				$result .= $this->formatXMLNode($level+0, $code, $address);
		}
		return $result;
	}

	protected function outputXmlStories($level, $name, $stories)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, $name);

		foreach ($stories as $store)
		{
			$result .= $this->openNodeDirectory($level+1, 'STORY');
			foreach ($store as $code=>$value)
			{
				if(is_array($value))
				{
					switch ($code)
					{
						case 'ADDRESS':
						case 'CONTACTS':
							$result .= $this->openNodeDirectory($level+2, $code);
							$result .= $this->outputXmlAddress($level+3, $value);
							$result .= $this->closeNodeDirectory($level+2, $code);
							break;
					}
				}
				else
					$result .= $this->formatXMLNode($level+2, $code, $value);
			}
			$result .= $this->closeNodeDirectory($level+1, 'STORY');
		}
		$result .= $this->closeNodeDirectory($level+0, $name);

		return $result;
	}

	protected function outputXmlTaxRates($level, $name, $taxes)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, $name);

		foreach ($taxes as $tax)
		{
			$result .= $this->openNodeDirectory($level+1, 'RATE');

			foreach ($tax as $k=>$v)
				$result .= $this->formatXMLNode($level+2, $k, $v);

			$result .= $this->closeNodeDirectory($level+1, 'RATE');
		}
		$result .= $this->closeNodeDirectory($level+0, $name);
		return $result;
	}

	protected function outputXmlTaxes($level, $name, $taxes)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, $name);

		foreach ($taxes as $tax)
		{
			$result .= $this->openNodeDirectory($level+1, 'TAX');

			foreach ($tax as $k=>$v)
				$result .= $this->formatXMLNode($level+2, $k, $v);

			$result .= $this->closeNodeDirectory($level+1, 'TAX');
		}
		$result .= $this->closeNodeDirectory($level+0, $name);
		return $result;
	}

	protected function outputXmlDiscounts($level, $name, $discounts)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, $name);
		$result .= $this->openNodeDirectory($level+1, 'DISCOUNT');

		foreach ($discounts as $k=>$v)
			$result .= $this->formatXMLNode($level+2, $k, $v);

		$result .= $this->closeNodeDirectory($level+1, 'DISCOUNT');
		$result .= $this->closeNodeDirectory($level+0, $name);
		return $result;
	}

	public function getNameNodeDocument()
	{
		return 'DOCUMENT';
	}
}