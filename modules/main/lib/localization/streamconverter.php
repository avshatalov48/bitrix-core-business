<?php declare(strict_types = 1);

namespace Bitrix\Main\Localization;

use Bitrix\Main\Text\Encoding;


class StreamConverter extends \php_user_filter
{
	public const FILTER_IDENTIFIER = 'bx.localization.converter';

	/** @var string */
	protected $code = '';

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
		while ($bucket = stream_bucket_make_writeable($in))
		{
			$this->code .= $bucket->data;
			$consumed += $bucket->datalen;
		}

		if ($closing || feof($this->stream))
		{
			$params = explode('/', str_replace(self::FILTER_IDENTIFIER.'.', '', $this->filtername));
			$sourceEncoding = $params[0];
			$targetEncoding = $params[1];

			if ($sourceEncoding != $targetEncoding)
			{
				$this->code = Encoding::convertEncoding($this->code, $sourceEncoding, $targetEncoding);
			}

			$bucket = stream_bucket_new($this->stream, $this->code);
			stream_bucket_append($out, $bucket);

			return PSFS_PASS_ON;
		}

		return PSFS_FEED_ME;
	}

	/**
	 * Registers stream filter.
	 * @return bool
	 */
	public static function register(): bool
	{
		return stream_filter_register(
			self::FILTER_IDENTIFIER.'.*',
			self::class
		);
	}

	/**
	 * Loads php file with realtime encoding convention.
	 *
	 * @param string $langPath File path to include.
	 * @param string $lang Source language.
	 * @param string $targetEncoding Target encoding.
	 * @param string $sourceEncoding Source encoding.
	 *
	 * @return mixed
	 */
	public static function include(string $langPath, string $lang, string $targetEncoding = '', string $sourceEncoding = '')
	{
		self::register();

		if (empty($targetEncoding) || empty($sourceEncoding))
		{
			$checkPath = Translation::convertLangPath($langPath, $lang);

			[, $checkTargetEncoding, $checkSourceEncoding] = Translation::getEncodings($lang, $checkPath);
			if (empty($targetEncoding))
			{
				$targetEncoding = $checkTargetEncoding;
			}
			if (empty($sourceEncoding))
			{
				$sourceEncoding = $checkSourceEncoding;
			}
		}

		return include (
			'php://filter/read='.self::FILTER_IDENTIFIER.
			".{$sourceEncoding}%2F{$targetEncoding}".
			'/resource='.$langPath
		);
	}
}