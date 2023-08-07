<?php

namespace Bitrix\Main\Web\Http;

class DechunkStream extends Stream
{
	public function __construct($stream, $mode = 'r+')
	{
		parent::__construct($stream, $mode);

		stream_filter_append($this->resource, 'dechunk', STREAM_FILTER_WRITE);
	}
}
