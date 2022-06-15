<?
namespace Bitrix\Mail\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;

class MailContacts extends \Bitrix\Main\UI\Selector\EntityBase
{
	const PREFIX = 'MC';

	private static function prepareEntity($data, $options = [])
	{
		global $APPLICATION;

		static $contactAvatars = [];

		$email = $data['EMAIL'];
		if ($contactAvatars[$email] === null)
		{
			ob_start();
			$APPLICATION->IncludeComponent('bitrix:mail.contact.avatar', '',
				[
					'mailContact' => $data,
				]);
			$contactAvatars[$email] = ob_get_clean();
		}
		$result = [
			'id' => self::PREFIX.$data['ID'],
			'entityType' => 'mailContacts',
			'entityId' => $data['ID'],
			'name' => htmlspecialcharsbx(Emoji::decode($data['NAME'])),
			'iconCustom' => $contactAvatars[$email],
			'email' => htmlspecialcharsbx($data['EMAIL']),
			'desc' => htmlspecialcharsbx($data['EMAIL']),
			'isEmail' => 'Y'
		];

		return $result;
	}

	public function getData($params = [])
	{
		$entityType = Handler::ENTITY_TYPE_MAILCONTACTS;

		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ITEMS_HIDDEN' => array(),
			'ADDITIONAL_INFO' => array(
				'GROUPS_LIST' => array(
					'mailcontacts' => array(
						'TITLE' => Loc::getMessage('MAIN_UI_SELECTOR_TITLE_MAILCONTACTS'),
						'TYPE_LIST' => [ $entityType ],
						'DESC_LESS_MODE' => 'N',
						'SORT' => 100
					)
				),
				'SORT_SELECTED' => 100
			)
		);

		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();
		if (is_null($currentUser->getId()))
		{
			return $result;
		}

		$entityOptions = (!empty($params['options']) ? $params['options'] : array());

		$lastItems = (!empty($params['lastItems']) ? $params['lastItems'] : array());

		$selectedItems = (!empty($params['selectedItems']) ? $params['selectedItems'] : array());
		$selectedItemsData = (!empty($entityOptions['selectedItemsData']) ? $entityOptions['selectedItemsData'] : array());

		$lastMailContactsIdList = [];
		if(!empty($lastItems[$entityType]))
		{
			$result["ITEMS_LAST"] = array_values($lastItems[$entityType]);
			foreach ($lastItems[$entityType] as $value)
			{
				$lastMailContactsIdList[] = str_replace(self::PREFIX, '', $value);
			}
		}

		$selectedMailContactsIdList = [];
		if(!empty($selectedItems[$entityType]))
		{
			foreach ($selectedItems[$entityType] as $value)
			{
				$selectedMailContactsIdList[] = str_replace(self::PREFIX, '', $value);
			}
		}

		$mailContactsIdList = array_merge($selectedMailContactsIdList, $lastMailContactsIdList);
		$mailContactsIdList = array_slice($mailContactsIdList, 0, count($selectedMailContactsIdList) > 20 ? count($selectedMailContactsIdList) : 20);
		$mailContactsIdList = array_unique($mailContactsIdList);

		$mailContactsEmailList = [];
		foreach($mailContactsIdList as $key => $contactId)
		{
			if (
				$contactId !== (int) $contactId.' '
				&& check_email($contactId, true)
			)
			{
				unset($mailContactsIdList[$key]);
				$mailContactsEmailList[] = $contactId;
			}
		}

		$mailContactsList = [];

		$filter = [
			'=USER_ID' => $currentUser->getId()
		];
		$order = [];

		if (!empty($mailContactsIdList))
		{
			$filter['ID'] = $mailContactsIdList;
			$limit = false;
		}
		else
		{
			$order = [
				'ID' => 'DESC'
			];
			$limit = 10;
		}

		$mailContacts = \Bitrix\Mail\Internals\MailContactTable::getList([
			'order' => $order,
			'filter' => $filter,
			'select' => ['ID', 'NAME', 'EMAIL', 'ICON'],
			'limit' => $limit,
		])->fetchAll();

		foreach ($mailContacts as $mailContact)
		{
			$mailContactsList[self::PREFIX.$mailContact['ID']] = self::prepareEntity($mailContact, $entityOptions);
		}

		if (!empty($mailContactsEmailList))
		{
			foreach ($mailContactsEmailList as $mailContactEmail)
			{
				$mailContactsList[self::PREFIX.$mailContactEmail] = self::prepareEntity([
					'ID' => $mailContactEmail,
					'NAME' => (
						isset($selectedItemsData[self::PREFIX.$mailContactEmail])
						&& isset($selectedItemsData[self::PREFIX.$mailContactEmail]['name'])
							? $selectedItemsData[self::PREFIX.$mailContactEmail]['name']
							: $mailContactEmail
					),
					'EMAIL' => $mailContactEmail,
					'ICON' => \Bitrix\Mail\Helper\MailContact::getIconData($mailContactEmail, '')
				], $entityOptions);
			}
		}

		if (empty($lastMailContactsIdList))
		{
			$result["ITEMS_LAST"] = array_keys($mailContactsList);
		}

		$result['ITEMS'] = $mailContactsList;

		return $result;
	}

	public function getTabList($params = [])
	{
		$result = [];

		$options = (!empty($params['options']) ? $params['options'] : []);

		if (
			isset($options['addTab'])
			&& $options['addTab'] == 'Y'
		)
		{
			$result = array(
				array(
					'id' => 'mailcontacts',
					'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_MAILCONTACTS2'),
					'sort' => 1000
				)
			);
		}

		return $result;
	}

	public function search($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ADDITIONAL_INFO' => array()
		);

		$entityOptions = (!empty($params['options']) ? $params['options'] : array());
		$requestFields = (!empty($params['requestFields']) ? $params['requestFields'] : array());
		$search = $requestFields['searchString'];

		if ($search <> '')
		{
			$currentUser = \Bitrix\Main\Engine\CurrentUser::get();
			if (!is_null($currentUser->getId()))
			{
				$searchWords = preg_split('/\s+/', trim($search), ($wordsLimit = 10) + 1);
				$searchWords = array_splice($searchWords, 0, $wordsLimit);
				$sortExpr = '0';
				$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
				foreach ($searchWords as $word)
				{
					$word = str_replace('%', '%%', $word);
					$word = $sqlHelper->forSql($word);
					$sortExpr .= sprintf(
						'+(CASE WHEN %s THEN 2 WHEN %s THEN 1 ELSE 0 END)',
						"(%1\$s LIKE '%%" . $word . "%%')",
						"(%2\$s LIKE '%%" . $word . "%%')"
					);
				}
				$sortWeight = new \Bitrix\Main\Entity\ExpressionField('SORT_WEIGHT', $sortExpr, ['NAME', 'EMAIL']);
				$queryFilter = [
					[
						'LOGIC' => 'OR',
						'%NAME' => $searchWords,
						'%EMAIL' => $searchWords,
					],
				];
				$queryFilter[] = ['=USER_ID' => $currentUser->getId()];
				$mailContacts = \Bitrix\Mail\Internals\MailContactTable::getList([
					'order' => [
						'SORT_WEIGHT' => 'DESC',
						'NAME' => 'ASC',
					],
					'filter' => $queryFilter,
					'select' => ['ID', 'NAME', 'EMAIL', 'ICON', $sortWeight],
					'limit' => 10,
				])->fetchAll();

				foreach ($mailContacts as $mailContact)
				{
					$result["ITEMS"][self::PREFIX.$mailContact['ID']] = self::prepareEntity($mailContact, $entityOptions);
				}
			}
		}

		return $result;
	}
}