<?php

namespace Bitrix\UI\FeaturePromoter;

interface FeaturePromoterProvider
{
	public function getRendererParameters(): array;
}