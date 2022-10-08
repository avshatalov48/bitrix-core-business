<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\OrmConverter;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Infrastructure\Service\LoggerService;
use Bitrix\Location\Repository\SourceRepository;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Service\MicroService\BaseSender;
use Bitrix\Main\Web\Json;

/**
 * Class TokenRequester
 * @package Bitrix\Location\Source\Osm
 * @internal
 */
final class TokenRequester extends BaseSender
{
	/** @var int */
	private const SAFE_BUFFER_TIME_SECONDS = 60;

	/** @var OsmSource */
	private $source;

	/**
	 * @param OsmSource $source
	 * @return TokenRequester
	 */
	public function setSource(OsmSource $source): TokenRequester
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return Token|null
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getToken(): ?Token
	{
		if ($this->source === null)
		{
			throw new RuntimeException('Source is not specified');
		}

		$token = $this->getFromConfig();
		if ($token)
		{
			return $token;
		}

		$token = $this->requestNewToken();
		if ($token)
		{
			$this->updateConfigToken($token);
		}

		return $token;
	}

	/**
	 * @param Token $token
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function updateConfigToken(Token $token): void
	{
		$config = $this->source->getConfig() ?? new Config();

		$config->setValue('TOKEN', serialize($token->convertToArray()));

		(new SourceRepository(new OrmConverter()))->save($this->source);
	}

	/**
	 * @return Token|null
	 */
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

	/**
	 * @return Token|null
	 * @throws ArgumentException
	 */
	private function requestNewToken(): ?Token
	{
		$result = $this->performRequest('osmgateway.token.get');

		if (!$result->isSuccess())
		{
			return null;
		}

		$tokenData = $result->getData();

		if (!$tokenData || !isset($tokenData['token']) || !isset($tokenData['expire']))
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

	/**
	 * @return string
	 */
	protected function getServiceUrl(): string
	{
		$serviceUrl = $this->source->getOsmServiceUrl();

		return $serviceUrl ?? '';
	}

	/**
	 * @return array
	 */
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
}
