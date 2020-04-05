<?php

namespace Bitrix\Sale;

interface IBusinessValueProvider
{
	public function getPersonTypeId();
	public function getBusinessValueProviderInstance($mapping);
}
