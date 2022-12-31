<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Error;

class SocialnetworkGroupCreateAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function __construct(\Bitrix\Main\Request $request = null)
	{
		parent::__construct($request);

		Loader::includeModule('socialnetwork');
	}

	public function getComponentAction(array $params = []): \Bitrix\Main\Engine\Response\Component
	{
		$componentParameters = ComponentHelper::getWorkgroupSliderMenuUnsignedParameters($this->getSourceParametersList());
		if (isset($params['TAB']))
		{
			$componentParameters['TAB'] = $params['TAB'];
		}

		$componentResponse = new \Bitrix\Main\Engine\Response\Component(
			'bitrix:socialnetwork.group_create.ex',
			($params['componentTemplate'] ?? ''),
			$componentParameters,
			[],
			[ 'PageTitle' ]
		);

		return $componentResponse;
	}

	public function loadPhotoAction(): ?array
	{
		$newPhotoFile = $this->getRequest()->getFile('newPhoto');

		if (!$newPhotoFile)
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_ERROR_NO_FILE')));
			return null;
		}

		if (!(\CFile::checkImageFile($newPhotoFile, 0, 0, 0, ['IMAGE']) === null))
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_ERROR_NO_IMAGE')));
			return null;
		}

		$newPhotoFile['MODULE_ID'] = 'socialnetwork';
		$fileId = \CFile::saveFile($newPhotoFile, 'socialnetwork');
		if (!$fileId)
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_SAVE_FILE_FAILED')));
			return null;
		}

		if (
			!isset($_SESSION['workgroup_avatar_loader'])
			|| !is_array($_SESSION['workgroup_avatar_loader'])
		)
		{
			$_SESSION['workgroup_avatar_loader'] = [];
		}

		$_SESSION['workgroup_avatar_loader'][] = (int)$fileId;

		$fileTmp = \CFile::resizeImageGet(
			$fileId,
			[
				'width' => 300,
				'height' => 300,
			],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false,
			false,
			true,
		);

		return [
			'success' => true,
			'fileId' => $fileId,
			'fileUri' => $fileTmp['src'],
		];
	}

	public function deletePhotoAction($fileId = 0, $groupId = 0): ?array
	{
		$groupId = (int)$groupId;
		$fileId = (int)$fileId;

		if ($fileId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_ERROR_NO_FILE')));
			return null;
		}

		if (
			($groupId > 0)
			&& !\Bitrix\Socialnetwork\Helper\Workgroup\Access::canUpdate([
				'groupId' => $groupId,
			])
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_ERROR_WRONG_WORKGROUP_ID')));
			return null;
		}


		if (
			$groupId <= 0
			&& !in_array($fileId, $_SESSION['workgroup_avatar_loader'], true)
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_DELETE_FILE_FAILED')));
			return null;
		}

		if ($groupId > 0)
		{
			$group = \Bitrix\Socialnetwork\WorkgroupTable::getById($groupId)->fetchObject();
			if (!$group)
			{
				$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_DELETE_FILE_FAILED')));
				return null;
			}
			if (
				!isset($_SESSION['workgroup_avatar_loader'])
				|| !is_array($_SESSION['workgroup_avatar_loader'])
			)
			{
				$_SESSION['workgroup_avatar_loader'] = [];
			}

			if (
				$group->getImageId() !== $fileId
				&& !in_array($fileId, $_SESSION['workgroup_avatar_loader'], true)
			)
			{
				$this->addError(new Error(Loc::getMessage('SONET_GCE_AJAX_DELETE_FILE_FAILED')));
				return null;
			}
		}

		\CFile::delete($fileId);

		return [
			'success' => true,
		];
	}

}