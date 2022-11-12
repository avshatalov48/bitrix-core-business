<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

class CBPHandleExternalEventActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener, IBPEventDrivenActivity
{
	private bool $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'Permission' => [],
			'SenderUserId' => null
		];

		$this->setPropertiesTypes([
			'SenderUserId' => [
				'Type' => 'user',
			],
		]);
	}

	public function subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$this->isInEventActivityMode = true;

		$v = [];
		$arPermissionTmp = $this->Permission;
		if (is_array($arPermissionTmp))
		{
			foreach ($arPermissionTmp as $val)
			{
				$v[] = (mb_strpos($val, '{=') === 0 ? $val : '{=user:' . $val . '}');
			}
		}

		if (count($v) > 0)
		{
			$this->writeToTrackingService(str_replace(
				['#EVENT#', '#VAL#'], [$this->name, implode(', ', $v)],
				Loc::getMessage('BPHEEA_TRACK'))
			);
		}

		$stateService = $this->workflow->getService('StateService');
		$stateService->addStateParameter(
			$this->getWorkflowInstanceId(),
			[
				'NAME' => $this->name,
				'TITLE' => $this->Title,
				'PERMISSION' => $this->Permission,
			]
		);

		$this->workflow->addEventHandler($this->name, $eventHandler);
	}

	public function unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$stateService = $this->workflow->getService('StateService');
		$stateService->deleteStateParameter($this->getWorkflowInstanceId(), $this->name);

		$this->workflow->removeEventHandler($this->name, $eventHandler);
	}

	public function execute()
	{
		if ($this->isInEventActivityMode)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->subscribe($this);

		$this->isInEventActivityMode = false;

		return CBPActivityExecutionStatus::Executing;
	}

	public function cancel()
	{
		if (!$this->isInEventActivityMode)
		{
			$this->unsubscribe($this);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function onExternalEvent($arEventParameters = [])
	{
		if ($this->onExternalEventHandler($arEventParameters))
		{
			$this->unsubscribe($this);
			$this->workflow->closeActivity($this);
		}
	}

	public function OnExternalDrivenEvent($arEventParameters = [])
	{
		return $this->onExternalEventHandler($arEventParameters);
	}

	private function onExternalEventHandler($arEventParameters = [])
	{
		if (count($this->Permission) > 0)
		{
			$arSenderGroups = (array_key_exists('Groups', $arEventParameters) ? $arEventParameters['Groups'] : []);
			if (!is_array($arSenderGroups))
			{
				$arSenderGroups = [$arSenderGroups];
			}
			if (array_key_exists('User', $arEventParameters))
			{
				$arSenderGroups[] = 'user_' . $arEventParameters['User'];
				$arSenderGroups = array_merge($arSenderGroups, CBPHelper::getUserExtendedGroups($arEventParameters['User']));
			}
			if (count($arSenderGroups) <= 0)
			{
				return false;
			}

			$bHavePerms = false;

			$intersect = array_intersect($this->Permission, $arSenderGroups);
			if (count($intersect) > 0)
			{
				$bHavePerms = true;
			}

			if (!$bHavePerms)
			{
				return false;
			}
		}

		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			if (array_key_exists('User', $arEventParameters))
			{
				$this->SenderUserId = 'user_' . $arEventParameters['User'];
			}

			return true;
		}

		return false;
	}

	public function OnStateExternalEvent($arEventParameters = [])
	{
		if (
			$this->executionStatus != CBPActivityExecutionStatus::Closed
			&& array_key_exists('User', $arEventParameters)
		)
		{
			$this->SenderUserId = 'user_' . $arEventParameters['User'];
		}
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		return array_merge($arErrors, parent::validateProperties($arTestProperties, $user));
	}

	public static function getPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = ''
	)
	{
		$runtime = CBPRuntime::getRuntime();

		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		$allowSetStatus = ($c == 1 || $currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity');

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				is_array($arCurrentActivity['Properties'])
				&& array_key_exists('Permission', $arCurrentActivity['Properties'])
			)
			{
				$arCurrentValues['permission'] = CBPHelper::usersArrayToString(
					$arCurrentActivity['Properties']['Permission'],
					$arWorkflowTemplate,
					$documentType
				);
			}

			if ($c > 1 && $currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity')
			{
				$arCurrentValues['setstate'] = $currentParent['Children'][$c - 1]['Properties']['TargetStateName'];
			}
		}

		$arStates = [];
		if ($allowSetStatus)
		{
			$arStates = CBPWorkflowTemplateLoader::getStatesOfTemplate($arWorkflowTemplate);
		}

		return $runtime->executeResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'formName' => $formName,
				'allowSetStatus' => $allowSetStatus,
				'arStates' => $arStates,
			]
		);
	}

	public static function getPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];

		$runtime = CBPRuntime::getRuntime();

		$arProperties = [];

		$arProperties['Permission'] = CBPHelper::usersStringToArray(
			$arCurrentValues['permission'],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arErrors = self::validateProperties(
			$arProperties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;
		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		if ($c == 1)
		{
			if ($arCurrentValues['setstate'] != '')
			{
				$currentParent['Children'][] = [
					'Type' => 'SetStateActivity',
					'Name' => md5(uniqid(mt_rand(), true)),
					'Properties' => ['TargetStateName' => $arCurrentValues['setstate']],
					'Children' => [],
				];
			}
		}
		elseif ($currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity')
		{
			if ($arCurrentValues['setstate'] != '')
			{
				$currentParent['Children'][$c - 1]['Properties']['TargetStateName'] = $arCurrentValues['setstate'];
			}
			else
			{
				unset($currentParent['Children'][$c - 1]);
			}
		}

		return true;
	}
}
