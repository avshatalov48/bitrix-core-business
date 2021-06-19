<?

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class MetaUserProvider extends BaseProvider
{
	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['all-users']))
		{
			$this->options['all-users'] = is_array($options['all-users']) ? $options['all-users'] : [];
			if (
				!isset($this->options['all-users']['allowView'])
				|| !is_bool($this->options['all-users']['allowView'])
			)
			{
				$this->options['all-users']['allowView'] = null;
			}
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function fillDialog(Dialog $dialog): void
	{
		$options = $this->getOptions();
		if (
			self::canViewAllUsers()
			&& isset($options['all-users'])
			&& $options['all-users']['allowView'] !== false
		)
		{
			$dialog->addRecentItem(self::getAllUsersItem($options['all-users']));
		}
	}

	public function getItems(array $ids): array
	{
		return self::getMetaUsers($ids, $this->getOptions());
	}

	public function getSelectedItems(array $ids): array
	{
		$options = array_merge([
			'all-users' => [
				'allowView' => true,
				'deselectable' => self::canViewAllUsers()
			]
		], $this->getOptions());

		return self::getMetaUsers($ids, $options);
	}

	public static function canViewAllUsers(): bool
	{
		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');

		return !$intranetInstalled || ($intranetInstalled && UserProvider::isIntranetUser());
	}

	public static function getMetaUsers(array $ids, $options = []): array
	{
		$users = [];

		foreach ($ids as $id)
		{
			if ($id === 'all-users')
			{
				$itemOptions =
					isset($options['all-users']) && is_array($options['all-users']) ? $options['all-users']: []
				;

				$canView =
					isset($itemOptions['allowView']) && is_bool($itemOptions['allowView'])
						? $itemOptions['allowView'] :
						self::canViewAllUsers()
				;

				if ($canView)
				{
					$users[] = self::getAllUsersItem($itemOptions);
				}
			}
		}

		return $users;
	}

	public static function getAllUsersItem(array $options = []): Item
	{
		$title = isset($options['title']) && is_string($options['title']) ? $options['title'] : '';
		if (empty($title))
		{
			$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
			$title = Loc::getMessage(
				$intranetInstalled ? 'SOCNET_ENTITY_SELECTOR_ALL_EMPLOYEES' : 'SOCNET_ENTITY_SELECTOR_ALL_USERS'
			);
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

		return new Item([
			'id' => 'all-users',
			'entityId' => 'meta-user',
			'entityType' => 'all-users',
			'title' => $title,
			'searchable' => $searchable,
			'saveable' => false,
			'deselectable' => $deselectable,
			'availableInRecentTab' => $availableInRecentTab,
			'sort' => 1,
		]);
	}

}