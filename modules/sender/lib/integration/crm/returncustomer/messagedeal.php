<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration\Crm\Connectors\Helper;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Message;
use Bitrix\Sender\PostingRecipientTable;

/**
 * Class MessageDeal
 * @package Bitrix\Sender\Integration\Crm\ReturnCustomer;
 */
class MessageDeal extends MessageBase
{
	const CODE = self::CODE_RC_DEAL;

	/**
	 * Get name.
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_NAME_DEAL');
	}

	protected function setConfigurationOptions()
	{
		global $USER;
		if ($this->configuration->hasOptions())
		{
			return;
		}

		$this->configuration->setArrayOptions([
			[
				'type' => 'string',
				'code' => 'TITLE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_TITLE_DEAL'),
				'required' => true,
				'value' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_TITLE_DEAL_DEF', ['%date%' => PrettyDate::formatDate()]),
				'hint' => [
					'menu' => array_map(
						function ($item)
						{
							return array(
								'id' => '#' . ($item['CODE'] ?? '') . '#',
								'text' => $item['NAME'] ?? '',
								'title' => $item['DESC'] ?? '',
								'items' => isset($item['ITEMS']) ? array_map(
									function ($item)
									{
										return array(
											'id' => '#' . ($item['CODE'] ?? '') . '#',
											'text' => $item['NAME'] ?? '',
											'title' => $item['DESC'] ?? ''
										);
									}, $item['ITEMS']
								) : []
							);
						},
						array_merge(
							Helper::getPersonalizeFieldsFromConnectors(),
							PostingRecipientTable::getPersonalizeList()
						)
					),
				],
			],
			[
				'type' => Message\ConfigurationOption::TYPE_USER_LIST,
				'code' => 'ASSIGNED_BY',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_ASSIGNED_BY'),
				'required' => true,
				'hint' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_ASSIGNED_BY_HINT'),
				'value' => $USER ? $USER->getId() : '',
			],
			[
				'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
				'code' => 'CHECK_WORK_TIME',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_CHECK_WORK_TIME'),
				'hint' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_CHECK_WORK_TIME_HINT'),
				'required' => false,
			],
			[
				'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
				'code' => 'ALWAYS_ADD',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_ALWAYS_ADD_DEAL'),
				'required' => false,
				'hint' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_ALWAYS_ADD_HINT'),
			],
			[
				'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
				'code' => 'LINK_WITH_RESPONSIBLE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_LINK_DEAL_WITH_RESPONSIBLE'),
				'required' => false,
			],
			[
				'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
				'code' => 'FROM_PREVIOUS',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_CREATE_FROM_PREVIOUS'),
				'required' => false,
				'hint' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_CREATE_FROM_PREVIOUS_HINT'),
			],
			[
				'type' => Message\ConfigurationOption::TYPE_NUMBER,
				'code' => 'DEAL_DAYS_AGO',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_DEAL_DAYS_AGO'),
				'required' => false,
				'value'  => 1,
				'min_value'  => 0,
				'max_value'  => 366,
				'hint' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_DEAL_DAYS_AGO_HINT'),
			],
			[
				'type' => 'text',
				'code' => 'COMMENT',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_COMMENT'),
				'required' => false,
				'placeholder' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_COMMENT_HINT'),
			],
			[
				'type' => 'list',
				'code' => 'CATEGORY_ID',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_DEAL_CATEGORY_ID'),
				'required' => false,
				'show_in_filter' => true,
				'items' => array_merge(
					array_map(
						function ($category)
						{
							return [
								'code' => $category['ID'],
								'value' => $category['NAME'],
							];
						},
						DealCategory::getAll(true)
					),
					[[
						'code' => '',
						'value' => Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_CONFIG_DEAL_CATEGORY_ID_LAST')
					]]
				)
			],
		]);
	}
}
