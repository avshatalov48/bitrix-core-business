<?php
namespace Bitrix\UI;

use Bitrix\ImBot\Bot\Network;
use Bitrix\ImBot\Bot\Support24;
use Bitrix\ImBot\Bot\SupportBox;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Main\ModuleManager;
use Bitrix\ImBot\Bot\Partner24;
use Bitrix\Bitrix24;

/**
 * Class InfoHelper
 * @package Bitrix\UI
 * @deprecated use Bitrix\UI\FeaturePromoter\Slider
 */
class InfoHelper
{
	public static function getInitParams(?string $currentUrl = null)
	{
		return [
			'frameUrlTemplate' => self::getUrl('/widget2/show/code/', $currentUrl),
			'trialableFeatureList' => self::getTrialableFeatureList(),
			'demoStatus' => self::getDemoStatus(),
			'availableDomainList' => Util::listDomain(),
		];
	}

	public static function getUrl(string $url = "/widget2/show/code/", ?string $currentUrl = null, bool $byLang = false)
	{
		$notifyUrl = Util::getHelpdeskUrl($byLang) . $url;
		$parameters = self::getParameters($currentUrl);

		return \CHTTP::urlAddParams($notifyUrl, $parameters, array("encode" => true));
	}

	public static function getParameters(?string $currentUrl = null): array
	{
		global $APPLICATION;

		$currentUser = CurrentUser::get();
		$isBitrix24Cloud = Loader::includeModule('bitrix24');
		$application = \Bitrix\Main\HttpApplication::getInstance();
		$host = self::getHostName();
		$userId = $currentUser->getId();
		$parameters = [
			'url' => $currentUrl ?? 'https://' . $_SERVER['HTTP_HOST'] . $APPLICATION->GetCurPageParam(),
			'is_admin' => ($isBitrix24Cloud && \CBitrix24::isPortalAdmin($userId))
			|| (!$isBitrix24Cloud && $currentUser->isAdmin()) ? 1 : 0,
			'is_integrator' => (int)($isBitrix24Cloud && \CBitrix24::isIntegrator($userId)),
			'tariff' => Option::get('main', '~controller_group_name', ''),
			'is_cloud' => $isBitrix24Cloud ? '1' : '0',
			'portal_date_register' => $isBitrix24Cloud ? Option::get('main', '~controller_date_create', '') : '',
			'host' => $host,
			'languageId' => LANGUAGE_ID,
			'user_id' => $userId,
			'user_email' => $currentUser->getEmail(),
			'user_name' => $currentUser->getFirstName(),
			'user_last_name' => $currentUser->getLastName(),
		];

		if (Loader::includeModule('intranet'))
		{
			$parameters['user_date_register'] = \Bitrix\Intranet\CurrentUser::get()->getDateRegister()?->getTimestamp();

			if (method_exists(\Bitrix\Intranet\User::class, 'getUserRole'))
			{
				$parameters['user_type'] = (new \Bitrix\Intranet\User())->getUserRole()->value;
			}
		}

		if (Loader::includeModule('imbot'))
		{
			$parameters['support_partner_code'] = Partner24::getBotCode();
			$partnerName = Partner24::getPartnerName();
			$parameters['support_partner_name'] = $partnerName;
			$supportBotId = 0;

			if (
				class_exists('\\Bitrix\\ImBot\\Bot\\Support24')
				&& (Support24::getSupportLevel() === Network::SUPPORT_LEVEL_PAID)
				&& Support24::isEnabled()
			)
			{
				$supportBotId = (int)Support24::getBotId();
			}
			elseif (
				method_exists('\\Bitrix\\ImBot\\Bot\\SupportBox', 'isEnabled')
				&& SupportBox::isEnabled()
			)
			{
				$supportBotId = SupportBox::getBotId();
			}

			$parameters['support_bot'] = $supportBotId;
		}

		if (!$isBitrix24Cloud)
		{
			$parameters['head'] = md5("BITRIX" . $application->getLicense()->getKey() . 'LICENCE');
			$parameters['key'] = md5($host . $userId . $parameters['head']);
		}
		else
		{
			$parameters['key'] = \CBitrix24::requestSign($host . $userId);
		}

		$method = "\\" . __METHOD__;

		$event =  (new Event('ui', $method, $parameters));
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if (($eventParameters = $eventResult->getParameters()) && is_array($eventParameters))
			{
				$parameters = array_merge($parameters, $eventParameters);
			}
		}

		return $parameters;
	}

	private static function getTrialableFeatureList(): array
	{
		if (
			Loader::includeModule('bitrix24')
			&& method_exists(Bitrix24\Feature::class, 'getTrialableFeatureList')
		)
		{
			return Bitrix24\Feature::getTrialableFeatureList();
		}

		return [];
	}

	private static function getDemoStatus(): string
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
			else
			{
				return 'EXPIRED';
			}
		}

		return 'UNKNOWN';
	}

	private static function getHostName()
	{
		if (ModuleManager::isModuleInstalled("bitrix24") && defined('BX24_HOST_NAME'))
		{
			return BX24_HOST_NAME;
		}

		$site = \Bitrix\Main\SiteTable::getList(array(
			'filter' => defined('SITE_ID') ? array('=LID' => SITE_ID) : array(),
			'order'  => array('ACTIVE' => 'DESC', 'DEF' => 'DESC', 'SORT' => 'ASC'),
			'select' => array('SERVER_NAME'),
			'cache'	 => array('ttl' => 86400)
		))->fetch();

		return $site['SERVER_NAME'] ?: Option::get('main', 'server_name', '');
	}
}

