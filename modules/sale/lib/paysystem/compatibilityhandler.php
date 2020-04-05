<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Payment;
use Bitrix\Main\IO;

Loc::loadMessages(__FILE__);

class CompatibilityHandler extends ServiceHandler implements ICheckable
{
	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return array();
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return false;
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return array();
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$result = new ServiceResult();

		$this->getParamsBusValue($payment);

		if ($this->initiateMode == self::STREAM)
		{
			$this->includeFile('payment.php');
		}
		else if ($this->initiateMode == self::STRING)
		{
			ob_start();
			$content = $this->includeFile('payment.php');

			$buffer = ob_get_contents();
			if (strlen($buffer) > 0)
				$content = $buffer;

			$result->setTemplate($content);
			ob_end_clean();
		}

		if ($this->service->getField('ENCODING') != '')
		{
			define("BX_SALE_ENCODING", $this->service->getField('ENCODING'));
			AddEventHandler('main', 'OnEndBufferContent', array($this, 'OnEndBufferContent'));
		}

		return $result;
	}

	/**
	 * @param Payment|null $payment
	 * @param string $template
	 * @return ServiceResult
	 */
	public function showTemplate(Payment $payment = null, $template = '')
	{
		$result = new ServiceResult();

		$this->getParamsBusValue($payment);

		if ($this->initiateMode == self::STREAM)
		{
			$this->includeFile('payment.php');
		}
		else if ($this->initiateMode == self::STRING)
		{
			ob_start();
			$content = $this->includeFile('payment.php');

			$buffer = ob_get_contents();
			if (strlen($buffer) > 0)
				$content = $buffer;

			$result->setTemplate($content);
			ob_end_clean();
		}

		if ($this->service->getField('ENCODING') != '')
		{
			define("BX_SALE_ENCODING", $this->service->getField('ENCODING'));
			AddEventHandler('main', 'OnEndBufferContent', array($this, 'OnEndBufferContent'));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	public function getParamsBusValue(Payment $payment = null)
	{
		$orderId = 0;
		$orderFields = array();
		$paymentFields = array();
		$relatedData = array();

		if ($payment !== null)
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			$order = $paymentCollection->getOrder();

			if ($order->getId() > 0)
			{
				$orderId = $order->getId();
				$orderFields = $order->getFieldValues();
				$paymentFields = $payment->getFieldValues();
			}
		}

		if ($orderId <= 0)
		{
			$data = Manager::getHandlerDescription($this->service->getField('ACTION_FILE'));
			$templateParams = $this->getExtraParams();

			$relatedData['TEMPLATE_PARAMS'] = array();
			foreach ($data['CODES'] as $codeId => $code)
			{
				if (array_key_exists($codeId, $templateParams))
				{
					$code['VALUE'] = $templateParams[$codeId];
					$relatedData['TEMPLATE_PARAMS'][$codeId] = $code;
				}
			}

			if (isset($templateParams['ORDER']))
				$orderFields = $templateParams['ORDER'];

			if (isset($templateParams['BASKET_ITEMS']))
			{
				$relatedData['BASKET_ITEMS'] = $templateParams['BASKET_ITEMS'];
				unset($templateParams['BASKET_ITEMS']);
			}

			if (isset($templateParams['TAX_LIST']))
			{
				$relatedData['TAX_LIST'] = $templateParams['TAX_LIST'];
				unset($templateParams['TAX_LIST']);
			}

			$paymentFields['PAY_SYSTEM_ID'] = $this->service->getField('ID');
		}

		\CSalePaySystemAction::InitParamArrays($orderFields, $orderId, '', $relatedData, $paymentFields);

		return $GLOBALS['SALE_INPUT_PARAMS'];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return string
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$this->getParamsBusValue($payment);
		$this->includeFile('result_rec.php');
		die();
	}

	/**
	 * @param $file
	 * @return string
	 */
	private function includeFile($file)
	{
		global $APPLICATION, $USER, $DB;
		$documentRoot = Application::getDocumentRoot();

		$path = $documentRoot.$this->service->getField('ACTION_FILE').'/'.$file;
		if (IO\File::isFileExists($path))
		{
			$result = require $path;
			if ($result !== false && $result !== 1)
				return $result;
		}

		return '';
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getEntityIds(Request $request)
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array();
	}

	public function getPrice(Payment $payment)
	{
		$paySystemId = $payment->getPaymentSystemId();
		$psData = Manager::getById($paySystemId);
		$psData['PSA_ACTION_FILE'] = $psData['ACTION_FILE'];
		$psData['PSA_TARIF'] = $psData['TARIF'];

		/** @var \Bitrix\Sale\PaymentCollection $collection */
		$collection = $payment->getCollection();

		/** @var \Bitrix\sale\Order $order */
		$order = $collection->getOrder();

		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $order->getShipmentCollection();

		$shipment = null;

		/** @var \Bitrix\Sale\Shipment $item */
		foreach ($shipmentCollection as $item)
		{
			if (!$item->isSystem())
			{
				$shipment = $item;
				break;
			}
		}

		/** @var \Bitrix\Sale\PropertyValueCollection $propertyCollection */
		$propertyCollection = $order->getPropertyCollection();

		/** @var \Bitrix\Sale\PropertyValue $deliveryLocation */
		$deliveryLocation = $propertyCollection->getDeliveryLocation();

		if ($shipment)
			return \CSalePaySystemsHelper::getPSPrice($psData, $payment->getSum(), $shipment->getPrice(), $deliveryLocation->getValue());

		return 0;
	}

	/**
	 * @return bool
	 */
	public function isPayableCompatibility()
	{
		$documentRoot = Application::getDocumentRoot();
		$actionFile = $this->service->getField('ACTION_FILE');

		return IO\File::isFileExists($documentRoot.$actionFile.'/tarif.php');
	}

	/**
	 * @return bool
	 */
	public function isCheckableCompatibility()
	{
		$documentRoot = Application::getDocumentRoot();
		$actionFile = $this->service->getField('ACTION_FILE');

		return IO\File::isFileExists($documentRoot.$actionFile.'/result.php');
	}

	/**
	 * @param Payment $payment
	 * @return string|boolean
	 */
	public function check(Payment $payment)
	{
		if ($this->isCheckableCompatibility())
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();

			\CSalePaySystemAction::InitParamArrays($order->getFieldValues(), $order->getId(), '', array(), $payment->getFieldValues());

			$res = $this->includeFile('result.php');
			return $res;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getDescription()
	{
		$data = array();

		$documentRoot = Application::getDocumentRoot();
		$handler = $this->service->getField('ACTION_FILE');

		$psTitle = '';
		$arPSCorrespondence = array();

		$actionFile = $documentRoot.$handler.'/.description.php';
		if (IO\File::isFileExists($actionFile))
		{
			require $actionFile;

			if ($arPSCorrespondence)
			{
				$codes = $this->convertCodesToNewFormat($arPSCorrespondence);

				if ($codes)
					$data = array('NAME' => $psTitle, 'SORT' => 100, 'CODES' => $codes);
			}
		}

		return $data;
	}

	/**
	 * @param array $arPSCorrespondence
	 * @return array
	 */
	private function convertCodesToNewFormat(array $arPSCorrespondence)
	{
		if ($arPSCorrespondence)
		{
			foreach ($arPSCorrespondence as $i => $property)
			{
				if ($property['TYPE'] == 'SELECT')
				{
					$options = array();
					foreach ($property['VALUE'] as $code => $value)
						$options[$code] = $value['NAME'];

					$arPSCorrespondence[$i] = array(
						'NAME' => $property['NAME'],
						'INPUT' => array(
							'TYPE' => 'ENUM',
							'OPTIONS' => $options
						),
						'SORT' => $property['SORT'],
					);
				}
				else if ($property['TYPE'] == 'FILE')
				{
					$arPSCorrespondence[$i] = array(
						'NAME' => $property['NAME'],
						'INPUT' => array(
							'TYPE' => 'FILE'
						),
						'SORT' => $property['SORT'],
					);
				}

				if (array_key_exists('DESCR', $property))
					$arPSCorrespondence[$i]['DESCRIPTION'] = $property['DESCR'];

				if (!isset($arPSCorrespondence[$i]['GROUP']))
					$arPSCorrespondence[$i]['GROUP'] = (isset($property['GROUP'])) ? $property['GROUP'] : 'PS_OTHER';
			}

			return $arPSCorrespondence;
		}

		return array();
	}

	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = array(
			'ORDER' => array(
				'ACCOUNT_NUMBER' => 'A1',
				'DATE_INSERT' => new DateTime(),
				'CURRENCY' => 'RUB',
				'SHOULD_PAY' => 2000,
				'PRICE' => 2000,
				'SUM_PAID' => 0,
			),
			'TAX_LIST' => array(
				array(
					'TAX_NAME' => Loc::getMessage('SALE_COMPATIBILITY_TAX'),
					'IS_IN_PRICE' => 'Y',
					'VALUE_MONEY' => 200,
					'VALUE' => 0.1,
					'IS_PERCENT' => 10
				)
			),
			'BASKET_ITEMS' => array(
				array(
					'NAME' => Loc::getMessage('SALE_COMPATIBILITY_BASKET_ITEM_NAME'),
					'IS_VAT_IN_PRICE' => true,
					'PRICE' => 900,
					'VAT_RATE' => 0.1,
					'QUANTITY' => 2,
					'MEASURE_NAME' => Loc::getMessage('SALE_COMPATIBILITY_BASKET_ITEM_MEASURE'),
					'CURRENCY' => 'RUB'
				)
			),
			'SELLER_CITY' => Loc::getMessage('SALE_COMPATIBILITY_BANK_CITY'),
			'SELLER_BCITY' => Loc::getMessage('SALE_COMPATIBILITY_BANK_CITY'),
			'SELLER_ADDRESS' => Loc::getMessage('SALE_COMPATIBILITY_BANK_ADDRESS'),
			'SELLER_PHONE' => '+76589321451',
			'SELLER_BANK_IBAN' => '1989 000 92',
			'SELLER_BANK_SWIFT' => '0000000000',
			'SELLER_BANK_PHONE' => '+76589321451',
			'SELLER_BANK' => Loc::getMessage('SALE_COMPATIBILITY_BANK_NAME'),
			'SELLER_RS' => '0000 0000 0000 0000 0000',
			'SELLER_BANK_ACCNO' => '0000 0000 0000 0000 0000',
			'SELLER_INN' => '000011112222',
			'SELLER_EU_INN' => '000011112222',
			'SELLER_REG' => '1615 00 785',
			'SELLER_KPP' => '123456789',
			'SELLER_NAME' => Loc::getMessage('SALE_COMPATIBILITY_COMPANY_NAME'),
			'SELLER_BIK' => '0123456',
			'SELLER_BIC' => '0123456',
			'SELLER_BANK_BLZ' => '0123456',
			'SELLER_KS' => '1111 1111 1111 1111',
			'SELLER_BANK_ROUTENO' => '1111 1111 1111 1111',
			'BUYER_NAME' => Loc::getMessage('SALE_COMPATIBILITY_BUYER_COMPANY_NAME'),
			'BUYER_INN' => '0123456789',
			'BUYER_PHONE' => '79091234523',
			'BUYER_FAX' => '88002000600',
			'BUYER_ADDRESS' => Loc::getMessage('SALE_COMPATIBILITY_BUYER_COMPANY_ADDRESS'),
			'BUYER_PAYER_NAME' => Loc::getMessage('SALE_COMPATIBILITY_BUYER_NAME_CONTACT'),
			'SELLER_DIR_POS' => Loc::getMessage('SALE_COMPATIBILITY_DIRECTOR_POSITION'),
			'SELLER_DIR' => Loc::getMessage('SALE_COMPATIBILITY_DIRECTOR_NAME'),
			'SELLER_ACC_POS' => Loc::getMessage('SALE_COMPATIBILITY_ACCOUNTANT_POSITION'),
			'SELLER_ACC' => Loc::getMessage('SALE_COMPATIBILITY_ACCOUNTANT_NAME'),
			'SELLER_EMAIL' => 'my@company.com',
			'COMMENT1' => Loc::getMessage('SALE_COMPATIBILITY_COMMENT1'),
			'COMMENT2' => Loc::getMessage('SALE_COMPATIBILITY_COMMENT2'),
		);

		if (Loader::includeModule('crm') && Loader::includeModule('iblock'))
		{
			$arFilter = array(
				'IBLOCK_ID' => intval(\CCrmCatalog::EnsureDefaultExists()),
				'CHECK_PERMISSIONS' => 'N',
				'!PROPERTY_TYPE' => 'G'
			);

			$dbRes = \CIBlockProperty::GetList(array(), $arFilter);
			while ($arRow = $dbRes->Fetch())
				$data['BASKET_ITEMS'][0]['PROPERTY_'.$arRow['ID']] = 'test';
		}

		return $data;
	}

}