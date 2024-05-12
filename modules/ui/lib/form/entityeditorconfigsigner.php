<?php

namespace Bitrix\UI\Form;

use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Engine\CurrentUser;

class EntityEditorConfigSigner
{
	public function __construct(private string $configId)
	{
	}

	private const SIGNED_PARAMS_SALT = 'EntityEditorConfigSigner';

	public function sign(array $paramsToSign): string
	{
		$paramsToSign['userId'] = (int)CurrentUser::get()->getId(); // must be user dependant
		$paramsToSign['configId'] = $this->configId; // must be editor dependant

		return (new Signer())->sign(Json::encode($paramsToSign), $this->getSalt());
	}

	public function unsign(string $signedParams): ?array
	{
		try
		{
			$params = (new Signer())->unsign($signedParams,  $this->getSalt());
			$params = (array)Json::decode($params);
			if (($params['userId'] ?? 0) !== (int)CurrentUser::get()->getId())
			{
				return null;
			}
			if (($params['configId'] ?? '') !== $this->configId)
			{
				return null;
			}
			unset($params['userId']);
			unset($params['configId']);

			return $params;
		}
		catch (BadSignatureException $e)
		{
			return null;
		}
	}

	private function getSalt(): string
	{
		return self::SIGNED_PARAMS_SALT;
	}
}
