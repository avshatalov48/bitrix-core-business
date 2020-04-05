<?

namespace Bitrix\Seo\Analytics\Services;

class AccountInstagram extends AccountFacebook
{
	const TYPE_CODE = 'instagram';

	/**
	 * @return array
	 */
	protected function getPublisherPlatforms()
	{
		return ['instagram'];
	}

	/**
	 * @return bool
	 */
	public function hasPublicPages()
	{
		return false;
	}

	protected function prepareExpensesData($data)
	{
		if (is_array($data['actions']))
		{
			$actions = [];
			foreach ($data['actions'] as $action)
			{
				$actions[$action['action_type']] = $action['value'];
			}

			$data['actions'] = $actions['link_click']
				+ $actions['post']
				+ $actions['comment']
			;
		}

		return $data;
	}
}