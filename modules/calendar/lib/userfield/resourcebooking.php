<?php
namespace Bitrix\Calendar\UserField;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Calendar\Internals;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);


class ResourceBooking extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'resourcebooking';
	const RESOURCE_CALENDAR_TYPE = 'resource';
	const BITRIX24_RESTRICTION = 100;
	const BITRIX24_RESTRICTION_CODE = 'uf_resourcebooking';

	protected static $restrictionCount = null;

	function getUserTypeDescription()
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

	public static function prepareSettings($userField = array())
	{
		$selectedResources = array();
		if (!is_array($userField["SETTINGS"]))
		{
			$userField["SETTINGS"] = array();
		}

		if (is_array($userField["SETTINGS"]["SELECTED_RESOURCES"]))
		{
			$selectedResources = self::handleResourceList($userField["SETTINGS"]["SELECTED_RESOURCES"]);
		}
		$selectedUsers = array();
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
			"RESOURCE_LIMIT" => self::getBitrx24Limitation()
		);
	}

	function getDBColumnType()
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
		}
	}

	function checkFields($userField, $value)
	{
		if($userField["MANDATORY"] =="Y" && ($value == 'empty' || !$value))
		{
			return array(array(
				"id" => $userField['FIELD_NAME'],
				"text"=>str_replace("#FIELD_NAME#", $userField['EDIT_FORM_LABEL'],
					Loc::getMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))
			));
		}

		return array();
	}

	public static function onBeforeSaveAll($userField, $values, $userId = false)
	{
		$valuesToSave = array();
		$currentUserId = \CCalendar::getCurUserId();
		$dateFrom = false;
		$dateTo = false;
		$serviceName = '';
		$entityTitle = '';
		$fields = array();

		$resourseList = Internals\ResourceTable::getList(
			array(
				"filter" => array(
					"PARENT_TYPE" => $userField['ENTITY_ID'],
					"PARENT_ID" => $userField['VALUE_ID'],
					"UF_ID" => $userField['ID']
				)
			)
		);

		$currentEntriesIndex = array();
		while ($resourse = $resourseList->fetch())
		{
			$currentEntriesIndex[$resourse['CAL_TYPE'].$resourse['RESOURCE_ID']] = $resourse;
		}

		if ($userField['ENTITY_ID'] == 'CRM_DEAL' && Loader::includeModule('crm'))
		{
			$entity = \CCrmDeal::GetByID($userField['VALUE_ID'], false);
			if (!empty($entity) && $entity['TITLE'])
			{
				$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE").': '.$entity['TITLE'];
			}
		}
		elseif ($userField['ENTITY_ID'] == 'CRM_LEAD' && Loader::includeModule('crm'))
		{
			$entity = \CCrmLead::GetByID($userField['VALUE_ID'], false);
			if (!empty($entity) && $entity['TITLE'])
			{
				$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE").': '.$entity['TITLE'];
			}
		}

		if (!$entityTitle)
		{
			$entityTitle = Loc::getMessage("USER_TYPE_RESOURCE_EVENT_TITLE");
		}

		foreach ($values as $value)
		{
			if ($value == 'empty')
			{
				continue;
			}

			$value = self::parseValue($value);

			if (!$dateFrom || !$dateTo)
			{
				$dateFromTimestamp = \CCalendar::timestamp($value['from']);
				$skipTime = isset($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] == 'Y';
				$dateFrom = \CCalendar::date($dateFromTimestamp, !$skipTime);
				$duration = intval($value['duration']);
				$dateTo = \CCalendar::date($dateFromTimestamp + ($skipTime ? $duration - \CCalendar::DAY_LENGTH : $duration), !$skipTime);
				$serviceName = trim($value['serviceName']);

				$fields = array(
					"DATE_FROM" => $dateFrom,
					"DATE_TO" => $dateTo,
					"SKIP_TIME" => $skipTime,
					"NAME" => $entityTitle
				);

				if ($serviceName !== '')
				{
					$fields["DESCRIPTION"] = Loc::getMessage("USER_TYPE_RESOURCE_SERVICE_LABEL").': '.$serviceName;
				}

				if (!$skipTime)
				{
					$userTimezoneName = \CCalendar::getUserTimezoneName($currentUserId, true);
					if($userTimezoneName)
					{
						$fields['TZ_FROM'] = $userTimezoneName;
						$fields['TZ_TO'] = $userTimezoneName;
					}
				}
			}

			$entryId = false;
			if (isset($currentEntriesIndex[$value['type'].$value['id']]))
			{
				$fields['ID'] = $currentEntriesIndex[$value['type'].$value['id']]['EVENT_ID'];
				$entryId = $currentEntriesIndex[$value['type'].$value['id']]['ID'];
			}
			else
			{
				unset($fields['ID']);
			}

//			try
//			{
				$resourceBookingId = self::saveResource(
					$entryId,
					$value['type'],
					$value['id'],
					$fields,
					array(
						'userField' => $userField,
						'serviceName' => $serviceName
					)
				);

				if ($resourceBookingId)
				{
					$valuesToSave[] = $resourceBookingId;
				}
//			}
//			catch(Main\SystemException $e)
//			{
//
//			}
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
		if ($resourceType == 'user')
		{
			$eventFields["CAL_TYPE"] = $resourceType;
			$eventFields["OWNER_ID"] = $resourceId;

			if ($params['userField']['ENTITY_ID'] == 'CRM_DEAL' || $params['userField']['ENTITY_ID'] == 'CRM_LEAD')
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
			if ($params['userField']['ENTITY_ID'] == 'CRM_DEAL')
			{
				$userFields['UF_CRM_CAL_EVENT'] = ["D_".$params['userField']['VALUE_ID']];
			}
			elseif ($params['userField']['ENTITY_ID'] == 'CRM_LEAD')
			{
				$userFields['UF_CRM_CAL_EVENT'] = ["L_".$params['userField']['VALUE_ID']];
			}

//			if (!self::isUserAvailable([
//				'DATE_FROM' => $eventFields["DATE_FROM"],
//				'DATE_TO' => $eventFields["DATE_TO"],
//
//			]))
//			{
//				throw new Main\SystemException('Resource can\'t be booked');
//			}

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
			if ($eventFields['TZ_FROM'])
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
//			if ($userTimezoneName)
//			{
//				$from->setTimeZone(new \DateTimeZone($userTimezoneName));
//				$to->setTimeZone(new \DateTimeZone($userTimezoneName));
//			}

			$resourceTableFields = [
				'EVENT_ID' => $entryId,
				'CAL_TYPE' => $eventFields["CAL_TYPE"],
				'RESOURCE_ID' => $resourceId,
				'PARENT_TYPE' => isset($params['bindingEntityType']) ? $params['bindingEntityType'] : $params['userField']['ENTITY_ID'],
				'PARENT_ID' => isset($params['bindingEntityId']) ? $params['bindingEntityId'] :  $params['userField']['VALUE_ID'],
				'UF_ID' => isset($params['bindingUserfieldId']) ? $params['bindingUserfieldId'] :  $params['userField']['ID'],
				'DATE_FROM' => $from,
				'DATE_TO' => $to,
				'SKIP_TIME' => $eventFields['SKIP_TIME'],
				'DATE_FROM_UTC' => $fromUtc,
				'DATE_TO_UTC' => $toUtc,
				'TZ_FROM' => $eventFields['TZ_FROM'],
				'TZ_TO' => $eventFields['TZ_TO'],
				'TZ_OFFSET_FROM' => \CCalendar::getTimezoneOffset($eventFields['TZ_FROM'], \CCalendar::timestamp($eventFields["DATE_FROM"])),
				'TZ_OFFSET_TO' => \CCalendar::getTimezoneOffset($eventFields['TZ_TO'], \CCalendar::timestamp($eventFields["DATE_TO"])),
				'CREATED_BY' => $currentUserId,
				'SERVICE_NAME' => $params['serviceName']
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
				\CCalendar::deleteEvent(intVal($entryId), false);
			}

			foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "onAfterResourceBookingAdd") as $event)
			{
				ExecuteModuleEventEx($event, [
					'userFieldValueId' => $valueToSave,
					'bookingEventId' => $entryId,
					'resourceType' => $resourceType,
					'resourceId' => $resourceId,
					'serviceName' => $params['serviceName'],
					'dateFrom' => $from,
					'dateTo' => $to,
					'skipTime' => $eventFields['SKIP_TIME'],
					'timezoneFrom' => $eventFields['TZ_FROM'],
					'timezoneTo' => $eventFields['TZ_TO']
				]);
			}
		}

		return $valueToSave;
	}

	private static function isUserAvailable($params)
	{
		$fromTs = \CCalendar::Timestamp($params["DATE_FROM"]);
		$toTs = \CCalendar::Timestamp($params["DATE_TO"]);
		$fromTs = $fromTs - \CCalendar::GetTimezoneOffset($params["TZ_FROM"], $fromTs);
		$toTs = $toTs - \CCalendar::GetTimezoneOffset($params["TZ_TO"], $toTs);

		$accessibility = \CCalendar::GetAccessibilityForUsers(array(
			'users' => [$params['id']],
			'from' => \CCalendar::Date($fromTs), // date or datetime in UTC
			'to' => \CCalendar::Date($toTs), // date or datetime in UTC
			'getFromHR' => true,
			'checkPermissions' => false
		));

		if ($accessibility[$params['id']])
		{
			foreach($accessibility[$params['id']] as $entry)
			{
				$entFromTs = \CCalendar::Timestamp($entry["DATE_FROM"]);
				$entToTs = \CCalendar::Timestamp($entry["DATE_TO"]);

				$entFromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
				$entToTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

				if ($entFromTs < $toTs && $entToTs > $fromTs)
				{
					return false;
				}
			}
		}

		return true;
	}

	private static function isResourceAvailable()
	{

	}

	public static function releaseResource($entry)
	{
		\CCalendar::deleteEvent(intVal($entry['EVENT_ID']), true, array('checkPermissions' => false));
		Internals\ResourceTable::delete($entry['ID']);
	}

	private static function handleResourceList($resources)
	{
		$result = array();
		foreach($resources as $resource)
		{
			if (is_array($resource))
			{
				if ($resource['id'] && ($resource['deleted'] || $resource['title'] == ''))
				{
					$sectionList = Internals\SectionTable::getList(
						array(
							"filter" => array("ID" => $resource['id'], "CAL_TYPE" => $resource['type']),
							"select" => array("ID", "CAL_TYPE", "NAME")
						)
					);
					if ($sectionList->fetch())
					{
						Internals\SectionTable::delete(array('ID' => $resource['id']));
					}
				}
				else if ($resource['id'])
				{
					$sectionList = Internals\SectionTable::getList(
						array(
							"filter" => array("ID" => $resource['id'], "CAL_TYPE" => $resource['type']),
							"select" => array("ID", "CAL_TYPE", "NAME")
						)
					);
					if ($section = $sectionList->fetch())
					{
						if ($section['NAME'] != $resource['title'])
						{
							\CCalendarSect::edit(array(
								'arFields' => array(
									'ID' => $resource['id'],
									'CAL_TYPE' => $resource['type'],
									'NAME' => $resource['title'],
									'ACCESS' => array()
								)
							));
						}
					}
					$result[] = $resource;
				}
				elseif (!$resource['id'] && $resource['title'] !== '')
				{
					$resource['id'] = \CCalendarSect::edit(array(
						'arFields' => array(
							'CAL_TYPE' => $resource['type'],
							'NAME' => $resource['title'],
							'ACCESS' => array()
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
		$type = $type == 'user' || $type == 'resource' ? $type : 'user';
		$id = intval($id) > 0 ? $id : 1;
		$duration = intval($duration) > 0 ? $duration : 60;
		return $type.'|'.$id.'|'.$from.'|'.$duration.'|'.$serviceName;
	}

	public static function parseValue($value)
	{
		$res = false;
		if(strpos($value, '|') >= 0)
		{
			list($type, $id, $from, $duration, $serviceName) = explode('|', $value);
			$res = array(
				'type' => $type,
				'id' => $id,
				'from' => $from,
				'duration' => $duration,
				'serviceName' => $serviceName ? $serviceName : ''
			);
		}
		return $res;
	}

	function getSettingsHTML($userField = false, $htmlControl = [], $varsFromForm = false)
	{
		static::initDisplay(array('userfield_resourcebooking', 'calendar_planner', 'socnetlogdest', 'helper'));

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
			$settingsValue = array();
		}

		$params = [
			'controlId' => $userField['FIELD_NAME'],
			'settings' => $settingsValue,
			'userField' => $userField,
			'htmlControl' => $htmlControl,
			'outerWrapId' => $userField['FIELD_NAME'].'-settings-outer-wrap',
			'formName' => 'post_form'
		];

		if ($settingsValue['USE_USERS'] == 'Y')
		{
			$params['socnetDestination'] = \CCalendar::getSocNetDestination(false, array(), $settingsValue['SELECTED_USERS']);
		}

		$result = '<tr>
			<td></td>
			<td>
				<div id="'.HtmlFilter::encode($params['outerWrapId']).'"></div>
				<script>(function(){new BX.Calendar.UserField.ResourceBooking.AdminSettingsViewer('.
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

	public static function getPublicEdit($userField, $additionalParams = array())
	{
		static::initDisplay(array('userfield_resourcebooking', 'calendar_planner', 'socnetlogdest'));

		$fieldName = static::getFieldName($userField, $additionalParams);
		$userField['VALUE'] = static::getFieldValue($userField, $additionalParams);

		$value = static::fetchFieldValue($userField["VALUE"]);

		if ($userField['SETTINGS']['USE_RESOURCES'] == 'Y'
		&& (!is_array($userField['SETTINGS']['SELECTED_RESOURCES']) || !count($userField['SETTINGS']['SELECTED_RESOURCES'])))
		{
			$userField['SETTINGS']['USE_RESOURCES'] = 'N';
		}

		$params = [
			'controlId' => $userField['FIELD_NAME'],
			'inputName' => $fieldName,
			'value' => $value,
			'plannerId' => $userField['FIELD_NAME'].'_planner',
			'userSelectorId' => 'resource_booking_user_selector',
			'useUsers' => $userField['SETTINGS']['USE_USERS'] == 'Y',
			'useResources' => $userField['SETTINGS']['USE_RESOURCES'] == 'Y',
			'fullDay' => $userField['SETTINGS']['FULL_DAY'] == 'Y',
			'allowOverbooking' => $userField['SETTINGS']['ALLOW_OVERBOOKING'] !== 'N',
			'useServices' => $userField['SETTINGS']['USE_SERVICES'] == 'Y',
			'serviceList' => $userField['SETTINGS']['SERVICE_LIST'],
			'resourceList' => $userField['SETTINGS']['SELECTED_RESOURCES'],
			'userList' => $userField['SETTINGS']['SELECTED_USERS'],
			'userfieldId' => $userField['ID'],
			'resourceLimit' => self::getBitrx24Limitation()
		];

		if ($params['useUsers'])
		{
			$params['socnetDestination'] = \CCalendar::getSocNetDestination(false, array(), $userField['SETTINGS']['SELECTED_USERS']);
		}

		ob_start();
		?>

		<div id="<?= HtmlFilter::encode($params['controlId'])?>" class="crm-entity-widget-resourcebook-container"></div>
		<script>
			(function(){
				'use strict';
				new BX.Calendar.UserField.ResourceBooking(<?= \Bitrix\Main\Web\Json::encode($params)?>)
					.showEditLayout();
			})();
		</script>
		<?

		$html = ob_get_clean();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicView($userField, $additionalParams = array())
	{
		$context = isset($additionalParams['CONTEXT']) ? $additionalParams['CONTEXT'] : '';
		$value = static::fetchFieldValue($userField["VALUE"]);
		$skipTime = is_array($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] == 'Y';
		$fromTs = \CCalendar::timestamp($value['DATE_FROM'], true, !$skipTime);
		$toTs = \CCalendar::timestamp($value['DATE_TO'], true, !$skipTime);

		$users = [];
		$resources = [];
		$resourceNames = [];
		$userIdList = [];
		$resourseIdList = [];
		foreach($value['ENTRIES'] as $entry)
		{
			if ($entry['TYPE'] == 'user')
			{
				$userIdList[] = $entry['RESOURCE_ID'];

				$db = \CUser::getList($by = 'ID', $order = 'ASC',
					array('ID'=> $entry['RESOURCE_ID']),
					array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'PERSONAL_PHOTO'))
				);
				if ($row = $db->fetch())
				{
					$row['URL'] = \CCalendar::getUserUrl($row["ID"]);
					$users[] = $row;
				}
			}
			else
			{
				$resourseIdList[] = $entry['RESOURCE_ID'];
			}
		}

		if (count($resourseIdList) > 0)
		{
			$sectionList = Internals\SectionTable::getList(
				array(
					"filter" => array(
						"=ACTIVE" => 'Y',
						"!=CAL_TYPE" => ['user', 'group', 'company_calendar'],
						"ID" => $resourseIdList
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

		if ($context == 'CRM_GRID')
		{
			\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/calendar/userfield/resourcebooking.css');
			$resListItems = array();
			if(count($users) > 0)
			{
				foreach($users as $user)
				{
					$resListItems[] = '<span>'.HtmlFilter::encode(\CCalendar::getUserName($user)).'</span>';
				}
			}
			if(count($resourceNames) > 0)
			{
				foreach($resourceNames as $resourceName)
				{
					$resListItems[] = '<span>'.$resourceName.'</span>';
				}
			}

			if (count($resListItems) > 0)
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
			static::initDisplay(array('userfield_resourcebooking'));
			if (count($users) + count($resourceNames) == 0)
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

						<? if(count($users) > 0): ?>
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

						<? if(count($resourceNames) > 0): ?>
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

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		return '';
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
			'ENTRIES' => array()
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

	public static function getB24LimitationPopupParams()
	{
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$params = array(
				"B24_LICENSE_BUTTON_TEXT" => GetMessage("B24_LICENSE_BUTTON"),
				"B24_TRIAL_BUTTON_TEXT" => GetMessage("B24_TRIAL_BUTTON"),
				"IS_FULL_DEMO_EXISTS" => \CBitrix24::getLicenseType() != "company" && \Bitrix\Bitrix24\Feature::isEditionTrialable("demo") ? "Y" : "N",
				"HOST_NAME" => BX24_HOST_NAME,
				"AJAX_URL" => \CBitrix24::PATH_COUNTER,
				"LICENSE_ALL_PATH" => \CBitrix24::PATH_LICENSE_ALL,
				"LICENSE_DEMO_PATH" => \CBitrix24::PATH_LICENSE_DEMO
			);
			if (
				!\Bitrix\Bitrix24\Feature::isEditionTrialable("demo")
				&& !empty($featureGroupName)
				&& \Bitrix\Bitrix24\Feature::isEditionTrialable($featureGroupName)
			)
			{
				$params["FEATURE_GROUP_NAME"] = $featureGroupName;
				$params["AJAX_ACTIONS_URL"] = "/bitrix/tools/b24_actions.php";
				$params["B24_FEATURE_TRIAL_SUCCESS_TEXT"] = GetMessageJS("B24_FEATURE_TRIAL_SUCCESS_TEXT");
			}

			$billingCurrency = \CBitrix24::BillingCurrency();
			$productPrices = \CBitrix24::getPrices($billingCurrency);
			$params["tfPrice"] = \CBitrix24::ConvertCurrency($productPrices["TF1"]["PRICE"], $billingCurrency);

			return $params;
		}
		return null;
	}

	public static function getBitrx24Limitation()
	{
		$limit = -1;
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$licenseType = \CBitrix24::getLicenseType();

			if ($licenseType == 'project')
			{
				$limit = 6;
			}
			elseif ($licenseType == 'tf' || $licenseType == 'retail')
			{
				$limit = 12;
			}
			elseif ($licenseType == 'team')
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
		if ($userTypeFields['USER_TYPE_ID'] == 'resourcebooking')
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
			$dbUsers = \CUser::getList($by = 'ID', $order = 'ASC',
				[
					'ID'=> implode(' | ', $selectedUsers)
				],
				['FIELDS' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL']]
			);

			$resultData['SETTINGS']['USER_INDEX'] = [];
			while($user = $dbUsers->fetch())
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

		$fromTs = (isset($params['from']) && $params['from']) ? \CCalendar::timestamp($params['from']) : time();
		$from = \CCalendar::date($fromTs, false);
		$to = (isset($params['to']) && $params['to'])
			? \CCalendar::date(\CCalendar::timestamp($params['to']), false)
			: \CCalendar::date($fromTs + \CCalendar::DAY_LENGTH * 60, false);

		if (isset($params['timezone']))
		{
			$deltaOffset = \CCalendar::GetTimezoneOffset($params['timezone']) - \CCalendar::GetCurrentOffsetUTC($curUserId);
		}
		else
		{
			$deltaOffset = 0;
		}

		// Fetch fetch UF properties
		if ($params['fieldName'])
		{
			$r = \CUserTypeEntity::getList(array("ID" => "ASC"), array("FIELD_NAME" => $params['fieldName']));
			if ($r)
			{
				$fieldProperties = $r->fetch();
				$resultData['fieldSettings'] = $fieldProperties['SETTINGS'];
			}
		}

		if (isset($data['users']))
		{
			$userIdList = explode('|', $data['users']['value']);
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
							$fromTs -= $entry['~USER_OFFSET_FROM'];
							$toTs -= $entry['~USER_OFFSET_TO'];
							$fromTs += $deltaOffset;
							$toTs += $deltaOffset;
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
			$dbUsers = \CUser::getList($by = 'ID', $order = 'ASC',
				[
					'ID'=> implode(' | ', $userIdList)
				],
				['FIELDS' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL']]
			);

			while($user = $dbUsers->fetch())
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

			$resourceIdList = explode('|', $data['resources']['value']);
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
				if ($row['DT_SKIP_TIME'] !== "Y")
				{
					$fromTs -= $row['~USER_OFFSET_FROM'];
					$toTs -= $row['~USER_OFFSET_TO'];
					$fromTs += $deltaOffset;
					$toTs += $deltaOffset;
				}
				$resultData['resourcesAccessibility'][$row['SECT_ID']][] = array(
					'id' => $row["ID"],
					'dateFrom' => \CCalendar::date($fromTs, $row['DT_SKIP_TIME'] != 'Y'),
					'dateTo' => \CCalendar::date($toTs, $row['DT_SKIP_TIME'] != 'Y'),
					'fullDay' => $row['DT_SKIP_TIME'] === "Y"
				);
			}
		}

		$resultData['workTimeStart'] = \COption::GetOptionString('calendar', 'work_time_start', 9);
		$resultData['workTimeEnd'] = \COption::GetOptionString('calendar', 'work_time_end', 19);

		return $resultData;
	}
}