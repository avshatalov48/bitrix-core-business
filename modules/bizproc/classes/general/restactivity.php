<?php

use Bitrix\Bizproc;
use Bitrix\Bizproc\RestActivityTable;
use Bitrix\Rest\Sqs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CBPRestActivity extends CBPActivity implements
	IBPEventActivity,
	IBPActivityExternalEventListener,
	IBPActivityDebugEventListener
{
	const TOKEN_SALT = 'bizproc';
	const PROPERTY_NAME_PREFIX = 'property_';
	const REST_ACTIVITY_ID = 0;
	protected static $restActivityData = [];

	protected $subscriptionId = 0;
	protected $eventId;

	private static function getRestActivityData()
	{
		if (!isset(static::$restActivityData[static::REST_ACTIVITY_ID]))
		{
			$result = RestActivityTable::getById(static::REST_ACTIVITY_ID);
			$row = $result->fetch();
			static::$restActivityData[static::REST_ACTIVITY_ID] = $row ?: [];
		}

		return static::$restActivityData[static::REST_ACTIVITY_ID];
	}

	public function __construct($name)
	{
		parent::__construct($name);

		$activityData = self::getRestActivityData();
		$this->arProperties = [
			'Title' => '',
			'UseSubscription' =>
				isset($activityData['USE_SUBSCRIPTION']) && $activityData['USE_SUBSCRIPTION'] === 'Y'
					? 'Y'
					: 'N'
			,
			'IsTimeout' => 0,
			'AuthUserId' => isset($activityData['AUTH_USER_ID']) ? 'user_' . $activityData['AUTH_USER_ID'] : null,
			'SetStatusMessage' => 'Y',
			'StatusMessage' => '',
			'TimeoutDuration' => 0,
			'TimeoutDurationType' => 's',
		];

		if (!empty($activityData['PROPERTIES']))
		{
			foreach ($activityData['PROPERTIES'] as $propertyName => $property)
			{
				if (isset($this->arProperties[$propertyName]))
				{
					continue;
				}
				$this->arProperties[$propertyName] = $property['DEFAULT'] ?? null;
			}
		}

		$types = [];
		if (!empty($activityData['RETURN_PROPERTIES']))
		{
			foreach ($activityData['RETURN_PROPERTIES'] as $returnPropertyName => $property)
			{
				if (isset($this->arProperties[$returnPropertyName]))
				{
					continue;
				}
				$this->arProperties[$returnPropertyName] = $property['DEFAULT'] ?? null;
				if (isset($property['TYPE']))
				{
					$types[$returnPropertyName] = [
						'Type' => $property['TYPE'],
						'Multiple' => CBPHelper::getBool($property['MULTIPLE']),
						'Options' => $property['OPTIONS'] ?? null,
					];
				}
			}
		}
		$types['IsTimeout'] = [
			'Type' => 'int',
		];
		$this->SetPropertiesTypes($types);
	}

	protected function reInitialize()
	{
		parent::ReInitialize();

		$this->IsTimeout = 0;
		$this->eventId = null;
		$activityData = self::getRestActivityData();
		if (!empty($activityData['RETURN_PROPERTIES']))
		{
			foreach ($activityData['RETURN_PROPERTIES'] as $name => $property)
			{
				$this->__set($name, $property['DEFAULT'] ?? null);
			}
		}
	}

	public function execute()
	{
		$activityData = static::getRestActivityData();

		if (!$activityData)
		{
			throw new Exception(Loc::getMessage('BPRA_NOT_FOUND_ERROR'));
		}

		if (!Loader::includeModule('rest') || !\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$properties = $activityData['PROPERTIES'];
		$propertiesValues = [];
		if (!empty($properties))
		{
			/** @var CBPDocumentService $documentService */
			$documentService = $this->workflow->GetService('DocumentService');
			foreach ($properties as $name => $property)
			{
				$property = static::normalizeProperty($property);
				$properties[$name] = $property;
				$propertiesValues[$name] = $this->__get($name);

				if ($propertiesValues[$name])
				{
					$fieldTypeObject = $documentService->getFieldTypeObject($this->GetDocumentType(), $property);
					if ($fieldTypeObject)
					{
						$fieldTypeObject->setDocumentId($this->GetDocumentId());
						$propertiesValues[$name] = $fieldTypeObject->externalizeValue(
							$this->GetName(),
							$propertiesValues[$name]
						);
					}
				}

				if ($propertiesValues[$name] === null)
				{
					$propertiesValues[$name] = '';
				}
			}

			if ($this->workflow->isDebug())
			{
				$map = $this->getDebugInfo(
					$propertiesValues,
					$properties
				);
				$this->writeDebugInfo($map);
			}
		}

		$dbRes = \Bitrix\Rest\AppTable::getList([
			'filter' => [
				'=CLIENT_ID' => $activityData['APP_ID'],
			],
		]);
		$application = $dbRes->fetch();

		if (!$application)
		{
			throw new Exception('Rest application not found.');
		}

		$appStatus = \Bitrix\Rest\AppTable::getAppStatusInfo($application, '');
		if ($appStatus['PAYMENT_ALLOW'] === 'N')
		{
			throw new Exception('Rest application status error: payment required');
		}

		$userId = CBPHelper::ExtractUsers($this->AuthUserId, $this->GetDocumentId(), true);
		if (empty($userId) && !empty($activityData['AUTH_USER_ID']))
		{
			$userId = $activityData['AUTH_USER_ID'];
		}

		$auth = [
			'WORKFLOW_ID' => $this->getWorkflowInstanceId(),
			'ACTIVITY_NAME' => $this->name,
			'CODE' => $activityData['CODE'],
			\Bitrix\Rest\OAuth\Auth::PARAM_LOCAL_USER => $userId,
			"application_token" => \CRestUtil::getApplicationToken($application),
		];

		$this->eventId = \Bitrix\Main\Security\Random::getString(32, true);

		$queryItems = [
			Sqs::queryItem(
				$activityData['APP_ID'],
				$activityData['HANDLER'],
				[
					'workflow_id' => $this->getWorkflowInstanceId(),
					'code' => $activityData['CODE'],
					'document_id' => $this->GetDocumentId(),
					'document_type' => $this->GetDocumentType(),
					'event_token' => self::generateToken($this->getWorkflowInstanceId(), $this->name, $this->eventId),
					'properties' => $propertiesValues,
					'use_subscription' => $this->UseSubscription,
					'timeout_duration' => $this->CalculateTimeoutDuration(),
					'ts' => time(),
				],
				$auth,
				[
					"sendAuth" => true,
					"sendRefreshToken" => true,
					"category" => Sqs::CATEGORY_BIZPROC,
				]
			),
		];

		\Bitrix\Rest\OAuthService::getEngine()->getClient()->sendEvent($queryItems);

		if (is_callable(['\Bitrix\Rest\UsageStatTable', 'logRobot']))
		{
			if ($activityData['IS_ROBOT'] === 'Y')
			{
				\Bitrix\Rest\UsageStatTable::logRobot($activityData['APP_ID'], $activityData['CODE']);
			}
			else
			{
				\Bitrix\Rest\UsageStatTable::logActivity($activityData['APP_ID'], $activityData['CODE']);
			}

			\Bitrix\Rest\UsageStatTable::finalize();
		}

		if ($this->SetStatusMessage === 'Y')
		{
			$message = $this->StatusMessage;
			if (empty($message))
			{
				$message = Loc::getMessage('BPRA_DEFAULT_STATUS_MESSAGE');
			}
			$this->SetStatusTitle($message);
		}

		if ($this->UseSubscription !== 'Y')
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->Subscribe($this);

		return CBPActivityExecutionStatus::Executing;
	}

	public function subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$schedulerService = $this->workflow->GetService('SchedulerService');
			$this->subscriptionId = $schedulerService->SubscribeOnTime(
				$this->workflow->GetInstanceId(),
				$this->name,
				time() + $timeoutDuration
			);
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
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

	public function onExternalEvent($eventParameters = [])
	{
		if ($this->executionStatus === CBPActivityExecutionStatus::Closed)
		{
			return;
		}

		$onAgent = (array_key_exists('SchedulerService', $eventParameters) && $eventParameters['SchedulerService'] === 'OnAgent');
		if ($onAgent)
		{
			$this->IsTimeout = 1;
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);

			return;
		}

		if ($this->eventId !== (string)$eventParameters['EVENT_ID'])
		{
			return;
		}

		$this->WriteToTrackingService(
			!empty($eventParameters['LOG_MESSAGE']) && is_string($eventParameters['LOG_MESSAGE'])
				? $eventParameters['LOG_MESSAGE']
				: Loc::getMessage('BPRA_DEFAULT_LOG_MESSAGE')
		);

		if (!empty($eventParameters['RETURN_VALUES']))
		{
			$activityData = self::getRestActivityData();
			$whiteList = array();
			if (!empty($activityData['RETURN_PROPERTIES']))
			{
				foreach ($activityData['RETURN_PROPERTIES'] as $name => $property)
				{
					$whiteList[mb_strtoupper($name)] = $name;
				}
			}

			/** @var CBPDocumentService $documentService */
			$documentService = $this->workflow->GetService('DocumentService');
			$eventParameters['RETURN_VALUES'] = array_change_key_case(
				(array)$eventParameters['RETURN_VALUES'],
				CASE_UPPER
			);
			foreach ($eventParameters['RETURN_VALUES'] as $name => $value)
			{
				if (!isset($whiteList[$name]))
				{
					continue;
				}

				$property = $activityData['RETURN_PROPERTIES'][$whiteList[$name]];
				if ($property && $value)
				{
					$property = static::normalizeProperty($property);
					$fieldTypeObject = $documentService->getFieldTypeObject($this->GetDocumentType(), $property);
					if ($fieldTypeObject)
					{
						$fieldTypeObject->setDocumentId($this->GetDocumentId());
						$value = $fieldTypeObject->internalizeValue($this->GetName(), $value);
					}

					$map = $this->getDebugInfo(
						[$name => $value],
						[$name => $property]
					);
					$this->writeDebugInfo($map);
				}

				$this->__set($whiteList[$name], $value);
			}
		}

		if (empty($eventParameters['LOG_ACTION']))
		{
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	public function onDebugEvent(array $eventParameters = [])
	{
		if ($this->executionStatus === CBPActivityExecutionStatus::Closed)
		{
			return;
		}

		$this->writeDebugTrack(
			$this->getWorkflowInstanceId(),
			$this->getName(),
			$this->executionStatus,
			$this->executionResult,
			$this->Title,
			\Bitrix\Main\Localization\Loc::getMessage('BPRA_DEBUG_EVENT')
		);

		$this->Unsubscribe($this);
		$this->workflow->CloseActivity($this);
	}

	public function cancel()
	{
		if ($this->UseSubscription === 'Y')
		{
			$this->Unsubscribe($this);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function getPropertiesDialog(
		$documentType,
		$activityName,
		$workflowTemplate,
		$workflowParameters,
		$workflowVariables,
		$currentValues = null,
		$formName = ""
	)
	{
		if (!Loader::includeModule('rest'))
		{
			return false;
		}

		$activityData = self::getRestActivityData();

		$dbRes = \Bitrix\Rest\AppTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=CLIENT_ID' => $activityData['APP_ID'],
			],
		]);
		$application = $dbRes->fetch();

		if ($application)
		{
			$activityData['APP_ID_INT'] = $application['ID'];
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $workflowTemplate,
			'workflowParameters' => $workflowParameters,
			'workflowVariables' => $workflowVariables,
			'currentValues' => $currentValues,
			'formName' => $formName,
		]);

		$map = [
			'AuthUserId' => [
				'Name' => Loc::getMessage("BPRA_PD_USER_ID"),
				'FieldName' => 'authuserid',
				'Type' => 'user',
				'Default' => 'user_' . $activityData['AUTH_USER_ID'],
			],
			'SetStatusMessage' => [
				'Name' => 'SetStatusMessage',
				'FieldName' => 'setstatusmessage',
				'Type' => 'bool',
			],
			'StatusMessage' => [
				'Name' => 'StatusMessage',
				'FieldName' => 'statusmessage',
				'Type' => 'text',
				'Default' => Loc::getMessage('BPRA_DEFAULT_STATUS_MESSAGE'),
			],
			'UseSubscription' => [
				'Name' => 'StatusMessage',
				'FieldName' => 'usesubscription',
				'Type' => 'bool',
				'Default' => $activityData['USE_SUBSCRIPTION'],
			],
			'TimeoutDuration' => [
				'Name' => 'StatusMessage',
				'FieldName' => 'timeoutduration',
				'Type' => 'int',
			],
			'TimeoutDurationType' => [
				'Name' => 'StatusMessage',
				'FieldName' => 'timeoutdurationtype',
				'Type' => 'string',
				'Default' => 's',
			],
		];

		$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : [];
		foreach ($properties as $name => $property)
		{
			if (!array_key_exists($name, $map))
			{
				$map[$name] = [
					'Name' => RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID),
					'Description' => RestActivityTable::getLocalization($property['DESCRIPTION'] ?? '', LANGUAGE_ID),
					'FieldName' => static::PROPERTY_NAME_PREFIX . mb_strtolower($name),
					'Type' => $property['TYPE'] ?? 'string',
					'Required' => $property['REQUIRED'] ?? false,
					'Multiple' => $property['MULTIPLE'] ?? false,
					'Default' => $property['DEFAULT'] ?? null,
					'Options' => $property['OPTIONS'] ?? null,
				];
			}
		}

		$appPlacement = null;
		if (!empty($activityData['APP_ID_INT']))
		{
			$appPlacement = self::getAppPlacement($activityData['APP_ID_INT'], $activityData['CODE']);
		}

		$dialog
			->setMap($map)
			->setRuntimeData([
				'ACTIVITY_DATA' => $activityData,
				'IS_ADMIN' => static::checkAdminPermissions(),
				'APP_PLACEMENT' => $appPlacement,
			])
			->setRenderer([__CLASS__, 'renderPropertiesDialog']);

		return $dialog;
	}

	private static function getAppPlacement(int $appId, string $code): ?array
	{
		$result = \Bitrix\Rest\PlacementTable::getList([
			'filter' => [
				'=APP_ID' => $appId,
				'=ADDITIONAL' => $code,
				'=PLACEMENT' => \Bitrix\Bizproc\RestService::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
			],
		])->fetch();

		return $result ?: null;
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

		$currentValues = $dialog->getCurrentValues();

		$appPlacement = $data['APP_PLACEMENT'];
		$placementSid = null;
		$appCurrentValues = [];

		ob_start();

		if ($appPlacement):

			foreach ($properties as $key => $property)
			{
				$appCurrentValues[$key] = $dialog->getCurrentValue('property_'.mb_strtolower($key));
			}

			global $APPLICATION;
			echo '<tr><td align="right" colspan="2">';
			$placementSid = $APPLICATION->includeComponent(
				'bitrix:app.layout',
				'',
				array(
					'ID' => $appPlacement['APP_ID'],
					'PLACEMENT' => \Bitrix\Bizproc\RestService::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
					'PLACEMENT_ID' => $appPlacement['ID'],
					"PLACEMENT_OPTIONS" => [
						'code' => $activityData['CODE'],
						'activity_name' => $dialog->getActivityName(),
						'properties' => $properties,
						'current_values' => $appCurrentValues,
						'document_type' => $dialog->getDocumentType(),
						'document_fields' => $documentService->GetDocumentFields($dialog->getDocumentType())
					],
					'PARAM' => array(
						'FRAME_WIDTH' => '100%',
						'FRAME_HEIGHT' => '350px'
					),
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			echo '</td></tr>';
		else:

		foreach ($properties as $name => $property):
			$required = CBPHelper::getBool($property['REQUIRED']);
			$name = mb_strtolower($name);
			$value = !CBPHelper::isEmptyValue($currentValues[static::PROPERTY_NAME_PREFIX.$name]) ? $currentValues[static::PROPERTY_NAME_PREFIX.$name] : $property['DEFAULT'];

			$property['NAME'] = RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
			if (isset($property['DESCRIPTION']))
			{
				$property['DESCRIPTION'] = RestActivityTable::getLocalization($property['DESCRIPTION'], LANGUAGE_ID);
			}

			?>
			<tr>
				<td align="right" width="40%" valign="top">
					<span class="<?=$required?'adm-required-field':''?>">
						<?= htmlspecialcharsbx($property['NAME']) ?>:
					</span>
					<?if (!empty($property['DESCRIPTION'])):?>
						<br/><?= htmlspecialcharsbx($property['DESCRIPTION']) ?>
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

		endif;

		if (static::checkAdminPermissions()):?>
			<tr>
				<td align="right" width="40%" valign="top"><span class=""><?= Loc::getMessage("BPRA_PD_USER_ID") ?>:</span></td>
				<td width="60%">
					<?=$dialog->renderFieldControl('AuthUserId', $currentValues['authuserid'], true, 0)?>
				</td>
			</tr>
		<?endif?>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_SET_STATUS_MESSAGE") ?>:</td>
			<td>
				<select name="setstatusmessage">
					<option value="Y"<?= $currentValues["setstatusmessage"] === "Y" ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_YES") ?></option>
					<option value="N"<?= $currentValues["setstatusmessage"] === "N" ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_NO") ?></option>
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
					<option value="Y"<?= $currentValues["usesubscription"] === 'Y' ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_YES") ?></option>
					<option value="N"<?= $currentValues["usesubscription"] === 'N' ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_NO") ?></option>
				</select>
			</td>
		</tr>
		<? if ($activityData['USE_SUBSCRIPTION'] !== 'N'):?>
		<tr>
			<td align="right"><?= Loc::getMessage("BPRA_PD_TIMEOUT_DURATION") ?>:<br/><?= Loc::getMessage("BPRA_PD_TIMEOUT_DURATION_HINT") ?></td>
			<td valign="top">
				<?=CBPDocument::ShowParameterField('int', 'timeoutduration', $currentValues["timeoutduration"], array('size' => 20))?>
				<select name="timeoutdurationtype">
					<option value="s"<?= ($currentValues["timeoutdurationtype"] === "s") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_S") ?></option>
					<option value="m"<?= ($currentValues["timeoutdurationtype"] === "m") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_M") ?></option>
					<option value="h"<?= ($currentValues["timeoutdurationtype"] === "h") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_H") ?></option>
					<option value="d"<?= ($currentValues["timeoutdurationtype"] === "d") ? " selected" : "" ?>><?= Loc::getMessage("BPRA_PD_TIME_D") ?></option>
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

		if ($placementSid):?>
			<script>
				BX.ready(function()
				{
					var appLayout = BX.rest.AppLayout.get('<?=CUtil::JSEscape($placementSid)?>');
					var properties = <?=\Bitrix\Main\Web\Json::encode($properties)?>;
					var values = <?=\Bitrix\Main\Web\Json::encode($appCurrentValues)?>;
					var form = document.forms['<?=CUtil::JSEscape($dialog->getFormName())?>'];

					function setValueToForm(name, value)
					{
						name = 'property_' + name.toLowerCase();
						if (BX.type.isArray(value))
						{
							name += '[]';
						}
						else
						{
							value = [value];
						}

						Array.from(form.querySelectorAll('[name="'+name+'"]')).forEach(function(element)
						{
							BX.remove(element);
						});

						value.forEach(function(val)
						{
							form.appendChild(BX.create('input', {
								props: {
									type: 'hidden',
									name: name,
									value: val
								}
							}));
						});
					}

					var placementInterface = appLayout.messageInterface;
					placementInterface.setPropertyValue = function(param, callback)
					{
						for (var key in param)
						{
							if (properties[key])
							{
								setValueToForm(key, param[key]);
							}
						}
					}

					for(var k in values)
					{
						setValueToForm(k, values[k]);
					}
				});
			</script>
		<?php endif;

		return ob_get_clean();
	}

	public static function getPropertiesDialogValues(
		$documentType,
		$activityName,
		&$workflowTemplate,
		&$workflowParameters,
		&$workflowVariables,
		$currentValues,
		&$errors
	)
	{
		$runtime = CBPRuntime::GetRuntime();
		$errors = [];

		$map = [
			'setstatusmessage' => 'SetStatusMessage',
			'statusmessage' => 'StatusMessage',
			'usesubscription' => 'UseSubscription',
			'timeoutduration' => 'TimeoutDuration',
			'timeoutdurationtype' => 'TimeoutDurationType',
		];

		$properties = [];
		foreach ($map as $key => $value)
		{
			$properties[$value] = $currentValues[$key] ?? null;
		}

		$activityData = self::getRestActivityData();
		$activityProperties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : [];
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');
		$activityDocumentType = is_array($activityData['DOCUMENT_TYPE']) ? $activityData['DOCUMENT_TYPE'] : $documentType;

		foreach ($activityProperties as $name => $property)
		{
			$requestName = static::PROPERTY_NAME_PREFIX . mb_strtolower($name);

			if (isset($properties[$requestName]))
			{
				continue;
			}

			$errors = [];

			$properties[$name] = $documentService->GetFieldInputValue(
				$activityDocumentType,
				$property,
				$requestName,
				$currentValues,
				$errors
			);

			if (count($errors) > 0)
			{
				return false;
			}
		}

		if (static::checkAdminPermissions() && isset($currentValues['authuserid']))
		{
			$properties['AuthUserId'] = CBPHelper::usersStringToArray(
				$currentValues['authuserid'],
				$documentType,
				$errors
			);
			if (count($errors) > 0)
			{
				return false;
			}
		}
		else
		{
			unset($properties['AuthUserId']);
		}

		if (!empty($activityData['USE_SUBSCRIPTION']))
		{
			$properties['UseSubscription'] = $activityData['USE_SUBSCRIPTION'];
		}

		$errors = self::ValidateProperties(
			$properties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function validateProperties($testProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		$activityData = self::getRestActivityData();

		if (!$activityData)
		{
			return $errors;
		}

		$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : [];
		foreach ($properties as $name => $property)
		{
			$value = $testProperties[$name] ?? $property['DEFAULT'] ?? null;
			if (CBPHelper::getBool($property['REQUIRED'] ?? false) && CBPHelper::isEmptyValue($value))
			{
				$errors[] = [
					'code' => 'NotExist',
					'parameter' => $name,
					'message' => Loc::getMessage('BPRA_PD_ERROR_EMPTY_PROPERTY',
						[
							'#NAME#' => RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID),
						]
					),
				];
			}
		}

		if (
			isset($testProperties['AuthUserId'], $activityData['AUTH_USER_ID'])
			&& CBPHelper::stringify($testProperties['AuthUserId']) !== 'user_' . $activityData['AUTH_USER_ID']
			&& !static::checkAdminPermissions()
		)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'AuthUserId',
				'message' => Loc::getMessage('BPRA_PD_ERROR_EMPTY_PROPERTY',
					[
						'#NAME#' => Loc::getMessage('BPRA_PD_USER_ID'),
					]
				),
			];
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	private function calculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists('TimeoutDuration') ? $this->TimeoutDuration : 0);

		$timeoutDurationType = ($this->IsPropertyExists('TimeoutDurationType') ? $this->TimeoutDurationType : "s");
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

	private static function checkAdminPermissions()
	{
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		return $user->isAdmin();
	}

	private static function normalizeProperty(array $property): array
	{
		$property['NAME'] = RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
		$property['DESCRIPTION'] = RestActivityTable::getLocalization($property['DESCRIPTION'] ?? '', LANGUAGE_ID);

		return  Bizproc\FieldType::normalizeProperty($property);
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
