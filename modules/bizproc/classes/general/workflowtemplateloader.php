<?php

use Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Bizproc\Api\Enum\Template\WorkflowTemplateType;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateSettingsTable;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

define("BP_EI_DIRECTION_EXPORT", 0);
define("BP_EI_DIRECTION_IMPORT", 1);

/**
* Workflow templates service.
*/
class CBPWorkflowTemplateLoader
{
	protected $useGZipCompression = false;
	protected $templateType = null;
	protected static $workflowConstants = array();
	const CONSTANTS_CACHE_TAG_PREFIX = 'b_bp_wf_constants_';
	protected static $typesStates = array();

	private static $instance;

	public const MAX_PARAMETERS_LENGTH = 65535;
	public const MAX_VARIABLES_LENGTH = 65535;
	public const MAX_CONSTANTS_LENGTH = 16777215;

	private function __construct()
	{
		$this->useGZipCompression = static::useGZipCompression();
	}

	public function __clone()
	{
		trigger_error('Clone in not allowed.', E_USER_ERROR);
	}

	/**
	 * Static method returns loader object. Singleton pattern.
	 *
	 * @return CBPWorkflowTemplateLoader
	 */
	public static function getLoader()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public static function getList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		return $loader->GetTemplatesList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
	}

	public static function checkTemplateActivities(array $template)
	{
		foreach ($template as $activity)
		{
			if (!CBPActivity::IncludeActivityFile($activity['Type']))
				return false;
			if (!empty($activity['Children']))
			{
				$childResult = static::checkTemplateActivities($activity['Children']);
				if (!$childResult)
					return false;
			}
		}

		return true;
	}

	public function validateTemplate($arActivity, $user)
	{
		$errors = CBPActivity::callStaticMethod(
			$arActivity['Type'],
			'ValidateProperties',
			[$arActivity['Properties'], $user]
		);

		$pref = '';
		if (isset($arActivity['Properties']['Title']))
		{
			$pref =
				Loc::getMessage('BPWTL_ERROR_MESSAGE_PREFIX', ['#TITLE#' => $arActivity['Properties']['Title']])
				. ' '
			;
		}

		foreach ($errors as $i => $e)
		{
			$errors[$i]['message'] = $pref . $e['message'];
			$errors[$i]['activityName'] = $arActivity['Name'];
		}

		if (array_key_exists('Children', $arActivity) && count($arActivity['Children']) > 0)
		{
			$bFirst = true;

			$childrenErrors = [];
			foreach ($arActivity['Children'] as $arChildActivity)
			{
				if (!isset($arChildActivity['Activated']) || $arChildActivity['Activated'] !== 'N')
				{
					$childErrors = CBPActivity::callStaticMethod(
						$arActivity['Type'],
						'ValidateChild',
						[$arChildActivity['Type'], $bFirst]
					);

					foreach ($childErrors as $i => $e)
					{
						$childErrors[$i]['message'] = $pref . $e['message'];
						$childErrors[$i]['activityName'] = $arActivity['Name'];
					}

					if ($childErrors)
					{
						$childrenErrors[] = $childErrors;
					}

					$bFirst = false;
					$validateErrors = $this->validateTemplate($arChildActivity, $user);
					if ($validateErrors)
					{
						$childrenErrors[] = $validateErrors;
					}
				}
			}

			$errors = array_merge($errors, ...$childrenErrors);
		}

		return $errors;
	}

	protected function parseFields(&$arFields, $id = 0, $systemImport = false, $validationRequired = true)
	{
		$id = intval($id);
		$updateMode = ($id > 0 ? true : false);
		$addMode = !$updateMode;

		if ($addMode && !isset($arFields["DOCUMENT_TYPE"]))
			throw new CBPArgumentNullException("DOCUMENT_TYPE");

		if (isset($arFields["DOCUMENT_TYPE"]))
		{
			$arDocumentType = CBPHelper::ParseDocumentId($arFields["DOCUMENT_TYPE"]);

			$arFields["MODULE_ID"] = $arDocumentType[0];
			$arFields["ENTITY"] = $arDocumentType[1];
			$arFields["DOCUMENT_TYPE"] = $arDocumentType[2];
		}
		else
		{
			unset($arFields["MODULE_ID"]);
			unset($arFields["ENTITY"]);
			unset($arFields["DOCUMENT_TYPE"]);
		}

		if (isset($arFields["NAME"]) || $addMode)
		{
			$arFields["NAME"] = trim($arFields["NAME"]);
			if ($arFields["NAME"] == '')
				throw new CBPArgumentNullException("NAME");
		}

		if ($addMode && !isset($arFields['TEMPLATE']))
		{
			throw new CBPArgumentNullException('TEMPLATE');
		}

		if (array_key_exists('TEMPLATE', $arFields))
		{
			if (!is_array($arFields["TEMPLATE"]))
			{
				throw new CBPArgumentTypeException("TEMPLATE", "array");
			}
			else
			{
				$userTmp = null;

				if (!$systemImport)
				{
					if (array_key_exists("MODIFIER_USER", $arFields))
					{
						if (is_object($arFields["MODIFIER_USER"]) && is_a($arFields["MODIFIER_USER"], "CBPWorkflowTemplateUser"))
							$userTmp = $arFields["MODIFIER_USER"];
						else
							$userTmp = new CBPWorkflowTemplateUser($arFields["MODIFIER_USER"]);
					}
					else
					{
						$userTmp = new CBPWorkflowTemplateUser();
					}

					$errors = array();
					if ($validationRequired)
					{
						foreach ($arFields['TEMPLATE'] as $rawTemplate)
						{
							array_push($errors, ...$this->ValidateTemplate($rawTemplate, $userTmp));
						}
					}

					if (count($errors) > 0)
					{
						$messages = array();
						foreach ($errors as $v)
						{
							$messages[] = trim($v["message"]);
						}
						throw new CBPWorkflowTemplateValidationException(implode('.', $messages), $errors);
					}
				}
			}
		}

		$enumValues = array_column(WorkflowTemplateType::cases(), 'value');
		if (isset($arFields['TYPE']) && !in_array($arFields['TYPE'], $enumValues, true))
		{
			$arFields['TYPE'] = WorkflowTemplateType::Default->value;
		}

		if (isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] !== 'N')
			$arFields["ACTIVE"] = 'Y';

		if (isset($arFields["IS_SYSTEM"]) && $arFields["IS_SYSTEM"] !== 'Y')
			$arFields["IS_SYSTEM"] = 'N';

		if (isset($arFields["IS_MODIFIED"]) && $arFields["IS_MODIFIED"] !== 'N')
			$arFields["IS_MODIFIED"] = 'Y';

		unset($arFields["MODIFIED"]);
	}

	public static function add($fields, $systemImport = false)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		$loader->getTemplateType($fields);
		$loader->setShowInTimelineBeforeAdd($fields);
		$loader->setTemplateType($fields);

		$id = $loader->AddTemplate($fields, $systemImport);
		$loader->addTemplateSettings($id, $fields);

		return $id;
	}

	public function addTemplateSettings(int $templateId, array $templateFields): void
	{
		if (
			isset($templateFields['DOCUMENT_TYPE'])
			&& !empty($templateFields['TEMPLATE_SETTINGS'])
		)
		{
			$rows = [];
			foreach ($templateFields['TEMPLATE_SETTINGS'] as $option => $value)
			{
				$rows[] = [
					'TEMPLATE_ID' => $templateId,
					'NAME' => $option,
					'VALUE' => $value,
				];
			}

			WorkflowTemplateSettingsTable::addMultiSettings($rows);
		}
	}

	private function deleteTemplateSettings(int $templateId)
	{
		WorkflowTemplateSettingsTable::deleteSettingsByFilter(['=TEMPLATE_ID' => $templateId]);
	}

	public static function update($id, $fields, $systemImport = false, $validationRequired = true)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		$loader->getTemplateType($fields, $id);
		$loader->setShowInTimelineBeforeUpdate($fields);
		$loader->setTemplateType($fields);

		if (isset($fields['TEMPLATE']) && !$systemImport)
		{
			$fields['IS_MODIFIED'] = 'Y';
		}

		$returnId = $loader->UpdateTemplate($id, $fields, $systemImport, $validationRequired);
		$loader->updateTemplateSettings($id, $fields);
		self::cleanTemplateCache($returnId);

		return $returnId;
	}

	public function setTemplateType(array &$fields, ?string $templateType = null)
	{
		$fields['TYPE'] = $this->templateType;

		if (!empty($templateType))
		{
			$fields['TYPE'] = $templateType;
		}
	}

	private function updateTemplateSettings(int $templateId, array $templateFields)
	{
		if (
			isset($templateFields['DOCUMENT_TYPE'])
			&& !empty($templateFields['TEMPLATE_SETTINGS'])
		)
		{
			$filter = [];
			$filter['LOGIC'] = 'OR';
			foreach ($templateFields['TEMPLATE_SETTINGS'] as $option => $value)
			{
				$filter[] =  ['=NAME' => $option, '=TEMPLATE_ID' => $templateId];
			}

			$existSettings = [];
			$result = WorkflowTemplateSettingsTable::getList([
				'filter' => $filter,
				'select' => ['ID', 'NAME']
			]);
			while ($row = $result->fetch())
			{
				$existSettings[$row['NAME']] = $row['ID'];
			}

			foreach ($templateFields['TEMPLATE_SETTINGS'] as $option => $value)
			{
				if (isset($existSettings[$option]))
				{
					WorkflowTemplateSettingsTable::update($existSettings[$option], ['VALUE' => $value]);
				}
				else
				{
					WorkflowTemplateSettingsTable::add([
						'TEMPLATE_ID' => $templateId,
						'NAME' => $option,
						'VALUE' => $value,
					]);
				}
			}
		}
	}

	public function getTemplateType(array $fields, int $id = 0)
	{
		if (
			empty($fields['DOCUMENT_TYPE'])
			|| empty($fields['TEMPLATE'])
			|| !array_key_exists('AUTO_EXECUTE', $fields)
		)
		{
			if ($id > 0)
			{
				$template = self::getList(
					arFilter: ['ID' => $id],
					arSelectFields: ['DOCUMENT_TYPE', 'TEMPLATE', 'AUTO_EXECUTE']
				);
				if ($row = $template->fetch())
				{
					$fields['DOCUMENT_TYPE'] = $row['DOCUMENT_TYPE'];
					$fields['TEMPLATE'] = $row['TEMPLATE'];
					$fields['AUTO_EXECUTE'] = $row['AUTO_EXECUTE'];
				}
			}
			else
			{
				$this->templateType = WorkflowTemplateType::Default->value; // Not enough data to determine type

				return;
			}
		}

		$this->templateType = WorkflowTemplateType::Default->value;
		if ($this->isRobot((int)$fields['AUTO_EXECUTE']))
		{
			$this->templateType = WorkflowTemplateType::Robots->value;
			if ($this->isExternalModified($fields))
			{
				$this->templateType = WorkflowTemplateType::CustomRobots->value;
			}
		}
	}

	public function setShowInTimelineBeforeUpdate(array &$fields): void
	{
		$isCrm = array_key_exists('DOCUMENT_TYPE', $fields) && $fields['DOCUMENT_TYPE'][0] === 'crm';

		$customRobotToRobot =
			array_key_exists('TYPE', $fields)
			&& $fields['TYPE'] !== WorkflowTemplateType::Robots->value
			&& $this->templateType === WorkflowTemplateType::Robots->value
		;

		if ($isCrm && $customRobotToRobot)
		{
			$fields['TEMPLATE_SETTINGS']['SHOW_IN_TIMELINE'] = 'N';
		}
	}

	private function isRobot(int $autoExecute): bool
	{
		return $autoExecute === \CBPDocumentEventType::Automation || $autoExecute === \CBPDocumentEventType::Script;
	}

	public function setShowInTimelineBeforeAdd(array &$fields): void
	{
		$isCrm = array_key_exists('DOCUMENT_TYPE', $fields) && $fields['DOCUMENT_TYPE'][0] === 'crm';

		if ($isCrm && empty($fields['TEMPLATE_SETTINGS']['SHOW_IN_TIMELINE']))
		{
			$fields['TEMPLATE_SETTINGS']['SHOW_IN_TIMELINE'] = 'N';
		}
	}

	private function getSerializedForm($arTemplate)
	{
		return WorkflowTemplateTable::toSerializedForm($arTemplate);
	}

	private function getSerializedSettings($arTemplate)
	{
		return WorkflowTemplateTable::encodeJson($arTemplate);
	}

	public static function delete($id)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		$loader->DeleteTemplate($id);
		$loader->deleteTemplateSettings($id);
		self::cleanTemplateCache($id);

		\Bitrix\Bizproc\Storage\Factory::getInstance()->onAfterTemplateDelete($id);
	}

	protected static function cleanTemplateCache($id)
	{
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cache->clean(self::CONSTANTS_CACHE_TAG_PREFIX . $id);
		unset(self::$workflowConstants[$id]);
	}

	public function deleteTemplate($id)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			throw new Exception("id");
		}

		$hasInstance = (bool)WorkflowInstanceTable::getRow([
			'select' => ['ID'],
			'filter' => ['=WORKFLOW_TEMPLATE_ID' => $id],
			'order' => ['DOCUMENT_ID' => 'DESC'],
		]);

		if (!$hasInstance)
		{
			WorkflowTemplateTable::delete($id);

			$event = new Event(
				'bizproc',
				'onAfterWorkflowTemplateDelete',
				[
					'ID' => $id,
				]
			);
			EventManager::getInstance()->send($event);

			WorkflowDurationStatTable::deleteAllByTemplateId($id);
			Bitrix\Main\Config\Option::delete('bizproc', ['name' => 'tpl_track_on_' . $id]);
		}
		else
		{
			throw new CBPInvalidOperationException(GetMessage("BPCGWTL_CANT_DELETE"));
		}
	}

	public function loadWorkflow($workflowTemplateId)
	{
		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
		{
			throw new CBPArgumentOutOfRangeException("workflowTemplateId", $workflowTemplateId);
		}

		$dbTemplatesList = $this->GetTemplatesList(
			[],
			['ID' => $workflowTemplateId],
			false,
			false,
			['TEMPLATE', 'VARIABLES', 'PARAMETERS']
		);
		$arTemplatesListItem = $dbTemplatesList->Fetch();

		if (!$arTemplatesListItem)
		{
			throw new Exception(str_replace('#ID#', $workflowTemplateId, GetMessage('BPCGWTL_INVALID_WF_ID')));
		}

		$arTemplatesListItem['ID'] = $workflowTemplateId;

		return $this->loadWorkflowFromArray($arTemplatesListItem);
	}

	public function loadWorkflowFromArray($templatesListItem): array
	{
		$wfId = $templatesListItem['ID'];
		$wfTemplate = $templatesListItem['TEMPLATE'];
		$wfVariablesTypes = $templatesListItem['VARIABLES'];
		$wfParametersTypes = $templatesListItem['PARAMETERS'];

		if (!is_array($wfTemplate) || count($wfTemplate) <= 0)
		{
			throw new Exception(str_replace('#ID#', $wfId, GetMessage('BPCGWTL_EMPTY_TEMPLATE')));
		}

		$activityNames = [];
		$rootActivity = $this->parseWorkflowTemplate($wfTemplate, $activityNames);

		return [$rootActivity, $wfVariablesTypes, $wfParametersTypes];
	}

	private function parseWorkflowTemplate($arWorkflowTemplate, &$arActivityNames, CBPActivity $parentActivity = null)
	{
		if (!is_array($arWorkflowTemplate))
		{
			throw new CBPArgumentOutOfRangeException('arWorkflowTemplate');
		}

		foreach ($arWorkflowTemplate as $activityFormatted)
		{
			if (in_array($activityFormatted['Name'], $arActivityNames))
			{
				throw new Exception('DuplicateActivityName');
			}

			$arActivityNames[] = $activityFormatted['Name'];
			$activity = $this->createActivity($activityFormatted);
			if ($activity === null)
			{
				throw new Exception('Activity is not found.');
			}

			$activity->initializeFromArray($activityFormatted['Properties']);
			if ($parentActivity)
			{
				$parentActivity->fixUpParentChildRelationship($activity);
			}

			if (!empty($activityFormatted['Children']))
			{
				$this->parseWorkflowTemplate($activityFormatted['Children'], $arActivityNames, $activity);
			}
		}

		return $activity;
	}

	private function createActivity(array $activityFormatted): ?CBPActivity
	{
		$code = $activityFormatted['Type'];
		$name = $activityFormatted['Name'];
		$activated = !isset($activityFormatted['Activated']) || $activityFormatted['Activated'] === 'Y';

		if (CBPActivity::includeActivityFile($code))
		{
			$instance = CBPActivity::createInstance($code, $name);
			if ($instance)
			{
				$instance->setActivated($activated);
			}

			return $instance;
		}
		else
		{
			throw new Exception('Activity is not found.');
		}
	}

	public static function getStatesOfTemplate($arWorkflowTemplate)
	{
		if (!is_array($arWorkflowTemplate))
			throw new CBPArgumentTypeException("arWorkflowTemplate", "array");

		if (!is_array($arWorkflowTemplate[0]))
			throw new CBPArgumentTypeException("arWorkflowTemplate");

		$arStates = array();
		foreach ($arWorkflowTemplate[0]["Children"] as $state)
			$arStates[$state["Name"]] = ($state["Properties"]["Title"] <> '' ? $state["Properties"]["Title"] : $state["Name"]);

		return $arStates;
	}

	private static function findSetStateActivities($arWorkflowTemplate)
	{
		$arResult = array();

		if ($arWorkflowTemplate["Type"] == "SetStateActivity")
			$arResult[] = $arWorkflowTemplate["Properties"]["TargetStateName"];

		if (is_array($arWorkflowTemplate["Children"]))
		{
			foreach ($arWorkflowTemplate["Children"] as $key => $value)
				$arResult = $arResult + self::FindSetStateActivities($arWorkflowTemplate["Children"][$key]);
		}

		return $arResult;
	}

	public static function getTransfersOfState($arWorkflowTemplate, $stateName)
	{
		if (!is_array($arWorkflowTemplate))
			throw new CBPArgumentTypeException("arWorkflowTemplate", "array");

		if (!is_array($arWorkflowTemplate[0]))
			throw new CBPArgumentTypeException("arWorkflowTemplate");

		$stateName = trim($stateName);
		if ($stateName == '')
			throw new CBPArgumentNullException("stateName");

		$arTransfers = array();
		foreach ($arWorkflowTemplate[0]["Children"] as $state)
		{
			if ($stateName == $state["Name"])
			{
				foreach ($state["Children"] as $event)
					$arTransfers[$event["Name"]] = self::FindSetStateActivities($event);

				break;
			}
		}

		return $arTransfers;
	}

	private static function parseDocumentTypeStates($arTemplatesListItem)
	{
		$arWorkflowTemplate = $arTemplatesListItem["TEMPLATE"];
		if (!is_array($arWorkflowTemplate))
			throw new CBPArgumentTypeException("arTemplatesListItem");

		$result = array(
			"ID" => "",
			"TEMPLATE_ID" => $arTemplatesListItem["ID"],
			"TEMPLATE_NAME" => $arTemplatesListItem["NAME"],
			"TEMPLATE_DESCRIPTION" => $arTemplatesListItem["DESCRIPTION"],
			"STATE_NAME" => "",
			"STATE_TITLE" => "",
			"TEMPLATE_PARAMETERS" => $arTemplatesListItem["PARAMETERS"],
			"STATE_PARAMETERS" => array(),
			"STATE_PERMISSIONS" => array(),
			"WORKFLOW_STATUS" => -1,
		);

		$type = "CBP".$arWorkflowTemplate[0]["Type"];
		$bStateMachine = (
			$type === CBPStateMachineWorkflowActivity::class
			|| (
				class_exists($type)
				&& is_subclass_of($type, CBPStateMachineWorkflowActivity::class)
			)
		);

		if ($bStateMachine)
		{
			//if (strlen($stateName) <= 0)
			$stateName = $arWorkflowTemplate[0]["Properties"]["InitialStateName"];

			if (is_array($arWorkflowTemplate[0]["Children"]))
			{
				foreach ($arWorkflowTemplate[0]["Children"] as $state)
				{
					if ($stateName == $state["Name"])
					{
						$result["STATE_NAME"] = $stateName;
						$result["STATE_TITLE"] = $state["Properties"]["Title"];
						$result["STATE_PARAMETERS"] = array();
						$result["STATE_PERMISSIONS"] = $state["Properties"]["Permission"];

						if (is_array($state["Children"]))
						{
							foreach ($state["Children"] as $event)
							{
								if ($event["Type"] == "EventDrivenActivity")
								{
									if ($event["Children"][0]["Type"] == "HandleExternalEventActivity")
									{
										$result["STATE_PARAMETERS"][] = array(
											"NAME" => $event["Children"][0]["Name"],
											"TITLE" => $event["Children"][0]["Properties"]["Title"],
											"PERMISSION" => $event["Children"][0]["Properties"]["Permission"],
										);
									}
								}
							}
						}

						break;
					}
				}
			}
		}
		else
		{
			$result["STATE_PERMISSIONS"] = $arWorkflowTemplate[0]["Properties"]["Permission"] ?? null;
		}

		if (is_array($result["STATE_PERMISSIONS"]))
		{
			$arKeys = array_keys($result["STATE_PERMISSIONS"]);
			foreach ($arKeys as $key)
			{
				$ar = self::ExtractValuesFromVariables($result["STATE_PERMISSIONS"][$key], $arTemplatesListItem["VARIABLES"], $arTemplatesListItem["CONSTANTS"]);
				$result["STATE_PERMISSIONS"][$key] = CBPHelper::MakeArrayFlat($ar);
			}
		}

		return $result;
	}

	private static function extractValuesFromVariables($ar, $variables, $constants = array())
	{
		if (is_string($ar) && preg_match(CBPActivity::ValuePattern, $ar, $arMatches))
			$ar = array($arMatches['object'], $arMatches['field']);

		if (is_array($ar))
		{
			if (!CBPHelper::IsAssociativeArray($ar))
			{
				if (count($ar) == 2 && ($ar[0] == 'Variable' || $ar[0] == 'Constant' || $ar[0] == 'Template'))
				{
					if ($ar[0] == 'Variable' && is_array($variables) && array_key_exists($ar[1], $variables))
						return array($variables[$ar[1]]["Default"]);
					if ($ar[0] == 'Constant' && is_array($constants) && array_key_exists($ar[1], $constants))
						return array($constants[$ar[1]]["Default"]);

					return array();
				}

				$arResult = array();
				foreach ($ar as $ar1)
					$arResult[] = self::ExtractValuesFromVariables($ar1, $variables, $constants);

				return $arResult;
			}
		}

		return $ar;
	}

	public static function getDocumentTypeStates($documentType, $autoExecute = -1, $stateName = "")
	{
		$arFilter = array("DOCUMENT_TYPE" => $documentType);
		$autoExecute = intval($autoExecute);

		$cacheKey = implode('@', $documentType).'@'.$autoExecute;

		if (!isset(static::$typesStates[$cacheKey]))
		{
			$result = array();
			if ($autoExecute >= 0)
				$arFilter["AUTO_EXECUTE"] = $autoExecute;
			$arFilter["ACTIVE"] = "Y";

			$dbTemplatesList = self::GetList(
				array(),
				$arFilter,
				false,
				false,
				array('ID', 'NAME', 'DESCRIPTION', 'TEMPLATE', 'PARAMETERS', 'VARIABLES', 'CONSTANTS')
			);
			while ($arTemplatesListItem = $dbTemplatesList->Fetch())
				$result[$arTemplatesListItem["ID"]] = self::ParseDocumentTypeStates($arTemplatesListItem);

			static::$typesStates[$cacheKey] = $result;
		}
		return static::$typesStates[$cacheKey];
	}

	public static function getTemplateState($workflowTemplateId, $stateName = "")
	{
		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
			throw new CBPArgumentOutOfRangeException("workflowTemplateId", $workflowTemplateId);

		$result = null;

		$dbTemplatesList = self::GetList(
			array(),
			array('ID' => $workflowTemplateId),
			false,
			false,
			array('ID', 'NAME', 'DESCRIPTION', 'TEMPLATE', 'PARAMETERS', 'VARIABLES', 'CONSTANTS')
		);
		if ($arTemplatesListItem = $dbTemplatesList->Fetch())
			$result = self::ParseDocumentTypeStates($arTemplatesListItem);
		else
			throw new Exception(str_replace("#ID#", $workflowTemplateId, GetMessage("BPCGWTL_INVALID_WF_ID")));

		return $result;
	}

	public static function getTemplateUserId($workflowTemplateId)
	{
		$userId = 0;
		$dbTemplatesList = self::GetList(
			[],
			['ID' => (int) $workflowTemplateId], false,false, ['USER_ID']
		);
		if ($row = $dbTemplatesList->Fetch())
		{
			$userId = (int) $row['USER_ID'];
		}

		return $userId;
	}

	public static function getTemplateConstants($workflowTemplateId)
	{
		$workflowTemplateId = (int) $workflowTemplateId;
		if ($workflowTemplateId <= 0)
			throw new CBPArgumentOutOfRangeException("workflowTemplateId", $workflowTemplateId);

		if (!isset(self::$workflowConstants[$workflowTemplateId]))
		{
			$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
			$cacheTag = self::CONSTANTS_CACHE_TAG_PREFIX.$workflowTemplateId;
			if ($cache->read(3600*24*7, $cacheTag))
			{
				self::$workflowConstants[$workflowTemplateId] = (array) $cache->get($cacheTag);
			}
			else
			{
				$iterator = self::GetList(
					array(),
					array('ID' => $workflowTemplateId),
					false,
					false,
					array('CONSTANTS')
				);
				if ($row = $iterator->fetch())
				{
					self::$workflowConstants[$workflowTemplateId] = (array) $row['CONSTANTS'];
					$cache->set($cacheTag, self::$workflowConstants[$workflowTemplateId]);
				}
				else
					self::$workflowConstants[$workflowTemplateId] = array();

			}
		}

		return self::$workflowConstants[$workflowTemplateId];
	}

	/**
	 * @param $workflowTemplateId - Workflow Template ID
	 * @return bool
	 * @throws CBPArgumentOutOfRangeException
	 */
	public static function isConstantsTuned($workflowTemplateId)
	{
		$result = true;
		$constants = self::getTemplateConstants($workflowTemplateId);
		if (!empty($constants) && is_array($constants))
		{
			foreach ($constants as $key => $const)
			{
				$value = isset($const['Default']) ? $const['Default'] : null;
				if (CBPHelper::getBool($const['Required']) && CBPHelper::isEmptyValue($value))
				{
					$result = false;
					break;
				}
			}
		}
		return $result;
	}

	public static function checkWorkflowParameters($arTemplateParameters, $arPossibleValues, $documentType, &$arErrors)
	{
		$arErrors = [];
		$arWorkflowParameters = [];

		if (count($arTemplateParameters) <= 0)
		{
			return [];
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		foreach ($arTemplateParameters as $parameterKey => $arParameter)
		{
			$arErrorsTmp = [];

			$arWorkflowParameters[$parameterKey] = $documentService->GetFieldInputValue(
				$documentType,
				$arParameter,
				$parameterKey,
				$arPossibleValues,
				$arErrorsTmp
			);

			if (
				CBPHelper::getBool($arParameter['Required'] ?? null)
				&& CBPHelper::isEmptyValue($arWorkflowParameters[$parameterKey] ?? null)
			)
			{
				$arErrorsTmp[] = [
					'code' => 'RequiredValue',
					'message' => Loc::getMessage('BPCGWTL_INVALID8', ['#NAME#' => $arParameter['Name']]),
					'parameter' => $parameterKey,
				];
			}

			if ($arErrorsTmp)
			{
				$arErrors[] = $arErrorsTmp;
			}
		}

		if ($arErrors)
		{
			$arErrors = array_merge(...$arErrors);
		}

		return $arWorkflowParameters;
	}

	public static function searchTemplatesByDocumentType($documentType, $autoExecute = -1)
	{
		$result = [];

		$arFilter = ['DOCUMENT_TYPE' => $documentType];
		$autoExecute = intval($autoExecute);
		if ($autoExecute >= 0)
		{
			$arFilter['AUTO_EXECUTE'] = $autoExecute;
		}

		$dbTemplatesList = self::GetList(
			[],
			$arFilter,
			false,
			false,
			['ID', 'NAME', 'DESCRIPTION', 'AUTO_EXECUTE']
		);
		while ($arTemplatesListItem = $dbTemplatesList->Fetch())
		{
			$result[] = [
				'ID' => $arTemplatesListItem['ID'],
				'NAME' => $arTemplatesListItem['NAME'],
				'DESCRIPTION' => $arTemplatesListItem['DESCRIPTION'],
				'AUTO_EXECUTE' => $arTemplatesListItem['AUTO_EXECUTE'],
			];
		}

		return $result;
	}

	public static function &FindActivityByName(&$arWorkflowTemplate, $activityName)
	{
		$res = null;

		if (!$activityName)
		{
			return $res;
		}

		foreach ($arWorkflowTemplate as $key => $value)
		{
			$valueName = $value['Name'] ?? null;
			if ($valueName == $activityName)
			{
				return $arWorkflowTemplate[$key];
			}

			if (is_array($value["Children"] ?? null))
			{
				if ($res = &self::FindActivityByName($arWorkflowTemplate[$key]["Children"], $activityName))
				{
					return $res;
				}
			}
		}

		return $res;
	}

	public static function &FindParentActivityByName(&$arWorkflowTemplate, $activityName)
	{
		foreach ($arWorkflowTemplate as $key => $value)
		{
			if (is_array($value["Children"]))
			{
				for ($i = 0, $s = sizeof($value['Children']); $i < $s; $i++)
				{
					if ($value["Children"][$i]["Name"] == $activityName)
						return $arWorkflowTemplate[$key];
				}

				if ($res = &self::FindParentActivityByName($arWorkflowTemplate[$key]["Children"], $activityName))
					return $res;
			}
		}
		return null;
	}

	public static function exportTemplate($id, $bCompress = true)
	{
		$tpl = WorkflowTemplateTable::getById($id)->fetchObject();
		if (!$tpl)
		{
			return false;
		}

		$packer = new \Bitrix\Bizproc\Workflow\Template\Packer\Bpt();
		if (!$bCompress)
		{
			$packer->disableCompression();
		}

		return $packer->pack($tpl)->getPackage();
	}

	private static function walkThroughWorkflowTemplate(&$arWorkflowTemplate, $callback, $user)
	{
		foreach ($arWorkflowTemplate as $key => $value)
		{
			if (!call_user_func_array($callback, array($value, $user)))
				return false;

			if (isset($value['Children']) && is_array($value['Children']))
			{
				if (
					!self::WalkThroughWorkflowTemplate(
						$arWorkflowTemplate[$key]['Children'],
						$callback,
						$user
					)
				)
				{
					return false;
				}
			}
		}
		return true;
	}

	private static function importTemplateChecker($arActivity, $user)
	{
		$arErrors = CBPActivity::CallStaticMethod($arActivity["Type"], "ValidateProperties", array($arActivity["Properties"], $user));
		if (count($arErrors) > 0)
		{
			$m = "";
			foreach ($arErrors as $er)
				$m .= $er["message"].". ";

			throw new Exception($m);

			return false;
		}

		return true;
	}

	public static function importTemplate($id, $documentType, $autoExecute, $name, $description, $datum, $systemCode = null, $systemImport = false)
	{

		$packer = new \Bitrix\Bizproc\Workflow\Template\Packer\Bpt();
		$unpackResult = $packer->unpack($datum);

		if (!$unpackResult->isSuccess())
		{
			throw new \Bitrix\Main\ArgumentException(reset($unpackResult->getErrorMessages()));
		}

		$templateFields = $unpackResult->getTpl()->collectValues();
		$templateFields['DOCUMENT_FIELDS'] = $unpackResult->getDocumentFields();

		return self::importTemplateFromArray($id, $documentType, $autoExecute, $name, $description, $templateFields, $systemCode, $systemImport);
	}

	public static function importTemplateFromArray($id, $documentType, $autoExecute, $name, $description, $templateFields, $systemCode = null, $systemImport = false)
	{
		$id = intval($id);
		if ($id <= 0)
			$id = 0;

		if (!$systemImport)
		{
			if (!self::WalkThroughWorkflowTemplate($templateFields["TEMPLATE"], array("CBPWorkflowTemplateLoader", "ImportTemplateChecker"), new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)))
				return false;
		}
		elseif ($id > 0 && !empty($templateFields["CONSTANTS"]))
		{
			$userConstants = self::getTemplateConstants($id);
			if (!empty($userConstants))
			{
				foreach ($userConstants as $constantName => $constantData)
				{
					if (isset($templateFields["CONSTANTS"][$constantName]))
					{
						$templateFields["CONSTANTS"][$constantName]['Default'] = $constantData['Default'];
					}
				}
			}
		}

		$templateData = array(
			"DOCUMENT_TYPE" => $documentType,
			"AUTO_EXECUTE" => $autoExecute,
			"NAME" => $name,
			"DESCRIPTION" => $description,
			"TEMPLATE" => $templateFields["TEMPLATE"],
			"PARAMETERS" => $templateFields["PARAMETERS"],
			"VARIABLES" => $templateFields["VARIABLES"],
			"CONSTANTS" => $templateFields["CONSTANTS"],
			"USER_ID" => $systemImport ? 1 : $GLOBALS["USER"]->GetID(),
			"MODIFIER_USER" => new CBPWorkflowTemplateUser($systemImport ? 1 : CBPWorkflowTemplateUser::CurrentUser),
		);
		if (!is_null($systemCode))
			$templateData["SYSTEM_CODE"] = $systemCode;
		if ($id <= 0)
			$templateData['ACTIVE'] = 'Y';

		if ($id > 0)
			self::Update($id, $templateData, $systemImport);
		else
			$id = self::Add($templateData, $systemImport);

		if ($templateFields['DOCUMENT_FIELDS'] && is_array($templateFields['DOCUMENT_FIELDS']))
		{
			static::importDocumentFields($documentType, $templateFields['DOCUMENT_FIELDS']);
		}

		return $id;
	}

	public static function importDocumentFields(array $documentType, array $fields)
	{
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$currentDocumentFields = $documentService->GetDocumentFields($documentType, true);

		\Bitrix\Main\Type\Collection::sortByColumn($fields, "sort");
		$len = mb_strlen("_PRINTABLE");

		foreach ($fields as $code => $field)
		{
			//skip printable
			if (mb_strtoupper(mb_substr($code, -$len)) == "_PRINTABLE")
			{
				continue;
			}

			//skip references
			if (mb_strpos($code, '.') !== false)
			{
				continue;
			}

			$documentField = [
				"name" => $field["Name"],
				"code" => $code,
				"type" => $field["Type"],
				"multiple" => $field["Multiple"] ?? null,
				"required" => $field["Required"] ?? null,
			];

			if (isset($field['Options']) && is_array($field["Options"]) && count($field["Options"]) > 0)
			{
				$documentField['options'] = '';
				foreach ($field["Options"] as $k => $v)
				{
					if (!is_scalar($v))
					{
						continue;
					}

					$documentField["options"] .= "[".$k."]".$v."\n";
				}
			}

			unset($field["Name"], $field["Type"], $field["Multiple"], $field["Required"], $field["Options"]);
			$documentField = array_merge($documentField, $field);

			if ($currentDocumentFields && !array_key_exists($code, $currentDocumentFields))
			{
				$documentService->AddDocumentField($documentType, $documentField);
			}
			else
			{
				$documentService->UpdateDocumentField($documentType, $documentField);
			}
		}
	}

	public function getTemplatesList(
		array $order = ['ID' => 'DESC'],
		array $filter = [],
		$group = false,
		$navStartParams = false,
		array $select = []
	)
	{
		$this->prepareTemplatesSelect($select);
		$this->prepareTemplatesFilter($filter);

		if (is_array($group) && empty($group))
		{
			$countQuery =
				WorkflowTemplateTable::query()
					->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
					->setFilter($filter);

			$countResult = $countQuery->fetch();

			return $countResult ? $countResult['CNT'] : false;
		}

		$query = WorkflowTemplateTable::query()
			->setSelect($select)
			->setFilter($filter)
			->setOrder($order)
		;

		if (is_array($group) && !empty($group))
		{
			$query->setSelect($group);
			$query->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'));
			$query->setGroup($group);
		}

		$topCount = 0;
		$hasNavStartParams = (is_array($navStartParams) && !empty($navStartParams));
		if ($hasNavStartParams && isset($navStartParams['nTopCount']))
		{
			$topCount = (int)$navStartParams['nTopCount'];
		}

		if ($hasNavStartParams && $topCount > 0)
		{
			$query->setLimit($topCount);
		}
		elseif ($hasNavStartParams && isset($navStartParams['iNumPage']) && isset($navStartParams['nPageSize']))
		{
			$query->setOffset(((int)$navStartParams['iNumPage'] - 1) * (int)$navStartParams['nPageSize']);
			$query->setLimit((int)$navStartParams['nPageSize']);
		}

		$result = $query->exec();

		return new CBPWorkflowTemplateResult($result, $this->useGZipCompression);
	}

	protected function prepareTemplatesFilter(array &$filter): void
	{
		if (array_key_exists('DOCUMENT_TYPE', $filter) && is_array($filter['DOCUMENT_TYPE']))
		{
			[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($filter['DOCUMENT_TYPE']);
			$filter['=MODULE_ID'] = $moduleId;
			$filter['=ENTITY'] = $entity;
			$filter['=DOCUMENT_TYPE'] = $documentId;

			unset($filter['DOCUMENT_TYPE']);
		}

		$entity = WorkflowTemplateTable::getEntity();
		$strictMatchFields = [];

		foreach ($entity->getFields() as $field)
		{
			if ($field->getDataType() === 'string' || $field->getDataType() === 'boolean')
			{
				$strictMatchFields[] = $field->getName();
			}
		}

		foreach ($strictMatchFields as $fieldName)
		{
			if (isset($filter[$fieldName]) && is_string($filter[$fieldName]))
			{
				$filter['=' . $fieldName] = $filter[$fieldName];
				unset($filter[$fieldName]);
			}
		}

		if (array_key_exists('AUTO_EXECUTE', $filter))
		{
			$filter['AUTO_EXECUTE'] = match ((int)$filter['AUTO_EXECUTE'])
			{
				CBPDocumentEventType::None => 0,
				CBPDocumentEventType::Create => [1, 3, 5, 7],
				CBPDocumentEventType::Edit => [2, 3, 6, 7],
				CBPDocumentEventType::Delete => [4, 5, 6, 7],
				CBPDocumentEventType::Automation => 8,
				CBPDocumentEventType::Script => 32,
				default => [-1],
			};
		}
	}

	protected function prepareTemplatesSelect(array &$select): void
	{
		if (empty($select))
		{
			$select = [
				'ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'DOCUMENT_STATUS', 'AUTO_EXECUTE',
				'NAME', 'DESCRIPTION', 'TEMPLATE', 'PARAMETERS', 'VARIABLES', 'CONSTANTS',
				'MODIFIED', 'USER_ID', 'ACTIVE', 'IS_MODIFIED', 'IS_SYSTEM', 'SORT', 'TYPE'
			];
		}

		if ($select === ['*'])
		{
			$select = array_merge($select, ['USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN']);
		}

		if (count(array_intersect($select, ['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'])) > 0)
		{
			foreach (['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'] as $field)
			{
				if (!in_array($field, $select, true))
				{
					$select[] = $field;
				}
			}
		}

		$userFields = ['USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN'];
		if (array_intersect($select, $userFields))
		{
			foreach ($userFields as $userField)
			{
				if (in_array($userField, $select, true))
				{
					$select[$userField] = str_replace('USER_', 'USER.', $userField);

					$index = array_search($userField, $select, true);
					unset($select[$index]);
				}
			}
		}
	}

	/**
	 * @param array $fields
	 * @param bool $isSystemImport
	 * @return int
	 * @throws CBPArgumentNullException
	 * @throws CBPArgumentTypeException
	 * @throws CBPWorkflowTemplateValidationException
	 */
	public function addTemplate(array $fields, bool $isSystemImport = false): int
	{
		self::ParseFields($fields, 0, $isSystemImport);

		unset($fields['ID']);

		$tplFields = $this->prepareTplFields($fields);
		$result = WorkflowTemplateTable::add($tplFields);

		$id = 0;
		if ($result->isSuccess())
		{
			$id = $result->getId();

			$event = new Event(
				'bizproc',
				'onAfterWorkflowTemplateAdd',
				[
					'FIELDS' => $fields,
				]
			);
			EventManager::getInstance()->send($event);

			return $id;
		}

		return $id;
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @param bool $systemImport
	 * @param bool $validationRequired
	 * @return int
	 * @throws CBPArgumentNullException
	 * @throws CBPArgumentTypeException
	 * @throws CBPWorkflowTemplateValidationException
	 */
	public function updateTemplate($id, array $fields, bool $systemImport = false, bool $validationRequired = true): int
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			throw new CBPArgumentNullException('id');
		}

		self::ParseFields($fields, $id, $systemImport, $validationRequired);

		$tplFields = $this->prepareTplFields($fields);
		$result = WorkflowTemplateTable::update($id, $tplFields);

		if ($result->isSuccess())
		{
			$event = new Event(
				'bizproc',
				'onAfterWorkflowTemplateUpdate',
				[
					'ID' => $id,
					'FIELDS' => $fields,
				]
			);
			EventManager::getInstance()->send($event);

			return $id;
		}

		return $id;
	}

	private function prepareTplFields(array $fields): array
	{
		$fields['MODIFIED'] = new \Bitrix\Main\Type\DateTime();
		unset($fields['TEMPLATE_SETTINGS']);

		return $fields;
	}

	public static function useGZipCompression()
	{
		$useGZipCompressionOption = \Bitrix\Main\Config\Option::get("bizproc", "use_gzip_compression", "");
		if ($useGZipCompressionOption === "Y")
		{
			$result = true;
		}
		elseif ($useGZipCompressionOption === "N")
		{
			$result = false;
		}
		else
		{
			$result = function_exists("gzcompress");
		}

		return $result;
	}

	public static function getCompressedFieldLength($field): false|int
	{
		if (self::useGZipCompression())
		{
			return mb_strlen(gzcompress(serialize($field), 9));
		}

		return mb_strlen(serialize($field));
	}

	private function prepareFieldsForTemplate(array $fields): array
	{
		unset($fields['MODIFIER_USER']);
		[$moduleId, $entity, $documentType] = $fields['DOCUMENT_TYPE'];
		$fields['MODULE_ID'] = $moduleId;
		$fields['ENTITY'] = $entity;
		$fields['DOCUMENT_TYPE'] = $documentType;

		return $fields;
	}

	public static function prepareDocumentType(array &$fields)
	{
		if (array_key_exists('DOCUMENT_TYPE', $fields) && !is_array($fields['DOCUMENT_TYPE']))
		{
			$fields['DOCUMENT_TYPE'] = [
				$fields['MODULE_ID'],
				$fields['ENTITY'],
				$fields['DOCUMENT_TYPE']
			];
		}
	}

	public static function prepareSettingsCollection(array &$fields)
	{
		if (array_key_exists('TEMPLATE_SETTINGS', $fields))
		{
			$newValues = [];
			$settingsValues = $fields['TEMPLATE_SETTINGS']?->getAll();

			if (!empty($settingsValues))
			{
				foreach ($settingsValues as $setting)
				{
					$newValues[$setting->getName()] = $setting->getValue();
				}
			}

			$fields['TEMPLATE_SETTINGS'] = $newValues;
		}
	}

	private function isExternalModified(array $fields): ?bool
	{
		$template = new \Bitrix\Bizproc\Automation\Engine\Template($fields['DOCUMENT_TYPE']);
		$template->setTemplate($fields['TEMPLATE']);
		$template->setExecuteType($fields['AUTO_EXECUTE']);

		return $template->isExternalModified();
	}
}

class CBPWorkflowTemplateResult extends CDBResult
{
	private $useGZipCompression = false;

	public function __construct($res, $useGZipCompression)
	{
		$this->useGZipCompression = $useGZipCompression;
		parent::__construct($res);
	}

	private function getFromSerializedForm($value)
	{
		return WorkflowTemplateTable::getFromSerializedForm($value);
	}

	private function getFromSerializedSettings($value)
	{
		return WorkflowTemplateTable::decodeJson($value);
	}

	function fetch()
	{
		$res = parent::Fetch();

		if ($res)
		{
			if (array_key_exists("DOCUMENT_TYPE", $res) && !is_array($res["DOCUMENT_TYPE"]))
			{
				$res["DOCUMENT_TYPE"] = array($res["MODULE_ID"], $res["ENTITY"], $res["DOCUMENT_TYPE"]);
			}

			if (array_key_exists("TEMPLATE", $res) && !is_array($res["TEMPLATE"]))
			{
				$res["TEMPLATE"] = $this->GetFromSerializedForm($res["TEMPLATE"]);
			}

			if (array_key_exists("VARIABLES", $res) && !is_array($res["VARIABLES"]))
			{
				$res["VARIABLES"] = $this->GetFromSerializedForm($res["VARIABLES"]);
			}

			if (array_key_exists("CONSTANTS", $res) && !is_array($res["CONSTANTS"]))
			{
				$res["CONSTANTS"] = $this->GetFromSerializedForm($res["CONSTANTS"]);
			}

			if (array_key_exists("PARAMETERS", $res) && !is_array($res["PARAMETERS"]))
			{
				$res["PARAMETERS"] = $this->GetFromSerializedForm($res["PARAMETERS"]);
			}

			if (array_key_exists('TEMPLATE_SETTINGS', $res) && !is_array($res['TEMPLATE_SETTINGS']))
			{
				$res['TEMPLATE_SETTINGS'] = $this->getFromSerializedSettings($res['TEMPLATE_SETTINGS']);
			}

			if (array_key_exists('MODIFIED', $res) && $res['MODIFIED'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$res['MODIFIED'] =
					$res['MODIFIED']->toString(new \Bitrix\Main\Context\Culture(['FORMAT_DATETIME' => FORMAT_DATETIME]))
				;
			}
		}

		return $res;
	}
}

class CBPWorkflowTemplateUser
{
	const CurrentUser = "CurrentUser";

	private $userId = 0;
	private $isAdmin = false;
	private $fullName = '';

	public function __construct($userId = null)
	{
		$this->userId = 0;
		$this->isAdmin = false;
		$this->fullName = '';

		if (is_int($userId))
		{
			$userGroups = CUser::GetUserGroup($userId);
			$this->userId = (int)$userId;
			$this->isAdmin = in_array(1, $userGroups);
		}
		elseif ($userId === self::CurrentUser)
		{
			global $USER;
			if (is_object($USER) && $USER->IsAuthorized())
			{
				$this->userId = (int)$USER->GetID();
				$this->isAdmin = (
					$USER->IsAdmin()
					|| CModule::IncludeModule('bitrix24') && CBitrix24::IsPortalAdmin($USER->GetID())
				);
				$this->fullName = $USER->GetFullName();
			}
		}
	}

	public function getId()
	{
		return $this->userId;
	}

	public function getBizprocId()
	{
		return $this->userId > 0 ? 'user_'.$this->userId : null;
	}

	public function isAdmin()
	{
		return ($this->isAdmin || self::isBpAdmin($this->userId));
	}

	public function getFullName()
	{
		return $this->fullName;
	}

	private static function isBpAdmin(int $userId): bool
	{
		static $ids;
		if ($ids === null)
		{
			$idsString = \Bitrix\Main\Config\Option::get('bizproc', 'wtu_admins');
			$ids = array_map('intval', explode(',', $idsString));
		}
		return $userId && in_array($userId, $ids, true);
	}
}

class CBPWorkflowTemplateValidationException extends Exception
{
	private $errors;
	public function __construct($message = "", array $errors = array())
	{
		parent::__construct($message, 10010);
		$this->errors = $errors;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
