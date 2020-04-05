<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class Groups extends \Bitrix\Main\UI\Selector\EntityBase
{
	public function getData($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ADDITIONAL_INFO' => array(
				'SORT_SELECTED' => 100
			)
		);

		$entityType = Handler::ENTITY_TYPE_GROUPS;

		$options = (!empty($params['options']) ? $params['options'] : array());

		if (
			!empty($options['enableAll'])
			&& $options['enableAll'] == 'Y'
			&& !Handler::isExtranetUser()
		)
		{
			$allowToAllDestination = (
				!isset($options['context'])
				|| !in_array($options['context'], [ 'BLOG_POST', 'FEED_FILTER_TO' ])
				|| \Bitrix\Socialnetwork\ComponentHelper::getAllowToAllDestination()
			);
			if ($allowToAllDestination)
			{
				$result['ITEMS_LAST'][] = 'UA';
			}

			$result['ITEMS']['UA'] = array(
				'id' => 'UA',
				'name' => Loc::getMessage(
					ModuleManager::isModuleInstalled('intranet')
						? 'MAIN_UI_SELECTOR_ITEM_TOALL_INTRANET'
						: 'MAIN_UI_SELECTOR_ITEM_TOALL'
				),
				'searchable' => ($allowToAllDestination ? 'Y' : 'N')
			);
		}

		if (
			!empty($options['enableEmpty'])
			&& $options['enableEmpty'] == 'Y'
		)
		{
			$result['ITEMS_LAST'][] = 'EMPTY';

			$result['ITEMS']['EMPTY'] = array(
				'id' => 'EMPTY',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_ITEM_EMPTY'),
				'searchable' => 'N'
			);
		}

		if (
			!empty($options['enableUserManager'])
			&& $options['enableUserManager'] == 'Y'
		)
		{
			$result['ITEMS_LAST'][] = 'USER_MANAGER';

			$result['ITEMS']['USER_MANAGER'] = array(
				'id' => 'USER_MANAGER',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_ITEM_USER_MANAGER'),
				'searchable' => 'N'
			);
		}

		return $result;
	}

	public function getItemName($itemCode = '')
	{
		$result = '';

		switch($itemCode)
		{
			case 'EMPTY':
				$result = Loc::getMessage('MAIN_UI_SELECTOR_ITEM_EMPTY');
				break;
			case 'UA':
				$result = Loc::getMessage(
					ModuleManager::isModuleInstalled('intranet')
						? 'MAIN_UI_SELECTOR_ITEM_TOALL_INTRANET'
						: 'MAIN_UI_SELECTOR_ITEM_TOALL'
				);
				break;
			default:
		}

		return $result;
	}
}