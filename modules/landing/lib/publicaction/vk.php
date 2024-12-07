<?php

namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing\Error;
use Bitrix\Landing\PublicActionResult;
use Bitrix\Main\Loader;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Media;

class Vk
{
	public static function isAuthorized(): PublicActionResult
	{
		$result = new PublicActionResult();
		if (!Loader::includeModule('seo'))
		{
			$error = new Error;
			$error->addError('SEO_NOT_INCLUDED');
			$result->setError($error);

			return $result;
		}

		$service = Media\Service::getInstance();
		$authAdapter = Retargeting\AuthAdapter::create('vkontakte', $service);

		$result->setResult($authAdapter->hasAuth());

		return $result;
	}

	public static function getAuthUrl(): PublicActionResult
	{
		$result = new PublicActionResult();
		if (!Loader::includeModule('seo'))
		{
			$error = new Error;
			$error->addError('SEO_NOT_INCLUDED');
			$result->setError($error);

			return $result;
		}

		$service = Media\Service::getInstance();
		$authAdapter = Retargeting\AuthAdapter::create('vkontakte', $service);

		$result->setResult($authAdapter->getAuthUrl());

		return $result;
	}

	public static function getVideoInfo(string $videoId): PublicActionResult
	{
		$result = new PublicActionResult();
		if (!Loader::includeModule('seo'))
		{
			$error = new Error;
			$error->addError('SEO_NOT_INCLUDED');
			$result->setError($error);
			$result->setResult(false);

			return $result;
		}

		$response = Media\Service::getVideo($videoId);
		if ($response->isSuccess())
		{
			$responseData = $response->getData();
			if ($responseData['count'])
			{
				$responseItem = $responseData['items'][0];
				if ($responseItem['content_restricted'] && $responseItem['content_restricted_message'])
				{
					$error = new Error;
					$error->addError(
						'CONTENT_RESTRICTED',
						$responseItem['content_restricted_message']
					);
					$result->setError($error);
				}
				else
				{
					$result->setResult([
						'player' => $responseItem['player'],
						'preview' => $responseItem['image'][min(count($responseItem['image']), 4) - 1],
					]);
				}
			}
			else
			{
				$result->setResult([]);
			}
		}
		else
		{
			$error = new Error;
			foreach ($response->getErrors() as $err)
			{
				$error->addError($err->getCode(), $err->getMessage());
			}
			$result->setError($error);
		}

		return $result;
	}
}
