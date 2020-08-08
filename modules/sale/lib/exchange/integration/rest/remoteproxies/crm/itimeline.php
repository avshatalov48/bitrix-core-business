<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


interface ITimeline
{
	public function onReceive($entityId, $entitTypeId, $settings);
}