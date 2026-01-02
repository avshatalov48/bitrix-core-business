<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365;

use Bitrix\Calendar\Internal\Integration\Socialservices\Auth\Office365AuthService;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\Office365EventGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\Office365PushGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\Office365SectionGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\RequestParametersBuilder;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

class Office365GatewayProvider
{
	/**
	 * @var Office365SectionGateway[]
	 */
	private array $sectionGateways = [];

	/**
	 * @var Office365EventGateway[]
	 */
	private array $eventGateways = [];

	/**
	 * @var Office365PushGateway[]
	 */
	private array $pushGateways = [];

	public function __construct(
		private readonly Office365AuthService $authService,
		private readonly RequestParametersBuilder $requestParametersBuilder
	)
	{
	}

	/**
	 * @throws AuthorizationException
	 */
	public function getSectionGateway(int $userId): Office365SectionGateway
	{
		if (!isset($this->sectionGateways[$userId]))
		{
			$httpClient = $this->buildHttpClient($userId);

			$this->sectionGateways[$userId] = new Office365SectionGateway($httpClient);
		}

		return $this->sectionGateways[$userId];
	}

	/**
	 * @throws AuthorizationException
	 */
	public function getEventGateway(int $userId): Office365EventGateway
	{
		if (!isset($this->eventGateways[$userId]))
		{
			$httpClient = $this->buildHttpClient($userId);

			$this->eventGateways[$userId] = new Office365EventGateway($httpClient, $this->requestParametersBuilder);
		}

		return $this->eventGateways[$userId];
	}

	/**
	 * @throws AuthorizationException
	 */
	public function getPushGateway(int $userId): Office365PushGateway
	{
		if (!isset($this->pushGateways[$userId]))
		{
			$httpClient = $this->buildHttpClient($userId);

			$this->pushGateways[$userId] = new Office365PushGateway($httpClient);
		}

		return $this->pushGateways[$userId];
	}

	/**
	 * @throws AuthorizationException
	 */
	private function buildHttpClient(int $userId): HttpClient
	{
		$httpClient = new HttpClient();

		try
		{
			$authEntity = $this->authService->prepareAuthEntity($userId);

			if ($authEntity->GetAccessToken())
			{
				$httpClient->setHeader('Authorization', 'Bearer ' . $authEntity->getToken());
				$httpClient->setHeader('Content-Type', 'application/json');
				$httpClient->setHeader('Prefer', 'odata.maxpagesize=' . $this->getMaxPageSize());
				$httpClient->setRedirect(false);
			}
			elseif ($checkUser = $authEntity->GetCurrentUser())
			{
				if (!empty($checkUser['access_token']))
				{
					$httpClient->setHeader('Authorization', 'Bearer ' . $checkUser['access_token']);
					$httpClient->setHeader('Content-Type', 'application/json');
					$httpClient->setHeader('Prefer', 'odata.maxpagesize=' . $this->getMaxPageSize());
					$httpClient->setRedirect(false);
				}
				else
				{
					throw new AuthorizationException('Access token not received', 401);
				}
			}

			return $httpClient;
		}
		catch (AuthorizationException $e)
		{
			throw $e;
		}
		catch (LoaderException|SystemException)
		{
			throw new AuthorizationException('Office365 account not found', 403);
		}
	}

	protected function getMaxPageSize(): ?int
	{
		return 100;
	}
}
