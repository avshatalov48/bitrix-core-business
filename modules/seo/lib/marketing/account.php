<?

namespace Bitrix\Seo\Marketing;

use Bitrix\Seo\Retargeting;

/**
 * Class Account
 *
 * @package Bitrix\Seo\Marketing
 */
abstract class Account extends Retargeting\Account
{
	public abstract function getInstagramList();
}