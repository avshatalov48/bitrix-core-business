<?php

use Bitrix\Bizproc\Debugger\Workflow\DebugWorkflow;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Main;
use Bitrix\Bizproc;
use Bitrix\Bizproc\RestActivityTable;

/**
 * Workflow runtime.
 *
 * @method \CBPSchedulerService getSchedulerService()
 * @method \CBPStateService getStateService()
 * @method \CBPTrackingService getTrackingService()
 * @method \CBPTaskService getTaskService()
 * @method \CBPHistoryService getHistoryService()
 * @method \CBPDocumentService getDocumentService()
 * @method Bizproc\Service\Analytics getAnalyticsService()
 * @method Bizproc\Service\User getUserService()
 */
class CBPRuntime
{
	const EXCEPTION_CODE_INSTANCE_NOT_FOUND = 404;
	const EXCEPTION_CODE_INSTANCE_LOCKED = 423;
	const EXCEPTION_CODE_INSTANCE_TERMINATED = 499;

	const REST_ACTIVITY_PREFIX = 'rest_';

	private $isStarted = false;
	/** @var CBPRuntime $instance*/
	private static $instance;
	private static $featuresCache = [];

	private $services = [];
	private $debugServices = [];
	private $workflows = [];

	private $loadedActivities = [];

	private $activityFolders = [];
	private $workflowChains = [];

	/*********************  SINGLETON PATTERN  **************************************************/

	/**
	* Private constructor prevents from instantiating this class. Singleton pattern.
	*
	*/
	private function __construct()
	{
		$this->workflows = array();
		$this->services = array(
			"SchedulerService" => null,
			"StateService" => null,
			"TrackingService" => null,
			"TaskService" => null,
			"HistoryService" => null,
			"DocumentService" => null,
			"AnalyticsService" => null,
			"UserService" => null,
		);
		$this->loadedActivities = array();
		$this->activityFolders = array(
			$_SERVER["DOCUMENT_ROOT"]."/local/activities",
			$_SERVER["DOCUMENT_ROOT"]."/local/activities/custom",
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
	public static function getRuntime()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
			self::$instance->startRuntime();
		}

		return self::$instance;
	}

	public function __clone()
	{
		trigger_error('Clone in not allowed.', E_USER_ERROR);
	}

	public function __call($name, $arguments)
	{
		if (preg_match('|^get([a-z]+)service$|i', $name, $matches))
		{
			return $this->GetService($matches[1]. 'Service');
		}

		throw new Main\SystemException("Unknown method `{$name}`");
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
	public function startRuntime()
	{
		if ($this->isStarted)
		{
			return;
		}

		$serviceManager = Bizproc\Service\Manager::getInstance();
		foreach ($serviceManager->getAllServiceNames() as $serviceName)
		{
			$compatibleServiceName = mb_strtoupper($serviceName[0]) . mb_substr($serviceName, 1);
			if (!$this->services[$compatibleServiceName])
			{
				$this->services[$compatibleServiceName] = $serviceManager->getService($serviceName);
			}
			if (!isset($this->debugServices[$compatibleServiceName]) && $serviceManager->hasDebugService($serviceName))
			{
				$this->debugServices[$compatibleServiceName] = $serviceManager->getDebugService($serviceName);
			}

			$this->services[$compatibleServiceName]->start($this);
		}

		$this->isStarted = true;
	}

	/**
	* Public method stops runtime
	* @deprecated Unused and not actual
	*/
	public function stopRuntime()
	{
		if (!$this->isStarted)
		{
			return;
		}

		/** @var CBPWorkflow $workflow */
		foreach ($this->workflows as $key => $workflow)
		{
			$workflow->OnRuntimeStopped();
		}

		foreach ($this->services as $serviceId => $service)
		{
			$service->stop();
		}

		$this->isStarted = false;
	}

	/*******************  PROCESS WORKFLOWS  *********************************************************/

	/**
	 * Creates new workflow instance from the specified template.
	 *
	 * @param int $workflowTemplateId - ID of the workflow template
	 * @param array $documentId - ID of the document
	 * @param mixed $workflowParameters - Optional parameters of the created workflow instance
	 * @param array|null $parentWorkflow - Parent Workflow information.
	 * @return CBPWorkflow
	 * @throws CBPArgumentNullException
	 * @throws CBPArgumentOutOfRangeException
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function createWorkflow($workflowTemplateId, $documentId, $workflowParameters = array(), $parentWorkflow = null)
	{
		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
		{
			throw new Exception("workflowTemplateId");
		}

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$limit = \Bitrix\Main\Config\Option::get("bizproc", "limit_simultaneous_processes", "0");
		$ignoreLimits = !empty($workflowParameters[CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT]);
		if (!$ignoreLimits && intval($limit) > 0)
		{
			if (CBPStateService::CountDocumentWorkflows($documentId) >= $limit)
			{
				throw new Exception(GetMessage("BPCGDOC_LIMIT_SIMULTANEOUS_PROCESSES", ["#NUM#" => $limit]));
			}
		}
		unset($workflowParameters[CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT]);

		if (!$this->isStarted)
		{
			$this->StartRuntime();
		}

		$workflowId = $workflowParameters[CBPDocument::PARAM_PRE_GENERATED_WORKFLOW_ID] ?? static::generateWorkflowId();

		if ($parentWorkflow)
		{
			$this->addWorkflowToChain($workflowId, $parentWorkflow);
			if ($this->checkWorkflowRecursion($workflowId, $workflowTemplateId))
			{
				throw new Exception(GetMessage("BPCGDOC_WORKFLOW_RECURSION_LOCK"));
			}
		}

		$workflow = new CBPWorkflow($workflowId, $this);

		$loader = CBPWorkflowTemplateLoader::GetLoader();

		/** @var CBPCompositeActivity $rootActivity */
		[$rootActivity, $workflowVariablesTypes, $workflowParametersTypes] = $loader->LoadWorkflow($workflowTemplateId);
		foreach ($workflowParametersTypes as $parameterName => $parametersProperty)
		{
			if (!array_key_exists($parameterName, $workflowParameters))
			{
				$workflowParameters[$parameterName] = $parametersProperty['Default'];
			}
		}

		if ($rootActivity == null)
		{
			throw new Exception("EmptyRootActivity");
		}

		foreach(GetModuleEvents("bizproc", "OnCreateWorkflow", true)  as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$workflowTemplateId, $documentId, &$workflowParameters, $workflowId]);
		}

		$workflow->Initialize($rootActivity, $arDocumentId, $workflowParameters, $workflowVariablesTypes, $workflowParametersTypes, $workflowTemplateId);

		$starterUserId = 0;
		if (isset($workflowParameters[CBPDocument::PARAM_TAGRET_USER]))
		{
			$starterUserId = intval(mb_substr($workflowParameters[CBPDocument::PARAM_TAGRET_USER], mb_strlen("user_")));
		}

		$this->GetService("StateService")->AddWorkflow($workflowId, $workflowTemplateId, $arDocumentId, $starterUserId);

		$this->workflows[$workflowId] = $workflow;

		return $workflow;
	}

	public function createDebugWorkflow(int $templateId, array $documentId, $workflowParameters = [])
	{
		$complexDocumentId = CBPHelper::ParseDocumentId($documentId);

		$workflowId = $workflowParameters[CBPDocument::PARAM_PRE_GENERATED_WORKFLOW_ID] ?? static::generateWorkflowId();
		$workflow = new DebugWorkflow($workflowId, $this);

		$loader = CBPWorkflowTemplateLoader::GetLoader();

		[$rootActivity, $workflowVariablesTypes, $workflowParametersTypes] = $loader->LoadWorkflow($templateId);

		if (is_null($rootActivity))
		{
			throw new Exception('Empty root activity');
		}

		foreach(GetModuleEvents("bizproc", "OnCreateWorkflow", true)  as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$templateId, $documentId, &$workflowParameters, $workflowId]);
		}

		$workflow->Initialize(
			$rootActivity,
			$complexDocumentId,
			$workflowParameters,
			$workflowVariablesTypes,
			$workflowParametersTypes,
			$templateId
		);

		$starterUserId = 0;
		if (isset($workflowParameters[CBPDocument::PARAM_TAGRET_USER]))
		{
			$starterUserId = intval(mb_substr($workflowParameters[CBPDocument::PARAM_TAGRET_USER], mb_strlen("user_")));
		}

		$this
			->GetService("StateService")
			->AddWorkflow($workflowId, $templateId, $complexDocumentId, $starterUserId)
		;

		$this->workflows[$workflowId] = $workflow;

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
	public function getWorkflow($workflowId, $silent = false)
	{
		if ($workflowId == '')
			throw new Exception("workflowId");

		if (!$this->isStarted)
			$this->StartRuntime();

		if (array_key_exists($workflowId, $this->workflows))
			return $this->workflows[$workflowId];

		$workflow = $this->getWorkflowInstance($workflowId);
		$rootActivity = $workflow->getPersister()->LoadWorkflow($workflowId, $silent);

		if ($rootActivity == null)
		{
			throw new Exception("Empty root activity");
		}

		$workflow->reload($rootActivity);

		$this->workflows[$workflowId] = $workflow;
		return $workflow;
	}

	protected function getWorkflowInstance(string $workflowId): CBPWorkflow
	{
		if (WorkflowInstanceTable::isDebugWorkflow($workflowId))
		{
			return new DebugWorkflow($workflowId, $this);
		}

		return new CBPWorkflow($workflowId, $this);
	}

	public function onWorkflowStatusChanged($workflowId, $status)
	{
		if (
			$status === \CBPWorkflowStatus::Completed ||
			$status === \CBPWorkflowStatus::Terminated ||
			$status === \CBPWorkflowStatus::Suspended
		)
		{
			unset($this->workflows[$workflowId]);
		}
	}

	public function onDocumentDelete(array $documentId): void
	{
		/** @var CBPWorkflow $workflow */
		foreach ($this->workflows as $workflow)
		{
			if (CBPHelper::isEqualDocument($documentId, $workflow->getDocumentId()))
			{
				$workflow->abandon();
			}
		}
	}

	public static function generateWorkflowId(): string
	{
		return uniqid("", true);
	}

	/*******************  SERVICES  *********************************************************/

	/**
	* Returns service instance by its code.
	*
	* @param mixed $name - Service code.
	* @return mixed|CBPSchedulerService|CBPStateService|CBPTrackingService|CBPTaskService|CBPHistoryService|CBPDocumentService|Bizproc\Service\Analytics - Service instance or null if service is not found.
	*/
	public function getService($name)
	{
		if (array_key_exists($name, $this->services))
		{
			return $this->services[$name];
		}

		return null;
	}

	public function getDebugService($name)
	{
		if (array_key_exists($name, $this->debugServices))
		{
			return $this->debugServices[$name];
		}

		return null;
	}

	/**
	* Adds new service to runtime. Runtime should be stopped.
	*
	* @param string $name - Service code.
	* @param CBPRuntimeService $service - Service object.
	*/
	public function addService($name, CBPRuntimeService $service)
	{
		if ($this->isStarted)
			throw new Exception("Runtime is started");

		$name = trim($name);
		if ($name == '')
			throw new Exception("Service code is empty");
		if (!$service)
			throw new Exception("Service is null");

		if (array_key_exists($name, $this->services))
			throw new Exception("Service is already exists");

		$this->services[$name] = $service;
	}

	/*******************  EVENTS  ******************************************************************/

	/**
	* Static method transfer event to the specified workflow instance.
	*
	* @param mixed $workflowId - ID of the workflow instance.
	* @param mixed $eventName - Event name.
	* @param mixed $arEventParameters - Event parameters.
	*/
	public static function sendExternalEvent($workflowId, $eventName, $arEventParameters = array())
	{
		$runtime = CBPRuntime::GetRuntime();
		$workflow = $runtime->GetWorkflow($workflowId);
		if ($workflow)
		{
			//check if state exists
			$stateExists = CBPStateService::exists($workflowId);
			$documentExists = false;

			if ($stateExists)
			{
				$documentService = $runtime->getDocumentService();
				$documentExists = $documentService->isDocumentExists($workflow->getDocumentId());
			}

			if (!$stateExists || !$documentExists)
			{
				$workflow->Terminate();

				return false;
			}

			$workflow->SendExternalEvent($eventName, $arEventParameters);
		}
	}

	/*******************  UTILITIES  ***************************************************************/

	/**
	* Includes activity file by activity code.
	*
	* @param string $code - Activity code.
	*/
	public function includeActivityFile($code)
	{
		if (in_array($code, $this->loadedActivities))
			return true;

		if (preg_match("#[^a-zA-Z0-9_]#", $code))
			return false;
		if ($code == '')
			return false;

		$code = mb_strtolower($code);
		if (mb_substr($code, 0, 3) == "cbp")
			$code = mb_substr($code, 3);
		if ($code == '')
			return false;
		if (in_array($code, $this->loadedActivities))
			return true;

		$filePath = "";
		$fileDir = "";
		foreach ($this->activityFolders as $folder)
		{
			if (file_exists($folder."/".$code."/".$code.".php") && is_file($folder."/".$code."/".$code.".php"))
			{
				$filePath = $folder."/".$code."/".$code.".php";
				$fileDir = $folder."/".$code;
				break;
			}
		}

		if ($filePath <> '')
		{
			$this->LoadActivityLocalization($fileDir, $code.".php");
			include_once($filePath);
			$this->loadedActivities[] = $code;
			return true;
		}

		if (mb_strpos($code, static::REST_ACTIVITY_PREFIX) === 0)
		{
			$code = mb_substr($code, mb_strlen(static::REST_ACTIVITY_PREFIX));
			$result = RestActivityTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=INTERNAL_CODE' => $code)
			));
			$activity = $result->fetch();
			eval('class CBP'.static::REST_ACTIVITY_PREFIX.$code.' extends CBPRestActivity {const REST_ACTIVITY_ID = '.($activity? $activity['ID'] : 0).';}');
			$this->loadedActivities[] = static::REST_ACTIVITY_PREFIX.$code;
			return true;
		}

		return false;
	}

	public function getActivityDescription($code, $lang = false)
	{
		if (preg_match("#[^a-zA-Z0-9_]#", $code))
			return null;
		if ($code == '')
			return null;

		$code = mb_strtolower($code);
		if (mb_substr($code, 0, 3) == "cbp")
			$code = mb_substr($code, 3);
		if ($code == '')
			return null;

		$filePath = "";
		$fileDir = "";
		foreach ($this->activityFolders as $folder)
		{
			if (file_exists($folder."/".$code."/".$code.".php") && is_file($folder."/".$code."/".$code.".php"))
			{
				$filePath = $folder."/".$code."/.description.php";
				$fileDir = $folder."/".$code;
				break;
			}
		}

		if ($filePath <> '')
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

		if (mb_strpos($code, static::REST_ACTIVITY_PREFIX) === 0)
		{
			$code = mb_substr($code, mb_strlen(static::REST_ACTIVITY_PREFIX));
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

	public function getActivityReturnProperties($code, $lang = false): array
	{
		$activity = null;
		if (is_array($code))
		{
			$activity = $code;
			$code = $activity['Type'];
		}

		$description = $this->GetActivityDescription($code, $lang);
		$props = [];
		if (isset($description['RETURN']) && is_array($description['RETURN']))
		{
			foreach ($description['RETURN'] as $id => $prop)
			{
				$props[$id] = Bizproc\FieldType::normalizeProperty($prop);
			}
		}
		if (isset($description['ADDITIONAL_RESULT']) && is_array($description['ADDITIONAL_RESULT']))
		{
			foreach($description['ADDITIONAL_RESULT'] as $propertyKey)
			{
				if (isset($activity['Properties'][$propertyKey]) && is_array($activity['Properties'][$propertyKey]))
				{
					foreach ($activity['Properties'][$propertyKey] as $id => $prop)
					{
						$props[$id] = Bizproc\FieldType::normalizeProperty($prop);
					}
				}
			}
		}
		return $props;
	}

	private function loadActivityLocalization($path, $file, $lang = false)
	{
		\Bitrix\Main\Localization\Loc::loadLanguageFile($path. '/'. $file);
	}

	public function getResourceFilePath($activityPath, $filePath)
	{
		$path = str_replace("\\", "/", $activityPath);
		$path = mb_substr($path, 0, mb_strrpos($path, "/") + 1);

		$filePath = str_replace("\\", "/", $filePath);
		$filePath = ltrim($filePath, "/");

		if (file_exists($path.$filePath) && is_file($path.$filePath))
			return array($path.$filePath, $path);
		else
			return null;
	}

	public function executeResourceFile($activityPath, $filePath, $arParameters = array())
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

	public function searchActivitiesByType($type, array $documentType = null)
	{
		$type = mb_strtolower(trim($type));
		if ($type == '')
			return false;

		$arProcessedDirs = array();
		foreach ($this->activityFolders as $folder)
		{
			if (is_dir($folder) && $handle = opendir($folder))
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
						continue;
					if (!is_dir($folder."/".$dir))
						continue;
					$dirKey = mb_strtolower($dir);
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
						$activityType[$i] = mb_strtolower(trim($aType));

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

		if ($type == 'activity' || $type == 'robot_activity')
		{
			$arProcessedDirs = array_merge($arProcessedDirs, $this->getRestRobots(false, $documentType));
		}

		if ($type !== 'condition')
		{
			\Bitrix\Main\Type\Collection::sortByColumn($arProcessedDirs, ['SORT' => SORT_ASC, 'NAME' => SORT_ASC]);
		}

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

	public function unserializeWorkflowStream(string $stream)
	{
		$pos = mb_strpos($stream, ";");
		$usedActivities = mb_substr($stream, 0, $pos);
		$stream = mb_substr($stream, $pos + 1);

		foreach (explode(',', $usedActivities) as $activityCode)
		{
			$this->IncludeActivityFile($activityCode);
		}

		$classesList = array_map(
			function ($name)
			{
				return 'cbp'.$name;
			},
			$this->loadedActivities
		);

		/** @bug 0135642 */
		if (in_array('cbpstateactivity', $classesList))
		{
			$classesList[] = CBPStateEventActivitySubscriber::class;
		}
		if (in_array('cbplistenactivity', $classesList))
		{
			$classesList[] = CBPListenEventActivitySubscriber::class;
		}

		$classesList[] = \CBPWorkflow::class;
		$classesList[] = \CBPRuntime::class;
		$classesList[] = DebugWorkflow::class;
		$classesList[] = Bizproc\BaseType\Value\Date::class;
		$classesList[] = Bizproc\BaseType\Value\DateTime::class;
		$classesList[] = Main\Type\Date::class;
		$classesList[] = Main\Type\DateTime::class;
		$classesList[] = Main\Web\Uri::class;
		$classesList[] = \DateTime::class;
		$classesList[] = \DateTimeZone::class;

		return unserialize($stream, ['allowed_classes' => $classesList]);
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
			'CATEGORY' => [
				'ID' => 'rest',
			],
			'RETURN' => array(),
			//compatibility
			'PATH_TO_ACTIVITY' => '',
			'ROBOT_SETTINGS' => array(
				'CATEGORY' => 'other'
			)
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
				$result['RETURN'][$name] = [
					'NAME' => RestActivityTable::getLocalization($property['NAME'], $lang),
					'TYPE' => $property['TYPE'] ?? \Bitrix\Bizproc\FieldType::STRING,
					'OPTIONS' => $property['OPTIONS'] ?? null,
				];
			}
		}
		if ($activity['USE_SUBSCRIPTION'] !== 'N')
		{
			$result['RETURN']['IsTimeout'] = array(
				'NAME' => GetMessage('BPRA_IS_TIMEOUT'),
				'TYPE' => \Bitrix\Bizproc\FieldType::INT
			);
		}

		return $result;
	}

	public function checkActivityFilter($filter, $documentType)
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
