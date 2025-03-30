<?php

namespace Bitrix\Bizproc\Result;

interface DeviceResultHandler
{
	public function handle(RenderedResult $renderedResult): array;
}
