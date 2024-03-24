<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Dictionary\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class Element
 * @package Bitrix\Rest\Preset\Data
 */
class Element extends Base
{
	private const CACHE_DIR = '/rest/integration/element/';
	private const DEFAULT_DATA = [
		'application' => [
			'CODE' => 'application',
			'ELEMENT_CODE' => 'application',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1003_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1003_DESCRIPTION',
			'DESCRIPTION_FULL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1003_DESCRIPTION_FULL',
			'SECTION_CODE' => 'standard',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY_NEEDED' => 'D',
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'Y',
				'SCOPE' => [
					'crm',
				],
				'APPLICATION_DOWNLOAD_EXAMPLE_TYPE' => 'local_app',
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1003_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'out-hook' => [
			'CODE' => 'out-hook',
			'ELEMENT_CODE' => 'out-hook',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1002_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1002_DESCRIPTION',
			'SECTION_CODE' => 'standard',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY_NEEDED' => 'D',
				'OUTGOING_NEEDED' => 'Y',
				'SCOPE_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'OUTGOING_DOWNLOAD_EXAMPLE_TYPE' => 'out_hook',
				'SCOPE' => [],
				'DESCRIPTION_SCOPE' => [],
			],
		],
		'in-hook' => [
			'CODE' => 'in-hook',
			'ELEMENT_CODE' => 'in-hook',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1001_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1001_DESCRIPTION',
			'SECTION_CODE' => 'standard',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'CODE' => 'params',
						'METHOD' => 'profile',
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1001_INCOMING_QUERY_TITLE_ITEMS',
						'ITEMS' => [],
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'in_hook',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [],
				'DESCRIPTION_SCOPE' => [],
			],
		],
		'contact-sync' => [
			'CODE' => 'contact-sync',
			'ELEMENT_CODE' => 'contact-sync',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_DESCRIPTION',
			'SECTION_CODE' => 'external',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.contact.get',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'custom_sync',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_INCOMING_QUERY_INFORMATION_URL',
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php',
					],
				],
				'OUTGOING_NEEDED' => 'Y',
				'OUTGOING_DOWNLOAD_EXAMPLE_TYPE' => 'custom_sync',
				'OUTGOING_EVENTS' => [
					'ONCRMCONTACTUPDATE',
				],
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'DESCRIPTION_OUTGOING' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_DESCRIPTION_OUTGOING_DESCRIPTION',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_10_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'contact-add' => [
			'CODE' => 'contact-add',
			'SECTION_CODE' => 'migration',
			'ELEMENT_CODE' => 'contact-add',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_DESCRIPTION',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_TITLE',
						'METHOD' => 'crm.contact.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'CODE' => 'params',
						'ITEMS' => [
							[
								'title' => 'FIELDS[NAME]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'FIELDS[LAST_NAME]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_1',
							],
							[
								'title' => 'FIELDS[EMAIL][0][VALUE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_2',
								'value' => 'mail@example.com',
							],
							[
								'title' => 'FIELDS[EMAIL][0][VALUE_TYPE]',
								'value' => 'WORK',
							],
							[
								'title' => 'FIELDS[PHONE][0][VALUE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_3',
								'value' => '555888',
							],
							[
								'title' => 'FIELDS[PHONE][0][VALUE_TYPE]',
								'value' => 'WORK',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_add.php',
					],
				],
				'OUTGOING_NEEDED' => 'N',
				'WIDGET_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'OUTGOING_EVENTS' => [
					'ONCRMCONTACTADD',
					'ONCRMCONTACTUPDATE',
					'ONCRMCONTACTDELETE',
				],
				'DESCRIPTION_OUTGOING' => [],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_2_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'export-email-new-contact' => [
			'CODE' => 'export-email-new-contact',
			'ELEMENT_CODE' => 'export-email-new-contact',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_DESCRIPTION',
			'SECTION_CODE' => 'migration',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.contact.list',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'FILTER[>DATE_CREATE]',
								'value' => '2019-01-01',
							],
							[
								'title' => 'SELECT[]',
								'value' => 'NAME',
							],
							[
								'title' => 'SELECT[]',
								'value' => 'LAST_NAME',
							],
							[
								'title' => 'SELECT[]',
								'value' => 'EMAIL',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_list.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_3_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'user-add' => [
			'CODE' => 'user-add',
			'ELEMENT_CODE' => 'user-add',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_TITLE',
			'ACTIVE' => 'N',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_DESCRIPTION',
			'SECTION_CODE' => 'migration',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_TITLE',
						'METHOD' => 'user.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'CODE' => 'params',
						'ITEMS' => [
							[
								'title' => 'MESSAGE_TEXT',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'EMAIL',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_ITEMS_VALUE_1',
								'value' => 'mail@example.com',
							],
							[
								'title' => 'UF_DEPARTMENT[]',
								'value' => '1',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/users/user_add.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'user',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_4_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'lead-change-status' => [
			'CODE' => 'lead-change-status',
			'ELEMENT_CODE' => 'lead-change-status',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_DESCRIPTION',
			'SECTION_CODE' => 'auto-sales',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.lead.update',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
							[
								'title' => 'FIELDS[STATUS_ID]',
								'value' => 'CONVERTED',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_update.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_5_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'tasks-task-add' => [
			'CODE' => 'tasks-task-add',
			'ELEMENT_CODE' => 'tasks-task-add',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_TITLE',
			'ACTIVE' => 'N',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_DESCRIPTION',
			'SECTION_CODE' => 'auto-control',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'tasks.task.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'fields[TITLE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'fields[DESCRIPTION]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_ITEMS_VALUE_1',
							],
							[
								'title' => 'fields[RESPONSIBLE_ID]',
								'value' => '1',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/tasks/task/tasks/tasks_task_add.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'task',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_6_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'send-notify' => [
			'CODE' => 'send-notify',
			'ELEMENT_CODE' => 'send-notify',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_DESCRIPTION',
			'SECTION_CODE' => 'auto-control',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'im.notify',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'TO',
								'value' => '1',
							],
							[
								'title' => 'MESSAGE',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'TYPE',
								'value' => 'SYSTEM',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=93&CHAPTER_ID=07693',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'im',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_7_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'blogpost-add' => [
			'CODE' => 'blogpost-add',
			'ELEMENT_CODE' => 'blogpost-add',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_DESCRIPTION',
			'SECTION_CODE' => 'auto-control',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'log.blogpost.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'POST_TITLE',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'POST_MESSAGE',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_ITEMS_VALUE_1',
							],
							[
								'title' => 'DEST',
								'value' => 'UA',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/log/log_blogpost_add.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'log',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_8_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'deal-change-status' => [
			'CODE' => 'deal-change-status',
			'ELEMENT_CODE' => 'deal-change-status',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_DESCRIPTION',
			'SECTION_CODE' => 'auto-sales',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.deal.update',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
							[
								'title' => 'FIELDS[STAGE_ID]',
								'value' => 'WON',
							],
							[
								'title' => 'FIELDS[CLOSED]',
								'value' => '1',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_deal_update.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_9_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'tasks-task-get' => [
			'CODE' => 'tasks-task-get',
			'ELEMENT_CODE' => 'tasks-task-get',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_TITLE',
			'ACTIVE' => 'N',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_DESCRIPTION',
			'SECTION_CODE' => 'auto-control',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'tasks.task.get',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'taskId',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/tasks/task/tasks/tasks_task_get.php',
					],
				],
				'OUTGOING_NEEDED' => 'Y',
				'OUTGOING_EVENTS' => [
					'ONTASKUPDATE',
				],
				'WIDGET_NEEDED' => 'N',
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'task',
				],
				'DESCRIPTION_OUTGOING' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_DESCRIPTION_OUTGOING_DESCRIPTION',
				],
				'DESCRIPTION_WIDGET' => [],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_11_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'widget-contact-detail-tab' => [
			'CODE' => 'widget-contact-detail-tab',
			'ELEMENT_CODE' => 'widget-contact-detail-tab',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_DESCRIPTION',
			'SECTION_CODE' => 'widget',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.contact.get',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'Y',
				'WIDGET_LIST' => [
					'CRM_CONTACT_DETAIL_TAB',
				],
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'placement',
				],
				'DESCRIPTION_WIDGET' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_DESCRIPTION_WIDGET_DESCRIPTION',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_12_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'widget-contact-detail-activity' => [
			'CODE' => 'widget-contact-detail-activity',
			'ELEMENT_CODE' => 'widget-contact-detail-activity',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_DESCRIPTION',
			'SECTION_CODE' => 'widget',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.contact.update',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php',
					],
				],
				'WIDGET_LIST' => [
					'CRM_CONTACT_DETAIL_TAB',
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'Y',
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'placement',
				],
				'DESCRIPTION_WIDGET' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_DESCRIPTION_WIDGET_DESCRIPTION',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_13_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'widget-call-cart' => [
			'CODE' => 'widget-call-cart',
			'ELEMENT_CODE' => 'widget-call-cart',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_DESCRIPTION',
			'SECTION_CODE' => 'widget',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.lead.get',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_get.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'Y',
				'WIDGET_LIST' => [
					'CALL_CARD',
				],
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'telephony',
					'placement',
				],
				'DESCRIPTION_WIDGET' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_DESCRIPTION_WIDGET_DESCRIPTION',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_14_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'creat-invoice-by-tasks-time' => [
			'CODE' => 'creat-invoice-by-tasks-time',
			'ELEMENT_CODE' => 'creat-invoice-by-tasks-time',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_DESCRIPTION',
			'SECTION_CODE' => 'widget',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_INCOMING_QUERY_TITLE',
						'DESCRIPTION_METHOD' => [
							'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_DESCRIPTION_METHOD_DESCRIPTION',
						],
						'CODE' => 'params',
						'METHOD' => 'task.elapseditem.getlist',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_INCOMING_QUERY_INFORMATION_URL',
						'ITEMS' => [
							[
								'title' => 'FIELDS[TITLE]',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/tasks/task/elapseditem/getlist.php',
					],
				],
				'OUTGOING_NEEDED' => 'Y',
				'OUTGOING_EVENTS' => [],
				'WIDGET_LIST' => [
					'CRM_TASK_DETAIL',
				],
				'WIDGET_NEEDED' => 'N',
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'task',
				],
				'DESCRIPTION_OUTGOING' => [],
				'DESCRIPTION_WIDGET' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_DESCRIPTION_WIDGET_DESCRIPTION',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_15_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'custom-widget' => [
			'CODE' => 'custom-widget',
			'ELEMENT_CODE' => 'custom-widget',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_17_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_17_DESCRIPTION',
			'SECTION_CODE' => 'migration',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'METHOD' => 'crm.lead.get',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_17_INCOMING_QUERY_INFORMATION_URL',
						'DESCRIPTION_METHOD' => [],
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_17_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'ITEMS' => [
							[
								'title' => 'ID',
								'value' => '42',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_get.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'Y',
				'OUTGOING_EVENTS' => [
					'ONCRMLEADADD',
				],
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'task',
				],
				'DESCRIPTION_WIDGET' => [],
				'DESCRIPTION_SCOPE' => [],
			],
		],
		'bot-notify-staff' => [
			'CODE' => 'bot-notify-staff',
			'ELEMENT_CODE' => 'bot-notify-staff',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_18_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_18_DESCRIPTION',
			'SECTION_CODE' => 'chat-bot',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_18_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'imbot.message.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'DESCRIPTION_METHOD' => [],
						'ITEMS' => [
							[
								'title' => 'BOT_ID',
								'value' => '',
							],
							[
								'title' => 'CLIENT_ID',
								'value' => '',
							],
							[
								'title' => 'DIALOG_ID',
								'value' => '1',
							],
							[
								'title' => 'MESSAGE',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_18_INCOMING_QUERY_ITEMS_VALUE_0',
							],
						],
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'Y',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'imbot',
				],
				'DESCRIPTION_OUTGOING' => [],
				'DESCRIPTION_WIDGET' => [],
				'DESCRIPTION_SCOPE' => [],
			],
		],
		'lead-add' => [
			'CODE' => 'lead-add',
			'SECTION_CODE' => 'external',
			'ELEMENT_CODE' => 'lead-add',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_TITLE',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_DESCRIPTION',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'ADMIN_ONLY' => 'Y',
			'OPTIONS' => [
				'QUERY' => [
					[
						'CODE' => 'params',
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_TITLE',
						'METHOD' => 'crm.lead.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'QUERY_INFORMATION_URL.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_INFORMATION_URL',
						'ITEMS' => [
							[
								'title' => 'FIELDS[TITLE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_0',
							],
							[
								'title' => 'FIELDS[NAME]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_1',
							],
							[
								'title' => 'FIELDS[LAST_NAME]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_2',
							],
							[
								'title' => 'FIELDS[EMAIL][0][VALUE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_3',
								'value' => 'mail@example.com',
							],
							[
								'title' => 'FIELDS[EMAIL][0][VALUE_TYPE]',
								'value' => 'WORK',
							],
							[
								'title' => 'FIELDS[PHONE][0][VALUE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_4',
								'value' => '555888',
							],
							[
								'title' => 'FIELDS[PHONE][0][VALUE_TYPE]',
								'value' => 'WORK',
							],
						],
						'QUERY_INFORMATION_URL' => 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_add.php',
					],
				],
				'OUTGOING_NEEDED' => 'D',
				'WIDGET_NEEDED' => 'D',
				'BOT_NEEDED' => 'D',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
				],
				'DESCRIPTION_SCOPE' => [
					'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_1_DESCRIPTION_SCOPE_DESCRIPTION',
				],
			],
		],
		'bot-action-chat' => [
			'CODE' => 'bot-action-chat',
			'ELEMENT_CODE' => 'bot-action-chat',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_19_TITLE_MSGVER_1',
			'ACTIVE' => 'Y',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_19_DESCRIPTION',
			'SECTION_CODE' => 'chat-bot',
			'ICON_CLASS' => 'rest-integration-tile-img-hidden',
			'OPTIONS' => [
				'QUERY' => [
					[
						'ITEMS_TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_19_INCOMING_QUERY_TITLE',
						'CODE' => 'params',
						'METHOD' => 'crm.lead.add',
						'METHOD_DOWNLOAD_EXAMPLE_TYPE' => 'query',
						'DESCRIPTION_METHOD' => [
							'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_19_TITLE_MSGVER_1',
						],
						'ITEMS' => [
							[
								'title' => 'BOT_ID',
								'value' => '',
							],
							[
								'title' => 'CLIENT_ID',
								'value' => '',
							],
							[
								'title' => 'DIALOG_ID',
								'value' => 'chat1',
							],
							[
								'title' => 'FIELDS[TITLE]',
								'value.MESSAGE_CODE' => 'REST_INTEGRATION_PATTERNS_19_INCOMING_QUERY_ITEMS_VALUE_0',
							],
						],
					],
				],
				'OUTGOING_NEEDED' => 'Y',
				'OUTGOING_EVENTS' => [
					'ONCRMLEADADD',
					'ONCRMINVOICEDELETE',
				],
				'WIDGET_NEEDED' => 'N',
				'WIDGET_DOWNLOAD_EXAMPLE' => '',
				'BOT_NEEDED' => 'Y',
				'APPLICATION_NEEDED' => 'D',
				'SCOPE' => [
					'crm',
					'task',
				],
				'DESCRIPTION_OUTGOING' => [],
				'DESCRIPTION_WIDGET' => [],
				'DESCRIPTION_SCOPE' => [],
			],
		],
	];
	public const DEFAULT_APPLICATION = 'application';
	public const DEFAULT_IN_WEBHOOK = 'in-hook';
	public const DEFAULT_OUT_WEBHOOK = 'out-hook';

	/**
	 * @param $code string
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	public static function get($code) : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'item_' . $code . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$remoteDictionary = new Integration();
			$dictionary = $remoteDictionary->toArray();
			if (!empty($dictionary))
			{
				$dictionaryCode = array_column($dictionary, 'code');
				$key = array_search($code, $dictionaryCode, true);
				if ($key !== false)
				{
					$el = $dictionary[$key];
					if (!empty($el['option']))
					{
						$data = Json::decode(base64_decode($el['option']));
						if (is_array($data))
						{
							$data = static::changeMessage($data);
							$data['CODE'] = $data['ELEMENT_CODE'];
							$result = $data;
						}
					}
				}
			}
			if (empty($result) && !empty(static::DEFAULT_DATA[$code]))
			{
				$result = static::changeMessage(static::DEFAULT_DATA[$code]);
			}

			$cache->endDataCache($result);
		}

		return $result;
	}

	/**
	 * @param $sectionCode string
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	public static function getList($sectionCode) : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'section_' . $sectionCode . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$dictionary = new Integration();

			foreach ($dictionary as $el)
			{
				if (!empty($el['option']))
				{
					$data = Json::decode(base64_decode($el['option']));
					if (is_array($data) && $sectionCode === $data['SECTION_CODE'])
					{
						$data = static::changeMessage($data);
						$data['CODE'] = $data['ELEMENT_CODE'];
						$result[$data['CODE']] = $data;
					}
				}
			}

			if (empty($result))
			{
				foreach (static::DEFAULT_DATA as $data)
				{
					if ($sectionCode === $data['SECTION_CODE'])
					{
						$data = static::changeMessage($data);
						$result[$data['CODE']] = $data;
					}
				}
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}