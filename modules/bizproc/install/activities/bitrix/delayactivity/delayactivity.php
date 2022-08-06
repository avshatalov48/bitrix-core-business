<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPDelayActivity extends CBPActivity implements
	IBPEventActivity,
	IBPActivityExternalEventListener,
	IBPActivityDebugEventListener,
	IBPEventDrivenActivity
{
	private $subscriptionId = 0;
	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'TimeoutDuration' => null,
			'TimeoutDurationType' => 's',
			'TimeoutTime' => null,
			'TimeoutTimeIsLocal' => 'N',
			'WriteToLog' => 'N',
		];
	}

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->subscriptionId > 0)
		{
			$this->Unsubscribe($this);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function Execute()
	{
		if ($this->isInEventActivityMode)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$result = $this->Subscribe($this);
		$this->isInEventActivityMode = false;

		return $result ? CBPActivityExecutionStatus::Executing : CBPActivityExecutionStatus::Closed;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$this->isInEventActivityMode = true;

		$delayIntervalProperties = [
			'TimeoutDuration' => $this->getRawProperty('TimeoutDuration'),
			'TimeoutDurationType' => $this->getRawProperty('TimeoutDurationType'),
			'TimeoutTime' => $this->getRawProperty('TimeoutTime'),
			'TimeoutTimeIsLocal' => $this->getRawProperty('TimeoutTimeIsLocal'),
		];

		$timeoutDuration = $this->parseValue($delayIntervalProperties['TimeoutDuration']);
		$timeoutDurationValue = 0;
		$timeoutTime = $this->parseValue($delayIntervalProperties['TimeoutTime']);

		if (is_array($timeoutTime)) //if multiple value
		{
			$timeoutTime = reset($timeoutTime);
		}

		if ($timeoutDuration != null)
		{
			$timeoutDurationValue = $this->CalculateTimeoutDuration();
			$expiresAt = time() + $timeoutDurationValue;
		}
		elseif ($timeoutTime != null)
		{
			$timeoutTime = $this->timeoutTimeToTimestamp($timeoutTime, $delayIntervalProperties);
			$expiresAt = $timeoutTime;
		}
		else
		{
			$expiresAt = time();
		}

		$this->writeToTrackingService(
			$this->getDelayAutomationTrack($delayIntervalProperties),
			0,
			CBPTrackingType::DebugAutomation
		);

		if ($timeoutTime != null && $eventHandler === $this && $expiresAt <= time() + 1) //now + 1 second
		{
			$this->logMessage(GetMessage('BPDA_TRACK3'));

			return false;
		}

		$schedulerService = $this->workflow->GetService('SchedulerService');
		$this->subscriptionId =
			$schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, $expiresAt)
		;

		if (!$this->subscriptionId)
		{
			throw new Exception(GetMessage('BPDA_SUBSCRIBE_ERROR'));
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);

		if ($timeoutDuration != null)
		{
			$timeoutDurationValue = max($timeoutDurationValue, CBPSchedulerService::getDelayMinLimit());
			$timestamp = time() + $timeoutDurationValue;

			$this->logMessage(
				GetMessage(
					'BPDA_TRACK4',
					[
						'#PERIOD1#' => trim(CBPHelper::FormatTimePeriod($timeoutDurationValue)),
						'#PERIOD2#' => sprintf(
							'%s (%s)',
							ConvertTimeStamp($timestamp, 'FULL'),
							date('P', $timestamp)
						),
					]
				)
			);
		}
		elseif ($timeoutTime != null)
		{
			$timestamp = max($timeoutTime, time() + CBPSchedulerService::getDelayMinLimit());
			$this->logMessage(
				GetMessage(
					'BPDA_TRACK1',
					[
						'#PERIOD#' => sprintf(
							'%s (%s)',
							ConvertTimeStamp($timestamp, 'FULL'),
							date('P', $timestamp)
						)
					]
				)
			);
		}
		else
		{
			$this->logMessage(GetMessage('BPDA_TRACK2'));
		}

		return true;
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$schedulerService = $this->workflow->GetService('SchedulerService');
		$schedulerService->UnSubscribeOnTime($this->subscriptionId);
		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
		$this->subscriptionId = 0;
	}

	public function OnExternalEvent($arEventParameters = [])
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			if (!empty($arEventParameters['DebugEvent']))
			{
				$this->writeToTrackingService(
					\Bitrix\Main\Localization\Loc::getMessage('BPDA_DEBUG_EVENT'),
					0,
					CBPTrackingType::Debug
				);
			}

			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	public function onDebugEvent(array $eventParameters = [])
	{
		$eventParameters['DebugEvent'] = true;
		$this->OnExternalEvent($eventParameters);
	}

	public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		if (empty($arTestProperties['TimeoutDuration']) && empty($arTestProperties['TimeoutTime']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'TimeoutDuration',
				'message' => GetMessage('BPDA_EMPTY_PROP')
			];
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}

	private function CalculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists('TimeoutDuration') ? $this->TimeoutDuration : 0);

		$timeoutDurationType = $this->TimeoutDurationType;
		$timeoutDurationType = mb_strtolower($timeoutDurationType);
		if (!in_array($timeoutDurationType, ['s', 'd', 'h', 'm']))
		{
			$timeoutDurationType = 's';
		}

		$timeoutDuration = intval($timeoutDuration);
		switch ($timeoutDurationType)
		{
			case 'd':
				$timeoutDuration *= 3600 * 24;
				break;
			case 'h':
				$timeoutDuration *= 3600;
				break;
			case 'm':
				$timeoutDuration *= 60;
				break;
			default:
				break;
		}

		return min($timeoutDuration, 3600 * 24 * 365 * 5);
	}

	private function logMessage(string $message): void
	{
		if (
			$this->WriteToLog === 'Y'
			|| $this->workflow->getService('TrackingService')->isForcedMode($this->getWorkflowInstanceId())
		)
		{
			$this->WriteToTrackingService($message);
		}
		else
		{
			$this->SetStatusTitle($message);
		}
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = '')
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

			if (is_array($arCurrentActivity['Properties']))
			{
				$arCurrentValues['delay_time'] = $arCurrentActivity['Properties']['TimeoutDuration'];
				$arCurrentValues['delay_type'] = $arCurrentActivity['Properties']['TimeoutDurationType'];
				$arCurrentValues['delay_date'] = $arCurrentActivity['Properties']['TimeoutTime'];
				if ($arCurrentValues['delay_date'] && !CBPActivity::isExpression($arCurrentValues['delay_date']))
				{
					$arCurrentValues['delay_date'] = ConvertTimeStamp($arCurrentValues['delay_date'], 'FULL');
				}
				$arCurrentValues['delay_date_is_local'] = $arCurrentActivity['Properties']['TimeoutTimeIsLocal'];
				$arCurrentValues['delay_write_to_log'] = $arCurrentActivity['Properties']['WriteToLog'];
			}

			if (
				is_array($arCurrentValues)
				&& array_key_exists('delay_time', $arCurrentValues)
				&& (intval($arCurrentValues['delay_time']) > 0)
				&& !array_key_exists('delay_type', $arCurrentValues)
			)
			{
				$arCurrentValues['delay_time'] = intval($arCurrentValues['delay_time']);

				$arCurrentValues['delay_type'] = 's';
				if ($arCurrentValues['delay_time'] % (3600 * 24) == 0)
				{
					$arCurrentValues['delay_time'] = $arCurrentValues['delay_time'] / (3600 * 24);
					$arCurrentValues['delay_type'] = 'd';
				}
				elseif ($arCurrentValues['delay_time'] % 3600 == 0)
				{
					$arCurrentValues['delay_time'] = $arCurrentValues['delay_time'] / 3600;
					$arCurrentValues['delay_type'] = 'h';
				}
				elseif ($arCurrentValues['delay_time'] % 60 == 0)
				{
					$arCurrentValues['delay_time'] = $arCurrentValues['delay_time'] / 60;
					$arCurrentValues['delay_type'] = 'm';
				}
			}
		}

		if (!is_array($arCurrentValues) || !array_key_exists('delay_type', $arCurrentValues))
		{
			$arCurrentValues['delay_type'] = 's';
		}
		if (
			!is_array($arCurrentValues)
			|| !array_key_exists('delay_time', $arCurrentValues)
			&& !array_key_exists('delay_date', $arCurrentValues)
		)
		{
			$arCurrentValues['delay_time'] = 1;
			$arCurrentValues['delay_type'] = 'h';
		}

		if (!is_array($arCurrentValues) || !array_key_exists('delay_date_is_local', $arCurrentValues))
		{
			$arCurrentValues['delay_date_is_local'] = 'N';
		}

		if (!is_array($arCurrentValues) || !array_key_exists('delay_write_to_log', $arCurrentValues))
		{
			$arCurrentValues['delay_write_to_log'] = 'N';
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'formName'        => $formName
			]
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];
		$properties = [];

		if ($arCurrentValues['time_type_selector'] == 'time')
		{
			if (CBPDocument::IsExpression($arCurrentValues['delay_date']))
			{
				$arCurrentValues['delay_date_x'] = $arCurrentValues['delay_date'];
				$arCurrentValues['delay_date'] = '';
			}

			if ($arCurrentValues['delay_date'] <> '' && $d = MakeTimeStamp($arCurrentValues['delay_date']))
			{
				$properties['TimeoutTime'] = $d;
			}
			elseif (
				$arCurrentValues['delay_date_x'] <> ''
				&& CBPActivity::isExpression($arCurrentValues['delay_date_x'])
			)
			{
				$properties['TimeoutTime'] = $arCurrentValues['delay_date_x'];
			}

			$properties['TimeoutTimeIsLocal'] = ($arCurrentValues['delay_date_is_local'] === 'Y') ? 'Y' : 'N';
		}
		else
		{
			$properties['TimeoutDuration'] = $arCurrentValues['delay_time'];
			$properties['TimeoutDurationType'] = $arCurrentValues['delay_type'];
		}

		$properties['WriteToLog'] = CBPHelper::getBool($arCurrentValues['delay_write_to_log']) ? 'Y' : 'N';

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$errors = self::ValidateProperties($properties, $user);
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}

	private function getDelayAutomationTrack(array $delayIntervalProperties): array
	{
		$delayInterval = \Bitrix\Bizproc\Automation\Engine\DelayInterval::createFromActivityProperties($delayIntervalProperties);
		$parsed = static::parseExpression($delayInterval->getBasis());
		[$fieldProperty, $fieldValue] = $this->getRuntimeProperty($parsed['object'], $parsed['field'], $this);
		if ($parsed['object'] !== \Bitrix\Bizproc\Workflow\Template\SourceType::System)
		{
			$fieldName = $fieldProperty['Name'] ?? $parsed['field'];
		}
		else
		{
			$fieldName = null;
			$fieldValue = $this->parseValue($delayInterval->getBasis());
		}
		$fieldValue = $fieldValue ? $this->timeoutTimeToTimestamp($fieldValue, $delayIntervalProperties) : '';
		if ($fieldValue)
		{
			$fieldValue = sprintf(
				'%s (%s)',
				ConvertTimeStamp($fieldValue, 'FULL'),
				date('P', $fieldValue)
			);
		}

		return array_merge(
			$delayInterval->toArray(),
			[
				'fieldName' => $fieldName,
				'fieldValue' => $fieldValue,
			]
		);
	}

	private function timeoutTimeToTimestamp($timeoutTime, $delayIntervalProperties)
	{
		$isLocalTime = ($this->parseValue($delayIntervalProperties['TimeoutTimeIsLocal']) === 'Y');

		if ($timeoutTime instanceof \Bitrix\Bizproc\BaseType\Value\Date)
		{
			return $timeoutTime->getTimestamp();
		}
		else
		{
			if (!is_numeric($timeoutTime))
			{
				$timeoutTime = MakeTimeStamp((string) $timeoutTime);
			}

			if ($isLocalTime)
			{
				$timeoutTime -= \CTimeZone::GetOffset();
			}
		}

		return $timeoutTime;
	}
}
