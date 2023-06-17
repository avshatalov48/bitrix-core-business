<?php

use Bitrix\Bizproc;
use Bitrix\Main;

abstract class CBPActivity
{
	use Bizproc\Debugger\Mixins\WriterDebugTrack;

	public $parent = null;

	public $executionStatus = CBPActivityExecutionStatus::Initialized;
	public $executionResult = CBPActivityExecutionResult::None;

	private $arStatusChangeHandlers = [];

	const StatusChangedEvent = 0;
	const ExecutingEvent = 1;
	const CancelingEvent = 2;
	const ClosedEvent = 3;
	const FaultingEvent = 4;

	private const ValueSinglePattern = '\{=\s*(?<object>[a-z0-9_]+)\s*\:\s*(?<field>[a-z0-9_\.]+)(\s*>\s*(?<mod1>[a-z0-9_\:]+)(\s*,\s*(?<mod2>[a-z0-9_]+))?)?\s*\}';

	const ValuePattern = '#^\s*'.self::ValueSinglePattern.'\s*$#i';
	private const ValueSimplePattern = '#^\s*\{\{(.*?)\}\}\s*$#i';
	const ValueInlinePattern = '#'.self::ValueSinglePattern.'#i';
	/** Internal pattern used in calc.php */
	const ValueInternalPattern = '\{=\s*([a-z0-9_]+)\s*\:\s*([a-z0-9_\.]+)(\s*>\s*([a-z0-9_\:]+)(\s*,\s*([a-z0-9_]+))?)?\s*\}';

	const CalcPattern = '#^\s*(=\s*(.*)|\{\{=\s*(.*)\s*\}\})\s*$#is';
	const CalcInlinePattern = '#\{\{=\s*(.*?)\s*\}\}([^\}]|$)#is';

	protected $arProperties = [];
	protected $arPropertiesTypes = [];

	protected $name = '';
	/** @var CBPWorkflow | \Bitrix\Bizproc\Debugger\Workflow\DebugWorkflow $workflow */
	public $workflow = null;

	public $arEventsMap = [];

	/************************  PROPERTIES  ************************************************/

	public function getDocumentId()
	{
		$rootActivity = $this->GetRootActivity();
		return $rootActivity->GetDocumentId();
	}

	public function setDocumentId($documentId)
	{
		$rootActivity = $this->GetRootActivity();
		$rootActivity->SetDocumentId($documentId);
	}

	public function getDocumentType()
	{
		$rootActivity = $this->GetRootActivity();
		if (!is_array($rootActivity->documentType) || count($rootActivity->documentType) <= 0)
		{
			/** @var CBPDocumentService $documentService */
			$documentService = $this->workflow->GetService("DocumentService");
			$rootActivity->documentType = $documentService->GetDocumentType($rootActivity->documentId);
		}
		return $rootActivity->documentType;
	}

	public function setDocumentType($documentType)
	{
		$rootActivity = $this->GetRootActivity();
		$rootActivity->documentType = $documentType;
	}

	public function getDocumentEventType()
	{
		$rootActivity = $this->GetRootActivity();
		return (int)$rootActivity->getRawProperty(CBPDocument::PARAM_DOCUMENT_EVENT_TYPE);
	}

	public function getWorkflowStatus()
	{
		$rootActivity = $this->GetRootActivity();
		return $rootActivity->GetWorkflowStatus();
	}

	public function setWorkflowStatus($status)
	{
		$rootActivity = $this->GetRootActivity();
		$rootActivity->SetWorkflowStatus($status);
	}

	public function setFieldTypes($arFieldTypes = array())
	{
		if (count($arFieldTypes) > 0)
		{
			$rootActivity = $this->GetRootActivity();
			foreach ($arFieldTypes as $key => $value)
				$rootActivity->arFieldTypes[$key] = $value;
		}
	}

	public function getWorkflowTemplateId()
	{
		$rootActivity = $this->GetRootActivity();
		//prevent recursion by checking setter
		if (method_exists($rootActivity, 'SetWorkflowTemplateId'))
		{
			return $rootActivity->GetWorkflowTemplateId();
		}

		return 0;
	}

	public function getTemplateUserId()
	{
		$userId = 0;
		$rootActivity = $this->GetRootActivity();
		//prevent recursion by checking setter
		if (method_exists($rootActivity, 'setTemplateUserId'))
		{
			$userId = $rootActivity->getTemplateUserId();
		}

		if (!$userId && $tplId = $this->GetWorkflowTemplateId())
		{
			$userId = CBPWorkflowTemplateLoader::getTemplateUserId($tplId);
		}

		return $userId;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [];
	}

	/**********************************************************/
	protected function clearProperties()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		$documentType = $this->GetDocumentType();
		/** @var CBPDocumentService $documentService */
		$documentService = $this->workflow->GetService("DocumentService");

		if (is_array($rootActivity->arPropertiesTypes) && count($rootActivity->arPropertiesTypes) > 0
			&& is_array($rootActivity->arFieldTypes) && count($rootActivity->arFieldTypes) > 0)
		{
			foreach ($rootActivity->arPropertiesTypes as $key => $value)
			{
				if ($rootActivity->arFieldTypes[$value["Type"]]["BaseType"] == "file")
				{
					foreach ((array) $rootActivity->__get($key) as $v)
					{
						if (intval($v) > 0)
						{
							$iterator = \CFile::getByID($v);
							if ($file = $iterator->fetch())
							{
								if ($file['MODULE_ID'] === 'bizproc')
									CFile::Delete($v);
							}
						}
					}
				}

				$fieldType = \Bitrix\Bizproc\FieldType::normalizeProperty($value);
				if ($fieldTypeObject = $documentService->getFieldTypeObject($documentType, $fieldType))
				{
					$fieldTypeObject->setDocumentId($documentId)
									->clearValue($rootActivity->arProperties[$key]);
				}
			}
		}
	}

	public function getPropertyBaseType($propertyName)
	{
		$rootActivity = $this->GetRootActivity();
		return $rootActivity->arFieldTypes[$rootActivity->arPropertiesTypes[$propertyName]["Type"]]["BaseType"];
	}

	public function getTemplatePropertyType($propertyName)
	{
		$rootActivity = $this->GetRootActivity();
		if ($propertyName === 'TargetUser' && !isset($rootActivity->arPropertiesTypes[$propertyName]))
		{
			return ['Type' => 'user'];
		}

		return $rootActivity->arPropertiesTypes[$propertyName];
	}

	public function setProperties($arProperties = array())
	{
		if (count($arProperties) > 0)
		{
			foreach ($arProperties as $key => $value)
			{
				$this->arProperties[$key] = $value;
			}
		}
	}

	public function setPropertiesTypes($arPropertiesTypes = array())
	{
		if (count($arPropertiesTypes) > 0)
		{
			foreach ($arPropertiesTypes as $key => $value)
			{
				$this->arPropertiesTypes[$key] = $value;
			}
		}
	}

	public function getPropertyType($propertyName): ?array
	{
		return $this->arPropertiesTypes[$propertyName] ?? null;
	}

	/**********************************************************/
	protected function clearVariables()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		$documentType = $this->GetDocumentType();
		/** @var CBPDocumentService $documentService */
		$documentService = $this->workflow->GetService("DocumentService");

		if (is_array($rootActivity->arVariablesTypes) && count($rootActivity->arVariablesTypes) > 0
			&& is_array($rootActivity->arFieldTypes) && count($rootActivity->arFieldTypes) > 0)
		{
			foreach ($rootActivity->arVariablesTypes as $key => $value)
			{
				if (
					isset($rootActivity->arFieldTypes[$value["Type"]])
					&& $rootActivity->arFieldTypes[$value["Type"]]["BaseType"] === "file"
				)
				{
					foreach ((array) $rootActivity->arVariables[$key] as $v)
					{
						if (intval($v) > 0)
						{
							$iterator = \CFile::getByID($v);
							if ($file = $iterator->fetch())
							{
								if ($file['MODULE_ID'] === 'bizproc')
									CFile::Delete($v);
							}
						}
					}
				}

				$fieldType = \Bitrix\Bizproc\FieldType::normalizeProperty($value);
				if ($fieldTypeObject = $documentService->getFieldTypeObject($documentType, $fieldType))
				{
					$fieldTypeObject->setDocumentId($documentId)
						->clearValue($rootActivity->arVariables[$key]);
				}
			}
		}
	}

	public function getVariableBaseType($variableName)
	{
		$rootActivity = $this->GetRootActivity();
		return $rootActivity->arFieldTypes[$rootActivity->arVariablesTypes[$variableName]["Type"]]["BaseType"];
	}

	public function setVariables($arVariables = array())
	{
		if (count($arVariables) > 0)
		{
			$rootActivity = $this->GetRootActivity();
			foreach ($arVariables as $key => $value)
				$rootActivity->arVariables[$key] = $value;
		}
	}

	public function setVariablesTypes($arVariablesTypes = array())
	{
		if (count($arVariablesTypes) > 0)
		{
			$rootActivity = $this->GetRootActivity();
			foreach ($arVariablesTypes as $key => $value)
				$rootActivity->arVariablesTypes[$key] = $value;
		}
	}

	public function setVariable($name, $value)
	{
		$rootActivity = $this->GetRootActivity();
		$rootActivity->arVariables[$name] = $value;
	}

	public function getVariable($name)
	{
		$rootActivity = $this->GetRootActivity();

		if (array_key_exists($name, $rootActivity->arVariables))
		{
			return $rootActivity->arVariables[$name];
		}

		return null;
	}

	public function getVariableType($name)
	{
		$rootActivity = $this->GetRootActivity();
		return isset($rootActivity->arVariablesTypes[$name]) ? $rootActivity->arVariablesTypes[$name] : null;
	}

	private function getConstantTypes()
	{
		$rootActivity = $this->GetRootActivity();
		if (method_exists($rootActivity, 'GetWorkflowTemplateId'))
		{
			$templateId = $rootActivity->GetWorkflowTemplateId();
			if ($templateId > 0)
			{
				return CBPWorkflowTemplateLoader::getTemplateConstants($templateId);
			}
		}
		return null;
	}

	public function getConstant($name)
	{
		$constants = $this->GetConstantTypes();
		if (isset($constants[$name]['Default']))
			return $constants[$name]['Default'];
		return null;
	}

	public function getConstantType($name)
	{
		$constants = $this->GetConstantTypes();
		if (isset($constants[$name]))
			return $constants[$name];
		return array('Type' => null, 'Multiple' => false, 'Required' => false, 'Options' => null);
	}

	public function isVariableExists($name)
	{
		$rootActivity = $this->GetRootActivity();
		$variables = $rootActivity->arVariables ?? [];
		$variablesTypes = $rootActivity->arVariablesTypes ?? [];

		return (
			array_key_exists($name, $variables)
			|| array_key_exists($name, $variablesTypes)
		);
	}

	/************************************************/
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return CBPCompositeActivity|CBPActivity|null
	 */
	public function getRootActivity()
	{
		$p = $this;
		while ($p->parent != null)
		{
			$p = $p->parent;
		}

		return $p;
	}

	public function setWorkflow(CBPWorkflow $workflow)
	{
		$this->workflow = $workflow;
	}

	public function unsetWorkflow()
	{
		$this->workflow = null;
	}

	public function getWorkflowInstanceId()
	{
		return $this->workflow->GetInstanceId();
	}

	public function setStatusTitle($title = '')
	{
		$rootActivity = $this->GetRootActivity();
		$stateService = $this->workflow->GetService("StateService");
		if ($rootActivity instanceof CBPStateMachineWorkflowActivity)
		{
			$arState = $stateService->GetWorkflowState($this->GetWorkflowInstanceId());

			$arActivities = $rootActivity->CollectNestedActivities();
			/** @var CBPActivity $activity */
			foreach ($arActivities as $activity)
				if ($activity->GetName() == $arState["STATE_NAME"])
					break;

			$stateService->SetStateTitle(
				$this->GetWorkflowInstanceId(),
				$activity->Title.($title != '' ? ": ".$title : '')
			);
		}
		else
		{
			if ($title != '')
			{
				$stateService->SetStateTitle(
					$this->GetWorkflowInstanceId(),
					$title
				);
			}
		}
	}

	public function addStatusTitle($title = '')
	{
		if ($title == '')
			return;

		$stateService = $this->workflow->GetService("StateService");

		$mainTitle = $stateService->GetStateTitle($this->GetWorkflowInstanceId());
		$mainTitle .= ((mb_strpos($mainTitle, ": ") !== false) ? ", " : ": ").$title;

		$stateService->SetStateTitle($this->GetWorkflowInstanceId(), $mainTitle);
	}

	public function deleteStatusTitle($title = '')
	{
		if ($title == '')
			return;

		$stateService = $this->workflow->GetService("StateService");
		$mainTitle = $stateService->GetStateTitle($this->GetWorkflowInstanceId());

		$ar1 = explode(":", $mainTitle);
		if (count($ar1) <= 1)
			return;

		$newTitle = "";

		$ar2 = explode(",", $ar1[1]);
		foreach ($ar2 as $a)
		{
			$a = trim($a);
			if ($a != $title)
			{
				if ($newTitle <> '')
					$newTitle .= ", ";
				$newTitle .= $a;
			}
		}

		$result = $ar1[0].($newTitle <> '' ? ": " : "").$newTitle;

		$stateService->SetStateTitle($this->GetWorkflowInstanceId(), $result);
	}

	private function getPropertyValueRecursive($val, $convertToType = null, ?callable $decorator = null)
	{
		// array(2, 5, array("SequentialWorkflowActivity1", "DocumentApprovers"))
		// array("Document", "IBLOCK_ID")
		// array("Workflow", "id")
		// "Hello, {=SequentialWorkflowActivity1:DocumentApprovers}, {=Document:IBLOCK_ID}!"

		$parsed = static::parseExpression($val);
		if ($parsed)
		{
			$result = null;
			if ($convertToType)
				$parsed['modifiers'][] = $convertToType;
			$this->getRealParameterValue(
				$parsed['object'],
				$parsed['field'],
				$result,
				$parsed['modifiers'],
				$decorator
			);
			return array(1, $result);
		}
		elseif (is_array($val))
		{
			$b = true;
			$r = array();

			$keys = array_keys($val);

			$i = 0;
			foreach ($keys as $key)
			{
				if ($key."!" != $i."!")
				{
					$b = false;
					break;
				}
				$i++;
			}

			foreach ($keys as $key)
			{
				[$t, $a] = $this->GetPropertyValueRecursive($val[$key], $convertToType, $decorator);
				if ($b)
				{
					if ($t == 1 && is_array($a))
						$r = array_merge($r, $a);
					else
						$r[] = $a;
				}
				else
				{
					$r[$key] = $a;
				}
			}

			if (count($r) == 2)
			{
				$keys = array_keys($r);
				if ($keys[0] == 0 && $keys[1] == 1 && is_string($r[0]) && is_string($r[1]))
				{
					$result = null;
					$modifiers = $convertToType ? array($convertToType) : array();
					if ($this->GetRealParameterValue($r[0], $r[1], $result, $modifiers, $decorator))
						return array(1, $result);
				}
			}
			return array(2, $r);
		}
		else
		{
			if (is_string($val))
			{
				$typeClass = null;
				$fieldTypeObject = null;
				if ($convertToType)
				{
					/** @var CBPDocumentService $documentService */
					$documentService = $this->workflow->GetService("DocumentService");
					$documentType = $this->GetDocumentType();

					$typesMap = $documentService->getTypesMap($documentType);
					$convertToType = mb_strtolower($convertToType);
					if (isset($typesMap[$convertToType]))
					{
						$typeClass = $typesMap[$convertToType];
						$fieldTypeObject = $documentService->getFieldTypeObject(
							$documentType,
							array('Type' => \Bitrix\Bizproc\FieldType::STRING)
						);
					}
				}

				$calc = new Bizproc\Calc\Parser($this);
				if (preg_match(self::CalcPattern, $val))
				{
					$r = $calc->Calculate($val);
					if ($r !== null)
					{
						if ($typeClass && $fieldTypeObject)
						{
							if (is_array($r))
								$fieldTypeObject->setMultiple(true);
							$r = $fieldTypeObject->convertValue($r, $typeClass);
						}
						return array(is_array($r)? 1 : 2, $r);
					}
				}

				//parse inline calculator
				$val = preg_replace_callback(
					static::CalcInlinePattern,
					function($matches) use ($calc)
					{
						$r = $calc->Calculate($matches[1]);
						if (is_array($r))
							$r = implode(', ', CBPHelper::MakeArrayFlat($r));
						return $r !== null? $r.$matches[2] : $matches[0];
					},
					$val
				);

				//parse properties
				$val = preg_replace_callback(
					static::ValueInlinePattern,
					fn($matches) => $this->parseStringParameter($matches, $convertToType, $decorator),
					$val
				);

				//converting...
				if ($typeClass && $fieldTypeObject)
				{
					$val = $fieldTypeObject->convertValue($val, $typeClass);
				}
			}

			return array(2, $val);
		}
	}

	private function getRealParameterValue(
		$objectName,
		$fieldName,
		&$result,
		array $modifiers = null,
		?callable $decorator = null
	)
	{
		$return = true;
		$property = null;
		/** @var CBPDocumentService $documentService */
		$documentService = $this->workflow->GetService("DocumentService");

		if ($objectName == "Document")
		{
			$rootActivity = $this->GetRootActivity();
			$documentId = $rootActivity->GetDocumentId();

			$documentType = $this->GetDocumentType();
			$document = $documentService->GetDocument($documentId, $documentType);
			$documentFields = $documentService->GetDocumentFields($documentType);
			//check aliases
			$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);
			if (!isset($document[$fieldName]) && mb_strtoupper(mb_substr($fieldName, -10)) === '_PRINTABLE')
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				if (!in_array('printable', $modifiers))
				{
					$modifiers[] = 'printable';
				}
			}
			if (!isset($document[$fieldName]) && isset($documentFieldsAliasesMap[$fieldName]))
			{
				$fieldName = $documentFieldsAliasesMap[$fieldName];
			}

			$result = '';

			if (isset($document[$fieldName]))
			{
				$result = $document[$fieldName];
				if (is_array($result) && mb_strtoupper(mb_substr($fieldName, -10)) === '_PRINTABLE')
				{
					$result = implode(", ", CBPHelper::MakeArrayFlat($result));
				}

				$property = isset($documentFields[$fieldName]) ? $documentFields[$fieldName] : null;
			}
		}
		elseif (in_array($objectName, ['Template', 'Variable', 'Constant']))
		{
			$rootActivity = $this->GetRootActivity();

			if (mb_substr($fieldName, -10) == "_printable")
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				$modifiers = ['printable'];
			}

			switch ($objectName)
			{
				case 'Variable':
					$result = $rootActivity->GetVariable($fieldName);
					$property = $rootActivity->getVariableType($fieldName);
					break;
				case 'Constant':
					$result = $rootActivity->GetConstant($fieldName);
					$property = $rootActivity->GetConstantType($fieldName);
					break;
				default:
					$result = $rootActivity->__get($fieldName);
					$property = $rootActivity->getTemplatePropertyType($fieldName);
			}
		}
		elseif ($objectName === 'GlobalConst')
		{
			$property = Bizproc\Workflow\Type\GlobalConst::getById($fieldName);
			if (!$property && mb_substr($fieldName, -10) == "_printable")
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				$modifiers = ['printable'];
				$property = Bizproc\Workflow\Type\GlobalConst::getById($fieldName);
			}

			$result = Bizproc\Workflow\Type\GlobalConst::getValue($fieldName);
		}
		elseif ($objectName === 'GlobalVar')
		{
			$property = Bizproc\Workflow\Type\GlobalVar::getById($fieldName);
			if (!$property && mb_substr($fieldName, -10) == "_printable")
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				$modifiers = ['printable'];
				$property = Bizproc\Workflow\Type\GlobalVar::getById($fieldName);
			}

			$result = Bizproc\Workflow\Type\GlobalVar::getValue($fieldName);
		}
		elseif ($objectName == "Workflow")
		{
			$result = $this->GetWorkflowInstanceId();
			$property = array('Type' => 'string');
		}
		elseif ($objectName == "User")
		{
			if (mb_substr($fieldName, -10) == "_printable")
			{
				$modifiers = ['printable'];
			}

			$result = 0;
			if (isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->isAuthorized())
			{
				$result = "user_".$GLOBALS["USER"]->GetID();
			}
			$property = array('Type' => 'user');
		}
		elseif ($objectName == "System")
		{
			if (mb_substr($fieldName, -10) == "_printable")
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				$modifiers = ['printable'];
			}

			$result = null;
			$property = array('Type' => 'datetime');
			$systemField = mb_strtolower($fieldName);
			if ($systemField === 'now')
			{
				$result = new Bizproc\BaseType\Value\DateTime();
			}
			elseif ($systemField === 'nowlocal')
			{
				$result = new Bizproc\BaseType\Value\DateTime(time(), CTimeZone::GetOffset());
			}
			elseif ($systemField == 'date')
			{
				$result = new Bizproc\BaseType\Value\Date();
				$property = array('Type' => 'date');
			}
			elseif ($systemField === 'eol')
			{
				$result = PHP_EOL;
				$property = ['Type' => 'string'];
			}
			elseif ($systemField === 'hosturl')
			{
				$result = Main\Engine\UrlManager::getInstance()->getHostUrl();
				$property = ['Type' => 'string'];
			}

			if ($result === null)
			{
				$return = false;
			}
		}
		elseif ($objectName)
		{
			$activity = $this->workflow->GetActivityByName($objectName);
			if ($activity)
			{
				$result = $activity->__get($fieldName);
				$property = $activity->getPropertyType($fieldName);
			}
			else
				$return = false;
		}
		else
			$return = false;

		if ($property && $result)
		{
			$fieldTypeObject = $documentService->getFieldTypeObject($this->GetDocumentType(), $property);
			if ($fieldTypeObject)
			{
				$fieldTypeObject->setDocumentId($this->GetDocumentId());
				$result = $fieldTypeObject->internalizeValue($objectName, $result);
			}
		}

		if ($return)
		{
			$result = $this->applyPropertyValueModifiers($fieldName, $property, $result, $modifiers);

			if ($decorator)
			{
				$result = $decorator($objectName, $fieldName, $property, $result);
			}
		}
		return $return;
	}

	public function getRuntimeProperty($object, $field, CBPActivity $ownerActivity): array
	{
		$rootActivity = $ownerActivity->getRootActivity();
		$documentType = $rootActivity->getDocumentType();
		$documentId = $rootActivity->getDocumentId();
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->GetDocumentFields($documentType);

		$result = null;
		$property = null;

		if (CBPHelper::isEmptyValue($object))
		{
			return [$property, $result];
		}
		elseif ($object === 'Template' || $object === Bizproc\Workflow\Template\SourceType::Parameter)
		{
			$result = $rootActivity->__get($field);
			$property = $rootActivity->getTemplatePropertyType($field);
		}
		elseif ($object === Bizproc\Workflow\Template\SourceType::Variable)
		{
			$result = $rootActivity->getVariable($field);
			$property = $rootActivity->getVariableType($field);
		}
		elseif ($object === Bizproc\Workflow\Template\SourceType::Constant)
		{
			$result = $rootActivity->getConstant($field);
			$property = $rootActivity->getConstantType($field);
		}
		elseif ($object === Bizproc\Workflow\Template\SourceType::GlobalConstant)
		{
			$result = Bizproc\Workflow\Type\GlobalConst::getValue($field);
			$property = Bizproc\Workflow\Type\GlobalConst::getVisibleById($field, $documentType);
		}
		elseif ($object === Bizproc\Workflow\Template\SourceType::GlobalVariable)
		{
			$result = Bizproc\Workflow\Type\GlobalVar::getValue($field);
			$property = Bizproc\Workflow\Type\GlobalVar::getVisibleById($field, $documentType);
		}
		elseif ($object === Bizproc\Workflow\Template\SourceType::DocumentField)
		{
			$property = $documentFields[$field] ?? null;
			$result = $documentService->getFieldValue($documentId, $field, $documentType);
		}
		else
		{
			$activity = $rootActivity->workflow->getActivityByName($object);
			if ($activity)
			{
				$result = $activity->__get($field);
				$property = $activity->getPropertyType($field);
			}
		}

		if (!$property)
		{
			$property = ['Type' => 'string'];
		}

		return [$property, $result];
	}

	private function applyPropertyValueModifiers($fieldName, $property, $value, array $modifiers)
	{
		if (empty($property) || empty($modifiers) || !is_array($property))
			return $value;

		$typeName = null;
		$typeClass = null;
		$format = null;
		$modifiers = array_slice($modifiers, 0, 2);

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		/** @var CBPDocumentService $documentService */
		$documentService = $this->workflow->GetService("DocumentService");
		$documentType = $this->GetDocumentType();

		$typesMap = $documentService->getTypesMap($documentType);
		foreach ($modifiers as $m)
		{
			$m = mb_strtolower($m);
			if (isset($typesMap[$m]))
			{
				$typeName = $m;
				$typeClass = $typesMap[$m];
			}
			else
			{
				$format = $m;
			}
		}

		$priority = $format && array_search($format, $modifiers) === 0 ? 'format' : 'type';

		if ($typeName === \Bitrix\Bizproc\FieldType::STRING && $format === 'printable')
		{
			$typeClass = null;
		}

		if ($typeClass || $format)
		{
			$fieldTypeObject = $documentService->getFieldTypeObject($documentType, $property);

			if ($fieldTypeObject)
			{
				$fieldTypeObject->setDocumentId($documentId);

				if ($format && $priority === 'format')
				{
					$value = $fieldTypeObject->formatValue($value, $format);
				}

				if ($typeClass)
				{
					$value = $fieldTypeObject->convertValue($value, $typeClass);
				}

				if ($format && $priority !== 'format')
				{
					$value = $fieldTypeObject->formatValue($value, $format);
				}
			}
			elseif ($format == 'printable') // compatibility: old printable style
			{
				$value = $documentService->GetFieldValuePrintable(
					$documentId,
					$fieldName,
					$property['Type'],
					$value,
					$property
				);

				if (is_array($value))
					$value = implode(", ", CBPHelper::MakeArrayFlat($value));
			}
		}

		return $value;
	}

	private function parseStringParameter($matches, $convertToType = null, ?callable $decorator = null)
	{
		$result = "";
		$modifiers = [];
		if (!empty($matches['mod1']))
		{
			$modifiers[] = $matches['mod1'];
		}
		if (!empty($matches['mod2']))
		{
			$modifiers[] = $matches['mod2'];
		}
		if ($convertToType)
		{
			$modifiers[] = $convertToType;
		}

		if (empty($modifiers))
		{
			$modifiers[] = \Bitrix\Bizproc\FieldType::STRING;
		}

		if ($this->getRealParameterValue($matches['object'], $matches['field'], $result, $modifiers, $decorator))
		{
			if (is_array($result))
			{
				$result = implode(", ", CBPHelper::MakeArrayFlat($result));
			}
		}
		else
		{
			$result = $matches[0];
		}

		return $result;
	}

	public function parseValue($value, $convertToType = null, ?callable $decorator = null)
	{
		[$t, $r] = $this->getPropertyValueRecursive($value, $convertToType, $decorator);

		return $r;
	}

	protected function getRawProperty($name)
	{
		if (isset($this->arProperties[$name]))
		{
			return $this->arProperties[$name];
		}
		else
		{
			$ro = $this->getRootActivity()->getReadOnlyData();
			if (isset($ro[$this->getName()]) && isset($ro[$this->getName()][$name]))
			{
				return $ro[$this->getName()][$name];
			}
		}

		return null;
	}

	public function __get($name)
	{
		$property = $this->getRawProperty($name);
		if ($property !== null)
		{
			[$t, $r] = $this->GetPropertyValueRecursive($property);
			return $r;
		}
		return null;
	}

	public function __isset($name)
	{
		return $this->isPropertyExists($name);
	}

	public function pullProperties(): array
	{
		$result = $this->arProperties;
		$this->arProperties = array_fill_keys(array_keys($this->arProperties), null);

		return [$this->getName() => $result];
	}

	public function __set($name, $val)
	{
		if (array_key_exists($name, $this->arProperties))
		{
			$this->arProperties[$name] = $val;
		}
	}

	public function isPropertyExists($name)
	{
		return array_key_exists($name, $this->arProperties);
	}

	public function collectNestedActivities()
	{
		return null;
	}

	public function collectUsages()
	{
		$usages = [];
		$this->collectUsagesRecursive($this->arProperties, $usages);
		return $usages;
	}

	protected function collectUsagesRecursive($val, &$usages)
	{
		if (is_array($val))
		{
			foreach ($val as $v)
			{
				$this->collectUsagesRecursive($v, $usages);
			}
		}
		elseif (is_string($val))
		{
			$parsed = static::parseExpression($val);
			if ($parsed)
			{
				$usages[] = $this->getObjectSourceType($parsed['object'], $parsed['field']);
			}
			else
			{
				//TODO: check calc functions
				/*$calc = new CBPCalc($this);
				if (preg_match(self::CalcPattern, $val))
				{
					$r = $calc->Calculate($val);

				}

				//parse inline calculator
				$val = preg_replace_callback(
					static::CalcInlinePattern,
					function($matches) use ($calc)
					{
						$r = $calc->Calculate($matches[1]);

					},
					$val
				);*/

				//parse properties
				$val = preg_replace_callback(
					static::ValueInlinePattern,
					function($matches) use (&$usages)
					{
						$usages[] = $this->getObjectSourceType($matches['object'], $matches['field']);
					},
					$val
				);
			}
		}
	}

	protected function getObjectSourceType($objectName, $fieldName)
	{
		return \Bitrix\Bizproc\Workflow\Template\SourceType::getObjectSourceType($objectName, $fieldName);
	}

	/************************  CONSTRUCTORS  *****************************************************/

	public function __construct($name)
	{
		$this->name = $name;
	}

	/************************  DEBUG  ***********************************************************/

	public function toString()
	{
		return $this->name.
			" [".get_class($this)."] (status=".
			CBPActivityExecutionStatus::Out($this->executionStatus).
			", result=".
			CBPActivityExecutionResult::Out($this->executionResult).
			", count(ClosedEvent)=".
			count($this->arStatusChangeHandlers[self::ClosedEvent]).
			")";
	}

	public function dump($level = 3)
	{
		$result = str_repeat("	", $level).$this->ToString()."\n";

		if (is_subclass_of($this, "CBPCompositeActivity"))
		{
			/** @var CBPActivity $activity */
			foreach ($this->arActivities as $activity)
				$result .= $activity->Dump($level + 1);
		}

		return $result;
	}

	/************************  PROCESS  ***********************************************************/

	public function initialize()
	{
	}

	public function finalize()
	{
	}

	public function execute()
	{
		return CBPActivityExecutionStatus::Closed;
	}

	protected function reInitialize()
	{
		$this->executionStatus = CBPActivityExecutionStatus::Initialized;
		$this->executionResult = CBPActivityExecutionResult::None;
	}

	public function cancel()
	{
		return CBPActivityExecutionStatus::Closed;
	}

	public function handleFault(Exception $exception)
	{
		$status = $this->cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
		{
			return CBPActivityExecutionStatus::Faulting;
		}

		return $status;
	}

	/************************  LOAD / SAVE  *******************************************************/

	public function fixUpParentChildRelationship(CBPActivity $nestedActivity)
	{
		$nestedActivity->parent = $this;
	}

	public static function load($stream)
	{
		if ($stream == '')
			throw new Exception("stream");

		return CBPRuntime::GetRuntime()->unserializeWorkflowStream($stream);
	}

	protected function getACNames()
	{
		return array(mb_substr(get_class($this), 3));
	}

	private static function searchUsedActivities(CBPActivity $activity, &$arUsedActivities)
	{
		$arT = $activity->GetACNames();
		foreach ($arT as $t)
		{
			if (!in_array($t, $arUsedActivities))
			{
				$arUsedActivities[] = $t;
			}
		}

		if ($arNestedActivities = $activity->CollectNestedActivities())
		{
			foreach ($arNestedActivities as $nestedActivity)
			{
				self::SearchUsedActivities($nestedActivity, $arUsedActivities);
			}
		}
	}

	public function save()
	{
		$usedActivities = [];
		self::SearchUsedActivities($this, $usedActivities);

		if ($children = $this->collectNestedActivities())
		{
			/** @var CBPActivity $child */
			foreach ($children as $child)
			{
				$child->unsetWorkflow();
			}
		}

		$strUsedActivities = implode(",", $usedActivities);
		return $strUsedActivities.";".serialize($this);
	}

	/************************  STATUS CHANGE HANDLERS  **********************************************/

	public function addStatusChangeHandler($event, $eventHandler)
	{
		if (!is_array($this->arStatusChangeHandlers))
			$this->arStatusChangeHandlers = array();

		if (!array_key_exists($event, $this->arStatusChangeHandlers))
			$this->arStatusChangeHandlers[$event] = array();

		$this->arStatusChangeHandlers[$event][] = $eventHandler;
	}

	public function removeStatusChangeHandler($event, $eventHandler)
	{
		if (!is_array($this->arStatusChangeHandlers))
			$this->arStatusChangeHandlers = array();

		if (!array_key_exists($event, $this->arStatusChangeHandlers))
			$this->arStatusChangeHandlers[$event] = array();

		$index = array_search($eventHandler, $this->arStatusChangeHandlers[$event], true);

		if ($index !== false)
			unset($this->arStatusChangeHandlers[$event][$index]);
	}

	/************************  EVENTS  **********************************************************************/

	private function fireStatusChangedEvents($event, $arEventParameters = array())
	{
		if (array_key_exists($event, $this->arStatusChangeHandlers) && is_array($this->arStatusChangeHandlers[$event]))
		{
			foreach ($this->arStatusChangeHandlers[$event] as $eventHandler)
				call_user_func_array(array($eventHandler, "OnEvent"), array($this, $arEventParameters));
		}
	}

	public function setStatus($newStatus, $arEventParameters = array())
	{
		$this->executionStatus = $newStatus;
		$this->FireStatusChangedEvents(self::StatusChangedEvent, $arEventParameters);

		switch ($newStatus)
		{
			case CBPActivityExecutionStatus::Executing:
				$this->FireStatusChangedEvents(self::ExecutingEvent, $arEventParameters);
				break;

			case CBPActivityExecutionStatus::Canceling:
				$this->FireStatusChangedEvents(self::CancelingEvent, $arEventParameters);
				break;

			case CBPActivityExecutionStatus::Closed:
				$this->FireStatusChangedEvents(self::ClosedEvent, $arEventParameters);
				break;

			case CBPActivityExecutionStatus::Faulting:
				$this->FireStatusChangedEvents(self::FaultingEvent, $arEventParameters);
				break;

			default:
				return;
		}
	}

	/************************  CREATE  *****************************************************************/

	public static function includeActivityFile($code)
	{
		$runtime = CBPRuntime::GetRuntime();
		return $runtime->IncludeActivityFile($code);
	}

	public static function createInstance($code, $data)
	{
		if (preg_match("#[^a-zA-Z0-9_]#", $code))
			throw new Exception("Activity '".$code."' is not valid");

		$classname = 'CBP'.$code;
		if (class_exists($classname))
			return new $classname($data);
		else
			return null;
	}

	public static function callStaticMethod($code, $method, $arParameters = array())
	{
		$runtime = CBPRuntime::GetRuntime();
		if (!$runtime->IncludeActivityFile($code))
		{
			return [
				[
					"code" => "ActivityNotFound",
					"parameter" => $code,
					"message" => GetMessage("BPGA_ACTIVITY_NOT_FOUND_1", ['#ACTIVITY#' => htmlspecialcharsbx($code)])
				]
			];
		}

		if (preg_match("#[^a-zA-Z0-9_]#", $code))
		{
			throw new Exception("Activity '".$code."' is not valid");
		}

		$classname = 'CBP'.$code;

		if (method_exists($classname,$method))
		{
			return call_user_func_array(array($classname, $method), $arParameters);
		}

		return false;
	}

	public function initializeFromArray($arParams)
	{
		if (is_array($arParams))
		{
			foreach ($arParams as $key => $value)
			{
				if (array_key_exists($key, $this->arProperties))
					$this->arProperties[$key] = $value;
			}
		}
	}

	/************************  MARK  ****************************************************************/

	public function markCanceled($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			if ($this->executionStatus != CBPActivityExecutionStatus::Canceling)
				throw new Exception("InvalidCancelActivityState");

			$this->executionResult = CBPActivityExecutionResult::Canceled;
			$this->MarkClosed($arEventParameters);
		}
	}

	public function markCompleted($arEventParameters = array())
	{
		$this->executionResult = CBPActivityExecutionResult::Succeeded;
		$this->MarkClosed($arEventParameters);
	}

	public function markFaulted($arEventParameters = array())
	{
		$this->executionResult = CBPActivityExecutionResult::Faulted;
		$this->MarkClosed($arEventParameters);
	}

	private function markClosed($arEventParameters = array())
	{
		switch ($this->executionStatus)
		{
			case CBPActivityExecutionStatus::Executing:
			case CBPActivityExecutionStatus::Canceling:
			case CBPActivityExecutionStatus::Faulting:
			{
				if (is_subclass_of($this, "CBPCompositeActivity"))
				{
					foreach ($this->arActivities as $activity)
					{
						if (($activity->executionStatus != CBPActivityExecutionStatus::Initialized)
							&& ($activity->executionStatus != CBPActivityExecutionStatus::Closed))
						{
							throw new Exception("ActiveChildExist");
						}
					}
				}

				/** @var CBPTrackingService $trackingService */
				$trackingService = $this->workflow->GetService("TrackingService");
				$trackingService->Write($this->GetWorkflowInstanceId(), CBPTrackingType::CloseActivity, $this->name, $this->executionStatus, $this->executionResult, ($this->IsPropertyExists("Title") ? $this->Title : ""));
				$this->SetStatus(CBPActivityExecutionStatus::Closed, $arEventParameters);

				return;
			}
		}

		throw new Exception("InvalidCloseActivityState");
	}

	protected function writeToTrackingService($message = "", $modifiedBy = 0, $trackingType = -1)
	{
		/** @var CBPTrackingService $trackingService */
		$trackingService = $this->workflow->GetService("TrackingService");
		if ($trackingType < 0)
			$trackingType = CBPTrackingType::Custom;
		$trackingService->Write($this->GetWorkflowInstanceId(), $trackingType, $this->name, $this->executionStatus, $this->executionResult, ($this->IsPropertyExists("Title") ? $this->Title : ""), $message, $modifiedBy);
	}

	protected function trackError(string $errorMsg)
	{
		$this->writeToTrackingService($errorMsg, 0, \CBPTrackingType::Error);
	}

	protected function getDebugInfo(array $values = [], array $map = []): array
	{
		if (count($map) <= 0)
		{
			$map = static::getPropertiesMap($this->getDocumentType());
		}

		foreach ($map as $key => &$property)
		{
			if (is_string($property))
			{
				$property = [
					'Name' => $property,
					'Type' => 'string',
				];
			}

			if (!array_key_exists('TrackType', $property))
			{
				$property['TrackType'] = CBPTrackingType::Debug;
			}

			if (array_key_exists('TrackValue', $property))
			{
				continue;
			}

			if (!array_key_exists($key, $values))
			{
				$property['TrackValue'] = $this->__get($key);

				continue;
			}

			$property['TrackValue'] = $values[$key];
		}

		return $map;
	}

	protected function writeDebugInfo(array $map)
	{
		/** @var CBPDocumentService $documentService */
		$documentService = $this->workflow->GetService("DocumentService");

		foreach ($map as $property)
		{
			if (is_string($property))
			{
				$property = [
					'Name' => $property,
					'Type' => 'string',
				];
			}

			$fieldType = $documentService->getFieldTypeObject($this->getDocumentType(), $property);
			if (!$fieldType)
			{
				if (!array_key_exists('BaseType', $property))
				{
					continue;
				}
				$property['Type'] = $property['BaseType'];
				$fieldType = $documentService->getFieldTypeObject($this->getDocumentType(), $property);

				if (!$fieldType)
				{
					continue;
				}
			}

			$value = $fieldType->formatValue($property['TrackValue']);
			$value = ($value !== '') ? $value : '[]';

			$this->writeDebugTrack(
				$this->getWorkflowInstanceId(),
				$this->getName(),
				$this->executionStatus,
				$this->executionResult,
				$this->getTitle(),
				$this->preparePropertyForWritingToTrack($value, $property['Name'] ?? ''),
				$property['TrackType'] ?? \CBPTrackingType::Debug
			);
		}
	}

	protected function getTitle(): string
	{
		$activityTitle = $this->isPropertyExists('Title') ? $this->Title : '';

		if (is_string($activityTitle))
		{
			return $activityTitle;
		}

		return '';
	}

	public static function validateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		return array();
	}

	public static function validateChild($childActivity, $bFirstChild = false)
	{
		return array();
	}

	public static function &findActivityInTemplate(&$arWorkflowTemplate, $activityName)
	{
		return CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
	}

	public static function isExpression($text)
	{
		if (is_string($text))
		{
			$text = trim($text);
			if (
				preg_match(static::CalcPattern, $text)
				|| preg_match(static::ValuePattern, $text)
				|| preg_match(self::ValueSimplePattern, $text)
			)
			{
				return true;
			}
		}

		return false;
	}

	public static function parseExpression($exp): ?array
	{
		$matches = null;
		if (is_string($exp) && preg_match(static::ValuePattern, $exp, $matches))
		{
			$result = [
				'object' => $matches['object'],
				'field' => $matches['field'],
				'modifiers' => [],
			];
			if (!empty($matches['mod1']))
			{
				$result['modifiers'][] = $matches['mod1'];
			}
			if (!empty($matches['mod2']))
			{
				$result['modifiers'][] = $matches['mod2'];
			}

			return $result;
		}
		return null;
	}

	protected function getStorage(): Bizproc\Storage\ActivityStorage
	{
		return $this->getStorageFactory()->getActivityStorage($this);
	}

	private function getStorageFactory(): Bizproc\Storage\Factory
	{
		return Bizproc\Storage\Factory::getInstance();
	}
}
