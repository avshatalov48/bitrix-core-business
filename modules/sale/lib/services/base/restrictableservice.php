<?php

namespace Bitrix\Sale\Services\Base;

interface RestrictableService
{
	public function getStartupRestrictions(): RestrictionInfoCollection;
	public function getServiceId(): int;
}