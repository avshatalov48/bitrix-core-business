<?php
use Bitrix\Lists\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Lists\Internals\Controller;
use Bitrix\Main\Config\Option;

define('STOP_STATISTICS', true);
define('NO_AGENT_STATISTIC','Y');
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::includeModule('lists') || !\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ListAjaxController extends Controller
{
	/** @var  int */
	protected $iblockId;
	protected $elementId;
	protected $socnetGroupId;
	protected $sectionId = 0;
	/** @var  string */
	protected $iblockTypeId;
	protected $listPerm;

	protected function listOfActions()
	{
		return array(
			'performActionBp' => array(
				'method' => array('POST'),
			),
			'addSection' => array(
				'method' => array('POST'),
			),
			'deleteSection' => array(
				'method' => array('POST'),
			),
			'editSection' => array(
				'method' => array('POST'),
			),
			'getSection' => array(
				'method' => array('POST'),
			),
			'toogleSectionGrid' => array(
				'method' => array('POST'),
			),
			'rebuildSeachableContent' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionAddSection()
	{
		$this->checkRequiredPostParams(array('iblockTypeId', 'iblockId', 'sectionId', 'sectionName', 'socnetGroupId'));
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');
		$sectionName = trim($this->request->getPost('sectionName'), " \n\r\t");

		$this->checkPermission();
		if($this->listPerm < CListPermissions::CAN_WRITE
			&& !CIBlockSectionRights::userHasRightTo($this->iblockId, $this->sectionId, 'section_section_bind'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_ACCESS_DENIED'))));
		}
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();


		$sectionObject = new CIBlockSection;
		$sectionId = $sectionObject->add(array(
			'IBLOCK_ID' => $this->iblockId,
			'NAME' => $sectionName,
			'IBLOCK_SECTION_ID' => $this->sectionId,
			'CHECK_PERMISSIONS' => 'N',
		));

		if($sectionId)
		{
			$this->sendJsonSuccessResponse(
				array('id' => intval($sectionId), 'message' => Loc::getMessage('LISTS_LAC_MESSAGE_SUCCESS')));
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_UNKNOWN_ERROR'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function processActionDeleteSection()
	{
		$this->checkRequiredPostParams(
			array('iblockTypeId', 'iblockId', 'sectionId', 'socnetGroupId', 'sectionIdForDelete'));
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');
		$sectionIdForDelete = intval($this->request->getPost('sectionIdForDelete'));

		$this->checkPermission();
		if($this->listPerm < CListPermissions::CAN_WRITE
			&& !CIBlockSectionRights::userHasRightTo($this->iblockId, $sectionIdForDelete, 'section_delete'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_ACCESS_DENIED'))));
		}
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$sectionObject = new CIBlockSection;
		if($sectionObject->delete($sectionIdForDelete, false))
			$this->sendJsonSuccessResponse(array('message' => Loc::getMessage('LISTS_LAC_MESSAGE_SUCCESS')));
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_UNKNOWN_ERROR'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function processActionEditSection()
	{
		$this->checkRequiredPostParams(array('iblockTypeId', 'iblockId', 'sectionId',
			'sectionName', 'socnetGroupId', 'currentSectionId'));
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = intval($this->request->getPost('sectionId'));
		$sectionName = trim($this->request->getPost('sectionName'), " \n\r\t");
		$currentSectionId = intval($this->request->getPost('currentSectionId'));

		$this->checkPermission();
		if($this->listPerm < CListPermissions::CAN_WRITE
			&& !CIBlockSectionRights::userHasRightTo($this->iblockId, $currentSectionId, 'section_edit'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_ACCESS_DENIED'))));
		}
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$sectionObject = new CIBlockSection;
		$queryObject = CIBlockSection::getList(array(), array(
			'IBLOCK_ID' => $this->iblockId,
			'ID' => $currentSectionId,
			'GLOBAL_ACTIVE' => 'Y',
			'CHECK_PERMISSIONS' => 'N',
		));
		if($section = $queryObject->getNext())
		{
			$sectionObject->update($currentSectionId, array(
				'IBLOCK_ID' => $this->iblockId,
				'NAME' => $sectionName,
			));
			$this->sendJsonSuccessResponse(array('message' => Loc::getMessage('LISTS_LAC_MESSAGE_SUCCESS')));
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_UNKNOWN_ERROR'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function processActionGetSection()
	{
		$this->checkRequiredPostParams(
			array('iblockTypeId', 'iblockId', 'sectionId', 'socnetGroupId', 'currentSectionId'));
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');
		$currentSectionId = intval($this->request->getPost('currentSectionId'));

		$this->checkPermission();
		if($this->listPerm < CListPermissions::CAN_WRITE
			&& !CIBlockSectionRights::userHasRightTo($this->iblockId, $currentSectionId, 'section_read'))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_ACCESS_DENIED'))));
		}
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$queryObject = CIBlockSection::getList(array(), array(
			'IBLOCK_ID' => $this->iblockId,
			'ID' => $currentSectionId,
			'GLOBAL_ACTIVE' => 'Y',
			'CHECK_PERMISSIONS' => 'N',
		));
		if($section = $queryObject->getNext())
		{
			$this->sendJsonSuccessResponse(array('data' => array('NAME' => $section['~NAME']),
				'message' => Loc::getMessage('LISTS_LAC_MESSAGE_SUCCESS')));
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_SECTION_NOT_GET_DATA'))));
			$this->sendJsonErrorResponse();
		}
	}

	protected function processActionPerformActionBp()
	{
		$this->checkRequiredPostParams(
			array('workflowId', 'iblockTypeId', 'elementId', 'iblockId', 'sectionId', 'socnetGroupId', 'action')
		);

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');

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

		$this->sendJsonSuccessResponse(array('message' => Loc::getMessage('LISTS_LAC_MESSAGE_SUCCESS')));
	}

	protected function checkPermission()
	{
		global $USER;
		$this->listPerm = CListPermissions::checkAccess(
			$USER,
			$this->iblockTypeId,
			$this->iblockId,
			$this->socnetGroupId
		);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif(
			$this->listPerm < CListPermissions::CAN_READ && !(
				CIBlockRights::userHasRightTo($this->iblockId, $this->iblockId, "element_read") ||
				CIBlockSectionRights::userHasRightTo($this->iblockId, $this->sectionId, "section_element_bind")
			)
		)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_LAC_ACCESS_DENIED'))));
		}
	}

	protected function processActionToogleSectionGrid()
	{
		$this->checkRequiredPostParams(array('gridId'));
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$gridId = $this->request->getPost('gridId');
		$showSectionGrid = CUserOptions::getOption('lists_show_section_grid', $gridId, 'N');
		if($showSectionGrid == 'Y')
		{
			$currentValue = 'N';
			CUserOptions::setOption('lists_show_section_grid', $gridId, 'N');
		}
		else
		{
			$currentValue = 'Y';
			CUserOptions::setOption('lists_show_section_grid', $gridId, 'Y');
		}

		$this->sendJsonSuccessResponse(array("currentValue" => $currentValue));
	}

	protected function processActionRebuildSeachableContent()
	{
		$this->checkRequiredPostParams(array('iblockId'));

		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
		$this->sectionId = $this->request->getPost('sectionId');

		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$rebuildedData = Option::get('lists', 'rebuild_seachable_content');
		$rebuildedData = unserialize($rebuildedData);
		if(isset($rebuildedData[$this->iblockId]))
		{
			$agentName = 'CLists::runRebuildSeachableContent('.$this->iblockId.');';
			$queryObject = CAgent::getList(array(), array('NAME' => $agentName));
			if(!$queryObject->fetch())
			{
				CAgent::addAgent($agentName, 'lists', 'Y', 5, '', 'Y', ConvertTimeStamp(
					time() + CTimeZone::getOffset(), 'FULL'));
			}

			$totalItems = $this->request->getPost('totalItems');
			$this->sendJsonProcessingResponse(
				array('processedItems' => $rebuildedData[$this->iblockId], 'totalItems' => $totalItems));
		}
		else
		{
			$this->sendJsonCompletedResponse();
		}
	}
}
$controller = new ListAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();