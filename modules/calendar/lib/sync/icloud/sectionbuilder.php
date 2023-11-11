<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\Localization\Loc;

class SectionBuilder
{
	use SingletonTrait;

	/**
	 * @param Section $section
	 *
	 * @return string
	 */
	public function getCreateSectionContent(Section $section): string
	{
		$xmlns = " xmlns:A0=\"urn:ietf:params:xml:ns:caldav\"";
		$xmlns .= " xmlns:A1=\"http://apple.com/ns/ical/\"";

		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		$body .= "<A:mkcol xmlns:A=\"DAV:\"". $xmlns .">\r\n";
		$body .= "\t<A:set>\r\n";
		$body .= "\t\t<A:prop>\r\n";
		$body .= "\t\t\t<A:resourcetype>\r\n";
		$body .= "\t\t\t\t<A:collection/>\r\n";
		$body .= "\t\t\t\t<A0:calendar/>\r\n";
		$body .= "\t\t\t</A:resourcetype>\r\n";
		$body = $this->getBodyProperties($section, $body);
		$body .= "</A:mkcol>";

		return $body;
	}

	/**
	 * @param Section $section
	 *
	 * @return string
	 */
	public function getUpdateSectionContent(Section $section): string
	{
		$xmlns = " xmlns:A0=\"urn:ietf:params:xml:ns:caldav\"";
		$xmlns .= " xmlns:A1=\"http://apple.com/ns/ical/\"";

		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		$body .= "<A:propertyupdate xmlns:A=\"DAV:\"". $xmlns .">\r\n";
		$body .= "\t<A:set>\r\n";
		$body .= "\t\t<A:prop>\r\n";
		$body = $this->getBodyProperties($section, $body);
		$body .= "</A:propertyupdate>";

		return $body;
	}

	/**
	 * @param Section $section
	 * @param string $body
	 *
	 * @return string
	 */
	private function getBodyProperties(Section $section, string $body): string
	{
		if ($section->getName())
		{
			IncludeModuleLangFile(
				$_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php'
			);
			$body .= "\t\t\t<A:displayname>"
				. ($section->getExternalType() === 'local' ? (Loc::getMessage('EC_CALENDAR_BITRIX24_NAME') . ' ') : '')
				. $section->getName() . "</A:displayname>\r\n";
		}
		if ($section->getColor())
		{
			$body .= "\t\t\t<A1:calendar-color>" . $section->getColor() . "</A1:calendar-color>\r\n";
		}
		$body .= "\t\t</A:prop>\r\n";
		$body .= "\t</A:set>\r\n";

		return $body;
	}
}