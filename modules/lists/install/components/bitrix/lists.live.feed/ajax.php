<?php

use Bitrix\Main;
use Bitrix\Lists\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Lists\Internals\Controller;
use Bitrix\Main\Text\HtmlFilter;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('lists') || !\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class LiveFeedAjaxController extends Controller
{
	/** @var  array */
	protected $lists = array();
	protected $formOprions = array();
	/** @var  string */
	protected $iblockTypeId = 'bitrix_processes';
	protected $listPerm;
	protected $formId;
	protected $randomString;
	protected $iblockDescription;
	protected $iblockCode;
	/** @var  int */
	protected $socnetGroupId = 0;
	protected $iblockId;

	protected function listOfActions()
	{
		return array(
			'getList' => array(
				'method' => array('POST'),
			),
			'setDelegateResponsible' => array(
				'method' => array('POST'),
			),
			'setResponsible' => array(
				'method' => array('POST'),
			),
			'getBizprocTemplateId' => array(
				'method' => array('POST'),
			),
			'createSettingsDropdown' => array(
				'method' => array('POST'),
			),
			'checkPermissions' => array(
				'method' => array('POST'),
			),
			'isConstantsTuned' => array(
				'method' => array('POST'),
			),
			'checkDelegateResponsible' => array(
				'method' => array('POST'),
			),
			'checkDataElementCreation' => array(
				'method' => array('POST'),
			),
			'getListAdmin' => array(
				'method' => array('POST'),
			),
			'notifyAdmin' => array(
				'method' => array('POST'),
			),
		);
	}

	private function isFeatureEnabled(): bool
	{
		return Loader::includeModule('bizproc') && CLists::isFeatureEnabled();
	}

	protected function processActionGetList()
	{
		if (!$this->isFeatureEnabled())
		{
			ShowError(Loc::getMessage('LISTS_SEAC_ACCESS_DENIED'));
			return;
		}

		$this->checkRequiredPostParams(array('iblockId', 'randomString'));

		if($this->errorCollection->hasErrors())
		{
			$errors = $this->errorCollection->toArray();
			$errorObject = array_shift($errors);
			ShowError($errorObject->getMessage());
			return;
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockDescription = (string)$this->request->getPost('iblockDescription');
		$this->iblockCode = $this->request->getPost('iblockCode');
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));

		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$errors = $this->errorCollection->toArray();
			$errorObject = array_shift($errors);
			ShowError($errorObject->getMessage());
			return;
		}

		$this->formId = 'lists_element_add_'.$this->iblockId;
		$this->randomString = htmlspecialcharsbx($this->request->getPost('randomString'));

		$this->getListData();
		$this->createPreparedFields();

		$this->getBizprocData();

		$this->createHtml();
	}

	protected function processActionSetDelegateResponsible()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if(!Loader::includeModule('iblock'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IBLOCK'))));
		}
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$selectUsers = $this->request->getPost('selectUsers');

		$rightObject = new CIBlockRights($this->iblockId);
		$rights = $rightObject->getRights();
		$rightsList = $rightObject->getRightsList(false);
		$idRight = array_search('iblock_full', $rightsList);
		foreach($rights as $keyRight => $right)
		{
			$res = mb_strpos($right['GROUP_CODE'], 'U');
			if($res === 0)
			{
				$arraySearch = array_search($right['GROUP_CODE'], $selectUsers);
				if($right['TASK_ID'] == $idRight)
				{
					if(!empty($selectUsers))
					{
						if($arraySearch || $arraySearch == 0)
							unset($rights[$keyRight]);
					}
					else
						unset($rights[$keyRight]);
				}
				else
				{
					if(!empty($selectUsers))
					{
						if($arraySearch || $arraySearch == 0)
							unset($rights[$keyRight]);
					}
				}
			}
		}
		if(!empty($selectUsers))
		{
			foreach($selectUsers as $keySelect => $idUser)
			{
				$rights['n'.$keySelect] = array('GROUP_CODE' => $idUser, 'TASK_ID' => $idRight);
			}
		}
		$rightObject->setRights($rights);

		$this->sendJsonSuccessResponse(array(
			'message' => Loc::getMessage('LISTS_SEAC_MESSAGE_DELEGATE_RESPONSIBLE')
		));
	}

	/**
	 * Displays a form to fill constants
	 * return html
	 */
	protected function processActionSetResponsible()
	{
		$this->checkRequiredPostParams(array('iblockId', 'randomString'));
		if (!$this->isFeatureEnabled())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$errors = $this->errorCollection->toArray();
			$errorObject = array_shift($errors);
			ShowError($errorObject->getMessage());
			return;
		}
		$this->iblockId = intval($this->request->getPost('iblockId'));

		if (!CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, 'iblock_rights_edit'))
		{
			ShowError(Loc::getMessage('LISTS_SEAC_ACCESS_DENIED'));

			return;
		}

		$html = '';
		$templateId = intval($this->request->getPost('templateId'));
		if(!empty($templateId))
		{
			$documentType = BizprocDocument::generateDocumentComplexType(
				COption::GetOptionString("lists", "livefeed_iblock_type_id"),
				$this->iblockId
			);
			$templateLoader = CBPWorkflowTemplateLoader::GetLoader();
			$templateQuery = $templateLoader->getTemplatesList(
				array('ID' => 'DESC'),
				array('DOCUMENT_TYPE' => $documentType, 'ID' => $templateId),
				false,
				false,
				array('ID', 'NAME')
			);
			if($template = $templateQuery->fetch())
			{
				$html .= $this->createHtmlSetConstants($template['ID'], $template['NAME']);
			}
		}
		else
		{
			$this->randomString = htmlspecialcharsbx($this->request->getPost('randomString'));
			$templateData = $this->getTemplatesIdList($this->iblockId);
			if(empty($templateData))
			{
				$html = '';
			}
			elseif(count($templateData) > 1)
			{
				$html .= '<p>'.Loc::getMessage("LISTS_SET_RESPONSIBLE_POPUP_DESCRIPTION").'</p>';
				foreach($templateData as $templateId => $templateName)
				{
					$url = 'javascript:BX.Lists[\'LiveFeedClass_'.$this->randomString.'\'].setResponsible(\''.$templateId.'\');';
					$html .= '<a href="'.$url.'"><div class="bx-lists-designer-item">'.htmlspecialcharsbx($templateName).'</div></a>';
				}
			}
			else
			{
				foreach($templateData as $templateId => $templateName)
				{
					$html .= $this->createHtmlSetConstants($templateId, $templateName);
				}
			}
		}

		if(empty($html))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$errors = $this->errorCollection->toArray();
			$errorObject = array_shift($errors);
			ShowError($errorObject->getMessage());
			return;
		}
		echo $html;

		$this->end();
	}

	protected function createHtmlSetConstants($templateId, $templateName)
	{
		$html = '';

		$html .= '<span class="bx-lists-template-name">'.htmlspecialcharsbx($templateName).'</span>';
		ob_start();
		$this->getApplication()->includeComponent(
			'bitrix:bizproc.workflow.setconstants',
			'',
			Array('ID' => $templateId, 'POPUP' => 'Y')
		);
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= '<hr class="bx-lists-constants-form-hr">';

		return $html;
	}

	protected function processActionIsConstantsTuned()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if (!$this->isFeatureEnabled())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$templateData = $this->getTemplatesIdList($this->iblockId);

		if(empty($templateData))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$admin = CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, 'iblock_rights_edit');

		$isConstantsTuned = true;
		foreach($templateData as $templateId => $templateName)
		{
			if(!CBPWorkflowTemplateLoader::isConstantsTuned($templateId))
				$isConstantsTuned = false;
		}
		if($isConstantsTuned)
		{
			$this->sendJsonSuccessResponse(array(
				'templateData' => $templateData,
			));
		}
		else
		{
			$this->sendJsonSuccessResponse(array(
				'admin' => $admin,
				'templateData' => $templateData,
			));
		}
	}

	/**
	 * @param $iblockId
	 * @return array
	 */
	protected function getTemplatesIdList($iblockId)
	{
		if (!$this->isFeatureEnabled())
		{
			return array();
		}

		$documentType = BizprocDocument::generateDocumentComplexType(
			COption::GetOptionString("lists", "livefeed_iblock_type_id"),
			$iblockId
		);
		$templateLoader = CBPWorkflowTemplateLoader::GetLoader();
		$templateQuery = $templateLoader->getTemplatesList(
			array('ID' => 'DESC'),
			array('DOCUMENT_TYPE' => $documentType, 'AUTO_EXECUTE' => CBPDocumentEventType::Create),
			false,
			false,
			array('ID', 'NAME')
		);
		$templateData = array();
		while($template = $templateQuery->fetch())
		{
			$templateData[$template['ID']] = $template['NAME'];
		}
		return $templateData;
	}

	protected function processActionGetListAdmin()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");

		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$rightObject = new CIBlockRights($this->iblockId);
		$rights = $rightObject->getRights();
		$rightsList = $rightObject->getRightsList(false);
		$idRight = array_search('iblock_full', $rightsList);
		$listUser = array();
		$nameTemplate = CSite::GetNameFormat(false);
		foreach($rights as $right)
		{
			$res = mb_strpos($right['GROUP_CODE'], 'U');
			if($right['TASK_ID'] == $idRight && $res === 0)
			{
				$userId = mb_substr($right['GROUP_CODE'], 1);
				$users = CUser::GetList("id", "asc",
					array('ID' => $userId, 'ACTIVE' => 'Y'),
					array('FIELDS' => array('ID', 'PERSONAL_PHOTO', 'NAME', 'LAST_NAME'))
				);
				$user = $users->fetch();
				$file['src'] = '';
				if ($user)
				{
					$file = \CFile::ResizeImageGet(
						$user['PERSONAL_PHOTO'],
						array('width' => 58, 'height' => 58),
						\BX_RESIZE_IMAGE_EXACT,
						false
					);
				}
				$listUser[$userId]['id'] = $userId;
				$listUser[$userId]['img'] = $file['src'] ?? null;
				$listUser[$userId]['name'] = CUser::FormatName($nameTemplate, $user, false);
			}
		}
		$users = CUser::getList('ID', 'ASC',
			array('GROUPS_ID' => 1, 'ACTIVE' => 'Y'),
			array('FIELDS' => array('ID', 'PERSONAL_PHOTO', 'NAME', 'LAST_NAME'))
		);
		while ($user = $users->fetch())
		{
			$file = \CFile::ResizeImageGet(
				$user['PERSONAL_PHOTO'],
				array('width' => 58, 'height' => 58),
				\BX_RESIZE_IMAGE_EXACT,
				false
			);
			$listUser[$user['ID']]['id'] = $user['ID'];
			$listUser[$user['ID']]['img'] = $file['src'] ?? null;
			$listUser[$user['ID']]['name'] = CUser::FormatName($nameTemplate, $user, false);
		}

		$listUser= array_values($listUser);
		$this->sendJsonSuccessResponse(array(
			'listAdmin' => $listUser
		));
	}

	protected function processActionNotifyAdmin()
	{
		$this->checkRequiredPostParams(array('userId'));
		if(!Loader::includeModule('im'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IM'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$siteId = SITE_ID;
		if($this->request->getPost('siteId'))
			$siteId = $this->request->getPost('siteId');
		$siteDir = SITE_DIR;
		if($this->request->getPost('siteId'))
			$siteDir = $this->request->getPost('siteDir');

		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$userIdFrom = intval($this->getUser()->getID());
		$userIdTo = intval($this->request->getPost('userId'));
		$iblockName = $this->request->getPost('iblockName');

		$urlForAdmin = COption::GetOptionString('socialnetwork', 'user_page', '/company/personal/', $siteId);
		$urlForAdmin = $urlForAdmin.'log/?bp_setting='.$this->iblockId;

		$messageFields = array(
			'TO_USER_ID' => $userIdTo,
			'FROM_USER_ID' => $userIdFrom,
			'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => 'lists',
			'NOTIFY_TAG' => 'LISTS|NOTIFY_ADMIN|'.$userIdTo.'|'.$userIdFrom,
			'NOTIFY_MESSAGE' => Loc::getMessage('LISTS_SEAC_NOTIFY_MESSAGE', array('#NAME_PROCESSES#' => $iblockName, '#URL#' => $urlForAdmin))
		);
		$messageId = CIMNotify::Add($messageFields);

		if($messageId)
		{
			$this->sendJsonSuccessResponse(
				array('message' => Loc::getMessage('LISTS_SEAC_NOTIFY_SUCCESS'))
			);
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_NOTIFY_ERROR'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function processActionGetBizprocTemplateId()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if (!$this->isFeatureEnabled())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}
		$documentType = BizprocDocument::generateDocumentComplexType(COption::GetOptionString("lists", "livefeed_iblock_type_id"), $this->iblockId);
		$templateLoader = CBPWorkflowTemplateLoader::GetLoader();
		$templateQuery = $templateLoader->getTemplatesList(
			array('ID' => 'DESC'),
			array('DOCUMENT_TYPE' => $documentType, 'AUTO_EXECUTE' => CBPDocumentEventType::Create),
			false,
			false,
			array('ID', 'NAME')
		);
		$isAvailable = false;
		$templateData = array();
		while($template = $templateQuery->fetch())
		{
			$isAvailable = true;
			$templateData[$template['ID']]['ID'] = $template['ID'];
			$templateData[$template['ID']]['NAME'] = $template['NAME'];
		}
		if(!$isAvailable)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}
		$manyTemplate = false;
		if(count($templateData) > 1)
		{
			$manyTemplate = true;
		}

		$this->sendJsonSuccessResponse(array(
			'templateData' => $templateData,
			'manyTemplate' => $manyTemplate
		));
	}

	protected function processActionCheckPermissions()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if (!$this->isFeatureEnabled())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse(array());
	}

	protected function processActionCreateSettingsDropdown()
	{
		$this->checkRequiredPostParams(array('iblockId', 'randomString'));
		if (!$this->isFeatureEnabled())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->randomString = htmlspecialcharsbx($this->request->getPost('randomString'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$settingsDropdown = array();
		$settingsDropdown[] = array(
			'text' => Loc::getMessage('LISTS_SEAC_SELECT_RESPONSIBILITY_NEW'),
			'title' => Loc::getMessage('LISTS_SEAC_SELECT_RESPONSIBILITY_NEW'),
			'href' => "javascript:BX.Lists['LiveFeedClass_{$this->randomString}'].setResponsible();",
		);
		$settingsDropdown[] = array(
			'text' => Loc::getMessage('LISTS_SEAC_DELEGATE_RESPONSIBLE_NEW'),
			'title' => Loc::getMessage('LISTS_SEAC_DELEGATE_RESPONSIBLE_NEW'),
			'href' => "javascript:BX.Lists['LiveFeedClass_{$this->randomString}'].setDelegateResponsible();",
		);
		$settingsDropdown[] = array(
			'text' => Loc::getMessage('LISTS_SEAC_DESIGNER_BP_NEW'),
			'title' => Loc::getMessage('LISTS_SEAC_DESIGNER_BP_NEW'),
			'href' => "javascript:BX.Lists['LiveFeedClass_{$this->randomString}'].jumpProcessDesigner();",
		);
		$settingsDropdown[] = array(
			'text' => Loc::getMessage('LISTS_SEAC_SETTING_LIST_NEW'),
			'title' => Loc::getMessage('LISTS_SEAC_SETTING_LIST_NEW'),
			'href' => "javascript:BX.Lists['LiveFeedClass_{$this->randomString}'].jumpSettingProcess();",
		);

		$this->sendJsonSuccessResponse(array(
			'settingsDropdown' => $settingsDropdown,
		));
	}

	protected function processActionCheckDelegateResponsible()
	{
		$this->checkRequiredPostParams(array('iblockId'));
		if(!Loader::includeModule('iblock'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IBLOCK'))));
		}
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = COption::GetOptionString('lists', 'livefeed_iblock_type_id');
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$rightObject = new CIBlockRights($this->iblockId);
		$rights = $rightObject->getRights();
		$rightsList = $rightObject->getRightsList(false);
		$idRight = array_search('iblock_full', $rightsList);
		$listUser = array();
		$nameTemplate = CSite::GetNameFormat(false);
		$count = 0;
		foreach($rights as $right)
		{
			$res = mb_strpos($right['GROUP_CODE'], 'U');
			if($right['TASK_ID'] == $idRight && $res === 0)
			{
				$userId = mb_substr($right['GROUP_CODE'], 1);
				$userGroups = CUser::getUserGroup($userId);
				if(!in_array(1, $userGroups))
				{
					$userQuery = CUser::getList(
						'ID', 'ASC',
						array('ID' => $userId, 'ACTIVE' => 'Y'),
						array('FIELDS' => array('ID' ,'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'EMAIL')
						)
					);
					if ($user = $userQuery->fetch())
					{
						$listUser[$count]['id'] = $right['GROUP_CODE'];
						$listUser[$count]['name'] = CUser::formatName($nameTemplate, $user, false);
					}
				}
			}
			$count++;
		}

		$this->sendJsonSuccessResponse(array(
			'listUser' => $listUser
		));
	}

	protected function processActionCheckDataElementCreation()
	{
		if(($_POST["save"] ?? '') != "Y" && ($_POST["changePostFormTab"] ?? '') != "lists" && !check_bitrix_sessid())
		{
			$this->errorCollection->add([new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IBLOCK'))]);
		}

		if (!$this->isFeatureEnabled())
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_BIZPROC'))));

		if(!Loader::includeModule('iblock'))
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IBLOCK'))));

		$this->iblockId = intval($this->request->getPost('IBLOCK_ID'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermissionElement();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$documentType = BizprocDocument::generateDocumentComplexType(COption::GetOptionString("lists", "livefeed_iblock_type_id"), $this->iblockId);

		$templateIdString = $_POST['TEMPLATE_ID'];
		$templateData = explode(',', $templateIdString);

		if(!empty($templateData))
		{
			if ($this->isFeatureEnabled())
			{
				$isConstantsTuned = true;
				foreach($templateData as $templateId)
				{
					if(!empty($templateId))
					{
						if(!CBPWorkflowTemplateLoader::isConstantsTuned($templateId))
							$isConstantsTuned = false;
					}
				}

				if(!$isConstantsTuned)
				{
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_CONSTANTS_TUNED_NEW'))));
					$this->sendJsonErrorResponse();
				}
			}
		}
		else
		{
			if ($this->isFeatureEnabled())
			{
				$templateData = $this->getTemplatesIdList($this->iblockId);

				if(!empty($templateData))
				{
					$isConstantsTuned = true;
					foreach($templateData as $templateId => $templateName)
					{
						if(!CBPWorkflowTemplateLoader::isConstantsTuned($templateId))
							$isConstantsTuned = false;
					}
					if(!$isConstantsTuned)
					{
						$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_CONSTANTS_TUNED_NEW'))));
						$this->sendJsonErrorResponse();
					}
				}
				else
				{
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW'))));
					$this->sendJsonErrorResponse();
				}
			}
		}

		$list = new CList($this->iblockId);
		$fields = $list->getFields();
		$elementData = array(
			"IBLOCK_ID" => $this->iblockId,
			"NAME" => $_POST["NAME"],
		);
		$props = array();
		foreach($fields as $fieldId => $field)
		{
			if($fieldId == "PREVIEW_PICTURE" || $fieldId == "DETAIL_PICTURE")
			{
				$elementData[$fieldId] = $_FILES[$fieldId];
				if(isset($_POST[$fieldId."_del"]) && $_POST[$fieldId."_del"]=="Y")
					$elementData[$fieldId]["del"] = "Y";
			}
			elseif($fieldId == "PREVIEW_TEXT" || $fieldId == "DETAIL_TEXT")
			{
				if(
					isset($field["SETTINGS"])
					&& is_array($field["SETTINGS"])
					&& $field["SETTINGS"]["USE_EDITOR"] == "Y"
				)
					$elementData[$fieldId."_TYPE"] = "html";
				else
					$elementData[$fieldId."_TYPE"] = "text";

				$elementData[$fieldId] = $_POST[$fieldId];
			}
			elseif($fieldId == 'ACTIVE_FROM' || $fieldId == 'ACTIVE_TO')
			{
				$elementData[$fieldId] = is_array($_POST[$fieldId])
					? array_shift($_POST[$fieldId])
					: $_POST[$fieldId]
				;
			}
			elseif($list->is_field($fieldId))
			{
				$elementData[$fieldId] = $_POST[$fieldId];
			}
			elseif($field["PROPERTY_TYPE"] == "F")
			{
				if(isset($_POST[$fieldId."_del"]))
					$deleteArray = $_POST[$fieldId."_del"];
				else
					$deleteArray = array();
				$props[$field["ID"]] = array();
				$files = $_FILES;
				if(isset($files[$fieldId]) && is_array($files[$fieldId]))
				{
					CFile::ConvertFilesToPost($files[$fieldId], $props[$field["ID"]]);
					foreach($props[$field["ID"]] as $fileId => $file)
					{
						if(
							isset($deleteArray[$fileId])
							&& (
								(!is_array($deleteArray[$fileId]) && $deleteArray[$fileId]=="Y")
								|| (is_array($deleteArray[$fileId]) && $deleteArray[$fileId]["VALUE"]=="Y")
							)
						)
						{
							if(isset($props[$field["ID"]][$fileId]["VALUE"]))
								$props[$field["ID"]][$fileId]["VALUE"]["del"] = "Y";
							else
								$props[$field["ID"]][$fileId]["del"] = "Y";
						}
					}
				}
			}
			elseif($field["PROPERTY_TYPE"] == "N")
			{
				if(is_array($_POST[$fieldId]) && !array_key_exists("VALUE", $_POST[$fieldId]))
				{
					$props[$field["ID"]] = array();
					foreach($_POST[$fieldId] as $key=>$value)
					{
						if(is_array($value))
						{
							if($value["VALUE"] <> '')
							{
								$value = str_replace(" ", "", str_replace(",", ".", $value["VALUE"]));
								if(!is_numeric($value))
								{
									$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_VALIDATE_FIELD_ERROR', array('#NAME#' => $field['NAME'])))));
									$this->sendJsonErrorResponse();
								}
								$props[$field["ID"]][$key] = doubleval($value);
							}

						}
						else
						{
							if($value <> '')
							{
								$value = str_replace(" ", "", str_replace(",", ".", $value));
								if(!is_numeric($value))
								{
									$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_VALIDATE_FIELD_ERROR', array('#NAME#' => $field['NAME'])))));
									$this->sendJsonErrorResponse();
								}
								$props[$field["ID"]][$key] = doubleval($value);
							}
						}
					}
				}
				else
				{
					if(is_array($_POST[$fieldId]))
					{
						if($_POST[$fieldId]["VALUE"] <> '')
						{
							$value = str_replace(" ", "", str_replace(",", ".", $_POST[$fieldId]["VALUE"]));
							if(!is_numeric($value))
							{
								$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_VALIDATE_FIELD_ERROR', array('#NAME#' => $field['NAME'])))));
								$this->sendJsonErrorResponse();
							}
							$props[$field["ID"]] = doubleval($value);
						}
					}
					else
					{
						if($_POST[$fieldId] <> '')
						{
							$value = str_replace(" ", "", str_replace(",", ".", $_POST[$fieldId]));
							if(!is_numeric($value))
							{
								$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_VALIDATE_FIELD_ERROR', array('#NAME#' => $field['NAME'])))));
								$this->sendJsonErrorResponse();
							}
							$props[$field["ID"]] = doubleval($value);
						}
					}
				}
			}
			else
			{
				$props[$field["ID"]] = $_POST[$fieldId] ?? '';
			}
		}
		$elementData["MODIFIED_BY"] = $this->getUser()->getID();
		unset($elementData["TIMESTAMP_X"]);
		if(!empty($props))
		{
			$elementData["PROPERTY_VALUES"] = $props;
		}

		$documentStates = CBPDocument::GetDocumentStates($documentType, null);
		$userId = $this->getUser()->getId();
		$write = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::WriteDocument,
			$userId,
			$documentType,
			array('AllUserGroups' => array(), 'DocumentStates' => $documentStates)
		);

		if(!$write)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_IS_ACCESS_DENIED_STATUS'))));
			$this->sendJsonErrorResponse();
		}

		$bizprocParametersValues = array();
		$stringError = '';
		foreach ($documentStates as $documentState)
		{
			if($documentState["ID"] == '')
			{
				$errors = array();
				$bizprocParametersValues[$documentState['TEMPLATE_ID']] = CBPDocument::StartWorkflowParametersValidate(
					$documentState['TEMPLATE_ID'],
					$documentState['TEMPLATE_PARAMETERS'],
					$documentType,
					$errors
				);
				foreach($errors as $e)
					$stringError .= $e['message'].'<br />';
			}
		}
		if(!empty($stringError))
		{
			$this->errorCollection->add(array(new Error($stringError)));
			$this->sendJsonErrorResponse();
		}

		$objectElement = new CIBlockElement;
		$idElement = $objectElement->Add($elementData, false, true, true);

		if($idElement)
		{
			foreach($documentStates as $documentState)
			{
				if($documentState["ID"] == '')
				{
					$startDuration = $_POST['timeToStart'] ?? null;
					if (is_numeric($startDuration))
					{
						$startDuration = (int)$startDuration;
					}
					else
					{
						$startDuration = null;
					}

					$currentUserId = Main\Engine\CurrentUser::get()->getId();
					$startWorkflowRequest = new \Bitrix\Bizproc\Api\Request\WorkflowService\StartWorkflowRequest(
						userId: $this->getUser()->getId(),
						targetUserId: $this->getUser()->getId(),
						templateId: $documentState['TEMPLATE_ID'],
						complexDocumentId: ['lists', 'BizprocDocument', $idElement],
						parameters: array_merge(
							$bizprocParametersValues[$documentState['TEMPLATE_ID']],
							[
								CBPDocument::PARAM_TAGRET_USER => 'user_' . $currentUserId,
							],
						),
						startDuration: $startDuration,
					);
					$workflowService = new \Bitrix\Bizproc\Api\Service\WorkflowService(
						accessService: new \Bitrix\Lists\Api\Service\WorkflowAccessService(),
					);
					$workflowService->startWorkflow($startWorkflowRequest);
				}
			}

			/*if(!empty($errorsTmp))
			{
				$documentStates = null;
				CBPDocument::AddDocumentToHistory(
					array('lists','BizprocDocument',$idElement),
					$elementData['NAME'],
					$this->getUser()->getID()
				);
			}*/
		}
		else
		{
			$this->errorCollection->add(array(new Error($objectElement->LAST_ERROR)));
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse(array());
	}

	protected function checkPermission()
	{
		$this->listPerm = CListPermissions::checkAccess(
			$this->getUser(),
			$this->iblockTypeId,
			$this->iblockId,
			$this->socnetGroupId
		);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_LISTS_FOR_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif($this->listPerm < CListPermissions::IS_ADMIN && !CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, 'iblock_edit'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_ACCESS_DENIED'))));
		}
	}

	protected function checkPermissionElement()
	{
		$this->listPerm = CListPermissions::checkAccess(
			$this->getUser(),
			$this->iblockTypeId,
			$this->iblockId,
			$this->socnetGroupId
		);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_LISTS_FOR_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif(($this->listPerm < CListPermissions::CAN_READ &&
			!CIBlockSectionRights::UserHasRightTo($this->iblockId, 0, 'section_element_bind')))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_ACCESS_DENIED'))));
		}
	}

	protected function getListData()
	{
		$list = new CList($this->iblockId);
		$this->lists['FIELDS'] = $list->getFields();

		$this->lists['SELECT'] = array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID', 'CREATED_BY', 'BP_PUBLISHED');
		$this->lists['DATA'] = array();
		$this->lists['DATA']['NAME'] = Loc::getMessage('LISTS_SEAC_FIELD_NAME_DEFAULT');
		$this->lists['DATA']['IBLOCK_SECTION_ID'] =  '';

		foreach($this->lists['FIELDS'] as $fieldId => $field)
		{
			$this->lists['FIELDS'][$fieldId]['NAME'] = $this->lists['FIELDS'][$fieldId]['NAME'];

			if($list->is_field($fieldId))
			{
				if($fieldId == 'ACTIVE_FROM' || $fieldId == 'PREVIEW_PICTURE' || $fieldId == 'DETAIL_PICTURE')
				{
					if($field['DEFAULT_VALUE'] === '=now')
						$this->lists['DATA'][$fieldId] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), 'FULL');
					elseif($field['DEFAULT_VALUE'] === '=today')
						$this->lists['DATA'][$fieldId] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), 'SHORT');
					else
						$this->lists['DATA'][$fieldId] = '';
				}
				else
				{
					$this->lists['DATA'][$fieldId] = $field['DEFAULT_VALUE'];
				}
				$this->lists['SELECT'][] = $fieldId;
			}
			elseif(is_array($field['PROPERTY_USER_TYPE']) && array_key_exists('GetPublicEditHTML', $field['PROPERTY_USER_TYPE']))
			{
				$this->lists['DATA'][$fieldId] = array(
					'n0' => array(
						'VALUE' => $field['DEFAULT_VALUE'],
						'DESCRIPTION' => '',
					)
				);
			}
			elseif($field['PROPERTY_TYPE'] == 'L')
			{
				$this->lists['DATA'][$fieldId] = array();
				$propEnums = CIBlockProperty::getPropertyEnum($field['ID']);
				while($enum = $propEnums->fetch())
					if($enum['DEF'] == 'Y')
						$this->lists['DATA'][$fieldId][] =$enum['ID'];
			}
			elseif($field['PROPERTY_TYPE'] == 'F')
			{
				$this->lists['DATA'][$fieldId] = array(
					'n0' => array('VALUE' => $field['DEFAULT_VALUE'], 'DESCRIPTION' => ''),
				);
			}
			elseif($field['PROPERTY_TYPE'] == 'G' || $field['PROPERTY_TYPE'] == 'E')
			{
				$this->lists['DATA'][$fieldId] = array($field['DEFAULT_VALUE']);
			}
			else
			{
				$this->lists['DATA'][$fieldId] = array(
					'n0' => array('VALUE' => $field['DEFAULT_VALUE'], 'DESCRIPTION' => ''),
				);
				if($field['MULTIPLE'] == 'Y')
				{
					if(is_array($field['DEFAULT_VALUE']) || mb_strlen($field['DEFAULT_VALUE']))
						$this->lists['DATA'][$fieldId]['n1'] = array('VALUE' => '', 'DESCRIPTION' => '');
				}
			}

			if($fieldId == 'CREATED_BY')
				$this->lists['SELECT'][] = 'CREATED_USER_NAME';

			if($fieldId == 'MODIFIED_BY')
				$this->lists['SELECT'][] = 'USER_NAME';
		}
	}

	/**
	 * @return array
	 */
	protected function createFormData()
	{
		foreach($this->lists['DATA'] as $key => $value)
		{
			$this->lists['FORM_DATA'][$key] = $value;
			if(is_array($value))
			{
				foreach($value as $key1 => $value1)
				{
					if(is_array($value1))
					{
						foreach($value1 as $key2 => $value2)
							if(!is_array($value2))
								$value[$key1][$key2] = htmlspecialcharsbx($value2);
					}
					else
					{
						$value[$key1] = htmlspecialcharsbx($value1);
					}
				}
				$this->lists['FORM_DATA'][$key] = $value;
			}
			else
			{
				$this->lists['FORM_DATA'][$key] = htmlspecialcharsbx($value);
			}
		}
	}

	protected function getElementFields()
	{
		$elements = CIBlockElement::getList(
			array(),
			array(
				'IBLOCK_ID' => $this->iblockId,
				"=ID" => $this->lists['ELEMENT_ID'],
			),
			false,
			false,
			$this->lists['SELECT']
		);
		$element = $elements->getNextElement();

		$this->lists['ELEMENT_FIELDS'] = is_object($element) ? $element->getFields() : [];
	}

	protected function createPreparedFields()
	{
		$this->lists['PREPARED_FIELDS'] = array();
		$this->lists['ELEMENT_ID'] = 0;
		$this->createFormData();
		$this->getElementFields();

		$this->getApplication()->showAjaxHead();

		foreach($this->lists['FIELDS'] as $fieldId => $field)
		{
			if ($field['TYPE'] === 'S:employee')
			{
				//todo: its too slow in Newsfeed
				$field['SETTINGS']['USE_ENTITY_SELECTOR'] = 'Y';
			}

			$isReadOnly = ($field['SETTINGS']['ADD_READ_ONLY_FIELD'] ?? '') === 'Y';

			if ($isReadOnly)
			{
				$field['VALUE'] = $this->lists['FORM_DATA'][$fieldId] ?? null;
				$preparedField = \Bitrix\Lists\Field::prepareFieldDataForEditForm($field);

				$this->lists['PREPARED_FIELDS'][$fieldId] = $preparedField;

				continue;
			}

			if($fieldId == 'ACTIVE_FROM' || $fieldId == 'ACTIVE_TO')
			{
				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					'id' => $fieldId.'['.$this->iblockId.']',
					'name' => $field['NAME'],
					'required' => $field['IS_REQUIRED']=='Y'? true: false,
					'type' => 'date',
					'value' => $this->lists['FORM_DATA'][$fieldId]
				);
			}
			elseif($fieldId == 'PREVIEW_PICTURE' || $fieldId == 'DETAIL_PICTURE')
			{
				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					'id' => $fieldId,
					'name' => $field['NAME'],
					'required' => $field['IS_REQUIRED']=='Y'? true: false,
					'type' => 'file'
				);
			}
			elseif($fieldId == 'PREVIEW_TEXT' || $fieldId == 'DETAIL_TEXT')
			{
				if($field['SETTINGS']['USE_EDITOR'] == 'Y')
				{
					$params = array(
						'width' => '100%',
						'height' => '200px',
						'iblockId' => $this->iblockId
					);
					$match = array();
					if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['WIDTH'], $match) && ($match[1] > 0))
					{
						$params['width'] = $match[1].$match[2];
					}
					if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['HEIGHT'], $match) && ($match[1] > 0))
					{
						$params['height'] = $match[1].$match[2];
					}

					$html = $this->connectionHtmlEditor($fieldId, $fieldId, $params, $this->lists['FORM_DATA'][$fieldId]);

					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						'id'=>$fieldId,
						'name'=>$field['NAME'],
						'required'=>$field['IS_REQUIRED']=='Y'? true: false,
						'type' => 'custom',
						'value' => $html,
					);
				}
				else
				{
					$params = array(
						'style' => '',
					);
					if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['WIDTH'], $match) && ($match[1] > 0))
					{
						if($match[2] == '')
							$params['cols'] = $match[1];
						else
							$params['style'] .= 'width:'.$match[1].$match[2].';';
					}
					if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['HEIGHT'], $match) && ($match[1] > 0))
					{
						if($match[2] == "")
							$params['rows'] = $match[1];
						else
							$params['style'] .= 'height:'.$match[1].$match[2].';';
					}

					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						'id'=>$fieldId,
						'name'=>$field['NAME'],
						'required'=>$field['IS_REQUIRED']=='Y'? true: false,
						'type' => 'textarea',
						'params' => $params,
					);
				}
			}
			elseif($fieldId == "DATE_CREATE" || $fieldId == "TIMESTAMP_X")
			{
				if($this->lists['ELEMENT_FIELDS'][$fieldId])
					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type" => "custom",
						"value" => $this->lists['ELEMENT_FIELDS'][$fieldId],
					);
			}
			elseif($fieldId == "CREATED_BY")
			{
				if($this->lists['ELEMENT_FIELDS']["CREATED_BY"])
					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type" => "custom",
						"value" => "[".$this->lists['ELEMENT_FIELDS']["CREATED_BY"]."] ".$this->lists['ELEMENT_FIELDS']["CREATED_USER_NAME"],
					);
			}
			elseif($fieldId == "MODIFIED_BY")
			{
				if($this->lists['ELEMENT_FIELDS']["MODIFIED_BY"])
					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type" => "custom",
						"value" => "[".$this->lists['ELEMENT_FIELDS']["MODIFIED_BY"]."] ".$this->lists['ELEMENT_FIELDS']["USER_NAME"],
					);
			}
			elseif(
				is_array($field["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicEditHTMLMulty", $field["PROPERTY_USER_TYPE"])
				&& $field["MULTIPLE"] == "Y" && $field["PROPERTY_TYPE"] != "E"
			)
			{
				$html = call_user_func_array($field["PROPERTY_USER_TYPE"]["GetPublicEditHTMLMulty"],
					array(
						$field,
						$this->lists['FORM_DATA'][$fieldId],
						array(
							"VALUE"=>$fieldId,
							"DESCRIPTION"=>'',
							"FORM_NAME"=>$this->formId,
							"MODE"=>"FORM_FILL",
						),
					));

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>"custom",
					"value"=>$html,
				);
			}
			elseif(is_array($field["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicEditHTML", $field["PROPERTY_USER_TYPE"]))
			{
				$params = array(
					'width' => '100%',
					'height' => '200px',
					'iblockId' => ''
				);
				$listTypeNotMultiple = array('S:DiskFile', 'S:ECrm');
				if($field["MULTIPLE"] == "Y" && !in_array($field["TYPE"], $listTypeNotMultiple))
				{
					$checkHtml = false;
					$html = '<table id="tbl'.$fieldId.'">';
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
					{
						if($field["TYPE"] == "S:HTML")
						{
							$checkHtml = true;
							$fieldIdForHtml = 'id_'.$fieldId.'__'.$key.'_';
							$fieldNameForHtml = $fieldId."[".$key."][VALUE][TEXT]";
							$html .= '<tr><td><input type="hidden" name="'.$fieldId.'['.HtmlFilter::encode($key).
								'][VALUE][TYPE]" value="html">'
								.$this->connectionHtmlEditor($fieldIdForHtml, $fieldNameForHtml, $params,
									is_array($value['VALUE']) ? $value['VALUE']['TEXT']: '').'</td></tr>';
						}
						elseif($field['TYPE'] == 'S:DateTime')
						{
							$html .= '<tr><td>
								<input class="bx-lists-input-calendar" type="text" name="'.$fieldId.'['.
								HtmlFilter::encode($key).'][VALUE]" onclick="BX.calendar({node: this.parentNode, field: this, bTime: true, bHideTime: false});" value="'.$value['VALUE'].'">
								<span class="bx-lists-calendar-icon" onclick="BX.calendar({node:this, field:\''.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]\', form: \'\', bTime: true, bHideTime: false});"
									  onmouseover="BX.addClass(this, \'calendar-icon-hover\');" onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"></span>
							</td></tr>';
						}
						elseif($field['TYPE'] == 'S:Date')
						{
							$html .= '<tr><td>
								<input class="bx-lists-input-calendar" type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" onclick="BX.calendar({node: this.parentNode, field: this, bTime: false, bHideTime: false});" value="'.$value['VALUE'].'">
								<span class="bx-lists-calendar-icon" onclick="BX.calendar({node:this, field:\''.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]\', form: \'\', bTime: false, bHideTime: false});"
									  onmouseover="BX.addClass(this, \'calendar-icon-hover\');" onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"></span>
							</td></tr>';
						}
						elseif($field['TYPE'] == 'E:EList')
						{
							$html .= '<tr><td><span class="bx-bp-select">'.call_user_func_array($field["PROPERTY_USER_TYPE"]["GetPublicEditHTML"],
								array(
									$field,
									$value,
									array(
										"VALUE"=>$fieldId."[".$key."][VALUE]",
										"DESCRIPTION"=>'',
										"FORM_NAME"=>$this->formId,
										"MODE"=>"FORM_FILL",
										"COPY"=>false,
									),
								)).'</span></td></tr>';
						}
						else
						{
							$html .= '<tr><td>'.call_user_func_array($field["PROPERTY_USER_TYPE"]["GetPublicEditHTML"],
									array(
										$field,
										$value,
										array(
											"VALUE"=>$fieldId."[".$key."][VALUE]",
											"DESCRIPTION"=>'',
											"FORM_NAME"=>$this->formId,
											"MODE"=>"FORM_FILL",
											"COPY"=>false,
										),
									)).'</td></tr>';
						}
					}
					$html .= '</table>';
					if($checkHtml)
						$html .= '<span class="bx-lists-input-add-button"><input type="button" onclick="BX.Lists[\'LiveFeedClass_'.$this->randomString.'\'].createAdditionalHtmlEditor(\'tbl'.$fieldId.'\', \''.$fieldId.'\', \''.$this->formId.'\');" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'"></span>';
					else
					{
						$regExp = '/'.$fieldId.'\[(n)([0-9]*)\]|data-id=".+?"/g';
						$html .= '<span class="bx-lists-input-add-button"><input type="button" onclick="BX.Lists.addNewTableRow(\'tbl'.$fieldId.'\', 1, '.htmlspecialcharsbx($regExp).', 2)" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'"></span>';
					}

					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type"=>"custom",
						"value"=>$html,
					);
				}
				else
				{
					$html = '';
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
					{
						if($field["TYPE"] == "S:HTML")
						{
							$fieldNameForHtml = $fieldId."[".$key."][VALUE][TEXT]";
							$html = $this->connectionHtmlEditor($fieldId, $fieldNameForHtml, $params, is_array($value['VALUE']) ? $value['VALUE']['TEXT']: '');
							$html .= '<input type="hidden" name="'.$fieldId.'['.$key.'][VALUE][TYPE]" value="html">';
						}
						elseif($field['TYPE'] == 'S:DateTime')
						{
							$html = '
								<input class="bx-lists-input-calendar" type="text" name="'.$fieldId.'[n0][VALUE]" onclick="BX.calendar({node: this.parentNode, field: this, bTime: true, bHideTime: false});" value="'.$value['VALUE'].'">
								<span class="bx-lists-calendar-icon" onclick="BX.calendar({node:this, field:\''.$fieldId.'[n0][VALUE]\', form: \'\', bTime: true, bHideTime: false});"
									  onmouseover="BX.addClass(this, \'calendar-icon-hover\');" onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"></span>
							';
						}
						elseif($field['TYPE'] == 'S:Date')
						{
							$html = '
								<input class="bx-lists-input-calendar" type="text" name="'.$fieldId.'[n0][VALUE]" onclick="BX.calendar({node: this.parentNode, field: this, bTime: false, bHideTime: false});" value="'.$value['VALUE'].'">
								<span class="bx-lists-calendar-icon" onclick="BX.calendar({node:this, field:\''.$fieldId.'[n0][VALUE]\', form: \'\', bTime: false, bHideTime: false});"
									  onmouseover="BX.addClass(this, \'calendar-icon-hover\');" onmouseout="BX.removeClass(this, \'calendar-icon-hover\');" border="0"></span>
							';
						}
						elseif($field['TYPE'] == 'E:EList')
						{
							$html = '<span class="bx-bp-select">'.call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTML'],
								array(
									$field,
									$value,
									array(
										'VALUE' => $fieldId.'['.$key.'][VALUE]',
										'DESCRIPTION' => '',
										'FORM_NAME' => $this->formId,
										'MODE' => 'FORM_FILL',
										'COPY' => false,
									),
								)).'</span>';
						}
						else
						{
							if($field['TYPE'] == 'S:ECrm')
							{
								\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
								Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
								Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');
							}
							$html = call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTML'],
								array(
									$field,
									$value,
									array(
										'VALUE' => $fieldId.'['.$key.'][VALUE]',
										'DESCRIPTION' => '',
										'FORM_NAME' => $this->formId,
										'MODE' => 'FORM_FILL',
										'COPY' => false,
									),
								));

						}
						break;
					}

					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						'id' => $fieldId,
						'name' => $field['NAME'],
						'required' => $field['IS_REQUIRED']=='Y'? true: false,
						'type' => 'custom',
						'value' => $html,
					);
				}
			}
			elseif($field["PROPERTY_TYPE"] == "N")
			{
				$html = '';
				if($field["MULTIPLE"] == "Y")
				{
					$html = '<table id="tbl'.$fieldId.'">';
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						$html .= '<tr><td><input type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" value="'.$value["VALUE"].'"></td></tr>';
					$html .= '</table>';
					$html .= '<span class="bx-lists-input-add-button"><input type="button" onclick="javascript:BX.Lists.addNewTableRow(\'tbl'.$fieldId.'\', 1, /'.$fieldId.'\[(n)([0-9]*)\]/g, 2)" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'"></span>';
				}
				else
				{
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						$html = '<input type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" value="'.$value["VALUE"].'">';
				}

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>"custom",
					"value"=>$html,
				);
			}
			elseif($field["PROPERTY_TYPE"] == "S")
			{
				$html = '';
				if($field["MULTIPLE"] == "Y")
				{
					$html = '<table id="tbl'.$fieldId.'">';
					if ($field["ROW_COUNT"] > 1)
					{
						foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						{
							$html .= '<tr><td><textarea name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" rows="'.intval($field["ROW_COUNT"]).'" cols="'.intval($field["COL_COUNT"]).'">'.$value["VALUE"].'</textarea></td></tr>';
						}
					}
					else
					{
						foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						{
							$html .= '<tr><td><input type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" value="'.$value["VALUE"].'"></td></tr>';
						}
					}
					$html .= '</table>';
					$html .= '<span class="bx-lists-input-add-button"><input type="button" onclick="javascript:BX.Lists.addNewTableRow(\'tbl'.$fieldId.'\', 1, /'.$fieldId.'\[(n)([0-9]*)\]/g, 2)" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'"></span>';
				}
				else
				{
					if ($field["ROW_COUNT"] > 1)
					{
						foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						{
							$html = '<textarea name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" rows="'.intval($field["ROW_COUNT"]).'" cols="'.intval($field["COL_COUNT"]).'">'.$value["VALUE"].'</textarea>';
						}
					}
					else
					{
						foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
						{
							$html = '<input type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" value="'.$value["VALUE"].'" size="'.intval($field["COL_COUNT"]).'">';
						}
					}
				}

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>"custom",
					"value"=>$html,
				);
			}
			elseif($field["PROPERTY_TYPE"] == "L")
			{
				$items = array("" => Loc::getMessage("LISTS_SEAC_NO_VALUE"));
				$propEnums = CIBlockProperty::getPropertyEnum($field["ID"]);
				while($enum = $propEnums->fetch())
					$items[$enum["ID"]] = $enum["VALUE"];

				if($field["MULTIPLE"] == "Y")
				{
					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId.'[]',
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type"=>'list',
						"items"=>$items,
						"value"=>$this->lists['FORM_DATA'][$fieldId],
						"params" => array("size"=>5, "multiple"=>"multiple"),
					);
				}
				else
				{
					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type"=>'list',
						"items"=>$items,
						"value"=>$this->lists['FORM_DATA'][$fieldId],
					);
				}
			}
			elseif($field['PROPERTY_TYPE'] == 'F')
			{
				$html = '
					<script>
						var wrappers = document.getElementsByClassName("bx-lists-input-file");
						for (var i = 0; i < wrappers.length; i++)
						{
							var inputs = wrappers[i].getElementsByTagName("input");
							for (var j = 0; j < inputs.length; j++)
							{
								inputs[j].onchange = getName;
							}
						}
						function getName ()
						{
							var str = this.value, i;
							if (str.lastIndexOf("\\\"))
							{
								i = str.lastIndexOf("\\\")+1;
							}
							else
							{
								i = str.lastIndexOf("\\\")+1;
							}
							str = str.slice(i);
							var uploaded = this.parentNode.parentNode.getElementsByClassName("fileformlabel")[0];
							uploaded.innerHTML = str;
						}
					</script>
				';
				if($field['MULTIPLE'] == 'Y')
				{
					$html .= '<table id="tbl'.$fieldId.'">';
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
					{
						$html .= '<tr><td><span class="file-wrapper"><span class="bx-lists-input-file">
								<span class="webform-small-button bx-lists-small-button">'.Loc::getMessage('LISTS_SEAC_FILE_ADD') .'</span>';

						$html .= $this->connectionFile($fieldId, $key, $value, $field['PROPERTY_TYPE']);

						$html .= '</span><span class="fileformlabel bx-lists-input-file-name"></span></span></td></tr>';
					}
					$html .= '</table>';
					$html .= '
						<span class="bx-lists-input-add-button">
							<input type="button" onclick="javascript:BX.Lists[\'LiveFeedClass_'.$this->randomString.'\'].addNewFileTableRow(\'tbl'.$fieldId.'\', 1, /'.$fieldId.'\[(n)([0-9]*)\]/g, 2);
							BX.Lists[\'LiveFeedClass_'.$this->randomString.'\'].getNameInputFile();" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'">
						</span>';

					$this->lists['PREPARED_FIELDS'][$fieldId] = array(
						"id"=>$fieldId,
						"name"=>$field["NAME"],
						"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
						"type"=>"custom",
						"value"=>$html,
					);
				}
				else
				{
					foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
					{
						$html .= '<span class="file-wrapper"><span class="bx-lists-input-file">
								<span class="webform-small-button bx-lists-small-button">'.Loc::getMessage('LISTS_SEAC_FILE_ADD') .'</span>';
						$html .= $this->connectionFile($fieldId, $key, $value, $field['PROPERTY_TYPE']);
						$html .= '</span><span class="fileformlabel bx-lists-input-file-name"></span></span>';
						$this->lists['PREPARED_FIELDS'][$fieldId] = array(
							"id" => $fieldId.'['.$key.'][VALUE]',
							"name" => $field["NAME"],
							"required" => $field["IS_REQUIRED"]=="Y"? true: false,
							"type" => "file",
							"value" => $html,
						);
					}
				}
			}
			elseif($field["PROPERTY_TYPE"] == "G")
			{
				if($field["IS_REQUIRED"]=="Y")
					$items = array();
				else
					$items = array("" => Loc::getMessage("LISTS_SEAC_NO_VALUE"));

				$rsSections = CIBlockSection::GetTreeList(Array("IBLOCK_ID" => $field["LINK_IBLOCK_ID"]));
				while($res = $rsSections->GetNext())
					$items[$res["ID"]] = str_repeat(" . ", $res["DEPTH_LEVEL"]).$res["NAME"];

				if($field["MULTIPLE"] == "Y")
					$params = array("size"=>4, "multiple"=>"multiple");
				else
					$params = array();

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId.'[]',
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>'list',
					"items"=>$items,
					"value"=>$this->lists['FORM_DATA'][$fieldId],
					"params" => $params,
				);
			}
			elseif($field["PROPERTY_TYPE"] == "E")
			{
				if(!is_array($this->lists['FORM_DATA'][$fieldId]))
					$this->lists['FORM_DATA'][$fieldId] = array($this->lists['FORM_DATA'][$fieldId]);

				$currentElements = array();
				foreach($this->lists['FORM_DATA'][$fieldId] as $listElementId)
				{
					if($listElementId)
					{
						$currentElements[] = $listElementId;
					}
				}
				$randomGenerator = new Bitrix\Main\Type\RandomSequence($fieldId);
				$randString = mb_strtolower($randomGenerator->randString(6));
				$html = '';
				global $APPLICATION;
				ob_start();
				$APPLICATION->includeComponent('bitrix:iblock.element.selector', '',
					array(
						'SELECTOR_ID' => $randString,
						'INPUT_NAME' => $fieldId,
						'IBLOCK_ID' => $field["LINK_IBLOCK_ID"],
						'MULTIPLE' => $field["MULTIPLE"],
						'CURRENT_ELEMENTS_ID' => $currentElements,
						'POPUP' => 'Y',
						'PANEL_SELECTED_VALUES' => 'Y'
					),
					null, array('HIDE_ICONS' => 'Y')
				);
				$html .= ob_get_contents();
				ob_end_clean();

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>'custom',
					"value"=>$html,
				);
			}
			elseif($field["MULTIPLE"] == "Y")
			{
				$html = '<table id="tbl'.$fieldId.'"><tr><td>';
				foreach($this->lists['FORM_DATA'][$fieldId] as $key => $value)
					$html .= '<tr><td><input type="text" name="'.$fieldId.'['.HtmlFilter::encode($key).'][VALUE]" value="'.$value["VALUE"].'"></td></tr>';
				$html .= '</td></tr></table>';
				$html .= '
				<span class="bx-lists-input-add-button">
					<input type="button" onclick="javascript:BX.Lists.addNewTableRow(\'tbl'.$fieldId.'\', 1, /'.$fieldId.'\[(n)([0-9]*)\]/g, 2)" value="'.Loc::getMessage("LISTS_SEAC_ADD_BUTTON").'">
				</span>';

				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type"=>"custom",
					"value"=>$html,
				);
			}
			elseif(is_array($this->lists['FORM_DATA'][$fieldId]) && array_key_exists("VALUE", $this->lists['FORM_DATA'][$fieldId]))
			{
				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId.'[VALUE]',
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type" => "text",
					"value" => $this->lists['FORM_DATA'][$fieldId]["VALUE"],
				);
			}
			else
			{
				$this->lists['PREPARED_FIELDS'][$fieldId] = array(
					"id"=>$fieldId,
					"name"=>$field["NAME"],
					"required"=>$field["IS_REQUIRED"]=="Y"? true: false,
					"type" => "text",
				);
			}

			if(!($fieldId == 'DATE_CREATE' || $fieldId == 'TIMESTAMP_X' || $fieldId == 'CREATED_BY' || $fieldId == 'MODIFIED_BY'))
			{
				if(isset($field['SETTINGS']['SHOW_ADD_FORM']))
				{
					$this->lists['PREPARED_FIELDS'][$fieldId]['show'] = $field['SETTINGS']['SHOW_ADD_FORM'] == 'Y' ? 'Y' : 'N';
				}
				else
				{
					$this->lists['PREPARED_FIELDS'][$fieldId]['show'] = 'Y';
				}
			}
		}
	}

	protected function getBizprocData()
	{
		$userId = $this->getUser()->getID();
		$currentUserGroups = $this->getUser()->getUserGroupArray();
		if(!$this->lists['ELEMENT_FIELDS'] || $this->lists['ELEMENT_FIELDS']['CREATED_BY'] == $userId)
			$currentUserGroups[] = 'Author';

		$documentType = 'iblock_'.$this->iblockId;
		CBPDocument::addShowParameterInit('lists', 'only_users', $documentType);

		$this->lists['BIZPROC_FIELDS'] = array();
		$bizprocIndex = 0;
		$documentStates = CBPDocument::getDocumentStates(array('lists', 'BizprocDocument', $documentType), null);
		$runtime = CBPRuntime::getRuntime();
		$runtime->startRuntime();
		$documentService = $runtime->getService('DocumentService');

		foreach ($documentStates as $documentState)
		{
			$bizprocIndex++;
			$viewWorkflow = CBPDocument::CanUserOperateDocumentType(
				CBPCanUserOperateOperation::StartWorkflow,
				$userId,
				array('lists', 'BizprocDocument', $documentType),
				array('sectionId'=> 0, 'AllUserGroups' => $currentUserGroups, 'DocumentStates' => $documentStates, 'WorkflowId' => $documentState['ID'] > 0 ? $documentState['ID'] : $documentState['TEMPLATE_ID'])
			);

			if($viewWorkflow)
			{
				$templateId = intval($documentState['TEMPLATE_ID']);
				$templateName = $documentState['TEMPLATE_NAME'];
				$workflowParameters = $documentState['TEMPLATE_PARAMETERS'];
				if(!is_array($workflowParameters))
					$workflowParameters = array();
				if($documentState["ID"] == '' && $templateId > 0)
				{
					$parametersValues = array();
					$keys = array_keys($workflowParameters);
					foreach ($keys as $key)
					{
						$value = $workflowParameters[$key]["Default"];
						if (!is_array($value))
						{
							$parametersValues[$key] = $value;
						}
						else
						{
							$keys1 = array_keys($value);
							foreach ($keys1 as $key1)
							{
								$parametersValues[$key][$key1] = $value[$key1];
							}
						}
					}
					foreach ($workflowParameters as $parameterKey => $arParameter)
					{
						$parameterKeyExt = "bizproc".$templateId."_".$parameterKey;

						$html = $documentService->GetFieldInputControl(
							array('lists', 'BizprocDocument', $documentType),
							$arParameter,
							array("Form" => "start_workflow_form1", "Field" => $parameterKeyExt),
							$parametersValues[$parameterKey],
							false,
							true
						);

						$this->lists['BIZPROC_FIELDS'][$parameterKeyExt.$bizprocIndex] = array(
							"id" => $parameterKeyExt.$bizprocIndex,
							"required" => $arParameter["Required"],
							"name" => $arParameter["Name"],
							"title" => $arParameter["Description"],
							"type" => "custom",
							"realType" => $arParameter["Type"],
							"value" => $html,
							'show' => 'Y',
							'templateName' => $templateName,
							'templateId' => $templateId
						);
					}
				}
			}
		}
	}

	protected function connectionFile($fieldId, $key, $value, $type)
	{
		if($type == 'F')
			$fieldId = $fieldId.'['.$key.'][VALUE]';

		$obFile = new CListFile(
			$this->iblockId,
			$this->lists['ELEMENT_FIELDS']["IBLOCK_SECTION_ID"] ?? 0,
			$this->lists['ELEMENT_ID'],
			$fieldId,
			$value["VALUE"]
		);
		$obFile->SetSocnetGroup($this->socnetGroupId);
		$obFileControl = new CListFileControl($obFile, $fieldId);

		return $obFileControl->getHTML(array(
			'max_size' => 102400,
			'max_width' => 150,
			'max_height' => 150,
			'url_template' => '',
			'a_title' => Loc::getMessage("LISTS_SEAC_ENLARGE"),
			'download_text' => Loc::getMessage("LISTS_SEAC_DOWNLOAD"),
		));
	}

	protected function connectionHtmlEditor($fieldId, $fieldNameForHtml, $params, $content)
	{
		$html = '';
		if (Loader::includeModule('fileman'))
		{
			ob_start();
			$editor = new CHTMLEditor;
			$res = array(
				'name' => $fieldNameForHtml,
				'inputName' => $fieldNameForHtml,
				'id' => $fieldId.$params['iblockId'],
				'width' => $params['width'],
				'height' => $params['height'],
				'content' => $content,
				'useFileDialogs' => false,
				'minBodyWidth' => 350,
				'normalBodyWidth' => 555,
				'bAllowPhp' => false,
				'limitPhpAccess' => false,
				'showTaskbars' => false,
				'showNodeNavi' => false,
				'beforeUnloadHandlerAllowed' => true,
				'askBeforeUnloadPage' => false,
				'bbCode' => false,
				'siteId' => SITE_ID,
				'autoResize' => true,
				'autoResizeOffset' => 40,
				'saveOnBlur' => true,
				'actionUrl' => '/bitrix/tools/html_editor_action.php',
				'setFocusAfterShow' => false,
				'controlsMap' => array(
					array('id' => 'Bold', 'compact' => true, 'sort' => 80),
					array('id' => 'Italic', 'compact' => true, 'sort' => 90),
					array('id' => 'Underline', 'compact' => true, 'sort' => 100),
					array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
					array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
					array('id' => 'Color', 'compact' => true, 'sort' => 130),
					array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
					array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
					array('separator' => true, 'compact' => false, 'sort' => 145),
					array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
					array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
					array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
					array('separator' => true, 'compact' => false, 'sort' => 200),
					array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
					array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
					array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
					array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
					array('separator' => true, 'compact' => false, 'sort' => 290),
					array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
					array('id' => 'More', 'compact' => true, 'sort' => 400)
				),
			);
			$editor->show($res);
			$html = ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}

	protected function createHtml()
	{
		if(empty($this->lists['PREPARED_FIELDS']))
			return;

		switch ($this->iblockCode)
		{
			case 'bitrix_outgoing_doc':
				$blueDudeCode = 2216841;
				break;
			case 'bitrix_cash':
				$blueDudeCode = 2216531;
				break;
			case 'bitrix_trip':
				$blueDudeCode = 2216791;
				break;
			case 'bitrix_invoice':
				$blueDudeCode = 2215233;
				break;
			case 'bitrix_holiday':
				$blueDudeCode = 2216341;
				break;
			default:
				$blueDudeCode = 0;
				break;
		}

		$manyParameters = false;
		if(!empty($this->lists['BIZPROC_FIELDS']))
		{
			$title = Loc::getMessage('LISTS_IS_BIZPROC_PARAMETERS');
			if(count($this->lists['BIZPROC_FIELDS']) > 1)
			{
				$title = Loc::getMessage('LISTS_BIZPROC_PARAMETERS');
				$manyParameters = true;
			}
		}

		$data = array_merge($this->lists['PREPARED_FIELDS'], $this->lists['BIZPROC_FIELDS']);
		$listTemplateId = array();
		$bizprocTitle = true;

		?>

		<div class="bx-lists-iblock-description">
			<?= nl2br($this->convertBBcode($this->iblockDescription)) ?>
			<? if(!empty($blueDudeCode)): ?>
				<br><br>
				<a style="cursor:pointer;"
				   onclick='BX.Helper.show("redirect=detail&code=<?=$blueDudeCode ?>");'>
					<?= Loc::getMessage('LISTS_IS_DESRIPTION_DETAIL') ?>
				</a>
			<? endif ?>
		</div>
		<div class="bx-lists-block-errors" id="bx-lists-block-errors" style="display:none;"></div>

		<table class="bx-lists-table-content">
			<? foreach($data as $fieldId => $field): ?>

				<? if(preg_match('/^bizproc/', $fieldId)): ?>

					<? if($bizprocTitle): ?>
						<? $bizprocTitle = false ?>
						<tr><td colspan="2" style="visibility: hidden"></td></tr>
						<tr>
							<td colspan="2" class="bx-lists-bizproc-parameters-title"><?= $title ?></td>
						</tr>
					<? endif ?>

					<? if($manyParameters && empty($listTemplateId[$field['templateId']])): ?>
						<tr><td colspan="2" style="visibility: hidden"></td></tr>
						<tr class="bx-lists-bp-parameters-template">
							<td colspan="2"><?= $field["templateName"] ?></td>
						</tr>
						<? $listTemplateId[$field['templateId']] = true; ?>
					<? endif ?>
				<? endif ?>

				<?
					$customHtml = (isset($field['value'])? $field['value'] : $this->lists['FORM_DATA'][$field['id']]);
					$params = '';
					if(is_array($field['params'] ?? null) && $field['type'] <> 'file')
					{
						foreach($field['params'] as $p => $v)
							$params .= ' '.$p.'="'.$v.'"';
					}
					$required = $field['required'] ? '<span class="bx-lists-required">*</span>' : '';
					$style = $field['show'] == 'Y' ? '' : 'style="display:none;"';
					if($field['type'] == 'file'): ?>
						<script>
							var wrappers = document.getElementsByClassName('bx-lists-input-file');
							for (var i = 0; i < wrappers.length; i++)
							{
								var inputs = wrappers[i].getElementsByTagName('input');
								for (var j = 0; j < inputs.length; j++)
								{
									inputs[j].onchange = getName;
								}
							}
							function getName ()
							{
								var str = this.value, i;
								if (str.lastIndexOf('\\'))
								{
									i = str.lastIndexOf('\\')+1;
								}
								else
								{
									i = str.lastIndexOf('/')+1;
								}
								str = str.slice(i);
								var uploaded = this.parentNode.parentNode.getElementsByClassName('fileformlabel')[0];
								uploaded.innerHTML = str;
							}
						</script>
					<? endif;
				?>

				<tr <?= $style ?>>
					<td class="bx-lists-table-first-td">
						<?=htmlspecialcharsbx($field['name'])?>: <?= $required ?>
					</td>
					<?
					switch($field['type']):
						case 'label':
						case 'custom':
							?><td><?
							$spanOne = '';
							$spanTwo = '';
							if (!empty($field["realType"]))
							{
								switch ($field["realType"])
								{
									case "select":
									case "internalselect":
									case "E:EList":
									case "bool":
										$spanOne = '<span class="bx-bp-select">';
										$spanTwo = '</span>';
										break;
								}
							}
							echo $spanOne.$customHtml.$spanTwo;
							?></td><?
							break;
						case 'checkbox':
							?>
							<td>
								<input type="hidden" name="<?=$field['id']?>" value="N">
								<input type="checkbox" name="<?=$field['id']?>" value="Y"
									<?=($customHtml == "Y"? ' checked':'')?><?=HtmlFilter::encode($params)?>>
							</td>
							<?
							break;
						case 'textarea':
							?>
							<td>
								<textarea name="<?=$field['id']?>"<?=HtmlFilter::encode($params)?>><?=$customHtml?></textarea>
							</td>
							<?
							break;
						case 'list':
							$class = '';
							if(!empty($params))
							{
								$class = 'bx-bp-select-linking';
								$spanOne = '';
								$spanTwo = '';
							}
							else
							{
								$spanOne = '<span class="bx-bp-select">';
								$spanTwo = '</span>';
							}

							?><td>
							<?= $spanOne ?>
						<select name="<?=$field['id']?>"<?=HtmlFilter::encode($params)?> class="<?= $class ?>">
							<?
							if(is_array($field['items'])):
								if(!is_array($customHtml))
									$customHtml = array($customHtml);
								foreach($field['items'] as $k=>$v):?>
									<option value="<?=htmlspecialcharsbx($k)?>"
										<?=(in_array($k, $customHtml)? ' selected':'')?>>
										<?=htmlspecialcharsbx($v)?></option>
								<? endforeach; ?>
								</select>
								<?= $spanTwo ?>
								<?= $field['customHtml'] ?? '' ?>
								</td>
								<?
							endif;
							break;
						case 'file':
							?>
							<td>
								<span class="file-wrapper">
									<span class="bx-lists-input-file">
										<span class="webform-small-button bx-lists-small-button">
											<?= Loc::getMessage('LISTS_SEAC_FILE_ADD') ?></span>
										<input name="<?= $field['id'] ?>"
											size="<?= HtmlFilter::encode($field['params']['size'] ?? '') ?>" type="file">
									</span>
									<span class="fileformlabel bx-lists-input-file-name"></span>
								</span>
							</td>
							<?
							break;
						case 'date':
							?>
							<td>
								<input class="bx-lists-input-calendar" value="<?=$customHtml?>" type="text"
								       name="<?= $field['id'] ?>" onclick="BX.calendar({node: this.parentNode,
								       field: this, bTime: true, bHideTime: false});">
								<span class="bx-lists-calendar-icon" onclick="BX.calendar({node:this,
									field:'<?= $field['id'] ?>', form: '', bTime: true, bHideTime: false});"
								      onmouseover="BX.addClass(this, 'calendar-icon-hover');"
								      onmouseout="BX.removeClass(this, 'calendar-icon-hover');" border="0"></span>
							</td>
							<?
							break;
						default:
							?>
							<td>
								<input type="text" name="<?=$field['id']?>" value="<?=$customHtml?>"<?=HtmlFilter::encode($params)?>>
							</td>
							<?
							break;
					endswitch;
					?>
				</tr>

			<? endforeach ?>
		</table>

		<?
	}

	private function convertBBcode(string $text): string
	{
		$textParser = new \CTextParser();
		$textParser->allow = [
			'HTML' => 'N',
			'USER' => 'N',
			'ANCHOR' => 'Y',
			'BIU' => 'Y',
			'IMG' => 'N',
			'QUOTE' => 'N',
			'CODE' => 'N',
			'FONT' => 'Y',
			'LIST' => 'Y',
			'SMILES' => 'N',
			'NL2BR' => 'Y',
			'VIDEO' => 'N',
			'TABLE' => 'N',
			'CUT_ANCHOR' => 'N',
			'ALIGN' => 'N'
		];

		return $textParser->convertText($text);
	}
}
$controller = new LiveFeedAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();
