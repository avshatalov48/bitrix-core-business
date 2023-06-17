<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Util;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Sharing\SharingEventManager;
use Bitrix\Calendar\Sharing\SharingUser;
use Bitrix\Crm;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Cookie;

Loc::loadMessages(__FILE__);

class SharingAjax extends \Bitrix\Main\Engine\Controller
{
	private ?Sharing\Link\Factory $factory = null;

	public function configureActions(): array
	{
		return [
			'getUserAccessibility' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'saveEvent' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'saveCrmEvent' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'deleteEvent' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'saveFirstEntry' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'getConferenceLink' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'getIcsFileContent' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
			'handleTimelineNotify' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class
				],
				'+postfilters' => [
					new ActionFilter\Cors()
				],
			],
		];
	}

	public function disableOptionPayAttentionToNewSharingFeatureAction(): void
	{
		Sharing\Helper::disableOptionPayAttentionToNewSharingFeature();
	}

	public function enableUserSharingAction(): ?array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return null;
		}

		$sharing = new Sharing\Sharing(\CCalendar::GetUserId());
		$result = $sharing->enable();
		if (!$result->isSuccess())
		{
			$this->addErrors($this->getErrors());
			return null;
		}

		$userLink = $sharing->getUserLink();
		if (!$userLink)
		{
			return null;
		}

		return [
			'url' => Sharing\Helper::getShortUrl($userLink->getUrl()),
		];
	}

	public function disableUserSharingAction(): ?bool
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return null;
		}

		$sharing = new Sharing\Sharing(\CCalendar::GetUserId());
		$result = $sharing->disable();
		if (!$result->isSuccess())
		{
			$this->addErrors($this->getErrors());
			return null;
		}

		return true;
	}

	public function getUserAccessibilityAction(): array
	{
		$request = $this->getRequest();

		$userId = (int)$request->getPost('userId');
		$fromTs = $request->getPost('timestampFrom') / 1000;
		$toTs = $request->getPost('timestampTo') / 1000;

		if (!$userId || !$fromTs || !$toTs)
		{
			return [];
		}

		return (new Sharing\SharingAccessibilityManager([
			'userId' => $userId,
			'timestampFrom' => $fromTs,
			'timestampTo' => $toTs
		]))->getUserAccessibilitySegmentsInUtc();
	}

	/**
	 * @return array
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveEventAction(): array
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$result = [];
		$request = $this->getRequest();

		$userName = $sqlHelper->forSql(trim($request['userName'] ?? ''));
		$userContact = $sqlHelper->forSql(trim($request['userContact'] ?? ''));
		$parentLinkHash = $sqlHelper->forSql(trim($request['parentLinkHash'] ?? ''));
		$ownerId = (int)($request['ownerId'] ?? null);
		$ownerCreated = ($request['ownerCreated'] ?? null) === 'true';

		$contactDataError = !Sharing\SharingEventManager::validateContactData($userContact);
		$contactNameError = !Sharing\SharingEventManager::validateContactName($userName);
		if ($contactDataError || $contactNameError)
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_INCORRECT_CONTACT_DATA')));

			return [
				'contactDataError' => $contactDataError,
				'contactNameError' => $contactNameError,
			];
		}

		/** @var Sharing\Link\Link $link */
		$link = $this->getSharingLinkFactory()->getLinkByHash($parentLinkHash);

		if (
			!$link
			|| !$link->isActive()
			|| $link->getObjectType() !== Sharing\Link\Helper::USER_SHARING_TYPE
			|| $link->getObjectId() !== $ownerId
		)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		$userParams = [
			'NAME' => $userName,
			'CONTACT_DATA' => $userContact,
		];
		if ($ownerCreated)
		{
			$userId = SharingUser::getInstance()->getAnonymousUserForOwner($userParams);
		}
		else
		{
			$userId = SharingUser::getInstance()->login(true, $userParams);
		}


		if (!$userId)
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_USER_NOT_FOUND')));

			return $result;
		}

		$eventData = Sharing\SharingEventManager::getEventDataFromRequest($request);
		$eventData['eventName'] = Sharing\SharingEventManager::getSharingEventNameByUserName($userName);

		$event = Sharing\SharingEventManager::prepareEventForSave($eventData, $userId);

		$sharingEventManager = new Sharing\SharingEventManager($event, $userId, $ownerId, $parentLinkHash);
		$eventCreateResult = $sharingEventManager->createEvent(false, $userParams['NAME']);

		if ($errors = $eventCreateResult->getErrors())
		{
			$this->addError($errors[0]);

			return $result;
		}

		/** @var Event $event */
		$event = $eventCreateResult->getData()['event'];
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $eventCreateResult->getData()['eventLink'];

		// Auto-accepting meeting for manager
		\CCalendarEvent::SetMeetingStatus([
			'eventId' => $event->getId(),
			'userId' => $ownerId,
			'status' => 'Y',
			'hostNotification' => false,
			'sharingAutoAccept' => true,
		]);

		/** @var Event $event */
		$event = (new Mappers\Factory())->getEvent()->getById($event->getId());

		$notificationService = null;
		if (SharingEventManager::isEmailCorrect($userContact))
		{
			$notificationService = (new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
			;
		}

		if ($notificationService !== null)
		{
			$notificationService->notifyAboutMeetingStatus($userContact);
		}

		return [
			'eventId' => $event->getId(),
			'eventName' => $event->getName(),
			'eventLinkId' => $eventLink->getId(),
			'eventLinkHash' => $eventLink->getHash(),
			'eventLinkShortUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl()),
		];
	}

	public function saveCrmEventAction(): array
	{
		$result = [];
		$request = $this->getRequest();

		if (!Loader::includeModule('crm'))
		{
			$this->addError(new Error('Module crm not found'));

			return $result;
		}

		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();


		$crmDealLinkHash = $sqlHelper->forSql(trim($request['crmDealLinkHash'] ?? ''));
		$ownerId = (int)($request['ownerId'] ?? null);
		$ownerCreated = ($request['ownerCreated'] ?? null) === 'true';

		/** @var Sharing\Link\CrmDealLink $crmDealLink */
		$crmDealLink = $this->getSharingLinkFactory()->getLinkByHash($crmDealLinkHash);

		if (
			!$crmDealLink
			|| !$crmDealLink->isActive()
			|| $crmDealLink->getObjectType() !== Sharing\Link\Helper::CRM_DEAL_SHARING_TYPE
			|| $crmDealLink->getOwnerId() !== $ownerId
		)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		if (!$crmDealLink->getContactId()|| !$crmDealLink->getContactType())
		{
			$userName = $sqlHelper->forSql(trim($request['userName'] ?? ''));
			$userContact = $sqlHelper->forSql(trim($request['userContact'] ?? ''));

			$contactDataError = !Sharing\SharingEventManager::validateContactData($userContact);
			$contactNameError = !Sharing\SharingEventManager::validateContactName($userName);
			if ($contactDataError || $contactNameError)
			{
				$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_INCORRECT_CONTACT_DATA')));

				return [
					'contactDataError' => $contactDataError,
					'contactNameError' => $contactNameError,
				];
			}
		}
		else
		{
			$contactType = $crmDealLink->getContactType();
			$contactId = $crmDealLink->getContactId();
			$contactData = \Bitrix\Crm\Service\Container::getInstance()
				->getEntityBroker($contactType)
				->getById($contactId)
			;
			if (!$contactData)
			{
				$this->addError(new Error('Contact not found'));

				return $result;
			}

			$userName = '';
			if ($contactType === \CCrmOwnerType::Contact)
			{
				$userName = $contactData->getFullName();
			}
			elseif ($contactType === \CCrmOwnerType::Company)
			{
				$userName = $contactData->getTitle();
			}
			$userContact = '';
		}

		$userParams = [
			'NAME' => $userName,
			'CONTACT_DATA' => $userContact,
		];
		if ($ownerCreated)
		{
			$userId = SharingUser::getInstance()->getAnonymousUserForOwner($userParams);
		}
		else
		{
			$userId = SharingUser::getInstance()->login(true, $userParams);
		}

		$eventData = Sharing\SharingEventManager::getCrmEventDataFromRequest($request);
		$eventData['eventName'] = Sharing\SharingEventManager::getSharingEventNameByUserName($userName);

		$event = Sharing\SharingEventManager::prepareEventForSave($eventData, $userId);

		$sharingEventManager = new Sharing\SharingEventManager($event, $userId, $ownerId, $crmDealLinkHash);
		$eventCreateResult = $sharingEventManager->createEvent(false, $userParams['NAME']);

		if ($errors = $eventCreateResult->getErrors())
		{
			$this->addError($errors[0]);

			return $result;
		}

		/** @var Event $event */
		$event = $eventCreateResult->getData()['event'];
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $eventCreateResult->getData()['eventLink'];

		$dateFrom = $request['dateFrom'] ?? null;
		$timezone = $request['timezone'] ?? null;

		/** @var DateTime $eventStart */
		$eventStart = Util::getDateObject($dateFrom, false, $timezone);
		$activityName = Loc::getMessage('EC_SHARINGAJAX_ACTIVITY_SUBJECT');
		(new Sharing\Crm\ActivityManager($event->getId(), $crmDealLink, $userName))
			->createCalendarSharingActivity($activityName, $event->getDescription(), $eventStart)
		;

		// Auto-accepting meeting for manager
		\CCalendarEvent::SetMeetingStatus([
			'eventId' => $event->getId(),
			'userId' => $ownerId,
			'status' => 'Y',
			'hostNotification' => false,
			'sharingAutoAccept' => true,
		]);

		//notify client about meeting is auto-accepted
		if ($crmDealLink->getContactId() > 0)
		{
			(new Crm\Integration\Calendar\Notification\NotificationService())
				->setCrmDealLink($crmDealLink)
				->setEvent($event)
				->setEventLink($eventLink)
				->sendCrmSharingAutoAccepted()
			;
		}
		else
		{
			(new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
				->notifyAboutMeetingCreated($userContact)
			;
		}

		return [
			'eventId' => $event->getId(),
			'eventName' => $event->getName(),
			'eventLinkId' => $eventLink->getId(),
			'eventLinkHash' => $eventLink->getHash(),
			'eventLinkShortUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl()),
		];
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public function deleteEventAction(): array
	{
		$result = [];
		$request = $this->getRequest();
		$eventId = (int)$request['eventId'];
		$eventLinkHash = Application::getConnection()->getSqlHelper()->forSql($request['eventLinkHash']);

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $this->getSharingLinkFactory()->getLinkByHash($eventLinkHash);
		if (
			!$eventLink
			|| !$eventLink->isActive()
			|| $eventLink->getObjectType() !== Sharing\Link\Helper::EVENT_SHARING_TYPE
		)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventId);
		if (!$event)
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_NOT_FOUND')));

			return $result;
		}

		if ($event->getId() !== $eventLink->getEventId())
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_DELETE_ERROR')));

			return $result;
		}

		if ($event->getSpecialLabel() === Dictionary::EVENT_TYPE['shared_crm'])
		{
			SharingEventManager::onSharingCrmEventClientDelete($event->getId());
		}

		$eventDeleteResult = (new Sharing\SharingEventManager($event, $eventLink->getHostId(), $eventLink->getOwnerId()))
			->deactivateEventLink($eventLink)
			->deleteEvent()
		;
		if ($eventDeleteResult->getErrors())
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_DELETE_ERROR')));
		}

		return $result;
	}

	/**
	 * @return void
	 */
	public function saveFirstEntryAction()
	{
		$cookieName = 'CALENDAR_SHARING_FIRST_PAGE_VISITED';
		$cookie = new Cookie($cookieName, 'Y', null, false);
		Context::getCurrent()->getResponse()->addCookie($cookie);
	}

	/**
	 * @param int $linkId
	 * @return array
	 * @throws \Bitrix\Main\LoaderException|\Bitrix\Main\ArgumentException
	 * @throws BaseException
	 */
	public function getConferenceLinkAction(string $eventLinkHash): array
	{
		$result = [];
		$eventLinkHash = Application::getConnection()->getSqlHelper()->forSql($eventLinkHash);

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $this->getSharingLinkFactory()->getLinkByHash($eventLinkHash);
		if (!$eventLink || !$eventLink->isActive() || $eventLink->getObjectType() !== Sharing\Link\Helper::EVENT_SHARING_TYPE)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		$conferenceLink = (new Sharing\SharingConference($eventLink))->getConferenceLink();

		if (!$conferenceLink)
		{
			$this->addError(new Error('Error while creating conference link'));

			return $result;
		}

		$result['conferenceLink'] = $conferenceLink;

		return $result;
	}

	/**
	 * @throws ArgumentException
	 */
	public function getIcsFileContentAction(string $eventLinkHash): string
	{
		$result = '';
		$eventLinkHash = Application::getConnection()->getSqlHelper()->forSql($eventLinkHash);

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $this->getSharingLinkFactory()->getLinkByHash($eventLinkHash);
		if (
			!$eventLink
			|| !$eventLink->isActive()
			|| $eventLink->getObjectType() !== Sharing\Link\Helper::EVENT_SHARING_TYPE
		)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventLink->getEventId());

		if (!$event)
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_ACCESS_DENIED')));

			return $result;
		}

		$event->setName(SharingEventManager::getSharingEventNameByUserId($eventLink->getOwnerId()));
		return IcsManager::getInstance()->getIcsFileContent($event, [
			'eventUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl()),
			'conferenceUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE),
		]);
	}

	public function getDeletedSharedEventAction(int $entryId): array
	{
		$event = \CCalendar::getDeletedSharedEvent($entryId);
		$linkArray = [];
		if (is_array($event))
		{
			/** @var Sharing\Link\EventLink $link */
			$eventId = $entryId === $event['ID'] ? $entryId : $event['PARENT_ID'];
			$link = (new Sharing\Link\Factory())->getDeletedEventLinkByEventId($eventId);
			if (!is_null($link))
			{
				$linkArray = [
					'canceledTimestamp' => $link->getCanceledTimestamp(),
					'externalUserName' => $link->getExternalUserName(),
					'externalUserId' => $link->getHostId(),
				];
			}
		}

		return [
			'entry' => $event,
			'link' => $linkArray,
			'userTimezone' => $this->getUserTimezoneName(),
		];
	}

	public function handleTimelineNotifyAction(): array
	{
		$result = [];

		if (!Loader::includeModule('crm'))
		{
			$this->addError(new Error('Module crm not installed'));

			return $result;
		}

		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$request = $this->getRequest();
		$linkHash = $sqlHelper->forSql(trim($request['linkHash'] ?? ''));
		$entityType = $sqlHelper->forSql(trim($request['entityType'] ?? ''));
		$notifyType = $sqlHelper->forSql(trim($request['notifyType']) ?? '');
		$entityId = (int)($request['entityId'] ?? null);
		$dateFrom = $request['dateFrom'] ?? null;
		$timezone = $request['timezone'] ?? null;


		/** @var Sharing\Link\CrmDealLink $link */
		$link = $this->getSharingLinkFactory()->getLinkByHash($linkHash);
		if (
			!$link
			|| !$link->isActive()
			|| $link->getObjectId() !== $entityId
			|| $link->getObjectType() !== $entityType
		)
		{
			$this->addError(new Error('Link not found'));

			return $result;
		}

		(new Sharing\Crm\NotifyManager($link, $notifyType))
			->sendSharedCrmActionsEvent(Util::getDateTimestamp($dateFrom, $timezone));

		return $result;
	}

	private function getUserTimezoneName(): string
	{
		$userId = \CCalendar::GetCurUserId();

		return \CCalendar::getUserTimezoneName($userId);
	}

	private function getSharingLinkFactory(): Sharing\Link\Factory
	{
		if (!$this->factory)
		{
			$this->factory = new Sharing\Link\Factory();
		}

		return $this->factory;
	}
}