<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\Error;

class File extends Base
{
	private const TOKEN_SALT = 'bizproc.file.show';

	public function configureActions(): array
	{
		$configureActions = parent::configureActions();
		$configureActions['show'] = [
			'-prefilters' => [
				Main\Engine\ActionFilter\Csrf::class,
				Main\Engine\ActionFilter\Authentication::class,
			],
			'+prefilters' => [
				new Main\Engine\ActionFilter\CloseSession(),
			]
		];

		return $configureActions;
	}

	public function showAction($token)
	{
		[$fileId] = self::extractToken($token);
		$file = null;

		if ($fileId)
		{
			$file = \CFile::getFileArray($fileId);
		}

		if (!$file)
		{
			$this->addError(new Error('No file'));
			return null;
		}

		return BFile::createByFileData($file);
	}

	public static function getPublicLink(int $fileId): string
	{
		return Main\Engine\UrlManager::getInstance()->create(
			'bizproc.file.show',
			[
				'token' => static::generateToken($fileId),
			],
			true
		)->getUri();
	}

	private static function generateToken(int $fileId)
	{
		$signer = new Main\Security\Sign\Signer;
		return $signer->sign((string) $fileId, self::TOKEN_SALT);
	}

	private static function extractToken(string $token): array
	{
		$signer = new Main\Security\Sign\Signer;

		try
		{
			$unsigned = $signer->unsign($token, self::TOKEN_SALT);
			$result = [$unsigned];
		}
		catch (\Exception $e)
		{
			$result = [null];
		}

		return $result;
	}
}