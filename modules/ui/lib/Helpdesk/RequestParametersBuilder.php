<?php

namespace Bitrix\UI\Helpdesk;

use Bitrix\ImBot\Bot\Network;
use Bitrix\ImBot\Bot\Partner24;
use Bitrix\ImBot\Bot\Support24;
use Bitrix\ImBot\Bot\SupportBox;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Intranet;
use Bitrix\Bitrix24;

class RequestParametersBuilder
{
	private CurrentUser $currentUser;
	/**
	 * @var array<string, int|string>
	 */
	private array $parameters = [];
	private bool $isCloud;

	public function __construct()
	{
		$this->currentUser = CurrentUser::get();
		$this->isCloud = Loader::includeModule('bitrix24');
	}

	public function build(): array
	{
		$this->buildPortalInformation();
		$this->buildUserCharacteristics();
		$this->buildSupportConfiguration();
		$this->buildKeyConfiguration();
		$this->buildExternalParameters();
		$this->buildHeadInformation();

		return $this->parameters;
	}

	private function buildSupportConfiguration(): void
	{
		if (!Loader::includeModule('imbot'))
		{
			return;
		}

		$this->parameters += [
			'support_partner_code' => Partner24::getBotCode(),
			'support_partner_name' => Partner24::getPartnerName(),
		];
		$supportBotId = 0;

		if (Support24::getSupportLevel() === Network::SUPPORT_LEVEL_PAID && Support24::isEnabled())
		{
			$supportBotId = Support24::getBotId();
		}
		elseif (SupportBox::isEnabled())
		{
			$supportBotId = SupportBox::getBotId();
		}

		$this->parameters['support_bot'] = $supportBotId;
	}

	private function buildExternalParameters(): void
	{
		$method = '\\' . __METHOD__;
		$event =  (new Event('ui', $method, $this->parameters));
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if (($eventParameters = $eventResult->getParameters()) && is_array($eventParameters))
			{
				$this->parameters = array_merge($this->parameters, $eventParameters);
			}
		}
	}

	private function buildKeyConfiguration(): void
	{
		if ($this->isCloud)
		{
			$this->parameters['key'] = \CBitrix24::requestSign($this->getHostName() . $this->currentUser->getId());
		}
		else
		{
			$this->parameters['head'] = md5('BITRIX' . Application::getInstance()->getLicense()->getKey() . 'LICENCE');
			$this->parameters['key'] = md5($this->getHostName() . $this->currentUser->getId() . $this->parameters['head']);
		}
	}

	private function buildUserCharacteristics(): void
	{
		$userId = $this->currentUser->getId();
		$this->parameters += [
			'is_admin' => ($this->isCloud && \CBitrix24::isPortalAdmin($userId))
			|| (!$this->isCloud && $this->currentUser->isAdmin()) ? 1 : 0,
			'is_integrator' => (int)($this->isCloud && \CBitrix24::isIntegrator($userId)),
			'user_id' => $userId,
			'user_email' => $this->currentUser->getEmail(),
			'user_name' => $this->currentUser->getFirstName(),
			'user_last_name' => $this->currentUser->getLastName(),
		];

		if (Loader::includeModule('intranet'))
		{
			$this->parameters['user_date_register'] = \Bitrix\Intranet\CurrentUser::get()->getDateRegister()?->getTimestamp();

			if (method_exists(Intranet\User::class, 'getUserRole'))
			{
				$this->parameters['user_type'] = (new Intranet\User())->getUserRole()->value;
			}
		}
	}

	private function buildPortalInformation(): void
	{
		$this->parameters += [
			'tariff' => Option::get('main', '~controller_group_name', ''),
			'is_cloud' => $this->isCloud ? '1' : '0',
			'host' => $this->getHostName(),
			'languageId' => LANGUAGE_ID,
			'demoStatus' => $this->getDemoStatus(),
			'isAutoPay' => $this->isCloud && \CBitrix24::isAutoPayLicense(),
		];

		if ($this->isCloud)
		{
			$this->parameters += [
				'portal_date_register' => Option::get('main', '~controller_date_create', ''),
				'canAllUsersBuyTariff' => \CBitrix24::canAllBuyLicense(),
			];
		}
	}

	private function buildHeadInformation(): void
	{
		if (!Loader::includeModule('intranet'))
		{
			return;
		}

		$currentUser = Intranet\CurrentUser::get();
		$heads = \CIntranetUtils::GetDepartmentManager($currentUser->getDepartmentIds(), $currentUser->getId(), true);

		if (empty($heads))
		{
			$this->parameters['isSubordinate'] = 0;

			return;
		}

		foreach ($heads as $head)
		{
			if (!empty($head) && isset($head['ID']))
			{
				$this->parameters += [
					'tools' => [
						'isSubordinate' => 1,
						'head' => [
							'id' => (int)$head['ID'],
							'name' => \CUser::FormatName(\CSite::GetNameFormat(false), $head),
							'avatar' => $this->prepareUserPhoto($head),
						],
					],
				];

				return;
			}
		}
	}

	private function prepareUserPhoto(array $headData): ?string
	{
		return $headData['PERSONAL_PHOTO'] ? (string)Intranet\Component\UserProfile::getUserPhoto($headData['PERSONAL_PHOTO']) : '';
	}

	private function getHostName(): ?string
	{
		if ($this->isCloud && defined('BX24_HOST_NAME'))
		{
			return BX24_HOST_NAME;
		}

		return Context::getCurrent()?->getRequest()->getHttpHost();
	}

	private function getDemoStatus(): string
	{
		if (Loader::includeModule('bitrix24'))
		{
			if (\CBitrix24::IsDemoLicense())
			{
				return 'ACTIVE';
			}

			if (Bitrix24\Feature::isEditionTrialable('demo'))
			{
				return 'AVAILABLE';
			}

			return 'EXPIRED';
		}

		return 'UNKNOWN';
	}
}