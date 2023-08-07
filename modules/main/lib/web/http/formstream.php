<?php

namespace Bitrix\Main\Web\Http;

class FormStream extends Stream
{
	public function __construct(array $data)
	{
		parent::__construct('php://temp', 'r+');

		$this->build($data);
		$this->rewind();
	}

	protected function build(array $data)
	{
		$this->write(http_build_query($data, '', '&'));
	}
}
