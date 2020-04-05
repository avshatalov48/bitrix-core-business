<?

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Seo\LeadAds\Account;
use Bitrix\Seo\LeadAds;

/**
 * Class AccountVkontakte
 *
 * @package Bitrix\Seo\LeadAds\Services
 */
class AccountVkontakte extends Account
{
	const TYPE_CODE = LeadAds\Service::TYPE_VKONTAKTE;

	const URL_ACCOUNT_LIST = 'https://vk.com/groups?tab=admin';

	const URL_INFO = 'https://vk.com/page-19542789_53868676';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	/**
	 * Get row by id.
	 *
	 * @param string $id ID.
	 * @return array|mixed|null
	 */
	public function getRowById($id)
	{
		$list = $this->getList();
		while ($row = $list->fetch())
		{
			if ($row['ID'] == $id)
			{
				return $row;
			}
		}

		return null;
	}

	/**
	 * Get list.
	 *
	 * @return \Bitrix\Seo\Retargeting\Response
	 */
	public function getList()
	{
		// https://vk.com/dev/groups.get
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'groups.get',
			'fields' => array(
				'fields' => 'id,name',
				'extended' => 1,
				'filter' => 'admin'
			)
		));
		$items = $response->getData();
		$items = empty($items['items']) ? [] : is_array($items['items']) ? $items['items'] : [];
		$response->setData($items);

		return $response;
	}

	/**
	 * Get profile.
	 *
	 * @return array|null
	 */
	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'users.get',
			'fields' => array(
				//'user_ids' => array(),
				'fields' => 'photo_50,screen_name'
			)
		));


		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://vk.com/' . $data['SCREEN_NAME'],
				'PICTURE' => $data['PHOTO_50'],
			);
		}
		else
		{
			return null;
		}
	}
}