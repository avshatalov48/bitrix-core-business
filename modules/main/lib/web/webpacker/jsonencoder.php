<?php

namespace Bitrix\Main\Web\WebPacker;

use Bitrix\Main;

class JsonEncoder
{
	/**
	 * Method-wrapper for Main\Web\Json::encode to support non-UTF8 encodings in building JS files (widgets, forms, etc)
	 * @see c849bc06c051 revision
	 *
	 * @param $data
	 * @return mixed
	 *
	 * @throws Main\ArgumentException
	 */
	public static function encode($data)
	{
		return Main\Web\Json::encode(
			$data,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
		);
	}
}