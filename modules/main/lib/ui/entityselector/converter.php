<?

namespace Bitrix\Main\UI\EntitySelector;

class Converter
{
	static $sampleSortPriority = [
		'meta-user',
		'user',
		'mail-contact',
		'project',
		'department',
		'crm-company',
		'crm-contact',
		'crm-lead',
		'crm-deal',
		'crm-quote',
		'crm-order',
		'crm-product',
	];

	public static function getCompatEntities()
	{
		static $compatEntities;

		if ($compatEntities)
		{
			return $compatEntities;
		}

		$compatEntities = [
			'user' => [
				'prefix' => 'U',
				'pattern' => '^(?<prefix>U)(?<itemId>\d+)$',
				'reversePrefix' => 'U',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'project' => [
				'prefix' => 'SG',
				'pattern' => '^(?<prefix>SG)(?<itemId>\d+)$',
				'reversePrefix' => 'SG',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-company' => [
				'prefix' => 'CRMCOMPANY',
				'pattern' => '^(?<prefix>CRMCOMPANY)(?<itemId>.+)$',
				'reversePrefix' => 'CRMCOMPANY',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-contact' => [
				'prefix' => 'CRMCONTACT',
				'pattern' => '^(?<prefix>CRMCONTACT)(?<itemId>.+)$',
				'reversePrefix' => 'CRMCONTACT',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-lead' => [
				'prefix' => 'CRMLEAD',
				'pattern' => '^(?<prefix>CRMLEAD)(?<itemId>.+)$',
				'reversePrefix' => 'CRMLEAD',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-deal' => [
				'prefix' => 'CRMDEAL',
				'pattern' => '^(?<prefix>CRMDEAL)(?<itemId>.+)$',
				'reversePrefix' => 'CRMDEAL',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-quote' => [
				'prefix' => 'CRMQUOTE',
				'pattern' => '^(?<prefix>CRMQUOTE)(?<itemId>.+)$',
				'reversePrefix' => 'CRMQUOTE',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-order' => [
				'prefix' => 'CRMORDER',
				'pattern' => '^(?<prefix>CRMORDER)(?<itemId>.+)$',
				'reversePrefix' => 'CRMORDER',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'crm-product' => [
				'prefix' => 'CRMPRODUCT',
				'pattern' => '^(?<prefix>CRMPRODUCT)(?<itemId>.+)$',
				'reversePrefix' => 'CRMPRODUCT',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'mail-contact' => [
				'prefix' => 'MC',
				'pattern' => '^(?<prefix>MC)(?<itemId>[0-9]+)$',
				'reversePrefix' => 'MC',
				'reversePattern' => '^(?<itemId>\d+)$'
			],
			'department' => [
				'prefix' => (function($itemId) {
					return is_string($itemId) && $itemId[-1] === 'F' ? 'D' : 'DR';
				}),
				'itemId' => function($prefix, $itemId) {
					return $prefix === 'D' ? $itemId.':F' : $itemId;
				},
				'pattern' => '^(?<prefix>DR?)(?<itemId>\d+)$',
				'reversePrefix' => (function($suffix) {
					return $suffix === ':F' ? 'D' : 'DR';
				}),
				'reversePattern' => '^(?<itemId>\d+)(?<suffix>.*)$'
			],
		];

		return $compatEntities;
	}

	public static function convertFromFinderCodes(array $codesList = [])
	{
		$result = [];
		foreach ($codesList as $code)
		{
			if ($code === 'UA')
			{
				$result[] = ['meta-user', 'all-users'];
				continue;
			}

			foreach (self::getCompatEntities() as $entityId => $entity)
			{
				if(preg_match('/'.$entity['pattern'].'/i', $code, $matches))
				{
					$result[] = [ $entityId, (int)$matches['itemId'] ];
				}
			}
		}

		return $result;
	}

	public static function convertToFinderCodes(array $entitiesList = [])
	{
		$result = [];

		foreach ($entitiesList as [ $entityId, $id ])
		{
			if (
				$entityId === 'meta-user'
				&& $id === 'all-users'
			)
			{
				$result[] = 'UA';
				continue;
			}

			foreach (self::getCompatEntities() as $sampleEntityId => $sampleEntity)
			{
				if ($entityId !== $sampleEntityId)
				{
					continue;
				}

				if(preg_match('/'.$sampleEntity['reversePattern'].'/i', $id, $matches))
				{
					$result[] = (is_callable($sampleEntity['reversePrefix']) ? $sampleEntity['reversePrefix']($matches['suffix']) : $sampleEntity['reversePrefix']).$matches['itemId'];
				}
			}
		}

		return $result;
	}

	public static function sortEntities(array $entities = [])
	{
		usort($entities, function($a, $b) {
			$aKey = array_search($a[0], self::$sampleSortPriority, true);
			$bKey = array_search($b[0], self::$sampleSortPriority, true);

			if($aKey < $bKey)
			{
				return -1;
			}

			if ($aKey > $bKey)
			{
				return 1;
			}

			return 0;
		});

		return $entities;
	}
}