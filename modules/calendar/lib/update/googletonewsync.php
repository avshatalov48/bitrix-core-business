<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EO_Event_Collection;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Update\Stepper;
use CCalendar;
use Exception;

final class GoogleToNewSync extends Stepper
{
	const PORTION = 100;
	const STATUS_SUCCESS = 'success';
	const OPTION_CONVERTED = 'googleToNewSyncConverted';
	const OPTION_STATUS = 'googleToNewSyncStatus';

	protected static $moduleId = 'calendar';

	public static function className(): string
	{
		return __CLASS__;
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function execute(array &$option): bool
	{
		return self::FINISH_EXECUTION;
	}
}