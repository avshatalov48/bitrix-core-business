<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Result;
use Bitrix\Bizproc\Automation;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class Template
 * @package Bitrix\Bizproc\Automation\Engine
 */
class Template
{
	protected static $parallelActivityType = 'ParallelActivity';
	protected static $sequenceActivityType = 'SequenceActivity';
	/** @deprecated @var string $delayActivityType  */
	protected static $delayActivityType = 'DelayActivity';
	protected static $robotDelayActivityType = 'RobotDelayActivity';
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
			'VARIABLES' => [],
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
				],
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

	public function deleteRobots(array $robots, int $userId): Result
	{
		$isSameRobot = function ($lhsRobot, $rhsRobot) {
			return strcmp($lhsRobot->getName(), $rhsRobot->getName());
		};

		$remainingRobots = array_udiff($this->getRobots(), $robots, $isSameRobot);

		return $this->save($remainingRobots, $userId);
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

	public function getParameters(): array
	{
		return $this->template['PARAMETERS'] ?? [];
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
				$robot['Name'] ?? null, //activityName
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
				$robot['Name'] ?? null,
				&$raw,
				&$v,
				&$p,
				$request,
				&$robotErrors,
			]
		);

		if ($result)
		{
			$templateActivity = \CBPWorkflowTemplateLoader::findActivityByName($raw, $robot['Name'] ?? null);

			$robotTitle = $robot['Properties']['Title'] ?? null;
			$robot['Properties'] = $templateActivity['Properties'];
			$robot['Properties']['Title'] = $robotTitle;

			$saveResult->setData(array('robot' => $robot));
		}
		else
		{
			foreach ($robotErrors as $i => $error)
			{
				$errorMessage = $error['message'] ?? null;
				$errorCode = $error['code'] ?? null;
				$errorParameter = $error['parameter'] ?? null;
				$saveResult->addError(new Error($errorMessage, $errorCode, ['parameter' => $errorParameter]));
			}
		}

		return $saveResult;
	}

	public function save(array $robots, $userId, array $additional = [])
	{
		$userId = (int)$userId;
		$result = new Result();
		$templateId = !empty($this->template['ID']) ? $this->template['ID'] : 0;

		if (isset($additional['PARAMETERS']) && is_array($additional['PARAMETERS']))
		{
			$this->template['PARAMETERS'] = $additional['PARAMETERS'];
		}
		if (isset($additional['CONSTANTS']) && is_array($additional['CONSTANTS']))
		{
			$this->template['CONSTANTS'] = $additional['CONSTANTS'];
		}

		if ($templateId)
		{
			$templateResult = $this->updateTemplateRobots($robots, $userId);
		}
		else
		{
			$this->setRobots($robots);
			$templateResult = $this->addBizprocTemplate($userId);
		}

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
			'VARIABLES' => $this->template['VARIABLES'] ?? [],
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
		$msg = Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_NAME', [
			'#STATUS#' => $this->template['DOCUMENT_STATUS']
		]);

		if ($this->autoExecuteType === \CBPDocumentEventType::Script)
		{
			$msg = Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_SCRIPT_NAME');
		}

		return $msg;
	}

	private function updateTemplateRobots(array $robots, int $userId): Result
	{
		$templateId = $this->template['ID'];
		$result = new Result();

		$errors = $this->validateUpdatedRobots($robots, new \CBPWorkflowTemplateUser($userId));
		if (!$errors->isEmpty())
		{
			$result->addErrors($errors->getValues());

			return $result;
		}

		$this->setRobots($robots);
		$updateFields = [
			'TEMPLATE' => $this->template['TEMPLATE'],
			'PARAMETERS' => $this->template['PARAMETERS'],
			'VARIABLES' => [],
			'CONSTANTS' => $this->template['CONSTANTS'],
			'USER_ID' => $userId,
			'MODIFIER_USER' => new \CBPWorkflowTemplateUser($userId),
		];

		if (isset($this->template['NAME']))
		{
			$updateFields['NAME'] = $this->template['NAME'];
		}

		try
		{
			\CBPWorkflowTemplateLoader::update($templateId, $updateFields, false, false);
		}
		catch (\Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	private function validateUpdatedRobots(array $robots, \CBPWorkflowTemplateUser $user): ErrorCollection
	{
		$errors = new ErrorCollection();
		$loader = \CBPWorkflowTemplateLoader::GetLoader();
		$originalRobots = $this->getRobots();

		$isSameRobot = function ($lhsRobot, $rhsRobot) {
			return $lhsRobot->getName() === $rhsRobot->getName();
		};

		/**@var Robot $robot */
		foreach ($robots as $robot)
		{
			if (is_array($robot))
			{
				$robot = new Robot($robot);
			}
			if (!($robot instanceof Robot))
			{
				$errors->setError(new Error('Robots array is incorrect'));
			}
			if (!$errors->isEmpty())
			{
				break;
			}

			$indexOfFoundRobot = -1;
			foreach ($originalRobots as $index => $originalRobot)
			{
				if ($isSameRobot($robot, $originalRobot))
				{
					$indexOfFoundRobot = $index;
					break;
				}
			}

			if ($indexOfFoundRobot < 0 || !$this->areRobotsEqual($robot, $originalRobots[$indexOfFoundRobot]))
			{
				$sequence = $this->convertRobotToSequenceActivity($robot);
				foreach ($loader->ValidateTemplate($sequence, $user) as $rawError)
				{
					$errors->setError(new Error(trim($rawError['message'])));
				}
				unset($originalRobots[$indexOfFoundRobot]);
			}
		}

		return $errors;
	}

	private function areRobotsEqual(Robot $lhsRobot, Robot $rhsRobot): bool
	{
		$lhsCondition = $lhsRobot->getCondition() ?? new ConditionGroup();
		$rhsCondition = $rhsRobot->getCondition() ?? new ConditionGroup();

		$lhsDelay = $lhsRobot->getDelayInterval();
		$rhsDelay = $rhsRobot->getDelayInterval();
		if (!isset($lhsDelay) || $lhsDelay->isNow())
		{
			$lhsDelay = new DelayInterval();
		}
		if (!isset($rhsDelay) || $rhsDelay->isNow())
		{
			$rhsDelay = new DelayInterval();
		}

		return
			$lhsCondition->toArray()['items'] === $rhsCondition->toArray()['items']
			&& $lhsDelay->toArray() === $rhsDelay->toArray()
			&& $lhsRobot->getBizprocActivity() === $rhsRobot->getBizprocActivity()
		;
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
				if (
					$activity['Type'] === static::$delayActivityType
					|| $activity['Type'] === static::$robotDelayActivityType)
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
				'Children' => [],
			]],
			'PARAMETERS' => $this->template['PARAMETERS'],
			'CONSTANTS' => $this->template['CONSTANTS'],
			'SYSTEM_CODE'  => 'bitrix_bizproc_automation',
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
					$sequence = $this->convertRobotToSequenceActivity($robot);
				}
				else
				{
					$sequence['Children'] = array_merge(
						$sequence['Children'],
						$this->convertRobotToSequenceActivity($robot)['Children']
					);
				}
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

	private function convertRobotToSequenceActivity(Robot $robot): array
	{
		$sequence = $this->createSequenceActivity();

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
				$delayInterval->toActivityProperties($this->getDocumentType()),
				$delayName
			);
		}

		$activity = $robot->getBizprocActivity();
		$condition = $robot->getCondition();

		if ($condition && $condition->getItems())
		{
			$activity = $condition->createBizprocActivity($activity, $this->getDocumentType(), $this);
		}

		$sequence['Children'][] = $activity;

		return $sequence;
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

	public function getRobotsByNames(array $names): array
	{
		return array_uintersect($this->getRobots(), $names, function ($lhs, $rhs) {
			$lhsName = is_string($lhs) ? $lhs : $lhs->getName();
			$rhsName = is_string($rhs) ? $rhs : $rhs->getName();

			return strcmp($lhsName, $rhsName);
		});
	}

	/**
	 * @return array Template activities.
	 */
	public function getActivities()
	{
		return $this->template['TEMPLATE'];
	}

	public function getModified(): ?DateTime
	{
		return $this->template['MODIFIED'] ?? null;
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
			case 'Variable':
				return $this->template['VARIABLES'][$field] ?? null;
			case 'Constant':
				return $this->template['CONSTANTS'][$field] ?? null;
			case 'GlobalConst':
				return Bizproc\Workflow\Type\GlobalConst::getVisibleById($field, $this->getDocumentType());
			case 'GlobalVar':
				return Bizproc\Workflow\Type\GlobalVar::getVisibleById($field, $this->getDocumentType());
			case 'Document':
				static $fields;
				if (!$fields)
				{
					$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
					$fields = $documentService->GetDocumentFields($this->getDocumentType());
				}

				return $fields[$field] ?? null;
			default:
				if ($this->isConverted)
				{
					return $this->findRobotProperty($object, $field);
				}
				else
				{
					return $this->findActivityProperty($object, $field);
				}
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
			'Properties' => [
				'Title' => 'Automation sequence',
			],
			'Children' => [],
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
			'Children' => [],
		);
	}

	private function createDelayActivity(array $delayProperties, $delayName)
	{
		if (!isset($delayProperties['Title']))
		{
			$delayProperties['Title'] = Loc::getMessage('BIZPROC_AUTOMATION_ROBOT_DELAY_ACTIVITY');
		}

		return array(
			'Type' => static::$robotDelayActivityType,
			'Name' => $delayName,
			'Properties' => $delayProperties,
			'Children' => [],
		);
	}
}