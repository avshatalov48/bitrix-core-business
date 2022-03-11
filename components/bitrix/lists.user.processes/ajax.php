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

class ProcessesAjaxController extends Controller
{
	/** @var  string */
	protected $iblockTypeId;
	protected $listPerm;

	protected function listOfActions()
	{
		return array(
			'showProcesses' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionShowProcesses()
	{
		$this->iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$this->checkPermission();
		if($this->errorCollection->hasErrors())
		{
			$this->sendJsonErrorResponse();
		}

		$siteDir = '/';
		$siteId = true;
		if($this->request->getPost('siteDir'))
			$siteDir = $this->request->getPost('siteDir');
		if($this->request->getPost('siteId'))
			$siteId = $this->request->getPost('siteId');

		$path = rtrim($siteDir, '/');

		$listData = array();
		$lists = CIBlock::getList(
			array("SORT" => "ASC","NAME" => "ASC"),
			array("ACTIVE" => "Y","TYPE" => $this->iblockTypeId, "SITE_ID" => $siteId)
		);
		while($list = $lists->fetch())
		{
			if(CLists::getLiveFeed($list['ID']))
			{
				$listData[$list['ID']]['name'] = $list['NAME'];
				$listData[$list['ID']]['url'] = $path.COption::GetOptionString('lists', 'livefeed_url').'?livefeed=y&list_id='.$list["ID"].'&element_id=0';
				if($list['PICTURE'] > 0)
				{
					$imageFile = CFile::GetFileArray($list['PICTURE']);
					if($imageFile !== false)
					{
						$imageFile = CFile::ResizeImageGet(
							$imageFile,
							array("width" => 36, "height" => 30),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							false
						);
						$listData[$list['ID']]['picture'] = '<img src="'.$imageFile["src"].'" width="19" height="16" border="0" />';
					}
				}
				else
				{
					$listData[$list['ID']]['picture'] = '<img src="/bitrix/images/lists/default.png" width="19" height="16" border="0" />';
				}
			}
		}

		if(!empty($listData))
		{
			$this->sendJsonSuccessResponse(
				array(
					'lists' => $listData
				)
			);
		}
		else
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('LISTS_SEAC_NOT_PROCESSES'))));
			$this->sendJsonErrorResponse();
		}
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
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_WRONG_IBLOCK_TYPE'))));
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_WRONG_IBLOCK'))));
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED'))));
					break;
				default:
					$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_UNKNOWN_ERROR'))));
					break;
			}
		}
		elseif($this->listPerm <= CListPermissions::ACCESS_DENIED)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('CC_BLL_ACCESS_DENIED'))));
		}
	}
}

$controller = new ProcessesAjaxController();
$controller
	->setActionName(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec();