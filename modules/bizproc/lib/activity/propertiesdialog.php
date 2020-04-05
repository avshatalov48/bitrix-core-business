<?php
namespace Bitrix\Bizproc\Activity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ArgumentException;

class PropertiesDialog
{
	protected $activityFile;
	protected $dialogFileName = 'properties_dialog.php';
	protected $map;
	protected $mapCallback;
	protected $documentType;
	protected $activityName;
	protected $workflowTemplate;
	protected $workflowParameters;
	protected $workflowVariables;
	protected $currentValues;
	protected $formName;
	protected $siteId;
	protected $renderer;
	protected $context;

	/** @var array */
	protected $runtimeData = array();

	public function __construct($activityFile, array $data = null)
	{
		$this->activityFile = $activityFile;

		if (is_array($data))
		{
			if (isset($data['documentType']) && is_array($data['documentType']))
				$this->setDocumentType($data['documentType']);
			if (isset($data['activityName']))
				$this->setActivityName($data['activityName']);
			if (isset($data['workflowTemplate']) && is_array($data['workflowTemplate']))
				$this->setWorkflowTemplate($data['workflowTemplate']);
			if (isset($data['workflowParameters']) && is_array($data['workflowParameters']))
				$this->setWorkflowParameters($data['workflowParameters']);
			if (isset($data['currentValues']) && is_array($data['currentValues']))
				$this->setCurrentValues($data['currentValues']);
			if (isset($data['formName']))
				$this->setFormName($data['formName']);
			if (isset($data['siteId']))
				$this->setSiteId($data['siteId']);
		}
	}

	/**
	 * @return mixed
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
	 * @return mixed
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
	 * @return mixed
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
	 * @return mixed
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
	 * @return mixed
	 */
	public function getWorkflowVariables()
	{
		return $this->workflowVariables;
	}

	/**
	 * @param bool $compatible
	 * @return array
	 */
	public function getCurrentValues($compatible = false)
	{
		if (!is_array($this->currentValues))
		{
			// Get current values from saved activity properties.
			$this->currentValues = array();
			$currentActivity = \CBPWorkflowTemplateLoader::findActivityByName(
				$this->getWorkflowTemplate(),
				$this->getActivityName()
			);

			if (is_array($currentActivity) && is_array($currentActivity['Properties']))
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

							if ($compatible && $property['Type'] === FieldType::USER)
							{
								$this->currentValues[$property['FieldName']] = \CBPHelper::usersArrayToString(
									$currentActivity['Properties'][$id],
									$this->getWorkflowTemplate(),
									$this->getDocumentType()
								);
							}
						}
					}

					if (
						\CBPHelper::isEmptyValue($this->currentValues[$property['FieldName']])
						&& isset($property['Default'])
					)
					{
						$this->currentValues[$property['FieldName']] = $property['Default'];
						if ($compatible && $property['Type'] === FieldType::USER)
						{
							$this->currentValues[$property['FieldName']] = \CBPHelper::usersArrayToString(
								$property['Default'],
								$this->getWorkflowTemplate(),
								$this->getDocumentType()
							);
						}

					}
				}
			}
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
		$values = $this->getCurrentValues();
		return isset($values[$valueKey]) ? $values[$valueKey] : $default;
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
	 * @return mixed
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
	 * @return mixed
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
			throw new ArgumentException('Wrong callable argument.');

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

		return is_array($this->map) ? $this->map : array();
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

		$typeClass = $documentService->getTypeClass($this->getDocumentType(), $field['Type']);
		if ($typeClass && class_exists($typeClass))
		{
			return new FieldType($field, $this->getDocumentType(), $typeClass);
		}
		return null;
	}

	public function setRenderer($callable)
	{
		if (!is_callable($callable))
			throw new ArgumentException('Wrong callable argument.');

		$this->renderer = $callable;
	}

	public function __toString()
	{
		if ($this->renderer !== null)
		{
			return call_user_func($this->renderer, $this);
		}

		$runtime = \CBPRuntime::getRuntime();
		$runtime->startRuntime();

		return (string)$runtime->executeResourceFile(
			$this->activityFile,
			$this->dialogFileName,
			array_merge(array(
				'dialog' => $this,
				//compatible parameters
				'arCurrentValues' => $this->getCurrentValues($this->dialogFileName === 'properties_dialog.php'),
				'formName' => $this->getFormName()
				), $this->getRuntimeData()
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
		if (strpos($dialogFileName, '.') === false)
			$dialogFileName .= '.php';

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
}