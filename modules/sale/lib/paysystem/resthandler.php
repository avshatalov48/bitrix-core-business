<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Internals\PaySystemRestHandlersTable;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Internals\Input;

/**
 * Class RestHandler
 * @package Sale\Handlers\PaySystem
 */
class RestHandler extends PaySystem\ServiceHandler
{
	private $handlerSettings = array();

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 * @throws \Bitrix\Main\SystemException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
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
			if (is_array($value['CODE']))
			{
				$result = [];
				break;
			}

			$result[$key] = $businessValueParams[$value['CODE']];
		}

		return $result;
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

		$template .= '<input type="hidden" name="BX_PAYSYSTEM_ID" value="'.$this->service->getField('ID').'">';
		$template .= '<input name="button" value="'.Loc::getMessage('SALE_HANDLERS_REST_HANDLER_BUTTON_PAID').'" type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;">';
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
	 * @return array
	 */
	public function getDescription()
	{
		$settings = $this->getHandlerSettings();

		return array(
			'NAME' => $settings['NAME'],
			'CODES' => $settings['CODES'] ?: []
		);
	}

	/**
	 * @return array
	 */
	private function getHandlerSettings()
	{
		if (!$this->handlerSettings)
		{
			$handler = $this->service->getField('ACTION_FILE');
			$dbRes = PaySystemRestHandlersTable::getList(array('filter' => array('CODE' => $handler)));
			$data = $dbRes->fetch();
			if ($data)
			{
				$this->handlerSettings = $data['SETTINGS'];
			}
		}

		return $this->handlerSettings;
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
}