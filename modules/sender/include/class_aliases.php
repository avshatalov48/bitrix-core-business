<?php

$classAliases = array(
	array('Bitrix\Sender\Connector\Base', 'Bitrix\Sender\Connector'),
	array('Bitrix\Sender\Connector\Manager', 'Bitrix\Sender\ConnectorManager'),
	array('Bitrix\Sender\Connector\Result', 'Bitrix\Sender\ConnectorResult'),

	array('Bitrix\Sender\Trigger\Base', 'Bitrix\Sender\Trigger'),
	array('Bitrix\Sender\Trigger\Manager', 'Bitrix\Sender\TriggerManager'),
	array('Bitrix\Sender\Trigger\Settings', 'Bitrix\Sender\TriggerSettings'),
	array('Bitrix\Sender\Trigger\TriggerConnector', 'Bitrix\Sender\TriggerConnector'),
	array('Bitrix\Sender\Trigger\TriggerConnectorClosed', 'Bitrix\Sender\TriggerConnectorClosed'),
);

foreach ($classAliases as $classAlias)
{
	class_alias($classAlias[0], $classAlias[1]);
}
