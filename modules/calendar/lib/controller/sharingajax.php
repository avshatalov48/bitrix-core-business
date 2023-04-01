<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\ControllerHelper\SharingAjaxHelper;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Calendar\Sharing;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Sharing\SharingEventManager;
use Bitrix\Calendar\Sharing\SharingUser;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Cookie;

Loc::loadMessages(__FILE__);

class SharingAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
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
		];
	}

	public function toggleLinkAction(int $userLinkId, string $isActive): void
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return;
		}

		$sharingLinkFactory = new Sharing\Link\Factory();
		$userId = \CCalendar::GetUserId();

		$userLinks = $sharingLinkFactory->getUserLinks($userId);
		$userLink = current(array_filter($userLinks, static function($userLink) use ($userLinkId) {
			return $userLink->getId() === $userLinkId;
		}));

		if ($userLink)
		{
			$userLink->setActive($isActive === 'true');
			(new Sharing\Link\UserLinkMapper())->update($userLink);
		}
	}

	public function deleteUserLinksAction()
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return;
		}

		$sharingLinkFactory = new Sharing\Link\Factory();
		$userId = \CCalendar::GetUserId();

		$userLinks = $sharingLinkFactory->getUserLinks($userId);

		$userLinkMapper = new Sharing\Link\UserLinkMapper();
		foreach ($userLinks as $userLink)
		{
			$userLinkMapper->delete($userLink);
		}
	}

	public function getDialogDataAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$request = $this->getRequest();

		$isSharingOn = $request->getPost('isSharingOn') === 'true';

		$sharingLinkFactory = new Sharing\Link\Factory();
		$userId = \CCalendar::GetUserId();

		$userLinks = $sharingLinkFactory->getUserLinksArray($userId);
		if (empty($userLinks))
		{
			$userLinks =
				$sharingLinkFactory
					->createUserLink($userId, $isSharingOn)
					->getUserLinksArray($userId)
			;
		}

		$serverPath = \CCalendar::GetServerPath();
		//TODO We need a method to process batch of userLinks, not a cycle
		foreach ($userLinks as &$userLink)
		{
			$userLink['url'] = Sharing\Helper::getShortUrl($userLink['url']);
			$userLink['serverPath'] = $serverPath;
		}

		return ['links' => $userLinks];
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
		global $DB;

		$result = [];
		$request = $this->getRequest();

		$userName = $DB->ForSql(trim($request['userName'] ?? ''));
		$userContact = $DB->ForSql(trim($request['userContact'] ?? ''));
		$userLinkHash = trim($request['userLinkHash'] ?? '');
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

		/** @var Sharing\Link\UserLink $userLink */
		$userLink = (new Sharing\Link\Factory())->getLinkByHash($userLinkHash);
		if (!$userLink || !$userLink->isActive() || $userLink->getObjectType() !== 'user')
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

		$event = Sharing\SharingEventManager::prepareEventForSave($request, $userId);

		$eventCreateResult = (new Sharing\SharingEventManager($event, $userId, $ownerId, $userLinkHash))
			->createEvent()
		;

		if ($errors = $eventCreateResult->getErrors())
		{
			$this->addError($errors[0]);
		}
		else
		{
			/** @var Event $event */
			$event = $eventCreateResult->getData()['event'];
			/** @var Sharing\Link\EventLink $eventLink */
			$eventLink = $eventCreateResult->getData()['eventLink'];

			$notificationService = null;
			if (SharingEventManager::isEmailCorrect($userContact))
			{
				$notificationService = (new Sharing\Notification\Mail())
					->setEventLink($eventLink)
					->setEvent($event)
				;
			}
			if (SharingEventManager::isPhoneNumberCorrect($userContact))
			{
				$notificationService = (new Sharing\Notification\Sms())
					->setEventLink($eventLink)
					->setEvent($event)
				;
			}

			if ($notificationService !== null)
			{
				$notificationService->notifyAboutMeetingStatus($userContact);
			}

			$result = [
				'eventId' => $event->getId(),
				'eventName' => $event->getName(),
				'eventLinkId' => $eventLink->getId(),
				'eventLinkHash' => $eventLink->getHash(),
				'eventLinkShortUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl()),
			];
		}

		return $result;
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
		$eventLinkHash = trim($request['eventLinkHash']);

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getLinkByHash($eventLinkHash);
		if (!$eventLink || !$eventLink->isActive() || $eventLink->getObjectType() !== 'event')
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

		$eventDeleteResult = (new Sharing\SharingEventManager($event, $eventLink->getHostId(), $eventLink->getOwnerId()))
			->deleteEvent($eventLink->getId())
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

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getLinkByHash($eventLinkHash);
		if (!$eventLink || !$eventLink->isActive() || $eventLink->getObjectType() !== 'event')
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
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getLinkByHash($eventLinkHash);
		if (!$eventLink || !$eventLink->isActive() || $eventLink->getObjectType() !== 'event')
		{
			$this->addError(new Error('Link not found'));

			return '';
		}

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventLink->getEventId());

		if (!$event)
		{
			$this->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_ACCESS_DENIED')));

			return '';
		}

		return IcsManager::getInstance()->getIcsFileContent($event, [
			'eventUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl()),
			'conferenceUrl' => Sharing\Helper::getShortUrl($eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE),
		]);
	}

	public function getDeletedSharedEventAction(int $entryId): array
	{
		return [
			'entry' => SharingAjaxHelper::getDeletedSharedEvent($entryId),
			'userTimezone' => SharingAjaxHelper::getUserTimezoneName(),
		];
	}

}