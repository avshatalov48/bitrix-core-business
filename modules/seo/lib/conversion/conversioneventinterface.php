<?php

namespace Bitrix\Seo\Conversion;

interface ConversionEventInterface
{
	public function validate() : bool;
}