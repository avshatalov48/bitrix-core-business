<?php

$urlProviderTechDomain = (new \Bitrix\Main\License\UrlProvider())->getTechDomain();

if (\Bitrix\Main\Application::getInstance()->getLicense()->isCis())
{
	$locationOsmServiceUrl = 'https://osm-ru-002.' . $urlProviderTechDomain;
	$locationOsmMapServiceUrl = 'https://osm-ru-001.' . $urlProviderTechDomain;
}
else
{
	$locationOsmServiceUrl = 'https://osm-de-002.' . $urlProviderTechDomain;
	$locationOsmMapServiceUrl = 'https://osm-de-001.' . $urlProviderTechDomain;
}

$location_default_option = [
	'log_level' => 400, // Error
	'osm_service_url' => $locationOsmServiceUrl,
	'osm_map_service_url' => $locationOsmMapServiceUrl,
];

unset($urlProviderTechDomain, $locationOsmServiceUrl, $locationOsmMapServiceUrl);
