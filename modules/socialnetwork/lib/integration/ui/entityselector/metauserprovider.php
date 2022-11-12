<?

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class MetaUserProvider extends BaseProvider
{
	private const SUPPORTED_IDS = [self::ALL_USERS, self::OTHER_USERS];

	private const ALL_USERS = 'all-users';
	private const OTHER_USERS = 'other-users';

	public function __construct(array $options = [])
	{
		parent::__construct();

		$internalizeOption = function (array $options, string $key): void {
			if (isset($options[$key]))
			{
				$this->options[$key] = is_array($options[$key]) ? $options[$key] : [];
				if (
					!isset($this->options[$key]['allowView'])
					|| !is_bool($this->options[$key]['allowView'])
				)
				{
					$this->options[$key]['allowView'] = null;
				}
			}
		};

		foreach (self::SUPPORTED_IDS as $id)
		{
			$internalizeOption($options, $id);
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function fillDialog(Dialog $dialog): void
	{
		if (!self::canViewAllUsers())
		{
			return;
		}

		$options = $this->getOptions();
		$ids = [];
		foreach (self::SUPPORTED_IDS as $id)
		{
			if (isset($options[$id]) && $options[$id]['allowView'] !== false)
			{
				$ids[] = $id;
			}
		}

		foreach (self::getMetaUsers($ids, $options) as $metaUser)
		{
			$dialog->addRecentItem($metaUser);
		}
	}

	public function getItems(array $ids): array
	{
		return self::getMetaUsers($ids, $this->getOptions());
	}

	public function getSelectedItems(array $ids): array
	{
		$options = [];
		foreach (self::SUPPORTED_IDS as $id)
		{
			$options[$id] = [
				'allowView' => true,
				'deselectable' => self::canViewAllUsers(),
			];
		}

		return self::getMetaUsers($ids, array_merge($options, $this->getOptions()));
	}

	public static function canViewAllUsers(): bool
	{
		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');

		return !$intranetInstalled || ($intranetInstalled && UserProvider::isIntranetUser());
	}

	public static function getMetaUsers(array $ids, $options = []): array
	{
		$users = [];

		$sort = 1;
		foreach ($ids as $id)
		{
			if (!isset($users[$id]) && in_array($id, self::SUPPORTED_IDS, true))
			{
				$itemOptions =
					isset($options[$id]) && is_array($options[$id]) ? $options[$id]: []
				;

				$canView =
					isset($itemOptions['allowView']) && is_bool($itemOptions['allowView'])
						? $itemOptions['allowView'] :
						self::canViewAllUsers()
				;

				if ($canView)
				{
					$itemOptions['sort'] ??= $sort;
					$users[$id] = self::getMetaUserItem($id, $itemOptions);
					$sort++;
				}
			}
		}

		return array_values($users);
	}

	public static function getAllUsersItem(array $options = []): Item
	{
		return self::getMetaUserItem(self::ALL_USERS, $options);
	}

	private static function getMetaUserItem(string $id, array $options = []): Item
	{
		$title = isset($options['title']) && is_string($options['title']) ? $options['title'] : '';
		if (empty($title))
		{
			$title = self::getTitle($id);
		}

		$deselectable =
			isset($options['deselectable']) && is_bool($options['deselectable']) ? $options['deselectable'] : true
		;

		$searchable =
			isset($options['searchable']) && is_bool($options['searchable']) ? $options['searchable'] : false
		;

		$availableInRecentTab =
			isset($options['availableInRecentTab']) && is_bool($options['availableInRecentTab'])
				? $options['availableInRecentTab']
				: true
		;

		$sort = isset($options['sort']) && is_numeric($options['sort']) ? (int)$options['sort'] : 1;

		return new Item([
			'id' => $id,
			'entityId' => 'meta-user',
			'entityType' => $id,
			'title' => $title,
			'searchable' => $searchable,
			'saveable' => false,
			'deselectable' => $deselectable,
			'availableInRecentTab' => $availableInRecentTab,
			'sort' => $sort,
		]);
	}

	private static function getTitle(string $id): ?string
	{
		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');

		if ($id === self::ALL_USERS)
		{
			return Loc::getMessage(
				$intranetInstalled ? 'SOCNET_ENTITY_SELECTOR_ALL_EMPLOYEES' : 'SOCNET_ENTITY_SELECTOR_ALL_USERS'
			);
		}

		if ($id === self::OTHER_USERS)
		{
			return Loc::getMessage(
				$intranetInstalled ? 'SOCNET_ENTITY_SELECTOR_OTHER_EMPLOYEES' : 'SOCNET_ENTITY_SELECTOR_OTHER_USERS'
			);
		}

		return null;
	}
}
