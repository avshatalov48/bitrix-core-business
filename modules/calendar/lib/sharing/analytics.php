<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Main\Analytics\AnalyticsEvent;

class Analytics
{
	use SingletonTrait;

	public const CLIENT_STARTED = 'multiple';
	public const MANAGER_STARTED = 'manager_starting';

	protected const TOOL_CALENDAR = 'calendar';
	protected const CATEGORY_SLOTS = 'slots';

	protected const CONTEXT_CALENDAR = 'calendar';
	protected const CONTEXT_CRM = 'crm';

	protected const EVENT_LINK_CREATED = 'link_created';
	protected const EVENT_MEETING_PLACED = 'meeting_placed';
	protected const EVENT_CHAT_STARTED = 'chat_started';
	protected const EVENT_CALL_STARTED = 'call';

	protected const LINK_CREATE_METHOD_CRM_SEND = 'crm_send';

	protected const TYPE_SOLO = 'solo';
	protected const TYPE_MULTIPLE = 'multiple';

	public function sendLinkSent(Link\CrmDealLink $crmDealLink): void
	{
		$analyticsEvent = $this->getAnalyticsEvent(self::EVENT_LINK_CREATED)
			->setElement(self::LINK_CREATE_METHOD_CRM_SEND)
		;

		$this->sendAnalytics($analyticsEvent, $crmDealLink);
	}

	public function sendMeetingCreated(Link\Joint\JointLink $link): void
	{
		$analyticsEvent = $this->getAnalyticsEvent(self::EVENT_MEETING_PLACED);

		$this->sendAnalytics($analyticsEvent, $link);
	}

	public function sendChatCreated(Link\EventLink $eventLink, string $whoStarted): void
	{
		$analyticsEvent = $this->getAnalyticsEvent(self::EVENT_CHAT_STARTED)
			->setElement($whoStarted)
		;

		$parentLink = (new Link\Factory())->getLinkByHash($eventLink->getParentLinkHash());

		$this->sendAnalytics($analyticsEvent, $parentLink);
	}

	public function sendCallStarted(Link\Joint\JointLink $parentLink): void
	{
		$analyticsEvent = $this->getAnalyticsEvent(self::EVENT_CALL_STARTED);

		$this->sendAnalytics($analyticsEvent, $parentLink);
	}

	protected function getAnalyticsEvent(string $eventName): AnalyticsEvent
	{
		return (new AnalyticsEvent($eventName, self::TOOL_CALENDAR, self::CATEGORY_SLOTS));
	}

	protected function sendAnalytics(AnalyticsEvent $analyticsEvent, ?Link\Joint\JointLink $link): void
	{
		$isJoint = false;
		$members = [];
		$rule = null;

		if (!is_null($link))
		{
			$members = $link->getMembers();
			$rule = $link->getSharingRule();
			$isJoint = $link->isJoint();
		}

		$context = $link instanceof Link\CrmDealLink ? self::CONTEXT_CRM : self::CONTEXT_CALENDAR;
		$type = $isJoint ? self::TYPE_MULTIPLE : self::TYPE_SOLO;
		$membersCount = count($members) + 1;
		$ruleChanges = (new Link\Rule\Mapper())->getChanges($rule);

		$analyticsEvent
			->setSection($context)
			->setType($type)
			->setP1("peopleCount_$membersCount")
			->setP2("customDays_{$ruleChanges['customDays']}")
			->setP3("customLength_{$ruleChanges['customLength']}")
			->send()
		;
	}
}
