<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Internals\FlagRegistry;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\ICloud;
use Bitrix\Calendar\Sync\Managers\ConnectionManager;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use CCalendar;
use CUserOptions;
use Exception;

Loc::loadMessages(__FILE__);

class SyncAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
		return [];
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
	 * @throws LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws Exception
	 */
	public function createGoogleConnectionAction(): array
	{
		if (!\CCalendar::isGoogleApiEnabled())
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_GOOGLE_API_REQUIRED'), 'google_api_required'));
		}
		if (!Loader::includeModule('dav'))
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'), 'dav_required'));
		}

		$response = [
			'status' => 'error',
			'message' => 'Could not finish sync.'
		];

		$owner = Helper::getRole(\CCalendar::GetUserId(), User::TYPE);
		$pusher = static function ($result) use ($owner)
		{
			Util::addPullEvent(
				'process_sync_connection',
				$owner->getId(),
				(array) $result
			);

			if ($result['stage'] === 'export_finished')
			{
				NotificationManager::addFinishedSyncNotificationAgent(
					$owner->getId(),
					$result['vendorName']
				);
			}
		};

		if(empty($this->getErrors()))
		{
			try
			{
				$manager = new Google\StartSynchronizationManager($owner->getId());
				FlagRegistry::getInstance()->setFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);
				if ($connection = $manager->addStatusHandler($pusher)->start())
				{
					$response = [
						'status' => 'success',
						'message' => 'CONNECTION_CREATED',
						'connectionId' => $connection->getId(),
					];
				}
				FlagRegistry::getInstance()->resetFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);
			}
			catch (BaseException $e)
			{
			}
		}

		return $response;
	}

	public function createOffice365ConnectionAction(): array
	{
		$response = [
			'status' => 'success',
			'message' => 'CONNECTION_CREATED'
		];
		try
		{
			if (!Loader::includeModule('dav'))
			{
				throw new LoaderException('Module dav is required');
			}
			if (!Loader::includeModule('socialservices'))
			{
				throw new LoaderException('Module socialservices is required');
			}

			$owner = Helper::getRole(\CCalendar::GetUserId(), User::TYPE);
			$pusher = static function ($result) use ($owner)
			{
				Util::addPullEvent(
					'process_sync_connection',
					$owner->getId(),
					(array) $result
				);

				if ($result['stage'] === 'events_sync_finished')
				{
					NotificationManager::addFinishedSyncNotificationAgent(
						$owner->getId(),
						$result['vendorName']
					);
				}
			};

			$controller = new Sync\Office365\StartSyncController($owner);
			FlagRegistry::getInstance()->setFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);

			// start process
			if ($connection = $controller->addStatusHandler($pusher)->start())
			{
				$response['connectionId'] = $connection->getId();
			}
			else
			{
				$response['connectionId'] = null;
			}
			FlagRegistry::getInstance()->resetFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);
		}
		catch (LoaderException $e)
		{
			$this->writeToLogException($e);
			$response = [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}
		catch (\Throwable $e)
		{
			$this->writeToLogException($e);
			$response = [
				'status' => 'error',
				'message' => 'Could not finish sync: '.$e->getMessage()
			];
		}

		return $response;
	}

	public function createIcloudConnectionAction($appleId, $appPassword)
	{
		$params['ENTITY_ID'] = \CCalendar::getCurUserId();
		$params['ENTITY_TYPE'] = 'user';
		$params['SERVER_HOST'] = ICloud\Helper::SERVER_PATH;
		$params['SERVER_USERNAME'] = trim($appleId);
		$params['SERVER_PASSWORD'] = trim($appPassword);
		$params['NAME'] = str_replace('#NAME#', $params['SERVER_USERNAME'], ICloud\Helper::CONNECTION_NAME);

		if (!Loader::includeModule('dav'))
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED')));

			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'),
			];
		}
		$typeModel = TypeModel::createFromXmlId($params['ENTITY_TYPE']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, []))
		{
			$this->addError(new Error('Access Denied'));

			return [
				'status' => 'error',
				'message' => 'Access Denied',
			];
		}
		if (!preg_match("/[a-z]{4}-[a-z]{4}-[a-z]{4}-[a-z]{4}/", $params['SERVER_PASSWORD']))
		{
			$this->addError(new Error('Incorrect app password'));

			return [
				'status' => 'incorrect_app_pass',
				'message' => 'Incorrect app password'
			];
		}

		$vendorSyncManager = new Icloud\VendorSyncManager();

		$connection = $vendorSyncManager->initConnection($params);
		if (!$connection)
		{
			$this->addError(new Error(Loc::getMessage('EC_SYNCALAX_ICLOUD_WRONG_AUTH')));

			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCALAX_ICLOUD_WRONG_AUTH'),
			];
		}

		return [
			'status' => 'success',
			'connectionId' => $connection
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
		FlagRegistry::getInstance()->setFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);
		$result = (new Icloud\VendorSyncManager())->syncIcloudConnection($connectionId);
		FlagRegistry::getInstance()->resetFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);

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

			/** @var Factory $mapperFactory */
			$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
			$connection = $mapperFactory->getConnection()->getMap([
				'=ID' => $connectionId,
				'=ENTITY_TYPE' => 'user',
				'=ENTITY_ID' => \CCalendar::getCurUserId(),
				'=IS_DELETED' => 'N'
			])->fetch();

			if ($connection)
			{
				return (new ConnectionManager())->deactivateConnection($connection)->isSuccess();
			}

			return false;
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
		CUserOptions::DeleteOption('calendar', 'last_sync_iphone', false, $userId);
		CUserOptions::DeleteOption('calendar', 'last_sync_mac', false, $userId);
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
}
