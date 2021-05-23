<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Bizproc\Automation;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Template
 * @package Bitrix\Bizproc\Automation\Engine
 */
class Template
{
	protected static $parallelActivityType = 'ParallelActivity';
	protected static $sequenceActivityType = 'SequenceActivity';
	protected static $delayActivityType = 'DelayActivity';
	protected static $conditionActivityType = 'IfElseActivity';
	protected static $availableActivities = [];
	protected static $availableActivityClasses = [];

	protected $template;
	protected $autoExecuteType = \CBPDocumentEventType::Automation;
	/** @var  null|Robot[] */
	protected $robots;
	protected $isExternalModified;
	protected $isConverted = false;

	/**
	 * Template constructor.
	 * @param array $documentType
	 * @param null $documentStatus
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct(array $documentType, $documentStatus = null)
	{
		$this->template = array(
			'ID' => 0,
			'MODULE_ID' => $documentType[0],
			'ENTITY' => $documentType[1],
			'DOCUMENT_TYPE' => $documentType[2],
			'DOCUMENT_STATUS' => $documentStatus,
			'AUTO_EXECUTE' => $this->autoExecuteType,
			'TEMPLATE' => [],
			'PARAMETERS' => [],
			'CONSTANTS' => [],
		);

		if ($documentStatus)
		{
			$row = WorkflowTemplateTable::getList([
				'filter' => [
					'=MODULE_ID' => $documentType[0],
					'=ENTITY' => $documentType[1],
					'=DOCUMENT_TYPE' => $documentType[2],
					'=DOCUMENT_STATUS' => $documentStatus,
					//'=AUTO_EXECUTE' => $this->autoExecuteType
				]
			])->fetch();
			if ($row)
			{
				$this->template = $row;
				$this->autoExecuteType = (int) $this->template['AUTO_EXECUTE'];
			}
		}
	}

	public static function createByTpl(Tpl $tpl)
	{
		$instance = new static($tpl->getDocumentComplexType());
		$instance->template = $tpl->collectValues();
		$instance->autoExecuteType = (int) $instance->template['AUTO_EXECUTE'];

		return $instance;
	}

	public function getDocumentStatus()
	{
		return isset($this->template['DOCUMENT_STATUS']) ? $this->template['DOCUMENT_STATUS'] : null;
	}

	public function setDocumentStatus($status)
	{
		$this->template['DOCUMENT_STATUS'] = (string) $status;
		return $this;
	}

	public function setName(string $name)
	{
		$this->template['NAME'] = $name;
		return $this;
	}

	public function getExecuteType($autoExecuteType)
	{
		return $this->autoExecuteType;
	}

	public function setExecuteType($autoExecuteType)
	{
		if (\CBPDocumentEventType::Out($autoExecuteType) === '')
		{
			throw new ArgumentException('Incorrect DocumentEventType');
		}

		$this->autoExecuteType = $autoExecuteType;
	}

	public function getId()
	{
		return isset($this->template['ID']) ? (int)$this->template['ID'] : 0;
	}

	public function getRobotSettingsDialog(array $robot, $request = null)
	{
		if (isset($robot['Properties']) && is_array($robot['Properties']))
		{
			$robot['Properties'] = Automation\Helper::convertProperties($robot['Properties'], $this->getDocumentType());
		}

		unset($robot['Delay'], $robot['Condition']);

		$copy = clone $this;
		$copy->setRobots([$robot]);

		return \CBPActivity::callStaticMethod(
			$robot['Type'],
			"GetPropertiesDialog",
			array(
				$this->getDocumentType(), //documentType
				$robot['Name'], //activityName
				$copy->template['TEMPLATE'], //arWorkflowTemplate
				[], //arWorkflowParameters
				[], //arWorkflowVariables
				$request, //arCurrentValues = null
				'bizproc_automation_robot_dialog', //formName = ""
				null, //popupWindow = null
				SITE_ID //siteId = ''
			)
		);
	}

	public function saveRobotSettings(array $robot, array $request)
	{
		$saveResult = new Result();
		$documentType = $this->getDocumentType();

		if (isset($robot['Properties']) && is_array($robot['Properties']))
		{
			$robot['Properties'] = Automation\Helper::unConvertProperties($robot['Properties'], $documentType);
		}

		$request = Automation\Helper::unConvertProperties($request, $documentType);

		$copy = clone $this;
		$copy->setRobots([$robot]);
		$raw = $copy->template['TEMPLATE'];

		$robotErrors = $v = $p = array();
		$result = \CBPActivity::callStaticMethod(
			$robot['Type'],
			"GetPropertiesDialogValues",
			[
				$documentType,
				$robot['Name'],
				&$raw,
				&$v,
				&$p,
				$request,
				&$robotErrors
			]
		);

		if ($result)
		{
			$templateActivity = \CBPWorkflowTemplateLoader::findActivityByName($raw, $robot['Name']);

			$robotTitle = $robot['Properties']['Title'];
			$robot['Properties'] = $templateActivity['Properties'];
			$robot['Properties']['Title'] = $robotTitle;

			$saveResult->setData(array('robot' => $robot));
		}
		else
		{
			foreach ($robotErrors as $i => $error)
			{
				$saveResult->addError(new Error($error['message'], $error['code'], ['parameter' => $error['parameter']]));
			}
		}

		return $saveResult;
	}

	public function save(array $robots, $userId, array $additional = [])
	{
		$userId = (int)$userId;
		$result = new Result();
		$templateId = !empty($this->template['ID']) ? $this->template['ID'] : 0;

		$this->setRobots($robots);

		if (isset($additional['PARAMETERS']) && is_array($additional['PARAMETERS']))
		{
			$this->template['PARAMETERS'] = $additional['PARAMETERS'];
		}
		if (isset($additional['CONSTANTS']) && is_array($additional['CONSTANTS']))
		{
			$this->template['CONSTANTS'] = $additional['CONSTANTS'];
		}

		$templateResult = $templateId ?
			$this->updateBizprocTemplate($templateId, $userId) : $this->addBizprocTemplate($userId);

		if ($templateResult->isSuccess())
		{
			$resultData = $templateResult->getData();
			if (isset($resultData['ID']))
			{
				$this->template['ID'] = $resultData['ID'];
			}
		}
		else
		{
			$result->addErrors($templateResult->getErrors());
		}

		return $result;
	}

	public function setRobots(array $robots)
	{
		$this->robots = array();
		$this->isExternalModified = null;
		foreach ($robots as $robot)
		{
			if (is_array($robot))
				$robot = new Robot($robot);

			if (!($robot instanceof Robot))
			{
				throw new ArgumentException('Robots array is incorrect', 'robots');
			}

			$this->robots[] = $robot;
		}

		$this->unConvertTemplate();// make bizproc template

		return $this;
	}

	/**
	 * Convert instance data to array.
	 * @return array
	 */
	public function toArray()
	{
		$result = [
			'ID' => $this->getId(),
			'DOCUMENT_TYPE' => $this->getDocumentType(),
			'DOCUMENT_STATUS' => $this->template['DOCUMENT_STATUS'],
			'PARAMETERS' => $this->template['PARAMETERS'],
			'CONSTANTS' => $this->template['CONSTANTS'],
		];

		$result['IS_EXTERNAL_MODIFIED'] = $this->isExternalModified();
		$result['ROBOTS'] = array();

		foreach ($this->getRobots() as $robot)
		{
			$result['ROBOTS'][] = $robot->toArray();
		}

		return $result;
	}

	public static function getAvailableRobots(array $documentType)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$availableActivities[$key]))
		{
			static::$availableActivities[$key] = \CBPRuntime::getRuntime()
				->searchActivitiesByType('robot_activity', $documentType);
		}
		return static::$availableActivities[$key];
	}

	protected static function getAvailableRobotClasses(array $documentType)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$availableActivityClasses[$key]))
		{
			static::$availableActivityClasses[$key] = array();
			$activities = static::getAvailableRobots($documentType);
			foreach ($activities as $activity)
			{
				static::$availableActivityClasses[$key][] = $activity['CLASS'];
			}
		}
		return static::$availableActivityClasses[$key];
	}

	protected function addBizprocTemplate($userId)
	{
		$userId = (int)$userId;
		$documentType = $this->getDocumentType();

		$raw = $this->template;
		$raw['DOCUMENT_TYPE'] = $documentType;
		$raw['NAME'] = $raw['NAME'] ?? $this->makeTemplateName();
		$raw['USER_ID'] = $userId;
		$raw['MODIFIER_USER'] = new \CBPWorkflowTemplateUser($userId);

		$result = new Result();
		try
		{
			$raw['ID'] = \CBPWorkflowTemplateLoader::add($raw, $userId === 1);
			$result->setData(array('ID' => $raw['ID']));

			$raw['MODULE_ID'] = $documentType[0];
			$raw['ENTITY'] = $documentType[1];
			$raw['DOCUMENT_TYPE'] = $documentType[2];
			$raw['PARAMETERS'] = [];
			$raw['CONSTANTS'] = [];
			$this->template = $raw;
		}
		catch (\Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	protected function makeTemplateName()
	{
		$msg = Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_NAME', array(
			'#STATUS#' => $this->template['DOCUMENT_STATUS']
		));

		if ($this->autoExecuteType === \CBPDocumentEventType::Script)
		{
			$msg = Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_SCRIPT_NAME');
		}

		return $msg;
	}

	protected function updateBizprocTemplate($id, $userId)
	{
		$raw = $this->template;
		$result = new Result();

		$updateFields = [
			'TEMPLATE'      => $raw['TEMPLATE'],
			'PARAMETERS'    => $raw['PARAMETERS'],
			'VARIABLES'     => [],
			'CONSTANTS'     => $raw['CONSTANTS'],
			'USER_ID' 		=> $userId,
			'MODIFIER_USER' => new \CBPWorkflowTemplateUser($userId),
		];

		if (isset($raw['NAME']))
		{
			$updateFields['NAME'] = $raw['NAME'];
		}

		try
		{
			\CBPWorkflowTemplateLoader::update($id, $updateFields);
		}
		catch (\Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	protected function convertTemplate()
	{
		$this->robots = array();

		$raw = $this->template;
		if (!is_array($raw) || !isset($raw['TEMPLATE']))
		{
			return false; // BP template is lost.
		}

		/*if (!empty($raw['PARAMETERS']) || !empty($raw['VARIABLES']) || !empty($raw['CONSTANTS']))
		{
			$this->isExternalModified = true;
			return false; // modified or incorrect.
		}*/

		if (empty($raw['TEMPLATE'][0]['Children']) || !is_array($raw['TEMPLATE'][0]['Children']))
			return true;

		if (count($raw['TEMPLATE'][0]['Children']) > 1)
		{
			$this->isExternalModified = true;
			return false; // modified or incorrect.
		}

		$parallelActivity = $raw['TEMPLATE'][0]['Children'][0];
		if (!$parallelActivity || $parallelActivity['Type'] !== static::$parallelActivityType)
		{
			$this->isExternalModified = true;
			return false; // modified or incorrect.
		}

		foreach ($parallelActivity['Children'] as $sequence)
		{
			$delay = $condition = null;
			$robotsCnt = 0;
			foreach ($sequence['Children'] as $activity)
			{
				if ($activity['Type'] === static::$delayActivityType)
				{
					$delay = $activity;
					continue;
				}

				if ($activity['Type'] === static::$conditionActivityType)
				{
					$condition = ConditionGroup::convertBizprocActivity($activity, $this->getDocumentType(), $this);
					if ($condition === false)
					{
						$this->isExternalModified = true;
						$this->robots = array();
						return false; // modified or incorrect.
					}
				}

				if (!$this->isRobot($activity))
				{
					$this->isExternalModified = true;
					$this->robots = array();
					return false; // modified or incorrect.
				}

				$robotActivity = new Robot($activity);
				if ($delay !== null)
				{
					$delayInterval = DelayInterval::createFromActivityProperties($delay['Properties']);
					$robotActivity->setDelayInterval($delayInterval);
					$robotActivity->setDelayName($delay['Name']);
					$delay = null;
				}

				if ($condition !== null)
				{
					$robotActivity->setCondition($condition);
					$condition = null;
				}

				if ($robotsCnt > 0)
				{
					$robotActivity->setExecuteAfterPrevious();
				}

				++$robotsCnt;
				$this->robots[] = $robotActivity;
			}
		}

		$this->isConverted = true;
		return $this->robots;
	}

	protected function unConvertTemplate()
	{
		$documentType = $this->getDocumentType();
		$this->template = [
			'ID' => $this->getId(),
			'MODULE_ID' => $documentType[0],
			'ENTITY' => $documentType[1],
			'DOCUMENT_TYPE' => $documentType[2],
			'DOCUMENT_STATUS' => $this->template['DOCUMENT_STATUS'],
			'NAME' => $this->template['NAME'] ?? $this->makeTemplateName(),
			'AUTO_EXECUTE' => $this->autoExecuteType,
			'TEMPLATE'     => [[
				'Type' => 'SequentialWorkflowActivity',
				'Name' => 'Template',
				'Properties' => ['Title' => 'Bizproc Automation template'],
				'Children' => []
			]],
			'PARAMETERS' => $this->template['PARAMETERS'],
			'CONSTANTS' => $this->template['CONSTANTS'],
			'SYSTEM_CODE'  => 'bitrix_bizproc_automation'
		];

		if ($this->robots)
		{
			$parallelActivity = $this->createParallelActivity();
			$sequence = $this->createSequenceActivity();

			foreach ($this->robots as $i => $robot)
			{
				if ($i !== 0 && !$robot->isExecuteAfterPrevious())
				{
					$parallelActivity['Children'][] = $sequence;
					$sequence = $this->createSequenceActivity();
				}

				$delayInterval = $robot->getDelayInterval();
				if ($delayInterval && !$delayInterval->isNow())
				{
					$delayName = $robot->getDelayName();
					if (!$delayName)
					{
						$delayName = Robot::generateName();
						$robot->setDelayName($delayName);
					}

					$sequence['Children'][] = $this->createDelayActivity(
						$delayInterval->toActivityProperties($documentType),
						$delayName
					);
				}

				$activity = $robot->getBizprocActivity();
				$condition = $robot->getCondition();

				if ($condition && count($condition->getItems()) > 0)
				{
					$activity = $condition->createBizprocActivity($activity, $documentType, $this);
				}

				$sequence['Children'][] = $activity;
			}

			$parallelActivity['Children'][] = $sequence;

			if (count($parallelActivity['Children']) < 2)
			{
				$parallelActivity['Children'][] = $this->createSequenceActivity();
			}

			$this->template['TEMPLATE'][0]['Children'][] = $parallelActivity;
		}
		$this->robots = null;
		$this->isConverted = false;
	}

	protected function isRobot(array $activity)
	{
		if (!in_array($activity['Type'], static::getAvailableRobotClasses($this->getDocumentType())))
			return false;

		if (!empty($activity['Children']))
			return false;
		return true;
	}

	/**
	 * @return Robot[] Robot activities.
	 */
	public function getRobots()
	{
		if ($this->robots === null)
			$this->convertTemplate();

		return $this->robots;
	}

	/**
	 * Returns Robot by it`s id.
	 * @param string $name Robot identificator.
	 * @return Robot|null Robot instance.
	 */
	public function getRobotByName(string $name): ?Robot
	{
		foreach ($this->getRobots() as $robot)
		{
			if ($name === $robot->getName())
			{
				return  $robot;
			}
		}
		return  null;
	}

	/**
	 * @return array Template activities.
	 */
	public function getActivities()
	{
		return $this->template['TEMPLATE'];
	}

	/**
	 * Checks is template was modified by external editor.
	 * @return bool
	 */
	public function isExternalModified()
	{
		if ($this->isExternalModified === null)
			$this->getRobots();

		return ($this->isExternalModified === true);
	}

	public function getDocumentType(): array
	{
		return [$this->template['MODULE_ID'], $this->template['ENTITY'], $this->template['DOCUMENT_TYPE']];
	}

	public function getProperty($object, $field): ?array
	{
		switch ($object)
		{
			case 'Template':
				return $this->template['PARAMETERS'][$field] ?? null;
				break;
			case 'Variable':
				return $this->template['VARIABLES'][$field] ?? null;
				break;
			case 'Constant':
				return $this->template['CONSTANTS'][$field] ?? null;
				break;
			case 'GlobalConst':
				return Bizproc\Workflow\Type\GlobalConst::getById($field);
				break;
			case 'Document':
				static $fields;
				if (!$fields)
				{
					$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
					$fields = $documentService->GetDocumentFields($this->getDocumentType());
				}

				return $fields[$field] ?? null;
				break;
			default:
				if ($this->isConverted)
				{
					return $this->findRobotProperty($object, $field);
				}
				else
				{
					return $this->findActivityProperty($object, $field);
				}
				break;
		}
	}

	private function findRobotProperty($object, $field): ?array
	{
		$robot = $this->getRobotByName($object);
		return $robot ? $robot->getReturnProperty($field) : null;
	}

	private function findActivityProperty($object, $field): ?array
	{
		$activity = self::findTemplateActivity($this->template['TEMPLATE'], $object);
		if (!$activity)
		{
			return null;
		}

		$props = \CBPRuntime::GetRuntime(true)->getActivityReturnProperties($activity['Type']);
		return $props[$field] ?? null;
	}

	private static function findTemplateActivity(array $template, $id)
	{
		foreach ($template as $activity)
		{
			if ($activity['Name'] === $id)
			{
				return $activity;
			}
			if (is_array($activity['Children']))
			{
				$found = self::findTemplateActivity($activity['Children'], $id);
				if ($found)
				{
					return $found;
				}
			}
		}
		return null;
	}

	private function createSequenceActivity()
	{
		return array(
			'Type' => static::$sequenceActivityType,
			'Name' => Robot::generateName(),
			'Properties' => array(
				'Title' => 'Automation sequence'
			),
			'Children' => array()
		);
	}

	private function createParallelActivity()
	{
		return array(
			'Type' => static::$parallelActivityType,
			'Name' => Robot::generateName(),
			'Properties' => array(
				'Title' => Loc::getMessage('BIZPROC_AUTOMATION_PARALLEL_ACTIVITY'),
			),
			'Children' => array()
		);
	}

	private function createDelayActivity(array $delayProperties, $delayName)
	{
		if (!isset($delayProperties['Title']))
			$delayProperties['Title'] = Loc::getMessage('BIZPROC_AUTOMATION_DELAY_ACTIVITY');

		return array(
			'Type' => static::$delayActivityType,
			'Name' => $delayName,
			'Properties' => $delayProperties,
			'Children' => array()
		);
	}
}