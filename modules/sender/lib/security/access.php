<?php

namespace Bitrix\Sender\Security;

use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Access\Event\EventResult;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Sender\Access\AccessController;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\SectionDictionary;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Security\Role\Permission;
use Http\Request;

Loc::loadMessages(__FILE__);

/**
 * Class Access
 *
 * @package Bitrix\Sender\Security
 */
class Access
{
	private static $list;

	/** @var  User $user User. */
	protected $user;

	/** @var array $permissions Permissions. */
	protected $permissions;

	private static $instance;

	protected const ACTION_VIEW = 'VIEW';

	/**
	 * Get Access instance for current user.
	 *
	 * @deprecated
	 * @return static
	 * @throws ArgumentException
	 */
	public static function current()
	{
		return new static(User::current());
	}

	/**
	 * PageAccess constructor.
	 *
	 * @param User $user User instance.
	 *
	 * @throws ArgumentException
	 */
	private function __construct(User $user)
	{
		$this->user = $user;
		self::registerEvent(EventDictionary::EVENT_ON_AFTER_CHECK);
		$this->permissions = Permission::getByUserId($this->user->getId());
	}

	/**
	 * singleton for DB requests optimization
	 *
	 * @param null $user
	 *
	 * @return Access
	 * @throws ArgumentException
	 */
	public static function getInstance($user = null)
	{
		if(is_null(self::$instance))
		{
			self::$instance = new self(!is_null($user) ? $user : User::current());
		}
		return self::$instance;
	}

	/**
	 * Return true if can view anything.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewAnything()
	{
		return (
			$this->canViewLetters()
			||
			$this->canViewAds()
			||
			$this->canViewRc()
			||
			$this->canViewTemplates()
			||
			$this->canViewToloka()
			||
			$this->canViewBlacklist()
			||
			$this->canViewClientList()
			||
			$this->canViewSegments()
			||
			$this->canViewTemplates()
		);
	}

	/**
	 * Return true if can modify settings.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifySettings()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_SETTINGS_EDIT);
	}

	/**
	 * Return true if can modify template.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyTemplates()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_TEMPLATE_EDIT);
	}

	/**
	 * Return true if can pause start or stop Mailing
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canPauseStartStopLetter()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_PAUSE_START_STOP);
	}

	/**
	 * Return true if can pause start or stop ADS
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canPauseStartStopAds()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_PAUSE_START_STOP);
	}

	/**
	 * Return true if can pause start or stop RC
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canPauseStartStopRc()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_RC_PAUSE_START_STOP);
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewLetters()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_VIEW);
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewTemplates()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_TEMPLATE_VIEW);
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewClientList()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW);
	}

	/**
	 * Return true if user can modify letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyLetters()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_EMAIL_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_AUDIO_CALL_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_INFO_CALL_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_SMS_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_MESSENGER_EDIT)
			;
	}

	/**
	 * Return can user start stop or pause
	 *
	 * @param string $letterClass
	 *
	 * @return bool
	 */
	public function canStopStartPause(string $letterClass)
	{
		$letterType = explode("\\", $letterClass);

		switch ($letterType[count($letterType) - 1])
		{
			case 'Rc':
				return AccessController::can($this->user->getId(), ActionDictionary::ACTION_RC_PAUSE_START_STOP);
				break;
			case 'Ad':
				return AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_PAUSE_START_STOP);
				break;
			default:
				return AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_PAUSE_START_STOP);
		}
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewAds()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_VIEW);
	}

	/**
	 * Return true if can modify letters.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyAds()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_GOOGLE_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_YANDEX_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_FB_INSTAGRAM_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_VK_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_LOOK_ALIKE_VK_EDIT)
			|| AccessController::can($this->user->getId(), ActionDictionary::ACTION_ADS_LOOK_ALIKE_FB_EDIT)
			;
	}

	/**
	 * Return true if can view return customer tools.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewRc()
	{
		if(!ModuleManager::isModuleInstalled('crm'))
		{
			return false;
		}

		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_RC_VIEW);
	}

	/**
	 * Return true if can view return customer tools.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewToloka()
	{
		return Integration\Bitrix24\Service::isTolokaVisibleInRegion()
			&& AccessController::can($this->user->getId(),ActionDictionary::ACTION_RC_VIEW);
	}

	/**
	 * Return true if can modify return customer tools.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyRc()
	{
		if(!ModuleManager::isModuleInstalled('crm'))
		{
			return false;
		}

		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_RC_EDIT);
	}

	/**
	 * Return true if can view segments.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewSegments()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_SEGMENT_VIEW);
	}

	/**
	 * Return true if can view segment contacts.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewSegmentContact()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW);
	}

	/**
	 * Return true if can modify segments.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifySegments()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_SEGMENT_EDIT);
	}

	/**
	 * Return true if can view blacklist.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewStart()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_START_VIEW);
	}

	/**
	 * Return true if can view blacklist.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewBlacklist()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_BLACKLIST_VIEW);
	}

	/**
	 * Return true if can modify blacklist.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyBlacklist()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_BLACKLIST_EDIT);
	}


	/**
	 * Return true if can view abuses.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canViewAbuses()
	{
		return AccessController::can($this->user->getId(), ActionDictionary::ACTION_MAILING_VIEW);
	}

	/**
	 * Return true if can modify abuses.
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModifyAbuses()
	{
		return !Integration\Bitrix24\Service::isCloud() && $this->canModifySegments();
	}

	/**
	 * Returns true if user can view specified entity.
	 * @param string $entityCode
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canView($entityCode)
	{
		return $this->canPerform($entityCode, Permission::ACTION_VIEW);
	}

	/**
	 * Returns true if user can modify specified entity.
	 * @param string $entityCode
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canModify($entityCode)
	{
		return $this->canPerform($entityCode, Permission::ACTION_MODIFY);
	}

	/**
	 * Returns true if user can perform specified action on the entity.
	 * @param string $entityCode Code of the entity.
	 * @param string $actionCode Code of the action.
	 * @param string $minPerm Code of minimal permission.
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canPerform($entityCode, $actionCode, $minPerm = null)
	{
		if ($this->user->canEdit())
		{
			return true;
		}

		if($actionCode === self::ACTION_VIEW)
			return $this->user->canView();

		return false;
	}

	private static function getSectionAndAction($action)
	{
		$actionMap = ActionDictionary::getLegacyMap();
		$actionName = ActionDictionary::getActionName($action);
		$sectionName = explode("_", $actionName)[0];
		$sectionConst = constant(SectionDictionary::class."::".$sectionName);
		$sectionMap = SectionDictionary::getLegacyMap();

		return [$sectionMap[$sectionConst], $actionMap[$action]];
	}
	/**
	 * @param \Bitrix\Main\Event $event
	 *
	 * @return mixed
	 * @throws ArgumentException
	 */
	public static function handleEvent(Event $event)
	{
		$eventData = $event->getParameters();
		$action = $eventData['action'];
		[$sectionCode, $actionCode] = self::getSectionAndAction($action);

		$instance = self::getInstance();
		$eventResult = new EventResult(EventResult::SUCCESS);

		try
		{
			$canAccess = $instance->canPerform($sectionCode, $actionCode);
		} catch (ArgumentException $e)
		{
			return $eventResult->forbidAccess();
		}

		if($canAccess)
		{
			return $eventResult->allowAccess();
		}

		return $eventResult->forbidAccess();
	}

	/**
	 * @param $eventName
	 * @param array $filter
	 */
	public static function registerEvent($eventName, array $filter = [])
	{
		if(empty(static::$list[$eventName]))
		{
			EventManager::getInstance()->addEventHandler(
				AccessController::class,
				EventDictionary::EVENT_ON_AFTER_CHECK,
				array(__CLASS__, 'handleEvent'),
				false,
				1);
		}

		static::$list[$eventName][] = $filter;
	}
}
