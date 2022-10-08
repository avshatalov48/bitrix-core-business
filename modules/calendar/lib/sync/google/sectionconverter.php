<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\Localization\Loc;

class SectionConverter
{
	/**
	 * @var Section
	 */
	private Section $section;

	/**
	 * @param Section $section
	 */
	public function __construct(Section $section)
	{
		$this->section = $section;
	}

	public function convertForEdit(): array
	{
		$section = [];

		$section['summary'] = $this->section->getName();

		//todo move to level up
		if ($this->section->getExternalType() === \CCalendarSect::EXTERNAL_TYPE_LOCAL)
		{
			IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');
			$section['summary'] = Loc::getMessage('EC_CALENDAR_BITRIX24_NAME') . " " . $section['summary'];
		}

		return $section;
	}
}
