<?
IncludeModuleLangFile(__FILE__);

use \Bitrix\Bizproc\RestActivityTable;

/**
* Workflow runtime.
*/
class CBPRuntime
{
	const EXCEPTION_CODE_INSTANCE_NOT_FOUND = 404;

	const REST_ACTIVITY_PREFIX = 'rest_';

	private $isStarted = false;
	private static $instance;
	private static $featuresCache = array();

	private $arServices = array(
		"SchedulerService" => null,
		"StateService" => null,
		"TrackingService" => null,
		"TaskService" => null,
		"HistoryService" => null,
		"DocumentService" => null,
	);
	private $arWorkflows = array();

	private $arLoadedActivities = array();

	private $arActivityFolders = array();
	private $workflowChains = array();

	/*********************  SINGLETON PATTERN  **************************************************/

	/**
	* Private constructor prevents from instantiating this class. Singleton pattern.
	* 
	*/
	private function __construct()
	{
		$this->isStarted = false;
		$this->arWorkflows = array();
		$this->arServices = array(
			"SchedulerService" => null,
			"StateService" => null,
			"TrackingService" => null,
			"TaskService" => null,
			"HistoryService" => null,
			"DocumentService" => null,
		);
		$this->arLoadedActivities = array();
		$this->arActivityFolders = array(
			$_SERVER["DOCUMENT_ROOT"]."/local/activities",
			$_SERVER["DOCUMENT_ROOT"].BX_ROOT."/activities/custom",
			$_SERVER["DOCUMENT_ROOT"].BX_ROOT."/activities/bitrix",
			$_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/bizproc/activities",
		);
	}

	/**
	* Static method returns runtime object. Singleton pattern.
	*
	* @return CBPRuntime
	*/
	public static function GetRuntime()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function __clone()
	{
		trigger_error('Clone in not allowed.', E_USER_ERROR);
	}

	/**
	 * Method checks if feature is enabled.
	 * @param string $featureName Feature name. Checks default if empty.
	 * @return bool
	 */
	public static function isFeatureEnabled($featureName = '')
	{
		if (!CModule::IncludeModule('bitrix24'))
			return true;

		$featureName = (string)$featureName;

		if ($featureName === '')
			$featureName = 'bizproc';

		if (!isset(static::$featuresCache[$featureName]))
			static::$featuresCache[$featureName] = \Bitrix\Bitrix24\Feature::isFeatureEnabled($featureName);

		return static::$featuresCache[$featureName];
	}

	/*********************  START / STOP RUNTIME  **************************************************/

	/**
	* Public method starts runtime
	* 
	*/
	public function StartRuntime()
	{
		if ($this->isStarted)
			return;

		if ($this->arServices["SchedulerService"] == null)
			$this->arServices["SchedulerService"] = new CBPSchedulerService();
		if ($this->arServices["StateService"] == null)
			$this->arServices["StateService"] = new CBPStateService();
		if ($this->arServices["TrackingService"] == null)
			$this->arServices["TrackingService"] = new CBPTrackingService();
		if ($this->arServices["TaskService"] == null)
			$this->arServices["TaskService"] = new CBPTaskService();
		if ($this->arServices["HistoryService"] == null)
			$this->arServices["HistoryService"] = new CBPHistoryService();
		if ($this->arServices["DocumentService"] == null)
			$this->arServices["DocumentService"] = new CBPDocumentService();

		foreach ($this->arServices as $serviceId => $service)
			$service->Start($this);

		$this->isStarted = true;
	}

	/**
	* Public method stops runtime
	* 
	*/
	public function StopRuntime()
	{
		if (!$this->isStarted)
			return;

		/** @var CBPWorkflow $workflow */
		foreach ($this->arWorkflows as $key => $workflow)
			$workflow->OnRuntimeStopped();

		foreach ($this->arServices as $serviceId => $service)
			$service->Stop();

		$this->isStarted = false;
	}

	/*******************  PROCESS WORKFLOWS  *********************************************************/

	/**
	 * Creates new workflow instance from the specified template.
	 *
	 * @param int $workflowTemplateId - ID of the workflow template
	 * @param string $documentId - ID of the document
	 * @param mixed $workflowParameters - Optional parameters of the created workflow instance
	 * @param array|null $parentWorkflow - Parent Workflow information.
	 * @return CBPWorkflow
	 * @throws CBPArgumentNullException
	 * @throws CBPArgumentOutOfRangeException
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function CreateWorkflow($workflowTemplateId, $documentId, $workflowParameters = array(), $parentWorkflow = null)
	{
		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
			throw new Exception("workflowTemplateId");

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$limit = \Bitrix\Main\Config\Option::get("bizproc", "limit_simultaneous_processes", "0");
		$ignoreLimits = !empty($workflowParameters[CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT]);
		if (!$ignoreLimits && intval($limit) > 0)
		{
			if (CBPStateService::CountDocumentWorkflows($documentId) >= $limit)
				throw new Exception(GetMessage("BPCGDOC_LIMIT_SIMULTANEOUS_PROCESSES", array("#NUM#" => $limit)));
		}
		unset($workflowParameters[CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT]);

		if (!$this->isStarted)
			$this->StartRuntime();

		$workflowId = uniqid("", true);

		if ($parentWorkflow)
		{
			$this->addWorkflowToChain($workflowId, $parentWorkflow);
			if ($this->checkWorkflowRecursion($workflowId, $workflowTemplateId))
				throw new Exception(GetMessage("BPCGDOC_WORKFLOW_RECURSION_LOCK"));
		}

		$workflow = new CBPWorkflow($workflowId, $this);

		$loader = CBPWorkflowTemplateLoader::GetLoader();

		list($rootActivity, $workflowVariablesTypes, $workflowParametersTypes) = $loader->LoadWorkflow($workflowTemplateId);

		if ($rootActivity == null)
			throw new Exception("EmptyRootActivity");
		//if (!is_a($rootActivity, "IBPRootActivity"))
		//	throw new Exception("RootActivityIsNotAIBPRootActivity");

		foreach(GetModuleEvents("bizproc", "OnCreateWorkflow", true)  as $arEvent)
			ExecuteModuleEventEx($arEvent, array($workflowTemplateId, $documentId, &$workflowParameters));

		$workflow->Initialize($rootActivity, $arDocumentId, $workflowParameters, $workflowVariablesTypes, $workflowParametersTypes, $workflowTemplateId);

		$starterUserId = 0;
		if (isset($workflowParameters[CBPDocument::PARAM_TAGRET_USER]))
			$starterUserId = intval(substr($workflowParameters[CBPDocument::PARAM_TAGRET_USER], strlen("user_")));

		$this->arServices["StateService"]->AddWorkflow($workflowId, $workflowTemplateId, $arDocumentId, $starterUserId);

		$this->arWorkflows[$workflowId] = $workflow;
		return $workflow;
	}

	/**
	 * Returns existing workflow instance by its ID
	 *
	 * @param string $workflowId ID of the workflow instance.
	 * @param bool $silent
	 * @return CBPWorkflow
	 * @throws Exception
	 */
	public function GetWorkflow($workflowId, $silent = false)
	{
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		if (!$this->isStarted)
			$this->StartRuntime();

		if (array_key_exists($workflowId, $this->arWorkflows))
			return $this->arWorkflows[$workflowId];

		$workflow = new CBPWorkflow($workflowId, $this);

		$persister = CBPWorkflowPersister::GetPersister();
		$rootActivity = $persister->LoadWorkflow($workflowId, $silent);
		if ($rootActivity == null)
			throw new Exception("Empty root activity");

		$workflow->Reload($rootActivity);

		$this->arWorkflows[$workflowId] = $workflow;
		return $workflow;
	}

	/*******************  SERVICES  *********************************************************/

	/**
	* Returns service instance by its code.
	* 
	* @param mixed $name - Service code.
	* @return mixed|CBPSchedulerService|CBPStateService|CBPTrackingService|CBPTaskService|CBPHistoryService|CBPDocumentService - Service instance or null if service is not found.
	*/
	public function GetService($name)
	{
		if (array_key_exists($name, $this->arServices))
			return $this->arServices[$name];

		return null;
	}

	/**
	* Adds new service to runtime. Runtime should be stopped.
	* 
	* @param string $name - Service code.
	* @param CBPRuntimeService $service - Service object.
	*/
	public function AddService($name, CBPRuntimeService $service)
	{
		if ($this->isStarted)
			throw new Exception("Runtime is started");

		$name = trim($name);
		if (strlen($name) <= 0)
			throw new Exception("Service code is empty");
		if (!$service)
			throw new Exception("Service is null");

		if (array_key_exists($name, $this->arServices))
			throw new Exception("Service is already exists");

		$this->arServices[$name] = $service;
	}

	/*******************  EVENTS  ******************************************************************/

	/**
	* Static method transfer event to the specified workflow instance.
	* 
	* @param mixed $workflowId - ID of the workflow instance.
	* @param mixed $eventName - Event name.
	* @param mixed $arEventParameters - Event parameters.
	*/
	public static function SendExternalEvent($workflowId, $eventName, $arEventParameters = array())
	{
		$runtime = CBPRuntime::GetRuntime();
		$workflow = $runtime->GetWorkflow($workflowId);
		if ($workflow)
			$workflow->SendExternalEvent($eventName, $arEventParameters);
	}

	/*******************  UTILITIES  ***************************************************************/

	/**
	* Includes activity file by activity code.
	* 
	* @param string $code - Activity code.
	*/
	public function IncludeActivityFile($code)
	{
		if (in_array($code, $this->arLoadedActivities))
			return true;

		if (preg_match("#[^a-zA-Z0-9_]#", $code))
			return false;
		if (strlen($code) <= 0)
			return false;

		$code = strtolower($code);
		if (substr($code, 0, 3) == "cbp")
			$code = substr($code, 3);
		if (strlen($code) <= 0)
			return false;
		if (in_array($code, $this->arLoadedActivities))
			return true;

		$filePath = "";
		$fileDir = "";
		foreach ($this->arActivityFolders as $folder)
		{
			if (file_exists($folder."/".$code."/".$code.".php") && is_file($folder."/".$code."/".$code.".php"))
			{
				$filePath = $folder."/".$code."/".$code.".php";
				$fileDir = $folder."/".$code;
				break;
			}
		}

		if (strlen($filePath) > 0)
		{
			$this->LoadActivityLocalization($fileDir, $code.".php");
			include_once($filePath);
			$this->arLoadedActivities[] = $code;
			return true;
		}

		if (strpos($code, static::REST_ACTIVITY_PREFIX) === 0)
		{
			$code = substr($code, strlen(static::REST_ACTIVITY_PREFIX));
			$result = RestActivityTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=INTERNAL_CODE' => $code)
			));
			$activity = $result->fetch();
			eval('class CBP'.static::REST_ACTIVITY_PREFIX.$code.' extends CBPRestActivity {const REST_ACTIVITY_ID = '.($activity? $activity['ID'] : 0).';}');
			$this->arLoadedActivities[] = static::REST_ACTIVITY_PREFIX.$code;
			return true;
		}

		return false;
	}

	public function GetActivityDescription($code, $lang = false)
	{
		if (preg_match("#[^a-zA-Z0-9_]#", $code))
			return null;
		if (strlen($code) <= 0)
			return null;

		$code = strtolower($code);
		if (substr($code, 0, 3) == "cbp")
			$code = substr($code, 3);
		if (strlen($code) <= 0)
			return null;

		$filePath = "";
		$fileDir = "";
		foreach ($this->arActivityFolders as $folder)
		{
			if (file_exists($folder."/".$code."/".$code.".php") && is_file($folder."/".$code."/".$code.".php"))
			{
				$filePath = $folder."/".$code."/.description.php";
				$fileDir = $folder."/".$code;
				break;
			}
		}

		if (strlen($filePath) > 0)
		{
			$arActivityDescription = array();
			if (file_exists($filePath) && is_file($filePath))
			{
				$this->LoadActivityLocalization($fileDir, ".description.php");
				include($filePath);
			}
			$arActivityDescription["PATH_TO_ACTIVITY"] = $fileDir;

			return $arActivityDescription;
		}

		if (strpos($code, static::REST_ACTIVITY_PREFIX) === 0)
		{
			$code = substr($code, strlen(static::REST_ACTIVITY_PREFIX));
			$result = RestActivityTable::getList(array(
				'filter' => array('=INTERNAL_CODE' => $code)
			));
			$activity = $result->fetch();
			if ($activity)
			{
				return $this->makeRestActivityDescription($activity, $lang);
			}
		}

		return null;
	}

	private function LoadActivityLocalization($path, $file, $lang = false)
	{
		global $MESS;

		if ($lang === false)
			$lang = LANGUAGE_ID;

		$p = $path."/lang/".$lang."/".$file;
		$defaultLang = \Bitrix\Main\Localization\Loc::getDefaultLang($lang);
		$pe = $path."/lang/".$defaultLang."/".$file;

		if (file_exists($p) && is_file($p))
			include($p);
		elseif (file_exists($pe) && is_file($pe))
			include($pe);
	}

	public function GetResourceFilePath($activityPath, $filePath)
	{
		$path = str_replace("\\", "/", $activityPath);
		$path = substr($path, 0, strrpos($path, "/") + 1);

		$filePath = str_replace("\\", "/", $filePath);
		$filePath = ltrim($filePath, "/");

		if (file_exists($path.$filePath) && is_file($path.$filePath))
			return array($path.$filePath, $path);
		else
			return null;
	}

	public function ExecuteResourceFile($activityPath, $filePath, $arParameters = array())
	{
		$result = null;
		$path = $this->GetResourceFilePath($activityPath, $filePath);
		if ($path != null)
		{
			ob_start();

			foreach ($arParameters as $key => $value)
				${$key} = $value;

			$this->LoadActivityLocalization($path[1], $filePath);
			include($path[0]);
			$result = ob_get_contents();
			ob_end_clean();
		}
		return $result;
	}

	public function SearchActivitiesByType($type, array $documentType = null)
	{
		$type = strtolower(trim($type));
		if (strlen($type) <= 0)
			return false;

		$arProcessedDirs = array();
		foreach ($this->arActivityFolders as $folder)
		{
			if ($handle = @opendir($folder))
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
						continue;
					if (!is_dir($folder."/".$dir))
						continue;
					$dirKey = strtolower($dir);
					if (array_key_exists($dirKey, $arProcessedDirs))
						continue;
					if (!file_exists($folder."/".$dir."/.description.php"))
						continue;

					$arActivityDescription = array();
					$this->LoadActivityLocalization($folder."/".$dir, ".description.php");
					include($folder."/".$dir."/.description.php");

					//Support multiple types
					$activityType = (array)$arActivityDescription['TYPE'];
					foreach ($activityType as $i => $aType)
						$activityType[$i] = strtolower(trim($aType));

					if (in_array($type, $activityType, true))
					{
						$arProcessedDirs[$dirKey] = $arActivityDescription;
						$arProcessedDirs[$dirKey]["PATH_TO_ACTIVITY"] = $folder."/".$dir;
						if (
							isset($arActivityDescription['FILTER']) && is_array($arActivityDescription['FILTER'])
							&& !$this->checkActivityFilter($arActivityDescription['FILTER'], $documentType)
						)
							$arProcessedDirs[$dirKey]['EXCLUDED'] = true;

					}

				}
				closedir($handle);
			}
		}

		if ($type == 'activity')
		{
			$arProcessedDirs = array_merge($arProcessedDirs, $this->getRestActivities(false, $documentType));
		}

		if ($type == 'robot_activity')
		{
			$arProcessedDirs = array_merge($arProcessedDirs, $this->getRestRobots(false, $documentType));
		}

		if ($type != 'condition')
			\Bitrix\Main\Type\Collection::sortByColumn($arProcessedDirs, 'NAME');

		return $arProcessedDirs;
	}

	/**
	 * @param bool $lang Language ID.
	 * @param null|array $documentType Document type.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getRestActivities($lang = false, $documentType = null)
	{
		$result = array();
		$iterator = RestActivityTable::getList(array(
			'filter' => array('=IS_ROBOT' => 'N')
		));

		while ($activity = $iterator->fetch())
		{
			$result[static::REST_ACTIVITY_PREFIX.$activity['INTERNAL_CODE']] = $this->makeRestActivityDescription($activity, $lang, $documentType);
		}

		return $result;
	}

	/**
	 * @param bool $lang Language ID.
	 * @param null|array $documentType Document type.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getRestRobots($lang = false, $documentType = null)
	{
		$result = array();
		$iterator = RestActivityTable::getList(array(
			'filter' => array('=IS_ROBOT' => 'Y')
		));

		while ($activity = $iterator->fetch())
		{
			$result[static::REST_ACTIVITY_PREFIX.$activity['INTERNAL_CODE']] = $this->makeRestRobotDescription($activity, $lang, $documentType);
		}

		return $result;
	}


	private function makeRestActivityDescription($activity, $lang = false, $documentType = null)
	{
		if ($lang === false)
			$lang = LANGUAGE_ID;

		$code = static::REST_ACTIVITY_PREFIX.$activity['INTERNAL_CODE'];
		$result = array(
			'NAME' => '['.RestActivityTable::getLocalization($activity['APP_NAME'], $lang).'] '
				.RestActivityTable::getLocalization($activity['NAME'], $lang),
			'DESCRIPTION' => RestActivityTable::getLocalization($activity['DESCRIPTION'], $lang),
			'TYPE' => 'activity',
			'CLASS' => $code,
			'JSCLASS' => 'BizProcActivity',
			'CATEGORY' => array(
				'ID' => 'rest',
			),
			'RETURN' => array(),
			//compatibility
			'PATH_TO_ACTIVITY' => ''
		);

		if (
			isset($activity['FILTER']) && is_array($activity['FILTER'])
			&& !$this->checkActivityFilter($activity['FILTER'], $documentType)
		)
			$result['EXCLUDED'] = true;

		if (!empty($activity['RETURN_PROPERTIES']))
		{
			foreach ($activity['RETURN_PROPERTIES'] as $name => $property)
			{
				$result['RETURN'][$name] = array(
					'NAME' => RestActivityTable::getLocalization($property['NAME'], $lang),
					'TYPE' => isset($property['TYPE']) ? $property['TYPE'] : \Bitrix\Bizproc\FieldType::STRING
				);
			}
		}
		if ($activity['USE_SUBSCRIPTION'] != 'N')
			$result['RETURN']['IsTimeout'] = array(
				'NAME' => GetMessage('BPRA_IS_TIMEOUT'),
				'TYPE' => \Bitrix\Bizproc\FieldType::INT
			);

		return $result;
	}

	private function makeRestRobotDescription($activity, $lang = false, $documentType = null)
	{
		if ($lang === false)
			$lang = LANGUAGE_ID;

		$code = static::REST_ACTIVITY_PREFIX.$activity['INTERNAL_CODE'];
		$result = array(
			'NAME' => '['.RestActivityTable::getLocalization($activity['APP_NAME'], $lang).'] '
				.RestActivityTable::getLocalization($activity['NAME'], $lang),
			'DESCRIPTION' => RestActivityTable::getLocalization($activity['DESCRIPTION'], $lang),
			'TYPE' => array('activity', 'robot_activity'),
			'CLASS' => $code,
			'JSCLASS' => 'BizProcActivity',
			'CATEGORY' => array(),
			'RETURN' => array(),
			//compatibility
			'PATH_TO_ACTIVITY' => '',
			'ROBOT_SETTINGS' => array(
				'CATEGORY' => 'other',
				'IS_AUTO' => true
			)
		);

		if (
			isset($activity['FILTER']) && is_array($activity['FILTER'])
			&& !$this->checkActivityFilter($activity['FILTER'], $documentType)
		)
			$result['EXCLUDED'] = true;

		return $result;
	}

	private function checkActivityFilter($filter, $documentType)
	{
		$distrName = CBPHelper::getDistrName();
		foreach ($filter as $type => $rules)
		{
			$found = $this->checkActivityFilterRules($rules, $documentType, $distrName);
			if ($type == 'INCLUDE' && !$found || $type == 'EXCLUDE' && $found)
				return false;
		}
		return true;
	}

	private function checkActivityFilterRules($rules, $documentType, $distrName)
	{
		if (!is_array($rules) || CBPHelper::IsAssociativeArray($rules))
			$rules = array($rules);

		foreach ($rules as $rule)
		{
			$result = false;
			if (is_array($rule))
			{
				if (!$documentType)
					$result = true;
				else
				{
					foreach ($documentType as $key => $value)
					{
						if (!isset($rule[$key]))
							break;
						$result = $rule[$key] == $value;
						if (!$result)
							break;
					}
				}
			}
			else
			{
				$result = (string)$rule == $distrName;
			}
			if ($result)
				return true;
		}
		return false;
	}

	private function addWorkflowToChain($childId, $parent)
	{
		$this->workflowChains[$childId] = $parent;
		return $this;
	}

	private function checkWorkflowRecursion($workflowId, $currentTemplateId)
	{
		$templates = array($currentTemplateId);
		while (isset($this->workflowChains[$workflowId]))
		{
			$parent = $this->workflowChains[$workflowId];
			if (in_array($parent['templateId'], $templates))
				return true;
			$templates[] = $parent['templateId'];
			$workflowId = $parent['workflowId'];
		}
		return false;
	}
}