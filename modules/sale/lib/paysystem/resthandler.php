<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Context;
use Bitrix\Main\Type;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Internals\PaySystemRestHandlersTable;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Internals\Input;
use Bitrix\Sale\Helpers\Rest;

/**
 * Class RestHandler
 * @package Sale\Handlers\PaySystem
 */
class RestHandler extends PaySystem\ServiceHandler
{
	private $handlerFields = array();

	private const FORM_MODE = 'form';
	private const CHECKOUT_MODE = 'checkout';
	private const IFRAME_MODE = 'iframe';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 * @throws \Bitrix\Main\SystemException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$request = Context::getCurrent()->getRequest();
		}

		$mode = $this->getMode();

		if ($mode === self::CHECKOUT_MODE)
		{
			return $this->initiateCheckoutPay($payment, $request);
		}

		if ($mode === self::IFRAME_MODE)
		{
			return $this->initiateIframePay($payment);
		}

		return $this->initiateFormPay($payment, $request);
	}

	private function initiateCheckoutPay(Payment $payment, Request $request): ServiceResult
	{
		$result = new ServiceResult();

		$settings = $this->getHandlerSettings();

		$actionUri = $settings['CHECKOUT_DATA']['ACTION_URI'] ?? null;
		if (!isset($actionUri))
		{
			$result->addError(new Error(Loc::getMessage('SALE_HANDLERS_REST_HANDLER_ERROR_URI_MISSING')));
			return $result;
		}

		if (!$this->canCheckout($payment, $request))
		{
			$template = $this->getCheckoutFormTemplate($payment);
		}
		else
		{
			$params = $this->getCheckoutPayParams($payment, $request);
			$requestResult = Rest\Http::sendRequest($actionUri, $params);
			if (!$requestResult->isSuccess())
			{
				$result->addErrors($requestResult->getErrors());
				return $result;
			}

			$requestData = $requestResult->getData();
			if (empty($requestData['PAYMENT_URL']) || empty($requestData['PAYMENT_ID']))
			{
				if (!empty($requestData['PAYMENT_ERRORS']) && is_array($requestData['PAYMENT_ERRORS']))
				{
					foreach ($requestData['PAYMENT_ERRORS'] as $error)
					{
						$result->addError(new Error($error));
					}

					return $result;
				}

				$result->addError(new Error(Loc::getMessage('SALE_HANDLERS_REST_HANDLER_ERROR_DATA_MISSING')));
				return $result;
			}

			$result->setPsData(['PS_INVOICE_ID' => $requestData['PAYMENT_ID']]);
			$url = $requestData['PAYMENT_URL'];

			$result->setPaymentUrl($url);

			$qrCode = ((new PaySystem\BarcodeGenerator())->generate($url));
			if ($qrCode)
			{
				$result->setQr(base64_encode($qrCode));
			}

			$template = $this->getCheckoutPayTemplate($url);
		}

		if ($this->initiateMode === static::STREAM)
		{
			echo $template;
		}
		else
		{
			$result->setTemplate($template);
		}

		return $result;
	}

	private function needMoreCheckoutParams(array $settings, array $params): bool
	{
		$checkoutSettings = $settings['CHECKOUT_DATA'];
		if (isset($checkoutSettings['FIELDS']))
		{
			return !empty(array_diff_key($checkoutSettings['FIELDS'], $params));
		}

		return false;
	}

	private function getCheckoutPayParams(Payment $payment, Request $request): array
	{
		$params = [];

		$checkoutSettings = $this->getHandlerSettings()['CHECKOUT_DATA'];
		if (isset($checkoutSettings['FIELDS']))
		{
			$params = $this->getQueryDataFromFields($payment, $checkoutSettings['FIELDS']);
		}

		$requestData = $request->toArray();
		foreach ($requestData as $field => $value)
		{
			if (isset($checkoutSettings['FIELDS'][$field]))
			{
				$params[$field] = $value;
			}
		}

		return array_merge($params, $this->getSystemParams($payment));
	}

	private function getCheckoutFormTemplate(Payment $payment): string
	{
		$settings = $this->getHandlerSettings();
		$formSettings = $settings['CHECKOUT_DATA'];

		$template = '<div class="mb-4" id="rest-checkout">';
		$template .= '<form name="rest-checkout-form" id="rest-checkout-form">';

		if (isset($formSettings['FIELDS']))
		{
			$template .= $this->getTemplateFromFields($payment, $formSettings['FIELDS']);
		}

		$template .= '<input type="hidden" name="BX_PAYSYSTEM_ID" value="'.$this->service->getField('ID').'">';
		$template .= '<input name="button" value="'.Loc::getMessage('SALE_HANDLERS_REST_HANDLER_BUTTON_PAID').'" type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;">';
		$template .= '</form>';
		$template .= '</div>';

		$messages = Loc::loadLanguageFile(__FILE__);
		$template .= '
			<script>
				BX.message(' . \CUtil::PhpToJSObject($messages) . ');

				(function() {
					"use strict";

					if (!BX.Sale)
					{
						BX.Sale = {};
					}

					if (BX.Sale.RestHandler)
					{
						return;
					}

					BX.Sale.RestHandler = {
						init: function(params)
						{
							this.formNode = BX(params.formId);
							this.paysystemBlockNode = BX(params.paysystemBlockId);
							this.ajaxUrl = params.ajaxUrl;
							this.paymentId = params.paymentId;
							this.paySystemId = params.paySystemId;
							this.isAllowedSubmitting = true;
							this.returnUrl = params.returnUrl;

							this.bindEvents();
						},

						bindEvents: function()
						{
							BX.bind(this.formNode, "submit", BX.proxy(this.sendRequest, this));
						},

						sendRequest: function(e)
						{
							e.preventDefault();

							if (!this.isAllowedSubmitting)
							{
								return;
							}

							var data,
								formData = this.getAllFormData(),
								submitButton = this.formNode.querySelector("input[type=\"submit\"]"),
								i;

							if (submitButton)
							{
								submitButton.disabled = true;
							}
							this.isAllowedSubmitting = false;

							data = {
								sessid: BX.bitrix_sessid(),
								PAYMENT_ID: this.paymentId,
								PAYSYSTEM_ID: this.paySystemId,
								RETURN_URL: this.returnUrl,
							};

							for (i in formData)
							{
								if (formData.hasOwnProperty(i))
								{
									data[i] = formData[i];
								}
							}

							BX.ajax({
								method: "POST",
								dataType: "json",
								url: this.ajaxUrl,
								data: data,
								onsuccess: BX.proxy(function (result) {
									if (result.status === "success")
									{
										this.isAllowedSubmitting = true;
										this.updateTemplateHtml(result.template);
									}
									else if (result.status === "error")
									{
										this.isAllowedSubmitting = true;
										this.showErrorTemplate(result.buyerErrors);
										BX.onCustomEvent("onPaySystemAjaxError", [result.buyerErrors]);
									}
								}, this)
							});
						},

						getAllFormData: function()
						{
							var prepared = BX.ajax.prepareForm(this.formNode),
								i;

							for (i in prepared.data)
							{
								if (prepared.data.hasOwnProperty(i) && i === "")
								{
									delete prepared.data[i];
								}
							}

							return !!prepared && prepared.data ? prepared.data : {};
						},

						updateTemplateHtml: function (html)
						{
							BX.html(this.paysystemBlockNode, html)
						},

						showErrorTemplate: function(errors)
						{
							var errorsList = [
								BX.message("SALE_HANDLERS_REST_HANDLER_TEMPLATE_ERROR_MESSAGE_HEADER"),
							];
							if (errors)
							{
								for (var error in errors)
								{
									if (errors.hasOwnProperty(error))
									{
										errorsList.push(errors[error]);
									}
								}
							}

							errorsList.push(BX.message("SALE_HANDLERS_REST_HANDLER_TEMPLATE_ERROR_MESSAGE_FOOTER"));

							var resultDiv = BX.create("div", {
								props: {className: "alert alert-danger"},
								html: errorsList.join("<br />"),
							});

							this.paysystemBlockNode.innerHTML = "";
							this.paysystemBlockNode.appendChild(resultDiv);
						},
					}
				})();

				BX.ready(function() {
					BX.Sale.RestHandler.init({
						formId: "rest-checkout-form",
						paysystemBlockId: "rest-checkout",
						ajaxUrl: "/bitrix/tools/sale_ps_ajax.php",
						paymentId: "' . \CUtil::JSEscape($payment->getId()) . '",
						paySystemId: "' . \CUtil::JSEscape($payment->getPaymentSystemId()) . '",
						returnUrl: "' . $this->service->getContext()->getUrl() . '",
					});
				});
			</script>
		';

		return $template;
	}

	private function getCheckoutPayTemplate($paymentUrl): string
	{
		$template = '<a class="btn btn-lg btn-success" style="border-radius: 32px;" href="' . $paymentUrl . '">';
		$template .= Loc::getMessage('SALE_HANDLERS_REST_HANDLER_BUTTON_PAID');
		$template .= '</a>';

		return $template;
	}

	private function initiateIframePay(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$template = $this->getIframeTemplate($payment);
		if ($this->initiateMode === static::STREAM)
		{
			echo $template;
		}
		else
		{
			$result->setTemplate($template);
		}

		return $result;
	}

	private function initiateFormPay(Payment $payment, ?Request $request): ServiceResult
	{
		$result = $this->showTemplate($payment, "template");
		if (!$result->isSuccess())
		{
			$result = new ServiceResult();
			$template = $this->getDefaultTemplate($payment);
			if ($this->initiateMode === static::STREAM)
			{
				echo $template;
			}
			else
			{
				$result->setTemplate($template);
			}
		}

		$result->setPaymentUrl($this->getPaymentUrl($payment));

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getPaymentUrl(Payment $payment): string
	{
		if ($this->isAllowAutoRedirect())
		{
			$settings = $this->getHandlerSettings();

			if (isset($settings['FORM_DATA']['FIELDS']))
			{
				$queryParams = $this->getQueryDataFromFields($payment, $settings['FORM_DATA']['FIELDS']);
			}
			elseif (isset($settings['FORM_DATA']['PARAMS']))
			{
				$queryParams = $this->getQueryDataFromParams($payment, $settings['FORM_DATA']['PARAMS']);
			}

			$queryParams['BX_PAYSYSTEM_ID'] = $this->service->getField('ID');
			return (new Uri($settings['FORM_DATA']['ACTION_URI']))->addParams($queryParams)->getLocator();
		}

		return '';
	}

	/**
	 * @return bool
	 */
	private function isAllowAutoRedirect(): bool
	{
		$result = false;

		$settings = $this->getHandlerSettings();
		$formSettings = $settings['FORM_DATA'];
		if (!empty($formSettings['ACTION_URI']) && mb_strtoupper($formSettings['METHOD']) === 'GET')
		{
			$result = true;
			if (isset($formSettings['FIELDS']))
			{
				foreach ($formSettings['FIELDS'] as $value)
				{
					if ((isset($value['VISIBLE']) && $value['VISIBLE'] === 'Y') || is_array($value['CODE']))
					{
						$result = false;
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param array $fields
	 * @return array
	 */
	private function getQueryDataFromFields(Payment $payment, array $fields): array
	{
		$result = [];
		$businessValueParams = $this->getParamsBusValue($payment);

		foreach ($fields as $key => $value)
		{
			if (!is_array($value['CODE']) && !empty($value['CODE']))
			{
				$result[$key] = $businessValueParams[$value['CODE']];
			}
		}

		return $result;
	}

	private function getIframeTemplate(Payment $payment): string
	{
		\CJSCore::Init("loader");

		$settings = $this->getHandlerSettings();
		$formSettings = $settings['IFRAME_DATA'];

		$iframeData = $this->getIframePayParams($payment);

		$actionUriHost = (new Uri($formSettings['ACTION_URI']))->getHost();

		$template = "
			<div class='rest-paysystem-wrapper' id='rest-paysystem-wrapper'>
				<iframe
					src='{$formSettings['ACTION_URI']}'
					class='rest-payment-frame'
					name='restPaymentFrame'
					id='rest-payment-frame'
					style='border: none; height: 350px; width: 100%'
					sandbox='allow-forms allow-scripts allow-modals allow-top-navigation allow-same-origin'
				>
					<div class='alert alert-danger'>" . Loc::getMessage('SALE_HANDLERS_REST_HANDLER_ERROR_IFRAME') . "</div>
				</iframe>
				<div class='alert alert-info'>" . Loc::getMessage('SALE_HANDLERS_REST_HANDLER_TEMPLATE_WARNING_RETURN') . "</div>
			</div>
		";
		$template .= '
			<script>
				BX.ready(function() {
					var iframe = document.getElementById("rest-payment-frame");
					var loader = null;

					if (BX.Loader)
					{
						loader = new BX.Loader({
							target: iframe.parentElement,
							size: iframe.offsetHeight / 2,
						});
						loader.show();
					}

					var parent = iframe.parentElement;
					iframe.style.width = parent.clientWidth;

					iframe.onload = function () {
						if (loader)
						{
							loader.hide();
						}

						var paymentFrame = iframe.contentWindow;
						if (paymentFrame)
						{
							var iframeData = ' . \CUtil::PhpToJSObject($iframeData) . ';
							iframeData.BX_COMPUTED_STYLE = JSON.parse(JSON.stringify(window.getComputedStyle(parent)));

							paymentFrame.postMessage(iframeData, "' . $formSettings['ACTION_URI'] . '");
						}
					}
					iframe.onerror = function () {
						if (loader)
						{
							loader.hide();
						}

						var restPaysystemWrapper = document.getElementById("rest-paysystem-wrapper");
						restPaysystemWrapper.innerHTML = "";
						restPaysystemWrapper.appendChild(
							BX.create("div", {
								props: {className: "alert alert-danger"},
								text: "' . Loc::getMessage('SALE_HANDLERS_REST_HANDLER_ERROR_IFRAME_LOAD') . '",
							})
						);
					}

					window.addEventListener("message", function (event) {
						try
						{
							var originHost = new URL(event.origin).hostname;
						}
						catch(error)
						{
							return;
						}

						if (originHost !== "' . $actionUriHost . '")
						{
							return;
						}

						if (event.data.width && parseInt(event.data.width) > 0)
						{
							iframe.style.width = event.data.width + "px";
						}
						if (event.data.height && parseInt(event.data.height) > 0)
						{
							iframe.style.height = event.data.height + "px";
						}
					}, false);
				});
			</script>
		';

		return $template;
	}

	private function getIframePayParams(Payment $payment): array
	{
		$params = [];

		$formSettings = $this->getHandlerSettings()['IFRAME_DATA'];
		if (isset($formSettings['FIELDS']))
		{
			$params = $this->getQueryDataFromFields($payment, $formSettings['FIELDS']);
		}

		return array_merge($params, $this->getSystemParams($payment));
	}

	/**
	 * @param Payment $payment
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getDefaultTemplate(Payment $payment): string
	{
		$settings = $this->getHandlerSettings();
		$formSettings = $settings['FORM_DATA'];

		$template = '<form action="'.htmlspecialcharsbx($formSettings['ACTION_URI']).'" method="'.htmlspecialcharsbx($formSettings['METHOD']).'" name="rest-handler-form">';

		if (isset($formSettings['FIELDS']))
		{
			$template .= $this->getTemplateFromFields($payment, $formSettings['FIELDS']);
		}
		elseif (isset($formSettings['PARAMS']))
		{
			$template .= $this->getTemplateFromParams($payment, $formSettings['PARAMS']);
		}

		$template .= '<input type="hidden" name="BX_PAYSYSTEM_ID" value="' . $this->service->getField('ID') . '">';
		$template .= '<input type="hidden" name="BX_RETURN_URL" value="' . $this->service->getContext()->getUrl() . '">';
		$template .= '<input name="button" value="' . Loc::getMessage('SALE_HANDLERS_REST_HANDLER_BUTTON_PAID') . '" type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;">';
		$template .= '</form>';

		return $template;
	}

	/**
	 * @param Payment $payment
	 * @param array $fields
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getTemplateFromFields(Payment $payment, array $fields): string
	{
		$template = '';

		foreach ($fields as $key => $value)
		{
			$input = $this->getInputParams($payment, $value);
			$template .= $this->createInput($key, $input);
		}

		return $template;
	}

	/**
	 * @param Payment $payment
	 * @param $value
	 * @return string[]
	 */
	private function getInputParams(Payment $payment, $value): array
	{
		$result = [
			'TYPE' => 'STRING',
			'VALUE' => '',
			'NAME' => '',
			'HIDDEN' => 'Y',
		];

		$settings = $this->getHandlerSettings();
		$params = $this->getParamsBusValue($payment);

		if (is_array($value['CODE']))
		{
			/** $value from FIELDS like array */
			$input = $value['CODE'];
			$result['NAME'] = $input['NAME'] ?? '';
		}
		else
		{
			/** $value from FIELDS like map on CODES */
			$code = $value['CODE'];
			$input = $settings['CODES'][$code];
			$result['VALUE'] = $params[$code];
			$result['NAME'] = $input['NAME'] ?? '';
		}

		if (isset($input['INPUT']['TYPE']))
		{
			$result['TYPE'] = mb_strtoupper($input['INPUT']['TYPE']);
		}

		if (isset($value['VISIBLE']) && $value['VISIBLE'] === 'Y')
		{
			$result['HIDDEN'] = 'N';
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param array $input
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	private function createInput(string $name, array $input): string
	{
		if (!in_array($input['TYPE'], ['Y/N', 'STRING', 'ENUM'], true))
		{
			return '';
		}

		if ($input['HIDDEN'] === 'Y')
		{
			return Input\Manager::getEditHtml($name, $input);
		}

		$inputHtml = '';
		$input['STYLE'] = "max-width: 300px;";

		if ($input['TYPE'] === 'Y/N')
		{
			$input['CLASS'] = "form-check-input";

			$inputHtml .= '<div class="form-check">';
			$inputHtml .= Input\Manager::getEditHtml($name, $input);
			$inputHtml .= '<label class="form-check-label">'.$input['NAME'].'</label>';
			$inputHtml .= '</div>';
		}
		elseif ($input['TYPE'] === 'STRING' || $input['TYPE'] === 'ENUM')
		{
			$input['CLASS'] = "form-control";

			$inputHtml .= '<div class="form-group">';
			$inputHtml .= '<label>'.$input['NAME'].'</label>';
			$inputHtml .= Input\Manager::getEditHtml($name, $input);
			$inputHtml .= '</div>';
		}

		return $inputHtml;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		$settings = $this->getHandlerSettings();
		return $settings['CURRENCY'];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientType($psMode)
	{
		$settings = $this->getHandlerSettings();
		
		$clientType = (string)($settings['CLIENT_TYPE'] ?? '');
		if ($clientType && ClientType::isValid($clientType))
		{
			return $clientType;
		}
		
		return parent::getClientType($psMode);
	}

	protected function includeDescription(): array
	{
		$fields = $this->getHandlerFields();
		$settings = $this->getHandlerSettings();

		return [
			'NAME' => $fields['NAME'] ?? '',
			'SORT' => $fields['SORT'] ?? 100,
			'CODES' => $settings['CODES'] ?: []
		];
	}

	private function getHandlerFields(): array
	{
		if (!$this->handlerFields)
		{
			$handler = $this->service->getField('ACTION_FILE');
			$dbRes = PaySystemRestHandlersTable::getList([
				'filter' => ['CODE' => $handler]
			]);
			$data = $dbRes->fetch();

			if ($data)
			{
				$this->handlerFields = $data;
			}
		}

		return $this->handlerFields;
	}

	/**
	 * @return array
	 */
	private function getHandlerSettings(): array
	{
		$handlerFields = $this->getHandlerFields();
		return $handlerFields['SETTINGS'] ?? [];
	}

	private function getMode(): string
	{
		$settings = $this->getHandlerSettings();

		if (!empty($settings['IFRAME_DATA']))
		{
			return self::IFRAME_MODE;
		}

		if (!empty($settings['CHECKOUT_DATA']))
		{
			return self::CHECKOUT_MODE;
		}

		return self::FORM_MODE;
	}

	private function getSystemParams(Payment $payment): array
	{
		$params['BX_SYSTEM_PARAMS'] = [
			'RETURN_URL' => $this->service->getContext()->getUrl(),
			'PAYSYSTEM_ID' => $this->service->getField('ID'),
			'PAYMENT_ID' => $payment->getId(),
			'SUM' => $payment->getSum(),
			'CURRENCY' => $payment->getField('CURRENCY'),
		];

		$invoiceId = $payment->getField('PS_INVOICE_ID');
		if (isset($invoiceId))
		{
			$params['BX_SYSTEM_PARAMS']['EXTERNAL_PAYMENT_ID'] = $invoiceId;
		}

		return $params;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new ServiceResult();

		$result->setPsData($this->getPsData($request));
		$result->setOperationType(ServiceResult::MONEY_COMING);

		return $result;
	}

	/**
	 * @param Request $request
	 * @return array
	 */
	private function getPsData(Request $request): array
	{
		$psData = [
			'PS_STATUS' => 'Y',
			'PS_STATUS_CODE' => 'Y',
			'PS_RESPONSE_DATE' => new Type\DateTime(),
			'PAY_VOUCHER_DATE' => new Type\Date(),
		];

		if ($psInvoiceId = $request->get('PS_INVOICE_ID'))
		{
			$psData['PS_INVOICE_ID'] = $psInvoiceId;
		}

		if ($psStatusCode = $request->get('PS_STATUS_CODE'))
		{
			$psData['PS_STATUS_CODE'] = $psStatusCode;
		}

		if ($psStatusDescription = $request->get('PS_STATUS_DESCRIPTION'))
		{
			$psData['PS_STATUS_DESCRIPTION'] = $psStatusDescription;
		}

		if ($psStatusMessage = $request->get('PS_STATUS_MESSAGE'))
		{
			$psData['PS_STATUS_MESSAGE'] = $psStatusMessage;
		}

		if ($psSum = $request->get('PS_SUM'))
		{
			$psData['PS_SUM'] = $psSum;
		}

		if ($psCurrency = $request->get('PS_CURRENCY'))
		{
			$psData['PS_CURRENCY'] = $psCurrency;
		}

		if ($psRecurringToken = $request->get('PS_RECURRING_TOKEN'))
		{
			$psData['PS_RECURRING_TOKEN'] = $psRecurringToken;
		}

		if ($psCardNumber = $request->get('PS_CARD_NUMBER'))
		{
			$psData['PS_CARD_NUMBER'] = $psCardNumber;
		}

		return $psData;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('PAYMENT_ID');
	}

	/**
	 * For PARAMS compatibility
	 *
	 * @param Payment $payment
	 * @param array $params
	 * @return array
	 */
	private function getQueryDataFromParams(Payment $payment, array $params): array
	{
		$result = [];
		$businessValueParams = $this->getParamsBusValue($payment);

		foreach ($params as $key => $value)
		{
			$result[$key] = $businessValueParams[$value];
		}

		return $result;
	}

	/**
	 * For PARAMS compatibility
	 *
	 * @param Payment $payment
	 * @param array $params
	 * @return string
	 */
	private function getTemplateFromParams(Payment $payment, array $params): string
	{
		$template = '';
		$businessValueParams = $this->getParamsBusValue($payment);

		foreach ($params as $key => $value)
		{
			$template .= '<input type="hidden" name="'.htmlspecialcharsbx($key).'" value="'.htmlspecialcharsbx($businessValueParams[$value]).'">';
		}

		return $template;
	}

	public function canCheckout(Payment $payment, Request $request = null): bool
	{
		if ($request === null)
		{
			$request = Context::getCurrent()->getRequest();
		}

		$mode = $this->getMode();
		if ($mode !== self::CHECKOUT_MODE)
		{
			return false;
		}

		$settings = $this->getHandlerSettings();
		$actionUri = $settings['CHECKOUT_DATA']['ACTION_URI'] ?? null;
		if (!isset($actionUri))
		{
			return false;
		}

		$params = $this->getCheckoutPayParams($payment, $request);

		return !$this->needMoreCheckoutParams($settings, $params);
	}
}
