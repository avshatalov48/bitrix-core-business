<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class Timeline extends Rest\RemoteProxies\Base
	implements ITimeline
{
	public function onReceive($entityId, $entitTypeId, $settings)
	{
		return $this->cmd(
			Rest\Cmd\Registry::CRM_TIMELINE_ONRECEIVE_NAME, [
				'entityId'=>$entityId,
				'entitTypeId'=>$entitTypeId,
				'settings'=>$settings]
		)->call();
	}
}