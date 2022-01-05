<?php

namespace Bitrix\Seo\LeadAds\Response\Builder;

use Bitrix\Seo\LeadAds\LeadAdsForm;

interface FormBuilderInterface
{
	public function buildForm(array $form) : LeadAdsForm;
}