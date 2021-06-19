<?
IncludeModuleLangFile(__FILE__);

define("BP_EI_DIRECTION_EXPORT", 0);
define("BP_EI_DIRECTION_IMPORT", 1);

/**
* Workflow templates service.
*/
class CAllBPWorkflowTemplateLoader
{
	protected $useGZipCompression = false;
	protected static $workflowConstants = array();
	const CONSTANTS_CACHE_TAG_PREFIX = 'b_bp_wf_constants_';
	protected static $typesStates = array();

	private static $instance;

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
	public static function GetLoader()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public static function GetList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
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

	private function ValidateTemplate($arActivity, $user)
	{
		$errors = CBPActivity::CallStaticMethod(
			$arActivity["Type"],
			"ValidateProperties",
			array($arActivity["Properties"], $user)
		);

		$pref = '';
		if (isset($arActivity["Properties"]) && isset($arActivity["Properties"]["Title"]))
		{
			$pref = str_replace("#TITLE#", $arActivity["Properties"]["Title"], GetMessage("BPWTL_ERROR_MESSAGE_PREFIX"))." ";
		}

		foreach ($errors as $i => $e)
		{
			$errors[$i]["message"] = $pref.$e["message"];
			$errors[$i]["activityName"] = $arActivity['Name'];
		}

		if (array_key_exists("Children", $arActivity) && count($arActivity["Children"]) > 0)
		{
			$bFirst = true;
			foreach ($arActivity["Children"] as $arChildActivity)
			{
				$childErrors = CBPActivity::CallStaticMethod(
					$arActivity["Type"],
					"ValidateChild",
					array($arChildActivity["Type"], $bFirst)
				);
				foreach ($childErrors as $i => $e)
				{
					$childErrors[$i]["message"] = $pref.$e["message"];
					$childErrors[$i]["activityName"] = $arActivity['Name'];
				}
				$errors = array_merge($errors, $childErrors);

				$bFirst = false;
				$errors = array_merge($errors, $this->ValidateTemplate($arChildActivity, $user));
			}
		}

		return $errors;
	}

	protected function ParseFields(&$arFields, $id = 0, $systemImport = false)
	{
		$id = intval($id);
		$updateMode = ($id > 0 ? true : false);
		$addMode = !$updateMode;

		if ($addMode && !is_set($arFields, "DOCUMENT_TYPE"))
			throw new CBPArgumentNullException("DOCUMENT_TYPE");

		if (is_set($arFields, "DOCUMENT_TYPE"))
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

		if (is_set($arFields, "NAME") || $addMode)
		{
			$arFields["NAME"] = trim($arFields["NAME"]);
			if ($arFields["NAME"] == '')
				throw new CBPArgumentNullException("NAME");
		}

		if ($addMode && !is_set($arFields, "TEMPLATE"))
			throw new CBPArgumentNullException("TEMPLATE");

		if (is_set($arFields, "TEMPLATE"))
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
					foreach ($arFields["TEMPLATE"] as $v)
						$errors = array_merge($errors, $this->ValidateTemplate($v, $userTmp));

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

				$arFields["TEMPLATE"] = $this->GetSerializedForm($arFields["TEMPLATE"]);
			}
		}

		foreach (array('PARAMETERS', 'VARIABLES', 'CONSTANTS') as $field)
		{
			if (is_set($arFields, $field))
			{
				if ($arFields[$field] == null)
				{
					$arFields[$field] = false;
				}
				elseif (is_array($arFields[$field]))
				{
					if (count($arFields[$field]) > 0)
						$arFields[$field] = $this->GetSerializedForm($arFields[$field]);
					else
						$arFields[$field] = false;
				}
				else
				{
					throw new CBPArgumentTypeException($field);
				}
			}
		}

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != 'N')
			$arFields["ACTIVE"] = 'Y';

		if(is_set($arFields, "IS_SYSTEM") && $arFields["IS_SYSTEM"] != 'Y')
			$arFields["IS_SYSTEM"] = 'N';

		if(is_set($arFields, "IS_MODIFIED") && $arFields["IS_MODIFIED"] != 'N')
			$arFields["IS_MODIFIED"] = 'Y';

		unset($arFields["MODIFIED"]);
	}

	public static function Add($arFields, $systemImport = false)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		return $loader->AddTemplate($arFields, $systemImport);
	}

	public static function Update($id, $arFields, $systemImport = false)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		if (isset($arFields['TEMPLATE']) && !$systemImport)
			$arFields['IS_MODIFIED'] = 'Y';
		$returnId = $loader->UpdateTemplate($id, $arFields, $systemImport);
		self::cleanTemplateCache($returnId);
		return $returnId;
	}

	private function GetSerializedForm($arTemplate)
	{
		$buffer = serialize($arTemplate);
		if ($this->useGZipCompression)
			$buffer = gzcompress($buffer, 9);
		return $buffer;
	}

	public static function Delete($id)
	{
		$loader = CBPWorkflowTemplateLoader::GetLoader();
		$loader->DeleteTemplate($id);
		self::cleanTemplateCache($id);
	}

	protected static function cleanTemplateCache($id)
	{
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cache->clean(self::CONSTANTS_CACHE_TAG_PREFIX.$id);
	}

	public function DeleteTemplate($id)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new Exception("id");

		$dbResult = $DB->Query(
			"SELECT COUNT('x') as CNT ".
			"FROM b_bp_workflow_instance WI ".
			"WHERE WI.WORKFLOW_TEMPLATE_ID = ".intval($id)." "
		);

		if ($arResult = $dbResult->Fetch())
		{
			$cnt = intval($arResult["CNT"]);

			if ($cnt <= 0)
			{
				$DB->Query(
					"DELETE FROM b_bp_workflow_template ".
					"WHERE ID = ".intval($id)." "
				);
			}
			else
			{
				throw new CBPInvalidOperationException(GetMessage("BPCGWTL_CANT_DELETE"));
			}
		}
		else
		{
			throw new Exception(GetMessage("BPCGWTL_UNKNOWN_ERROR"));
		}
	}

	public function LoadWorkflow($workflowTemplateId)
	{
		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
			throw new CBPArgumentOutOfRangeException("workflowTemplateId", $workflowTemplateId);

		$dbTemplatesList = $this->GetTemplatesList(array(), array("ID" => $workflowTemplateId), false, false, array("TEMPLATE", "VARIABLES", "PARAMETERS"));
		$arTemplatesListItem = $dbTemplatesList->Fetch();

		if (!$arTemplatesListItem)
			throw new Exception(str_replace("#ID#", $workflowTemplateId, GetMessage("BPCGWTL_INVALID_WF_ID")));

		$arWorkflowTemplate = $arTemplatesListItem["TEMPLATE"];
		$workflowVariablesTypes = $arTemplatesListItem["VARIABLES"];
		$workflowParametersTypes = $arTemplatesListItem["PARAMETERS"];

		if (!is_array($arWorkflowTemplate) || count($arWorkflowTemplate) <= 0)
			throw new Exception(str_replace("#ID#", $workflowTemplateId, GetMessage("BPCGWTL_EMPTY_TEMPLATE")));

		$arActivityNames = array();
		$rootActivity = $this->ParceWorkflowTemplate($arWorkflowTemplate, $arActivityNames, null);

		return array($rootActivity, $workflowVariablesTypes, $workflowParametersTypes);
	}

	private function ParceWorkflowTemplate($arWorkflowTemplate, &$arActivityNames, CBPActivity $parentActivity = null)
	{
		if (!is_array($arWorkflowTemplate))
			throw new CBPArgumentOutOfRangeException("arWorkflowTemplate");

		foreach ($arWorkflowTemplate as $activityFormatted)
		{
			if (in_array($activityFormatted["Name"], $arActivityNames))
				throw new Exception("DuplicateActivityName");

			$arActivityNames[] = $activityFormatted["Name"];
			$activity = $this->CreateActivity($activityFormatted["Type"], $activityFormatted["Name"]);
			if ($activity == null)
				throw new Exception("Activity is not found.");

			$activity->InitializeFromArray($activityFormatted["Properties"]);
			if ($parentActivity)
				$parentActivity->FixUpParentChildRelationship($activity);

			if ($activityFormatted["Children"])
				$this->ParceWorkflowTemplate($activityFormatted["Children"], $arActivityNames, $activity);
		}

		return $activity;
	}

	private function CreateActivity($activityCode, $activityName)
	{
		if (CBPActivity::IncludeActivityFile($activityCode))
			return CBPActivity::CreateInstance($activityCode, $activityName);
		else
			throw new Exception('Activity is not found.');
	}

	public static function GetStatesOfTemplate($arWorkflowTemplate)
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

	private static function FindSetStateActivities($arWorkflowTemplate)
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

	public static function GetTransfersOfState($arWorkflowTemplate, $stateName)
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

	private static function ParseDocumentTypeStates($arTemplatesListItem)
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
			$result["STATE_PERMISSIONS"] = $arWorkflowTemplate[0]["Properties"]["Permission"];
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

	private static function ExtractValuesFromVariables($ar, $variables, $constants = array())
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

	public static function GetDocumentTypeStates($documentType, $autoExecute = -1, $stateName = "")
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

	public static function GetTemplateState($workflowTemplateId, $stateName = "")
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

	public static function CheckWorkflowParameters($arTemplateParameters, $arPossibleValues, $documentType, &$arErrors)
	{
		$arErrors = array();
		$arWorkflowParameters = array();

		if (count($arTemplateParameters) <= 0)
			return array();

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		foreach ($arTemplateParameters as $parameterKey => $arParameter)
		{
			$arErrorsTmp = array();

			$arWorkflowParameters[$parameterKey] = $documentService->GetFieldInputValue(
				$documentType,
				$arParameter,
				$parameterKey,
				$arPossibleValues,
				$arErrorsTmp
			);

			if (CBPHelper::getBool($arParameter['Required']) && CBPHelper::isEmptyValue($arWorkflowParameters[$parameterKey]))
			{
				$arErrorsTmp[] = array(
					"code" => "RequiredValue",
					"message" => str_replace("#NAME#", $arParameter["Name"], GetMessage("BPCGWTL_INVALID8")),
					"parameter" => $parameterKey,
				);
			}

			$arErrors = array_merge($arErrors, $arErrorsTmp);
		}

		return $arWorkflowParameters;
	}

	public static function SearchTemplatesByDocumentType($documentType, $autoExecute = -1)
	{
		$result = array();

		$arFilter = array("DOCUMENT_TYPE" => $documentType);
		$autoExecute = intval($autoExecute);
		if ($autoExecute >= 0)
			$arFilter["AUTO_EXECUTE"] = $autoExecute;

		$dbTemplatesList = self::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "DESCRIPTION", "AUTO_EXECUTE")
		);
		while ($arTemplatesListItem = $dbTemplatesList->Fetch())
		{
			$result[] = array(
				"ID" => $arTemplatesListItem["ID"],
				"NAME" => $arTemplatesListItem["NAME"],
				"DESCRIPTION" => $arTemplatesListItem["DESCRIPTION"],
				"AUTO_EXECUTE" => $arTemplatesListItem["AUTO_EXECUTE"],
			);
		}

		return $result;
	}

	public static function &FindActivityByName(&$arWorkflowTemplate, $activityName)
	{
		foreach ($arWorkflowTemplate as $key => $value)
		{
			if ($value["Name"] == $activityName)
				return $arWorkflowTemplate[$key];

			if (is_array($value["Children"]))
			{
				if ($res = &self::FindActivityByName($arWorkflowTemplate[$key]["Children"], $activityName))
					return $res;
			}
		}
		return null;
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

	public static function ExportTemplate($id, $bCompress = true)
	{
		$tpl = \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::getById($id)->fetchObject();
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

	private static function WalkThroughWorkflowTemplate(&$arWorkflowTemplate, $callback, $user)
	{
		foreach ($arWorkflowTemplate as $key => $value)
		{
			if (!call_user_func_array($callback, array($value, $user)))
				return false;

			if (is_array($value["Children"]))
			{
				if (!self::WalkThroughWorkflowTemplate($arWorkflowTemplate[$key]["Children"], $callback, $user))
					return false;
			}
		}
		return true;
	}

	private static function ImportTemplateChecker($arActivity, $user)
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

	public static function ImportTemplate($id, $documentType, $autoExecute, $name, $description, $datum, $systemCode = null, $systemImport = false)
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

		if ($templateFields['DOCUMENT_FIELDS'])
		{
			$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
			$currentDocumentFields = $documentService->GetDocumentFields($documentType, true);

			\Bitrix\Main\Type\Collection::sortByColumn($templateFields['DOCUMENT_FIELDS'], "sort");
			$len = mb_strlen("_PRINTABLE");

			foreach ($templateFields['DOCUMENT_FIELDS'] as $code => $field)
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

				$documentField = array(
					"name" => $field["Name"],
					"code" => $code,
					"type" => $field["Type"],
					"multiple" => $field["Multiple"],
					"required" => $field["Required"],
				);

				if (is_array($field["Options"]) && count($field["Options"]) > 0)
				{
					foreach ($field["Options"] as $k => $v)
						$documentField["options"] .= "[".$k."]".$v."\n";
				}

				unset($field["Name"], $field["Type"], $field["Multiple"], $field["Required"], $field["Options"]);
				$documentField = array_merge($documentField, $field);

				if (!array_key_exists($code, $currentDocumentFields))
				{
					$documentService->AddDocumentField($documentType, $documentField);
				}
				else
				{
					$documentService->UpdateDocumentField($documentType, $documentField);
				}
			}
		}

		return $id;
	}

	public function GetTemplatesList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = ["ID", "MODULE_ID", "ENTITY", "DOCUMENT_TYPE", "DOCUMENT_STATUS", "AUTO_EXECUTE",
				"NAME", "DESCRIPTION", "TEMPLATE", "PARAMETERS", "VARIABLES", "CONSTANTS", "MODIFIED", "USER_ID",
				"ACTIVE", "IS_MODIFIED", "IS_SYSTEM", 'SORT'];
		}

		if (count(array_intersect($arSelectFields, array("MODULE_ID", "ENTITY", "DOCUMENT_TYPE"))) > 0)
		{
			if (!in_array("MODULE_ID", $arSelectFields))
				$arSelectFields[] = "MODULE_ID";
			if (!in_array("ENTITY", $arSelectFields))
				$arSelectFields[] = "ENTITY";
			if (!in_array("DOCUMENT_TYPE", $arSelectFields))
				$arSelectFields[] = "DOCUMENT_TYPE";
		}

		if (array_key_exists("DOCUMENT_TYPE", $arFilter))
		{
			$d = CBPHelper::ParseDocumentId($arFilter["DOCUMENT_TYPE"]);
			$arFilter["MODULE_ID"] = $d[0];
			$arFilter["ENTITY"] = $d[1];
			$arFilter["DOCUMENT_TYPE"] = $d[2];
		}

		if (array_key_exists("AUTO_EXECUTE", $arFilter))
		{
			$arFilter["AUTO_EXECUTE"] = intval($arFilter["AUTO_EXECUTE"]);

			if ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::None)
				$arFilter["AUTO_EXECUTE"] = 0;
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Create)
				$arFilter["AUTO_EXECUTE"] = array(1, 3, 5, 7);
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Edit)
				$arFilter["AUTO_EXECUTE"] = array(2, 3, 6, 7);
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Delete)
				$arFilter["AUTO_EXECUTE"] = array(4, 5, 6, 7);
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Automation)
				$arFilter["AUTO_EXECUTE"] = 8;
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Script)
				$arFilter["AUTO_EXECUTE"] = 32;
			else
				$arFilter["AUTO_EXECUTE"] = array(-1);
		}

		static $arFields = array(
			"ID" => Array("FIELD" => "T.ID", "TYPE" => "int"),
			"MODULE_ID" => Array("FIELD" => "T.MODULE_ID", "TYPE" => "string"),
			"ENTITY" => Array("FIELD" => "T.ENTITY", "TYPE" => "string"),
			"DOCUMENT_TYPE" => Array("FIELD" => "T.DOCUMENT_TYPE", "TYPE" => "string"),
			"DOCUMENT_STATUS" => Array("FIELD" => "T.DOCUMENT_STATUS", "TYPE" => "string"),
			"AUTO_EXECUTE" => Array("FIELD" => "T.AUTO_EXECUTE", "TYPE" => "int"),
			"NAME" => Array("FIELD" => "T.NAME", "TYPE" => "string"),
			"DESCRIPTION" => Array("FIELD" => "T.DESCRIPTION", "TYPE" => "string"),
			"TEMPLATE" => Array("FIELD" => "T.TEMPLATE", "TYPE" => "string"),
			"PARAMETERS" => Array("FIELD" => "T.PARAMETERS", "TYPE" => "string"),
			"VARIABLES" => Array("FIELD" => "T.VARIABLES", "TYPE" => "string"),
			"CONSTANTS" => Array("FIELD" => "T.CONSTANTS", "TYPE" => "string"),
			"MODIFIED" => Array("FIELD" => "T.MODIFIED", "TYPE" => "datetime"),
			"USER_ID" => Array("FIELD" => "T.USER_ID", "TYPE" => "int"),
			"SYSTEM_CODE" => Array("FIELD" => "T.SYSTEM_CODE", "TYPE" => "string"),
			"ACTIVE" => Array("FIELD" => "T.ACTIVE", "TYPE" => "string"),
			"IS_MODIFIED" => Array("FIELD" => "T.IS_MODIFIED", "TYPE" => "string"),
			"IS_SYSTEM" => Array("FIELD" => "T.IS_SYSTEM", "TYPE" => "string"),
			"SORT" => Array("FIELD" => "T.SORT", "TYPE" => "int"),

			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
		);

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_workflow_template T ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_bp_workflow_template T ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_workflow_template T ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$dbRes = new CBPWorkflowTemplateResult($dbRes, $this->useGZipCompression);
		return $dbRes;
	}

	public function AddTemplate($arFields, $systemImport = false)
	{
		global $DB;

		self::ParseFields($arFields, 0, $systemImport);

		$arInsert = $DB->PrepareInsert("b_bp_workflow_template", $arFields);

		$strSql =
			"INSERT INTO b_bp_workflow_template (".$arInsert[0].", MODIFIED) ".
			"VALUES(".$arInsert[1].", ".$DB->CurrentTimeFunction().")";
		$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

		return intval($DB->LastID());
	}

	public function UpdateTemplate($id, $arFields, $systemImport = false)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new CBPArgumentNullException("id");

		self::ParseFields($arFields, $id, $systemImport);

		$strUpdate = $DB->PrepareUpdate("b_bp_workflow_template", $arFields);

		$strSql =
			"UPDATE b_bp_workflow_template SET ".
			"	".$strUpdate.", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = ".intval($id)." ";
		$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $id;
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
}

class CBPWorkflowTemplateResult extends CDBResult
{
	private $useGZipCompression = false;

	public function __construct($res, $useGZipCompression)
	{
		$this->useGZipCompression = $useGZipCompression;
		parent::__construct($res);
	}

	private function GetFromSerializedForm($value)
	{
		if ($value <> '')
		{
			if ($this->useGZipCompression)
			{
				$value1 = @gzuncompress($value);
				if ($value1 !== false)
					$value = $value1;
			}

			$value = unserialize($value, ['allowed_classes' => false]);
			if (!is_array($value))
				$value = array();
		}
		else
		{
			$value = array();
		}
		return $value;
	}

	function Fetch()
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

class CBPWorkflowTemplateValidationException
	extends Exception
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

//Compatibility
class CBPWorkflowTemplateLoader extends CAllBPWorkflowTemplateLoader {}