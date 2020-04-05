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

class ListsEditAjaxController extends Controller
{
	/** @var  int */
	protected $iblockId;
	protected $socnetGroupId = 0;
	/** @var  string */
	protected $iblockTypeId;
	protected $listPerm;

	protected function listOfActions()
	{
		return array(
			'copyIblock' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionCopyIblock()
	{
		$this->checkRequiredPostParams(array('iblockTypeId', 'iblockId', 'socnetGroupId'));

		$this->fillDataForCheckPermission();
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$errors = array();
		$copyIblockId = CLists::copyIblock($this->iblockId, $errors);
		if(!empty($errors))
		{
			foreach($errors as $error)
				$this->errorCollection->addOne(new Error($error));
		}
		if($this->errorCollection->hasErrors())
			$this->sendJsonErrorResponse();

		$this->sendJsonSuccessResponse(
			array('copyIblockId' => $copyIblockId, 'message' => Loc::getMessage('LISTS_MESSAGE_SUCCESS')));
	}

	protected function fillDataForCheckPermission()
	{
		$this->iblockId = intval($this->request->getPost('iblockId'));
		$this->iblockTypeId = $this->request->getPost('iblockTypeId');
		$this->socnetGroupId = intval($this->request->getPost('socnetGroupId'));
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
		elseif($this->listPerm < CListPermissions::IS_ADMIN ||
			!($this->iblockId && CIBlockRights::userHasRightTo($this->iblockId, $this->iblockId, "iblock_edit")))
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_ACCESS_DENIED'))));
		}
	}
}

$controller = new ListsEditAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();