<?
namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Sync\GoogleApiSync;

/**
 * Class MigrateFromCaldav
 * @package Bitrix\Calendar\Update
 */
final class MigrateFromCaldav
{
	/**
	 * Agent method for migration purposes
	 * @return null|string
	 */
	public static function doMigration()
	{
		global $DB;
		$strSql = "SHOW TABLES LIKE 'b_calendar_tmp_migration'";
		$sqlResult = $DB->Query($strSql);
		$canDoMigration = false;
		if ($tableExists = $sqlResult->Fetch())
		{
			$canDoMigration = true;
		}

		//Stop migration process if impossible or empty table or table not exists.
		if (!\Bitrix\Main\Loader::includeModule('dav') || !$canDoMigration)
		{
			\CCalendar::ClearCache();
			\CAgent::RemoveAgent("Bitrix\\Calendar\\Update\\MigrateFromCaldav::doMigration();", "calendar");
			return null;
		}
		else
		{
			//Do migration process
			$query = $DB->Query("SELECT * FROM b_calendar_tmp_migration WHERE ATTEMPTS < 10 ORDER BY ATTEMPTS ASC LIMIT 3");
			$hasRows = false;
			while ($row = $query->Fetch())
			{
				$hasRows = true;
				$connectionId = $row['CONNECTION_ID'];
				$userId = intval($row['USER_ID']);
				$sectionsList = checkSerializedData($row['SECTION_DATA']) ? unserialize($row['SECTION_DATA']) : false;

				if ($sectionsList)
				{
					$googleApiConnection = new GoogleApiSync($userId);
					$googleCalendars = $googleApiConnection->getCalendarItems();
					$errorCode = $googleApiConnection->getTransportConnectionError();


					if (!$errorCode)
					{
						foreach ($sectionsList as $section)
						{
							$localEventsList = \CCalendarEvent::getList(array(
								'userId' => $userId,
								'arFilter' => array('SECTION' => $section['ID'])
							));

							$davXmlId = preg_replace('/(\/caldav\/v2\/)|(\/events\/)/', '', $section['CAL_DAV_CAL'], -1, $replaced);
							if ($replaced == 2)
							{
								$isVirtual = preg_match('/(@virtual)/', $davXmlId);

								foreach ($googleCalendars as $googleCalendar)
								{
									if ($isVirtual && ($googleCalendar['id'] == $section['DESCRIPTION'] || $googleCalendar['summary'] == $section['NAME']))
									{
										$DB->Query("UPDATE b_calendar_section SET GAPI_CALENDAR_ID = '" . $DB->ForSql($googleCalendar['id']) . "', TEXT_COLOR = '" . $DB->ForSql($googleCalendar['textColor']) . "', COLOR = '" . $DB->ForSql($googleCalendar['backgroundColor']) . "' WHERE ID = '" . intval($section['ID']) . "'");
										continue;
									}
									if ($davXmlId != $googleCalendar['id'])
									{
										continue;
									}
									$section['GAPI_CALENDAR_ID'] = $davXmlId;
									$sectionSyncToken = NULL;
									$externalEvents = $googleApiConnection->getEvents($section);
									$sectionSyncToken = $googleApiConnection->getEventsSyncToken();

									if ($localEventsList)
									{
										foreach ($localEventsList as $localEvent)
										{
											if (preg_match('/(@google.com)/', $localEvent['DAV_XML_ID']) === 1)
												continue;
											foreach ($externalEvents as $externalEvent)
											{
												if (!empty($localEvent['DAV_XML_ID']) && !empty($externalEvent['iCalUID']) && ($localEvent['DAV_XML_ID'] == $externalEvent['iCalUID']))
												{
													$localEvent['DAV_XML_ID'] = $externalEvent['DAV_XML_ID'];

													$newEventData = array_merge(
														$localEvent,
														array(
															'SECTIONS' => array($section['ID']),
															'OWNER_ID' => $userId,
															'userId' => $userId,
														)
													);
													\CCalendarEvent::Edit(array('arFields' => $newEventData));
													break;
												}
											}
										}
									}
									$DB->Query("UPDATE b_calendar_section SET GAPI_CALENDAR_ID = '" . $DB->ForSql($davXmlId) . "', SYNC_TOKEN = '" . $DB->ForSql($sectionSyncToken) . "', TEXT_COLOR = '" . $DB->ForSql($googleCalendar['textColor']) . "', COLOR = '" . $DB->ForSql($googleCalendar['backgroundColor']) . "' WHERE ID = '" . intval($section['ID']) . "'");
									break;
								}
							}
						}
						//In a case when google api can return data and we have access to it
						$DB->Query("DELETE FROM b_calendar_tmp_migration WHERE ID = '" . intval($row['ID']) . "'");

						\CDavConnection::Update($connectionId, array('ACCOUNT_TYPE' => 'google_api_oauth', 'SERVER' => 'https://www.googleapis.com/calendar/v3'), false);
					}
					else
					{
						if ($row['ATTEMPTS'] >= 1000)
						{
							$DB->Query("DELETE FROM b_calendar_tmp_migration WHERE ID = " . intval($row['ID']));
							\CDavConnection::SetLastResult($connectionId, "GAPI_MIGRATE_ERROR");
						}
						$DB->Query("UPDATE b_calendar_tmp_migration SET ATTEMPTS = ATTEMPTS + 1 WHERE ID = " . intval($row['ID']));
					}
				}
				else
				{
					//In a case when no tables for migration received.
					$DB->Query("DELETE FROM b_calendar_tmp_migration WHERE ID = '" . intval($row['ID']) . "'");
					\CDavConnection::Update($connectionId, array('ACCOUNT_TYPE' => 'google_api_oauth', 'SERVER' => 'https://www.googleapis.com/calendar/v3'), false);
				}
			}

			if (!$hasRows)
			{
				$DB->Query("DROP TABLE b_calendar_tmp_migration");
			}

			return "Bitrix\\Calendar\\Update\\MigrateFromCaldav::doMigration();";
		}
	}
}