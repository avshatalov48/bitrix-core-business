<?php
use Bitrix\Lists\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Lists\Internals\Controller;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('lists') || !\Bitrix\Main\Application::getInstance()->getContext()
		->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ListsElementEditAjaxController extends Controller
{
	/** @var  int */
	protected $iblockId;
	protected $elementId;
	protected $socnetGroupId = 0;
	protected $sectionId = 0;
	/** @var  string */
	protected $iblockTypeId;
	protected $listPerm;

	protected function listOfActions()
	{
		return array(
			'completeWorkflow' => array(
				'method' => array('POST'),
			),
			'isConstantsTuned' => array(
				'method' => array('POST'),
			),
			'fillConstants' => array(
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

	protected function processActionCompleteWorkflow()
	{
		$this->checkRequiredPostParams(
			array('workflowId', 'iblockTypeId', 'elementId', 'iblockId', 'sectionId', 'socnetGroupId', 'action')
		);

		$this->fillDataForCheckPermission();
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$workflowId = $this->request->getPost('workflowId');
		$this->elementId = $this->request->getPost('elementId');

		$listError = CLists::completeWorkflow(
			$workflowId,
			$this->iblockTypeId,
			$this->elementId,
			$this->iblockId,
			$this->request->getPost('action')
		);

		if(!empty($listError))
		{
			$this->errorCollection->add(array(new Error($listError)));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse(array('message' => Loc::getMessage('LISTS_MESSAGE_SUCCESS')));
	}

	protected function processActionIsConstantsTuned()
	{
		$this->checkRequiredPostParams(array('iblockId', 'iblockTypeId', 'socnetGroupId', 'sectionId'));
		if(!Loader::includeModule('bizproc') || !CBPRuntime::isFeatureEnabled())
		{
			$this->errorCollection->add(
				array(new Error(Loc::getMessage('LISTS_CONNECTION_MODULE_BIZPROC')))
			);
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->fillDataForCheckPermission();

		$templateData = $this->getTemplatesIdList();
		if(empty($templateData))
		{
			$this->errorCollection->add(
				array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW')))
			);
		}

		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$admin = true;
		if($this->listPerm < CListPermissions::IS_ADMIN &&
			!CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, 'iblock_edit'))
			$admin = false;

		$isConstantsTuned = true;
		foreach($templateData as $templateId => $template)
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

	protected function getTemplatesIdList()
	{
		if(!Loader::includeModule('bizproc') || !CBPRuntime::isFeatureEnabled() || empty($this->iblockTypeId) || empty($this->iblockId))
		{
			return array();
		}

		$documentType = BizprocDocument::generateDocumentComplexType($this->iblockTypeId, $this->iblockId);
		$templates = array_merge(
			\CBPWorkflowTemplateLoader::SearchTemplatesByDocumentType($documentType, CBPDocumentEventType::Create),
			\CBPWorkflowTemplateLoader::SearchTemplatesByDocumentType($documentType, CBPDocumentEventType::Edit)
		);
		$templateData = array();
		foreach($templates as $template)
		{
			$templateData[$template['ID']]['ID'] = $template['ID'];
			$templateData[$template['ID']]['NAME'] = $template['NAME'];
		}
		return $templateData;
	}

	protected function processActionFillConstants()
	{
		$this->checkRequiredPostParams(array('iblockId', 'listTemplateId'));
		if(!Loader::includeModule('bizproc') || !CBPRuntime::isFeatureEnabled())
		{
			$this->errorCollection->add(
				array(new Error(Loc::getMessage('LISTS_CONNECTION_MODULE_BIZPROC')))
			);
		}
		if($this->errorCollection->hasErrors())
		{
			$errorObject = array_shift($this->errorCollection->toArray());
			ShowError($errorObject->getMessage());
			return;
		}
		$this->iblockId = intval($this->request->getPost('iblockId'));
		if(!CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, 'iblock_edit'))
		{
			ShowError(Loc::getMessage('LISTS_ACCESS_DENIED'));
			return;
		}

		$listTemplateId = $this->request->getPost('listTemplateId');

		$html = '';
		if(!empty($listTemplateId) && is_array($listTemplateId))
		{
			foreach($listTemplateId as $templateData)
			{
				$html .= '<span class="bx-lists-popup-header">'.htmlspecialcharsbx($templateData['NAME']).'</span>';
				ob_start();
				$this->getApplication()->includeComponent(
					'bitrix:bizproc.workflow.setconstants',
					'',
					Array(
						'ID' => $templateData['ID'],
						'POPUP' => 'Y'
					)
				);
				$html .= ob_get_contents();
				ob_end_clean();
				$html .= '<hr class="bx-lists-popup-separator">';
			}
		}

		if(empty($html))
		{
			$this->errorCollection->add(
				array(new Error(Loc::getMessage('LISTS_NOT_BIZPROC_TEMPLATE_NEW')))
			);
		}
		if($this->errorCollection->hasErrors())
		{
			$errorObject = array_shift($this->errorCollection->toArray());
			ShowError($errorObject->getMessage());
			return;
		}
		echo $html;
	}

	protected function processActionGetListAdmin()
	{
		$this->checkRequiredPostParams(array('iblockId', 'iblockTypeId', 'socnetGroupId', 'sectionId'));
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->fillDataForCheckPermission();
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
		foreach($rights as $right)
		{
			$res = strpos($right['GROUP_CODE'], 'U');
			if($right['TASK_ID'] == $idRight && $res === 0)
			{
				$userId = substr($right['GROUP_CODE'], 1);
				$users = CUser::GetList($by="id", $order="asc",
					array('ID' => $userId),
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
				$listUser[$userId]['img'] = $file['src'];
				$listUser[$userId]['name'] = CUser::FormatName($nameTemplate, $user, false);
			}
		}
		$users = CUser::getList(($b = 'ID'), ($o = 'ASC'),
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
			$listUser[$user['ID']]['img'] = $file['src'];
			$listUser[$user['ID']]['name'] = CUser::FormatName($nameTemplate, $user, false);
		}

		$listUser= array_values($listUser);
		$this->sendJsonSuccessResponse(array(
			'listAdmin' => $listUser
		));
	}

	protected function processActionNotifyAdmin()
	{
		$this->checkRequiredPostParams(
			array('userId', 'iblockId', 'iblockTypeId', 'socnetGroupId', 'sectionId', 'elementUrl')
		);
		if(!Loader::includeModule('im'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_CONNECTION_MODULE_IM'))));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->fillDataForCheckPermission();
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$userIdFrom = intval($this->getUser()->getID());
		$userIdTo = intval($this->request->getPost('userId'));

		$messageFields = array(
			'TO_USER_ID' => $userIdTo,
			'FROM_USER_ID' => $userIdFrom,
			'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => 'lists',
			'NOTIFY_TAG' => 'LISTS|NOTIFY_ADMIN|'.$userIdTo.'|'.$userIdFrom,
			'NOTIFY_MESSAGE' => Loc::getMessage(
				'LISTS_NOTIFY_MESSAGE', array('#URL#' => $this->request->getPost('elementUrl')))
		);
		$messageId = CIMNotify::Add($messageFields);

		if($messageId)
		{
			$this->sendJsonSuccessResponse(
				array('message' => Loc::getMessage('LISTS_NOTIFY_SUCCESS'))
			);
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_NOTIFY_ERROR'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function fillDataForCheckPermission()
	{
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');
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
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_FOR_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif(
			$this->listPerm < CListPermissions::CAN_READ && !(
				CIBlockRights::UserHasRightTo($this->iblockId, $this->iblockId, "element_read") ||
				CIBlockSectionRights::UserHasRightTo($this->iblockId, $this->sectionId, "section_element_bind")
			)
		)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_ACCESS_DENIED'))));
		}
	}
}

$controller = new ListsElementEditAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();