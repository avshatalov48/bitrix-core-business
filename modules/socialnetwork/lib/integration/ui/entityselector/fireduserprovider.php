<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\EO_User;
use Bitrix\Main\EO_User_Collection;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\Tab;

class FiredUserProvider extends UserProvider
{
	protected const ENTITY_ID = 'fired-user';

	protected function prepareOptions(array $options = []): void
	{
		parent::prepareOptions($options);
		$this->options['activeUsers'] = false;

		if (isset($options['fieldName']) && is_string($options['fieldName']))
		{
			$this->options['fieldName'] = $options['fieldName'];
		}

		if (isset($options['referenceClass']) && is_string($options['referenceClass']))
		{
			$this->options['referenceClass'] = $options['referenceClass'];
		}

		$this->options['module'] = (
		(isset($options['module']) && is_string($options['module']))
			? $options['module']
			: null
		);

		$this->options['entityTypeId'] = (
		!empty($options['entityTypeId'])
			? (int)$options['entityTypeId']
			: null
		);
	}

	protected static function getQuery(array $options = []): Query
	{
		$query = parent::getQuery($options);

		self::sendOnFiredUserProviderQueryEvent($options);

		if (
			!empty($options['referenceClass'])
			&& class_exists($options['referenceClass'])
			&& !empty($options['fieldName'])
		)
		{
			/*
			 * If a referenceClass is not null,
			 * then we reduce the list of fired users only have reference in the referenceClass entity
			 */
			$fieldName = Application::getConnection()->getSqlHelper()->forSql($options['fieldName']);
			$tableName = mb_strtolower($query->getEntity()->getCode());

			$query->whereExists(new SqlExpression(
				"SELECT 1 FROM "
				. $options['referenceClass']::getTableName()
				. " WHERE {$fieldName} = {$tableName}.ID"
			));
		}

		return $query;
	}

	protected static function sendOnFiredUserProviderQueryEvent(array $options): void
	{
		$event = new \Bitrix\Main\Event('ui', 'onFiredUserProviderQuery', [
			'module' => $options['module'],
			'entityTypeId' => $options['entityTypeId'],
		]);

		$event->send();
	}

	protected function getPreloadedUsersCollection(): EO_User_Collection
	{
		return $this->getUserCollection([
			'order' => [
				'LAST_ACTIVITY_DATE' => 'desc',
			],
			'limit' => self::MAX_USERS_IN_RECENT_TAB,
		]);
	}

	public function handleBeforeItemSave(Item $item): void
	{
		// Not add fired users in the recent tab
		$item->setSaveable(false);
	}

	public function fillDialog(Dialog $dialog): void
	{
		parent::fillDialog($dialog);

		// if the referenced entity has fired users, then add the tab
		if (count($dialog->getItemCollection()->getEntityItems('fired-user')))
		{
			$icon =
				'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20' .
				'fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M11' .
				'.934%202.213a.719.719%200%2001.719%200l3.103%201.79c.222.13.36.367.36.623V8.21a.719.71' .
				'9%200%2001-.36.623l-3.103%201.791a.72.72%200%2001-.719%200L8.831%208.832a.719.719%200%' .
				'2001-.36-.623V4.627c0-.257.138-.495.36-.623l3.103-1.791zM7.038%2010.605a.719.719%200%2' .
				'001.719%200l3.103%201.792a.72.72%200%2001.359.622v3.583a.72.72%200%2001-.36.622l-3.102' .
				'%201.792a.719.719%200%2001-.72%200l-3.102-1.791a.72.72%200%2001-.36-.623v-3.583c0-.257' .
				'.138-.494.36-.622l3.103-1.792zM20.829%2013.02a.719.719%200%2000-.36-.623l-3.102-1.792a' .
				'.719.719%200%2000-.72%200l-3.102%201.792a.72.72%200%2000-.36.622v3.583a.72.72%200%2000' .
				'.36.622l3.103%201.792a.719.719%200%2000.719%200l3.102-1.791a.719.719%200%2000.36-.623v' .
				'-3.583z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E';

			$firedTab = new Tab([
				'id' => 'fired-user',
				'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_FIREDUSER_TAB_TITLE'),
				'stub' => true,
				'icon' => [
					'default' => $icon,
					'selected' => str_replace('ABB1B8', 'fff', $icon),
					//'default' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon.svg',
					//'selected' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon-selected.svg'
				]
			]);

			$footerOptions = [
				'content' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_FIREDUSER_FOOTER_INFO'),
			];
			$firedTab->setFooter('BX.SocialNetwork.EntitySelector.TextFooter', $footerOptions);
			$dialog->addTab($firedTab);
		}
	}

	public static function makeItem(EO_User $user, array $options = []): Item
	{
		$item = parent::makeItem($user, $options);

		// Not add fired users in the recent tab
		$item->setAvailableInRecentTab(false);
		return $item;
	}

}
