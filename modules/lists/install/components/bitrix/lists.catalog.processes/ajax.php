<?php
use Bitrix\Lists\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Lists\Internals\Controller;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('lists') || !\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class CatalogProcessesAjaxController extends Controller
{
	/** @var  string or int */
	protected $listPerm;
	/** @var  string */
	protected $iblockTypeId;

	protected function listOfActions()
	{
		return array(
			'installProcesses' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionInstallProcesses()
	{
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermission();
		$this->checkRequiredPostParams(array('processes'));
		if($this->errorCollection->hasErrors())
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_NOT_SELECTED_PROCESSES'))));
			$this->sendJsonErrorResponse();
		}
		$siteId = SITE_ID;
		if($this->request->getPost('siteId'))
			$siteId = $this->request->getPost('siteId');

		try
		{
			$processes = $this->request->getPost('processes');
			if(is_array($processes))
			{
				foreach($processes as $filePath)
				{
					\Bitrix\Lists\Importer::installProcess($filePath, $siteId);
				}
			}
			else
			{
				$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_INCORRECT_DATA'))));
			}
		}
		catch (Exception $e)
		{
			$this->errorCollection->add(array(new Error($e->getMessage())));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}
		$this->sendJsonSuccessResponse(array('message' => Loc::getMessage('LISTS_CPAC_MESSAGE_SUCCESS')));
	}

	protected function checkPermission()
	{
		global $USER;
		$this->listPerm = CListPermissions::checkAccess($USER, $this->iblockTypeId);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif($this->listPerm < CListPermissions::IS_ADMIN)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_CPAC_ACCESS_DENIED'))));
		}
	}
}
$controller = new CatalogProcessesAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();