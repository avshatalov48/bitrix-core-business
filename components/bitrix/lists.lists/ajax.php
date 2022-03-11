<?php

use Bitrix\Lists\Internals\Error\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Lists\Internals\Controller;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('lists') || !\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ListsAjaxController extends Controller
{
	/** @var  int */
	protected $iblockId;
	/** @var  string */
	protected $iblockTypeId;
	protected $listPerm;

	protected function listOfActions()
	{
		return array(
			'setLiveFeed' => array(
				'method' => array('POST'),
			),
			'createDefaultProcesses' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionSetLiveFeed()
	{
		$this->checkRequiredPostParams(array('iblockId', 'checked'));
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->iblockId = intval($this->request->getPost('iblockId'));
		if($this->request->getPost('checked') == 'true')
			$checked = 1;
		else
			$checked = 0;

		CLists::setLiveFeed($checked, $this->iblockId);

		$this->sendJsonSuccessResponse();
	}

	protected function processActionCreateDefaultProcesses()
	{
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}
		try
		{
			$defaultSiteId = CSite::GetDefSite();
			$iterator = CSite::GetByID($defaultSiteId);
			$site = $iterator->Fetch();
			$defaultLang = $site? $site['LANGUAGE_ID'] : 'en';
			if($defaultLang == 'ua')
				$defaultLang = 'ru';
			\Bitrix\Lists\Importer::installProcesses($defaultLang, $this->request->getPost('siteId'));
		}
		catch (Exception $e)
		{
			$this->errorCollection->add(array(new Error($e->getMessage())));
		}
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse();
	}

	protected function checkPermission()
	{
		$this->listPerm = CListPermissions::checkAccess(
			$this->getUser(),
			$this->iblockTypeId
		);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif($this->listPerm < CListPermissions::IS_ADMIN)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_ACCESS_DENIED'))));
		}
	}
}

$controller = new ListsAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();