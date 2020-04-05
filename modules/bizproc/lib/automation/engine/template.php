<?php
namespace Bitrix\Bizproc\Automation\Engine;

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

	protected static $availableActivities = array();
	protected static $availableActivityClasses = array();

	protected $template;
	/** @var  null|Robot[] */
	protected $robots;
	protected $isExternalModified;

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
			'AUTO_EXECUTE' => \CBPDocumentEventType::Automation
		);

		if ($documentStatus)
		{
			$row = WorkflowTemplateTable::getList([
				'filter' => [
					'=MODULE_ID' => $documentType[0],
					'=ENTITY' => $documentType[1],
					'=DOCUMENT_TYPE' => $documentType[2],
					'=DOCUMENT_STATUS' => $documentStatus,
					'=AUTO_EXECUTE' => \CBPDocumentEventType::Automation
				]
			])->fetch();
			if ($row)
			{
				$this->template = $row;
			}
		}
	}

	public function getId()
	{
		return isset($this->template['ID']) ? (int)$this->template['ID'] : 0;
	}

	public function getRobotSettingsDialog(array $robot, $request = null)
	{
		if (isset($robot['Properties']) && is_array($robot['Properties']))
		{
			$robot['Properties'] = $this->convertRobotProperties($robot['Properties'], $this->getDocumentType());
		}

		$this->setRobots(array($robot));

		return \CBPActivity::callStaticMethod(
			$robot['Type'],
			"GetPropertiesDialog",
			array(
				$this->getDocumentType(),
				$robot['Name'],
				$this->template['TEMPLATE'],
				array(),
				array(),
				null,
				$request,
				null,
				SITE_ID
			)
		);
	}

	public function saveRobotSettings(array $robot, array $request)
	{
		$saveResult = new Result();
		$documentType = $this->getDocumentType();

		if (isset($robot['Properties']) && is_array($robot['Properties']))
		{
			$robot['Properties'] = $this->unConvertRobotProperties($robot['Properties'], $documentType);
		}

		if (is_array($request))
		{
			$request = $this->unConvertRobotProperties($request, $documentType);
		}

		$this->setRobots(array($robot));
		$raw = $this->template['TEMPLATE'];

		$robotErrors = $v = $p = array();
		$result = \CBPActivity::callStaticMethod(
			$robot['Type'],
			"GetPropertiesDialogValues",
			array(
				$documentType,
				$robot['Name'],
				&$raw,
				&$v,
				&$p,
				$request,
				&$robotErrors
			)
		);

		if ($result)
		{
			$templateActivity = \CBPWorkflowTemplateLoader::findActivityByName($raw, $robot['Name']);

			if ($robot['Type'] === 'CrmSendEmailActivity') //Fix for WAF
			{
				$templateActivity['Properties'] = $this->unConvertRobotProperties($templateActivity['Properties'], $documentType);
			}

			$robotTitle = $robot['Properties']['Title'];
			$robot['Properties'] = $templateActivity['Properties'];
			$robot['Properties']['Title'] = $robotTitle;

			$saveResult->setData(array('robot' => $robot));
		}
		else
		{
			foreach ($robotErrors as $i => $error)
			{
				$saveResult->addError(new Error($error['message']));
			}
		}

		return $saveResult;
	}

	public function save(array $robots, $userId)
	{
		$userId = (int)$userId;
		$result = new Result();
		$templateId = !empty($this->template['ID']) ? $this->template['ID'] : 0;

		$this->setRobots($robots);

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
				throw new ArgumentException('Robots array is incorrect', 'robots');

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
			'DOCUMENT_STATUS' => $this->template['DOCUMENT_STATUS']
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
			$runtime = \CBPRuntime::getRuntime();
			static::$availableActivities[$key] = $runtime->searchActivitiesByType('robot_activity', $documentType);
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
		$raw['NAME'] = Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_NAME', array(
			'#STATUS#' => $this->template['DOCUMENT_STATUS']
		));
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
			$this->template = $raw;
		}
		catch (\Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	protected function updateBizprocTemplate($id, $userId)
	{
		$raw = $this->template;
		$result = new Result();

		try
		{
			\CBPWorkflowTemplateLoader::update($id, array(
				'TEMPLATE'      => $raw['TEMPLATE'],
				'PARAMETERS'    => array(),
				'VARIABLES'     => array(),
				'CONSTANTS'     => array(),
				'USER_ID' 		=> $userId,
				'MODIFIER_USER' => new \CBPWorkflowTemplateUser($userId),
			));
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

		if (!empty($raw['PARAMETERS']) || !empty($raw['VARIABLES']) || !empty($raw['CONSTANTS']))
		{
			$this->isExternalModified = true;
			return false; // modified or incorrect.
		}

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
					$condition = ConditionGroup::convertBizprocActivity($activity);
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

		return $this->robots;
	}

	protected function unConvertTemplate()
	{
		$documentType = $this->getDocumentType();
		$this->template = array(
			'ID' => $this->getId(),
			'MODULE_ID' => $documentType[0],
			'ENTITY' => $documentType[1],
			'DOCUMENT_TYPE' => $documentType[2],
			'DOCUMENT_STATUS' => $this->template['DOCUMENT_STATUS'],
			'AUTO_EXECUTE' => \CBPDocumentEventType::Automation,
			'TEMPLATE'     => array(array(
				'Type' => 'SequentialWorkflowActivity',
				'Name' => 'Template',
				'Properties' => array('Title' => 'Bizproc Automation template'),
				'Children' => array()
			)),
			'SYSTEM_CODE'  => 'bitrix_bizproc_automation'
		);

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
						$delayInterval->toActivityProperties(),
						$delayName
					);
				}

				$activity = $robot->getBizprocActivity();
				$condition = $robot->getCondition();

				if ($condition && count($condition->getItems()) > 0)
				{
					$activity = $condition->createBizprocActivity($activity);
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
	 * @return null|Robot[] Robot activities.
	 */
	public function getRobots()
	{
		if ($this->robots === null)
			$this->convertTemplate();

		return $this->robots;
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

	private function convertRobotProperties(array $properties, array $documentType)
	{
		foreach ($properties as $code => $property)
		{
			if (is_scalar($property))
			{
				$property = Automation\Helper::convertExpressions($property, $documentType);
			}
			elseif (is_array($property))
			{
				foreach ($property as $key => $value)
				{
					if (is_scalar($value))
					{
						$value = Automation\Helper::convertExpressions($value, $documentType);
					}
					$property[$key] = $value;
				}
			}
			$properties[$code] = $property;
		}
		return $properties;
	}

	private function unConvertRobotProperties(array $properties, array $documentType)
	{
		foreach ($properties as $code => $property)
		{
			if (is_array($property))
			{
				$properties[$code] = self::unConvertRobotProperties($property, $documentType);
			}
			else
			{
				$properties[$code] = Automation\Helper::unConvertExpressions($property, $documentType);
			}
		}
		return $properties;
	}

	private function getDocumentType()
	{
		return [$this->template['MODULE_ID'], $this->template['ENTITY'], $this->template['DOCUMENT_TYPE']];
	}
}