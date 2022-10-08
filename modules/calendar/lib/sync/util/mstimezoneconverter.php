<?php


namespace Bitrix\Calendar\Sync\Util;


use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;

class MsTimezoneConverter
{
	/**
	 * @return string[][]
	 */
	public static function getTimezoneMap(): array
	{
		return [
			Loc::getMessage('MS_TIMEZONE_PAGO_PAGO') => ['Pago_Pago'],
			Loc::getMessage('MS_TIMEZONE_NIUE') => ['Pacific/Niue'],
			Loc::getMessage('MS_TIMEZONE_MIDWAY') => ['Pacific/Midway'],
			'UTC-11' => ['Pacific/Pago_Pago', 'Pacific/Niue', 'Pacific/Midway',],
			Loc::getMessage('MS_TIMEZONE_ADAK') => ['America/Adak'],
			'Aleutian Standard Time' => ['America/Adak',],
			Loc::getMessage('MS_TIMEZONE_HONOLULU') => ['Pacific/Honolulu'],
			Loc::getMessage('MS_TIMEZONE_RAROTONGA') => ['Pacific/Rarotonga'],
			Loc::getMessage('MS_TIMEZONE_TAHITI') => ['Pacific/Tahiti'],
			'Hawaiian Standard Time' => [
				'Pacific/Honolulu',
				'Pacific/Rarotonga',
				'Pacific/Tahiti',
			],
			Loc::getMessage('MS_TIMEZONE_MARQUESAS') => [
				'Pacific/Marquesas'
			],
			Loc::getMessage('MS_TIMEZONE_ANCHORAGE') => [
				'America/Anchorage'
			],
			Loc::getMessage('MS_TIMEZONE_JUNEAU') => [
				'America/Juneau'
			],
			Loc::getMessage('MS_TIMEZONE_METLAKATLA') => [
				'America/Metlakatla',
			],
			Loc::getMessage('MS_TIMEZONE_NOME') => [
				'America/Nome',
			],
			Loc::getMessage('MS_TIMEZONE_SITKA') => [
				'America/Sitka',
			],
			Loc::getMessage('MS_TIMEZONE_YAKUTAT') => [
				'America/Yakutat',
			],
			Loc::getMessage('MS_TIMEZONE_GAMBIER') => [
				'Pacific/Gambier',
			],
			Loc::getMessage('MS_TIMEZONE_TIJUANA') => [
				'America/Tijuana',
			],
			Loc::getMessage('MS_TIMEZONE_PITCAIRN') => [
				'Pacific/Pitcairn',
			],
			Loc::getMessage('MS_TIMEZONE_LOS_ANGELES') => [
				'America/Los_Angeles',
			],
			Loc::getMessage('MS_TIMEZONE_VANCOUVER') => [
				'America/Vancouver',
			],
			Loc::getMessage('MS_TIMEZONE_PHOENIX') => [
				'America/Phoenix',
			],
			Loc::getMessage('MS_TIMEZONE_HERMOSILLO') => [
				'America/Hermosillo',
			],
			Loc::getMessage('MS_TIMEZONE_CHIHUAHUA') => [
				'America/Chihuahua',
			],
			Loc::getMessage('MS_TIMEZONE_MAZATLAN') => [
				'America/Mazatlan',
			],
			Loc::getMessage('MS_TIMEZONE_DENVER') => [
				'America/Denver',
			],
			Loc::getMessage('MS_TIMEZONE_EDMONTON') => [
				'America/Edmonton',
			],
			Loc::getMessage('MS_TIMEZONE_CAMBRIDGE_BAY') => [
				'America/Cambridge_Bay',
			],
			Loc::getMessage('MS_TIMEZONE_INUVIK') => [
				'America/Inuvik'
			],
			Loc::getMessage('MS_TIMEZONE_YELLOWKNIFE') => [
				'America/Yellowknife'
			],
			Loc::getMessage('MS_TIMEZONE_OJINAGA') => [
				'America/Ojinaga'
			],
			Loc::getMessage('MS_TIMEZONE_BOISE') => [
				'America/Boise'
			],
			Loc::getMessage('MS_TIMEZONE_WHITEHORSE') => [
				'America/Whitehorse'
			],
			Loc::getMessage('MS_TIMEZONE_CRESTON') => [
				'America/Creston'
			],
			Loc::getMessage('MS_TIMEZONE_DAWSON') => [
				'America/Dawson'
			],
			Loc::getMessage('MS_TIMEZONE_DAWSON_CREEK') => [
				'America/Dawson_Creek'
			],
			Loc::getMessage('MS_TIMEZONE_FORT_NELSON') => [
				'America/Fort_Nelson'
			],
			Loc::getMessage('MS_TIMEZONE_GUATEMALA') => [
				'America/Guatemala'
			],
			Loc::getMessage('MS_TIMEZONE_BELIZE') => [
				'America/Belize',
			],
			Loc::getMessage('MS_TIMEZONE_COSTA_RICA') => [
				'America/Costa_Rica',
			],
			Loc::getMessage('MS_TIMEZONE_GALAPAGOS') => [
				'Pacific/Galapagos',
			],
			Loc::getMessage('MS_TIMEZONE_TEGUCIGALPA') => [
				'America/Tegucigalpa',
			],
			Loc::getMessage('MS_TIMEZONE_MANAGUA') => [
					'America/Managua'
			],
			Loc::getMessage('MS_TIMEZONE_EL_SALVADOR') => [
				'America/El_Salvador'
			],
			Loc::getMessage('MS_TIMEZONE_CHICAGO') => [
				'America/Chicago'
			],
			Loc::getMessage('MS_TIMEZONE_WINNIPEG') => [
				'America/Winnipeg'
			],
			Loc::getMessage('MS_TIMEZONE_RAINY_RIVER') => [
				'America/Rainy_River',
			],
			Loc::getMessage('MS_TIMEZONE_RANKIN_INLET') => [
				'America/Rankin_Inlet',
			],
			Loc::getMessage('MS_TIMEZONE_RESOLUTE') => [
				'America/Resolute',
			],
			Loc::getMessage('MS_TIMEZONE_MATAMOROS') => [
				'America/Matamoros',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_KNOX') => [
				'America/Indiana/Knox',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_TELL_CITY') => [
				'America/Indiana/Tell_City',
			],
			Loc::getMessage('MS_TIMEZONE_MENOMINEE') => [
				'America/Menominee',
			],
			Loc::getMessage('MS_TIMEZONE_NORTH_DAKOTA_BEULAH') => [
				'America/North_Dakota/Beulah',
			],
			Loc::getMessage('MS_TIMEZONE_NORTH_DAKOTA_CENTER') => [
				'America/North_Dakota/Center',
			],
			Loc::getMessage('MS_TIMEZONE_NORTH_DAKOTA_NEW_SALEM') => [
				'America/North_Dakota/New_Salem',
			],
			Loc::getMessage('MS_TIMEZONE_EASTER') => [
				'Pacific/Easter',
			],
			Loc::getMessage('MS_TIMEZONE_EASTER_ST') => [
				'Pacific/Easter',
			],
			Loc::getMessage('MS_TIMEZONE_EASTER_DST') => [
				'Pacific/Easter',
			],
			Loc::getMessage('MS_TIMEZONE_MEXICO_CITY') => [
				'America/Mexico_City',
			],
			Loc::getMessage('MS_TIMEZONE_BAHIA_BANDERAS') => [
					'America/Bahia_Banderas',
			],
			Loc::getMessage('MS_TIMEZONE_MERIDA') => [
					'America/Merida',
			],
			Loc::getMessage('MS_TIMEZONE_MONTERREY') => [
					'America/Monterrey',
			],
			Loc::getMessage('MS_TIMEZONE_REGINA') => [
				'America/Regina',
			],
			Loc::getMessage('MS_TIMEZONE_SWIFT_CURRENT') => [
					'America/Swift_Current',
			],
			Loc::getMessage('MS_TIMEZONE_BOGOTA') => [
					'America/Bogota',
			],
			Loc::getMessage('MS_TIMEZONE_RIO_BRANCO') => [
					'America/Rio_Branco',
			],
			Loc::getMessage('MS_TIMEZONE_EIRUNEPE') => [
				'America/Eirunepe'
			],
			Loc::getMessage('MS_TIMEZONE_GUAYAQUIL') => [
					'America/Guayaquil'
			],
			Loc::getMessage('MS_TIMEZONE_JAMAICA') => [
					'America/Jamaica'
			],
			Loc::getMessage('MS_TIMEZONE_CAYMAN') => [
					'America/Cayman'
			],
			Loc::getMessage('MS_TIMEZONE_PANAMA') => [
				'America/Panama',
			],
			Loc::getMessage('MS_TIMEZONE_LIMA') => [
					'America/Lima',
			],
			Loc::getMessage('MS_TIMEZONE_CANCUN') => [
					'America/Cancun',
			],
			Loc::getMessage('MS_TIMEZONE_NEW_YORK') => [
					'America/New_York',
			],
			Loc::getMessage('MS_TIMEZONE_NASSAU') => [
				'America/Nassau',
			],
			Loc::getMessage('MS_TIMEZONE_TORONTO') => [
					'America/Toronto',
			],
			Loc::getMessage('MS_TIMEZONE_IQALUIT') => [
					'America/Iqaluit',
			],
			Loc::getMessage('MS_TIMEZONE_NIPIGON') => [
					'America/Nipigon',
			],
			Loc::getMessage('MS_TIMEZONE_PANGNIRTUNG') => [
				'America/Pangnirtung',
			],
			Loc::getMessage('MS_TIMEZONE_THUNDER_BAY') => [
					'America/Thunder_Bay',
			],
			Loc::getMessage('MS_TIMEZONE_DETROIT') => [
					'America/Detroit',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_PETERSBURG') => [
					'America/Indiana/Petersburg',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_VINCENNES') => [
				'America/Indiana/Vincennes',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_WINAMAC') => [
					'America/Indiana/Winamac',
			],
			Loc::getMessage('MS_TIMEZONE_KENTUCKY_MONTICELLO') => [
					'America/Kentucky/Monticello',
			],
			Loc::getMessage('MS_TIMEZONE_PORT-AU-PRINCE') => [
					'America/Port-au-Prince',
			],

			Loc::getMessage('MS_TIMEZONE_HAVANA') => [
				'America/Havana',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_MARENGO') => [
					'America/Indiana/Marengo',
			],
			Loc::getMessage('MS_TIMEZONE_INDIANA_VEVAY') => [
					'America/Indiana/Vevay',
			],
			Loc::getMessage('MS_TIMEZONE_GRAND_TURK') => [
					'America/Grand_Turk',
			],
			Loc::getMessage('MS_TIMEZONE_ASUNCION') => [
					'America/Asuncion',
			],
			Loc::getMessage('MS_TIMEZONE_HALIFAX') => [
					'America/Halifax',
			],
			Loc::getMessage('MS_TIMEZONE_BERMUDA') => [
					'Atlantic/Bermuda',
			],
			Loc::getMessage('MS_TIMEZONE_GLACE_BAY') => [
					'America/Glace_Bay',
			],
			Loc::getMessage('MS_TIMEZONE_GOOSE_BAY') => [
					'America/Goose_Bay',
			],
			Loc::getMessage('MS_TIMEZONE_MONCTON') => [
					'America/Moncton',
			],
			Loc::getMessage('MS_TIMEZONE_THULE') => [
					'America/Thule',
			],
			Loc::getMessage('MS_TIMEZONE_CARACAS') => [
					'America/Caracas',
			],
			Loc::getMessage('MS_TIMEZONE_CUIABA') => [
					'America/Cuiaba',
			],
			Loc::getMessage('MS_TIMEZONE_CAMPO_GRANDE') => [
					'America/Campo_Grande',
			],
			Loc::getMessage('MS_TIMEZONE_LA_PAZ') => [
					'America/La_Paz',
			],
			Loc::getMessage('MS_TIMEZONE_ANTIGUA') => [
					'America/Antigua',
			],
			Loc::getMessage('MS_TIMEZONE_ANGUILLA') => [
					'America/Anguilla',
			],
			Loc::getMessage('MS_TIMEZONE_ARUBA') => [
					'America/Aruba',
			],
			Loc::getMessage('MS_TIMEZONE_BARBADOS') => [
					'America/Barbados',
			],
			Loc::getMessage('MS_TIMEZONE_ST_BARTHELEMY') => [
					'America/St_Barthelemy',
			],
			Loc::getMessage('MS_TIMEZONE_KRALENDIJK') => [
					'America/Kralendijk',
			],
			Loc::getMessage('MS_TIMEZONE_MANAUS') => [
					'America/Manaus',
			],
			Loc::getMessage('MS_TIMEZONE_BOA_VISTA') => [
					'America/Boa_Vista',
			],
			Loc::getMessage('MS_TIMEZONE_PORTO_VELHO') => [
					'America/Porto_Velho',
			],
			Loc::getMessage('MS_TIMEZONE_BLANC-SABLON') => [
					'America/Blanc-Sablon',
			],
			Loc::getMessage('MS_TIMEZONE_CURACAO') => [
					'America/Curacao',
			],
			Loc::getMessage('MS_TIMEZONE_DOMINICA') => [
					'America/Dominica',
			],
			Loc::getMessage('MS_TIMEZONE_SANTO_DOMINGO') => [
					'America/Santo_Domingo',
			],
			Loc::getMessage('MS_TIMEZONE_GRENADA') => [
					'America/Grenada',
			],
			Loc::getMessage('MS_TIMEZONE_GUADELOUPE') => [
					'America/Guadeloupe',
			],
			Loc::getMessage('MS_TIMEZONE_GUYANA') => [
					'America/Guyana',
			],

			Loc::getMessage('MS_TIMEZONE_ST_KITTS') => [
				'America/St_Kitts',
			],
			Loc::getMessage('MS_TIMEZONE_ST_LUCIA') => [
					'America/St_Lucia',
			],
			Loc::getMessage('MS_TIMEZONE_MARIGOT') => [
					'America/Marigot',
			],
			Loc::getMessage('MS_TIMEZONE_MARTINIQUE') => [
					'America/Martinique',
			],
			Loc::getMessage('MS_TIMEZONE_MONTSERRAT') => [
					'America/Montserrat',
			],
			Loc::getMessage('MS_TIMEZONE_PUERTO_RICO') => [
					'America/Puerto_Rico',
			],
			Loc::getMessage('MS_TIMEZONE_LOWER_PRINCES') => [
					'America/Lower_Princes',
			],
			Loc::getMessage('MS_TIMEZONE_PORT_OF_SPAIN') => [
					'America/Port_of_Spain',
			],
			Loc::getMessage('MS_TIMEZONE_ST_VINCENT') => [
					'America/St_Vincent',
			],
			Loc::getMessage('MS_TIMEZONE_TORTOLA') => [
					'America/Tortola',
			],
			Loc::getMessage('MS_TIMEZONE_ST_THOMAS') => [
					'America/St_Thomas',
			],
			Loc::getMessage('MS_TIMEZONE_SANTIAGO') => [
					'America/Santiago',
			],
			Loc::getMessage('MS_TIMEZONE_ST_JOHNS') => [
					'America/St_Johns',
			],
			Loc::getMessage('MS_TIMEZONE_ARAGUAINA') => [
					'America/Araguaina',
			],
			Loc::getMessage('MS_TIMEZONE_SAO_PAULO') => [
					'America/Sao_Paulo',
			],
			Loc::getMessage('MS_TIMEZONE_CAYENNE') => [
					'America/Cayenne',
			],
			Loc::getMessage('MS_TIMEZONE_ROTHERA') => [
				'Antarctica/Rothera',
			],
			Loc::getMessage('MS_TIMEZONE_PALMER') => [
				'Antarctica/Palmer',
			],
			Loc::getMessage('MS_TIMEZONE_FORTALEZA') => [
					'America/Fortaleza',
			],
			Loc::getMessage('MS_TIMEZONE_BELEM') => [
					'America/Belem',
			],
			Loc::getMessage('MS_TIMEZONE_MACEIO') => [
					'America/Maceio',
			],
			Loc::getMessage('MS_TIMEZONE_RECIFE') => [
					'America/Recife',
			],
			Loc::getMessage('MS_TIMEZONE_SANTAREM') => [
					'America/Santarem',
			],
			Loc::getMessage('MS_TIMEZONE_STANLEY') => [
					'Atlantic/Stanley',
			],
			Loc::getMessage('MS_TIMEZONE_PARAMARIBO') => [
					'America/Paramaribo',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_LA_RIOJA') => [
					'America/Argentina/La_Rioja',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_RIO_GALLEGOS') => [
					'America/Argentina/Rio_Gallegos',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_SALTA') => [
					'America/Argentina/Salta',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_SAN_JUAN') => [
					'America/Argentina/San_Juan',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_SAN_LUIS') => [
					'America/Argentina/San_Luis',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_TUCUMAN') => [
					'America/Argentina/Tucuman',
			],
			Loc::getMessage('MS_TIMEZONE_NORONHA') => [
				'America/Noronha',
			],
			Loc::getMessage('MS_TIMEZONE_NORONHA_DST') => [
				'America/Noronha',
			],
			Loc::getMessage('MS_TIMEZONE_NORONHA_ALT') => [
				'America/Noronha',
			],
			Loc::getMessage('MS_TIMEZONE_NORONHA_ST') => [
				'America/Noronha',
			],
			Loc::getMessage('MS_TIMEZONE_ARGENTINA_USHUAIA') => [
				'America/Argentina/Ushuaia',
			]
			,Loc::getMessage('MS_TIMEZONE_ARGENTINA_USHUAIA_DST') => [
				'America/Argentina/Ushuaia',
			]
			,Loc::getMessage('MS_TIMEZONE_ARGENTINA_USHUAIA_ALT') => [
				'America/Argentina/Ushuaia',
			]
			,Loc::getMessage('MS_TIMEZONE_ARGENTINA_USHUAIA_ST') => [
				'America/Argentina/Ushuaia',
			],
			Loc::getMessage('MS_TIMEZONE_MONTEVIDEO') => [
				'America/Montevideo',
			],
			Loc::getMessage('MS_TIMEZONE_PUNTA_ARENAS') => [
					'America/Punta_Arenas',
			],
			Loc::getMessage('MS_TIMEZONE_MIQUELON') => [
					'America/Miquelon',
			],
			Loc::getMessage('MS_TIMEZONE_BAHIA') => [
					'America/Bahia',
			],
			Loc::getMessage('MS_TIMEZONE_SOUTH_GEORGIA') => [
				'Atlantic/South_Georgia',
			],
			Loc::getMessage('MS_TIMEZONE_CANARY') => [
				'Atlantic/Canary',
			],
			Loc::getMessage('MS_TIMEZONE_GUERNSEY') => [
					'Europe/Guernsey',
			],
					Loc::getMessage('MS_TIMEZONE_ISLE_OF_MAN') => [
					'Europe/Isle_of_Man',
			],
			Loc::getMessage('MS_TIMEZONE_JERSEY') => [
					'Europe/Jersey',
			],
			Loc::getMessage('MS_TIMEZONE_LISBON') => [
					'Europe/Lisbon',
			],
			Loc::getMessage('MS_TIMEZONE_MADEIRA') => [
					'Atlantic/Madeira',
			],
			Loc::getMessage('MS_TIMEZONE_REYKJAVIK') => [
					'Atlantic/Reykjavik',
			],
			Loc::getMessage('MS_TIMEZONE_OUAGADOUGOU') => [
					'Africa/Ouagadougou',
			],
			Loc::getMessage('MS_TIMEZONE_ABIDJAN') => [
					'Africa/Abidjan',
			],
			Loc::getMessage('MS_TIMEZONE_ACCRA') => [
					'Africa/Accra',
			],
			Loc::getMessage('MS_TIMEZONE_BANJUL') => [
					'Africa/Banjul',
			],
			Loc::getMessage('MS_TIMEZONE_CONAKRY') => [
					'Africa/Conakry',
			],
			Loc::getMessage('MS_TIMEZONE_BISSAU') => [
					'Africa/Bissau',
			],
			Loc::getMessage('MS_TIMEZONE_MONROVIA') => [
					'Africa/Monrovia',
			],
			Loc::getMessage('MS_TIMEZONE_BAMAKO') => [
					'Africa/Bamako',
			],
			Loc::getMessage('MS_TIMEZONE_NOUAKCHOTT') => [
					'Africa/Nouakchott',
			],
			Loc::getMessage('MS_TIMEZONE_ST_HELENA') => [
					'Atlantic/St_Helena',
			],
			Loc::getMessage('MS_TIMEZONE_FREETOWN') => [
					'Africa/Freetown',
			],
			Loc::getMessage('MS_TIMEZONE_DAKAR') => [
					'Africa/Dakar',
			],
			Loc::getMessage('MS_TIMEZONE_LOME') => [
					'Africa/Lome',
			],
			Loc::getMessage('MS_TIMEZONE_SAO_TOME') => [
					'Africa/Sao_Tome',
			],
			Loc::getMessage('MS_TIMEZONE_CASABLANCA') => [
					'Africa/Casablanca',
			],
			Loc::getMessage('MS_TIMEZONE_EL_AAIUN') => [
					'Africa/El_Aaiun',
			],
			Loc::getMessage('MS_TIMEZONE_BERLIN') => [
					'Europe/Berlin',
			],
			Loc::getMessage('MS_TIMEZONE_ANDORRA') => [
					'Europe/Andorra',
			],
			Loc::getMessage('MS_TIMEZONE_VIENNA') => [
					'Europe/Vienna',
			],
			Loc::getMessage('MS_TIMEZONE_ZURICH') => [
					'Europe/Zurich',
			],
			Loc::getMessage('MS_TIMEZONE_BUSINGEN') => [
					'Europe/Busingen',
			],
			Loc::getMessage('MS_TIMEZONE_GIBRALTAR') => [
					'Europe/Gibraltar',
			],
			Loc::getMessage('MS_TIMEZONE_ROME') => [
					'Europe/Rome',
			],
					Loc::getMessage('MS_TIMEZONE_VADUZ') => [
					'Europe/Vaduz',
			],
			Loc::getMessage('MS_TIMEZONE_LUXEMBOURG') => [
					'Europe/Luxembourg',
			],
			Loc::getMessage('MS_TIMEZONE_MONACO') => [
					'Europe/Monaco',
			],
			Loc::getMessage('MS_TIMEZONE_MALTA') => [
					'Europe/Malta',
			],
			Loc::getMessage('MS_TIMEZONE_AMSTERDAM') => [
					'Europe/Amsterdam',
			],
			Loc::getMessage('MS_TIMEZONE_OSLO') => [
					'Europe/Oslo',
			],
			Loc::getMessage('MS_TIMEZONE_STOCKHOLM') => [
					'Europe/Stockholm',
			],
			Loc::getMessage('MS_TIMEZONE_LONGYEARBYEN') => [
					'Arctic/Longyearbyen',
			],
			Loc::getMessage('MS_TIMEZONE_SAN_MARINO') => [
					'Europe/San_Marino',
			],
			Loc::getMessage('MS_TIMEZONE_VATICAN') => [
					'Europe/Vatican',
			],
			Loc::getMessage('MS_TIMEZONE_BUDAPEST') => [
					'Europe/Budapest',
			],
			Loc::getMessage('MS_TIMEZONE_TIRANE') => [
					'Europe/Tirane',
			],
			Loc::getMessage('MS_TIMEZONE_PRAGUE') => [
					'Europe/Prague',
			],
			Loc::getMessage('MS_TIMEZONE_PODGORICA') => [
					'Europe/Podgorica',
			],
			Loc::getMessage('MS_TIMEZONE_BELGRADE') => [
					'Europe/Belgrade',
			],
			Loc::getMessage('MS_TIMEZONE_LJUBLJANA') => [
					'Europe/Ljubljana',
			],
			Loc::getMessage('MS_TIMEZONE_BRATISLAVA') => [
					'Europe/Bratislava',
			],
			Loc::getMessage('MS_TIMEZONE_PARIS') => [
					'Europe/Paris',
			],
			Loc::getMessage('MS_TIMEZONE_BRUSSELS') => [
					'Europe/Brussels',
			],
			Loc::getMessage('MS_TIMEZONE_COPENHAGEN') => [
					'Europe/Copenhagen',
			],
			Loc::getMessage('MS_TIMEZONE_MADRID') => [
					'Europe/Madrid',
			],
			Loc::getMessage('MS_TIMEZONE_CEUTA') => [
					'Africa/Ceuta',
			],
			Loc::getMessage('MS_TIMEZONE_WARSAW') => [
					'Europe/Warsaw',
			],
			Loc::getMessage('MS_TIMEZONE_SARAJEVO') => [
					'Europe/Sarajevo',
			],
			Loc::getMessage('MS_TIMEZONE_ZAGREB') => [
					'Europe/Zagreb',
			],
			Loc::getMessage('MS_TIMEZONE_SKOPJE') => [
					'Europe/Skopje',
			],
			Loc::getMessage('MS_TIMEZONE_LAGOS') => [
					'Africa/Lagos',
			],
			Loc::getMessage('MS_TIMEZONE_LUANDA') => [
					'Africa/Luanda',
			],
			Loc::getMessage('MS_TIMEZONE_PORTO-NOVO') => [
					'Africa/Porto-Novo',
			],
			Loc::getMessage('MS_TIMEZONE_KINSHASA') => [
					'Africa/Kinshasa',
			],
			Loc::getMessage('MS_TIMEZONE_BANGUI') => [
					'Africa/Bangui',
			],
			Loc::getMessage('MS_TIMEZONE_BRAZZAVILLE') => [
					'Africa/Brazzaville',
			],
			Loc::getMessage('MS_TIMEZONE_DOUALA') => [
					'Africa/Douala',
			],
			Loc::getMessage('MS_TIMEZONE_ALGIERS') => [
					'Africa/Algiers',
			],
			Loc::getMessage('MS_TIMEZONE_LIBREVILLE') => [
					'Africa/Libreville',
			],
			Loc::getMessage('MS_TIMEZONE_MALABO') => [
					'Africa/Malabo',
			],
			Loc::getMessage('MS_TIMEZONE_NIAMEY') => [
					'Africa/Niamey',
			],
			Loc::getMessage('MS_TIMEZONE_NDJAMENA') => [
					'Africa/Ndjamena',
			],
			Loc::getMessage('MS_TIMEZONE_TUNIS') => [
					'Africa/Tunis',
			],
			Loc::getMessage('MS_TIMEZONE_AMMAN') => [
					'Asia/Amman',
			],
			Loc::getMessage('MS_TIMEZONE_BUCHAREST') => [
					'Europe/Bucharest',
			],
			Loc::getMessage('MS_TIMEZONE_NICOSIA') => [
					'Asia/Nicosia',
			],
			Loc::getMessage('MS_TIMEZONE_FAMAGUSTA') => [
					'Asia/Famagusta',
			],
			Loc::getMessage('MS_TIMEZONE_ATHENS') => [
					'Europe/Athens',
			],
			Loc::getMessage('MS_TIMEZONE_BEIRUT') => [
					'Asia/Beirut',
			],
			Loc::getMessage('MS_TIMEZONE_CAIRO') => [
					'Africa/Cairo',
			],
			Loc::getMessage('MS_TIMEZONE_CHISINAU') => [
					'Europe/Chisinau',
			],
			Loc::getMessage('MS_TIMEZONE_DAMASCUS') => [
					'Asia/Damascus',
			],
			Loc::getMessage('MS_TIMEZONE_HEBRON') => [
					'Asia/Hebron',
			],
			Loc::getMessage('MS_TIMEZONE_GAZA') => [
					'Asia/Gaza',
			],
			Loc::getMessage('MS_TIMEZONE_JOHANNESBURG') => [
					'Africa/Johannesburg',
			],
			Loc::getMessage('MS_TIMEZONE_BUJUMBURA') => [
					'Africa/Bujumbura',
			],
			Loc::getMessage('MS_TIMEZONE_GABORONE') => [
					'Africa/Gaborone',
			],
			Loc::getMessage('MS_TIMEZONE_LUBUMBASHI') => [
					'Africa/Lubumbashi',
			],
			Loc::getMessage('MS_TIMEZONE_MASERU') => [
					'Africa/Maseru',
			],
					Loc::getMessage('MS_TIMEZONE_BLANTYRE') => [
					'Africa/Blantyre',
			],
			Loc::getMessage('MS_TIMEZONE_MAPUTO') => [
					'Africa/Maputo',
			],
			Loc::getMessage('MS_TIMEZONE_KIGALI') => [
					'Africa/Kigali',
			],
			Loc::getMessage('MS_TIMEZONE_MBABANE') => [
					'Africa/Mbabane',
			],
			Loc::getMessage('MS_TIMEZONE_LUSAKA') => [
					'Africa/Lusaka',
			],
			Loc::getMessage('MS_TIMEZONE_HARARE') => [
					'Africa/Harare',
			],
			Loc::getMessage('MS_TIMEZONE_KIEV') => [
					'Europe/Kiev',
			],
			Loc::getMessage('MS_TIMEZONE_MARIEHAMN') => [
					'Europe/Mariehamn',
			],
			Loc::getMessage('MS_TIMEZONE_SOFIA') => [
					'Europe/Sofia',
			],
			Loc::getMessage('MS_TIMEZONE_TALLINN') => [
					'Europe/Tallinn',
			],
			Loc::getMessage('MS_TIMEZONE_HELSINKI') => [
					'Europe/Helsinki',
			],
			Loc::getMessage('MS_TIMEZONE_VILNIUS') => [
					'Europe/Vilnius',
			],
			Loc::getMessage('MS_TIMEZONE_RIGA') => [
					'Europe/Riga',
			],
			Loc::getMessage('MS_TIMEZONE_UZHGOROD') => [
					'Europe/Uzhgorod',
			],
			Loc::getMessage('MS_TIMEZONE_ZAPOROZHYE') => [
					'Europe/Zaporozhye',
			],
			Loc::getMessage('MS_TIMEZONE_JERUSALEM') => [
					'Asia/Jerusalem',
			],
			Loc::getMessage('MS_TIMEZONE_KALININGRAD') => [
					'Europe/Kaliningrad',
			],
			Loc::getMessage('MS_TIMEZONE_KHARTOUM') => [
					'Africa/Khartoum',
			],
			Loc::getMessage('MS_TIMEZONE_TRIPOLI') => [
					'Africa/Tripoli',
			],
			Loc::getMessage('MS_TIMEZONE_WINDHOEK') => [
					'Africa/Windhoek',
			],
			Loc::getMessage('MS_TIMEZONE_BAGHDAD') => [
					'Asia/Baghdad',
			],
			Loc::getMessage('MS_TIMEZONE_ISTANBUL') => [
					'Europe/Istanbul',
			],
			Loc::getMessage('MS_TIMEZONE_RIYADH') => [
					'Asia/Riyadh',
			],
			Loc::getMessage('MS_TIMEZONE_BAHRAIN') => [
					'Asia/Bahrain',
			],
			Loc::getMessage('MS_TIMEZONE_KUWAIT') => [
					'Asia/Kuwait',
			],
			Loc::getMessage('MS_TIMEZONE_QATAR') => [
					'Asia/Qatar',
			],
			Loc::getMessage('MS_TIMEZONE_ADEN') => [
					'Asia/Aden',
			],
			Loc::getMessage('MS_TIMEZONE_MINSK') => [
					'Europe/Minsk',
			],
			Loc::getMessage('MS_TIMEZONE_BOUGAINVILLE') => [
				'Pacific/Bougainville',
			],
			Loc::getMessage('MS_TIMEZONE_SREDNEKOLYMSK') => [
					'Asia/Srednekolymsk',
			],
					Loc::getMessage('MS_TIMEZONE_UST-NERA') => [
					'Asia/Ust-Nera',
			],
					Loc::getMessage('MS_TIMEZONE_SAIPAN') => [
					'Pacific/Saipan',
			],
			Loc::getMessage('MS_TIMEZONE_HOBART') => [
					'Australia/Hobart',
			],
					Loc::getMessage('MS_TIMEZONE_KHANDYGA') => [
					'Asia/Khandyga',
			],
			Loc::getMessage('MS_TIMEZONE_ADELAIDE') => [
					'Australia/Adelaide',
			],
			Loc::getMessage('MS_TIMEZONE_BROKEN_HILL') => [
					'Australia/Broken_Hill',
			],
			Loc::getMessage('MS_TIMEZONE_DARWIN') => [
					'Australia/Darwin',
			],
			Loc::getMessage('MS_TIMEZONE_BRISBANE') => [
					'Australia/Brisbane',
			],
			Loc::getMessage('MS_TIMEZONE_LINDEMAN') => [
					'Australia/Lindeman',
			],
			Loc::getMessage('MS_TIMEZONE_SYDNEY') => [
					'Australia/Sydney',
			],
			Loc::getMessage('MS_TIMEZONE_MELBOURNE') => [
					'Australia/Melbourne',
			],
			Loc::getMessage('MS_TIMEZONE_PORT_MORESBY') => [
					'Pacific/Port_Moresby',
			],
			Loc::getMessage('MS_TIMEZONE_SEOUL') => [
					'Asia/Seoul',
			],
			Loc::getMessage('MS_TIMEZONE_DILI') => [
					'Asia/Dili',
			],
			Loc::getMessage('MS_TIMEZONE_EUCLA') => [
					'Australia/Eucla',
			],
			Loc::getMessage('MS_TIMEZONE_CHITA') => [
					'Asia/Chita',
			],
			Loc::getMessage('MS_TIMEZONE_TOKYO') => [
					'Asia/Tokyo',
			],
			Loc::getMessage('MS_TIMEZONE_JAYAPURA') => [
					'Asia/Jayapura',
			],
			Loc::getMessage('MS_TIMEZONE_ULAANBAATAR') => [
				'Asia/Ulaanbaatar',
			],
			Loc::getMessage('MS_TIMEZONE_MAKASSAR') => [
					'Asia/Makassar',
			],
			Loc::getMessage('MS_TIMEZONE_KUALA_LUMPUR') => [
					'Asia/Kuala_Lumpur',
			],
			Loc::getMessage('MS_TIMEZONE_KUCHING') => [
					'Asia/Kuching',
			],
			Loc::getMessage('MS_TIMEZONE_MANILA') => [
					'Asia/Manila',
			],
			Loc::getMessage('MS_TIMEZONE_PERTH') => [
					'Australia/Perth',
			],
			Loc::getMessage('MS_TIMEZONE_TOMSK') => [
					'Asia/Tomsk',
			],
			Loc::getMessage('MS_TIMEZONE_SHANGHAI') => [
					'Asia/Shanghai',
			],
			Loc::getMessage('MS_TIMEZONE_NOVOKUZNETSK') => [
					'Asia/Novokuznetsk',
			],
			Loc::getMessage('MS_TIMEZONE_JAKARTA') => [
					'Asia/Jakarta',
			],
			Loc::getMessage('MS_TIMEZONE_PONTIANAK') => [
					'Asia/Pontianak',
			],
			Loc::getMessage('MS_TIMEZONE_PHNOM_PENH') => [
					'Asia/Phnom_Penh',
			],
			Loc::getMessage('MS_TIMEZONE_VIENTIANE') => [
					'Asia/Vientiane',
			],
			Loc::getMessage('MS_TIMEZONE_BARNAUL') => [
					'Asia/Barnaul',
			],
			Loc::getMessage('MS_TIMEZONE_BANGKOK') => [
					'Asia/Bangkok',
			],
			Loc::getMessage('MS_TIMEZONE_URUMQI') => [
					'Asia/Urumqi',
			],
			Loc::getMessage('MS_TIMEZONE_CHAGOS') => [
					'Indian/Chagos',
			],
			Loc::getMessage('MS_TIMEZONE_BISHKEK') => [
					'Asia/Bishkek',
			],
			Loc::getMessage('MS_TIMEZONE_QOSTANAY') => [
					'Asia/Qostanay',
			],
			Loc::getMessage('MS_TIMEZONE_DHAKA') => [
					'Asia/Dhaka',
			],
			Loc::getMessage('MS_TIMEZONE_THIMPHU') => [
					'Asia/Thimphu',
			],
			Loc::getMessage('MS_TIMEZONE_KERGUELEN') => [
					'Indian/Kerguelen',
			],
			Loc::getMessage('MS_TIMEZONE_DUSHANBE') => [
					'Asia/Dushanbe',
			],
			Loc::getMessage('MS_TIMEZONE_ASHGABAT') => [
					'Asia/Ashgabat',
			],
			Loc::getMessage('MS_TIMEZONE_SAMARKAND') => [
					'Asia/Samarkand',
			],
			Loc::getMessage('MS_TIMEZONE_ORAL') => [
					'Asia/Oral',
			],
			Loc::getMessage('MS_TIMEZONE_YEREVAN') => [
					'Asia/Yerevan',
			],
			Loc::getMessage('MS_TIMEZONE_KABUL') => [
					'Asia/Kabul',
			],
			Loc::getMessage('MS_TIMEZONE_TASHKENT') => [
					'Asia/Tashkent',
			],
			Loc::getMessage('MS_TIMEZONE_MAHE') => [
					'Indian/Mahe',
			],
			Loc::getMessage('MS_TIMEZONE_SARATOV') => [
					'Europe/Saratov',
			],
			Loc::getMessage('MS_TIMEZONE_TBILISI') => [
					'Asia/Tbilisi',
			],
			Loc::getMessage('MS_TIMEZONE_DJIBOUTI') => [
					'Africa/Djibouti',
			],
			Loc::getMessage('MS_TIMEZONE_ADDIS_ABABA') => [
					'Africa/Addis_Ababa',
			],
			Loc::getMessage('MS_TIMEZONE_COMORO') => [
					'Indian/Comoro',
			],
			Loc::getMessage('MS_TIMEZONE_ANTANANARIVO') => [
					'Indian/Antananarivo',
			],
			Loc::getMessage('MS_TIMEZONE_MOGADISHU') => [
					'Africa/Mogadishu',
			],
			Loc::getMessage('MS_TIMEZONE_JUBA') => [
					'Africa/Juba',
			],
			Loc::getMessage('MS_TIMEZONE_DAR_ES_SALAAM') => [
					'Africa/Dar_es_Salaam',
			],
			Loc::getMessage('MS_TIMEZONE_KAMPALA') => [
					'Africa/Kampala',
			],
			Loc::getMessage('MS_TIMEZONE_MAYOTTE') => [
					'Indian/Mayotte',
			],
			Loc::getMessage('MS_TIMEZONE_TEHRAN') => [
					'Asia/Tehran',
			],
			Loc::getMessage('MS_TIMEZONE_DUBAI') => [
					'Asia/Dubai',
			],
			Loc::getMessage('MS_TIMEZONE_MUSCAT') => [
					'Asia/Muscat',
			],
			Loc::getMessage('MS_TIMEZONE_ASTRAKHAN') => [
					'Europe/Astrakhan',
			],
			Loc::getMessage('MS_TIMEZONE_ULYANOVSK') => [
					'Europe/Ulyanovsk',
			],
			Loc::getMessage('MS_TIMEZONE_BAKU') => [
					'Asia/Baku',
			],
			Loc::getMessage('MS_TIMEZONE_KIROV') => [
					'Europe/Kirov',
			],
			Loc::getMessage('MS_TIMEZONE_SIMFEROPOL') => [
					'Europe/Simferopol',
			],
			Loc::getMessage('MS_TIMEZONE_NAIROBI') => [
					'Africa/Nairobi',
			],
			Loc::getMessage('MS_TIMEZONE_SCORESBYSUND') => [
					'America/Scoresbysund',
			],
			Loc::getMessage('MS_TIMEZONE_ENDERBURY') => [
					'Pacific/Enderbury',
			],
			Loc::getMessage('MS_TIMEZONE_FAKAOFO') => [
					'Pacific/Fakaofo',
			],
			Loc::getMessage('MS_TIMEZONE_TONGATAPU') => [
					'Pacific/Tongatapu',
			],
			Loc::getMessage('MS_TIMEZONE_FUNAFUTI') => [
					'Pacific/Funafuti',
			],
			Loc::getMessage('MS_TIMEZONE_TARAWA') => [
					'Pacific/Tarawa',
			],
			Loc::getMessage('MS_TIMEZONE_MAJURO') => [
					'Pacific/Majuro',
			],
			Loc::getMessage('MS_TIMEZONE_KWAJALEIN') => [
					'Pacific/Kwajalein',
			],
			Loc::getMessage('MS_TIMEZONE_AUCKLAND') => [
					'Pacific/Auckland',
			],
			Loc::getMessage('MS_TIMEZONE_MCMURDO') => [
					'Antarctica/McMurdo',
			],
			Loc::getMessage('MS_TIMEZONE_NOUMEA') => [
					'Pacific/Noumea',
			],
			Loc::getMessage('MS_TIMEZONE_EFATE') => [
					'Pacific/Efate',
			],
			Loc::getMessage('MS_TIMEZONE_GUADALCANAL') => [
					'Pacific/Guadalcanal',
			],
			'Marquesas Standard Time' => [
				'Pacific/Marquesas',
			],
			'Alaskan Standard Time' => [
				'America/Anchorage',
				'America/Juneau',
				'America/Metlakatla',
				'America/Nome',
				'America/Sitka',
				'America/Yakutat',
			],
			'UTC-09' => [
				'Pacific/Gambier',
			],
			'Pacific Standard Time (Mexico)' => [
				'America/Tijuana',
			],
			'UTC-08' => [
				'Pacific/Pitcairn',
			],
			'Pacific Standard Time' => [
				'America/Los_Angeles',
				'America/Vancouver',
			],
			'US Mountain Standard Time' => [
				'America/Phoenix',
				'America/Hermosillo',
			],
			'Mountain Standard Time (Mexico)' => [
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
				'America/Boise',
			],
			'Yukon Standard Time' => [
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
				'America/Tegucigalpa',
				'America/Managua',
				'America/El_Salvador',
			],
			'Central Standard Time' => [
				'America/Chicago',
				'America/Winnipeg',
				'America/Rainy_River',
				'America/Rankin_Inlet',
				'America/Resolute',
				'America/Matamoros',
				'America/Indiana/Knox',
				'America/Indiana/Tell_City',
				'America/Menominee',
				'America/North_Dakota/Beulah',
				'America/North_Dakota/Center',
				'America/North_Dakota/New_Salem',
			],
			'Easter Island Standard Time' => [
				'Pacific/Easter',
			],
			'Central Standard Time (Mexico)' => [
				'America/Mexico_City',
				'America/Bahia_Banderas',
				'America/Merida',
				'America/Monterrey',
			],
			'Canada Central Standard Time' => [
				'America/Regina',
				'America/Swift_Current',
			],
			'SA Pacific Standard Time' => [
				'America/Bogota',
				'America/Rio_Branco',
				'America/Eirunepe',
				'America/Guayaquil',
				'America/Jamaica',
				'America/Cayman',
				'America/Panama',
				'America/Lima',
			],
			'Eastern Standard Time (Mexico)' => [
				'America/Cancun',
			],
			'Eastern Standard Time' => [
				'America/New_York',
				'America/Nassau',
				'America/Toronto',
				'America/Iqaluit',
				'America/Nipigon',
				'America/Pangnirtung',
				'America/Thunder_Bay',
				'America/Detroit',
				'America/Indiana/Petersburg',
				'America/Indiana/Vincennes',
				'America/Indiana/Winamac',
				'America/Kentucky/Monticello',
			],
			'Haiti Standard Time' => [
				'America/Port-au-Prince',
			],
			'Cuba Standard Time' => [
				'America/Havana',
			],
			'US Eastern Standard Time' => [
				'America/Indiana/Marengo',
				'America/Indiana/Vevay',
			],
			'Turks And Caicos Standard Time' => [
				'America/Grand_Turk',
			],
			'Paraguay Standard Time' => [
				'America/Asuncion',
			],
			'Atlantic Standard Time' => [
				'America/Halifax',
				'Atlantic/Bermuda',
				'America/Glace_Bay',
				'America/Goose_Bay',
				'America/Moncton',
				'America/Thule',
			],
			'Venezuela Standard Time' => [
				'America/Caracas',
			],
			'Central Brazilian Standard Time' => [
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
			],
			'Pacific SA Standard Time' => [
				'America/Santiago',
			],
			'Newfoundland Standard Time' => [
				'America/St_Johns',
			],
			'Tocantins Standard Time' => [
				'America/Araguaina',
			],
			'E. South America Standard Time' => [
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
				'America/Paramaribo',
			],
			'Argentina Standard Time' => [
				'America/Argentina/La_Rioja',
				'America/Argentina/Rio_Gallegos',
				'America/Argentina/Salta',
				'America/Argentina/San_Juan',
				'America/Argentina/San_Luis',
				'America/Argentina/Tucuman',
				'America/Argentina/Ushuaia',
			],
			'Montevideo Standard Time' => [
				'America/Montevideo',
			],
			'Magallanes Standard Time' => [
				'America/Punta_Arenas',
			],
			'Saint Pierre Standard Time' => [
				'America/Miquelon',
			],
			'Bahia Standard Time' => [
				'America/Bahia',
			],
			'UTC-02' => [
				'America/Noronha',
				'Atlantic/South_Georgia',
			],
			'Azores Standard Time' => [
				'Atlantic/Azores',
				'America/Scoresbysund',
			],
			'Cape Verde Standard Time' => [
				'Atlantic/Cape_Verde',
			],
			'UTC' => [
				'America/Danmarkshavn',
			],
			'GMT Standard Time' => [
				'Europe/London',
				'Atlantic/Canary',
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
			],
			'Morocco Standard Time' => [
				'Africa/Casablanca',
				'Africa/El_Aaiun',
			],
			'W. Europe Standard Time' => [
				'Europe/Berlin',
				'Europe/Andorra',
				'Europe/Vienna',
				'Europe/Zurich',
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
			],
			'Central European Standard Time' => [
				'Europe/Warsaw',
				'Europe/Sarajevo',
				'Europe/Zagreb',
				'Europe/Skopje',
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
				'Africa/Ndjamena',
				'Africa/Tunis',
			],
			'Jordan Standard Time' => [
				'Asia/Amman',
			],
			'GTB Standard Time' => [
				'Europe/Bucharest',
				'Asia/Nicosia',
				'Asia/Famagusta',
				'Europe/Athens',
			],
			'Middle East Standard Time' => [
				'Asia/Beirut',
			],
			'Egypt Standard Time' => [
				'Africa/Cairo',
			],
			'E. Europe Standard Time' => [
				'Europe/Chisinau',
			],
			'Syria Standard Time' => [
				'Asia/Damascus',
			],
			'West Bank Standard Time' => [
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
				'Africa/Lusaka',
				'Africa/Harare',
			],
			'FLE Standard Time' => [
				'Europe/Kiev',
				'Europe/Mariehamn',
				'Europe/Sofia',
				'Europe/Tallinn',
				'Europe/Helsinki',
				'Europe/Vilnius',
				'Europe/Riga',
				'Europe/Uzhgorod',
				'Europe/Zaporozhye',
			],
			'Israel Standard Time' => [
				'Asia/Jerusalem',
			],
			'Kaliningrad Standard Time' => [
				'Europe/Kaliningrad',
			],
			'Sudan Standard Time' => [
				'Africa/Khartoum',
			],
			'Libya Standard Time' => [
				'Africa/Tripoli',
			],
			'Namibia Standard Time' => [
				'Africa/Windhoek',
			],
			'Arabic Standard Time' => [
				'Asia/Baghdad',
			],
			'Turkey Standard Time' => [
				'Europe/Istanbul',
			],
			'Arab Standard Time' => [
				'Asia/Riyadh',
				'Asia/Bahrain',
				'Asia/Kuwait',
				'Asia/Qatar',
				'Asia/Aden',
			],
			'Belarus Standard Time' => [
				'Europe/Minsk',
			],
			'Russian Standard Time' => [
				'Europe/Moscow',
				'Europe/Kirov',
				'Europe/Simferopol',
			],
			'E. Africa Standard Time' => [
				'Africa/Nairobi',
				'Antarctica/Syowa',
				'Africa/Djibouti',
				'Africa/Addis_Ababa',
				'Indian/Comoro',
				'Indian/Antananarivo',
				'Africa/Mogadishu',
				'Africa/Juba',
				'Africa/Dar_es_Salaam',
				'Africa/Kampala',
				'Indian/Mayotte',
			],
			'Iran Standard Time' => [
				'Asia/Tehran',
			],
			'Arabian Standard Time' => [
				'Asia/Dubai',
				'Asia/Muscat',
			],
			'Astrakhan Standard Time' => [
				'Europe/Astrakhan',
				'Europe/Ulyanovsk',
			],
			'Azerbaijan Standard Time' => [
				'Asia/Baku',
			],
			'Russia Time Zone 3' => [
				'Europe/Samara',
			],
			'Mauritius Standard Time' => [
				'Indian/Mauritius',
				'Indian/Reunion',
				'Indian/Mahe',
			],
			'Saratov Standard Time' => [
				'Europe/Saratov',
			],
			'Georgian Standard Time' => [
				'Asia/Tbilisi',
			],
			'Volgograd Standard Time' => [
				'Europe/Volgograd',
			],
			'Caucasus Standard Time' => [
				'Asia/Yerevan',
			],
			'Afghanistan Standard Time' => [
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
				'Asia/Samarkand',
			],
			'Ekaterinburg Standard Time' => [
				'Asia/Yekaterinburg',
			],
			'Pakistan Standard Time' => [
				'Asia/Karachi',
			],
			'Qyzylorda Standard Time' => [
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
				'Asia/Qostanay',
			],
			'Bangladesh Standard Time' => [
				'Asia/Dhaka',
				'Asia/Thimphu',
			],
			'Omsk Standard Time' => [
				'Asia/Omsk',
			],
			'Myanmar Standard Time' => [
				'Indian/Cocos',
			],
			'SE Asia Standard Time' => [
				'Asia/Bangkok',
				'Antarctica/Davis',
				'Indian/Christmas',
				'Asia/Jakarta',
				'Asia/Pontianak',
				'Asia/Phnom_Penh',
				'Asia/Vientiane',
			],
			'Altai Standard Time' => [
				'Asia/Barnaul',
			],
			'W. Mongolia Standard Time' => [
				'Asia/Hovd',
			],
			'North Asia Standard Time' => [
				'Asia/Krasnoyarsk',
				'Asia/Novokuznetsk',
			],
			'N. Central Asia Standard Time' => [
				'Asia/Novosibirsk',
			],
			'Tomsk Standard Time' => [
				'Asia/Tomsk',
			],
			'China Standard Time' => [
				'Asia/Shanghai',
				'Asia/Hong_Kong',
				'Asia/Macau',
			],
			'North Asia East Standard Time' => [
				'Asia/Irkutsk',
			],
			'Singapore Standard Time' => [
				'Asia/Singapore',
				'Asia/Brunei',
				'Asia/Makassar',
				'Asia/Kuala_Lumpur',
				'Asia/Kuching',
				'Asia/Manila',
			],
			'W. Australia Standard Time' => [
				'Australia/Perth',
			],
			'Taipei Standard Time' => [
				'Asia/Taipei',
			],
			'Ulaanbaatar Standard Time' => [
				'Asia/Ulaanbaatar',
				'Asia/Choibalsan',
			],
			'Aus Central W. Standard Time' => [
				'Australia/Eucla',
			],
			'Transbaikal Standard Time' => [
				'Asia/Chita',
			],
			'Tokyo Standard Time' => [
				'Asia/Tokyo',
				'Asia/Jayapura',
				'Pacific/Palau',
				'Asia/Dili',
			],
			'North Korea Standard Time' => [
				'Asia/Pyongyang',
			],
			'Korea Standard Time' => [
				'Asia/Seoul',
			],
			'Yakutsk Standard Time' => [
				'Asia/Yakutsk',
				'Asia/Khandyga',
			],
			'Cen. Australia Standard Time' => [
				'Australia/Adelaide',
				'Australia/Broken_Hill',
			],
			'AUS Central Standard Time' => [
				'Australia/Darwin',
			],
			'E. Australia Standard Time' => [
				'Australia/Brisbane',
				'Australia/Lindeman',
			],
			'AUS Eastern Standard Time' => [
				'Australia/Sydney',
				'Australia/Melbourne',
			],
			'West Pacific Standard Time' => [
				'Pacific/Port_Moresby',
				'Antarctica/DumontDUrville',
				'Pacific/Guam',
				'Pacific/Saipan',
			],
			'Tasmania Standard Time' => [
				'Australia/Hobart',
				'Antarctica/Macquarie',
			],
			'Vladivostok Standard Time' => [
				'Asia/Vladivostok',
				'Asia/Ust-Nera',
			],
			'Lord Howe Standard Time' => [
				'Australia/Lord_Howe',
			],
			'Bougainville Standard Time' => [
				'Pacific/Bougainville',
			],
			'Russia Time Zone 10' => [
				'Asia/Srednekolymsk',
			],
			'Magadan Standard Time' => [
				'Asia/Magadan',
			],
			'Norfolk Standard Time' => [
				'Pacific/Norfolk',
			],
			'Sakhalin Standard Time' => [
				'Asia/Sakhalin',
			],
			'Central Pacific Standard Time' => [
				'Pacific/Guadalcanal',
				'Antarctica/Casey',
				'Pacific/Kosrae',
				'Pacific/Noumea',
				'Pacific/Efate',
			],
			'Russia Time Zone 11' => [
				'Asia/Kamchatka',
				'Asia/Anadyr',
			],
			'New Zealand Standard Time' => [
				'Pacific/Auckland',
				'Antarctica/McMurdo',
			],
			'UTC+12' => [
				'Pacific/Tarawa',
				'Pacific/Majuro',
				'Pacific/Kwajalein',
				'Pacific/Nauru',
				'Pacific/Funafuti',
				'Pacific/Wake',
				'Pacific/Wallis',
			],
			'Fiji Standard Time' => [
				'Pacific/Fiji',
			],
			'Chatham Islands Standard Time' => [
				'Pacific/Chatham',
			],
			'UTC+13' => [
				'Pacific/Enderbury',
				'Pacific/Fakaofo',
			],
			'Tonga Standard Time' => [
				'Pacific/Tongatapu',
			],
			'Samoa Standard Time' => [
				'Pacific/Apia',
			],
			'Line Islands Standard Time' => [
				'Pacific/Kiritimati',
			],
			//specific old names of timezones
			'America/Buenos_Aires' => [
				'America/Argentina/Buenos_Aires',
			],
			'America/Catamarca' => [
				'America/Argentina/Catamarca',
			],
			'America/Cordoba' => [
				'America/Argentina/Cordoba',
			],
			'America/Jujuy' => [
				'America/Argentina/Jujuy',
			],
			'America/Indianapolis' => [
				'America/Indiana/Indianapolis',
			],
			'America/Louisville' => [
				'America/Kentucky/Louisville',
			],
			'America/Mendoza' => [
				'America/Argentina/Mendoza',
			],
			'America/Santa_Isabel' => [
				'America/Tijuana',
			],
			'America/Shiprock' => [
				'America/Regina',
			],
			'Asia/Chongqing' => [
				'Asia/Choibalsan',
			],
			'Asia/Chungking' => [
				'Asia/Choibalsan',
			],
			'Asia/Dacca' => [
				'Asia/Dhaka',
			],
			'Asia/Harbin' => [
				'Asia/Shanghai',
			],
			'Asia/Istanbul' => [
				'Europe/Istanbul',
			],
			'Asia/Kashgar' => [
				'Asia/Urumqi',
			],
			'Asia/Katmandu' => [
				'Asia/Kathmandu',
			],
			'Asia/Macao' => [
				'Asia/Shanghai',
			],
			'Asia/Rangoon' => [
				'Asia/Yangon',
			],
			'Asia/Tel_Aviv' => [
				'Asia/Jerusalem',
			],
			'Asia/Thimbu' => [
				'Asia/Thimphu',
			],
			'Asia/Ujung_Pandang' => [
				'Asia/Makassar',
			],
			'Asia/Ulan_Bator' => [
				'Asia/Ulaanbaatar',
			],
			'Atlantic/Faeroe' => [
				'Atlantic/Faroe',
			],
			'Atlantic/Jan_Mayen' => [
				'Arctic/Longyearbyen',
			],
			'Australia/ACT' => [
				'Australia/Broken_Hill',
			],
			'Australia/Canberra' => [
				'Australia/Brisbane',
			],
			'Australia/Currie' => [
				'Australia/Hobar',
			],
			'Australia/LHI' => [
				'Australia/Lord_Howe',
			],
			'Australia/North' => [
				'Australia/Darwin',
			],
			'Australia/NSW' => [
				'Australia/Broken_Hill',
			],
			'Australia/Queensland' => [
				'Australia/Brisbane',
			],
			'Australia/South' => [
				'Australia/Adelaide',
			],
			'Australia/Tasmania' => [
				'Australia/Hobart',
			],
			'Australia/Brisbane' => [
				'Australia/Brisbane',
			],
			'Australia/West' => [
				'Australia/Perth',
			],
			'Australia/Yancowinna' => [
				'Australia/Adelaide',
			],
			'Brazil/Acre' => [
				'America/Rio_Branco',
			],
			'Brazil/DeNoronha' => [
				'America/Noronha',
			],
			'Brazil/East' => [
				'America/Sao_Paulo',
			],
			'Brazil/West' => [
				'America/Manaus',
			],
			'Canada/Atlantic' => [
				'America/Halifax',
			],
			'Canada/Central' => [
				'America/Winnipeg',
			],
			'Canada/Eastern' => [
				'America/Toronto',
			],
			'Canada/Mountain' => [
				'America/Edmonton',
			],
			'Canada/Newfoundland' => [
				'America/St_Johns',
			],
			'Canada/Pacific' => [
				'America/Vancouver',
			],
			'Canada/Saskatchewan' => [
				'America/Regina',
			],
			'Canada/Yukon' => [
				'America/Whitehorse',
			],
			'Chile/Continental' => [
				'America/Santiago',
			],
			'Chile/EasterIsland' => [
				'Pacific/Easter',
			],
			'Europe/Belfast' => [
				'Europe/London',
			],
			'Europe/Nicosia' => [
				'Asia/Nicosia',
			],
			'Europe/Tiraspol' => [
				'Europe/Chisinau',
			],
			'Mexico/BajaNorte' => [
				'America/Dawson',
			],
			'Mexico/BajaSur' => [
				'America/Mazatlan',
			],
			'Mexico/General' => [
				'America/Mexico_City',
			],
			'Pacific/Enderbury' => [
				'Pacific/Kanton',
			],
			'Pacific/Johnston' => [
				'Pacific/Tahiti',
			],
			'Pacific/Ponape' => [
				'Pacific/Pohnpei',
			],
			'Pacific/Samoa' => [
				'Pacific/Apia',
			],
			'Pacific/Truk' => [
				'Pacific/Chuuk',
			],
			'Pacific/Yap' => [
				'Pacific/Chuuk',
			],
			'US/Alaska' => [
				'America/Anchorage',
			],
			'US/Central' => [
				'America/Chicago',
			],
			'US/Eastern' => [
				'America/Toronto',
			],
			'US/Mountain' => [
				'America/Denver',
			],
			'US/Pacific' => [
				'America/Los_Angeles',
			],
			'US/Aleutian' => [
				'America/Adak',
			],
			'US/Arizona' => [
				'America/Phoenix',
			],
			'US/East-Indiana' => [
				'America/Indiana/Indianapolis',
			],
			'US/Hawaii' => [
				'Pacific/Honolulu',
			],
			'US/Indiana-Starke' => [
				'America/Indiana/Knox',
			],
			'US/Michigan' => [
				'America/Indiana/Indianapolis',
			],
			'US/Samoa' => [
				'Pacific/Pago_Pago',
			],
		];
	}

	/**
	 * @param string|null $timezone
	 * @return bool
	 */
	public static function hasTimezone(?string $timezone): bool
	{
		return $timezone !== null && isset(self::getTimezoneMap()[$timezone]);
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

	/**
	 * @param $tz
	 * @return string|null
	 */
	public static function getMsTimezone($tz): ?string
	{
		if (!is_string($tz))
		{
			return null;
		}

		$msTimezones = [];
		foreach (self::getTimezoneMap() as $key => $item)
		{
			if (is_array($item))
			{
				foreach ($item as $timezone)
				{
					if ($timezone === $tz)
					{
						$msTimezones[] = $key;
					}
				}
			}
			elseif (is_string($item) && $item === $tz)
			{
				$msTimezones[] = $key;
			}
		}

		if (!empty($msTimezones))
		{
			return end($msTimezones);
		}

		return null;
	}
}