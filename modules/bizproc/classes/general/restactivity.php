<?php

use \Bitrix\Bizproc\RestActivityTable;
use \Bitrix\Rest\Sqs;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CBPRestActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	const TOKEN_SALT = 'bizproc';
	const PROPERTY_NAME_PREFIX = 'property_';
	const REST_ACTIVITY_ID = 0;
	protected static $restActivityData = array();

	protected $subscriptionId = 0;
	protected $eventId;

	private static function getRestActivityData()
	{
		if (!isset(static::$restActivityData[static::REST_ACTIVITY_ID]))
		{
			$result = RestActivityTable::getById(static::REST_ACTIVITY_ID);
			$row = $result->fetch();
			static::$restActivityData[static::REST_ACTIVITY_ID] = $row ? $row : array();
		}
		return static::$restActivityData[static::REST_ACTIVITY_ID];
	}

	public function __construct($name)
	{
		parent::__construct($name);

		$activityData = self::getRestActivityData();
		$this->arProperties = array(
			'Title' => '',
			'UseSubscription' => isset($activityData['USE_SUBSCRIPTION']) && $activityData['USE_SUBSCRIPTION'] == 'Y' ? 'Y' : 'N',
			'IsTimeout' => 0,
			'AuthUserId' => isset($activityData['AUTH_USER_ID']) ? 'user_'.$activityData['AUTH_USER_ID'] : null,
			'SetStatusMessage' => 'Y',
			'StatusMessage' => '',
			'TimeoutDuration' => 0,
			'TimeoutDurationType' => 's',
		);

		if (!empty($activityData['PROPERTIES']))
		{
			foreach ($activityData['PROPERTIES'] as $name => $property)
			{
				if (isset($this->arProperties[$name]))
					continue;
				$this->arProperties[$name] = isset($property['DEFAULT']) ? $property['DEFAULT'] : null;
			}
		}

		$types = array();
		if (!empty($activityData['RETURN_PROPERTIES']))
		{
			foreach ($activityData['RETURN_PROPERTIES'] as $name => $property)
			{
				if (isset($this->arProperties[$name]))
					continue;
				$this->arProperties[$name] = isset($property['DEFAULT']) ? $property['DEFAULT'] : null;
				if (isset($property['TYPE']))
					$types[$name] = array(
						'Type' => $property['TYPE'],
						'Multiple' => CBPHelper::getBool($property['MULTIPLE']),
					);
			}
		}
		$types['IsTimeout'] = array(
			'Type' => 'int',
		);
		$this->SetPropertiesTypes($types);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->IsTimeout = 0;
		$this->eventId = null;
		$activityData = self::getRestActivityData();
		if (!empty($activityData['RETURN_PROPERTIES']))
		{
			foreach ($activityData['RETURN_PROPERTIES'] as $name => $property)
			{
				$this->{$name} = isset($property['DEFAULT']) ? $property['DEFAULT'] : null;
			}
		}
	}

	public function Execute()
	{
		$activityData = $this->getRestActivityData();
		if (!$activityData)
			throw new Exception(Loc::getMessage('BPRA_NOT_FOUND_ERROR'));
		if (!Loader::includeModule('rest') || !\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
			return CBPActivityExecutionStatus::Closed;

		$propertiesData = array();
		if (!empty($activityData['PROPERTIES']))
		{
			foreach ($activityData['PROPERTIES'] as $name => $property)
			{
				$propertiesData[$name] = $this->{$name};
			}
		}

		$session = \Bitrix\Rest\Event\Session::get();
		if(!$session)
		{
			throw new Exception('Rest session error');
		}

		$dbRes = \Bitrix\Rest\AppTable::getList(array(
			'filter' => array(
				'=CLIENT_ID' => $activityData['APP_ID'],
			)
		));
		$application = $dbRes->fetch();

		if (!$application)
		{
			throw new Exception('Rest application not found.');
		}

		$appStatus = \Bitrix\Rest\AppTable::getAppStatusInfo($application, '');
		if($appStatus['PAYMENT_ALLOW'] === 'N')
		{
			throw new Exception('Rest application status error: payment required');
		}

		$userId = CBPHelper::ExtractUsers($this->AuthUserId, $this->GetDocumentId(), true);

		$auth = array(
			'WORKFLOW_ID' => $this->getWorkflowInstanceId(),
			'ACTIVITY_NAME' => $this->name,
			'CODE' => $activityData['CODE'],
			\Bitrix\Rest\Event\Session::PARAM_SESSION => $session,
			\Bitrix\Rest\OAuth\Auth::PARAM_LOCAL_USER => $userId,
			"application_token" => \CRestUtil::getApplicationToken($application),
		);

		$this->eventId = \Bitrix\Main\Security\Random::getString(32, true);

		$queryItems = array(
			Sqs::queryItem(
				$activityData['APP_ID'],
				$activityData['HANDLER'],
				array(
					'workflow_id' => $this->getWorkflowInstanceId(),
					'code' => $activityData['CODE'],
					'document_id' => $this->GetDocumentId(),
					'document_type' => $this->GetDocumentType(),
					'event_token' => self::generateToken($this->getWorkflowInstanceId(), $this->name, $this->eventId),
					'properties' => $propertiesData,
					'use_subscription' => $this->UseSubscription,
					'timeout_duration' => $this->CalculateTimeoutDuration(),
					'ts' => time(),
				),
				$auth,
				array(
					"sendAuth" => true,
					"sendRefreshToken" => false,
					"category" => Sqs::CATEGORY_BIZPROC,
				)
			),
		);

		\Bitrix\Rest\OAuthService::getEngine()->getClient()->sendEvent($queryItems);

		if ($this->SetStatusMessage == 'Y')
		{
			$message = $this->StatusMessage;
			if (empty($message))
				$message = Loc::getMessage('BPRA_DEFAULT_STATUS_MESSAGE');
			$this->SetStatusTitle($message);
		}

		if ($this->UseSubscription != 'Y')
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);

		return CBPActivityExecutionStatus::Executing;
	}


	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception('eventHandler');

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$schedulerService = $this->workflow->GetService('SchedulerService');
			$this->subscriptionId = $schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, time() + $timeoutDuration);
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}


	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception('eventHandler');

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$schedulerService->UnSubscribeOnTime($this->subscriptionId);
			$this->subscriptionId = 0;
		}

		$this->eventId = null;
		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}


	public function OnExternalEvent($eventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		$onAgent = (array_key_exists('SchedulerService', $eventParameters) && $eventParameters['SchedulerService'] == 'OnAgent');
		if ($onAgent)
		{
			$this->IsTimeout = 1;
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
			return;
		}

		if ($this->eventId !== (string) $eventParameters['EVENT_ID'])
			return;

		if (!empty($eventParameters['RETURN_VALUES']))
		{
			$activityData = self::getRestActivityData();
			$whiteList = array();
			if (!empty($activityData['RETURN_PROPERTIES']))
			{
				foreach ($activityData['RETURN_PROPERTIES'] as $name => $property)
				{
					$whiteList[strtoupper($name)] = $name;
				}
			}

			$eventParameters['RETURN_VALUES'] = array_change_key_case((array) $eventParameters['RETURN_VALUES'], CASE_UPPER);
			foreach($eventParameters['RETURN_VALUES'] as $name => $value)
			{
				if (!isset($whiteList[$name]))
					continue;
				$this->{$whiteList[$name]} = $value;
			}
		}

		$this->WriteToTrackingService(
			!empty($eventParameters['LOG_MESSAGE']) ? $eventParameters['LOG_MESSAGE']
				: Loc::getMessage('BPRA_DEFAULT_LOG_MESSAGE'));

		if (empty($eventParameters['LOG_ACTION']))
		{
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	public function Cancel()
	{
		if ($this->UseSubscription == 'Y')
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $workflowTemplate, $workflowParameters, $workflowVariables, $currentValues = null, $formName = "")
	{
		$activityData = self::getRestActivityData();

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $workflowTemplate,
			'workflowParameters' => $workflowParameters,
			'workflowVariables' => $workflowVariables,
			'currentValues' => $currentValues
		));

		$map = array(
			'AuthUserId' => array(
				'Name' => 'AuthUserId',
				'FieldName' => 'authuserid',
				'Type' => 'user',
				'Default' => 'user_'.$activityData['AUTH_USER_ID']
			),
			'SetStatusMessage' => array(
				'Name' => 'SetStatusMessage',
				'FieldName' => 'setstatusmessage',
				'Type' => 'bool',
			),
			'StatusMessage' => array(
				'Name' => 'StatusMessage',
				'FieldName' => 'statusmessage',
				'Type' => 'text',
				'Default' => Loc::getMessage('BPRA_DEFAULT_STATUS_MESSAGE'),
			),
			'UseSubscription' => array(
				'Name' => 'StatusMessage',
				'FieldName' => 'usesubscription',
				'Type' => 'bool',
				'Default' => $activityData['USE_SUBSCRIPTION']
			),
			'TimeoutDuration' => array(
				'Name' => 'StatusMessage',
				'FieldName' => 'timeoutduration',
				'Type' => 'int',
			),
			'TimeoutDurationType' => array(
				'Name' => 'StatusMessage',
				'FieldName' => 'timeoutdurationtype',
				'Type' => 'string',
				'Default' => 's'
			),
		);

		$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
		foreach ($properties as $name => $property)
		{
			if (!array_key_exists($name, $map))
				$map[$name] = array(
					'Name' => RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID),
					'Description' => RestActivityTable::getLocalization($property['DESCRIPTION'], LANGUAGE_ID),
					'FieldName' => static::PROPERTY_NAME_PREFIX.strtolower($name),
					'Type' => $property['TYPE'],
					'Required' => $property['REQUIRED'],
					'Multiple' => $property['MULTIPLE'],
					'Default' => $property['DEFAULT'],
					'Options' => isset($property['OPTIONS']) ? $property['OPTIONS'] : null
				);
		}

		$dialog	->setMap($map)
				->setRuntimeData(array('ACTIVITY_DATA' => $activityData))
				->setRenderer(array(__CLASS__, 'renderPropertiesDialog'));

		return $dialog;
	}

	public static function renderPropertiesDialog(\Bitrix\Bizproc\Activity\PropertiesDialog $dialog)
	{
		$runtime = CBPRuntime::GetRuntime();
		$data = $dialog->getRuntimeData();
		$activityData = $data['ACTIVITY_DATA'];

		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");
		$activityDocumentType = is_array($activityData['DOCUMENT_TYPE']) ? $activityData['DOCUMENT_TYPE'] : $dialog->getDocumentType();
		$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();

		$currentValues = $dialog->getCurrentValues(true, true);

		ob_start();
		foreach ($properties as $name => $property):
			$required = CBPHelper::getBool($property['REQUIRED']);
			$name = strtolower($name);
			$value = !CBPHelper::isEmptyValue($currentValues[static::PROPERTY_NAME_PREFIX.$name]) ? $currentValues[static::PROPERTY_NAME_PREFIX.$name] : $property['DEFAULT'];
			?>
			<tr>
				<td align="right" width="40%" valign="top">
					<span class="<?=$required?'adm-required-field':''?>">
						<?= htmlspecialcharsbx(RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID)) ?>:
					</span>
					<?if (isset($property['DESCRIPTION'])):?>
						<br/><?= htmlspecialcharsbx(RestActivityTable::getLocalization($property['DESCRIPTION'], LANGUAGE_ID)) ?>
					<?endif;?>
				</td>
				<td width="60%">
					<?=$documentService->getFieldInputControl(
						$activityDocumentType,
						$property,
						array('Field' => static::PROPERTY_NAME_PREFIX.$name, 'Form' => $dialog->getFormName()),
						$value,
						true,
						false
					)?>
				</td>
			</tr>

			<?
		endforeach;

		if (static::checkAdminPermissions()):?>
			<tr>
				<td align="right" width="40%" valign="top"><span class=""><?= Loc::getMessage("BPRA_PD_USER_ID") ?>:</span></td>
				<td width="60%">
					<?=CBPDocument::ShowParameterField("user", 'authuserid', $currentValues['authuserid'], Array('rows'=>'1'))?>
				</td>
			</tr>
		<?endif?>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_SET_STATUS_MESSAGE") ?>:</td>
			<td>
				<select name="setstatusmessage">
					<option value="Y"<?= $currentValues["setstatusmessage"] == "Y" ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_YES") ?></option>
					<option value="N"<?= $currentValues["setstatusmessage"] == "N" ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_NO") ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_STATUS_MESSAGE") ?>:</td>
			<td valign="top"><?=CBPDocument::ShowParameterField("string", 'statusmessage', $currentValues['statusmessage'], Array('size'=>'45'))?></td>
		</tr>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_USE_SUBSCRIPTION") ?>:</td>
			<td>
				<select name="usesubscription" <?=!empty($activityData['USE_SUBSCRIPTION'])? 'disabled' : ''?>>
					<option value="Y"<?= $currentValues["usesubscription"] == 'Y' ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_YES") ?></option>
					<option value="N"<?= $currentValues["usesubscription"] == 'N' ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_NO") ?></option>
				</select>
			</td>
		</tr>
		<? if ($activityData['USE_SUBSCRIPTION'] != 'N'):?>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_TIMEOUT_DURATION") ?>:<br/><?= Loc::getMessage("BPRA_PD_TIMEOUT_DURATION_HINT") ?></td>
			<td valign="top">
				<?=CBPDocument::ShowParameterField('int', 'timeoutduration', $currentValues["timeoutduration"], array('size' => 20))?>
				<select name="timeoutdurationtype">
					<option value="s"<?= ($currentValues["timeoutdurationtype"] == "s") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_S") ?></option>
					<option value="m"<?= ($currentValues["timeoutdurationtype"] == "m") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_M") ?></option>
					<option value="h"<?= ($currentValues["timeoutdurationtype"] == "h") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_H") ?></option>
					<option value="d"<?= ($currentValues["timeoutdurationtype"] == "d") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_D") ?></option>
				</select>
				<?
				$delayMinLimit = CBPSchedulerService::getDelayMinLimit();
				if ($delayMinLimit):
					?>
					<p style="color: red;">* <?= Loc::getMessage("BPRA_PD_TIMEOUT_LIMIT") ?>: <?=CBPHelper::FormatTimePeriod($delayMinLimit)?></p>
					<?
				endif;
				?>
			</td>
		</tr>
		<?endif;

		return ob_get_clean();
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$workflowTemplate, &$workflowParameters, &$workflowVariables, $currentValues, &$errors)
	{
		$runtime = CBPRuntime::GetRuntime();
		$errors = array();

		$map = array(
			'setstatusmessage' => 'SetStatusMessage',
			'statusmessage' => 'StatusMessage',
			'usesubscription' => 'UseSubscription',
			'timeoutduration' => 'TimeoutDuration',
			'timeoutdurationtype' => 'TimeoutDurationType'
		);

		$properties = array();
		foreach ($map as $key => $value)
		{
			$properties[$value] = $currentValues[$key];
		}

		$activityData = self::getRestActivityData();
		$activityProperties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');
		$activityDocumentType = is_array($activityData['DOCUMENT_TYPE']) ? $activityData['DOCUMENT_TYPE'] : $documentType;

		foreach ($activityProperties as $name => $property)
		{
			$requestName = static::PROPERTY_NAME_PREFIX.strtolower($name);

			if (isset($properties[$requestName]))
				continue;

			$errors = array();

			$properties[$name] = $documentService->GetFieldInputValue(
				$activityDocumentType,
				$property,
				$requestName,
				$currentValues,
				$errors
			);

			if (count($errors) > 0)
				return false;
		}

		if (static::checkAdminPermissions())
		{
			$properties['AuthUserId'] = CBPHelper::usersStringToArray($currentValues['authuserid'], $documentType, $errors);
			if (count($errors) > 0)
				return false;
		}
		else
		{
			unset($properties['AuthUserId']);
		}

		if (!empty($activityData['USE_SUBSCRIPTION']))
			$properties['UseSubscription'] = $activityData['USE_SUBSCRIPTION'];

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();

		$activityData = self::getRestActivityData();

		if (!$activityData)
		{
			$errors[] = array(
				'code' => 'NoActivity',
				'parameter' => 'ActivityData',
				'message' => Loc::getMessage('BPRA_NOT_FOUND_ERROR')
			);
		}

		$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
		foreach ($properties as $name => $property)
		{
			$value = isset($property['DEFAULT']) ? $property['DEFAULT'] : null;
			if (isset($testProperties[$name]))
				$value = $testProperties[$name];
			if (CBPHelper::getBool($property['REQUIRED']) && CBPHelper::isEmptyValue($value))
			{
				$errors[] = array(
					'code' => 'NotExist',
					'parameter' => $name,
					'message' => Loc::getMessage('BPRA_PD_ERROR_EMPTY_PROPERTY',
						array(
							'#NAME#' => RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID)
						)
					)
				);
			}

		}

		if (
			isset($testProperties['AuthUserId'])
			&& isset($activityData['AUTH_USER_ID'])
			&& (string) $testProperties['AuthUserId'] !== $activityData['AUTH_USER_ID']
			&& !static::checkAdminPermissions()
		)
		{
			$errors[] = array(
				'code' => 'NotExist',
				'parameter' => 'AuthUserId',
				'message' => Loc::getMessage('BPRA_PD_ERROR_EMPTY_PROPERTY',
					array(
						'#NAME#' => Loc::getMessage('BPRA_PD_USER_ID')
					)
				)
			);
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	private function CalculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists('TimeoutDuration') ? $this->TimeoutDuration : 0);

		$timeoutDurationType = ($this->IsPropertyExists('TimeoutDurationType') ? $this->TimeoutDurationType : "s");
		$timeoutDurationType = strtolower($timeoutDurationType);
		if (!in_array($timeoutDurationType, array('s', 'd', 'h', 'm')))
			$timeoutDurationType = 's';

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

		return $timeoutDuration;
	}

	private static function checkAdminPermissions()
	{
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		return $user->isAdmin();
	}

	public static function generateToken($workflowId, $activityName, $eventId)
	{
		$signer = new \Bitrix\Main\Security\Sign\Signer;
		return $signer->sign($workflowId.'|'.$activityName.'|'.$eventId, self::TOKEN_SALT);
	}

	public static function extractToken($token)
	{
		$signer = new \Bitrix\Main\Security\Sign\Signer;

		try
		{
			$unsigned = $signer->unsign($token, self::TOKEN_SALT);
			$result = explode('|', $unsigned);
		}
		catch (\Exception $e)
		{
			$result = false;
		}

		return $result;
	}
}