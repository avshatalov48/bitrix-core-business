<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Sharing\Link;
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
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class SharingAjax extends \Bitrix\Main\Engine\Controller
{
	private ?Sharing\Link\Factory $factory = null;

	public function configureActions(): array
	{
		return [
			'getUsersAccessibility' => [
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

		return $sharing->getLinkInfo();
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

	public function disableUserLinkAction(?string $hash): bool
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return false;
		}

		$sharing = new Sharing\Sharing(\CCalendar::GetUserId());
		$result = $sharing->deactivateUserLink($hash);
		if (!$result->isSuccess())
		{
			$this->addErrors($this->getErrors());

			return false;
		}

		return true;
	}

	public function increaseFrequentUseAction(?string $hash)
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return false;
		}

		$sharing = new Sharing\Sharing(\CCalendar::GetUserId());
		$result = $sharing->increaseFrequentUse($hash);
		if (!$result->isSuccess())
		{
			$this->addErrors($this->getErrors());

			return false;
		}

		return true;
	}

	public function generateUserJointSharingLinkAction(array $memberIds): ?array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return null;
		}

		$userId = \CCalendar::GetCurUserId();
		$result = (new Sharing\Sharing($userId))->generateUserJointLink($memberIds);
		if (!$result->isSuccess())
		{
			$this->addErrors($this->getErrors());

			return null;
		}

		return $result->getData();
	}

	public function getAllUserLinkAction(): ?array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return null;
		}

		$userId = \CCalendar::GetCurUserId();

		return [
			'userLinks' => (new Sharing\Sharing($userId))->getAllUserLinkInfo(),
			'pathToUser' => Option::get('intranet', 'path_user', '/company/personal/user/#USER_ID#/', '-'),
		];
	}

	public function getUsersAccessibilityAction(): array
	{
		$request = $this->getRequest();

		$userIds = $request->getPost('userIds');
		$fromTs = $request->getPost('timestampFrom') / 1000;
		$toTs = $request->getPost('timestampTo') / 1000;

		if (!is_array($userIds) || !$fromTs || !$toTs)
		{
			return [];
		}

		return (new Sharing\SharingAccessibilityManager([
			'userIds' => array_map('intval', $userIds),
			'timestampFrom' => $fromTs,
			'timestampTo' => $toTs
		]))->getUsersAccessibilitySegmentsInUtc();
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

		/** @var Sharing\Link\Joint\JointLink $link */
		$link = Link\Factory::getInstance()->getLinkByHash($parentLinkHash);

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

		$event = Sharing\SharingEventManager::prepareEventForSave($eventData, $userId, $link);

		$sharingEventManager = new Sharing\SharingEventManager($event, $userId, $ownerId, $link);
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

		Sharing\Analytics::getInstance()->sendMeetingCreated($link);

		// Auto-accepting meeting for owner and members
		$this->autoAcceptSharingEvent($event->getId(), $ownerId);
		foreach ($link->getMembers() as $member)
		{
			$this->autoAcceptSharingEvent($event->getId(), $member->getId());
		}

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

		$notificationService?->notifyAboutMeetingCreated($userContact);

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
		$crmDealLink = Link\Factory::getInstance()->getLinkByHash($crmDealLinkHash);

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

		$event = Sharing\SharingEventManager::prepareEventForSave($eventData, $userId, $crmDealLink);

		$sharingEventManager = new Sharing\SharingEventManager($event, $userId, $ownerId, $crmDealLink);
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

		Sharing\Analytics::getInstance()->sendMeetingCreated($crmDealLink);

		$dateFrom = $request['dateFrom'] ?? null;
		$timezone = $request['timezone'] ?? null;

		/** @var DateTime $eventStart */
		$eventStart = Util::getDateObject($dateFrom, false, $timezone);
		$activityName = Loc::getMessage('EC_SHARINGAJAX_ACTIVITY_SUBJECT');

		$crmDealLink->setLastStatus(null);
		(new Sharing\Link\CrmDealLinkMapper())->update($crmDealLink);

		// Create calendar sharing activity in deal
		(new Sharing\Crm\ActivityManager($event->getId(), $crmDealLink, $userName))
			->createCalendarSharingActivity($activityName, $event->getDescription(), $eventStart)
		;

		// Auto-accepting meeting for owner and members
		$this->autoAcceptSharingEvent($event->getId(), $ownerId);
		foreach ($crmDealLink->getMembers() as $member)
		{
			$this->autoAcceptSharingEvent($event->getId(), $member->getId());
		}

		//notify client about meeting is auto-accepted
		if ($crmDealLink->getContactId() > 0)
		{
			Crm\Integration\Calendar\Notification\Manager::getSenderInstance($crmDealLink)
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
		$eventLink = Link\Factory::getInstance()->getLinkByHash($eventLinkHash);
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

		$sharingEventManager = new Sharing\SharingEventManager($event, $eventLink->getHostId(), $eventLink->getOwnerId());
		$eventDeleteResult = $sharingEventManager->deleteEvent();

		if ($eventDeleteResult->getErrors())
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_DELETE_ERROR')));
		}
		else
		{
			$sharingEventManager->deactivateEventLink($eventLink);
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

	public function saveLinkRuleAction(string $linkHash, array $ruleArray): array
	{
		$result = [];

		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			$this->addError(new Error('Access denied'));

			return $result;
		}

		$saveResult = \Bitrix\Calendar\Sharing\Link\Rule\Helper::getInstance()->saveLinkRule($linkHash, $ruleArray);

		if (!$saveResult)
		{
			$this->addError(new Error('Error while trying to save rule'));
		}

		return $result;
	}

	private function currentUserIsNotResponsible(Sharing\Link\Link $link): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if (!($link instanceof Sharing\Link\CrmDealLink))
		{
			return false;
		}

		$currentUserId = (new Crm\Service\Context())->getUserId();

		return $this->getAssignedId($link) !== $currentUserId;
	}

	private function getAssignedId(Sharing\Link\CrmDealLink $link): ?int
	{
		$entityBroker = Crm\Service\Container::getInstance()->getEntityBroker(\CCrmOwnerType::Deal);
		if (!$entityBroker)
		{
			return null;
		}

		$entity = $entityBroker->getById($link->getEntityId());
		if (!$entity)
		{
			return null;
		}

		return $entity->getAssignedById();
	}

	/**
	 * @param string $eventLinkHash
	 * @return array
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getConferenceLinkAction(string $eventLinkHash): array
	{
		$result = [];
		$eventLinkHash = Application::getConnection()->getSqlHelper()->forSql($eventLinkHash);

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = Link\Factory::getInstance()->getLinkByHash($eventLinkHash);
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
		$eventLink = Link\Factory::getInstance()->getLinkByHash($eventLinkHash);
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
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

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
		$link = Link\Factory::getInstance()->getLinkByHash($linkHash);
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

		if (in_array($notifyType, Sharing\Crm\NotifyManager::NOTIFY_TYPES, true))
		{
			(new Sharing\Crm\NotifyManager($link, $notifyType))
				->sendSharedCrmActionsEvent(Util::getDateTimestamp($dateFrom, $timezone));

			$link->setLastStatus($notifyType);
			(new Sharing\Link\CrmDealLinkMapper())->update($link);
		}

		return $result;
	}

	public function updateSharingSettingsCollapsedAction(string $collapsed): void
	{
		$sharing = new Sharing\Sharing(\CCalendar::GetUserId());
		$sharing->setSharingSettingsCollapsed($collapsed === 'Y');
	}

	public function setSortJointLinksByFrequentUseAction(string $sortByFrequentUse): void
	{
		$sharing = new \Bitrix\Calendar\Sharing\Sharing(\CCalendar::GetUserId());
		$sharing->setSortJointLinksByFrequentUse($sortByFrequentUse === 'Y');
	}

	private function getUserTimezoneName(): string
	{
		$userId = \CCalendar::GetCurUserId();

		return \CCalendar::getUserTimezoneName($userId);
	}

	private function autoAcceptSharingEvent(int $eventId, int $userId)
	{
		\CCalendarEvent::SetMeetingStatus([
			'eventId' => $eventId,
			'userId' => $userId,
			'status' => 'Y',
			'sharingAutoAccept' => true,
		]);
	}
}
