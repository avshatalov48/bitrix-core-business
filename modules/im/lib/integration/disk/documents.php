<?php

namespace Bitrix\Im\Integration\Disk;

use Bitrix\Main\Loader;

class Documents
{
	public const DISABLED = 'N';
	public const ENABLED = 'Y';
	public const LIMITED = 'L';

	/**
	 * Return feature status for the feature "Bitrix24.Documents".
	 * Returns:
	 *  - 'N' if feature is not available and should be hidden
	 *  - 'Y' if feature is fully available
	 *  - 'L.<infoheler article code>' - if feature is limited by portal's plan
	 *
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getDocumentsInCallStatus(): string
	{
		if (!Loader::includeModule('disk'))
		{
			return static::DISABLED;
		}
		if (!class_exists(\Bitrix\Disk\Integration\MessengerCall::class))
		{
			return static::DISABLED;
		}
		if (!\Bitrix\Disk\Integration\MessengerCall::isAvailableDocuments())
		{
			return static::DISABLED;
		}
		if (\Bitrix\Disk\Integration\MessengerCall::isEnabledDocuments())
		{
			return static::ENABLED;
		}

		return static::LIMITED . ":" . \Bitrix\Disk\Integration\MessengerCall::getInfoHelperCodeForDocuments();
	}

	/**
	 * Return feature status for the feature "Call resumes".
	 * Returns:
	 *  - 'N' if feature is not available and should not be displayed
	 *  - 'Y' if feature is fully available
	 *  - 'L.<infoheler article code>' - if feature is limited by portal's plan
	 *
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getResumesOfCallStatus(): string
	{
		if (!Loader::includeModule('disk'))
		{
			return static::DISABLED;
		}
		if (!class_exists(\Bitrix\Disk\Integration\MessengerCall::class))
		{
			return static::DISABLED;
		}
		if (!\Bitrix\Disk\Integration\MessengerCall::isAvailableDocuments())
		{
			return static::DISABLED;
		}
		if (\Bitrix\Disk\Integration\MessengerCall::isEnabledResumes())
		{
			return static::ENABLED;
		}

		return static::LIMITED . ":" . \Bitrix\Disk\Integration\MessengerCall::getInfoHelperCodeForResume();
	}
}