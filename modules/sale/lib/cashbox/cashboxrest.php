<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Result;

/**
 * Class CashboxRest
 * @package Bitrix\Sale\Cashbox
 */
class CashboxRest extends Cashbox implements IPrintImmediately, ICheckable
{
	/**
	 * @return mixed
	 */
	public function getHandlerCode()
	{
		$settings = $this->getField("SETTINGS");
		return $settings["REST"]["REST_CODE"];
	}

	/**
	 * @param $handlerCode
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getHandlerParams($handlerCode)
	{
		$handlerList = Manager::getRestHandlersList();
		return $handlerList[$handlerCode];
	}

	/**
	 * @param $handlerCode
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getConfigStructure($handlerCode)
	{
		$handlerParams = self::getHandlerParams($handlerCode);

		$result = $handlerParams["SETTINGS"]["CONFIG"];
		$result["REST"] = [
			"REQUIRED" => "Y",
			"ITEMS" => [
				"REST_CODE" => [
					"TYPE" => "STRING",
					"LABEL" => Loc::getMessage("SALE_CASHBOX_REST_HANDLER_CODE"),
					"READONLY" => true,
					"VALUE" => $handlerCode,
				]
			]
		];

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getCheckTypeMap(): array
	{
		return array(
			SellCheck::getType() => 'full_payment',
			SellReturnCashCheck::getType() => 'full_payment',
			SellReturnCheck::getType() => 'full_payment',
			AdvancePaymentCheck::getType() => 'advance',
			AdvanceReturnCashCheck::getType() => 'advance',
			AdvanceReturnCheck::getType() => 'advance',
			PrepaymentCheck::getType() => 'prepayment',
			PrepaymentReturnCheck::getType() => 'prepayment',
			PrepaymentReturnCashCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCashCheck::getType() => 'full_prepayment',
			CreditCheck::getType() => 'credit',
			CreditReturnCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'credit_payment',
		);
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPrintUrl()
	{
		$handlerCode = $this->getHandlerCode();
		$handlerParams = self::getHandlerParams($handlerCode);
		return $handlerParams["SETTINGS"]["PRINT_URL"];
	}

	/**
	 * @return array
	 */
	private function getRequestOptions(): array
	{
		$options = [];

		$handlerCode = $this->getHandlerCode();
		$handlerParams = self::getHandlerParams($handlerCode);
		if (isset($handlerParams['SETTINGS']['HTTP_VERSION']))
		{
			$options['HTTP_CLIENT_OPTIONS'] = [
				'version' => $handlerParams['SETTINGS']['HTTP_VERSION'],
			];
		}

		return $options;
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCheckUrl()
	{
		$handlerCode = $this->getHandlerCode();
		$handlerParams = self::getHandlerParams($handlerCode);
		return $handlerParams["SETTINGS"]["CHECK_URL"];
	}

	/**
	 * @param Check $check
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function buildCheckQuery(Check $check): array
	{
		$data = $check->getDataForCheck();

		$data["uuid"] = static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']);

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data["date_create"];
		$dateTimestamp = $dateTime->getTimestamp();
		$data["date_create"] = $dateTimestamp;

		$data["operation"] = $check::getCalculatedSign();

		$checkTypeMap = self::getCheckTypeMap();
		if (is_array($data["items"])) {
			foreach ($data["items"] as $index => $item) {
				$data["items"][$index]['payment_method'] = $checkTypeMap[$check::getType()];
			}
		}

		$data["number_kkm"] = $this->getField('NUMBER_KKM');
		$data["service_email"] = $this->getField('EMAIL');
		$cashboxParams = $this->getField("SETTINGS");

		// there's no need to pass our REST configuration (i.e. the handler code) to the application
		unset($cashboxParams["REST"]);
		$data["cashbox_params"] = $cashboxParams;

		return $data;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function buildZReportQuery($id): array
	{
		return [];
	}


	/**
	 * @param array $data
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function extractCheckData(array $data): array
	{
		$result = [];

		if (!$data['UUID'])
		{
			return $result;
		}

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($data['UUID']);
		if (empty($checkInfo))
		{
			return $result;
		}

		if ($data['STATUS'] === 'ERROR')
		{
			$result['ERROR'] = [
				'TYPE' => Errors\Error::TYPE,
				'MESSAGE' => $data['ERROR'],
			];
		}

		$result['ID'] = $checkInfo['ID'];
		$result['CHECK_TYPE'] = $checkInfo['TYPE'];

		$check = CheckManager::getObjectById($checkInfo['ID']);
		$dateTime = Main\Type\DateTime::createFromTimestamp($data["PRINT_END_TIME"]);
		$result['LINK_PARAMS'] = [
			Check::PARAM_REG_NUMBER_KKT => $data['REG_NUMBER_KKT'],
			Check::PARAM_FISCAL_DOC_ATTR => $data['FISCAL_DOC_ATTR'],
			Check::PARAM_FISCAL_DOC_NUMBER => $data['FISCAL_DOC_NUMBER'],
			Check::PARAM_FISCAL_RECEIPT_NUMBER => $data['FISCAL_RECEIPT_NUMBER'],
			Check::PARAM_FN_NUMBER => $data['FN_NUMBER'],
			Check::PARAM_SHIFT_NUMBER => $data['SHIFT_NUMBER'],
			Check::PARAM_DOC_SUM => (float)$checkInfo['SUM'],
			Check::PARAM_DOC_TIME => $dateTime->getTimestamp(),
			Check::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
		];

		return $result;
	}

	/**
	 * @param Check $check
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function check(Check $check): Result
	{
		$result = new Result();
		$url = $this->getCheckUrl();
		$checkUUID = $check->getField('EXTERNAL_UUID');
		$queryResult = Sale\Helpers\Rest\Http::sendRequest(
			$url,
			["uuid" => $checkUUID],
			$this->getRequestOptions()
		);
		$response = $queryResult->getData();

		if ($response === false)
		{
			return $result->addError(new Errors\Error(Loc::getMessage("SALE_CASHBOX_REST_DATA_ERROR_CHECK_CHECK")));
		}

		$response['UUID'] = $checkUUID;

		if ($response['STATUS'] === 'WAIT')
		{
			$result = $result->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_REST_PRINT_IN_PROGRESS')));

			return $result;
		}

		return static::applyCheckResult($response);
	}

	/**
	 * @param Check $check
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function printImmediately(Check $check): Result
	{
		$url = $this->getPrintUrl();
		$printResult = Sale\Helpers\Rest\Http::sendRequest(
			$url,
			$this->buildCheckQuery($check),
			$this->getRequestOptions()
		);

		if (!$printResult->isSuccess())
		{
			return $printResult;
		}

		$printData = $printResult->getData();
		if (isset($printData['ERRORS']) && is_array($printData['ERRORS']))
		{
			foreach ($printData['ERRORS'] as $errorMessage)
			{
				$printResult->addError(new Errors\Error($errorMessage));
			}
		}

		return $printResult;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function validate(): Result
	{
		$result = parent::validate();

		// check whether the specified handler actually exists
		$handlerCode = $this->getHandlerCode();
		$handlerList = Manager::getRestHandlersList();
		if (!isset($handlerList[$handlerCode]))
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						"SALE_CASHBOX_REST_HANDLER_NOT_FOUND",
						["#HANDLER_CODE#" => $handlerCode]
					)
				)
			);
		}

		return $result;
	}
}
