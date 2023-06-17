<?php

use Bitrix\Main\Loader;
use Bitrix\Location\Entity\Address\FieldType;

if (!Loader::includeModule('location'))
{
	return [];
}

return array(
	array(
		array(
			FieldType::COUNTRY => 'Россия',
			FieldType::ADM_LEVEL_2 => 'Москва',
			FieldType::ADM_LEVEL_3 => 'Тверской',
			FieldType::LOCALITY => 'Москва',
			FieldType::SUB_LOCALITY_LEVEL_1 => 'Центральный административный округ',
			FieldType::STREET => 'Тверская улица',
			FieldType::BUILDING => '16',
			FieldType::ADDRESS_LINE_1 => 'Тверская улица, 16',
			//FieldType::ADDRESS_LINE_2 => 'ТЦ "Галерея актер"',
			FieldType::POSTAL_CODE => '125009'
		),

		array (
			0 =>
				array (
					'long_name' => 'ТЦ "Галерея актер"',
					'short_name' => 'ТЦ "Галерея актер"',
					'types' =>
						array (
							0 => 'premise',
						),
				),
			1 =>
				array (
					'long_name' => '16',
					'short_name' => '16',
					'types' =>
						array (
							0 => 'street_number',
						),
				),
			2 =>
				array (
					'long_name' => 'Тверская улица',
					'short_name' => 'Тверская ул.',
					'types' =>
						array (
							0 => 'route',
						),
				),
			3 =>
				array (
					'long_name' => 'Центральный административный округ',
					'short_name' => 'Центральный административный округ',
					'types' =>
						array (
							0 => 'sublocality_level_1',
							1 => 'sublocality',
							2 => 'political',
						),
				),
			4 =>
				array (
					'long_name' => 'Москва',
					'short_name' => 'Москва',
					'types' =>
						array (
							0 => 'locality',
							1 => 'political',
						),
				),
			5 =>
				array (
					'long_name' => 'Тверской',
					'short_name' => 'Тверской',
					'types' =>
						array (
							0 => 'administrative_area_level_3',
							1 => 'political',
						),
				),
			6 =>
				array (
					'long_name' => 'Москва',
					'short_name' => 'Москва',
					'types' =>
						array (
							0 => 'administrative_area_level_2',
							1 => 'political',
						),
				),
			7 =>
				array (
					'long_name' => 'Россия',
					'short_name' => 'RU',
					'types' =>
						array (
							0 => 'country',
							1 => 'political',
						),
				),
			8 =>
				array (
					'long_name' => '125009',
					'short_name' => '125009',
					'types' =>
						array (
							0 => 'postal_code',
						),
				),
		)
	)
);
