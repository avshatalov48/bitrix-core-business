<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\Bot;
use Bitrix\Im\Integration\UI\EntitySelector\Helper;
use Bitrix\Im\Model\BotTable;
use Bitrix\Im\User;
use Bitrix\Main\EO_User;
use Bitrix\Main\EO_User_Collection;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\Search\Content;
use Bitrix\Main\UserTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class BotProvider extends BaseProvider
{
	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['searchableBotTypes']) && is_array($options['searchableBotTypes']))
		{
			$this->options['searchableBotTypes'] = $options['searchableBotTypes'];
		}

		$this->options['fillDialog'] = true;
		if (isset($options['fillDialog']) && is_bool($options['fillDialog']))
		{
			$this->options['fillDialog'] = $options['fillDialog'];
		}

		$this->options['fillDialogWithDefaultValues'] = true;
		if (isset($options['fillDialogWithDefaultValues']) && is_bool($options['fillDialogWithDefaultValues']))
		{
			$this->options['fillDialogWithDefaultValues'] = $options['fillDialogWithDefaultValues'];
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized() && !User::getInstance()->isExtranet();
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$limit = 100;

		$items = $this->getBotItems([
			'searchQuery' => $searchQuery->getQuery(),
			'limit' => $limit
		]);

		$limitExceeded = $limit <= count($items);
		if ($limitExceeded)
		{
			$searchQuery->setCacheable(false);
		}

		$dialog->addItems($items);
	}

	public function shouldFillDialog(): bool
	{
		return $this->getOption('fillDialog', true);
	}

	public function getItems(array $ids): array
	{
		if (!$this->shouldFillDialog())
		{
			return [];
		}

		return $this->getBotItems([
			'userId' => $ids,
		]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	public function getBotItems(array $options = []): array
	{
		return $this->makeBotItems($this->getBotCollection($options), $options);
	}

	public function makeBotItems(EO_User_Collection $bots, array $options = []): array
	{
		return self::makeItems($bots, array_merge($this->getOptions(), $options));
	}

	public function getBotCollection(array $options = []): EO_User_Collection
	{
		$options = array_merge($this->getOptions(), $options);

		return self::getBots($options);
	}

	public static function getBots(array $options = []): EO_User_Collection
	{
		$query = UserTable::query();

		$query->setSelect([
			'ID',
			'NAME',
			'LAST_NAME',
			'SECOND_NAME',
			'PERSONAL_PHOTO',
			'WORK_POSITION',
			'BOT_TYPE' => 'im_bot.TYPE',
			'BOT_COUNT_MESSAGE' => 'im_bot.COUNT_MESSAGE',
		]);

		$query->registerRuntimeField(
			new Reference(
				'im_bot',
				BotTable::class,
				Join::on('this.ID', 'ref.BOT_ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);

		if (!empty($options['searchQuery']) && is_string($options['searchQuery']))
		{
			$query->registerRuntimeField(
				new Reference(
					'USER_INDEX',
					\Bitrix\Main\UserIndexTable::class,
					Join::on('this.ID', 'ref.USER_ID'),
					['join_type' => 'INNER']
				)
			);

			$query->whereMatch(
				'USER_INDEX.SEARCH_USER_CONTENT',
				Filter\Helper::matchAgainstWildcard(
					Content::prepareStringToken($options['searchQuery']), '*', 1
				)
			);
		}

		if (isset($options['searchableBotTypes']) && is_array($options['searchableBotTypes']))
		{
			$query->whereIn('BOT_TYPE', $options['searchableBotTypes']);
		}

		$userIds = [];
		$userFilter = isset($options['userId']) ? 'userId' : (isset($options['!userId']) ? '!userId' : null);
		if (isset($options[$userFilter]))
		{
			if (is_array($options[$userFilter]) && !empty($options[$userFilter]))
			{
				foreach ($options[$userFilter] as $id)
				{
					$id = (int)$id;
					if ($id > 0)
					{
						$userIds[] = $id;
					}
				}

				$userIds = array_unique($userIds);

				if (!empty($userIds))
				{
					if ($userFilter === 'userId')
					{
						$query->whereIn('ID', $userIds);
					}
					else
					{
						$query->whereNotIn('ID', $userIds);
					}
				}
			}
			else if (!is_array($options[$userFilter]) && (int)$options[$userFilter] > 0)
			{
				if ($userFilter === 'userId')
				{
					$query->where('ID', (int)$options[$userFilter]);
				}
				else
				{
					$query->whereNot('ID', (int)$options[$userFilter]);
				}
			}
		}

		if (isset($options['limit']) && is_int($options['limit']))
		{
			$query->setLimit($options['limit']);
		}
		else
		{
			$query->setLimit(100);
		}

		if (!empty($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder([
				'BOT_COUNT_MESSAGE' => 'DESC'
			]);
		}

		$result = $query->exec();

		return $result->fetchCollection();
	}

	public static function makeItems(EO_User_Collection $bots, array $options = []): array
	{
		$result = [];
		$isBitrix24 = ModuleManager::isModuleInstalled('bitrix24');

		foreach ($bots as $bot)
		{
			$botData = Bot::getCache($bot->getId());
			if (
				$isBitrix24
				&& $botData['TYPE'] === Bot::TYPE_NETWORK
				&& $botData['CLASS'] === 'Bitrix\ImBot\Bot\Support24'
			)
			{
				continue;
			}

			$result[] = self::makeItem($bot, $options);
		}

		return $result;
	}

	public static function makeItem(EO_User $bot, array $options = []): Item
	{
		$defaultIcon =
			'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2228%22%20'
			. 'height%3D%2228%22%20viewBox%3D%220%200%2028%2028%22%3E%0A%20%20%3Cg%20fill%3D%22none%22%20'
			. 'fill-rule%3D%22evenodd%22%3E%0A%20%20%20%20%3Ccircle%20cx%3D%2214%22%20cy%3D%2214%22%20r%3D%2214%22%20'
			. 'fill%3D%22%232FC6F6%22%2F%3E%0A%20%20%20%20%3Cpath%20'
			. 'fill%3D%22%23FFFFFF%22%20d%3D%22M19.053132%2C10.0133936%20L19.9184066%2C7.09247624%20C19'
			. '.9937984%2C6.83851954%2019.930205%2C6.56296095%2019.7515811%2C6.36960075%20C19.5729573%2C6'
			. '.17624054%2019.3064404%2C6.09445472%2019.0524247%2C6.15505122%20C18.798409%2C6.21564772%2018'
			. '.5954856%2C6.40942049%2018.5200937%2C6.66337719%20L17.7789513%2C9.17078557%20C15.4748028%2C7'
			. '.94807693%2012.7275787%2C7.95098931%2010.4259431%2C9.17858062%20L9.68114981%2C6.66337719%20C9'
			. '.56460406%2C6.27079414%209.15710205%2C6.04859979%208.77096861%2C6.16709222%20C8.38483517%2C6'
			. '.28558465%208.16629117%2C6.69989319%208.28283693%2C7.09247624%20L9.15176243%2C10.0249005%20C7'
			. '.2004503%2C11.6106349%206.0672511%2C14.0147948%206.0740137%2C16.5545557%20C6.0740137%2C21.1380463%209'
			. '.67019697%2C20.0133316%2014.1097491%2C20.0133316%20C18.5493013%2C20.0133316%2022.1454845%2C21'
			. '.1380463%2022.1454845%2C16.5545557%20C22.1533008%2C14.0079881%2021.0139427%2C11.5979375%2019'
			. '.053132%2C10.0133936%20Z%20M14.1024472%2C15.9316939%20C10.9334248%2C15.9316939%208.36315777%2C16'
			. '.2657676%208.36315777%2C14.9001487%20C8.36315777%2C13.5345299%2010.9334248%2C12.4257765%2014'
			. '.1024472%2C12.4257765%20C17.2714696%2C12.4257765%2019.8453876%2C13.5334163%2019.8453876%2C14'
			. '.9001487%20C19.8453876%2C16.2668812%2017.2751206%2C15.9316939%2014.1024472%2C15.9316939%20Z%20M11'
			. '.477416%2C13.4487843%20C11.0249669%2C13.5328062%2010.7150974%2C13.9604811%2010.7703097%2C14'
			. '.4247164%20C10.825522%2C14.8889517%2011.2267231%2C15.229209%2011.6858298%2C15.201166%20C12'
			. '.1449365%2C15.1731231%2012.5031841%2C14.7864774%2012.5033322%2C14.3188606%20C12.4520761%2C13'
			. '.7928552%2011.9955831%2C13.4057049%2011.477416%2C13.4487843%20Z%20M16.0191544%2C14.4269902%20C16'
			. '.0754002%2C14.8911461%2016.4771659%2C15.230674%2016.9362856%2C15.2020479%20C17.3954053%2C15'
			. '.1734219%2017.7533545%2C14.7865259%2017.7533947%2C14.3188606%20C17.7021533%2C13.7912874%2017'
			. '.2433569%2C13.4035634%2016.7238275%2C13.4487843%20C16.2716033%2C13.5343137%2015.9629087%2C13'
			. '.9628342%2016.0191544%2C14.4269902%20Z%22%2F%3E%0A%20%20%3C%2Fg%3E%0A%3C%2Fsvg%3E%0A'
		;

		$customData = [
			'imUser' => User::getInstance($bot->getId())->getArray(),
			'imBot' => Bot::getCache($bot->getId()),
		];

		$avatar = Helper\User::makeAvatar($bot);
		if (!$avatar)
		{
			if ($customData['imUser']['COLOR'] !== '')
			{
				$avatar = str_replace(
					'2FC6F6',
					explode('#', $customData['imUser']['COLOR'])[1],
					$defaultIcon
				);
			}
			else
			{
				$avatar = $defaultIcon;
			}
		}

		return new Item([
			'id' => $bot->getId(),
			'entityId' => 'im-bot',
			'entityType' => Bot::getListForJs()[$bot->getId()]['type'],
			'title' => Helper\User::formatName($bot, $options),
			'avatar' => $avatar,
			'customData' => $customData,
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		if (!$this->shouldFillDialog())
		{
			return;
		}

		if (!$this->getOption('fillDialogWithDefaultValues', true))
		{
			$recentBots = new EO_User_Collection();
			$recentItems = $dialog->getRecentItems()->getEntityItems('im-bot');
			$recentIds = array_map('intval', array_keys($recentItems));
			$this->fillRecentBots($recentBots, $recentIds, new EO_User_Collection());

			$dialog->addRecentItems($this->makeBotItems($recentBots));

			return;
		}

		$maxBotsInRecentTab = 50;

		// Preload first 50 users ('doSearch' method has to have the same filter).
		$preloadedBots = $this->getBotCollection([
			'order' => ['ID' => 'DESC'],
			'limit' => $maxBotsInRecentTab
		]);

		if (count($preloadedBots) < $maxBotsInRecentTab)
		{
			// Turn off the user search
			$entity = $dialog->getEntity('im-bot');
			if ($entity)
			{
				$entity->setDynamicSearch(false);
			}
		}

		$recentBots = new EO_User_Collection();

		// Recent Items
		$recentItems = $dialog->getRecentItems()->getEntityItems('im-bot');
		$recentIds = array_map('intval', array_keys($recentItems));
		$this->fillRecentBots($recentBots, $recentIds, $preloadedBots);

		// Global Recent Items
		if (count($recentBots) < $maxBotsInRecentTab)
		{
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems('im-bot');
			$recentGlobalIds = [];

			if (!empty($recentGlobalItems))
			{
				$recentGlobalIds = array_map('intval', array_keys($recentGlobalItems));
				$recentGlobalIds = array_values(array_diff($recentGlobalIds, $recentBots->getIdList()));
				$recentGlobalIds = array_slice($recentGlobalIds, 0, $maxBotsInRecentTab - $recentBots->count());
			}

			$this->fillRecentBots($recentBots, $recentGlobalIds, $preloadedBots);
		}

		// The rest of preloaded users
		foreach ($preloadedBots as $preloadedBot)
		{
			$recentBots->add($preloadedBot);
		}

		$dialog->addRecentItems($this->makeBotItems($recentBots));
	}

	private function fillRecentBots(
		EO_User_Collection $recentBots,
		array $recentIds,
		EO_User_Collection $preloadedBots
	): void
	{
		if (count($recentIds) < 1)
		{
			return;
		}

		$ids = array_values(array_diff($recentIds, $preloadedBots->getIdList()));

		if (!empty($ids))
		{
			$bots = $this->getBotCollection([
				'userId' => $ids,
			]);

			foreach ($bots as $bot)
			{
				$preloadedBots->add($bot);
			}
		}

		foreach ($recentIds as $recentId)
		{
			$bot = $preloadedBots->getByPrimary($recentId);
			if ($bot)
			{
				$recentBots->add($bot);
			}
		}
	}
}