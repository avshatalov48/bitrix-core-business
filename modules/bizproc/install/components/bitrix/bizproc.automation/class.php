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
		return isset($this->arParams['DOCUMENT_CATEGORY_ID']) ? (int)$this->arParams['DOCUMENT_CATEGORY_ID'] : 0;
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

	public static function getRobotViewData($robot, array $documentType)
	{
		$availableRobots = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);
		$result = array(
			'responsibleLabel' => '',
			'responsibleUrl' => '',
			'responsibleId' => 0,
		);

		$type = strtolower($robot['Type']);
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

				if ($users && count($users) == 1 && $users[0] && strpos($users[0], 'user_') === 0)
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
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$documentService = $runtime->GetService('DocumentService');

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

		//for HTML editor
		Main\Loader::includeModule('fileman');

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

		$target->setDocumentId($documentId);

		if (!$canEdit)
		{
			if ($documentId)
			{
				$canRead = CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::ReadDocument,
					$GLOBALS["USER"]->GetID(),
					[$documentType[0], $documentType[1], $documentId]
				);
			}
			else
			{
				$canRead = CBPDocument::CanUserOperateDocumentType(
					CBPCanUserOperateOperation::ReadDocument,
					$GLOBALS["USER"]->GetID(),
					$documentType
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

			$dialog = $template->getRobotSettingsDialog($this->arParams['~ROBOT_DATA'], $this->arParams['~REQUEST']);

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

			if (strpos($this->arParams['~ROBOT_DATA']['Type'], 'rest_') === 0)
			{
				$this->arResult = array('dialog' => $dialog);
				$this->includeComponentTemplate('rest_robot_properties_dialog');
				return;
			}

			$dialog->setDialogFileName('robot_properties_dialog');
			echo $dialog;
			return;
		}

		$statusList = $target->getDocumentStatusList($documentCategoryId);

		$log = [];
		if ($documentId)
		{
			$tracker = new \Bitrix\Bizproc\Automation\Tracker($target);
			$log = $tracker->getLog(array_keys($statusList));
		}

		$availableRobots = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);

		$this->arResult = array(
			'CAN_EDIT' => $canEdit,
			'TITLE_VIEW' => $this->getTitleView(),
			'TITLE_EDIT' => $this->getTitleEdit(),

			'DOCUMENT_STATUS' => $target->getDocumentStatus(),
			'DOCUMENT_TYPE' => $documentType,
			'DOCUMENT_ID' => $documentId,
			'DOCUMENT_CATEGORY_ID' => $documentCategoryId,
			'DOCUMENT_SIGNED' => static::signDocument($documentType, $documentCategoryId, $documentId),

			'ENTITY_NAME' => $documentService->getEntityName($documentType[0], $documentType[1]),

			'STATUSES' => $statusList,

			'TEMPLATES' => $this->getTemplates(array_keys($statusList)),
			'TRIGGERS' => $target->getTriggers(array_keys($statusList)),
			'AVAILABLE_TRIGGERS' => $target->getAvailableTriggers(),
			'AVAILABLE_ROBOTS' => array_values($availableRobots),

			'DOCUMENT_FIELDS' => $this->getDocumentFields(),
			'LOG' => $log,

			'WORKFLOW_EDIT_URL' => $this->getWorkflowEditUrl(),
			'STATUSES_EDIT_URL' => $this->getStatusesEditUrl(),
			'B24_TARIF_ZONE' => SITE_ID,
			'USER_OPTIONS' => array(
				'defaults' => \CUserOptions::GetOption('bizproc.automation', 'defaults', array()),
				'save_state_checkboxes' => \CUserOptions::GetOption('bizproc.automation', 'save_state_checkboxes', array())
			),
			'FRAME_MODE' => $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER',
			'USE_DISK' => Main\Loader::includeModule('disk')
		);

		if (IsModuleInstalled('bitrix24') && CModule::IncludeModule('bitrix24'))
		{
			$this->arResult['B24_TARIF_ZONE'] = \CBitrix24::getLicensePrefix();
		}

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

	private function showError($message)
	{
		echo <<<HTML
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
				<span class="ui-alert-message">{$message}</span>
			</div>
HTML;
		return;
	}
}