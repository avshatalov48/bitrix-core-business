<?
namespace Bitrix\Rest;

class OAuthException
	extends RestException
{
	protected $result;

	/**
	 * OAuthException constructor.
	 * @param string|array $oauthResult
	 * @param \Exception|null $previous
	 */
	public function __construct($oauthResult, \Exception $previous = null)
	{
		$this->result = $oauthResult;

		parent::__construct(
			$this->result['error_description'] ?? '',
			static::ERROR_OAUTH,
			isset($oauthResult["error_status"])
				? $oauthResult["error_status"]
				: \CRestServer::STATUS_UNAUTHORIZED,
			$previous
		);

		if(isset($oauthResult['additional']) && is_array($oauthResult['additional']))
		{
			$this->setAdditional($oauthResult['additional']);
		}
	}

	public function getErrorCode()
	{
		return $this->result['error'];
	}
}
?>