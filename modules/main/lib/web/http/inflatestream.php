<?php

namespace Bitrix\Main\Web\Http;

class InflateStream extends Stream
{
	public function __construct($stream, $mode = 'r+')
	{
		parent::__construct($stream, $mode);

		// ZLIB or GZIP: use window=$W+32 for automatic header detection, so that both the formats can be recognized and decompressed; window=15+32=47 is the safer choice.
		stream_filter_append($this->resource, 'zlib.inflate', STREAM_FILTER_WRITE, ['window' => 47]);
	}
}
