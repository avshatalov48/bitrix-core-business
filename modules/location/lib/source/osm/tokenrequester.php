<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\OrmConverter;
use Bitrix\Location\Infrastructure\Service\LoggerService;
use Bitrix\Location\Repository\SourceRepository;
use Bitrix\Main\Data\ManagedCache;
use Bitrix\Main\Result;
use Bitrix\Main\Service\MicroService\BaseSender;
use Bitrix\Main\Application;
use Bitrix\Main\Service\MicroService\Client;

final class TokenRequester extends BaseSender
{
	private const SAFE_BUFFER_TIME_SECONDS = 60;
	private const ERROR_LICENSE_NOT_FOUND = 'LICENSE_NOT_FOUND';
	private const ERROR_WRONG_SIGN = 'WRONG_SIGN';
	private const ERROR_LICENSE_DEMO = 'LICENSE_DEMO';
	private const ERROR_LICENSE_NOT_ACTIVE = 'LICENSE_NOT_ACTIVE';

	private const CACHE_TABLE = '/bx/osmgateway/license';
	private const CACHE_TTL = 86400;

	private OsmSource $source;
	private ManagedCache $cacheManager;

	public function setSource(OsmSource $source): TokenRequester
	{
		$this->source = $source;
		$this->cacheManager = Application::getInstance()->getManagedCache();
		return $this;
	}

	public function getToken(): ?Token
	{
		$token = $this->getFromConfig();
		if ($token)
		{
			return $token;
		}

		if (!$this->hasLicenseIssues())
		{
			$token = $this->requestNewToken();
			if ($token)
			{
				$this->updateConfigToken($token);
			}
		}

		return $token;
	}

	private function updateConfigToken(Token $token): void
	{
		$config = $this->source->getConfig();
		if (!$config)
		{
			$config = new Config();
			$this->source->setConfig($config);
		}
		$config->setValue('TOKEN', serialize($token->convertToArray()));

		(new SourceRepository(new OrmConverter()))->save($this->source);
	}

	private function getFromConfig(): ?Token
	{
		$config = $this->source->getConfig();
		if ($config === null)
		{
			return null;
		}

		$tokenArray = $config->getValue('TOKEN');
		if (!$tokenArray)
		{
			return null;
		}

		if (!CheckSerializedData($tokenArray))
		{
			return null;
		}

		$token = Token::makeFromArray(unserialize($tokenArray, ['allowed_classes' => false]));
		if(!$token)
		{
			return null;
		}

		if ($token->getToken() === '')
		{
			return null;
		}

		if ($token->getExpiry() <= time() + self::SAFE_BUFFER_TIME_SECONDS)
		{
			$token = null;
		}

		return $token;
	}

	private function requestNewToken(): ?Token
	{
		$result = $this->performRequest('osmgateway.token.get');
		if (!$result->isSuccess())
		{
			$this->checkLicenseIssueByResult($result);
			return null;
		}

		$tokenData = $result->getData();
		if (
			!isset($tokenData['token'])
			|| !isset($tokenData['expire'])
		)
		{
			LoggerService::getInstance()->log(
				LoggerService\LogLevel::ERROR,
				print_r($result, true),
				LoggerService\EventType::SOURCE_OSM_TREQUESTER_TOKEN_ERROR
			);

			return null;
		}

		return new Token(
			(string)$tokenData['token'],
			(int)$tokenData['expire']
		);
	}

	protected function getServiceUrl(): string
	{
		$serviceUrl = $this->source->getOsmServiceUrl();

		return $serviceUrl ?? '';
	}

	public function getHttpClientParameters(): array
	{
		return [
			'socketTimeout' => 5,
			'streamTimeout' => 10,
			'headers' => [
				'Bx-Location-Osm-Host' => $this->source->getOsmHostName()
			]
		];
	}

	private function checkLicenseIssueByResult(Result $result): void
	{
		$licenseIssueErrorCodes = [
			self::ERROR_LICENSE_NOT_FOUND,
			self::ERROR_WRONG_SIGN,
			self::ERROR_LICENSE_DEMO,
			self::ERROR_LICENSE_NOT_ACTIVE,
		];

		$errors = $result->getErrors();
		foreach ($errors as $error)
		{
			if (in_array($error->getCode(), $licenseIssueErrorCodes, true))
			{
				$this->cacheManager->set(self::getCacheId(), true);
			}
		}
	}

	public function hasLicenseIssues(): bool
	{
		if ($this->cacheManager->read(self::CACHE_TTL, self::getCacheId(), self::CACHE_TABLE))
		{
			return (bool)$this->cacheManager->get(self::getCacheId());
		}

		return false;
	}

	private static function getCacheId(): string
	{
		return md5(serialize([
			'BX_TYPE' => Client::getPortalType(),
			'BX_LICENCE' => Client::getLicenseCode(),
		]));
	}
}
