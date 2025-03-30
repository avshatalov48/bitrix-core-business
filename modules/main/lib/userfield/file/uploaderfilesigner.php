<?php

namespace Bitrix\Main\UserField\File;


use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Security\Sign\BadSignatureException;

class UploaderFileSigner
{
	private const SALT_PREFIX = 'UploaderFileSigner_';
	public function __construct(
		private readonly string $entityId,
		private readonly string $fieldName
	)
	{
	}

	public function sign(int $fileId): string
	{
		return (new Signer())->sign((string)$fileId, $this->getSalt());
	}

	public function verify(string $signedString, int $fileId): bool
	{
		try
		{
			$unsignedFileId = (int)(new Signer())->unsign($signedString, $this->getSalt());

			return ($fileId === $unsignedFileId);
		}
		catch (BadSignatureException $e)
		{
			return false;
		}

		return false;
	}

	private function getSalt(): string
	{
		return substr(preg_replace('/[^a-zA-Z0-9_.-]+/i', '',self::SALT_PREFIX . '_' . $this->entityId .'_' . $this->fieldName), 0, 50);
	}
}
