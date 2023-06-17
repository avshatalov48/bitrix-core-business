<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Import\ImportError;
use Bitrix\Im\V2\Import\ImportService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use CFile;
use CRestUtil;

class Import extends Controller
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new \Bitrix\Rest\Engine\ActionFilter\Scope('im.import'),
				new \Bitrix\Main\Engine\ActionFilter\Scope(\Bitrix\Main\Engine\ActionFilter\Scope::REST),
				new \Bitrix\Rest\Engine\ActionFilter\AuthType(\Bitrix\Rest\Engine\ActionFilter\AuthType::APPLICATION)
			]
		);
	}

	public function createGroupAction(int $ownerId, CurrentUser $user, array $fields = [], string $externalId = ''): ?array
	{
		if (!ImportService::isAdmin((int)$user->getId()))
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}
		$isOpen = ($fields['isOpen'] ?? 'N') === 'Y';
		$type = $isOpen ? \IM_MESSAGE_OPEN : \IM_MESSAGE_CHAT;

		$chatParams = [
			'TITLE' => $fields['title'] ?? null,
			'DESCRIPTION' => $fields['description'] ?? null,
			'TYPE' => $type,
			'AVATAR_ID' => $this->saveAvatar($fields['avatar'] ?? null),
			'ENTITY_ID' => $externalId,
			'AUTHOR_ID' => $ownerId,
			'USERS' => false,
		];

		$initResult = ImportService::create($chatParams);

		if (!$initResult->isSuccess())
		{
			$this->addErrors($initResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($initResult->getResult());
	}

	public function createPrivateAction(array $users, CurrentUser $user, string $externalId = ''): ?array
	{
		if (!ImportService::isAdmin((int)$user->getId()))
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}
		if (count($users) !== 2)
		{
			$this->addError(new ImportError(ImportError::PRIVATE_CHAT_COUNT_USERS_ERROR));

			return null;
		}

		$users = array_map('intval', $users);

		$chatParams = [
			'TYPE' => \IM_MESSAGE_PRIVATE,
			'ENTITY_ID' => $externalId,
			'ENTITY_DATA_1' => "{$users[0]}|{$users[1]}",
			'AUTHOR_ID' => $users[0],
			'USERS' => false,
		];

		$initResult = ImportService::create($chatParams);

		if (!$initResult->isSuccess())
		{
			$this->addErrors($initResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($initResult->getResult());
	}

	public function getFolderIdAction(int $chatId, CurrentUser $user): ?array
	{
		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		if (!(new ImportService($chat, (int)$user->getId()))->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$folderId = \CIMDisk::GetFolderModel($chatId)->getId();
		\CIMDisk::ChangeFolderMembers($chatId, (int)$user->getId());

		return ['chatFolderId' => $folderId];
	}

	public function abortAction(int $chatId, CurrentUser $user): ?array
	{
		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		$importService = new ImportService($chat, (int)$user->getId());

		if (!$importService->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$abortResult = $importService->abort();

		if (!$abortResult->isSuccess())
		{
			$this->addErrors($abortResult->getErrors());

			return null;
		}

		return [
			'success' => true
		];
	}

	public function commitGroupAction(int $chatId, array $users, CurrentUser $user, \CRestServer $server): ?array
	{
		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		$importService = new ImportService($chat, (int)$user->getId());

		if (!$importService->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$finalizeResult = $importService->commitGroup($users, $server->getClientId());

		if (!$finalizeResult->isSuccess())
		{
			$this->addErrors($finalizeResult->getErrors());

			return null;
		}

		return [
			'success' => true
		];
	}

	public function commitPrivateAction(int $chatId, string $newIsMain, CurrentUser $user, \CRestServer $server, string $hideOriginal = 'Y'): ?array
	{
		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		$importService = new ImportService($chat, (int)$user->getId());

		if (!$importService->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$finalizeResult = $importService->commitPrivate($newIsMain === 'Y', $hideOriginal === 'Y', $server->getClientId());

		if (!$finalizeResult->isSuccess())
		{
			$this->addErrors($finalizeResult->getErrors());

			return null;
		}

		return [
			'success' => true
		];
	}

	private function saveAvatar(?string $fileContent): ?int
	{
		if (!isset($fileContent) || !$fileContent)
		{
			return null;
		}

		$file = CRestUtil::saveFile($fileContent);
		$imageCheck = (new \Bitrix\Main\File\Image($file["tmp_name"]))->getInfo();
		if(
			!$imageCheck
			|| !$imageCheck->getWidth()
			|| $imageCheck->getWidth() > 5000
			|| !$imageCheck->getHeight()
			|| $imageCheck->getHeight() > 5000
		)
		{
			return null;
		}

		if (!$file || !(mb_strpos($file['type'], 'image/') === 0))
		{
			return null;
		}

		return CFile::saveFile($file, 'im');
	}

}