<?php

use Bitrix\Mail\Integration\UI\EntitySelector\AddressBookProvider;
use Bitrix\Mail\Integration\UI\EntitySelector\MailCrmRecipientProvider;
use Bitrix\Mail\Integration\UI\EntitySelector\MailUserRecipientAppearanceFilter;
use Bitrix\Mail\Integration\UI\EntitySelector\MailCrmRecipientAppearanceFilter;

return array(
	'controllers' => array(
		'value' => array(
			'namespaces' => array(
				'\\Bitrix\\Mail\\Controller' => 'api',
			),
			'defaultNamespace' => '\\Bitrix\\Mail\\Controller',
		),
		'readonly' => true,
	),
	'ui.selector' => [
		'value' => [
			'mail.selector'
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'filters' => [
				[
					'id' => 'mail.mailUserRecipientAppearanceFilter',
					'entityId' => 'user',
					'className' => MailUserRecipientAppearanceFilter::class,
				],
				[
					'id' => 'mail.mailCrmRecipientAppearanceFilter',
					'entityId' => 'contact',
					'className' => MailCrmRecipientAppearanceFilter::class,
				],
				[
					'id' => 'mail.mailCrmRecipientAppearanceFilter',
					'entityId' => 'company',
					'className' => MailCrmRecipientAppearanceFilter::class,
				],
				[
					'id' => 'mail.mailCrmRecipientAppearanceFilter',
					'entityId' => 'lead',
					'className' => MailCrmRecipientAppearanceFilter::class,
				],
			],

			'entities' => [
				[
					'entityId' => 'address_book',
					'provider' => [
						'moduleId' => 'mail',
						'className' => AddressBookProvider::class,
					],
				],
				[
					'entityId' => 'mail_crm_recipient',
					'provider' => [
						'moduleId' => 'mail',
						'className' => MailCrmRecipientProvider::class,
					],
				],
			]
		],
		'readonly' => true,
	],
);