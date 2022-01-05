<?php

namespace Sale\Handlers\Delivery\Spsr;

use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Comparator\Mapper;

Loc::loadMessages(__FILE__);

/**
 * Class Location
 *
 * @package Sale\Handlers\Delivery\Spsr
 */
final class Location extends Mapper
{
	const EXTERNAL_SERVICE_CODE = 'SPSR';
	const CSV_FILE_PATH = '/bitrix/modules/sale/handlers/delivery/spsr/location.csv';

	public static function install()
	{
		return new Result();
	}

	public function mapStepless()
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}

	public function map($stage, $step = '', $progress = 0, $timeout = 0)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}
}
