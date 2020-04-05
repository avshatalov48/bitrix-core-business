<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Result;

Loc::loadLanguageFile(__FILE__);

final class ReportManager
{
	/** This is time re-sending a check print in minutes */
	const MIN_TIME_RESENDING_REPORT = 300;
	const MAX_TIME_RESENDING_REPORT = 43200;

	/**
	 * @param $cashboxId
	 * @return int
	 */
	public static function addZReport($cashboxId)
	{
		$addResult = Internals\CashboxZReportTable::add(
			array(
				'CASHBOX_ID' => $cashboxId,
				'DATE_CREATE' => new DateTime(),
				'STATUS' => 'N',
				'CURRENCY' => 'RUB'
			)
		);

		return $addResult->getId();
	}

	/**
	 * @param $cashboxId
	 * @return array|false
	 */
	public static function getLastZReport($cashboxId)
	{
		$dbRes = Internals\CashboxZReportTable::getList(
			array(
				'select' => array('*'),
				'filter' => array('CASHBOX_ID' => $cashboxId),
				'order' => array('ID' => 'DESC'),
				'limit' => 1,
			)
		);

		return $dbRes->fetch();
	}

	/**
	 * @param $cashboxId
	 * @return int
	 */
	public static function getPrintableZReport($cashboxId)
	{
		$lastZReport = static::getLastZReport($cashboxId);

		$now = new DateTime();
		$nowTs = $now->getTimestamp();

		if ($lastZReport && ($lastZReport['STATUS'] === 'N' || $lastZReport['STATUS'] === 'P'))
		{
			if ($lastZReport['STATUS'] === 'N')
			{
				Internals\CashboxZReportTable::update($lastZReport['ID'], array('STATUS' => 'P', 'DATE_PRINT_START' => new DateTime()));
				return $lastZReport['ID'];
			}
			else
			{
				/** @var Date $datePrintStart */
				$datePrintStart = $lastZReport['DATE_PRINT_START'];
				$datePrintStartTs = $datePrintStart->getTimestamp();

				$p = $nowTs - $datePrintStartTs;
				if ($p > static::MIN_TIME_RESENDING_REPORT && $p < static::MAX_TIME_RESENDING_REPORT)
				{
					return $lastZReport['ID'];
				}
				elseif ($p >= static::MAX_TIME_RESENDING_REPORT)
				{
					Internals\CashboxZReportTable::update($lastZReport['ID'], array('STATUS' => 'E', 'DATE_PRINT_END' => new DateTime()));
				}
			}
		}
		else
		{
			$cashbox = Manager::getCashboxFromCache($cashboxId);
			$prevPrintDate = new DateTime();
			$prevPrintDate->setTime($cashbox['SETTINGS']['Z_REPORT']['TIME']['H'], $cashbox['SETTINGS']['Z_REPORT']['TIME']['M']);
			if ($prevPrintDate->getTimestamp() > $nowTs)
				$prevPrintDate->add("-1d");

			/** @var Date $datePrintStart */
			if ($lastZReport)
				$datePrintStart = $lastZReport['DATE_PRINT_START'];
			else
				$datePrintStart = new DateTime('2017-01-01 00:00:00', 'Y-m-d H:i:s');

			$datePrintStartTs = $datePrintStart->getTimestamp();

			if ($prevPrintDate->getTimestamp() - $datePrintStartTs > static::MIN_TIME_RESENDING_REPORT)
			{
				$dbChecksCount = Internals\CashboxCheckTable::getList(
					array(
						'select' => array('CNT'),
						'filter' => array(
							'CASHBOX_ID' => $cashboxId,
							'>=DATE_PRINT_START' => $datePrintStart,
							'<DATE_PRINT_START' => $prevPrintDate,
						),
						'runtime' => array(
							new ExpressionField('CNT', 'COUNT(*)')
						)
					)
				);
				$checksCount = $dbChecksCount->fetch();
				if ($checksCount && $checksCount['CNT'] > 0)
				{
					$reportId = static::addZReport($cashboxId);
					Internals\CashboxZReportTable::update($reportId, array('STATUS' => 'P', 'DATE_PRINT_START' => new DateTime()));
					return $reportId;
				}
			}
		}

		return 0;
	}

	/**
	 * @param $reportId
	 * @param $data
	 * @return Result
	 */
	public static function saveZReportPrintResult($reportId, $data)
	{
		$result = new Result();

		if ($reportId <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_REPORT_ID')));
			return $result;
		}

		$report = Internals\CashboxZReportTable::getRowById($reportId);
		if (!$report)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_REPORT_NOT_FOUND', array('#REPORT_ID#' => $reportId))));
			return $result;
		}

		if ($report['STATUS'] === 'Y')
			return $result;

		if (isset($data['ERROR']))
		{
			$errorMessage = Loc::getMessage('SALE_CASHBOX_ERROR_REPORT_PRINT', array('#REPORT_ID#' => $reportId));
			if ($data['ERROR']['MESSAGE'])
				$errorMessage .= ': '.$data['ERROR']['MESSAGE'];

			if ($data['ERROR']['TYPE'] === Errors\Warning::TYPE)
			{
				if ($report['CNT_FAIL_PRINT'] >= 3)
				{
					$data['ERROR']['TYPE'] = Errors\Error::TYPE;
				}
				else
				{
					$result->addError(new Errors\Warning($errorMessage));
					Internals\CashboxZReportTable::update($reportId, array('CNT_FAIL_PRINT' => $report['CNT_FAIL_PRINT']+1));
					return $result;
				}
			}

			if ($data['ERROR']['TYPE'] === Errors\Error::TYPE)
			{
				$updatedFields = array('STATUS' => 'E', 'DATE_PRINT_END' => new DateTime());
				if ((int)$report['CNT_FAIL_PRINT'] === 0)
					$updatedFields['CNT_FAIL_PRINT'] = 1;

				Internals\CashboxZReportTable::update($reportId, $updatedFields);
				$error = new Errors\Error($errorMessage);
			}
			else
			{
				$error = new Errors\Warning($errorMessage);
			}

			Manager::writeToLog($report['CASHBOX_ID'], $error);
			$result->addError($error);
		}
		else
		{
			$updateResult = Internals\CashboxZReportTable::update(
				$reportId,
				array(
					'STATUS' => 'Y',
					'DATE_PRINT_END' => new DateTime(),
					'CASH_SUM' => $data['CASH_SUM'],
					'CASHLESS_SUM' => $data['CASHLESS_SUM'],
					'CUMULATIVE_SUM' => $data['CUMULATIVE_SUM'],
					'RETURNED_SUM' => $data['RETURNED_SUM'],
					'LINK_PARAMS' => $data['LINK_PARAMS']
				)
			);

			if (!$updateResult->isSuccess())
				$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}
}