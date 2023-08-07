<?php
namespace Bitrix\Calendar\Integration;

use Bitrix\Main;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Config\Option;

/**
 * Class Bitrix24Manager
 *
 * Required in Bitrix24 context. Provides information about the license and supported features.
 * @package Bitrix\Calendar\Integration
 */
class Bitrix24Manager
{
	const ICAL_EVENT_LIMIT_OPTION = "event_with_email_guest_amount";
	const EVENT_AMOUNT = "event_with_planner_amount";

	//region Members
	/** @var bool|null */
	private static ?bool $hasPurchasedLicense = null;
	/** @var bool|null */
	private static ?bool $hasDemoLicense = null;
	/** @var bool|null */
	private static ?bool $hasNfrLicense = null;
	/** @var bool|null */
	private static ?bool $hasPurchasedUsers = null;
	/** @var bool|null */
	private static ?bool $hasPurchasedDiskSpace = null;
	/** @var bool|null */
	private static ?bool $isPaidAccount = null;
	/** @var bool|null */
	private static ?bool $enableRestBizProc = null;
	//endregion

	//region Methods
	/**
	 * Check if current manager enabled.
	 * @return bool
	 */
	public static function isEnabled()
	{
		return ModuleManager::isModuleInstalled('bitrix24');
	}

	/**
	 * Check if portal has paid license, paid for extra users, paid for disk space or SIP features.
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isPaidAccount()
	{
		if (self::$isPaidAccount !== null)
		{
			return self::$isPaidAccount;
		}

		self::$isPaidAccount = self::hasPurchasedLicense()
			|| self::hasPurchasedUsers()
			|| self::hasPurchasedDiskSpace();

		if (!self::$isPaidAccount)
		{
			//Phone number check: voximplant::account_payed
			//SIP connector check: main::~PARAM_PHONE_SIP
			self::$isPaidAccount = \COption::GetOptionString('voximplant', 'account_payed', 'N') === 'Y'
				|| \COption::GetOptionString('main', '~PARAM_PHONE_SIP', 'N') === 'Y';
		}

		return self::$isPaidAccount;
	}
	/**
	 * Check if portal has paid license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedLicense()
	{
		if (self::$hasPurchasedLicense !== null)
		{
			return self::$hasPurchasedLicense;
		}

		if (
			!(ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24'))
			&& method_exists('CBitrix24', 'IsLicensePaid')
		)
		{
			return (self::$hasPurchasedLicense = false);
		}

		return (self::$hasPurchasedLicense = \CBitrix24::IsLicensePaid());
	}
	/**
	 *  Check if portal has trial license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasDemoLicense()
	{
		if(self::$hasDemoLicense !== null)
		{
			return self::$hasDemoLicense;
		}

		if (
			!(ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24'))
			&& method_exists('CBitrix24', 'IsDemoLicense')
		)
		{
			return (self::$hasDemoLicense = false);
		}

		return (self::$hasDemoLicense = \CBitrix24::IsDemoLicense());
	}
	/**
	 * Check if portal has NFR license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasNfrLicense()
	{
		if (self::$hasNfrLicense !== null)
		{
			return self::$hasNfrLicense;
		}

		if (
			!(ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24'))
			&& method_exists('CBitrix24', 'IsNfrLicense')
		)
		{
			return (self::$hasNfrLicense = false);
		}

		return (self::$hasNfrLicense = \CBitrix24::IsNfrLicense());
	}
	/**
	 * Check if portal has paid for extra users.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedUsers()
	{
		if(self::$hasPurchasedUsers !== null)
		{
			return self::$hasPurchasedUsers;
		}

		if(
			!(ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24'))
			&& method_exists('CBitrix24', 'IsExtraUsers')
		)
		{
			return (self::$hasPurchasedUsers = false);
		}

		return (self::$hasPurchasedUsers = \CBitrix24::IsExtraUsers());
	}
	/**
	 * Check if portal has paid for extra disk space.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedDiskSpace()
	{
		if(self::$hasPurchasedDiskSpace !== null)
		{
			return self::$hasPurchasedDiskSpace;
		}

		if(
			!(ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24'))
			&& method_exists('CBitrix24', 'IsExtraDiskSpace')
		)
		{
			return (self::$hasPurchasedDiskSpace = false);
		}

		return (self::$hasPurchasedDiskSpace = \CBitrix24::IsExtraDiskSpace());
	}

	/**
	 * Check if Business Processes are enabled for REST API.
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isRestBizProcEnabled()
	{
		return self::$enableRestBizProc ?? (self::$enableRestBizProc = (self::hasPurchasedLicense() || self::hasNfrLicense() || self::hasDemoLicense()));
	}

	/**
	 * @param array $params
	 * @return array|null
	 * @throws LoaderException
	 */
	public static function prepareStubInfo(array $params)
	{
		if (
			ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
			&& method_exists('CBitrix24', 'prepareStubInfo')
		)
		{
			$title = $params['TITLE'] ?? '';
			$content = $params['CONTENT'] ?? '';

			$replacements = isset($params['REPLACEMENTS']) && is_array($params['REPLACEMENTS'])
				? $params['REPLACEMENTS'] : array();

			if (!empty($replacements))
			{
				$search = array_keys($replacements);
				$replace = array_values($replacements);

				$title = str_replace($search, $replace, $title);
				$content = str_replace($search, $replace, $content);
			}

			$licenseAllButtonClass = ($params['GLOBAL_SEARCH']? 'ui-btn ui-btn-xs ui-btn-light-border' : 'success');
			$licenseDemoButtonClass = ($params['GLOBAL_SEARCH']? 'ui-btn ui-btn-xs ui-btn-light' : '');

			$options = [];
			if (isset($params['ANALYTICS_LABEL']) && $params['ANALYTICS_LABEL'] != '')
			{
				$options['ANALYTICS_LABEL'] = $params['ANALYTICS_LABEL'];
			}

			return \CBitrix24::prepareStubInfo(
				$title,
				$content,
				array(
					array('ID' => \CBitrix24::BUTTON_LICENSE_ALL, 'CLASS_NAME' => $licenseAllButtonClass),
					array('ID' => \CBitrix24::BUTTON_LICENSE_DEMO, 'CLASS_NAME' => $licenseDemoButtonClass),
				),
				$options
			);
		}

		return null;
	}

	/**
	 * Prepare JavaScript for license purchase information.
	 * @param array $params Popup params.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function prepareLicenseInfoPopupScript(array $params)
	{
		if (
			ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
			&& method_exists('CBitrix24', 'initLicenseInfoPopupJS')
		)
		{
			\CBitrix24::initLicenseInfoPopupJS();

			$popupID = isset($params['ID']) ? \CUtil::JSEscape($params['ID']) : '';
			$title = isset($params['TITLE']) ? \CUtil::JSEscape($params['TITLE']) : '';
			$content = '';
			if(isset($params['CONTENT']))
			{
				$content = \CUtil::JSEscape(
					str_replace(
						'#TF_PRICE#',
						\CBitrix24::getLicensePrice('tf'),
						$params['CONTENT']
					)
				);
			}

			return "if(typeof(B24.licenseInfoPopup) !== 'undefined'){ B24.licenseInfoPopup.show('{$popupID}', '{$title}', '{$content}'); }";
		}

		return '';
	}
	/**
	 * Prepare JavaScript for opening purchaise information by info-helper slider
	 * @param array $params Info-helper params.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function prepareLicenseInfoHelperScript(array $params)
	{
		$script = '';

		if (
			(is_string($params['ID']) && $params['ID'] !== '')
			&& ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
			&& ModuleManager::isModuleInstalled('ui')
			&& Loader::includeModule('ui')
		)
		{
			$script = 'if (top.hasOwnProperty("BX") && top.BX !== null && typeof(top.BX) === "function"'.
				' && top.BX.hasOwnProperty("UI") && top.BX.UI !== null && typeof(top.BX.UI) === "object"'.
				' && top.BX.UI.hasOwnProperty("InfoHelper") && top.BX.UI.InfoHelper !== null'.
				' && typeof(top.BX.UI.InfoHelper) === "object" && top.BX.UI.InfoHelper.hasOwnProperty("show")'.
				' && typeof(top.BX.UI.InfoHelper.show) === "function"){top.BX.UI.InfoHelper.show("'.
				\CUtil::JSEscape($params['ID']).'");}';
		}

		return $script;
	}

	/**
	 * Get URL for "Choose a Bitrix24 plan" page.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function getLicenseListPageUrl()
	{
		if (
			ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
		)
		{
			return \CBitrix24::PATH_LICENSE_ALL;
		}

		return '';
	}
	/**
	 * Get URL for "Free 30-day trial" page.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function getDemoLicensePageUrl()
	{
		if (
			ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
		)
		{
			return \CBitrix24::PATH_LICENSE_DEMO;
		}

		return '';
	}

	/**
	 * Check if specified feature is enabled
	 * @param string $releaseName Name of release.
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isFeatureEnabled($releaseName)
	{
		if (!(ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24')))
		{
			return true;
		}

		return \Bitrix\Bitrix24\Feature::isFeatureEnabled($releaseName);
	}

	/**
	 * Get variable value.
	 * @param string $name Name of variable
	 * @return mixed|null
	 * @throws LoaderException
	 */
	public static function getVariable($name)
	{
		if (!(ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24')))
		{
			return null;
		}

		return \Bitrix\Bitrix24\Feature::getVariable($name);
	}

	/**
	 * Check if "planner" feature is enabled
	 * @return bool
	 */
	public static function isPlannerFeatureEnabled()
	{
		$eventsLimit = self::getEventWithPlannerLimit();

		return $eventsLimit === -1 || self::getEventsAmount() <= $eventsLimit;
	}

	/**
	 * Check if specified feature is enabled
	 * @return int
	 * @throws LoaderException
	 */
	public static function getEventWithPlannerLimit()
	{
		$limit = self::getVariable('calendar_events_with_planner');
		if (is_null($limit))
		{
			$limit = -1;
		}
		return $limit;
	}

	/**
	 * Returns events amount
	 * @return int
	 */
	public static function getEventsAmount()
	{
		return Option::get('calendar', self::EVENT_AMOUNT, 0);
	}

	/**
	 * Sets events amount
	 * @param int $value amount of events
	 * @return void
	 * @throws ArgumentOutOfRangeException
	 */
	public static function setEventsAmount($value = 0)
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			Option::set('calendar', self::EVENT_AMOUNT, $value);
		}
	}

	/**
	 * Increase events amount
	 * @return void
	 * @throws ArgumentOutOfRangeException
	 */
	public static function increaseEventsAmount()
	{
		self::setEventsAmount(self::getEventsAmount() + 1);
	}

	/**
	 * Returns limitations for bitrix 24 for unpaid license
	 * @return int (-1 if no limitation)
	 * @throws LoaderException
	 */
	public static function getEventWithEmailGuestLimit()
	{
		$limit = self::getVariable('calendar_events_with_email_guests');
		if (is_null($limit))
		{
			$limit = (
					ModuleManager::isModuleInstalled('bitrix24')
					&& Loader::includeModule('bitrix24')
					&& !\CBitrix24::IsLicensePaid()
					&& !\CBitrix24::IsNfrLicense()
					&& !\CBitrix24::IsDemoLicense()
				)
				? 10
				: -1;
		}

		return $limit;
	}

	public static function getCountEventWithEmailGuestAmount()
	{
		return \COption::GetOptionInt('calendar', self::ICAL_EVENT_LIMIT_OPTION, 0);
	}

	public static function setCountEventWithEmailGuestAmount($value = 0)
	{
		return \COption::SetOptionInt('calendar', self::ICAL_EVENT_LIMIT_OPTION, $value);
	}

	public static function increaseEventWithEmailGuestAmount()
	{
		return \COption::SetOptionInt(
			'calendar',
			self::ICAL_EVENT_LIMIT_OPTION,
			self::getCountEventWithEmailGuestAmount() + 1
		);
	}

	public static function isEventWithEmailGuestAllowed()
	{
		$limit = self::getEventWithEmailGuestLimit();
		return $limit === -1 || self::getCountEventWithEmailGuestAmount() < $limit;
	}
}
?>