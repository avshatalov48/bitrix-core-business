<?php declare(strict_types = 1);

namespace Bitrix\Main\Localization;

use Bitrix\Main\Text\Encoding;
use php_user_filter as PhpStreamFilter;

class SteamConverter extends PhpStreamFilter
{
	public const FILTER_IDENTIFIER = 'bx.localization.converter';

	/** @var bool */
	protected static $isRegistered = false;

	/**
	 * @inheritDoc
	 * @link https://php.net/manual/en/php-user-filter.filter.php
	 * @param resource $in is a resource pointing to a bucket brigade which contains one or more bucket
	 * 	objects containing data to be filtered.
	 * @param resource $out is a resource pointing to a second bucket brigade into which your modified buckets should be placed.
	 * @param int &$consumed which must always be declared by reference, should be incremented by the length of
	 * 	the data which your filter reads in and alters. In most cases this means you will increment consumed
	 * 	by $bucket->datalen for each $bucket.
	 * @param bool $closing If the stream is in the process of closing (and therefore this is the last pass through
	 * 	the filterchain), the closing parameter will be set to TRUE.
	 * @return int
	 */
	public function filter($in, $out, &$consumed, $closing): int
	{
		$params = explode('/', str_replace(self::FILTER_IDENTIFIER.'.', '', $this->filtername));

		$sourceEncoding = $params[0];
		$targetEncoding = $params[1];

		while ($bucket = stream_bucket_make_writeable($in))
		{
			if ($sourceEncoding != $targetEncoding)
			{
				$bucket->data = Encoding::convertEncoding($bucket->data, $sourceEncoding, $targetEncoding);
			}
			$consumed += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}

		return PSFS_PASS_ON;
	}

	/**
	 * Registers stream filter.
	 * @return bool
	 */
	public static function register(): bool
	{
		self::$isRegistered = stream_filter_register(
			self::FILTER_IDENTIFIER.'.*',
			self::class
		);

		return self::$isRegistered;
	}

	/**
	 * Loads php file with realtime encoding convention.
	 *
	 * @param string $langPath File path to include.
	 * @param string $lang Target Encoding.
	 *
	 * @return mixed
	 */
	public static function include(string $langPath, string $lang)
	{
		if (!self::$isRegistered)
		{
			self::register();
		}

		$langPath = Translation::convertLangPath($langPath, $lang);

		list(, $targetEncoding, $sourceEncoding) = Translation::getEncodings($lang, $langPath);

		return include (
			'php://filter/read='.self::FILTER_IDENTIFIER.
			".{$sourceEncoding}%2F{$targetEncoding}".
			'/resource='.$langPath
		);
	}
}