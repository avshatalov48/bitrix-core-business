<?php

namespace Bitrix\Im\V2\Controller\Call;

use Bitrix\Main\Engine\Controller;
use Bitrix\UI\InfoHelper;

class Background extends Controller
{
	/**
	 * @restMethod im.v2.Call.Background.get
	 */
	public function getAction()
	{
		$diskFolder = \Bitrix\Im\Call\Background::getUploadFolder();
		$diskFolderId = $diskFolder? (int)$diskFolder->getId(): 0;
		$infoHelperParams = \Bitrix\Main\Loader::includeModule('ui')? InfoHelper::getInitParams(): [];

		return [
			'backgrounds' => [
				'default' => \Bitrix\Im\Call\Background::get(),
				'custom' => \Bitrix\Im\Call\Background::getCustom(),
			],
			'upload' => [
				'folderId' => $diskFolderId,
			],
			'limits' => \Bitrix\Im\Call\Background::getLimitForJs(),
			'infoHelperParams' => $infoHelperParams,
		];
	}

	/**
	 * @restMethod im.v2.Call.Background.commit
	 */
	public function commitAction(int $fileId)
	{
		$result = \CIMDisk::CommitBackgroundFile(
			$this->getCurrentUser()->getId(),
			$fileId
		);

		if (!$result)
		{
			$this->addError(new \Bitrix\Main\Error(
				"Specified fileId is not located in background folder.",
				"FILE_ID_ERROR"
			));

			return false;
		}

		return [
			'result' => true
		];
	}

	/**
	 * @restMethod im.v2.Call.Background.delete
	 */
	public function deleteAction(int $fileId)
	{
		$result = \CIMDisk::DeleteBackgroundFile(
			$this->getCurrentUser()->getId(),
			$fileId
		);

		if (!$result)
		{
			$this->addError(new \Bitrix\Main\Error(
				"Specified fileId is not located in background folder.",
				"FILE_ID_ERROR"
			));

			return false;
		}

		return [
			'result' => true
		];
	}
}