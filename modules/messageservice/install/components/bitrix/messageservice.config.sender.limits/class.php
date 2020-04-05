<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MessageServiceConfigSenderLimitsComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		if (!Main\Loader::includeModule('messageservice'))
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_MODULE_NOT_INSTALLED'));
			return;
		}

		if (!\Bitrix\MessageService\Context\User::isAdmin())
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_PERMISSION_DENIED'));
			return;
		}

		if (isset($_POST['action_button_messageservice_limits'])
			&& !empty($_POST['FIELDS'])
			&& is_array($_POST['FIELDS'])
		)
		{
			foreach ($_POST['FIELDS'] as $limit => $value)
			{
				list ($senderId, $fromId) = explode(':', $limit);
				\Bitrix\MessageService\Sender\Limitation::setDailyLimit($senderId, $fromId, $value['limit']);
			}
		}

		$this->arResult['GRID_ID'] = 'messageservice_limits';
		$this->arResult['COLUMNS'] = array(
			array(
				'id' => 'sender',
				'name' => Loc::getMessage('MESSAGESERVICE_HEADER_SENDER'),
				'default' => true
			),
			array(
				'id' => 'from',
				'name' => Loc::getMessage('MESSAGESERVICE_HEADER_SENDER_FROM'),
				'default' => true
			),
			array(
				'id' => 'limit',
				'name' => Loc::getMessage('MESSAGESERVICE_HEADER_LIMIT'),
				'default' => true,
				'editable' => array(
					'TYPE' => \Bitrix\Main\Grid\Editor\Types::NUMBER
				)
			),
			array(
				'id' => 'current',
				'name' => Loc::getMessage('MESSAGESERVICE_HEADER_CURRENT'),
				'default' => true
			),
		);
		$rows = array();

		$senders = MessageService\Sender\SmsManager::getSenders();
		$currents = MessageService\Internal\Entity\MessageTable::getAllDailyCount();
		foreach ($senders as $sender)
		{
			if (!$sender->canUse())
				continue;
			$fromList = $sender->getFromList();

			foreach ($fromList as $from)
			{
				$id = $sender->getId().':'.$from['id'];
				$limit = MessageService\Sender\Limitation::getDailyLimit($sender->getId(), $from['id']);
				$rows[] = array(
					'id' => $id,
					'columns' => array(

						'id' => $id,
						'sender' => $sender->getName(),
						'from' => $from['name'],
						'limit' => $limit,
						'current' => isset($currents[$id]) ? $currents[$id] : 0
					),
					'data' => array(
						'~limit' => $limit,
					),
					'actions1' => array(array(
						'TITLE' => GetMessage('MESSAGESERVICE_ROW_EDIT'),
						'TEXT' => GetMessage('MESSAGESERVICE_ROW_EDIT'),
						//'ONCLICK' => "Grid.editSelected();",
						"ACTION" => "CALLBACK", "DATA" => array(array("JS" => "Grid.editSelected()"))
					))
				);
			}
		}

		$this->arResult['ROWS'] = $rows;

		$this->arResult['TZ_LIST'] = $this->getTimezones();
		$this->arResult['RETRY_TIME'] = \Bitrix\MessageService\Sender\Limitation::getRetryTime();

		if (empty($this->arResult['RETRY_TIME']['tz']))
		{
			$this->arResult['RETRY_TIME']['tz'] = (new DateTime())->getTimezone()->getName();
		}

		$this->IncludeComponentTemplate();
	}

	private function getTimezones()
	{
		$tz = \CTimeZone::GetZones();

		return $tz;
	}
}