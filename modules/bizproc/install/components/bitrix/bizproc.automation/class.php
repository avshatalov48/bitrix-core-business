<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Main\UI\Extension::load('ui.alerts');

Loc::loadMessages(__FILE__);

class BizprocAutomationComponent extends \CBitrixComponent
{
	protected function getDocumentType()
	{
		return isset($this->arParams['DOCUMENT_TYPE']) && is_array($this->arParams['DOCUMENT_TYPE']) ? $this->arParams['DOCUMENT_TYPE'] : null;
	}

	protected function getDocumentId()
	{
		return isset($this->arParams['DOCUMENT_ID']) ? $this->arParams['DOCUMENT_ID'] : 0;
	}

	private function getStatusesEditUrl()
	{
		return isset($this->arParams['STATUSES_EDIT_URL']) ? $this->arParams['STATUSES_EDIT_URL'] : null;
	}

	private function getWorkflowEditUrl()
	{
		return isset($this->arParams['WORKFLOW_EDIT_URL']) ? $this->arParams['WORKFLOW_EDIT_URL'] : null;
	}
	private function getConstantsEditUrl()
	{
		return isset($this->arParams['CONSTANTS_EDIT_URL']) ? $this->arParams['CONSTANTS_EDIT_URL'] : null;
	}
	private function getParametersEditUrl()
	{
		return isset($this->arParams['PARAMETERS_EDIT_URL']) ? $this->arParams['PARAMETERS_EDIT_URL'] : null;
	}

	private function getTitleView()
	{
		return isset($this->arParams['TITLE_VIEW']) ? $this->arParams['TITLE_VIEW'] : null;
	}

	private function getTitleEdit()
	{
		return isset($this->arParams['TITLE_EDIT']) ? $this->arParams['TITLE_EDIT'] : null;
	}

	protected function getDocumentCategoryId()
	{
		return isset($this->arParams['DOCUMENT_CATEGORY_ID']) ? (int)$this->arParams['DOCUMENT_CATEGORY_ID'] : null;
	}

	protected function isApiMode()
	{
		return (isset($this->arParams['API_MODE']) && $this->arParams['API_MODE'] === 'Y');
	}

	protected function isOneTemplateMode()
	{
		return (isset($this->arParams['ONE_TEMPLATE_MODE']) && $this->arParams['ONE_TEMPLATE_MODE'] === true);
	}

	protected function getTemplateInfo()
	{
		return isset($this->arParams['TEMPLATE']) ? $this->arParams['TEMPLATE'] : null;
	}

	protected function getTemplates(array $statuses)
	{
		$relation = array();

		$documentType = $this->getDocumentType();

		foreach ($statuses as $status)
		{
			$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType, $status);

			if ($template->getId() > 0)
			{
				$templateArray = $template->toArray();
				foreach ($templateArray['ROBOTS'] as $i => $robot)
				{
					$templateArray['ROBOTS'][$i]['viewData'] = static::getRobotViewData($robot, $documentType);
				}

				$relation[$status] = $templateArray;
			}
			else
			{
				$template->save(array(), 1); // save bizproc template
				$relation[$status] = $template->toArray();
			}
		}

		return array_values($relation);
	}

	protected function prepareTemplateForView()
	{
		$template = $this->getTemplateInfo();

		if ($template)
		{
			$tplRow = $template['ID'] > 0 ? \Bitrix\Bizproc\WorkflowTemplateTable::getById($template['ID']) : null;
			$tpl = $tplRow ? $tplRow->fetchObject() : null;
			if (!$tpl)
			{
				$documentType = $this->getDocumentType();
				$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType);
				$template->setDocumentStatus('SCRIPT');
			}
			else
			{
				$documentType = $tpl->getDocumentComplexType();
				$template = \Bitrix\Bizproc\Automation\Engine\Template::createByTpl($tpl);
			}

			$templateArray = $template->toArray();
			foreach ($templateArray['ROBOTS'] as $i => $robot)
			{
				$templateArray['ROBOTS'][$i]['viewData'] = static::getRobotViewData($robot, $documentType);
			}

			foreach (['PARAMETERS', 'CONSTANTS'] as $key)
			{
				foreach ($templateArray[$key] as $id => $property)
				{
					if ($property['Type'] === 'user')
					{
						$templateArray[$key][$id]['Default'] = CBPHelper::UsersArrayToString(
							$templateArray[$key][$id]['Default'], [], $documentType
						);
					}
				}
			}

			return $templateArray;
		}

		return null;
	}

	protected function getTemplateStatusList()
	{
		$template = $this->getTemplateInfo();
		$list = [];

		if ($template)
		{
			$status = 'SCRIPT';
			$list[$status] = [
				'STATUS_ID' => $status,
				'NAME' => 'script'
			];
		}

		return $list;
	}

	public static function getTemplateViewData(array $template, $documentType)
	{
		foreach ($template['ROBOTS'] as $i => $robot)
		{
			$template['ROBOTS'][$i]['viewData'] = self::getRobotViewData($robot, $documentType);
		}
		return $template;
	}

	public static function getRobotViewData($robot, array $documentType)
	{
		$availableRobots = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);
		$result = array(
			'responsibleLabel' => '',
			'responsibleUrl' => '',
			'responsibleId' => 0,
		);

		$type = mb_strtolower($robot['Type']);
		if (isset($availableRobots[$type]) && isset($availableRobots[$type]['ROBOT_SETTINGS']))
		{
			$settings = $availableRobots[$type]['ROBOT_SETTINGS'];

			if ($settings['RESPONSIBLE_TO_HEAD'] && $robot['Properties'][$settings['RESPONSIBLE_TO_HEAD']] == 'Y')
			{
				$result['responsibleLabel'] = Loc::getMessage('BIZPROC_AUTOMATION_TO_HEAD');
			}

			if (isset($settings['RESPONSIBLE_PROPERTY']))
			{
				$users = self::getUsersFromResponsibleProperty($robot, $settings['RESPONSIBLE_PROPERTY']);
				$usersLabel = CBPHelper::UsersArrayToString($users, [], $documentType, false);

				if ($result['responsibleLabel'] && $usersLabel)
				{
					$result['responsibleLabel'] .= ', ';
				}
				$result['responsibleLabel'] .= $usersLabel;

				if ($users && count($users) == 1 && $users[0] && mb_strpos($users[0], 'user_') === 0)
				{
					$id = (int) \CBPHelper::StripUserPrefix($users[0]);
					$result['responsibleUrl'] = CComponentEngine::MakePathFromTemplate(
						'/company/personal/user/#user_id#/',
						array('user_id' => $id)
					);
					$result['responsibleId'] = $id;
				}
			}
		}
		return $result;
	}

	private static function getUsersFromResponsibleProperty(array $robot, $propertyName)
	{
		$value = null;
		$props = $robot['Properties'];
		$path = explode('.', $propertyName);

		foreach ($path as $chain)
		{
			$value = ($props && is_array($props) && isset($props[$chain])) ? $props[$chain] : null;
			$props = $value;
		}

		return $value ? (array)$value : null;
	}

	public function executeComponent()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			return $this->showError(Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));
		}

		$documentType = $this->getDocumentType();
		$documentCategoryId = $this->getDocumentCategoryId();
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();

		if ($this->isApiMode())
		{
			$this->arResult['DOCUMENT_FIELDS'] = $this->getDocumentFields();
			$this->arResult['DOCUMENT_USER_GROUPS'] = $this->getDocumentUserGroups();
			$this->arResult['DOCUMENT_SIGNED'] = static::signDocument($documentType, $documentCategoryId, null);
			$this->arResult['DOCUMENT_NAME'] = $documentService->getEntityName($documentType[0], $documentType[1]);
			$this->includeComponentTemplate('api');
			return;
		}

		$target = null;

		if (!$this->isOneTemplateMode())
		{
			/** @var \Bitrix\Bizproc\Automation\Target\BaseTarget $target */
			$target = $documentService->createAutomationTarget($documentType);

			if (!$target)
			{
				return $this->showError(Loc::getMessage('BIZPROC_AUTOMATION_NOT_SUPPORTED'));
			}

			if (!$target->isAvailable())
			{
				return $this->showError(Loc::getMessage('BIZPROC_AUTOMATION_NOT_AVAILABLE'));
			}
		}

		$tplUser = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);

		$canRead = $canEdit = (
			$tplUser->isAdmin() ||
			CBPDocument::CanUserOperateDocumentType(
				CBPCanUserOperateOperation::CreateAutomation,
				$GLOBALS["USER"]->GetID(),
				$documentType,
				['DocumentCategoryId' => $documentCategoryId]
			)
		);
		$documentId = $this->getDocumentId();

		if ($target)
		{
			$target->setDocumentId($documentId);
		}

		if (!$canEdit)
		{
			if ($documentId)
			{
				$canRead = CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::ReadDocument,
					$GLOBALS["USER"]->GetID(),
					[$documentType[0], $documentType[1], $documentId],
					['DocumentCategoryId' => $documentCategoryId]
				);
			}
			else
			{
				$canRead = CBPDocument::CanUserOperateDocumentType(
					CBPCanUserOperateOperation::ReadDocument,
					$GLOBALS["USER"]->GetID(),
					$documentType,
					['DocumentCategoryId' => $documentCategoryId]
				);
			}

			if (!$canRead)
			{
				return $this->showError(Loc::getMessage('BIZPROC_AUTOMATION_ACCESS_DENIED'));
			}
		}

		if (!$canRead && !$canEdit)
		{
			return $this->showError(Loc::getMessage('BIZPROC_AUTOMATION_NO_EDIT_PERMISSIONS'));
		}

		if (isset($this->arParams['ACTION']) && $this->arParams['ACTION'] == 'ROBOT_SETTINGS')
		{
			$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType);

			$dialog = $template->getRobotSettingsDialog($this->arParams['~ROBOT_DATA']);

			if ($dialog === '')
			{
				return;
			}

			if (!($dialog instanceof \Bitrix\Bizproc\Activity\PropertiesDialog))
			{
				ShowError('Robot dialog not supported in current context.');
				return;
			}

			if (is_array($this->arParams['~CONTEXT']))
				$dialog->setContext($this->arParams['~CONTEXT']);

			if (mb_strpos($this->arParams['~ROBOT_DATA']['Type'], 'rest_') === 0)
			{
				$this->arResult = array('dialog' => $dialog);
				$this->includeComponentTemplate('rest_robot_properties_dialog');
				return;
			}

			$dialog->setDialogFileName('robot_properties_dialog');
			echo $dialog;
			return;
		}

		$statusList = $target ? $target->getDocumentStatusList($documentCategoryId) : $this->getTemplateStatusList();

		$log = [];
		if ($documentId && $target)
		{
			$tracker = new \Bitrix\Bizproc\Automation\Tracker($target);
			$log = $tracker->getLog(array_keys($statusList));
		}

		$availableRobots = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);

		$triggers = [];
		if ($target)
		{
			$triggers = $target->getTriggers(array_keys($statusList));
			$target->prepareTriggersToShow($triggers);
		}

		$this->arResult = array(
			'CAN_EDIT' => $canEdit,
			'TITLE_VIEW' => $this->getTitleView(),
			'TITLE_EDIT' => $this->getTitleEdit(),
			'DOCUMENT_STATUS' => $target ? $target->getDocumentStatus() : null,
			'DOCUMENT_TYPE' => $documentType,
			'DOCUMENT_ID' => $documentId,
			'DOCUMENT_CATEGORY_ID' => $documentCategoryId,
			'DOCUMENT_SIGNED' => static::signDocument($documentType, $documentCategoryId, $documentId),
			'ENTITY_NAME' => $documentService->getEntityName($documentType[0], $documentType[1]),
			'STATUSES' => $statusList,
			'TEMPLATES' => $target ? $this->getTemplates(array_keys($statusList)) : [$this->prepareTemplateForView()],
			'TRIGGERS' => $triggers,
			'AVAILABLE_TRIGGERS' => $target ? $target->getAvailableTriggers() : [],
			'AVAILABLE_ROBOTS' => array_values($availableRobots),
			'GLOBAL_CONSTANTS' => \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll(),
			'DOCUMENT_FIELDS' => $this->getDocumentFields(),
			'LOG' => $log,
			'WORKFLOW_EDIT_URL' => $this->getWorkflowEditUrl(),
			'CONSTANTS_EDIT_URL' => $this->getConstantsEditUrl(),
			'PARAMETERS_EDIT_URL' => $this->getParametersEditUrl(),
			'STATUSES_EDIT_URL' => $this->getStatusesEditUrl(),
			'USER_OPTIONS' => [
				'defaults' => \CUserOptions::GetOption('bizproc.automation', 'defaults', []),
				'save_state_checkboxes' => \CUserOptions::GetOption('bizproc.automation', 'save_state_checkboxes', [])
			],
			'FRAME_MODE' => $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER',
			'USE_DISK' => Main\Loader::includeModule('disk'),
			'IS_EMBEDDED' => $this->isOneTemplateMode(),
			'SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING' => (
				isset($this->arParams['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'])
				&& $this->arParams['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'] === 'Y'
			)
		);

		$this->prepareDelayMinLimitResult();
		$this->includeComponentTemplate();
	}

	public static function signDocument(array $documentType, $documentCategoryId, $documentId)
	{
		$signer = new Main\Security\Sign\Signer;
		$jsonData = Main\Web\Json::encode([$documentType, $documentCategoryId, $documentId]);

		return $signer->sign($jsonData, 'bizproc.automation.document');
	}

	/**
	 * @param string $unsignedData
	 * @return array
	 */
	public static function unSignDocument($unsignedData)
	{
		$signer = new Main\Security\Sign\Signer;

		try
		{
			$unsigned = $signer->unsign($unsignedData, 'bizproc.automation.document');
			$result = Main\Web\Json::decode($unsigned);
		}
		catch (\Exception $e)
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	public static function getDestinationData(array $documentType)
	{
		$result = ['LAST' => []];

		if (!Main\Loader::includeModule('socialnetwork'))
		{
			return [];
		}

		$arStructure = CSocNetLogDestination::GetStucture(array());
		$result['DEPARTMENT'] = $arStructure['department'];
		$result['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$result['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		$result['DEST_SORT'] = CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "BIZPROC_AUTOMATION",
		));

		CSocNetLogDestination::fillLastDestination(
			$result['DEST_SORT'],
			$result['LAST']
		);

		$destUser = array();
		foreach ($result["LAST"]["USERS"] as $value)
		{
			$destUser[] = str_replace("U", "", $value);
		}

		$result["USERS"] = \CSocNetLogDestination::getUsers(array("id" => $destUser));
		$result["ROLES"] = array();

		$documentUserFields = \Bitrix\Bizproc\Automation\Helper::getDocumentFields($documentType, 'user');

		foreach ($documentUserFields as $field)
		{
			$result["ROLES"]['BPR_'.$field['Id']] = array(
				'id' => 'BPR_'.$field['Id'],
				'entityId' => $field['Expression'],
				'name' => $field['Name'],
				'avatar' => '',
				'desc' => '&nbsp;'
			);
		}

		$result["LAST"]["USERS"]["ROLES"] = array();

		return $result;
	}

	private function getDocumentFields($filter = null)
	{
		return array_values(\Bitrix\Bizproc\Automation\Helper::getDocumentFields($this->getDocumentType(), $filter));
	}

	private function getDocumentUserGroups()
	{
		return \Bitrix\Bizproc\Automation\Helper::getDocumentUserGroups($this->getDocumentType());
	}

	private function showError($message)
	{
		echo <<<HTML
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
				<span class="ui-alert-message">{$message}</span>
			</div>
HTML;
		return;
	}

	private function prepareDelayMinLimitResult()
	{
		$this->arResult['DELAY_MIN_LIMIT_M'] = 0;
		$this->arResult['DELAY_MIN_LIMIT_LABEL'] = '';

		$delayMinLimit = CBPSchedulerService::getDelayMinLimit();
		if ($delayMinLimit)
		{
			$this->arResult['DELAY_MIN_LIMIT_M'] = intdiv($delayMinLimit,60);
			$this->arResult['DELAY_MIN_LIMIT_LABEL'] = Loc::getMessage('BIZPROC_AUTOMATION_DELAY_MIN_LIMIT', [
				'#VAL#' => \CBPHelper::FormatTimePeriod($delayMinLimit)
			]);
		}
	}
}