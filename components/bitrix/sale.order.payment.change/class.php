<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

/**
 * Class SaleAccountPay
 */
class SaleOrderPaymentChange extends \CBitrixComponent
{
	/** @var  Main\ErrorCollection $errorCollection*/
	protected $errorCollection;

	/** @var \Bitrix\Sale\Order $order */
	protected $order = null;
	protected bool $isRefreshPrice = false;

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $params List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new Main\ErrorCollection();

		$params['PATH_TO_PAYMENT'] = trim((string)($params['PATH_TO_PAYMENT'] ?? ''));
		if ($params['PATH_TO_PAYMENT'] === '')
		{
			$params['PATH_TO_PAYMENT'] = '/personal/order/payment/';
		}
		elseif (mb_substr($params['PATH_TO_PAYMENT'], -1) !== '/')
		{
			$params['PATH_TO_PAYMENT'] .= '/';
		}

		$params['SET_TITLE'] = (string)($params['SET_TITLE'] ?? 'Y');

		if (!isset($params["ELIMINATED_PAY_SYSTEMS"]) || !is_array($params["ELIMINATED_PAY_SYSTEMS"]))
		{
			$params["ELIMINATED_PAY_SYSTEMS"] = array();
		}

		$params['REFRESH_PRICES'] = (string)($params['REFRESH_PRICES'] ?? 'N');
		if ($params['REFRESH_PRICES'] === 'Y')
		{
			$this->isRefreshPrice = true;
		}
		else
		{
			$params['REFRESH_PRICES'] = 'N';
		}

		$params['ALLOW_INNER'] = (string)($params['ALLOW_INNER'] ?? 'N');
		if ($params['ALLOW_INNER'] !== 'Y')
		{
			$params['ALLOW_INNER'] = 'N';
		}
		$params['ONLY_INNER_FULL'] = (string)($params['ONLY_INNER_FULL'] ?? 'N');
		if ($params['ONLY_INNER_FULL'] !== 'Y')
		{
			$params['ONLY_INNER_FULL'] = 'N';
		}
		if (!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
		{
			$params['ALLOW_INNER'] = 'N';
			$params['ONLY_INNER_FULL'] = 'N';
		}

		// region Hidden parameters
		$params['NAME_CONFIRM_TEMPLATE'] = 'confirm_template';

		$params['TEMPLATE_PATH'] = $this->getTemplateName();

		if (empty($params['ACCOUNT_NUMBER']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_ERROR_ORDER_NOT_EXISTS")));
		}

		if (empty($params['PAYMENT_NUMBER']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_ERROR_PAYMENT_NOT_EXISTS")));
		}

		$params['INNER_PAYMENT_SUM'] = (float)($params['INNER_PAYMENT_SUM'] ?? 0);
		$params['AJAX_DISPLAY'] = (string)($params['AJAX_DISPLAY'] ?? 'N');
		$params['NEW_PAY_SYSTEM_ID'] = (int)($params['NEW_PAY_SYSTEM_ID'] ?? 0);
		$params['RETURN_URL'] = trim((string)($params['RETURN_URL'] ?? ''));
		// endregion

		return $params;
	}

	/**
	 * Check Required Modules
	 * @throws Main\SystemException
	 * @return bool
	 */
	protected function checkModules()
	{
		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_MODULE_NOT_INSTALL')));
			return false;
		}
		return true;
	}

	/**
	 * Prepare data to render in new version of component.
	 * @return void
	 */
	protected function buildPaySystemsList()
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return;
		}

		$paymentCollection = $this->order->getPaymentCollection();
		/** @var \Bitrix\Sale\Payment $payment */
		if ((int)$this->arResult['PAYMENT']['ID'] > 0)
		{
			$payment = $paymentCollection->getItemById($this->arResult['PAYMENT']['ID']);
		}

		if (empty($payment))
		{
			$payment = $paymentCollection->createItem();
		}

		$paySystemList = PaySystem\Manager::getListWithRestrictions($payment);

		foreach ($paySystemList as $paySystemElement)
		{
			if (!in_array($paySystemElement['ID'], $this->arParams['ELIMINATED_PAY_SYSTEMS']))
			{
				if (!empty($paySystemElement["LOGOTIP"]))
				{
					$paySystemElement["LOGOTIP"] = CFile::GetFileArray($paySystemElement['LOGOTIP']);
					$fileTemp = CFile::ResizeImageGet(
						$paySystemElement["LOGOTIP"]["ID"],
						array("width" => "95", "height" =>"55"),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);
					$paySystemElement["LOGOTIP"] = $fileTemp["src"];
				}

				if ($paySystemElement['ID'] == $this->arResult['INNER_ID'])
				{
					$innerData = $paySystemElement;
				}
				else
				{
					$this->arResult['PAYSYSTEMS_LIST'][] = $paySystemElement;
				}
			}
		}

		if (empty($this->arResult['PAYSYSTEMS_LIST']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_EMPTY_PAY_SYSTEM_LIST")));
		}

		if ($this->arParams['ALLOW_INNER'] == 'Y' && isset($innerData))
		{
			if ((float)$this->arResult['INNER_PAYMENT_INFO']["CURRENT_BUDGET"] > 0)
			{
				$this->arResult['PAYSYSTEMS_LIST'][] = $innerData;
			}
		}
	}

	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		global $APPLICATION;
		$templateName = null;

		if ($this->checkModules() && $this->errorCollection->isEmpty())
		{
			if ($this->arParams["SET_TITLE"] !== "N")
			{
				$APPLICATION->SetTitle(Loc::getMessage('SOPC_TITLE'));
			}

			$this->prepareData();

			if ($this->order)
			{
				if (
					$this->arParams['ALLOW_INNER'] == 'Y'
					&& (float)$this->arParams['INNER_PAYMENT_SUM'] > 0
				)
				{
					$result = $this->initiateInnerPay();
					$errorMessages = $result->getErrorMessages();

					if (empty($errorMessages))
					{
						die();
					}
					else
					{
						$this->errorCollection->add($result->getErrors());
					}
				}
				elseif ($this->arParams['AJAX_DISPLAY'] === 'Y')
				{
					if ((int)$this->arParams['NEW_PAY_SYSTEM_ID'] == (int)$this->arResult['INNER_ID']
						&& $this->arParams['ALLOW_INNER'] === 'Y'
						&& $this->arResult['IS_ALLOW_PAY'] === 'Y')
					{
						$this->arResult['SHOW_INNER_TEMPLATE'] = 'Y';
					}
					else
					{
						$this->arResult['SHOW_INNER_TEMPLATE'] = 'N';
						$this->orderPayment();
					}

					$templateName = $this->arParams['NAME_CONFIRM_TEMPLATE'];
				}
				else
				{
					$this->buildPaySystemsList();
					if ($this->errorCollection->isEmpty())
					{
						$signer = new Main\Security\Sign\Signer;
						$this->arResult['$signedParams'] = $signer->sign(base64_encode(serialize($this->arParams)), 'sale.order.payment.change');
					}
				}
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_ERROR_ORDER_NOT_EXISTS')));
			}
		}

		$this->formatResultErrors();
		$this->includeComponentTemplate($templateName);
	}

	/**
	 * Move all errors to $this->arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		if (!$this->errorCollection->isEmpty())
		{
			/** @var Main\Error $error */
			foreach ($this->errorCollection->toArray() as $error)
			{
				$this->arResult['errorMessage'][] = $error->getMessage();
			}
		}
	}

	/**
	 * Collect data for component
	 * @throws Main\ArgumentNullException
	 */
	protected function prepareData()
	{
		if ($this->arParams['ACCOUNT_NUMBER'] === '')
		{
			return;
		}
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();
		$this->order = $orderClassName::loadByAccountNumber($this->arParams['ACCOUNT_NUMBER']);

		if (empty($this->order))
		{
			return;
		}
		$paymentClassName = $registry->getPaymentClassName();
		$paymentList = $paymentClassName::getList(
			array(
				"filter" => array("ACCOUNT_NUMBER" => $this->arParams['PAYMENT_NUMBER']),
				"select" => array('*')
			)
		);

		$this->arResult['PAYMENT'] = $paymentList->fetch();

		$this->arResult['IS_ALLOW_PAY'] = $this->order->isAllowPay() ? 'Y' : 'N';

		$this->arResult['INNER_ID'] = PaySystem\Manager::getInnerPaySystemId();

		if ($this->arParams['ALLOW_INNER'] == 'Y')
		{
			$this->loadInnerData();
		}
	}

	/**
	 * Initiate inner payment
	 * @return Main\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function initiateInnerPay()
	{
		$result = new Sale\Result();

		global $USER;

		if (!$USER->IsAuthorized())
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return $result;
		}

		$budget = $this->arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'];
		if ($budget <= 0)
		{
			$result->addError(new Main\Error(Loc::getMessage('SOPC_LOW_BALANCE')));
			return $result;
		}

		/** @var \Bitrix\Sale\Payment $payment */
		$paymentCollection = $this->order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($this->arResult['PAYMENT']['ID']);
		$paySystemObject = PaySystem\Manager::getObjectById($this->arResult['INNER_ID'] );

		$sum = $payment->getSum();
		$paymentSum = (float)$this->arParams['INNER_PAYMENT_SUM'];
		if ($budget >= $sum && $paymentSum >= $sum)
		{
			$payment->setFields(array(
					'PAY_SYSTEM_ID' => $paySystemObject->getField('ID'),
					'PAY_SYSTEM_NAME' => $paySystemObject->getField('NAME')
				)
			);
		}
		else
		{
			$paymentSum = min($paymentSum, $budget);

			if ($this->arParams['ONLY_INNER_FULL'] === 'Y' && $paymentSum < $sum)
			{
				$result->addError(new Main\Error(Loc::getMessage('SOPC_LOW_BALANCE')));
				return $result;
			}

			$rest = $sum - $paymentSum;
			$payment->setField('SUM', $rest);

			/** @var Sale\Payment $newPayment */
			$payment = $paymentCollection->createItem();
			$paymentResult = $payment->setFields(
				array(
					'SUM' => $paymentSum,
					'CURRENCY'=> $this->order->getCurrency(),
					'PAY_SYSTEM_ID' => $paySystemObject->getField('ID'),
					'PAY_SYSTEM_NAME' => $paySystemObject->getField('NAME')
				)
			);

			if (!$paymentResult->isSuccess())
			{
				$result->addError(new Main\Error(Loc::getMessage('SOPC_LOW_BALANCE')));
				return $result;
			}
		}

		$this->order->save();

		return $paySystemObject->initiatePay($payment, null, PaySystem\BaseServiceHandler::STRING);
	}

	/**
	 * Load user account data
	 * @return void
	 */
	protected function loadInnerData()
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return;
		}

		$accountList = CSaleUserAccount::GetList(
			array("CURRENCY" => "ASC"),
			array(
				"USER_ID" => (int)($USER->GetID()),
				"CURRENCY" => $this->order->getCurrency()
			),
			false,
			false,
			array("ID", "CURRENT_BUDGET", "CURRENCY", "TIMESTAMP_X")
		);

		if ($account = $accountList->Fetch())
		{
			$currencyList = CCurrencyLang::GetFormatDescription($account["CURRENCY"]);
			$account['FORMATED_CURRENCY'] = trim(str_replace("#", "", $currencyList['FORMAT_STRING']));
			$this->arResult['INNER_PAYMENT_INFO'] = $account;
		}
	}

	/**
	 * Ordering payment for calling in ajax callback
	 * @return void
	 */
	protected function orderPayment()
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return;
		}

		if ($this->arParams['ALLOW_INNER'] !== 'Y' && (int)$this->arParams['NEW_PAY_SYSTEM_ID'] == (int)$this->arResult['INNER_ID'])
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_ERROR_ORDER_PAYMENT_SYSTEM')));
			return;
		}

		$paySystemObject  = PaySystem\Manager::getObjectById((int)$this->arParams['NEW_PAY_SYSTEM_ID']);
		if (empty($paySystemObject))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_ERROR_ORDER_PAYMENT_SYSTEM')));
			return;
		}

		\Bitrix\Sale\DiscountCouponsManagerBase::freezeCouponStorage();

		/** @var \Bitrix\Sale\Payment $payment */
		$paymentCollection = $this->order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($this->arResult['PAYMENT']['ID']);
		$paymentResult = $payment->setFields(array(
				'PAY_SYSTEM_ID' => $paySystemObject->getField('ID'),
				'PAY_SYSTEM_NAME' => $paySystemObject->getField('NAME')
			)
		);

		if (!$paymentResult->isSuccess())
		{
			\Bitrix\Sale\DiscountCouponsManagerBase::unFreezeCouponStorage();
			$this->errorCollection->add($paymentResult->getErrors());
			return;
		}

		if ($this->isRefreshPrice)
		{
			$oldOrderPrice = $this->order->getPrice();
			$this->order->refreshData();
			if (count($this->order->getPaymentCollection()) > 1)
			{
				$newOrderPrice = $this->order->getPrice();
				$paymentSum = $payment->getSum();
				$payment->setFieldNoDemand('SUM', $paymentSum + ($newOrderPrice - $oldOrderPrice));
			}
		}

		$resultSaving = $this->order->save();

		\Bitrix\Sale\DiscountCouponsManagerBase::unFreezeCouponStorage();

		if ($resultSaving->isSuccess())
		{
			if ($this->arResult['IS_ALLOW_PAY'] == 'Y')
			{

				$this->arResult['ORDER_ID'] = $this->order->getField("ACCOUNT_NUMBER");
				$this->arResult['ORDER_DATE'] = $this->order->getDateInsert()->toString();
				$this->arResult['PAYMENT_ID'] = $payment->getField("ACCOUNT_NUMBER");
				$this->arResult['PAY_SYSTEM_NAME'] = $payment->getField("PAY_SYSTEM_NAME");
				$this->arResult['IS_CASH'] = $paySystemObject->isCash() || $paySystemObject->getField("ACTION_FILE") === 'cash';
				$this->arResult['NAME_CONFIRM_TEMPLATE'] = $this->arParams['NAME_CONFIRM_TEMPLATE'];

				if ($paySystemObject->getField('NEW_WINDOW') === 'Y')
				{
					$this->arResult["PAYMENT_LINK"] = $this->arParams['PATH_TO_PAYMENT'] . "?ORDER_ID=" . $this->order->getField("ACCOUNT_NUMBER") . "&PAYMENT_ID=" . $payment->getField('ACCOUNT_NUMBER');
				}
				else
				{
					if ($returnUrl = $this->arParams['RETURN_URL'])
					{
						$paySystemObject->getContext()->setUrl($returnUrl);
					}

					$paySystemBufferedOutput = $paySystemObject->initiatePay($payment, null, PaySystem\BaseServiceHandler::STRING);
					if ($paySystemBufferedOutput->isSuccess())
					{
						$this->arResult["TEMPLATE"] = $paySystemBufferedOutput->getTemplate();
					}
					else
					{
						$this->errorCollection->add($paySystemBufferedOutput->getErrors());
					}
				}
			}
		}
		else
		{
			$this->errorCollection->add($resultSaving->getErrors());
		}
	}
}
