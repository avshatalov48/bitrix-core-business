<?php


namespace Bitrix\Sale\Rest\Synchronization;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\SynchronizerLogTable;

class LoggerDiag extends \Bitrix\Sale\Exchange\Internals\LoggerDiagBase
{
	static protected function getNameOptionEndTime()
	{
		return 'rest_debug_end_time';
	}

	static protected function getNameOptionIntervalDayOption()
	{
		return "synchronizer_debug_interval_day";
	}

	public function save()
	{
		$params['MESSAGE_ID'] = $this->getField('MESSAGE_ID');
		$params['MESSAGE'] = $this->getField('MESSAGE');
		$params['DATE_INSERT'] = new DateTime();

		return static::log($params);
	}

	static public function log(array $params)
	{
		return static::isOn() ? SynchronizerLogTable::add($params):null;
	}

	static public function addMessage($messageId, $message='')
	{
		$mess = static::getMessage();

		$logger = new static();
		$logger->setField('MESSAGE_ID', isset($mess['LOGGER_'.$messageId])?$mess['LOGGER_'.$messageId]:$messageId);
		$logger->setField('MESSAGE', $message);
		$logger->save();
	}

	protected static function getMessage()
	{
		return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/lib/rest/synchronization/loggerdiag.php');
	}
}