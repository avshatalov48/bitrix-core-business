<?php

namespace Bitrix\Bizproc\Activity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ArgumentException;

class PropertiesDialog
{
	protected string $activityFile;
	protected string $dialogFileName = 'properties_dialog.php';
	protected ?array $map = null; //compatible Null
	protected $mapCallback;
	protected ?array $documentType = null; //compatible Null
	protected ?string $activityName = null; //compatible Null
	protected ?array $workflowTemplate = null; //compatible Null
	protected ?array $workflowParameters = null; //compatible Null
	protected ?array $workflowVariables = null; //compatible Null
	protected array $workflowConstants = [];
	protected mixed $currentValues = null; //compatible Null
	protected ?string $formName = null; //compatible Null
	protected ?string $siteId = null; //compatible Null
	protected $renderer;
	protected array $context = [];

	/** @var array */
	protected $runtimeData = [];

	public function __construct($activityFile, array $data = null)
	{
		$this->activityFile = $activityFile;

		if (is_array($data))
		{
			if (isset($data['documentType']) && is_array($data['documentType']))
			{
				$this->setDocumentType($data['documentType']);
			}
			if (isset($data['activityName']))
			{
				$this->setActivityName($data['activityName']);
			}
			if (isset($data['workflowTemplate']) && is_array($data['workflowTemplate']))
			{
				$this->setWorkflowTemplate($data['workflowTemplate']);
			}
			if (isset($data['workflowParameters']) && is_array($data['workflowParameters']))
			{
				$this->setWorkflowParameters($data['workflowParameters']);
			}
			if (isset($data['workflowVariables']) && is_array($data['workflowVariables']))
			{
				$this->setWorkflowVariables($data['workflowVariables']);
			}
			if (isset($data['workflowConstants']) && is_array($data['workflowConstants']))
			{
				$this->setWorkflowConstants($data['workflowConstants']);
			}
			if (isset($data['currentValues']) && is_array($data['currentValues']))
			{
				$this->setCurrentValues($data['currentValues']);
			}
			if (isset($data['formName']))
			{
				$this->setFormName($data['formName']);
			}
			if (isset($data['siteId']))
			{
				$this->setSiteId($data['siteId']);
			}
		}
	}

	public function getActivityFile(): string
	{
		return $this->activityFile;
	}

	public function setActivityFile(string $file): self
	{
		$this->activityFile = $file;

		return $this;
	}

	/**
	 * @return ?array
	 */
	public function getDocumentType()
	{
		return $this->documentType;
	}

	/**
	 * @param mixed $documentType
	 * @return $this
	 */
	public function setDocumentType(array $documentType)
	{
		$this->documentType = $documentType;

		return $this;
	}

	/**
	 * @param string $activityName
	 * @return PropertiesDialog
	 */
	public function setActivityName($activityName)
	{
		$this->activityName = (string)$activityName;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getActivityName()
	{
		return $this->activityName;
	}

	/**
	 * @param array $workflowTemplate
	 * @return PropertiesDialog
	 */
	public function setWorkflowTemplate(array $workflowTemplate)
	{
		$this->workflowTemplate = $workflowTemplate;

		return $this;
	}

	/**
	 * @return ?array
	 */
	public function getWorkflowTemplate()
	{
		return $this->workflowTemplate;
	}

	/**
	 * @param array $workflowParameters
	 * @return $this
	 */
	public function setWorkflowParameters(array $workflowParameters)
	{
		$this->workflowParameters = $workflowParameters;

		return $this;
	}

	/**
	 * @return ?array
	 */
	public function getWorkflowParameters()
	{
		return $this->workflowParameters;
	}

	/**
	 * @param array $workflowVariables
	 * @return $this
	 */
	public function setWorkflowVariables(array $workflowVariables)
	{
		$this->workflowVariables = $workflowVariables;

		return $this;
	}

	/**
	 * @return ?array
	 */
	public function getWorkflowVariables()
	{
		return $this->workflowVariables;
	}

	public function setWorkflowConstants(array $workflowConstants): static
	{
		$this->workflowConstants = $workflowConstants;

		return $this;
	}

	public function getWorkflowConstants(): array
	{
		return $this->workflowConstants;
	}

	/**
	 * @param bool $compatible
	 * @return array
	 */
	public function getCurrentValues($compatible = false)
	{
		$workflowTemplate = $this->getWorkflowTemplate();
		if (!is_array($this->currentValues))
		{
			// Get current values from saved activity properties.
			$this->currentValues = [];
			$currentActivity = \CBPWorkflowTemplateLoader::findActivityByName(
				$workflowTemplate,
				$this->getActivityName()
			);

			if (is_array($currentActivity) && isset($currentActivity['Properties']) && is_array($currentActivity['Properties']))
			{
				$map = $this->getMap();
				foreach ($map as $id => $property)
				{
					if (!isset($property['FieldName']))
						continue;

					$this->currentValues[$property['FieldName']] = null;

					if (isset($currentActivity['Properties'][$id]))
					{
						if (
							isset($property['Getter'])
							&& is_callable($property['Getter'])
							&& $property['Getter'] instanceof \Closure
						)
						{
							$getter = $property['Getter'];
							$property['Id'] = $id;
							$this->currentValues[$property['FieldName']] = $getter($this, $property, $currentActivity, $compatible);
						}
						else
						{
							$this->currentValues[$property['FieldName']] = $currentActivity['Properties'][$id];
						}
					}

					if (
						(
							$this->currentValues[$property['FieldName']] === null
							||
							$this->currentValues[$property['FieldName']] === ''
						)
						&& isset($property['Default'])
					)
					{
						$this->currentValues[$property['FieldName']] = $property['Default'];
					}
				}
			}
		}

		if ($compatible && $this->currentValues)
		{
			$compatibleValues = $this->currentValues;

			foreach ($this->getMap() as $id => $property)
			{
				if (!isset($property['FieldName']))
				{
					continue;
				}

				if (isset($property['Type']) && $property['Type'] === FieldType::USER && !isset($property['Getter']))
				{
					$compatibleValues[$property['FieldName']] = \CBPHelper::usersArrayToString(
						$compatibleValues[$property['FieldName']],
						$workflowTemplate,
						$this->getDocumentType()
					);
				}
			}

			return $compatibleValues;
		}

		return $this->currentValues;
	}

	/**
	 * @param $valueKey
	 * @param null $default Optional default value.
	 * @return mixed
	 */
	public function getCurrentValue($valueKey, $default = null)
	{
		if (is_array($valueKey))
		{
			if ($default === null && isset($valueKey['Default']))
			{
				$default = $valueKey['Default'];
			}
			$valueKey = $valueKey['FieldName'] ?? '';
		}

		$values = $this->getCurrentValues();

		return (is_array($values) && isset($values[$valueKey])) ? $values[$valueKey] : $default;
	}

	/**
	 * @param mixed $currentValues
	 * @return $this
	 */
	public function setCurrentValues($currentValues)
	{
		$this->currentValues = $currentValues;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getFormName()
	{
		return $this->formName;
	}

	/**
	 * @param string $formName
	 * @return $this
	 */
	public function setFormName($formName)
	{
		$this->formName = (string)$formName;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * @param mixed $siteId
	 */
	public function setSiteId($siteId)
	{
		$this->siteId = (string)$siteId;
	}

	/**
	 * @param array $map
	 * @return $this
	 */
	public function setMap(array $map)
	{
		$this->map = $map;

		return $this;
	}

	/**
	 * @param callable $callback
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setMapCallback($callback)
	{
		if (!is_callable($callback))
		{
			throw new ArgumentException('Wrong callable argument.');
		}

		$this->mapCallback = $callback;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMap()
	{
		if ($this->map === null && $this->mapCallback !== null)
		{
			$this->map = call_user_func($this->mapCallback, $this);
		}

		return $this->map ?? [];
	}

	/**
	 * @param array $field
	 * @return null|FieldType
	 */
	public function getFieldTypeObject(array $field)
	{
		$runtime = \CBPRuntime::getRuntime();
		$runtime->startRuntime();

		/** @var \CBPDocumentService $documentService */
		$documentService = $runtime->getService('DocumentService');

		$field = FieldType::normalizeProperty($field);

		$typeClass = $documentService->getTypeClass($this->getDocumentType(), $field['Type']);
		if ($typeClass && class_exists($typeClass))
		{
			return new FieldType($field, $this->getDocumentType(), $typeClass);
		}

		return null;
	}

	public function renderFieldControl($field, $value = null, $allowSelection = true, $renderMode = FieldType::RENDER_MODE_PUBLIC)
	{
		if (is_string($field))
		{
			$field = $this->getMap()[$field];
		}

		$fieldType = $field ? $this->getFieldTypeObject($field) : null;

		if (!$fieldType)
		{
			return 'incorrect field type';
		}

		if ($value === null)
		{
			$value = $this->getCurrentValue($field, $field['Default'] ?? null);
		}

		return $fieldType->renderControl(
			['Form' => $this->getFormName(), 'Field' => $field['FieldName']],
			$value,
			$allowSelection,
			$renderMode
		);
	}

	public function setRenderer($callable)
	{
		if (!is_callable($callable))
		{
			throw new ArgumentException('Wrong callable argument.');
		}

		$this->renderer = $callable;
	}

	public function __toString()
	{
		if ($this->renderer !== null)
		{
			return (string)call_user_func($this->renderer, $this);
		}

		$runtime = \CBPRuntime::getRuntime();
		$runtime->startRuntime();

		return (string)$runtime->executeResourceFile(
			$this->activityFile,
			$this->dialogFileName,
			array_merge(
				[
					'dialog' => $this,
					//compatible parameters
					'arCurrentValues' => $this->getCurrentValues($this->dialogFileName === 'properties_dialog.php'),
					'formName' => $this->getFormName(),
				],
				$this->getRuntimeData()
			)
		);
	}

	/**
	 * @param array $context Context data.
	 * @return PropertiesDialog
	 */
	public function setContext(array $context)
	{
		$this->context = $context;

		return $this;
	}

	/**
	 * @return array Context data.
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string $dialogFileName
	 * @return PropertiesDialog
	 */
	public function setDialogFileName($dialogFileName)
	{
		$dialogFileName = (string)$dialogFileName;
		if (!str_contains($dialogFileName, '.'))
		{
			$dialogFileName .= '.php';
		}

		$this->dialogFileName = $dialogFileName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDialogFileName()
	{
		return $this->dialogFileName;
	}

	/**
	 * Get runtime data
	 * @return array
	 */
	public function getRuntimeData()
	{
		return $this->runtimeData;
	}

	/**
	 * Set runtime data
	 * @param array $runtimeData
	 * @return PropertiesDialog
	 */
	public function setRuntimeData(array $runtimeData)
	{
		$this->runtimeData = $runtimeData;

		return $this;
	}

	public function getTemplateExpressions(): array
	{
		return [
			'parameters' => FieldType::normalizePropertyList($this->getWorkflowParameters() ?? []),
			'variables' => FieldType::normalizePropertyList($this->getWorkflowVariables() ?? []),
			'constants' => FieldType::normalizePropertyList($this->getWorkflowConstants()),
			'global_variables' => FieldType::normalizePropertyList($this->getGlobalVariables()),
			'global_constants' => FieldType::normalizePropertyList($this->getGlobalConstants()),
			'return_activities' => $this->getReturnProperties(),
		];
	}

	private function getGlobalConstants(): array
	{
		$documentType = $this->getDocumentType();
		if ($documentType)
		{
			return \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll($documentType);
		}

		return [];
	}

	private function getGlobalVariables(): array
	{
		$documentType = $this->getDocumentType();
		if ($documentType)
		{
			return \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($documentType);
		}

		return [];
	}

	private function getReturnProperties(): array
	{
		$documentType = $this->getDocumentType();
		$template = $this->getWorkflowTemplate();

		if (!is_array($documentType) || !is_array($template))
		{
			return [];
		}

		$runtime = \CBPRuntime::getRuntime();
		$activities = $runtime->searchActivitiesByType('activity', $documentType);
		$result = [];
		$this->extractChildProperties($template, $activities, $result);

		return $result;
	}

	private function extractChildProperties(array $children, array $activities, &$result): void
	{
		foreach ($children as $child)
		{
			$childType = mb_strtolower($child["Type"]);
			if (
				isset($activities[$childType]['RETURN'])
				&& is_array($activities[$childType]['RETURN'])
				&& count($activities[$childType]['RETURN']) > 0
			)
			{
				$childProps = [];
				foreach ($activities[$childType]['RETURN'] as $propId => $prop)
				{
					$childProps[] = [
						'Id' => $propId,
						'Name' => $prop['NAME'],
						'Type' => $prop['TYPE'],
					];
				}

				if (count($childProps) > 0)
				{
					$result[] = [
						'Id' => $child['Name'],
						'Type' => $child['Type'],
						'Title' => $child['Properties']['Title'],
						'Return' => $childProps,
					];
				}
			}
			elseif (
				isset($activities[$childType]['ADDITIONAL_RESULT'])
				&& is_array($activities[$childType]['ADDITIONAL_RESULT'])
			)
			{
				$additionalProps = [];
				foreach ($activities[$childType]['ADDITIONAL_RESULT'] as $propertyKey)
				{
					if (!isset($child['Properties'][$propertyKey]) || !is_array($child['Properties'][$propertyKey]))
					{
						continue;
					}

					foreach ($child['Properties'][$propertyKey] as $fieldId => $fieldData)
					{
						$additionalProps[] = [
							'Id' => $fieldId,
							'Name' => $fieldData['Name'],
							'Type' => $fieldData['Type'],
						];
					}
				}

				if (count($additionalProps) > 0)
				{
					$result[] = [
						'Id' => $child['Name'],
						'Type' => $child['Type'],
						'Title' => $child['Properties']['Title'],
						'Return' => $additionalProps,
					];
				}
			}

			if (is_array($child['Children']))
			{
				$this->extractChildProperties($child['Children'], $activities, $result);
			}
		}
	}
}
