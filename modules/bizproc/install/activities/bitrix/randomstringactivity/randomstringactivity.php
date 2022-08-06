<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

class CBPRandomStringActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"StringLength" => 5,
			"Alphabet" => [],

			//return
			'ResultString' => ''
		);

		$this->SetPropertiesTypes([
			'ResultString' => ['Type' => 'string']
		]);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		$this->ResultString = '';
	}

	public function Execute()
	{
		$size = (int)$this->StringLength;
		if (!$size)
		{
			$size = 5;
		}

		$alphabet = 0;
		foreach ((array) $this->Alphabet as $alp)
		{
			$alphabet |= (int)$alp;
		}

		if (!$alphabet)
		{
			$alphabet = Main\Security\Random::ALPHABET_NUM;
		}

		$this->ResultString = Main\Security\Random::getStringByAlphabet($size, $alphabet);
		$this->logDebug();

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["StringLength"]))
		{
			$arErrors[] = array(
				"code" => "StringLength",
				"message" => GetMessage("BPRNDSA_EMPTY_SIZE"),
			);
		}
		if (empty($arTestProperties["Alphabet"]))
		{
			$arErrors[] = array(
				"code" => "Alphabet",
				"message" => GetMessage("BPRNDSA_EMPTY_ALPHABET"),
			);
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		));

		$dialog->setMap(static::getPropertiesMap($documentType));

		return $dialog;
	}

	public static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'StringLength' => [
				'Name' => GetMessage('BPRNDSA_SIZE_NAME'),
				'FieldName' => 'string_length',
				'Type' => 'int',
				'Required' => true,
				'Default' => 5,
			],
			'Alphabet' => [
				'Name' => GetMessage('BPRNDSA_ALPHABET_NAME'),
				'FieldName' => 'alphabet',
				'Type' => 'select',
				'Required' => true,
				'Options' => [
					Main\Security\Random::ALPHABET_NUM => GetMessage('BPRNDSA_ALPHABET_NUM'),
					Main\Security\Random::ALPHABET_ALPHALOWER => GetMessage('BPRNDSA_ALPHABET_ALPHALOWER'),
					Main\Security\Random::ALPHABET_ALPHAUPPER => GetMessage('BPRNDSA_ALPHABET_ALPHAUPPER'),
					Main\Security\Random::ALPHABET_SPECIAL => GetMessage('BPRNDSA_ALPHABET_SPECIAL'),
				],
				'Default' => Main\Security\Random::ALPHABET_NUM,
				'Settings' => ['display' => 'checkboxes'],
				'Multiple' => true
			],
		];
	}

	public function logDebug()
	{
		$debugInfo = $this->getDebugInfo([
			'StringLength' => (int) $this->StringLength,
			'Alphabet' => $this->Alphabet,
		]);

		$debugInfo += $this->getDebugInfo(['ResultString' => $this->ResultString], [
			'ResultString' =>[
				'Name' => Main\Localization\Loc::getMessage('BPRNDSA_RESULT_STRING'),
				'Type' => 'string',
			]
		]);

		$this->writeDebugInfo($debugInfo);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$properties = array(
			"StringLength" => (int) $arCurrentValues["string_length"],
			"Alphabet" => (array) $arCurrentValues["alphabet"],
		);

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}
}