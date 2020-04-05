<?php
namespace Bitrix\Calendar\UserField;

use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Calendar\Internals;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);


class ResourceBooking extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'resourcebooking';

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
				$dateFromTimestamp = \CCalendar::Timestamp($value['from']);
				$skipTime = isset($userField['SETTINGS']) && $userField['SETTINGS']['FULL_DAY'] == 'Y';
				$dateFrom = \CCalendar::Date($dateFromTimestamp, !$skipTime);
				$duration = intval($value['duration']);
				$dateTo = \CCalendar::Date($dateFromTimestamp + ($skipTime ? $duration - \CCalendar::DAY_LENGTH : $duration), !$skipTime);
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
					$userTimezoneOffsetUTC = \CCalendar::GetCurrentOffsetUTC($currentUserId);
					$userTimezoneName = \CCalendar::GetUserTimezoneName($currentUserId);
					if(!$userTimezoneName)
					{
						$userTimezoneName = \CCalendar::GetGoodTimezoneForOffset($userTimezoneOffsetUTC);
					}
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

	private static function saveResource($id, $resourceType, $resourceId, $eventFields = array(), $params = array())
	{
		$valueToSave = false;
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
				$eventFields['SECTIONS'] = array($sectionId);
			}

			// Userfields for event
			$arUFFields = array();
			if ($params['userField']['ENTITY_ID'] == 'CRM_DEAL')
			{
				$arUFFields['UF_CRM_CAL_EVENT'] = array("D_".$params['userField']['VALUE_ID']);
			}
			elseif ($params['userField']['ENTITY_ID'] == 'CRM_LEAD')
			{
				$arUFFields['UF_CRM_CAL_EVENT'] = array("L_".$params['userField']['VALUE_ID']);
			}

			$entryId = \CCalendar::SaveEvent(array(
				'arFields' => $eventFields,
				'UF' => $arUFFields,
				'silentErrorMode' => false,
				'autoDetectSection' => true,
				'autoCreateSection' => true,
				'checkPermission' => false
			));
		}
		else
		{
			$eventFields["CAL_TYPE"] = $resourceType;
			$eventFields["SECTIONS"] = $resourceId;

			$entryId = \CCalendarEvent::Edit(array(
				'arFields' => $eventFields
			));
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

			$resourceTableFields = array(
				'EVENT_ID' => $entryId,
				'CAL_TYPE' => $eventFields["CAL_TYPE"],
				'RESOURCE_ID' => $resourceId,
				'PARENT_TYPE' => $params['userField']['ENTITY_ID'],
				'PARENT_ID' => $params['userField']['VALUE_ID'],
				'UF_ID' => $params['userField']['ID'],
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
			);

			if ($id)
			{
				$result = Internals\ResourceTable::update($id, $resourceTableFields);
			}
			else
			{
				$result = Internals\ResourceTable::add($resourceTableFields);
			}

			\CTimeZone::Enable();

			if ($result->isSuccess())
			{
				$valueToSave = $result->getId();
			}
			else
			{
				\CCalendar::DeleteEvent(intVal($entryId), false);
			}
		}

		return $valueToSave;
	}

	private static function releaseResource($entry)
	{
		\CCalendar::DeleteEvent(intVal($entry['EVENT_ID']), false);
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
							\CCalendarSect::Edit(array(
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
					$resource['id'] = \CCalendarSect::Edit(array(
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

	function getSettingsHTML($userField = false, $htmlControl, $bVarsFromForm)
	{
		static::initDisplay(array('userfield_resourcebooking', 'calendar_planner', 'socnetlogdest', 'helper'));

		if($bVarsFromForm)
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
			$params['socnetDestination'] = \CCalendar::GetSocNetDestination(false, array(), $settingsValue['SELECTED_USERS']);
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

	function getEditFormHTML($userField, $arHtmlControl)
	{
		return static::getPublicEdit($userField, $arHtmlControl);
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
			$params['socnetDestination'] = \CCalendar::GetSocNetDestination(false, array(), $userField['SETTINGS']['SELECTED_USERS']);
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
		$fromTs = \CCalendar::Timestamp($value['DATE_FROM'], true, !$skipTime);
		$toTs = \CCalendar::Timestamp($value['DATE_TO'], true, !$skipTime);

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

				$db = \CUser::GetList($by = 'ID', $order = 'ASC',
					array('ID'=> $entry['RESOURCE_ID']),
					array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'PERSONAL_PHOTO'))
				);
				if ($row = $db->Fetch())
				{
					$row['URL'] = \CCalendar::GetUserUrl($row["ID"]);
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
					$resListItems[] = '<span>'.HtmlFilter::encode(\CCalendar::GetUserName($user)).'</span>';
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
				$html = '<span>'.\CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $toTs - $fromTs).'</span>: ';
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
								class="crm-entity-widget-content-block-inner"><?= \CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $toTs - $fromTs) ?></div>
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
										<a class="crm-widget-employee-avatar-container" href="<?= $user['URL'] ?>" target="_blank"
										   style="background-image: url('<?= \CCalendar::GetUserAvatarSrc($user) ?>'); background-size: 30px;"></a><span
											class="crm-widget-employee-info"><a class="crm-widget-employee-name" href="<?= $user['URL']?>" target="_blank"><?= HtmlFilter::encode(\CCalendar::GetUserName($user))?></a><span class="crm-widget-employee-position"></span></span>
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
				"filter" => array("=ACTIVE" => 'Y', "XML_ID" => 'resource'),
				"select" => array("XML_ID", "NAME")
			)
		);

		while ($type = $typeList->fetch())
		{
			$type['SECTIONS'] = [];
			$result[$type['XML_ID']] = $type;
		}

		$sectionList = Internals\SectionTable::getList(
			array(
				"filter" => array("=ACTIVE" => 'Y', "CAL_TYPE" => ['resource'], "!=NAME" => ''),
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

				$fromTs = \CCalendar::Timestamp($result['DATE_FROM']);
				$toTs = \CCalendar::Timestamp($result['DATE_TO']);

				if (!$resourse['SKIP_TIME'])
				{
					$currentUserID = \CCalendar::GetCurUserId();

					$userOffsetFrom = \CCalendar::GetTimezoneOffset($resourse['TZ_FROM'], $fromTs) - \CCalendar::GetCurrentOffsetUTC($currentUserID);
					$userOffsetTo = \CCalendar::GetTimezoneOffset($resourse['TZ_TO'], $toTs) - \CCalendar::GetCurrentOffsetUTC($currentUserID);

					$result['DATE_FROM'] = \CCalendar::Date($fromTs - $userOffsetFrom);
					$result['DATE_TO'] = \CCalendar::Date($toTs - $userOffsetTo);
				}
				else
				{
					$result['DATE_TO'] = \CCalendar::Date($toTs + \CCalendar::DAY_LENGTH);
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
			$arProductPrices = \CBitrix24::getPrices($billingCurrency);
			$params["tfPrice"] = \CBitrix24::ConvertCurrency($arProductPrices["TF1"]["PRICE"], $billingCurrency);

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
			elseif ($licenseType == 'tf')
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
}