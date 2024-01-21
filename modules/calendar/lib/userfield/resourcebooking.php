<?php
namespace Bitrix\Calendar\UserField;

use Bitrix\Bitrix24;
use Bitrix\Calendar\Internals;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);


class ResourceBooking extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'resourcebooking';
	public const EVENT_LABEL = '#resourcebooking#';
	const RESOURCE_CALENDAR_TYPE = 'resource';
	const BITRIX24_RESTRICTION = 100;
	const BITRIX24_RESTRICTION_CODE = 'uf_resourcebooking';

	const CRM_LEAD_ENTITY_ID = 'CRM_LEAD';
	const CRM_SUSPENDED_LEAD_ENTITY_ID = 'CRM_LEAD_SPD';
	const CRM_DEAL_ENTITY_ID = 'CRM_DEAL';
	const CRM_SUSPENDED_DEAL_ENTITY_ID = 'CRM_DEAL_SPD';

	protected static $restrictionCount = null;

	public static function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage("USER_TYPE_RESOURCEBOOKING_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'getPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'getPublicView')
		);
	}

	public static function prepareSettings($userField = [])
	{
		$userField = [
			'SETTINGS' => [
				'SELECTED_RESOURCES' => $userField['SETTINGS']['SELECTED_RESOURCES'] ?? null,
				'SELECTED_USERS' => $userField['SETTINGS']['SELECTED_USERS'] ?? null,
				'USE_USERS' => $userField['SETTINGS']['USE_USERS'] ?? null,
				'USE_RESOURCES' => $userField['SETTINGS']['USE_RESOURCES'] ?? null,
				'FULL_DAY' => $userField['SETTINGS']['FULL_DAY'] ?? null,
				'ALLOW_OVERBOOKING' => $userField['SETTINGS']['ALLOW_OVERBOOKING'] ?? null,
				'USE_SERVICES' => $userField['SETTINGS']['USE_SERVICES'] ?? null,
				'SERVICE_LIST' => $userField['SETTINGS']['SERVICE_LIST'] ?? null,
				'TIMEZONE' => $userField['SETTINGS']['TIMEZONE'] ?? null,
				'USE_USER_TIMEZONE' => $userField['SETTINGS']['USE_USER_TIMEZONE'] ?? null,
			],
		];

		$selectedResources = [];

		if (is_array($userField["SETTINGS"]["SELECTED_RESOURCES"]))
		{
			$selectedResources = self::handleResourceList($userField["SETTINGS"]["SELECTED_RESOURCES"]);
		}
		$selectedUsers = [];
		if (is_array($userField["SETTINGS"]["SELECTED_USERS"]))
		{
			foreach($userField["SETTINGS"]["SELECTED_USERS"] as $user)
			{
				if (intval($user) > 0)
				{
					$selectedUsers[] = intval($user);
				}
			}
		}

		return array(
			"USE_USERS" => $userField["SETTINGS"]["USE_USERS"] === 'N' ? 'N' : 'Y',
			"USE_RESOURCES" => $userField["SETTINGS"]["USE_RESOURCES"] === 'N' ? 'N' : 'Y',
			"RESOURCES" => self::getDefaultResourcesList(),
			"SELECTED_RESOURCES" => $selectedResources,
			"SELECTED_USERS" => $selectedUsers,
			"FULL_DAY" => $userField["SETTINGS"]["FULL_DAY"] === 'Y' ? 'Y' : 'N',
			"ALLOW_OVERBOOKING" => $userField["SETTINGS"]["ALLOW_OVERBOOKING"] === 'N' ? 'N' : 'Y',
			"USE_SERVICES" => $userField["SETTINGS"]["USE_SERVICES"] === 'N' ? 'N' : 'Y',
			"SERVICE_LIST" => is_array($userField["SETTINGS"]["SERVICE_LIST"]) ? $userField["SETTINGS"]["SERVICE_LIST"] : self::getDefaultServiceList(),
			"RESOURCE_LIMIT" => self::getBitrx24Limitation(),
			"TIMEZONE" => $userField["SETTINGS"]["TIMEZONE"],
			"USE_USER_TIMEZONE" => $userField["SETTINGS"]["USE_USER_TIMEZONE"] === 'Y' ? 'Y' : 'N'
		);
	}

	public static function getDBColumnType()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\TextField('x'));
	}

	public static function checkFields($userField, $value)
	{
		if($userField["MANDATORY"] === "Y" && ($value === 'empty' || !$value))
		{
			return array(array(
				"id" => $userField['FIELD_NAME'],
				"text"=>str_replace("#FIELD_NAME#", $userField['EDIT_FORM_LABEL'],
					Loc::getMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))
			));
		}

		return [];
	}

	public static function onBeforeSaveAll($userField, $values, $userId = false)
	{
		$valuesToSave = [];
		$currentUserId = \CCalendar::getCurUserId();
		$dateFrom = false;
		$dateTo = false;
		$serviceName = '';
		$entityTitle = '';
		$fields = [];
		$entity = null;

		$resourseList = Internals\ResourceTable::query()
			->setSelect(['*'])
			->where('PARENT_TYPE', $userField['ENTITY_ID'])
			->where('PARENT_ID', $userField['VALUE_ID'])
			->where('UF_ID', $userField['ID'])
			->exec()
		;

		$currentEntriesIndex = [];
		while ($resourse = $resourseList->fetch())
		{
			$currentEntriesIndex[$resourse['CAL_TYPE'].$resourse['RESOURCE_ID']] = $resourse;
		}

		if (self::isCrmEntity($userField['ENTITY_ID']) && Loader::includeModule('crm'))
		{
			if ($userField['ENTITY_ID'] === self::CRM_DEAL_ENTITY_ID)
			{
				$dealResult = \CCrmDeal::GetListEx(
					[],
					['=ID' => $userField['VALUE_ID'], 'CHECK_PERMISSIONS' => 'N'],
					false,
					false,
					['TITLE'],
				);

				if (!empty($dealResult))
				{
					$entity = $dealResult->Fetch();
				}

				if (!empty($entity) && $entity['TITLE'])
				{
					$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE").': '.$entity['TITLE'];
				}
			}
			elseif ($userField['ENTITY_ID'] === self::CRM_LEAD_ENTITY_ID)
			{
				$leadResult = \CCrmLead::GetListEx(
					[],
					['=ID' => $userField['VALUE_ID'], 'CHECK_PERMISSIONS' => 'N'],
					false,
					false,
					['TITLE'],
				);

				if (!empty($leadResult))
				{
					$entity = $leadResult->Fetch();
				}

				if (!empty($entity) && $entity['TITLE'])
				{
					$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE").': '.$entity['TITLE'];
				}
			}

			if (
				$userField['ENTITY_ID'] === self::CRM_SUSPENDED_DEAL_ENTITY_ID
				|| $userField['ENTITY_ID'] === self::CRM_SUSPENDED_LEAD_ENTITY_ID
			)
			{
				$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE")." (Deleted)";
			}
		}

		if (!$entityTitle)
		{
			$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE");
		}

		$valuesToMerge = [];
		foreach ($values as $value)
		{
			if ((string)$value === (string)((int)$value))
			{
				$currentValue = static::fetchFieldValue($value);
				$skipTime = is_array($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] === 'Y';
				$fromTs = \CCalendar::timestamp($currentValue['DATE_FROM'], true, !$skipTime);
				$toTs = \CCalendar::timestamp($currentValue['DATE_TO'], true, !$skipTime);

				foreach ($currentValue['ENTRIES'] as $entry)
				{
					$entryExist = false;

					foreach ($values as $iValue)
					{
						$str = $entry['TYPE'].'|'.$entry['RESOURCE_ID'];
						if (str_starts_with($iValue, $str))
						{
							$entryExist = true;
							break;
						}
					}

					if (!$entryExist)
					{
						$valuesToMerge[] = self::prepareValue(
							$entry['TYPE'],
							$entry['RESOURCE_ID'],
							$currentValue['DATE_FROM'],
							$toTs - $fromTs,
							$currentValue['SERVICE_NAME']
						);
					}
				}
			}
		}

		$values = array_merge($values, $valuesToMerge);

		foreach ($values as $value)
		{
			$value = self::parseValue($value);

			if (!is_array($value))
			{
				continue;
			}

			if (!$dateFrom || !$dateTo)
			{
				$dateFromTimestamp = \CCalendar::timestamp($value['from']);
				$skipTime = isset($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] === 'Y';
				$dateFrom = \CCalendar::date($dateFromTimestamp, !$skipTime);
				$duration = (int)$value['duration'];
				$dateTo = \CCalendar::date($dateFromTimestamp + ($skipTime ? $duration - \CCalendar::DAY_LENGTH : $duration), !$skipTime);
				$serviceName = trim($value['serviceName']);

				$fields = [
					"DATE_FROM" => $dateFrom,
					"DATE_TO" => $dateTo,
					"SKIP_TIME" => $skipTime,
					"NAME" => $entityTitle
				];

				if (
					$userField['ENTITY_ID'] === self::CRM_SUSPENDED_DEAL_ENTITY_ID
					|| $userField['ENTITY_ID'] === self::CRM_SUSPENDED_LEAD_ENTITY_ID
				)
				{
					$fields["DELETED"] = 'Y';
				}

				if ($serviceName !== '')
				{
					$fields["DESCRIPTION"] = Loc::getMessage("USER_TYPE_RESOURCE_SERVICE_LABEL").': '.$serviceName;
				}

				if (!$skipTime)
				{
					if ($userField['SETTINGS']['USE_USER_TIMEZONE'] === 'Y')
					{
						$timezoneName = \CCalendar::getUserTimezoneName($currentUserId, true);
					}
					else if($userField['SETTINGS']['TIMEZONE'])
					{
						$timezoneName = $userField['SETTINGS']['TIMEZONE'];
					}
					else
					{
						$timezoneName = \CCalendar::GetGoodTimezoneForOffset((int)date("Z"));
					}

					if($timezoneName)
					{
						$fields['TZ_FROM'] = $timezoneName;
						$fields['TZ_TO'] = $timezoneName;
					}
				}
			}

			$entryId = false;
			if (isset($currentEntriesIndex[$value['type'] . $value['id']]))
			{
				$fields['ID'] = $currentEntriesIndex[$value['type'].$value['id']]['EVENT_ID'];
				$entryId = $currentEntriesIndex[$value['type'].$value['id']]['ID'];
			}
			else
			{
				unset($fields['ID']);
			}

			$resourceBookingId = self::saveResource(
				$entryId,
				$value['type'],
				$value['id'],
				$fields,
				[
					'userField' => $userField,
					'serviceName' => $serviceName
				]
			);

			if ($resourceBookingId)
			{
				$valuesToSave[] = $resourceBookingId;
			}
		}

		foreach ($currentEntriesIndex as $resourceEntry)
		{
			if (!in_array($resourceEntry['ID'], $valuesToSave))
			{
				self::releaseResource($resourceEntry);
			}
		}

		return  $valuesToSave;
	}

	public static function onDelete($userField, $values, $userId = false)
	{
		$resourseList = Internals\ResourceTable::getList(
			array(
				"filter" => array(
					"PARENT_TYPE" => $userField['ENTITY_ID'],
					"PARENT_ID" => $userField['ENTITY_VALUE_ID'],
					"UF_ID" => $userField['ID']
				)
			)
		);

		while ($resourse = $resourseList->fetch())
		{
			self::releaseResource($resourse);
		}
	}

	/**
	 * Saves resource of given type.
	 *
	 * @param integer $id id of current booking.
	 * @param string $resourceType resource type.
	 * @param integer $resourceId resource id.
	 * @param array $eventFields calendar event fields.
	 * @param array $params additional params.
	 *
	 * @return integer, id of resource booking or null
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function saveResource($id, $resourceType, $resourceId, $eventFields = [], $params = [])
	{
		$valueToSave = null;
		$currentUserId = \CCalendar::getCurUserId();

		$eventFields["EVENT_TYPE"] = '#resourcebooking#';
		if ($resourceType === 'user')
		{
			$eventFields["CAL_TYPE"] = $resourceType;
			$eventFields["OWNER_ID"] = $resourceId;

			if ($params['userField']['ENTITY_ID'] === 'CRM_DEAL' || $params['userField']['ENTITY_ID'] === 'CRM_LEAD')
			{
				$sectionId = \CCalendar::getCrmSection($resourceId, true);
			}
			else
			{
				$sectionId = \CCalendar::getMeetingSection($resourceId, true);
			}
			if ($sectionId)
			{
				$eventFields['SECTIONS'] = [$sectionId];
			}

			// Userfields for event
			$userFields = [];
			if ($params['userField']['ENTITY_ID'] === 'CRM_DEAL')
			{
				$userFields['UF_CRM_CAL_EVENT'] = ["D_".$params['userField']['VALUE_ID']];
			}
			elseif ($params['userField']['ENTITY_ID'] === 'CRM_LEAD')
			{
				$userFields['UF_CRM_CAL_EVENT'] = ["L_".$params['userField']['VALUE_ID']];
			}

			$entryId = \CCalendar::saveEvent([
				'arFields' => $eventFields,
				'UF' => $userFields,
				'silentErrorMode' => false,
				'autoDetectSection' => true,
				'autoCreateSection' => true,
				'checkPermission' => false
			]);
		}
		else
		{
			$eventFields["CAL_TYPE"] = $resourceType;
			$eventFields["SECTIONS"] = $resourceId;
			$entryId = \CCalendarEvent::edit([
				'arFields' => $eventFields
			]);
		}

		if ($entryId)
		{
			if ($eventFields['TZ_FROM'] ?? null)
			{
				$from = new Type\DateTime($eventFields["DATE_FROM"]);
				$to = new Type\DateTime($eventFields["DATE_TO"]);
				$fromUtc = new Type\DateTime($eventFields["DATE_FROM"], null, new \DateTimeZone('UTC'));
				$toUtc = new Type\DateTime($eventFields["DATE_TO"], null, new \DateTimeZone('UTC'));
			}
			else
			{
				$from = new Type\DateTime($eventFields["DATE_FROM"]);
				$to = new Type\DateTime($eventFields["DATE_TO"]);
				$fromUtc = $from;
				$toUtc = $to;
			}

			\CTimeZone::Disable();

			$resourceTableFields = [
				'EVENT_ID' => $entryId,
				'CAL_TYPE' => $eventFields["CAL_TYPE"],
				'RESOURCE_ID' => $resourceId,
				'PARENT_TYPE' => $params['bindingEntityType'] ?? $params['userField']['ENTITY_ID'],
				'PARENT_ID' => $params['bindingEntityId'] ?? $params['userField']['VALUE_ID'],
				'UF_ID' => $params['bindingUserfieldId'] ?? $params['userField']['ID'],
				'DATE_FROM' => $from,
				'DATE_TO' => $to,
				'SKIP_TIME' => $eventFields['SKIP_TIME'] ?? null,
				'DATE_FROM_UTC' => $fromUtc,
				'DATE_TO_UTC' => $toUtc,
				'TZ_FROM' => $eventFields['TZ_FROM'] ?? null,
				'TZ_TO' => $eventFields['TZ_TO'] ?? null,
				'TZ_OFFSET_FROM' => \CCalendar::getTimezoneOffset(
					$eventFields['TZ_FROM'] ?? null,
					\CCalendar::timestamp($eventFields["DATE_FROM"])
				),
				'TZ_OFFSET_TO' => \CCalendar::getTimezoneOffset(
					$eventFields['TZ_TO'] ?? null,
					\CCalendar::timestamp($eventFields["DATE_TO"])
				),
				'CREATED_BY' => $currentUserId,
				'SERVICE_NAME' => $params['serviceName'] ?? null
			];

			if ($id)
			{
				$result = Internals\ResourceTable::update($id, $resourceTableFields);
			}
			else
			{
				$result = Internals\ResourceTable::add($resourceTableFields);
			}

			if ($result->isSuccess())
			{
				$valueToSave = $result->getId();
			}
			else
			{
				\CCalendar::deleteEvent((int)$entryId, false);
			}

			foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "onAfterResourceBookingAdd") as $event)
			{
				ExecuteModuleEventEx($event, [[
					'userFieldValueId' => $valueToSave,
					'bookingEventId' => $entryId,
					'resourceType' => $resourceType,
					'resourceId' => $resourceId,
					'serviceName' => $params['serviceName'] ?? null,
					'dateFrom' => $from,
					'dateTo' => $to,
					'skipTime' => $eventFields['SKIP_TIME'],
					'timezoneFrom' => $eventFields['TZ_FROM'] ?? null,
					'timezoneTo' => $eventFields['TZ_TO'] ?? null,
				]]);
			}

			\CTimeZone::Enable();
		}

		return $valueToSave;
	}

	private static function isCrmEntity($entityId)
	{
		return in_array($entityId, [self::CRM_LEAD_ENTITY_ID, self::CRM_SUSPENDED_LEAD_ENTITY_ID,self::CRM_DEAL_ENTITY_ID, self::CRM_SUSPENDED_DEAL_ENTITY_ID]);
	}

	private static function isResourceAvailable()
	{

	}

	public static function releaseResource($entry)
	{
		\CCalendar::deleteEvent((int)$entry['EVENT_ID'], true, array('checkPermissions' => false));
		Internals\ResourceTable::delete($entry['ID']);
	}

	private static function handleResourceList($resources)
	{
		$result = [];
		foreach($resources as $resource)
		{
			if (is_array($resource))
			{
				if (($resource['id'] ?? null) && (($resource['deleted'] ?? null) || ($resource['title'] ?? null) == ''))
				{
					$sectionList = Internals\SectionTable::getList(
						array(
							"filter" => array("ID" => $resource['id'], "CAL_TYPE" => $resource['type'] ?? null),
							"select" => array("ID", "CAL_TYPE", "NAME")
						)
					);
					if ($sectionList->fetch())
					{
						Internals\SectionTable::delete(array('ID' => $resource['id']));
					}
				}
				else if ($resource['id'] ?? null)
				{
					$sectionList = Internals\SectionTable::getList(
						array(
							"filter" => array("ID" => $resource['id'], "CAL_TYPE" => $resource['type'] ?? null),
							"select" => array("ID", "CAL_TYPE", "NAME")
						)
					);
					if ($section = $sectionList->fetch())
					{
						if ($section['NAME'] != ($resource['title'] ?? null))
						{
							\CCalendarSect::edit(array(
								'arFields' => array(
									'ID' => $resource['id'],
									'CAL_TYPE' => $resource['type'] ?? null,
									'NAME' => $resource['title'] ?? null,
									'ACCESS' => []
								)
							));
						}
					}
					$result[] = $resource;
				}
				elseif (!($resource['id'] ?? null) && ($resource['title'] ?? null) !== '')
				{
					$resource['id'] = \CCalendarSect::edit(array(
						'arFields' => array(
							'CAL_TYPE' => $resource['type'] ?? null,
							'NAME' => $resource['title'] ?? null,
							'ACCESS' => []
						)
					));
					$result[] = $resource;
				}
			}
		}
		return $result;
	}

	public static function prepareValue($type, $id, $from, $duration, $serviceName = '')
	{
		$type = $type === 'user' || $type === 'resource' ? $type : 'user';
		$id = (int)$id > 0 ? $id : 1;
		$duration = (int)$duration > 0 ? $duration : 60;
		return $type.'|'.$id.'|'.$from.'|'.$duration.'|'.$serviceName;
	}

	public static function parseValue($value)
	{
		$res = false;
		if(mb_strpos($value, '|') >= 0)
		{
			$list = explode('|', $value);
			$type = $list[0] ?? null;
			$id = $list[1] ?? null;
			$from = $list[2] ?? null;
			$duration = $list[3] ?? null;
			$serviceName = $list[4] ?? null;
			if ($type === 'user' || ($type === 'resource' && (int)$id > 0))
			{
				$res = array(
					'type' => $type,
					'id' => $id,
					'from' => $from,
					'duration' => $duration,
					'serviceName' => $serviceName ? $serviceName : ''
				);
			}
		}
		return $res;
	}

	function getSettingsHTML($userField = false, $htmlControl = [], $varsFromForm = false)
	{
		\Bitrix\Main\UI\Extension::load(['uf', 'calendar.resourcebookinguserfield', 'calendar_planner', 'socnetlogdest', 'helper', 'main', 'ui', 'ui.selector']);

		if($varsFromForm)
		{
			$settingsValue = $GLOBALS[$htmlControl["NAME"]];
		}
		elseif(is_array($userField))
		{
			$settingsValue = $userField["SETTINGS"];
		}
		else
		{
			$settingsValue = [];
		}

		$controlId = $userField['FIELD_NAME'].'_'.rand();
		$params = [
			'controlId' => $controlId,
			'settings' => $settingsValue,
			'userField' => $userField,
			'htmlControl' => $htmlControl,
			'outerWrapId' => $controlId.'-settings-outer-wrap',
			'formName' => 'post_form'
		];

		if ($settingsValue['USE_USERS'] === 'Y')
		{
			$params['socnetDestination'] = \CCalendar::getSocNetDestination(false, [], $settingsValue['SELECTED_USERS']);
		}

		$result = '<tr>
			<td></td>
			<td>
				<div id="'.HtmlFilter::encode($params['outerWrapId']).'"></div>
				<script>(function(){new BX.Calendar.AdminSettingsViewer('.
			\Bitrix\Main\Web\Json::encode($params).
			').showLayout();})();</script>
			</td>
		</tr>';

		return $result;
	}

	function getEditFormHTML($userField, $htmlControl)
	{
		return static::getPublicEdit($userField, $htmlControl);
	}

	public static function getPublicEdit($userField, $additionalParams = [])
	{
		\Bitrix\Main\UI\Extension::load(['uf', 'calendar.resourcebookinguserfield', 'calendar_planner', 'socnetlogdest']);

		$fieldName = static::getFieldName($userField, $additionalParams);
		$userField['VALUE'] = static::getFieldValue($userField, $additionalParams);

		$value = static::fetchFieldValue($userField["VALUE"]);

		if (
			$userField['SETTINGS']['USE_RESOURCES'] === 'Y'
			&& (!is_array($userField['SETTINGS']['SELECTED_RESOURCES']) || empty($userField['SETTINGS']['SELECTED_RESOURCES']))
		)
		{
			$userField['SETTINGS']['USE_RESOURCES'] = 'N';
		}

		$controlId = $userField['FIELD_NAME'].'_'.rand();
		$params = [
			'controlId' => $controlId,
			'inputName' => $fieldName,
			'value' => $value,
			'plannerId' => $controlId.'_planner',
			'userSelectorId' => 'resource_booking_user_selector',
			'useUsers' => $userField['SETTINGS']['USE_USERS'] === 'Y',
			'useResources' => $userField['SETTINGS']['USE_RESOURCES'] === 'Y',
			'fullDay' => $userField['SETTINGS']['FULL_DAY'] === 'Y',
			'allowOverbooking' => $userField['SETTINGS']['ALLOW_OVERBOOKING'] !== 'N',
			'useServices' => $userField['SETTINGS']['USE_SERVICES'] === 'Y',
			'serviceList' => $userField['SETTINGS']['SERVICE_LIST'],
			'resourceList' => $userField['SETTINGS']['SELECTED_RESOURCES'],
			'userList' => $userField['SETTINGS']['SELECTED_USERS'],
			'userfieldId' => $userField['ID'],
			'resourceLimit' => self::getBitrx24Limitation(),
			'workTime' => [\COption::getOptionString('calendar', 'work_time_start', 9), \COption::getOptionString('calendar', 'work_time_end', 19)]
		];

		if ($params['useUsers'])
		{
			$params['socnetDestination'] = \CCalendar::getSocNetDestination(false, [], $userField['SETTINGS']['SELECTED_USERS']);
		}

		ob_start();
		?>

		<div id="<?= HtmlFilter::encode($params['controlId'])?>" class="crm-entity-widget-resourcebook-container"></div>
		<script>
			(function(){
				'use strict';
				BX.Runtime.loadExtension('calendar.resourcebookinguserfield').then(function(exports)
				{
					if (exports && BX.type.isFunction(exports.ResourcebookingUserfield))
					{
						exports.ResourcebookingUserfield.initEditFieldController(<?= \Bitrix\Main\Web\Json::encode($params)?>);
					}
				});
			})();
		</script>
		<?

		$html = ob_get_clean();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicView($userField, $additionalParams = [])
	{
		$context = $additionalParams['CONTEXT'] ?? '';
		$value = static::fetchFieldValue($userField["VALUE"] ?? null);
		$skipTime = is_array($userField['SETTINGS'] ?? null) && ($userField['SETTINGS']['FULL_DAY'] ?? null) == 'Y';
		$fromTs = \CCalendar::timestamp($value['DATE_FROM'] ?? null, true, !$skipTime);
		$toTs = \CCalendar::timestamp($value['DATE_TO'] ?? null, true, !$skipTime);

		$users = [];
		$resources = [];
		$resourceNames = [];
		$userIdList = [];
		$resourceIdList = [];

		foreach($value['ENTRIES'] as $entry)
		{
			if ($entry['TYPE'] === 'user')
			{
				$userIdList[] = (int) $entry['RESOURCE_ID'];
			}
			else
			{
				$resourceIdList[] = (int) $entry['RESOURCE_ID'];
			}
		}

		$userIdList = array_unique($userIdList);
		$resourceIdList = array_unique($resourceIdList);

		if (!empty($userIdList))
		{
			$orm = UserTable::getList([
				'filter' => [
					'=ID' => $userIdList,
					'=ACTIVE' => 'Y'
				],
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'PERSONAL_PHOTO']
			]);

			while ($user = $orm->fetch())
			{
				$user['URL'] = \CCalendar::getUserUrl($user["ID"]);
				$users[] = $user;
			}
		}

		if (!empty($resourceIdList))
		{
			$sectionList = Internals\SectionTable::getList(
				array(
					"filter" => array(
						"=ACTIVE" => 'Y',
						"!=CAL_TYPE" => ['user', 'group', 'company_calendar', 'company', 'calendar_company'],
						"ID" => $resourceIdList
					),
					"select" => array("ID", "CAL_TYPE", "NAME")
				)
			);

			while ($section = $sectionList->fetch())
			{
				$resources[$section['ID']] = $section;
				$resourceNames[] = HtmlFilter::encode($section['NAME']);
			}
		}

		if ($context === 'CRM_GRID')
		{
			\Bitrix\Main\UI\Extension::load([
				'ui.design-tokens',
				'ui.fonts.opensans',
			]);

			\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/calendar/userfield/resourcebooking.css');
			$resListItems = [];
			if (!empty($users))
			{
				foreach($users as $user)
				{
					$resListItems[] = '<span>'.HtmlFilter::encode(\CCalendar::getUserName($user)).'</span>';
				}
			}
			if (!empty($resourceNames))
			{
				foreach($resourceNames as $resourceName)
				{
					$resListItems[] = '<span>'.$resourceName.'</span>';
				}
			}

			if (!empty($resListItems))
			{
				$html = '<span>'.\CCalendar::getFromToHtml($fromTs, $toTs, $skipTime, $toTs - $fromTs).'</span>: ';
				if(!empty($value['SERVICE_NAME']))
				{
					$html .= '<span>'.HtmlFilter::encode($value['SERVICE_NAME']).'</span>, ';
				}
				$html .= '<span>'.implode(', ', $resListItems).'</span>';
			}
			else
			{
				$html = '<span class="calendar-resbook-field-empty">'.Loc::getMessage("USER_TYPE_RESOURCE_EMPTY").'</span>';
			}
		}
		else
		{
			\Bitrix\Main\UI\Extension::load(['uf', 'calendar.resourcebookinguserfield']);
			if (empty($users) && empty($resourceNames))
			{
				$html = Loc::getMessage("USER_TYPE_RESOURCE_EMPTY");
			}
			else
			{
				ob_start();
				?>
				<div class="calendar-res-book-public-view-outer-wrap">
					<div class="calendar-res-book-public-view-inner-wrap">
						<div class="crm-entity-widget-content-block crm-entity-widget-content-block-field-select">
							<div class="crm-entity-widget-content-block-title">
								<span
									class="crm-entity-widget-content-block-title-text"><?= ($skipTime ? Loc::getMessage("USER_TYPE_RESOURCE_DATE_BLOCK_TITLE") : Loc::getMessage("USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE")) ?></span>
							</div>
							<div
								class="crm-entity-widget-content-block-inner"><?= \CCalendar::getFromToHtml($fromTs, $toTs, $skipTime, $toTs - $fromTs) ?></div>
						</div>

						<? if(!empty($value['SERVICE_NAME'])): ?>
							<div class="crm-entity-widget-content-block crm-entity-widget-content-block-field-select">
								<div class="crm-entity-widget-content-block-title">
									<span
										class="crm-entity-widget-content-block-title-text"><?= Loc::getMessage("USER_TYPE_RESOURCE_SERVICE_PLACEHOLDER") ?></span>
								</div>
								<div
									class="crm-entity-widget-content-block-inner"><?= HtmlFilter::encode($value['SERVICE_NAME']) ?></div>
							</div>
						<?endif; ?>

						<? if (!empty($users)): ?>
							<div class="crm-entity-widget-content-block crm-entity-widget-content-block-field-select">
								<div class="crm-entity-widget-content-block-title">
									<span
										class="crm-entity-widget-content-block-title-text"><?= Loc::getMessage("USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME") ?></span>
								</div>
								<? foreach($users as $user): ?>
									<div class="crm-widget-employee-container">
										<a class="crm-widget-employee-avatar-container" href="<?= $user['URL'] ?>" target="_blank" style="background-image: url('<?= \CCalendar::getUserAvatarSrc($user) ?>'); background-size: 30px;"></a>
										<span class="crm-widget-employee-info"><a class="crm-widget-employee-name" href="<?= $user['URL']?>" target="_blank"><?= HtmlFilter::encode(\CCalendar::getUserName($user))?></a><span class="crm-widget-employee-position"></span></span>
									</div>
								<? endforeach; ?>
							</div>
						<?endif; ?>

						<? if (!empty($resourceNames)): ?>
							<div class="crm-entity-widget-content-block crm-entity-widget-content-block-field-select">
								<div class="crm-entity-widget-content-block-title">
									<span
										class="crm-entity-widget-content-block-title-text"><?= Loc::getMessage("USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME") ?></span>
								</div>
								<div class="crm-entity-widget-content-block-inner"><?= implode(', ', $resourceNames) ?></div>
							</div>
						<?endif; ?>
					</div>
				</div>
				<?
				$html = ob_get_clean();
			}
		}

		if ($context === 'UI_EDITOR')
		{
			$html = '<span class="field-item">' . $html . '</span>';
		}

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		$resultText = '';
		$value = static::fetchFieldValue($userField["VALUE"]);

		$users = [];
		$resources = [];
		$resourceNames = [];
		$userIdList = [];
		$resourseIdList = [];

		foreach($value['ENTRIES'] as $entry)
		{
			if ($entry['TYPE'] === 'user')
			{
				$userIdList[] = (int) $entry['RESOURCE_ID'];
			}
			else
			{
				$resourseIdList[] = (int) $entry['RESOURCE_ID'];
			}
		}

		$userIdList = array_unique($userIdList);
		$resourseIdList = array_unique($resourseIdList);

		if (!empty($userIdList))
		{
			$orm = UserTable::getList([
				'filter' => [
					'=ID' => $userIdList,
					'=ACTIVE' => 'Y'
				],
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'PERSONAL_PHOTO']
			]);

			while ($user = $orm->fetch())
			{
				$user['URL'] = \CCalendar::getUserUrl($user["ID"]);
				$users[] = $user;
			}
		}

		if (!empty($resourseIdList))
		{
			$sectionList = Internals\SectionTable::getList(
				array(
					"filter" => array(
						"=ACTIVE" => 'Y',
						"!=CAL_TYPE" => ['user', 'group', 'company_calendar', 'company', 'calendar_company'],
						"ID" => $resourseIdList
					),
					"select" => array("ID", "CAL_TYPE", "NAME")
				)
			);

			while ($section = $sectionList->fetch())
			{
				$resources[$section['ID']] = $section;
				$resourceNames[] = $section['NAME'];
			}
		}

		$resListItems = [];
		if (!empty($users))
		{
			foreach($users as $user)
			{
				$resListItems[] = \CCalendar::getUserName($user);
			}
		}
		if (!empty($resourceNames))
		{
			foreach($resourceNames as $resourceName)
			{
				$resListItems[] = $resourceName;
			}
		}

		if (!empty($resListItems))
		{
			$skipTime = is_array($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] === 'Y';
			$fromTs = isset($value['DATE_FROM']) ? \CCalendar::timestamp($value['DATE_FROM'], true, !$skipTime) : 0;
			$toTs = isset($value['DATE_TO']) ? \CCalendar::timestamp($value['DATE_TO'], true, !$skipTime) : 0;

			$resultText = \CCalendar::getFromToHtml($fromTs, $toTs, $skipTime, $toTs - $fromTs).': ';
			$resultText = str_replace("&ndash;", '-', $resultText);
			if(!empty($value['SERVICE_NAME']))
			{
				$resultText .= $value['SERVICE_NAME'].', ';
			}
			$resultText .= implode(', ', $resListItems);
		}
		return $resultText;
	}

	public static function getDefaultResourcesList()
	{
		$result = [];

		$typeList = Internals\TypeTable::getList(
			array(
				"filter" => array(
					"XML_ID" => self::RESOURCE_CALENDAR_TYPE
				),
				"select" => array("XML_ID", "NAME")
			)
		);

		while ($type = $typeList->fetch())
		{
			$type['SECTIONS'] = [];
			$result[$type['XML_ID']] = $type;
		}

		if (!$result[self::RESOURCE_CALENDAR_TYPE])
		{
			Internals\TypeTable::add([
				'XML_ID' => self::RESOURCE_CALENDAR_TYPE,
				'NAME' => self::RESOURCE_CALENDAR_TYPE,
				'ACTIVE' => 'Y'
			]);
			\CCalendar::ClearCache('type_list');

			$result[self::RESOURCE_CALENDAR_TYPE] = [
				'XML_ID' => self::RESOURCE_CALENDAR_TYPE,
				'NAME' =>  self::RESOURCE_CALENDAR_TYPE
			];
		}

		$sectionList = Internals\SectionTable::getList(
			array(
				"filter" => array(
					"=ACTIVE" => 'Y',
					"CAL_TYPE" => [self::RESOURCE_CALENDAR_TYPE],
					"!=NAME" => ''
				),
				"select" => array("ID", "CAL_TYPE", "NAME")
			)
		);

		while ($section = $sectionList->fetch())
		{
			if (is_array($result[$section['CAL_TYPE']]['SECTIONS']))
			{
				$result[$section['CAL_TYPE']]['SECTIONS'][] = $section;
			}
		}

		return $result;
	}

	protected static function fetchFieldValue($value)
	{
		$resourseList = Internals\ResourceTable::getList(
			array(
				"filter" => array(
					"=ID" => $value
				)
			)
		);

		$result = array(
			'ENTRIES' => []
		);

		while ($resourse = $resourseList->fetch())
		{
			if (!isset($result['DATE_FROM']))
			{
				\CTimeZone::Disable();
				$result['DATE_FROM'] = $resourse['DATE_FROM']->toString();
				$result['DATE_TO'] = $resourse['DATE_TO']->toString();
				$result['SERVICE_NAME'] = $resourse['SERVICE_NAME'];
				\CTimeZone::Enable();

				$fromTs = \CCalendar::timestamp($result['DATE_FROM']);
				$toTs = \CCalendar::timestamp($result['DATE_TO']);

				if (!$resourse['SKIP_TIME'])
				{
					$currentUserID = \CCalendar::getCurUserId();

					$userOffsetFrom = \CCalendar::getTimezoneOffset($resourse['TZ_FROM'], $fromTs) - \CCalendar::getCurrentOffsetUTC($currentUserID);
					$userOffsetTo = \CCalendar::getTimezoneOffset($resourse['TZ_TO'], $toTs) - \CCalendar::getCurrentOffsetUTC($currentUserID);

					$result['DATE_FROM'] = \CCalendar::date($fromTs - $userOffsetFrom);
					$result['DATE_TO'] = \CCalendar::date($toTs - $userOffsetTo);
				}
				else
				{
					$result['DATE_TO'] = \CCalendar::date($toTs + \CCalendar::DAY_LENGTH);
				}
			}

			$result['ENTRIES'][] = array(
				'ID' => $resourse['ID'],
				'EVENT_ID' => $resourse['EVENT_ID'],
				'TYPE' => $resourse['CAL_TYPE'],
				'RESOURCE_ID' => $resourse['RESOURCE_ID']
			);
		}

		return $result;
	}

	public static function getDefaultServiceList()
	{
		return [
			array('name' => '', 'duration' => 60)
		];
	}

	public static function getBitrx24Limitation()
	{
		$limit = -1;
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$b24limit = Bitrix24\Feature::getVariable('calendar_resourcebooking_limit');
			if ($b24limit !== null)
			{
				return $b24limit;
			}

			//else: fallback
			$licenseType = \CBitrix24::getLicenseType();

			if ($licenseType === 'project' || $licenseType === 'self')
			{
				$limit = 6;
			}
			elseif ($licenseType === 'tf' || $licenseType === 'retail')
			{
				$limit = 12;
			}
			elseif ($licenseType === 'team' || $licenseType === 'start_2019')
			{
				$limit = 24;
			}
		}

		return $limit;
	}

	public static function getAvailableEntriesList()
	{
		return array('CRM_LEAD', 'CRM_DEAL');
	}

	public static function onBeforeUserTypeAdd(&$userTypeFields)
	{
		if ($userTypeFields['USER_TYPE_ID'] === 'resourcebooking')
		{
			$userTypeFields['MULTIPLE'] = 'Y';
		}
		return true;
	}

	public static function getResourceEntriesList($idList = [])
	{
		return self::fetchFieldValue($idList);
	}

	public static function getUserFieldByFieldName($fieldName = '', $selectedUsers = [])
	{
		$resultData = null;
		if ($fieldName)
		{
			$r = \CUserTypeEntity::getList(array("ID" => "ASC"), array("FIELD_NAME" => $fieldName));
			if ($r)
			{
				$resultData = $r->fetch();
			}
		}

		if (!is_array($selectedUsers))
		{
			$selectedUsers = [];
		}
		if (is_array($resultData) && isset($resultData['SETTINGS']['SELECTED_USERS']))
		{
			$selectedUsers = array_merge($selectedUsers, $resultData['SETTINGS']['SELECTED_USERS']);
		}

		array_walk($selectedUsers, 'intval');
		$selectedUsers = array_unique($selectedUsers);

		if (!empty($selectedUsers))
		{
			$orm = UserTable::getList([
				'filter' => [
					'=ID' => $selectedUsers,
					'=ACTIVE' => 'Y'
				],
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL']
			]);

			$resultData['SETTINGS']['USER_INDEX'] = [];
			while($user = $orm->fetch())
			{
				$resultData['SETTINGS']['USER_INDEX'][$user['ID']] = [
					'id' => $user['ID'],
					'displayName' => \CCalendar::getUserName($user)
				];
			}
		}

		return $resultData;
	}

	public static function getFillFormData($data = [], $params = [])
	{
		global $USER;
		$resultData = [];
		$curUserId = $USER->GetID();

		if (isset($params['from']) && $params['from'] instanceof Date
			&& isset($params['to']) && $params['to'] instanceof Date

		)
		{
			$from = $params['from']->toString();
			$to = $params['to']->toString();
		}
		else
		{
			$fromTs = (isset($params['from']) && $params['from']) ? \CCalendar::timestamp($params['from']) : time();
			$from = \CCalendar::date($fromTs, false);
			$to = (isset($params['to']) && $params['to'])
				? \CCalendar::date(\CCalendar::timestamp($params['to']), false)
				: \CCalendar::date($fromTs + \CCalendar::DAY_LENGTH * 60, false);
		}

		if (isset($params['timezone']))
		{
			$deltaOffset = \CCalendar::GetTimezoneOffset($params['timezone']) - \CCalendar::GetCurrentOffsetUTC($curUserId);
		}
		else
		{
			$deltaOffset = 0;
		}
		$resultData['timezoneOffset'] = 0;

		// Fetch fetch UF properties
		if ($params['fieldName'])
		{
			$r = \CUserTypeEntity::getList(array("ID" => "ASC"), array("FIELD_NAME" => $params['fieldName']));
			if ($r)
			{
				$fieldProperties = $r->fetch();
				$resultData['fieldSettings'] = $fieldProperties['SETTINGS'];

				if ($resultData['fieldSettings']['USE_USER_TIMEZONE'] === 'N')
				{
					$resultData['timezoneOffset'] = $resultData['fieldSettings']['TIMEZONE'] ? \CCalendar::GetTimezoneOffset($resultData['fieldSettings']['TIMEZONE']) : intval(date("Z"));
					$resultData['timezoneOffsetLabel'] = 'UTC'.($resultData['timezoneOffset'] <> 0 ? ' '.($resultData['timezoneOffset'] < 0? '-':'+').sprintf("%02d", ($h = floor(abs($resultData['timezoneOffset'])/3600))).':'.sprintf("%02d", abs($resultData['timezoneOffset']) / 60 - $h * 60) : '');
				}
			}
		}

		if (isset($data['users']))
		{
			$userIdList = is_array($data['users']['value'])
				? $data['users']['value']
				: explode('|', $data['users']['value']);
			array_walk($userIdList, 'intval');

			$resultData['usersAccessibility'] = [];
			$accessibility = \CCalendar::getAccessibilityForUsers(array(
				'users' => $userIdList,
				'from' => $from, // date or datetime in UTC
				'to' => $to, // date or datetime in UTC
				'getFromHR' => true,
				'checkPermissions' => false
			));

			foreach($accessibility as $userId => $entries)
			{
				$resultData['usersAccessibility'][$userId] = [];

				foreach($entries as $entry)
				{
					if (isset($entry['DT_FROM']) && !isset($entry['DATE_FROM']))
					{
						$resultData['usersAccessibility'][$userId][] = array(
							'id' => $entry['ID'],
							'dateFrom' => $entry['DT_FROM'],
							'dateTo' => $entry['DT_TO'],
						);
					}
					else
					{
						$fromTs = \CCalendar::Timestamp($entry['DATE_FROM']);
						$toTs = \CCalendar::Timestamp($entry['DATE_TO']);

						if ($entry['DT_SKIP_TIME'] !== "Y")
						{
							if ($resultData['fieldSettings']['USE_USER_TIMEZONE'] === 'N')
							{
								$fromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM']) - $resultData['timezoneOffset'];
								$toTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO']) - $resultData['timezoneOffset'];
							}
							else
							{
								$fromTs -= $entry['~USER_OFFSET_FROM'];
								$toTs -= $entry['~USER_OFFSET_TO'];
								$fromTs += $deltaOffset;
								$toTs += $deltaOffset;
							}
						}

						$resultData['usersAccessibility'][$userId][] = array(
							'id' => $entry['ID'],
							'dateFrom' => \CCalendar::Date($fromTs, $entry['DT_SKIP_TIME'] != 'Y'),
							'dateTo' => \CCalendar::Date($toTs, $entry['DT_SKIP_TIME'] != 'Y'),
							'fullDay' => $entry['DT_SKIP_TIME'] === "Y"
						);
					}
				}
			}

			// User Index
			$orm = UserTable::getList(
				[
					'filter' => [
						'=ID' => $userIdList,
						'=ACTIVE' => 'Y'
					],
					'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL']
				]
			);

			$resultData['SETTINGS']['USER_INDEX'] = [];
			while($user = $orm->fetch())
			{
				$resultData['userIndex'][$user['ID']] = [
					'id' => $user['ID'],
					'displayName' => \CCalendar::getUserName($user)
				];
			}
		}

		if (isset($data['resources']))
		{
			$resultData['resourcesAccessibility'] = [];

			$resourceIdList = is_array($data['resources']['value'])
				? $data['resources']['value']
				: explode('|', $data['resources']['value']);

			array_walk($resourceIdList, 'intval');

			$resEntries = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"FROM_LIMIT" => $from,
						"TO_LIMIT" => $to,
						"CAL_TYPE" => 'resource',
						"ACTIVE_SECTION" => "Y",
						"SECTION" => $resourceIdList
					),
					'parseRecursion' => true,
					'setDefaultLimit' => false
				)
			);

			foreach($resEntries as $row)
			{
				$fromTs = \CCalendar::timestamp($row["DATE_FROM"]);
				$toTs = \CCalendar::timestamp($row['DATE_TO']);

				if ($row['DT_SKIP_TIME'] !== "Y" && $resultData['fieldSettings']['USE_USER_TIMEZONE'] !== 'N')
				{
					if ($resultData['fieldSettings']['USE_USER_TIMEZONE'] === 'N')
					{
						$fromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM']) - $resultData['timezoneOffset'];
						$toTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO']) - $resultData['timezoneOffset'];
					}
					else
					{
						$fromTs -= $row['~USER_OFFSET_FROM'];
						$toTs -= $row['~USER_OFFSET_TO'];
						$fromTs += $deltaOffset;
						$toTs += $deltaOffset;
					}
				}

				$resultData['resourcesAccessibility'][$row['SECT_ID']][] = array(
					'id' => $row["ID"],
					'dateFrom' => \CCalendar::date($fromTs, $row['DT_SKIP_TIME'] !== 'Y'),
					'dateTo' => \CCalendar::date($toTs, $row['DT_SKIP_TIME'] !== 'Y'),
					'fullDay' => $row['DT_SKIP_TIME'] === "Y"
				);
			}
		}

		$resultData['workTimeStart'] = floor(floatVal(\COption::GetOptionString('calendar', 'work_time_start', 9)));
		$resultData['workTimeEnd'] = ceil(floatVal(\COption::GetOptionString('calendar', 'work_time_end', 19)));

		return $resultData;
	}

	public static function getFormDateTimeSlots($fieldName = '', $options = [])
	{
		$from = (isset($options['from']) && $options['from'] instanceof Date) ? $options['from'] : new Date();
		if (isset($options['to']) && ($options['to'] instanceof Date))
		{
			$to = $options['to'];
		}
		else
		{
			$to = clone $from;
			$to->add($options['dateInterval'] ?? 'P5D');
		}

		$formData = \Bitrix\Calendar\UserField\ResourceBooking::getFillFormData(
			$options['settingsData'],
			[
				'fieldName' => $fieldName,
				'from' => $from,
				'to' => $to
			]
		);

		// Merge Accessibility
		$accessibility = [];
		if ($formData['fieldSettings']['USE_USERS'] === 'Y'
			&& isset($options['settingsData']['users']['value']))
		{
			$selectedUser = $options['settingsData']['users']['value'];
			if (
				isset($formData['usersAccessibility'])
				&& isset($formData['usersAccessibility'][$selectedUser])
			)
			{
				$accessibility = array_merge($accessibility, $formData['usersAccessibility'][$selectedUser]);
			}
		}

		if ($formData['fieldSettings']['USE_RESOURCES'] === 'Y'
			&& isset($options['settingsData']['resources']['value']))
		{
			$selectedResource = $options['settingsData']['resources']['value'];
			if (
				isset($formData['resourcesAccessibility'])
				&& isset($formData['resourcesAccessibility'][$selectedResource])
			)
			{
				$accessibility = array_merge($accessibility, $formData['resourcesAccessibility'][$selectedResource]);
			}
		}

		$result = null;
		if ($selectedUser || $selectedResource)
		{
			$format = Date::convertFormatToPhp(FORMAT_DATETIME);
			foreach ($accessibility as $i => $item)
			{
				$accessibility[$i]['fromTs'] = (new DateTime($item['dateFrom'], $format))->getTimestamp();
				$accessibility[$i]['toTs'] = (new DateTime($item['dateTo'], $format))->getTimestamp();
			}

			$result = self::getAvailableTimeSlots($accessibility, [
				'from' => $from,
				'to' => $to,
				'scale' => $options['settingsData']['time']['scale']
			]);
		}

		return $result;
	}

	private static function getAvailableTimeSlots($accessibility, $options)
	{
		$from = (isset($options['from']) && $options['from'] instanceof Date) ? $options['from'] : new Date();
		$to = (isset($options['to']) && $options['to'] instanceof Date) ? $options['to'] : new Date();
		$to->add('P1D');
		$scale = (int)$options['scale'] > 0 ? (int)$options['scale'] : 60;

		$workTimeStart = (int)\COption::getOptionString('calendar', 'work_time_start', 9);
		$workTimeEnd = (int)\COption::getOptionString('calendar', 'work_time_end', 19);

		$step = 0;
		$currentDate = new DateTime($from->toString(), Date::convertFormatToPhp(FORMAT_DATETIME));
		$slots = [];

		while ($currentDate->getTimestamp() < $to->getTimestamp())
		{
			$currentDate->setTime($workTimeStart, 0, 0);
			while ((int)$currentDate->format('H') < $workTimeEnd)
			{
				if ($currentDate->getTimestamp() > time())
				{
					$isFree = true;
					$slotStart = $currentDate->getTimestamp();
					$slotEnd = $slotStart + $scale * 60;

					foreach ($accessibility as $i => $item)
					{
						if ($item['toTs'] > $slotStart && $item['fromTs'] < $slotEnd)
						{
							$isFree = false;
							break;
						}
					}

					if ($isFree)
					{
						$slots[] = clone $currentDate;
					}
				}
				$currentDate->add('PT'.$scale.'M');
				$step++;
			}

			if($step > 1000)
			{
				break;
			}
			$currentDate->add('P1D');
		}

		return $slots;
	}

	public static function prepareFormDateValues($dateFrom = null, $fieldName = '', $options = [])
	{
		$result = [];
		if (!isset($dateFrom) || !($dateFrom instanceof DateTime))
		{
			throw new \Bitrix\Main\SystemException('Wrong dateFrom value type. DateTime expected');
		}
		if (empty($fieldName))
		{
			throw new \Bitrix\Main\SystemException('Wrong fieldName given');
		}

		$duration = 60;
		if (!empty($options['settingsData']['duration']['value']))
		{
			$duration = $options['settingsData']['duration']['value'];
		}
		else if (!empty($options['settingsData']['duration']['defaultValue']))
		{
			$duration = $options['settingsData']['duration']['defaultValue'];
		}

		$r = \CUserTypeEntity::getList(["ID" => "ASC"], ["FIELD_NAME" => $fieldName]);
		if ($r)
		{
			$fieldProperties = $r->fetch();
			$fieldSettings = $fieldProperties['SETTINGS'];

			if ($fieldSettings['USE_USERS'] === 'Y'
				&& isset($options['settingsData']['users']['value']))
			{
				$result[] = self::prepareValue('user', $options['settingsData']['users']['value'], $dateFrom->toString(), $duration);
			}

			if ($fieldSettings['USE_RESOURCES'] === 'Y'
				&& isset($options['settingsData']['resources']['value']))
			{
				$result[] = self::prepareValue('resource', $options['settingsData']['resources']['value'], $dateFrom->toString(), $duration);
			}
		}

		return $result;
	}
}