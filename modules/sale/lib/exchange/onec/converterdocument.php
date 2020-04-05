<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;

/**
 * Class ConverterDocumentOrder
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentOrder extends Converter
{
    /**
     * @param $documentImport
     * @return array
     * @throws ArgumentException
     */
    public function resolveParams($documentImport)
    {
        if(!($documentImport instanceof DocumentImport))
            throw new ArgumentException("Document must be instanceof DocumentImport");

        $result = array();

        $params = $documentImport->getFieldValues();

		$availableFields = Order::getAvailableFields();

		foreach ($availableFields as $k)
        {
            switch($k)
            {
                case 'ID_1C':
				case 'VERSION_1C':
					if(isset($params[$k]))
						$fields[$k] = $params[$k];
					break;
				case 'COMMENTS':
					if(isset($params['COMMENT']))
						$fields[$k] = $params['COMMENT'];
					break;
                case 'CANCELED':
					$value='';
					if(isset($params['CANCELED']))
						$value = $params['CANCELED'];

					if($value == 'Y')
					{
						$fields[$k] = 'Y';
					}
					else
					{
						$v='';
						if(isset($params['REK_VALUES']['CANCEL']))
						{
							$v = $params['REK_VALUES']['CANCEL'];
						}

						if($v == 'Y')
						{
							$fields[$k] = 'Y';
						}
						else
						{
							$fields[$k] = 'N';
						}
					}
                    break;
				case 'DATE_INSERT':
					if(isset($params['1C_TIME']) && $params['1C_TIME'] instanceof DateTime)
						$fields[$k] = $params['1C_TIME'];
					break;
				case 'STATUS_ID':
					if(isset($params['REK_VALUES']['1C_STATUS_ID']))
					{
						$settings = $this->getSettings();
						if($settings->changeStatusFor(EntityType::ORDER) == 'Y')
							$fields[$k] = $params['REK_VALUES']['1C_STATUS_ID'];
					}
					break;
				case '1C_PAYED_DATE':
				case '1C_DELIVERY_DATE':
					if(isset($params['REK_VALUES'][$k]))
						$fields[$k] = $params['REK_VALUES'][$k];
				break;
            }
        }

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['ITEMS'] = isset($params['ITEMS'])? $params['ITEMS']:array();
		$result['TAXES'] = isset($params['TAXES'])? $params['TAXES']:array();

        return $result;
    }

    /**
     * @param null $order
     * @param array $fields
     * @throws ArgumentException
     */
    public function sanitizeFields($order=null, array &$fields)
    {
        if(!empty($order) && !($order instanceof Order))
            throw new ArgumentException("Entity must be instanceof Order");

        if(empty($order))
        {
            $fields['DATE_STATUS'] = new DateTime() ;
            $fields['DATE_UPDATE'] = new DateTime();
        }
        else
		{
			if(isset($fields['DATE_INSERT']))
				unset($fields['DATE_INSERT']);
		}

        if(isset($fields['ID']))
            unset($fields['ID']);

        if(isset($fields['1C_PAYED_DATE']))
            unset($fields['1C_PAYED_DATE']);

        if(isset($fields['1C_DELIVERY_DATE']))
            unset($fields['1C_DELIVERY_DATE']);
    }
}

/**
 * Class ConverterDocumentShipment
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentShipment extends Converter
{
    /**
     * @param $documentImport
     * @return array
     * @throws ArgumentException
     */
    public function resolveParams($documentImport)
    {
        if(!($documentImport instanceof DocumentImport))
            throw new ArgumentException("Document must be instanceof DocumentImport");

        $result = array();

        $params = $documentImport->getFieldValues();

		$availableFields = Shipment::getAvailableFields();

		foreach ($availableFields as $k)
		{
			switch($k)
			{
				case 'ID_1C':
				case 'VERSION_1C':
					if(isset($params[$k]))
						$fields[$k] = $params[$k];
					break;
				case 'COMMENTS':
					if(isset($params['COMMENT']))
						$fields[$k] = $params['COMMENT'];
					break;
				case 'DELIVERY_DOC_DATE':
					if(isset($params['1C_DATE']))
						$fields[$k] = $params['1C_DATE'];
					break;
				case 'DELIVERY_DOC_NUM':
					if(isset($params['REK_VALUES']['1C_DELIVERY_NUM']))
						$fields[$k] = $params['REK_VALUES']['1C_DELIVERY_NUM'];
					break;
				case 'DEDUCTED':
					$deducted='';
					$cancel='';

					if(isset($params['REK_VALUES']['DEDUCTED']))
						$deducted = $params['REK_VALUES']['DEDUCTED'];
					if(isset($params['REK_VALUES']['CANCEL']))
						$cancel = $params['REK_VALUES']['CANCEL'];

					if($deducted == 'Y')
						$fields[$k] = 'Y';
					elseif($cancel == 'Y')
						$fields[$k] = 'N';
					break;
				case 'ALLOW_DELIVERY':
					$value='';
					if(isset($params['REK_VALUES']['DEDUCTED']))
						$value = $params['REK_VALUES']['DEDUCTED'];

					if($value == 'Y')
						$fields[$k] = 'Y';
					break;
				case 'TRACKING_NUMBER':
					if(isset($params['REK_VALUES']['1C_TRACKING_NUMBER']))
						$fields[$k] = $params['REK_VALUES']['1C_TRACKING_NUMBER'];
					break;
				case 'BASE_PRICE_DELIVERY':
					foreach($params['ITEMS'] as $items)
					{
						foreach($items as $item)
						{
							if($item['TYPE'] == ImportBase::ITEM_SERVICE)
							{
								//if((!empty($shipment)? $shipment->getPrice():'') != $item["PRICE"])
								//{
								//$fields["CUSTOM_PRICE_DELIVERY"] = "Y";
								$fields["BASE_PRICE_DELIVERY"] = $item["PRICE"];
								//$fields["CURRENCY"] = $settings->getCurrency();
								//}
								break 2;
							}
						}
					}
					break;
				case 'DELIVERY_ID':
					$deliverySystemId = 0;
					if(isset($params['REK_VALUES']['DELIVERY_SYSTEM_ID']))
					{
						$deliverySystemId = $params['REK_VALUES']['DELIVERY_SYSTEM_ID'];
					}

					if($deliverySystemId<=0)
					{
						if(isset($params['REK_VALUES']['DELIVERY_SYSTEM_ID_DEFAULT']))
						{
							$deliverySystemId = $params['REK_VALUES']['DELIVERY_SYSTEM_ID_DEFAULT'];
						}
					}

					/** @var ImportSettings $settings */
					$settings = $this->getSettings();

					if($deliverySystemId<=0)
					{
						$deliverySystemId = $settings->shipmentServiceFor($documentImport->getOwnerEntityTypeId());
					}

					if($deliverySystemId<=0)
					{
						$deliverySystemId = $settings->shipmentServiceDefaultFor($documentImport->getOwnerEntityTypeId());
					}

					$fields[$k] = $deliverySystemId;
					break;
			}
		}

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['ITEMS'] = isset($params['ITEMS'])? $params['ITEMS']:array();
		$result['TAXES'] = isset($params['TAXES'])? $params['TAXES']:array();

        return $result;
    }

    /**
     * @param Shipment|null $shipment
     * @param array $fields
     */
    public function sanitizeFields($shipment=null, array &$fields)
    {
        if(!empty($shipment) && !($shipment instanceof Shipment))
            throw new ArgumentException("Entity must be instanceof Shipment");

        foreach($fields as $k=>$v)
        {
            switch($k)
            {
                case 'BASE_PRICE_DELIVERY':
                    if((!empty($shipment)? $shipment->getPrice():'') != $v)
                    {
                        $fields['CURRENCY'] = $this->settings->getCurrency();
                        $fields['CUSTOM_PRICE_DELIVERY'] = "Y";
                    }
                    else
                    {
                        unset($fields['BASE_PRICE_DELIVERY']);
                    }
                    break;
				case 'DELIVERY_ID':
					if(!empty($shipment))
					{
						unset($fields['DELIVERY_ID']);
					}
					break;
            }
        }
        unset($fields['ID']);
    }
}

/**
 * Class ConverterDocumentPayment
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentPayment extends Converter
{
	/**
     * @param $documentImport
     * @return array
     * @throws ArgumentException
     */
    public function resolveParams($documentImport)
    {
        if(!($documentImport instanceof DocumentImport))
            throw new ArgumentException("Document must be instanceof DocumentImport");

        $result = array();

        $params = $documentImport->getFieldValues();

        $availableFields = array_merge(Payment::getAvailableFields(), array('CASH_BOX_CHECKS'));

        /** TODO: huck */
        rsort($availableFields);

        foreach ($availableFields as $k)
		{
			switch($k)
			{
				case 'ID_1C':
				case 'VERSION_1C':
					if(isset($params[$k]))
						$fields[$k] = $params[$k];
					break;
				case 'SUM':
					if(isset($params['AMOUNT']))
						$fields[$k] = $params['AMOUNT'];
					break;
				case 'COMMENTS':
					if(isset($params['COMMENT']))
						$fields[$k] = $params['COMMENT'];
					break;
				case 'PAY_VOUCHER_DATE':
					if(isset($params['REK_VALUES']['1C_PAYED_DATE']))
						$fields[$k] = $params['REK_VALUES']['1C_PAYED_DATE'];
					break;
				case 'PAY_VOUCHER_NUM':
					if(isset($params['REK_VALUES']['1C_PAYED_NUM']))
						$fields[$k] = $params['REK_VALUES']['1C_PAYED_NUM'];
					break;
				case 'PAID':
					$payed='';
					$cancel='';

					if(isset($params['REK_VALUES']['1C_PAYED']))
						$payed = $params['REK_VALUES']['1C_PAYED'];
					if(isset($params['REK_VALUES']['CANCEL']))
						$cancel = $params['REK_VALUES']['CANCEL'];

					if($payed == 'Y')
						$fields[$k] = 'Y';
					elseif($cancel == 'Y')
						$fields[$k] = 'N';
					break;
				case 'IS_RETURN':
					if(isset($params['REK_VALUES']['1C_RETURN']))
					{
						$value = $params['REK_VALUES']['1C_RETURN'];
						if($value == 'Y')
							$fields[$k] = 'Y';
					}
					break;
				case 'PAY_RETURN_COMMENT':
					if(isset($params['REK_VALUES']['1C_RETURN_REASON']))
						$fields[$k] = $params['REK_VALUES']['1C_RETURN_REASON'];
					break;
				case 'PAY_SYSTEM_ID':
					$paySystemId = 0;
					if(isset($params['REK_VALUES']['PAY_SYSTEM_ID']))
					{
						$paySystemId = $params['REK_VALUES']['PAY_SYSTEM_ID'];
					}

					if($paySystemId<=0)
					{
						if(isset($params['REK_VALUES']['PAY_SYSTEM_ID_DEFAULT']))
						{
							$paySystemId = $params['REK_VALUES']['PAY_SYSTEM_ID_DEFAULT'];
						}
					}
					/** @var ImportSettings $settings */
					$settings = $this->getSettings();

					if($paySystemId<=0)
					{
						$paySystemId = $settings->paySystemIdFor($documentImport->getOwnerEntityTypeId());
					}

					if($paySystemId<=0)
					{
						 $paySystemId = $settings->paySystemIdDefaultFor($documentImport->getOwnerEntityTypeId());
					}

					$fields[$k] = $paySystemId;
					break;

				case 'CASH_BOX_CHECKS':
					if(is_array($params[$k]))
					{
						foreach($params[$k] as $property=>$value)
						{
							switch($property)
							{
								case 'ID':
									$cashBoxChecks[$property] = $value;
									break;
								case 'CASHBOX_URL':
									$cashBoxChecks['LINK_PARAMS']['URL'] = $value;
									break;
								case 'CASHBOX_FISCAL_SIGN':
									$cashBoxChecks['LINK_PARAMS']['FISCAL_SIGN'] = $value;
									break;
								case 'CASHBOX_REG_NUMBER_KKT':
									$cashBoxChecks['LINK_PARAMS']['REG_NUMBER_KKT'] = $value;
							}
						}
					}
					break;
			}
		}

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['CASH_BOX_CHECKS'] = isset($cashBoxChecks)? $cashBoxChecks:array();

        return $result;
    }

    /**
     * @param Payment|null $payment
     * @param array $fields
     */
    public function sanitizeFields($payment=null, array &$fields)
    {
        if(!empty($payment) && !($payment instanceof Payment))
            throw new ArgumentException("Entity must be instanceof Payment");

        foreach($fields as $k=>$v)
        {
            switch($k)
            {
                case 'AMOUNT':
                    if(!empty($payment) && $payment->isPaid())
                    {
                        unset($fields['SUM']);
                    }
                    break;
				case 'PAY_SYSTEM_ID':
					if(!empty($payment))
					{
						unset($fields['PAY_SYSTEM_ID']);
					}
					break;
            }
        }

        if(empty($payment))
        {
            $fields['CURRENCY'] = $this->settings->getCurrency();
        }
        unset($fields['ID']);
    }
}

/**
 * Class ConverterDocumentProfile
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentProfile extends Converter
{
    /**
     * @param $documentImport
     * @return array
     * @throws ArgumentException
     */
    public function resolveParams($documentImport)
    {
        if(!($documentImport instanceof DocumentImport))
            throw new ArgumentException("Document must be instanceof DocumentImport");

        $result = array();

        $params = $documentImport->getFieldValues();
        foreach($params as $k=>$v)
        {
            switch($k)
            {
                case 'VERSION':
                    if(!empty($v))
                        $profile['VERSION_1C'] = $v;
                    break;
                case 'XML_ID':
                case 'OFICIAL_NAME':
                case 'FULL_NAME':
                case 'INN':
                case 'KPP':
                case 'OKPO_CODE':
                case 'EGRPO':
                case 'OKVED':
                case 'OKDP':
                case 'OKOPF':
                case 'OKFC':
                    //case 'OKPO':
                    if(!empty($v))
                        $profile[$k] = $v;
                    break;
                case 'ITEM_NAME':
                    if(!empty($v))
                    {
                        $profile[$k] = $v;
                        $profile['AGENT_NAME'] = $v;
                    }
                    break;
                case 'REGISTRATION_ADDRESS':
                case 'UR_ADDRESS':
                case 'ADDRESS':
                    foreach($params[$k] as $name=>$values)
                    {
                        if($name == 'ADDRESS_FIELD')
                        {
                            foreach($values as $nameAddres=>$valuesAddres)
                            {
                                $profile[$k][$nameAddres] = $valuesAddres['VALUE'];
                            }
                        }
                        else
                        {
                            $profile[$k][$name] = $values;
                        }
                    }
                    break;
                case 'CONTACTS':
                    foreach($params[$k]['CONTACT'] as $name=>$values)
                    {
                        $profile['CONTACT'][$name] = $values['VALUE'];
                    }

                    $profile['CONTACT']['EMAIL'] = !empty($profile['CONTACT']['MAIL_NEW'])? $profile['CONTACT']['MAIL_NEW']:null;
                    $profile['CONTACT']['PHONE'] = !empty($profile['CONTACT']['WORK_PHONE_NEW'])? $profile['CONTACT']['WORK_PHONE_NEW']:null;

                    break;
                case 'REPRESENTATIVES':
                    foreach($params[$k]['REPRESENTATIVE'] as $name=>$values)
                    {
                        if($name == 'CONTACT_PERSON')
                        {
                            $profile['CONTACT'][$name] = $values['ITEM_NAME'];
                        }
                    }
                    break;
            }
        }

        if(!empty($profile["OKPO_CODE"]))
            $profile["OKPO"] = $profile["OKPO_CODE"];

        if(strlen($profile["OFICIAL_NAME"]) > 0 && strlen($profile["INN"]) > 0)
            $profile["TYPE"] = "UR";
        elseif(strlen($profile["INN"]) > 0)
            $profile["TYPE"] = "IP";
        else
            $profile["TYPE"] = "FIZ";

        if(!empty($profile))
        {
            $property = array();
            foreach($profile as $name => $value)
            {
                switch($name)
                {
                    case 'ID':
                    case 'VERSION':
                    case 'ITEM_NAME':
                    case 'OFICIAL_NAME':
                    case 'FULL_NAME':
                    case 'INN':
                    case 'KPP':
                    case 'OKPO_CODE':
                    case 'EGRPO':
                    case 'OKVED':
                    case 'OKDP':
                    case 'OKOPF':
                    case 'OKFC':
                    case 'OKPO':
                        $property[$name] = $value;
                        break;
                    case 'CONTACT':
                        $property["EMAIL"] = $value["MAIL_NEW"];
                        $property["PHONE"] = $value["WORK_PHONE_NEW"];
                        break;
                    case 'REPRESENTATIVE':
                        $property["CONTACT_PERSON"] = $value["CONTACT_PERSON"];
                        break;
                    case 'REGISTRATION_ADDRESS':
                    case 'UR_ADDRESS':
                        foreach($value as $nameProperty => $valueProperty)
                        {
                            if(strlen($valueProperty) > 0 && empty($property[$nameProperty]))
                                $property[$nameProperty] = $valueProperty;
                        }
                        $property["ADDRESS_FULL"] = $value["PRESENTATION"];
                        $property["INDEX"] = $value["POST_CODE"];
                        break;
                    case 'ADDRESS':
                        foreach($value as $nameProperty => $valueProperty)
                        {
                            if(strlen($valueProperty) > 0 && empty($property["F_".$nameProperty]))
                                $property["F_".$nameProperty] = $valueProperty;
                        }
                        $property["F_ADDRESS_FULL"] = $value["PRESENTATION"];
                        $property["F_INDEX"] = $value["POST_CODE"];
                        break;
                }
            }
        }

		$result['TRAITS'] = isset($profile)? $profile:array();
		$result['ORDER_PROPS'] = isset($property)? $property:array();

        return $result;
    }

    /**
     * @param null $entity
     * @param array $fields
     * @return array
     */
    public function sanitizeFields($entity = null, array &$fields)
    {
        // TODO: Implement sanitizeFields() method.
    }
}