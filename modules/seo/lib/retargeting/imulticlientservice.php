<?

namespace Bitrix\Seo\Retargeting;

interface IMultiClientService
{
	/**
	 * Can user multiple clients
	 * @return bool
	 */
	public static function canUseMultipleClients();

	/**
	 * Get client id
	 * @return string
	 */
	public function getClientId();

	/**
	 * Set client id.
	 * @param string $clientId Client id.
	 * @return mixed
	 */
	public function setClientId($clientId);
}