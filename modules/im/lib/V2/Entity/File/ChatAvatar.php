<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Im\Common;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Main\File\Image;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;
use CFile;

class ChatAvatar
{
	protected Chat $chat;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
	}

	public function get(bool $addBlankPicture = false, bool $withDomain = false): string
	{
		$url = $addBlankPicture ? '/bitrix/js/im/images/blank.gif' : '';

		if ($this->chat->getAvatarId() > 0)
		{
			$avatar = $this->getResizeAvatar();
			if (!empty($avatar['src']))
			{
				$url = $avatar['src'];
			}
		}

		if ($withDomain && $url)
		{
			return Common::getPublicDomain() . $url;
		}

		return $url;
	}

	public function update(
		int|string $avatar,
		bool $withMessage = true,
		bool $skipRecent = false,
		bool $withoutSaveInChat = false
	): Result
	{
		if (!is_numeric($avatar))
		{
			$avatar = self::saveAvatarByString((string)$avatar) ?? 0;
		}

		return $this->updateById(
			(int)$avatar,
			$withMessage,
			$skipRecent,
			$withoutSaveInChat
		);
	}

	protected function updateById(
		int $avatarId,
		bool $withMessage = true,
		bool $skipRecent = false,
		bool $withoutSaveInChat = false
	): Result
	{
		$result = new Result();

		$oldAvatarId = $this->chat->getAvatarId();
		$this->chat->setAvatarId($avatarId);

		if (!$withoutSaveInChat)
		{
			$result = $this->chat->save();
			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		if (isset($oldAvatarId) && $oldAvatarId > 0)
		{
			CFile::Delete($oldAvatarId);
		}

		$avatar = $this->getResizeAvatar();
		if (
			!empty($avatar['src'])
			&& Loader::includeModule("pull")
		)
		{
			$this->sendPush($avatar);
		}

		if ($withMessage)
		{
			$this->chat->sendMessageUpdateAvatar($skipRecent);
		}

		return $result->setResult($avatarId);
	}

	protected function sendPush(array $avatar): void
	{
		$pushMessage = [
			'module_id' => 'im',
			'command' => 'chatAvatar',
			'params' => [
				'chatId' => $this->chat->getChatId(),
				'avatar' => $avatar['src'],
			],
			'extra' => Common::getPullExtra()
		];

		Event::add($this->chat->getRelations()->getUserIds(), $pushMessage);
		if ($this->chat->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chat->getId(), $pushMessage);
		}
	}

	protected function getResizeAvatar(): array
	{
		$avatar = \CFile::ResizeImageGet(
			$this->chat->getAvatarId() ?? 0,
			['width' => 200, 'height' => 200],
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true
		);
		if (empty($avatar))
		{
			return [];
		}

		return $avatar;
	}

	public static function saveAvatarByString(string $avatarBase64): ?int
	{
		$avatar = \CRestUtil::saveFile($avatarBase64);
		$imageCheck = (new Image($avatar["tmp_name"]))->getInfo();

		if (
			!$imageCheck
			|| !$imageCheck->getWidth() || $imageCheck->getWidth() > 5000
			|| !$imageCheck->getHeight() || $imageCheck->getHeight() > 5000
		)
		{
			return null;
		}

		if (!$avatar || mb_strpos($avatar['type'], "image/") !== 0)
		{
			return null;
		}

		$avatar = CFile::saveFile($avatar, 'im');

		return is_numeric($avatar) && $avatar > 0 ? $avatar : null;
	}
}
