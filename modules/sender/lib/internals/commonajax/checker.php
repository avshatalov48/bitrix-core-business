<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
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
			if (Security\Access::current()->canViewLetters())
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
			if (Security\Access::current()->canModifyLetters())
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
			if (Security\Access::current()->canModifyAbuses())
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
			if (Security\Access::current()->canViewSegments())
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
			if (Security\Access::current()->canViewSegments())
			{
				return;
			}
			if (Security\Access::current()->canViewLetters())
			{
				return;
			}
			if (Security\Access::current()->canViewAds())
			{
				return;
			}
			if (Security\Access::current()->canViewRc())
			{
				return;
			}
			if (Security\Access::current()->canModifySettings())
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
			if (Security\Access::current()->canModifySegments())
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
			if (Security\Access::current()->canViewRc())
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
			if (Security\Access::current()->canModifyRc())
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
			if (Security\Access::current()->canViewBlacklist())
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
			if (Security\Access::current()->canModifyBlacklist())
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
			if (Security\Access::current()->canViewAds())
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
			if (Security\Access::current()->canModifyAds())
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
			if (Security\Access::current()->canViewSegments())
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
			if (Security\Access::current()->canModifySegments())
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
			if (Security\Access::current()->canModifySettings())
			{
				return;
			}

			$result->addError(new Error(Loc::getMessage('SENDER_COMMON_AJAX_CHECKER_ERROR_NO_WRITE_ACCESS')));
		};
	}
}