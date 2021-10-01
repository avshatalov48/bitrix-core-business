<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Report\Internals\Controller;
use Bitrix\Main\Error;
use Bitrix\Report\Sharing;
use Bitrix\Report\RightsManager;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('report') || !\Bitrix\Main\Application::getInstance()->getContext()
		->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ReportListAjaxController extends Controller
{
	/** @var  int */
	/** @var  string */

	const ERROR_ACCESS_DENIED = 'REPORT_RLAC_22001';
	const ERROR_CHANGE_SHARING = 'REPORT_RLAC_22002';

	protected function listActions()
	{
		return array(
			'showSharing' => array(
				'method' => array('POST'),
			),
			'changeSharing' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionShowSharing()
	{
		if ($this->isRestricted())
		{
			$this->sendJsonErrorResponse();
			return;
		}

		$this->checkRequiredPostParams(array('reportId'));
		if($this->errorCollection->count())
		{
			$this->sendJsonErrorResponse();
		}

		$userId = $this->getUser()->getId();
		$reportId = $this->request->getPost('reportId');
		$rightsmanager = new RightsManager($userId);

		if(!$rightsmanager->canShare($reportId))
		{
			$this->errorCollection->add(array(
				new Error(Loc::getMessage('REPORT_ACCESS_DENIED'), self::ERROR_ACCESS_DENIED)));
			$this->sendJsonErrorResponse();
		}

		$entitySharing = Sharing::getEntityOfSharing($reportId);
		$entityList = array();
		foreach($entitySharing as $entity)
		{
			list($type, $id) = Sharing::parseEntityValue($entity['ENTITY']);
			$typeData = Sharing::getTypeData($type, $id);
			$entityList[] = array(
				'entityId' => $entity['ENTITY'],
				'name' => $typeData['name'],
				'right' => $entity['RIGHTS'],
				'avatar' => $typeData['avatar'],
				'type' => $type,
			);
		}

		$owner = Sharing::getUserData($userId);

		$selected = array();
		foreach($entityList as $entity)
		{
			$selected[] = $entity['entityId'];
		}
		$destination = Sharing::getSocNetDestination($userId, $selected);

		$this->sendJsonSuccessResponse(array(
			'members' => $entityList,
			'owner' => $owner,
			'destination' => array(
				'items' => array(
					'users' => $destination['USERS'],
					'groups' => array(),
					'sonetgroups' => $destination['SONETGROUPS'],
					'department' => $destination['DEPARTMENT'],
					'departmentRelation' => $destination['DEPARTMENT_RELATION'],
				),
				'itemsLast' => array(
					'users' => $destination['LAST']['USERS'],
					'groups' => array(),
					'sonetgroups' => $destination['LAST']['SONETGROUPS'],
					'department' => $destination['LAST']['DEPARTMENT'],
				),
				'itemsSelected' => $destination['SELECTED'],
			),
		));
	}

	protected function processActionChangeSharing()
	{
		if ($this->isRestricted())
		{
			$this->sendJsonErrorResponse();
			return;
		}

		$this->checkRequiredPostParams(array('reportId'));
		if($this->errorCollection->count())
			$this->sendJsonErrorResponse();

		$userId = $this->getUser()->getId();
		$reportId = intval($this->request->getPost('reportId'));
		$entityToNewShared = $this->request->getPost('entityToNewShared');
		$rightsmanager = new RightsManager($userId);

		if(!$rightsmanager->canShare($reportId))
		{
			$this->errorCollection->add(array(
				new Error(Loc::getMessage('REPORT_ACCESS_DENIED'), self::ERROR_ACCESS_DENIED)));
			$this->sendJsonErrorResponse();
		}

		$sharing = new Bitrix\Report\Sharing($reportId);
		$sharing->changeSharing($entityToNewShared);
		$this->errorCollection->add($sharing->getErrors());

		if($this->errorCollection->count())
			$this->sendJsonErrorResponse();

		$this->sendJsonSuccessResponse();
	}

	private function isRestricted(): bool
	{
		if (
			\Bitrix\Main\Loader::includeModule('bitrix24')
			&& !\Bitrix\Bitrix24\Feature::isFeatureEnabled('report')
		)
		{
			return true;
		}

		return false;
	}
}

$controller = new ReportListAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();