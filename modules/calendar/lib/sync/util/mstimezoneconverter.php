<?php


namespace Bitrix\Calendar\Sync\Util;


use Bitrix\Calendar\Util;

class MsTimezoneConverter
{
	/**
	 * @return string[][]
	 */
	public static function getTimezoneMap(): array
	{
		return [
			'UTC-11' => [
				'Etc/GMT+11',
				'Pacific/Pago_Pago',
				'Pacific/Niue',
				'Pacific/Midway',
				'Etc/GMT+11',
			],
			'Aleutian Standard Time' => [
				'America/Adak',
				'America/Adak',
			],
			'Hawaiian Standard Time' => [
				'Pacific/Honolulu',
				'Pacific/Rarotonga',
				'Pacific/Tahiti',
				'Pacific/Johnston',
				'Pacific/Honolulu',
				'Etc/GMT+10',
			],
			'Marquesas Standard Time' => [
				'Pacific/Marquesas',
				'Pacific/Marquesas',
			],
			'Alaskan Standard Time' => [
				'America/Anchorage',
				'America/Anchorage',
				'America/Juneau',
				'America/Metlakatla',
				'America/Nome',
				'America/Sitka',
				'America/Yakutat',
			],
			'UTC-09' => [
				'Etc/GMT+9',
				'Pacific/Gambier',
				'Etc/GMT+9',
			],
			'Pacific Standard Time (Mexico)' => [
				'America/Tijuana',
				'America/Tijuana',
				'America/Santa_Isabel',
			],
			'UTC-08' => [
				'Etc/GMT+8',
				'Pacific/Pitcairn',
				'Etc/GMT+8',
			],
			'Pacific Standard Time' => [
				'America/Los_Angeles',
				'America/Vancouver',
				'America/Los_Angeles',
				'PST8PDT',
			],
			'US Mountain Standard Time' => [
				'America/Phoenix',
				'America/Hermosillo',
				'America/Phoenix',
				'Etc/GMT+7',
			],
			'Mountain Standard Time (Mexico)' => [
				'America/Chihuahua',
				'America/Chihuahua',
				'America/Mazatlan',
			],
			'Mountain Standard Time' => [
				'America/Denver',
				'America/Edmonton',
				'America/Cambridge_Bay',
				'America/Inuvik',
				'America/Yellowknife',
				'America/Ojinaga',
				'America/Denver',
				'America/Boise',
				'MST7MDT',
			],
			'Yukon Standard Time' => [
				'America/Whitehorse',
				'America/Whitehorse',
				'America/Creston',
				'America/Dawson',
				'America/Dawson_Creek',
				'America/Fort_Nelson',
			],
			'Central America Standard Time' => [
				'America/Guatemala',
				'America/Belize',
				'America/Costa_Rica',
				'Pacific/Galapagos',
				'America/Guatemala',
				'America/Tegucigalpa',
				'America/Managua',
				'America/El_Salvador',
				'Etc/GMT+6',
			],
			'Central Standard Time' => [
				'America/Chicago',
				'America/Winnipeg',
				'America/Rainy_River',
				'America/Rankin_Inlet',
				'America/Resolute',
				'America/Matamoros',
				'America/Chicago',
				'America/Indiana/Knox',
				'America/Indiana/Tell_City',
				'America/Menominee',
				'America/North_Dakota/Beulah',
				'America/North_Dakota/Center',
				'America/North_Dakota/New_Salem',
				'CST6CDT',
			],
			'Easter Island Standard Time' => [
				'Pacific/Easter',
				'Pacific/Easter',
			],
			'Central Standard Time (Mexico)' => [
				'America/Mexico_City',
				'America/Mexico_City',
				'America/Bahia_Banderas',
				'America/Merida',
				'America/Monterrey',
			],
			'Canada Central Standard Time' => [
				'America/Regina',
				'America/Regina',
				'America/Swift_Current',
			],
			'SA Pacific Standard Time' => [
				'America/Bogota',
				'America/Rio_Branco',
				'America/Eirunepe',
				'America/Coral_Harbour',
				'America/Bogota',
				'America/Guayaquil',
				'America/Jamaica',
				'America/Cayman',
				'America/Panama',
				'America/Lima',
				'Etc/GMT+5',
			],
			'Eastern Standard Time (Mexico)' => [
				'America/Cancun',
				'America/Cancun',
			],
			'Eastern Standard Time' => [
				'America/New_York',
				'America/Nassau',
				'America/Toronto',
				'America/Iqaluit',
				'America/Montreal',
				'America/Nipigon',
				'America/Pangnirtung',
				'America/Thunder_Bay',
				'America/New_York',
				'America/Detroit',
				'America/Indiana/Petersburg',
				'America/Indiana/Vincennes',
				'America/Indiana/Winamac',
				'America/Kentucky/Monticello',
				'America/Louisville',
				'EST5EDT',
			],
			'Haiti Standard Time' => [
				'America/Port-au-Prince',
				'America/Port-au-Prince',
			],
			'Cuba Standard Time' => [
				'America/Havana',
				'America/Havana',
			],
			'US Eastern Standard Time' => [
				'America/Indianapolis',
				'America/Indianapolis',
				'America/Indiana/Marengo',
				'America/Indiana/Vevay',
			],
			'Turks And Caicos Standard Time' => [
				'America/Grand_Turk',
				'America/Grand_Turk',
			],
			'Paraguay Standard Time' => [
				'America/Asuncion',
				'America/Asuncion',
			],
			'Atlantic Standard Time' => [
				'America/Halifax',
				'Atlantic/Bermuda',
				'America/Halifax',
				'America/Glace_Bay',
				'America/Goose_Bay',
				'America/Moncton',
				'America/Thule',
			],
			'Venezuela Standard Time' => [
				'America/Caracas',
				'America/Caracas',
			],
			'Central Brazilian Standard Time' => [
				'America/Cuiaba',
				'America/Cuiaba',
				'America/Campo_Grande',
			],
			'SA Western Standard Time' => [
				'America/La_Paz',
				'America/Antigua',
				'America/Anguilla',
				'America/Aruba',
				'America/Barbados',
				'America/St_Barthelemy',
				'America/La_Paz',
				'America/Kralendijk',
				'America/Manaus',
				'America/Boa_Vista',
				'America/Porto_Velho',
				'America/Blanc-Sablon',
				'America/Curacao',
				'America/Dominica',
				'America/Santo_Domingo',
				'America/Grenada',
				'America/Guadeloupe',
				'America/Guyana',
				'America/St_Kitts',
				'America/St_Lucia',
				'America/Marigot',
				'America/Martinique',
				'America/Montserrat',
				'America/Puerto_Rico',
				'America/Lower_Princes',
				'America/Port_of_Spain',
				'America/St_Vincent',
				'America/Tortola',
				'America/St_Thomas',
				'Etc/GMT+4',
			],
			'Pacific SA Standard Time' => [
				'America/Santiago',
				'America/Santiago',
			],
			'Newfoundland Standard Time' => [
				'America/St_Johns',
				'America/St_Johns',
			],
			'Tocantins Standard Time' => [
				'America/Araguaina',
				'America/Araguaina',
			],
			'E. South America Standard Time' => [
				'America/Sao_Paulo',
				'America/Sao_Paulo',
			],
			'SA Eastern Standard Time' => [
				'America/Cayenne',
				'Antarctica/Rothera',
				'Antarctica/Palmer',
				'America/Fortaleza',
				'America/Belem',
				'America/Maceio',
				'America/Recife',
				'America/Santarem',
				'Atlantic/Stanley',
				'America/Cayenne',
				'America/Paramaribo',
				'Etc/GMT+3',
			],
			'Argentina Standard Time' => [
				'America/Buenos_Aires',
				'America/Buenos_Aires',
				'America/Argentina/La_Rioja',
				'America/Argentina/Rio_Gallegos',
				'America/Argentina/Salta',
				'America/Argentina/San_Juan',
				'America/Argentina/San_Luis',
				'America/Argentina/Tucuman',
				'America/Argentina/Ushuaia',
				'America/Catamarca',
				'America/Cordoba',
				'America/Jujuy',
				'America/Mendoza',
			],
			'Greenland Standard Time' => [
				'America/Godthab',
				'America/Godthab',
			],
			'Montevideo Standard Time' => [
				'America/Montevideo',
				'America/Montevideo',
			],
			'Magallanes Standard Time' => [
				'America/Punta_Arenas',
				'America/Punta_Arenas',
			],
			'Saint Pierre Standard Time' => [
				'America/Miquelon',
				'America/Miquelon',
			],
			'Bahia Standard Time' => [
				'America/Bahia',
				'America/Bahia',
			],
			'UTC-02' => [
				'Etc/GMT+2',
				'America/Noronha',
				'Atlantic/South_Georgia',
				'Etc/GMT+2',
			],
			'Azores Standard Time' => [
				'Atlantic/Azores',
				'America/Scoresbysund',
				'Atlantic/Azores',
			],
			'Cape Verde Standard Time' => [
				'Atlantic/Cape_Verde',
				'Atlantic/Cape_Verde',
				'Etc/GMT+1',
			],
			'UTC' => [
				'Etc/GMT',
				'America/Danmarkshavn',
				'Etc/GMT',
				'Etc/UTC',
			],
			'GMT Standard Time' => [
				'Europe/London',
				'Atlantic/Canary',
				'Atlantic/Faeroe',
				'Europe/London',
				'Europe/Guernsey',
				'Europe/Dublin',
				'Europe/Isle_of_Man',
				'Europe/Jersey',
				'Europe/Lisbon',
				'Atlantic/Madeira',
			],
			'Greenwich Standard Time' => [
				'Atlantic/Reykjavik',
				'Africa/Ouagadougou',
				'Africa/Abidjan',
				'Africa/Accra',
				'Africa/Banjul',
				'Africa/Conakry',
				'Africa/Bissau',
				'Atlantic/Reykjavik',
				'Africa/Monrovia',
				'Africa/Bamako',
				'Africa/Nouakchott',
				'Atlantic/St_Helena',
				'Africa/Freetown',
				'Africa/Dakar',
				'Africa/Lome',
			],
			'Sao Tome Standard Time' => [
				'Africa/Sao_Tome',
				'Africa/Sao_Tome',
			],
			'Morocco Standard Time' => [
				'Africa/Casablanca',
				'Africa/El_Aaiun',
				'Africa/Casablanca',
			],
			'W. Europe Standard Time' => [
				'Europe/Berlin',
				'Europe/Andorra',
				'Europe/Vienna',
				'Europe/Zurich',
				'Europe/Berlin',
				'Europe/Busingen',
				'Europe/Gibraltar',
				'Europe/Rome',
				'Europe/Vaduz',
				'Europe/Luxembourg',
				'Europe/Monaco',
				'Europe/Malta',
				'Europe/Amsterdam',
				'Europe/Oslo',
				'Europe/Stockholm',
				'Arctic/Longyearbyen',
				'Europe/San_Marino',
				'Europe/Vatican',
			],
			'Central Europe Standard Time' => [
				'Europe/Budapest',
				'Europe/Tirane',
				'Europe/Prague',
				'Europe/Budapest',
				'Europe/Podgorica',
				'Europe/Belgrade',
				'Europe/Ljubljana',
				'Europe/Bratislava',
			],
			'Romance Standard Time' => [
				'Europe/Paris',
				'Europe/Brussels',
				'Europe/Copenhagen',
				'Europe/Madrid',
				'Africa/Ceuta',
				'Europe/Paris',
			],
			'Central European Standard Time' => [
				'Europe/Warsaw',
				'Europe/Sarajevo',
				'Europe/Zagreb',
				'Europe/Skopje',
				'Europe/Warsaw',
			],
			'W. Central Africa Standard Time' => [
				'Africa/Lagos',
				'Africa/Luanda',
				'Africa/Porto-Novo',
				'Africa/Kinshasa',
				'Africa/Bangui',
				'Africa/Brazzaville',
				'Africa/Douala',
				'Africa/Algiers',
				'Africa/Libreville',
				'Africa/Malabo',
				'Africa/Niamey',
				'Africa/Lagos',
				'Africa/Ndjamena',
				'Africa/Tunis',
				'Etc/GMT-1',
			],
			'Jordan Standard Time' => [
				'Asia/Amman',
				'Asia/Amman',
			],
			'GTB Standard Time' => [
				'Europe/Bucharest',
				'Asia/Nicosia',
				'Asia/Famagusta',
				'Europe/Athens',
				'Europe/Bucharest',
			],
			'Middle East Standard Time' => [
				'Asia/Beirut',
				'Asia/Beirut',
			],
			'Egypt Standard Time' => [
				'Africa/Cairo',
				'Africa/Cairo',
			],
			'E. Europe Standard Time' => [
				'Europe/Chisinau',
				'Europe/Chisinau',
			],
			'Syria Standard Time' => [
				'Asia/Damascus',
				'Asia/Damascus',
			],
			'West Bank Standard Time' => [
				'Asia/Hebron',
				'Asia/Hebron',
				'Asia/Gaza',
			],
			'South Africa Standard Time' => [
				'Africa/Johannesburg',
				'Africa/Bujumbura',
				'Africa/Gaborone',
				'Africa/Lubumbashi',
				'Africa/Maseru',
				'Africa/Blantyre',
				'Africa/Maputo',
				'Africa/Kigali',
				'Africa/Mbabane',
				'Africa/Johannesburg',
				'Africa/Lusaka',
				'Africa/Harare',
				'Etc/GMT-2',
			],
			'FLE Standard Time' => [
				'Europe/Kiev',
				'Europe/Mariehamn',
				'Europe/Sofia',
				'Europe/Tallinn',
				'Europe/Helsinki',
				'Europe/Vilnius',
				'Europe/Riga',
				'Europe/Kiev',
				'Europe/Uzhgorod',
				'Europe/Zaporozhye',
			],
			'Israel Standard Time' => [
				'Asia/Jerusalem',
				'Asia/Jerusalem',
			],
			'Kaliningrad Standard Time' => [
				'Europe/Kaliningrad',
				'Europe/Kaliningrad',
			],
			'Sudan Standard Time' => [
				'Africa/Khartoum',
				'Africa/Khartoum',
			],
			'Libya Standard Time' => [
				'Africa/Tripoli',
				'Africa/Tripoli',
			],
			'Namibia Standard Time' => [
				'Africa/Windhoek',
				'Africa/Windhoek',
			],
			'Arabic Standard Time' => [
				'Asia/Baghdad',
				'Asia/Baghdad',
			],
			'Turkey Standard Time' => [
				'Europe/Istanbul',
				'Europe/Istanbul',
			],
			'Arab Standard Time' => [
				'Asia/Riyadh',
				'Asia/Bahrain',
				'Asia/Kuwait',
				'Asia/Qatar',
				'Asia/Riyadh',
				'Asia/Aden',
			],
			'Belarus Standard Time' => [
				'Europe/Minsk',
				'Europe/Minsk',
			],
			'Russian Standard Time' => [
				'Europe/Moscow',
				'Europe/Moscow',
				'Europe/Kirov',
				'Europe/Simferopol',
			],
			'E. Africa Standard Time' => [
				'Africa/Nairobi',
				'Antarctica/Syowa',
				'Africa/Djibouti',
				'Africa/Asmera',
				'Africa/Addis_Ababa',
				'Africa/Nairobi',
				'Indian/Comoro',
				'Indian/Antananarivo',
				'Africa/Mogadishu',
				'Africa/Juba',
				'Africa/Dar_es_Salaam',
				'Africa/Kampala',
				'Indian/Mayotte',
				'Etc/GMT-3',
			],
			'Iran Standard Time' => [
				'Asia/Tehran',
				'Asia/Tehran',
			],
			'Arabian Standard Time' => [
				'Asia/Dubai',
				'Asia/Dubai',
				'Asia/Muscat',
				'Etc/GMT-4',
			],
			'Astrakhan Standard Time' => [
				'Europe/Astrakhan',
				'Europe/Astrakhan',
				'Europe/Ulyanovsk',
			],
			'Azerbaijan Standard Time' => [
				'Asia/Baku',
				'Asia/Baku',
			],
			'Russia Time Zone 3' => [
				'Europe/Samara',
				'Europe/Samara',
			],
			'Mauritius Standard Time' => [
				'Indian/Mauritius',
				'Indian/Mauritius',
				'Indian/Reunion',
				'Indian/Mahe',
			],
			'Saratov Standard Time' => [
				'Europe/Saratov',
				'Europe/Saratov',
			],
			'Georgian Standard Time' => [
				'Asia/Tbilisi',
				'Asia/Tbilisi',
			],
			'Volgograd Standard Time' => [
				'Europe/Volgograd',
				'Europe/Volgograd',
			],
			'Caucasus Standard Time' => [
				'Asia/Yerevan',
				'Asia/Yerevan',
			],
			'Afghanistan Standard Time' => [
				'Asia/Kabul',
				'Asia/Kabul',
			],
			'West Asia Standard Time' => [
				'Asia/Tashkent',
				'Antarctica/Mawson',
				'Asia/Oral',
				'Asia/Aqtau',
				'Asia/Aqtobe',
				'Asia/Atyrau',
				'Indian/Maldives',
				'Indian/Kerguelen',
				'Asia/Dushanbe',
				'Asia/Ashgabat',
				'Asia/Tashkent',
				'Asia/Samarkand',
				'Etc/GMT-5',
			],
			'Ekaterinburg Standard Time' => [
				'Asia/Yekaterinburg',
				'Asia/Yekaterinburg',
			],
			'Pakistan Standard Time' => [
				'Asia/Karachi',
				'Asia/Karachi',
			],
			'Qyzylorda Standard Time' => [
				'Asia/Qyzylorda',
				'Asia/Qyzylorda',
			],
			'India Standard Time' => [
				'Asia/Kolkata',
			],
			'Sri Lanka Standard Time' => [
				'Asia/Colombo',
			],
			'Nepal Standard Time' => [
				'Asia/Kathmandu',
			],
			'Central Asia Standard Time' => [
				'Asia/Almaty',
				'Antarctica/Vostok',
				'Asia/Urumqi',
				'Indian/Chagos',
				'Asia/Bishkek',
				'Asia/Almaty',
				'Asia/Qostanay',
				'Etc/GMT-6',
			],
			'Bangladesh Standard Time' => [
				'Asia/Dhaka',
				'Asia/Dhaka',
				'Asia/Thimphu',
			],
			'Omsk Standard Time' => [
				'Asia/Omsk',
				'Asia/Omsk',
			],
			'Myanmar Standard Time' => [
				'Asia/Rangoon',
				'Indian/Cocos',
				'Asia/Rangoon',
			],
			'SE Asia Standard Time' => [
				'Asia/Bangkok',
				'Antarctica/Davis',
				'Indian/Christmas',
				'Asia/Jakarta',
				'Asia/Pontianak',
				'Asia/Phnom_Penh',
				'Asia/Vientiane',
				'Asia/Bangkok',
				'Asia/Saigon',
				'Etc/GMT-7',
			],
			'Altai Standard Time' => [
				'Asia/Barnaul',
				'Asia/Barnaul',
			],
			'W. Mongolia Standard Time' => [
				'Asia/Hovd',
				'Asia/Hovd',
			],
			'North Asia Standard Time' => [
				'Asia/Krasnoyarsk',
				'Asia/Krasnoyarsk',
				'Asia/Novokuznetsk',
			],
			'N. Central Asia Standard Time' => [
				'Asia/Novosibirsk',
				'Asia/Novosibirsk',
			],
			'Tomsk Standard Time' => [
				'Asia/Tomsk',
				'Asia/Tomsk',
			],
			'China Standard Time' => [
				'Asia/Shanghai',
				'Asia/Shanghai',
				'Asia/Hong_Kong',
				'Asia/Macau',
			],
			'North Asia East Standard Time' => [
				'Asia/Irkutsk',
				'Asia/Irkutsk',
			],
			'Singapore Standard Time' => [
				'Asia/Singapore',
				'Asia/Brunei',
				'Asia/Makassar',
				'Asia/Kuala_Lumpur',
				'Asia/Kuching',
				'Asia/Manila',
				'Asia/Singapore',
				'Etc/GMT-8',
			],
			'W. Australia Standard Time' => [
				'Australia/Perth',
				'Australia/Perth',
			],
			'Taipei Standard Time' => [
				'Asia/Taipei',
				'Asia/Taipei',
			],
			'Ulaanbaatar Standard Time' => [
				'Asia/Ulaanbaatar',
				'Asia/Ulaanbaatar',
				'Asia/Choibalsan',
			],
			'Aus Central W. Standard Time' => [
				'Australia/Eucla',
				'Australia/Eucla',
			],
			'Transbaikal Standard Time' => [
				'Asia/Chita',
				'Asia/Chita',
			],
			'Tokyo Standard Time' => [
				'Asia/Tokyo',
				'Asia/Jayapura',
				'Asia/Tokyo',
				'Pacific/Palau',
				'Asia/Dili',
				'Etc/GMT-9',
			],
			'North Korea Standard Time' => [
				'Asia/Pyongyang',
				'Asia/Pyongyang',
			],
			'Korea Standard Time' => [
				'Asia/Seoul',
				'Asia/Seoul',
			],
			'Yakutsk Standard Time' => [
				'Asia/Yakutsk',
				'Asia/Yakutsk',
				'Asia/Khandyga',
			],
			'Cen. Australia Standard Time' => [
				'Australia/Adelaide',
				'Australia/Adelaide',
				'Australia/Broken_Hill',
			],
			'AUS Central Standard Time' => [
				'Australia/Darwin',
				'Australia/Darwin',
			],
			'E. Australia Standard Time' => [
				'Australia/Brisbane',
				'Australia/Brisbane',
				'Australia/Lindeman',
			],
			'AUS Eastern Standard Time' => [
				'Australia/Sydney',
				'Australia/Sydney',
				'Australia/Melbourne',
			],
			'West Pacific Standard Time' => [
				'Pacific/Port_Moresby',
				'Antarctica/DumontDUrville',
				'Pacific/Truk',
				'Pacific/Guam',
				'Pacific/Saipan',
				'Pacific/Port_Moresby',
				'Etc/GMT-10',
			],
			'Tasmania Standard Time' => [
				'Australia/Hobart',
				'Australia/Hobart',
				'Australia/Currie',
				'Antarctica/Macquarie',
			],
			'Vladivostok Standard Time' => [
				'Asia/Vladivostok',
				'Asia/Vladivostok',
				'Asia/Ust-Nera',
			],
			'Lord Howe Standard Time' => [
				'Australia/Lord_Howe',
				'Australia/Lord_Howe',
			],
			'Bougainville Standard Time' => [
				'Pacific/Bougainville',
				'Pacific/Bougainville',
			],
			'Russia Time Zone 10' => [
				'Asia/Srednekolymsk',
				'Asia/Srednekolymsk',
			],
			'Magadan Standard Time' => [
				'Asia/Magadan',
				'Asia/Magadan',
			],
			'Norfolk Standard Time' => [
				'Pacific/Norfolk',
				'Pacific/Norfolk',
			],
			'Sakhalin Standard Time' => [
				'Asia/Sakhalin',
				'Asia/Sakhalin',
			],
			'Central Pacific Standard Time' => [
				'Pacific/Guadalcanal',
				'Antarctica/Casey',
				'Pacific/Ponape',
				'Pacific/Kosrae',
				'Pacific/Noumea',
				'Pacific/Guadalcanal',
				'Pacific/Efate',
				'Etc/GMT-11',
			],
			'Russia Time Zone 11' => [
				'Asia/Kamchatka',
				'Asia/Kamchatka',
				'Asia/Anadyr',
			],
			'New Zealand Standard Time' => [
				'Pacific/Auckland',
				'Antarctica/McMurdo',
				'Pacific/Auckland',
			],
			'UTC+12' => [
				'Etc/GMT-12',
				'Pacific/Tarawa',
				'Pacific/Majuro',
				'Pacific/Kwajalein',
				'Pacific/Nauru',
				'Pacific/Funafuti',
				'Pacific/Wake',
				'Pacific/Wallis',
				'Etc/GMT-12',
			],
			'Fiji Standard Time' => [
				'Pacific/Fiji',
				'Pacific/Fiji',
			],
			'Chatham Islands Standard Time' => [
				'Pacific/Chatham',
				'Pacific/Chatham',
			],
			'UTC+13' => [
				'Etc/GMT-13',
				'Pacific/Enderbury',
				'Pacific/Fakaofo',
				'Etc/GMT-13',
			],
			'Tonga Standard Time' => [
				'Pacific/Tongatapu',
				'Pacific/Tongatapu',
			],
			'Samoa Standard Time' => [
				'Pacific/Apia',
				'Pacific/Apia',
			],
			'Line Islands Standard Time' => [
				'Pacific/Kiritimati',
				'Pacific/Kiritimati',
				'Etc/GMT-14',
			],

		];
	}

	/**
	 * @param string|null $timezone
	 * @return bool
	 */
	public static function hasTimezone(?string $timezone): bool
	{
		return array_key_exists($timezone, self::getTimezoneMap());
	}

	/**
	 * @param string $msTimezone
	 * @return array|null
	 */
	public static function getValidateTimezones(string $msTimezone): ?array
	{
		if (!self::isMsTimezoneValidate($msTimezone))
		{
			return null;
		}

		$result = [];
		$timezones = self::getTimezones($msTimezone);
		foreach ($timezones as $timezone)
		{
			if (Util::isTimezoneValid($timezone) && !in_array($timezone, $result, true))
			{
				$result[] = $timezone;
			}
		}

		return $result;
	}

	/**
	 * @param string $msTimezone
	 * @return string[]
	 */
	private static function getTimezones(string $msTimezone): array
	{
		return self::getTimezoneMap()[$msTimezone];
	}

	/**
	 * @param $timezone
	 * @return bool
	 */
	public static function isMsTimezoneValidate($timezone): bool
	{
		return (!is_null($timezone) && self::hasTimezone($timezone));
	}
}