<?php

namespace Bitrix\Vote\Model\Dto;

use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Attachment\Manager;
use Bitrix\Vote\Service\AttachedVoteResultUrlService;
use Bitrix\Vote\Service\AttachedVoteSigner;

class AttachedVotePayload
{
	public function __construct(
		public readonly ?Attach $attach,
		public readonly bool $readAccessThroughSignedParam = false,
	)
	{}

	public static function makeByAttachId(int $attachId): AttachedVotePayload
	{
		try
		{
			$attach = new Attach($attachId);
		}
		catch (ObjectNotFoundException)
		{
			$attach = null;
		}

		return new AttachedVotePayload($attach);
	}

	public static function makeByEntityId(
		string $moduleId,
		string $entityType,
		int $entityId
	): AttachedVotePayload
	{
		return new AttachedVotePayload(Manager::loadFirstFromEntity($moduleId, $entityType, $entityId));
	}

	public static function makeBySignedAttachId(string $signedAttachId): AttachedVotePayload
	{
		try
		{
			$attach = (new AttachedVoteResultUrlService())->getAttachByUrlId($signedAttachId);
		}
		catch (ObjectNotFoundException|BadSignatureException)
		{
			$attach = null;
		}

		return new AttachedVotePayload($attach, true);
	}
}