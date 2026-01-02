<?php

namespace Bitrix\Vote\Service;

use Bitrix\Main\Security\Sign\Signer;

class AttachedVoteSigner
{
	public const SIGN_SALT = 'vote.result';

	public function sign(int $attachId): string
	{
		return (new Signer())->sign((string)$attachId, self::SIGN_SALT);
	}

	/**
	 * @param string $signedAttachId
	 *
	 * @return int
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\Security\Sign\BadSignatureException
	 */
	public function unsign(string $signedAttachId): int
	{
		return (int)(new Signer())->unsign($signedAttachId, self::SIGN_SALT);
	}
}