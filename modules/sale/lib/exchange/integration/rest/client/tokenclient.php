<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\Client;


use Bitrix\Sale\Exchange\Integration;

class TokenClient extends Base
{
	/** @var Integration\Entity\Token token */
	protected $token;

	public function __construct(Integration\Entity\Token $token)
	{
		parent::__construct([
			"accessToken" => $token->getAccessToken(),
			"refreshToken" => $token->getRefreshToken(),
			"endPoint" => $token->getRestEndpoint(),
		]);

		$this->token = $token;
	}

	protected function refreshAccessToken()
	{
		$success = $this->token->refresh(new Integration\OAuth\Bitrix24());
		if ($success)
		{
			$this->setAccessToken($this->token->getAccessToken());
			$this->setRefreshToken($this->token->getRefreshToken());
			$this->setEndPoint($this->token->getRestEndpoint());
		}

		return $success;
	}
}