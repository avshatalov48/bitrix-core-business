<?php

namespace Bitrix\Bizproc\Activity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

abstract class BaseActivity extends \CBPActivity
{
	protected static $requiredModules = [];
	protected $preparedProperties = [];

	public function __get($name)
	{
		return $this->preparedProperties[$name] ?? parent::__get($name);
	}

	public function Execute()
	{
		if (!static::checkModules())
		{
			return \CBPActivityExecutionStatus::Closed;
		}
		$this->prepareProperties();

		$errorCollection = $this->checkProperties();
		if ($errorCollection->isEmpty())
		{
			$errorCollection = $this->internalExecute();
		}

		foreach ($errorCollection as $error)
		{
			$this->logError($error->getMessage());
		}

		return \CBPActivityExecutionStatus::Closed;
	}

	protected function prepareProperties(): void
	{
		$fieldsMap = static::getPropertiesDialogMap();

		foreach ($this->arProperties as $propertyId => $propertyValue)
		{
			$type = '';
			if (isset($this->arPropertiesTypes) && isset($this->arPropertiesTypes[$propertyId]))
			{
				$type = $this->arPropertiesTypes[$propertyId]['Type'] ?? '';
			}
			if (!$type && isset($fieldsMap[$propertyId]))
			{
				$type = $fieldsMap[$propertyId]['Type'] ?? '';
			}

			$parsedValue = $this->ParseValue($propertyValue);
			$this->preparedProperties[$propertyId] = $this->convertPropertyValue($type, $parsedValue);
		}
	}

	protected function convertPropertyValue(string $type, $value)
	{
		switch ($type)
		{
			case FieldType::INT:
				return (int)$value;

			case FieldType::BOOL:
				return \CBPHelper::getBool($value);

			case FieldType::DOUBLE:
				return (double)$value;

			default:
				return $value;
		}
	}

	protected function checkProperties(): ErrorCollection
	{
		return new ErrorCollection();
	}

	protected function internalExecute(): ErrorCollection
	{
		return new ErrorCollection();
	}

	protected function logError(string $message = '', int $userId = 0): void
	{
		$this->log($message, $userId, \CBPTrackingType::Error);
	}

	protected function log(string $message = '', int $userId = 0, int $type = -1): void
	{
		$this->WriteToTrackingService($message, $userId, -1);
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$workflowTemplate,
		$workflowParameters,
		$workflowVariables,
		$currentValues = null,
		$formName = '',
		$popupWindow = null,
		$siteId = ''
	)
	{
		if (!static::checkModules())
		{
			return false;
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(static::getFileName(), [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $workflowTemplate,
			'workflowParameters' => $workflowParameters,
			'workflowVariables' => $workflowVariables,
			'currentValues' => $currentValues,
			'formName' => $formName,
			'siteId' => $siteId
		]);

		$dialog
			->setMapCallback([static::class, 'getPropertiesDialogMap'])
			->setRuntimeData(static::getRuntimeData());
		;

		if (!static::hasRenderer())
		{
			$dialog->setRenderer([static::class, 'renderPropertiesDialog']);
		}

		return $dialog;
	}

	private static function hasRenderer(): bool
	{
		$dir = dirname(static::getFileName());

		return
			file_exists($dir . DIRECTORY_SEPARATOR . 'properties_dialog.php')
			|| file_exists($dir . DIRECTORY_SEPARATOR . 'robot_properties_dialog.php')
		;
	}

	public static function renderPropertiesDialog(PropertiesDialog $dialog)
	{
		$propertiesDialogHtml = '';
		$isRobot = $dialog->getDialogFileName() === 'robot_properties_dialog.php';
		foreach ($dialog->getMap() as $fieldId => $field)
		{
			$propertiesDialogHtml .=
				$isRobot
					? static::renderRobotProperty($dialog, $field)
					: static::renderBizprocProperty($dialog, $field)
			;
		}

		return $propertiesDialogHtml;
	}

	protected static function renderBizprocProperty(PropertiesDialog $dialog, array $field): string
	{
		$controlHtml = $dialog->getFieldTypeObject($field)->renderControl(
			[
				'Form' => $dialog->getFormName(),
				'Field' => $field['FieldName'],
			],
			$dialog->getCurrentValue($field),
			true,
			0
		);

		return sprintf(
			'<tr><td align="right" width="40%%">%s:</td><td width="60%%">%s</td></tr>',
			htmlspecialcharsbx($field['Name']),
			$controlHtml
		);
	}

	protected static function renderRobotProperty(PropertiesDialog $dialog, array $field): string
	{
		$propertyHtml = '
			<div class="bizproc-automation-popup-settings">
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
					%s:
				</span>
				%s
			</div>
		';

		return sprintf(
			$propertyHtml,
			htmlspecialcharsbx($field['Name']),
			$dialog->renderFieldControl($field, $dialog->getCurrentValue($field))
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$workflowTemplate,
		&$workflowParameters,
		&$workflowVariables,
		$currentValues,
		&$errors
	): bool
	{
		if (!static::checkModules())
		{
			return false;
		}

		$dialog = new PropertiesDialog(static::getFileName(), [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $workflowTemplate,
			'workflowParameters' => $workflowParameters,
			'workflowVariables' => $workflowVariables,
			'currentValues' => $currentValues,
		]);

		$extractingResult = static::extractPropertiesValues($dialog, static::getPropertiesDialogMap($dialog));
		if (!$extractingResult->isSuccess())
		{
			foreach ($extractingResult->getErrors() as $error)
			{
				$errors[] = [
					'code' => $error->getCode(),
					'message' => $error->getMessage(),
					'parameter' => $error->getCustomData(),
				];
			}
		}
		else
		{
			$errors = self::ValidateProperties(
				$extractingResult->getData(),
				new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser)
			);
		}

		if ($errors)
		{
			return false;
		}

		$currentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName(
			$workflowTemplate,
			$activityName
		);
		$currentActivity['Properties'] = $extractingResult->getData();

		return true;
	}

	protected static function extractPropertiesValues(PropertiesDialog $dialog, array $fieldsMap): Result
	{
		$result = new Result();

		$properties = [];
		$errors = [];
		$currentValues = $dialog->getCurrentValues();
		$documentService = static::getDocumentService();

		foreach ($fieldsMap as $propertyKey => $fieldProperties)
		{
			$field = $documentService->getFieldTypeObject($dialog->getDocumentType(), $fieldProperties);
			if(!$field)
			{
				continue;
			}

			$properties[$propertyKey] = $field->extractValue(
				['Field' => $fieldProperties['FieldName']],
				$currentValues,
				$errors
			);
		}

		if ($errors)
		{
			foreach ($errors as $error)
			{
				$result->addError(
					new Error(
						$error['message'] ?? '',
						$error['code'] ?? '',
						$error['parameter'] ?? ''
					)
				);
			}
		}
		else
		{
			$result->setData($properties);
		}

		return $result;
	}

	abstract protected static function getFileName(): string;

	public static function ValidateProperties($testProperties = [], \CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		foreach (static::getPropertiesDialogMap() as $propertyKey => $fieldProperties)
		{
			if(
				\CBPHelper::getBool($fieldProperties['Required'])
				&& \CBPHelper::isEmptyValue($testProperties[$propertyKey])
			)
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => 'FieldValue',
					'message' => Loc::getMessage('BIZPROC_BA_EMPTY_PROP', ['#PROPERTY#' => $fieldProperties['Name']]),
				];
			}
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	protected static function checkModules(): bool
	{
		foreach (static::$requiredModules as $module)
		{
			if (!Loader::includeModule($module))
			{
				return false;
			}
		}

		return true;
	}

	protected static function getDocumentService(): \CBPDocumentService
	{
		return \CBPRuntime::GetRuntime(true)->getDocumentService();
	}

	public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
	{
		return [];
	}

	protected static function getRuntimeData(): array
	{
		return [];
	}
}