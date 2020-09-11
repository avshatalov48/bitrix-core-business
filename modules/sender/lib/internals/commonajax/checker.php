<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

/**
 * Class Checker
 * @package Bitrix\Sender\Internals\CommonAjax
 */
class Checker
{
	/**
	 * Get read permission checker.
	 *
	 * @return array
	 */
	public static function getReadPermissionChecker()
	{
		return array(__CLASS__, 'onReadPermissionCheck');
	}

	/**
	 * On read permission check.
	 *
	 * @param Result $result Result.
	 * @return void
	 */
	public static function onReadPermissionCheck(Result $result)
	{
		if (Security\User::current()->canView())
		{
			return;
		}

		$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
	}

	/**
	 * Get write permission checker.
	 *
	 * @return array
	 */
	public static function getWritePermissionChecker()
	{
		return array(__CLASS__, 'onWritePermissionCheck');
	}

	/**
	 * On write permission check.
	 *
	 * @param Result $result Result.
	 * @return void
	 */
	public static function onWritePermissionCheck(Result $result)
	{
		if (Security\User::current()->canEdit())
		{
			return;
		}

		$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
	}

	/**
	 * Get permission checker for viewing Letter.
	 *
	 * @return callable
	 */
	public static function getViewLetterPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewLetters())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying Letter.
	 *
	 * @return callable
	 */
	public static function getModifyLetterPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyLetters())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying Abuse.
	 *
	 * @return callable
	 */
	public static function getModifyAbusePermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyAbuses())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for viewing Segment.
	 *
	 * @return callable
	 */
	public static function getViewSegmentPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewSegments())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for selecting Segment.
	 *
	 * @return callable
	 */
	public static function getSelectSegmentPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewSegments())
			{
				return;
			}
			if (Security\Access::getInstance()->canViewLetters())
			{
				return;
			}
			if (Security\Access::getInstance()->canViewAds())
			{
				return;
			}
			if (Security\Access::getInstance()->canViewRc())
			{
				return;
			}
			if (Security\Access::getInstance()->canModifySettings())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying Segment.
	 *
	 * @return callable
	 */
	public static function getModifySegmentPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifySegments())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for viewing RC.
	 *
	 * @return callable
	 */
	public static function getViewRcPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewRc())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying RC.
	 *
	 * @return callable
	 */
	public static function getModifyRcPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyRc())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for viewing RC.
	 *
	 * @return callable
	 */
	public static function getViewBlacklistPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewBlacklist())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying RC.
	 *
	 * @return callable
	 */
	public static function getModifyBlacklistPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyBlacklist())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for viewing ad.
	 *
	 * @return callable
	 */
	public static function getViewAdPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewAds())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying ad.
	 *
	 * @return callable
	 */
	public static function getModifyAdPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyAds())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for viewing recipients.
	 *
	 * @return callable
	 */
	public static function getViewRecipientsPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canViewSegments())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_READ_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying recipients.
	 *
	 * @return callable
	 */
	public static function getModifyRecipientsPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifySegments())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 * Get permission checker for modifying settings.
	 *
	 * @return callable
	 */
	public static function getModifySettingsPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifySettings())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 *
	 * @return callable
	 */
	public static function getPauseStopStartLetterPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canPauseStartStopLetter())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 *
	 * @return callable
	 */
	public static function getPauseStopStartAdsPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canPauseStartStopAds())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 *
	 * @return callable
	 */
	public static function getPauseStopStartRcPermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canPauseStartStopRc())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}

	/**
	 *
	 * @return callable
	 */
	public static function getModifyTemplatePermissionChecker()
	{
		return function (Result $result)
		{
			if (Security\Access::getInstance()->canModifyTemplates())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}
}