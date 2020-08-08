<?php


namespace Bitrix\Sale\Exchange\Integration\Entity;


use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\Integration\OAuth;

class Token extends EO_B24integrationToken
{
	public function refresh(OAuth\Client $oauthClient)
	{
		$response = $oauthClient->getAccessToken(
			"refresh_token",
			["refresh_token" => $this->getRefreshToken()]
		);

		if (!isset($response["error"]) && is_array($response))
		{
			$this->update($response);
			return true;
		}

		return false;
	}

	/**
	 * @param array $fields
	 * @param null $guid
	 *
	 * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function update(array $fields = [])
	{
		if (isset($fields["guid"]) && $fields["guid"] <> '')
		{
			$this->setGuid($fields["guid"]);
		}

		if (isset($fields["access_token"]))
		{
			$this->setAccessToken($fields["access_token"]);
		}

		if (isset($fields["refresh_token"]))
		{
			$this->setRefreshToken($fields["refresh_token"]);
		}

		if (isset($fields["client_endpoint"]))
		{
			$this->setRestEndpoint($fields["client_endpoint"]);
		}

		if (isset($fields["member_id"]))
		{
			$this->setPortalId($fields["member_id"]);
		}

		if (isset($fields["expires_in"]) && intval($fields["expires_in"]) > 0)
		{
			$this->setExpires((new DateTime())->add(intval($fields["expires_in"])." seconds"));
		}

		$this->setChanged(new DateTime());

		return $this->save();
	}
}