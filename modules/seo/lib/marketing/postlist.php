<?

namespace Bitrix\Seo\Marketing;

use Bitrix\Seo\Retargeting\BaseApiObject;

abstract class PostList extends BaseApiObject
{
	abstract public function getList($params);
}
