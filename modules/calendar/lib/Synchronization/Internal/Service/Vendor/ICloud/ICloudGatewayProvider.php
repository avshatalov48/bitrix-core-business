<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Integration\Dav\Service\HttpClientProxyConfigurator;
use Bitrix\Calendar\Integration\Dav\WebDavMethodProvider;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway\ICloudEventGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway\ICloudSectionGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway\RequestDataBuilder;
use Bitrix\Main\Web\HttpClient;

class ICloudGatewayProvider
{
	private const USER_AGENT = 'Bitrix CalDAV/CardDAV/GroupDAV client';

	/**
	 * @var ICloudSectionGateway[]
	 */
	private array $sectionGateways = [];

	/**
	 * @var ICloudEventGateway[]
	 */
	private array $eventGateways = [];

	public function __construct(
		private readonly HttpClientProxyConfigurator $proxyConfigurator,
		private readonly WebDavMethodProvider $davMethodProvider,
		private readonly RequestDataBuilder $requestDataBuilder,
		private readonly RequestLogger $logger,
	)
	{
	}

	public function getSectionGateway(int $userId, Server $server): ICloudSectionGateway
	{
		if (!isset($this->sectionGateways[$userId]))
		{
			$client = $this->buildHttpClient($server->getHost(), $server->getUserName(), $server->getPassword());

			$this->sectionGateways[$userId] = new ICloudSectionGateway(
				$server->getBasePath(),
				$this->davMethodProvider,
				$this->requestDataBuilder,
				$client,
				$this->logger,
				$server->getOriginPath(),
			);
		}

		return $this->sectionGateways[$userId];
	}

	public function getEventGateway(int $userId, Server $server): ICloudEventGateway
	{
		if (!isset($this->eventGateways[$userId]))
		{
			$client = $this->buildHttpClient($server->getHost(), $server->getUserName(), $server->getPassword());

			$this->eventGateways[$userId] = new ICloudEventGateway(
				$this->davMethodProvider,
				$this->requestDataBuilder,
				$client,
				$this->logger,
				$server->getOriginPath(),
			);
		}

		return $this->eventGateways[$userId];
	}

	private function buildHttpClient(string $host, ?string $username, ?string $password): HttpClient
	{
		$client =
			(new HttpClient())
				->setHeader('Host', $host)
				->setHeader('User-Agent', self::USER_AGENT)
				->setCharset('UTF-8')
				->setAuthorization($username, $password)
				// ssl verification blocks requests to iCloud when using curl
				->disableSslVerification()
		;

		return $this->proxyConfigurator->apply($client);
	}
}
