<?php

namespace Bitrix\Mail\Integration\UI\EntitySelector;

use Bitrix\Mail\Internals\MailContactTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class AddressBookProvider extends BaseProvider
{
	public const PROVIDER_ENTITY_ID = 'address_book';
	private const ITEMS_TAB_LIMIT = 14;
	private const SEARCH_ITEMS_LIMIT = 100;

	private array $preinstalledItems = [];

	public function __construct(array $options = [])
	{
		$this->setPreinstalledItems($options['preinstalledItems'] ?? []);
		parent::__construct();
	}

	private static function buildName(string $name): string
	{
		return trim($name, "'\"");
	}

	private static function buildTitle(string $name, string $email): string
	{
		$emailIsEquivalentToName = $email === $name;

		return ($emailIsEquivalentToName ? $name : $name . ' (' . $email . ')');
	}

	private static function buildSubtitle(string $name, string $email): string
	{
		$emailIsEquivalentToName = $email === $name;

		return ($emailIsEquivalentToName ? '' : $email);
	}

	public function setPreinstalledItems($items, $updateData = false): void
	{
		$this->preinstalledItems = [];

		foreach ($items as $item)
		{
			/** @var array{email: string|null} $item */
			$email = $item['email'];

			if (empty($email))
			{
				continue;
			}

			if ($updateData)
			{
				global $USER;

				$contact = MailContactTable::getContactByEmail($email, $USER->getId());

				$item['name'] = $contact['NAME'];
				$item['entityId'] = $contact['ID'];

				if($contact['ID'] === 0)
				{
					continue;
				}
			}

			$name = self::buildName($item['name']) ?: $email;

			$this->preinstalledItems[] = new Item(
				[
					'id' => $email,
					'entityId' => self::PROVIDER_ENTITY_ID,
					'tabs' => static::PROVIDER_ENTITY_ID,
					'title' => $name,
					'tagOptions' => [
						'title' => self::buildTitle($name, $email),
					],
					'subtitle' => self::buildSubtitle($name, $email),
					'avatar' => self::getDefaultItemAvatar(),
					'customData' => [
						'entityType' => self::PROVIDER_ENTITY_ID,
						'entityId' => is_null($item['entityId']) ? 0 : (int) $item['entityId'],
						'name' => $name,
						'email' => $email,
					],
				]
			);
		}
	}

	public function getPreinstalledItems(): array
	{
		return $this->preinstalledItems;
	}

	public function isAvailable(): bool
	{
		return true;
	}

	public function getItems(array $ids): array
	{
		return $this->getPreinstalledItems();
	}

	public static function getCurrentUserId(): int
	{
		return is_object($GLOBALS['USER']) ? (int)$GLOBALS['USER']->getId() : 0;
	}

	private static function buildItem($name, $email, $id = 0): Item
	{
		$name = self::buildName($name);

		return new Item(
			[
				'id' => $email,
				'entityId' => self::PROVIDER_ENTITY_ID,
				'tabs' => static::PROVIDER_ENTITY_ID,
				'title' => $name,
				'tagOptions' => [
					'title' => self::buildTitle($name, $email),
				],
				'subtitle' => self::buildSubtitle($name, $email),
				'avatar' => self::getDefaultItemAvatar(),
				'customData' => [
					'entityType' => self::PROVIDER_ENTITY_ID,
					'id' => $id,
					'name' => $name,
					'email' => $email,
				],
			]
		);
	}

	private static function addItemsToDialog(array $items, Dialog $dialog)
	{
		$addedItemsCount = 0;

		foreach ($items as $item)
		{
			$dialog->addItem(self::buildItem($item['NAME'], $item['EMAIL'], $item['ID']));
			$addedItemsCount++;
		}

		return $addedItemsCount;
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$items = MailContactTable::getList(
			[
				'limit' => static::SEARCH_ITEMS_LIMIT,
				'filter' => [
					'=USER_ID' => static::getCurrentUserId(),
					[
						'LOGIC' => 'OR',
						'%NAME' => $searchQuery->getQuery(),
						'%EMAIL' => $searchQuery->getQuery(),
					],
				],
				'order' => [
					'ID' => 'DESC',
				],
				'select' => ['ID', 'NAME', 'EMAIL'],
			]
		)->fetchAll();

		self::addItemsToDialog($items, $dialog);
	}

	private static function getTabIcon(): string
	{
		return "data:image/svg+xml,%3Csvg%20width%3D%2228%22%20height%3D%2228%22%20viewBox%3D%220%200%2028%2028%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%0A%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20d%3D%22M8.99999%204.66669C7.89542%204.66669%206.99999%205.56212%206.99999%206.66669V7.00002C8.28865%207.00002%209.33332%208.04469%209.33332%209.33335C9.33332%2010.622%208.28865%2011.6667%206.99999%2011.6667C8.28865%2011.6667%209.33332%2012.7114%209.33332%2014C9.33332%2015.2887%208.28865%2016.3334%206.99999%2016.3334C8.28865%2016.3334%209.33332%2017.378%209.33332%2018.6667C9.33332%2019.9554%208.28865%2021%206.99999%2021V21.3334C6.99999%2022.4379%207.89542%2023.3334%208.99999%2023.3334H20.1667C21.2712%2023.3334%2022.1667%2022.4379%2022.1667%2021.3334V6.66669C22.1667%205.56212%2021.2712%204.66669%2020.1667%204.66669H8.99999ZM4.66666%209.33335C4.66666%208.68902%205.18899%208.16669%205.83332%208.16669H6.99999C7.64432%208.16669%208.16666%208.68902%208.16666%209.33335C8.16666%209.97769%207.64432%2010.5%206.99999%2010.5H5.83332C5.18899%2010.5%204.66666%209.97769%204.66666%209.33335ZM5.83332%2012.8334C5.18899%2012.8334%204.66666%2013.3557%204.66666%2014C4.66666%2014.6444%205.18899%2015.1667%205.83332%2015.1667H6.99999C7.64432%2015.1667%208.16666%2014.6444%208.16666%2014C8.16666%2013.3557%207.64432%2012.8334%206.99999%2012.8334H5.83332ZM4.66666%2018.6667C4.66666%2018.0224%205.18899%2017.5%205.83332%2017.5H6.99999C7.64432%2017.5%208.16666%2018.0224%208.16666%2018.6667C8.16666%2019.311%207.64432%2019.8334%206.99999%2019.8334H5.83332C5.18899%2019.8334%204.66666%2019.311%204.66666%2018.6667ZM18.7264%2016.7869C18.7718%2017.0203%2018.649%2017.2552%2018.4278%2017.3364C17.5073%2017.6745%2016.4694%2017.8747%2015.3687%2017.8986H14.9234C13.8173%2017.8746%2012.7747%2017.6725%2011.8508%2017.3315C11.6391%2017.2534%2011.5154%2017.0333%2011.5514%2016.8084C11.5858%2016.5939%2011.6239%2016.3882%2011.6627%2016.2351C11.7956%2015.7116%2012.5432%2015.3228%2013.231%2015.0239C13.4111%2014.9456%2013.5198%2014.8831%2013.6297%2014.82C13.737%2014.7583%2013.8454%2014.6961%2014.0224%2014.6178C14.0425%2014.5214%2014.0506%2014.4229%2014.0464%2014.3246L14.3511%2014.288C14.3511%2014.288%2014.3912%2014.3616%2014.3269%2013.9292C14.3269%2013.9292%2013.9845%2013.8395%2013.9686%2013.1509C13.9686%2013.1509%2013.7112%2013.2374%2013.6957%2012.8201C13.6925%2012.737%2013.6712%2012.6571%2013.6509%2012.5805C13.602%2012.3968%2013.5583%2012.2324%2013.781%2012.0891L13.6203%2011.6561C13.6203%2011.6561%2013.4512%209.98415%2014.1921%2010.1195C13.8915%209.63832%2016.4266%209.23835%2016.5949%2010.7117C16.6612%2011.1558%2016.6612%2011.6072%2016.5949%2012.0513C16.5949%2012.0513%2016.9736%2012.0073%2016.7208%2012.7349C16.7208%2012.7349%2016.5816%2013.2585%2016.3679%2013.1409C16.3679%2013.1409%2016.4025%2013.8026%2016.0659%2013.9148C16.0659%2013.9148%2016.09%2014.2672%2016.09%2014.2911L16.3713%2014.3336C16.3713%2014.3336%2016.3628%2014.6274%2016.4189%2014.6593C16.6755%2014.8267%2016.9568%2014.9537%2017.2527%2015.0355C18.1262%2015.2596%2018.5697%2015.644%2018.5697%2015.9805L18.7264%2016.7869Z%22%20fill%3D%22%23959CA4%22%2F%3E%0A%3C%2Fsvg%3E%0A";
	}

	private static function getDefaultItemAvatar(): ?string
	{
		return '/bitrix/images/mail/entity_provider_icons/addressbook.svg';
	}

	private static function addTemplatesTab($dialog): void
	{
		$dialog->addTab(new Tab([
			'id' => self::PROVIDER_ENTITY_ID,
			'title' => Loc::getMessage("ADDRESS_BOOK_PROVIDER_TAB_TITLE_MSGVER_1"),
			'header' => Loc::getMessage("ADDRESS_BOOK_PROVIDER_TAB_HEADER_MSGVER_1"),
			'icon' => [
				'default' => self::getTabIcon(),
				'selected' => str_replace('959CA4', 'FFF', self::getTabIcon()),
			],
		]));
	}

	private static function sortDBItemsByEmailOrder($items, $order)
	{
		$orderMap = array_flip($order);

		usort($items, function($a, $b) use ($orderMap) {
			return $orderMap[$a['EMAIL']] - $orderMap[$b['EMAIL']];
		});

		return $items;
	}

	public function fillDialog(Dialog $dialog): void
	{
		self::addTemplatesTab($dialog);
		$entity = $dialog->getEntity(static::PROVIDER_ENTITY_ID);
		$entity?->setDynamicSearch();

		$recentItems = $dialog->getRecentItems();
		$addressBookRecentItems = $recentItems->getEntityItems(self::PROVIDER_ENTITY_ID);
		$recentAddressBookIds = array_keys($addressBookRecentItems);
		$skipItemsFilter = [];
		$addedItemsCount = 0;

		if (count($recentAddressBookIds) > 0)
		{
			$skipItemsFilter = [
				'!@EMAIL' => $recentAddressBookIds,
			];

			$recentItems = MailContactTable::getList(
				[
					'filter' => [
						'=USER_ID' => self::getCurrentUserId(),
						'@EMAIL' => $recentAddressBookIds,
					],
					'select' => ['NAME', 'EMAIL', 'ID'],
				]
			)->fetchAll();

			$addedItemsCount = self::addItemsToDialog(self::sortDBItemsByEmailOrder($recentItems, $recentAddressBookIds), $dialog);
		}

		if ($addedItemsCount < self::ITEMS_TAB_LIMIT)
		{
			$itemsDB = MailContactTable::getList(
				[
					'filter' => array_merge([
						'=USER_ID' => self::getCurrentUserId(),
					], $skipItemsFilter),
					'order' => ['ID' => 'DESC'],
					'limit' => (self::ITEMS_TAB_LIMIT - $addedItemsCount),
					'select' => ['ID', 'NAME', 'EMAIL'],
				]
			)->fetchAll();

			self::addItemsToDialog($itemsDB, $dialog);
		}
	}
}