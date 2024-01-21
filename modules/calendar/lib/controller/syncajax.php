<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\ICloud;
use Bitrix\Calendar\Util;
use Bitrix\Main\Error;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sync;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Calendar\Core\Oauth;

Loc::loadMessages(__FILE__);

class SyncAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
		return [
			'handleMobileAuth' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class,
				],
			],
		];
	}

	public function getSyncInfoAction()
	{
		$params = [];
		$request = $this->getRequest();
		$params['type'] = $request->getPost('type');
		$params['userId'] = \CCalendar::getCurUserId();

		return \CCalendarSync::GetSyncInfo($params);
	}

	public function removeConnectionAction($connectionId, $removeCalendars)
	{
		\CCalendar::setOwnerId(\CCalendar::getCurUserId());
		\CCalendar::RemoveConnection(['id' => (int)$connectionId, 'del_calendars' => $removeCalendars === 'Y']);

		return true;
	}

	/**
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function addConnectionAction(): void
	{
		$request = $this->getRequest();
		$params['user_id'] = \CCalendar::getCurUserId();
		$params['user_name'] = $request['userName'];
		$params['name'] = $request['name'];
		$params['link'] = $request['server'];
		$params['pass'] = $request['pass'];

		foreach ($params as $parameter)
		{
			if ($parameter === '')
			{
				$this->addError(new Error(Loc::getMessage('EC_CALDAV_URL_ERROR'), 'incorrect_parameters'));
				break;
			}
		}

		if (Loader::IncludeModule('dav'))
		{
			$res = \CCalendar::AddConnection($params);

			if ($res === true)
			{
				\CDavGroupdavClientCalendar::DataSync("user", $params['user_id']);
			}
			else
			{
				$this->addError(new Error($res, 'incorrect_parameters'));
			}
		}
	}

	/**
	 * @return array
	 * @throws LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function createGoogleConnectionAction(): array
	{
		$response = [
			'status' => 'error',
			'message' => 'Could not finish sync.',
		];

		if (!\CCalendar::isGoogleApiEnabled())
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_GOOGLE_API_REQUIRED'), 'google_api_required'));

			return $response;
		}
		if (!Loader::includeModule('dav'))
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'), 'dav_required'));

			return $response;
		}

		return (new Google\StartSynchronizationManager(\CCalendar::GetCurUserId()))->synchronize();
	}

	/**
	 * @return string[]
	 * @throws LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createOffice365ConnectionAction(): array
	{
		if (!Loader::includeModule('dav'))
		{
			return [
				'status' => 'error',
				'message' => 'Module dav is required',
			];
		}
		if (!Loader::includeModule('socialservices'))
		{
			return [
				'status' => 'error',
				'message' => 'Module socialservices is required',
			];
		}

		$owner = Helper::getRole(\CCalendar::GetUserId(), User::TYPE);

		return (new Sync\Office365\StartSyncController($owner))->synchronize();
	}

	/**
	 * @param string|null $appleId
	 * @param string|null $appPassword
	 * @return array|string[]
	 * @throws LoaderException
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createIcloudConnectionAction(?string $appleId, ?string $appPassword)
	{
		$appleId = trim($appleId);
		$appPassword = trim($appPassword);

		if (!Loader::includeModule('dav'))
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED')));

			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'),
			];
		}
		$typeModel = TypeModel::createFromXmlId(User::TYPE);
		$accessController = new TypeAccessController(\CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, []))
		{
			$this->addError(new Error('Access Denied'));

			return [
				'status' => 'error',
				'message' => 'Access Denied',
			];
		}
		if (!preg_match("/[a-z]{4}-[a-z]{4}-[a-z]{4}-[a-z]{4}/", $appPassword))
		{
			$this->addError(new Error('Incorrect app password'));

			return [
				'status' => 'incorrect_app_pass',
				'message' => 'Incorrect app password'
			];
		}

		$connectionId = (new Icloud\VendorSyncManager())->initConnection($appleId, $appPassword);
		if (!$connectionId)
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCALAX_ICLOUD_WRONG_AUTH')));

			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCALAX_ICLOUD_WRONG_AUTH'),
			];
		}

		return [
			'status' => 'success',
			'connectionId' => $connectionId
		];
	}

	public function syncIcloudConnectionAction($connectionId)
	{
		if (!Loader::includeModule('dav'))
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED')));

			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'),
			];
		}

		$result = (new Icloud\VendorSyncManager())->syncIcloudConnection($connectionId);

		if ($result['status'] === 'error' && $result['message'])
		{
			$this->addError(new Error($result['message']));
		}

		return $result;
	}

	public function updateConnectionAction()
	{
		$params = [];
		$request = $this->getRequest();
		$params['type'] = $request->getPost('type');
		$params['userId'] = \CCalendar::getCurUserId();
		$requestUid = $request->getPost('requestUid');
		if (!empty($requestUid))
		{
			Util::setRequestUid($requestUid);
		}

		\CCalendarSync::UpdateUserConnections();

		Util::setRequestUid();

		return \CCalendarSync::GetSyncInfo($params);
	}

	public function getAuthLinkAction()
	{
		$type = $this->getRequest()->getPost('type');
		$type = in_array($type, ['slider', 'banner'], true)
			? $type
			: 'banner'
		;
		if (\Bitrix\Main\Loader::includeModule("mobile"))
		{
			return ['link' => \Bitrix\Mobile\Deeplink::getAuthLink("calendar_sync_".$type)];
		}
		return null;
	}

	/**
	 * @param int $connectionId
	 * @param string $removeCalendars
	 *
	 * @return bool
	 */
	public function deactivateConnectionAction(int $connectionId, $removeCalendars = 'N'): bool
	{
		try
		{
			if (!Loader::includeModule('dav'))
			{
				return false;
			}

			return \CCalendarSync::deactivateConnection($connectionId);
		}
		catch (\Exception $e)
		{
		    return false;
		}
	}

	public function getAllSectionsForIcloudAction(int $connectionId)
	{
		return \CCalendarSect::getAllSectionsForVendor($connectionId, [Sync\Icloud\Helper::ACCOUNT_TYPE]);
	}

	public function getAllSectionsForOffice365Action(int $connectionId)
	{
		return \CCalendarSect::getAllSectionsForVendor($connectionId, [Sync\Office365\Helper::ACCOUNT_TYPE]);
	}

	public function getAllSectionsForGoogleAction(int $connectionId)
	{
		return \CCalendarSect::getAllSectionsForVendor($connectionId, Sync\Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE);
	}

	public function clearSuccessfulConnectionNotifierAction(string $accountType)
	{
		\Bitrix\Calendar\Sync\Managers\NotificationManager::clearFinishedSyncNotificationAgent(
			(int)\CCalendar::GetUserId(),
			$accountType
		);
	}

	public function disableIphoneOrMacConnectionAction()
	{
		$userId = \CCalendar::getCurUserId();
		\CUserOptions::DeleteOption('calendar', 'last_sync_iphone', false, $userId);
		\CUserOptions::DeleteOption('calendar', 'last_sync_mac', false, $userId);
	}

	public function disableShowGoogleApplicationRefusedAction()
	{
		CUserOptions::SetOption('calendar', 'showGoogleApplicationRefused', 'N');
	}

	public function getOutlookLinkAction(int $id)
	{
		$result = '';
		$section = SectionTable::query()
			->setSelect(['XML_ID', 'CAL_TYPE', 'NAME', 'OWNER_ID'])
			->where('ID', $id)
			->exec()->fetchObject()
		;

		if ($section)
		{
			$result = \CCalendarSect::GetOutlookLink([
				'ID' => $section->getId(),
				'XML_ID' => $section->getXmlId(),
				'TYPE' => $section->getCalType(),
				'NAME' => $section->getName(),
				'PREFIX' => \CCalendar::GetOwnerName($section->getCalType(), $section->getOwnerId()),
				'LINK_URL' => \CCalendar::GetOuterUrl()
			]);
		}

		return ['result' => $result];
	}

	public function getOauthConnectionLinkAction(string $serviceName): array
	{
		$result = [];

		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			$this->addError(new Error('Access denied', 403));

			return $result;
		}

		$oauthEntity = Oauth\Factory::getInstance()->getByName($serviceName);
		if ($oauthEntity && $url = $oauthEntity->getUrl())
		{
			$result['connectionLink'] = $url;
		}
		else
		{
			$this->addError(new Error('Link not found', 404));
		}

		return $result;
	}

	public function handleMobileAuthAction(string $serviceName, string $hitHash): HttpResponse
	{
		$httpResponse = new HttpResponse();
		$httpResponse->addHeader('Location', 'bitrix24://');

		if (empty($serviceName) || empty($hitHash))
		{
			return $httpResponse;
		}

		if (!$GLOBALS['USER']->LoginHitByHash($hitHash, false, true))
		{
			return $httpResponse;
		}

		HttpApplication::getInstance()->getSession()->set('MOBILE_OAUTH', true);

		$oauthEntity = Oauth\Factory::getInstance()->getByName($serviceName);
		if ($oauthEntity && $url = $oauthEntity->getUrl())
		{
			return $this->redirectTo($url)->setSkipSecurity(true);
		}

		return $httpResponse;
	}
}
