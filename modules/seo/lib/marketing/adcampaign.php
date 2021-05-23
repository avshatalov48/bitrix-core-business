<?

namespace Bitrix\Seo\Marketing;

use Bitrix\Seo\Retargeting\BaseApiObject;

abstract class AdCampaign extends BaseApiObject
{
	abstract public function createCampaign(
		array $params
	);
	public abstract function getAdSetList($accountId);
	public abstract function getCampaignList($accountId);
	public abstract function updateAds($adsId);
	public abstract function getAds($adsId);
	public abstract function searchTargetingData($params);
}
