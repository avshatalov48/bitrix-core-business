<?

namespace Bitrix\Seo\Retargeting;

abstract class Account extends BaseApiObject
{
	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	public function getProfileCached()
	{
		$profile = $this->getProfile();
		if($profile)
		{

		}

		return $profile;
	}

	/**
	 * @return Response
	 */
	abstract public function getList();

	/**
	 * @return Response
	 */
	abstract public function getProfile();
}