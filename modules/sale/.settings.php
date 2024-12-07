<?php
return array(
	'controllers' => array(
		'value' => array(
			'defaultNamespace' => '\\Bitrix\\Sale\\Controller',
			'namespaces' => array(
				'\\Bitrix\\Sale\\Exchange\\Integration\\Controller' => 'integration',
			),
			'restIntegration' => [
				'enabled' => true,
			],
		),
		'readonly' => true,
	),
	'services' => [
		'value' => [
			'sale.basketReservationHistory' => [
				'className' => \Bitrix\Sale\Reservation\BasketReservationHistoryService::class,
			],
			'sale.basketReservation' => [
				'className' => \Bitrix\Sale\Reservation\BasketReservationService::class,
				// TODO: 'autowire' => true,
				'constructorParams' => static function()
				{
					return [
						new \Bitrix\Sale\Reservation\BasketReservationHistoryService(),
					];
				},
			],
			'sale.reservation.settings' => [
				'className' => \Bitrix\Sale\Reservation\Configuration\ReservationSettingsService::class,
			],
			'sale.paysystem.manager' => [
				'className' => \Bitrix\Sale\PaySystem\Manager::class
			],
			'sale.entityLabel' => [
				'className' => \Bitrix\Sale\Label\EntityLabelService::class,
			]
		],
	],
);
