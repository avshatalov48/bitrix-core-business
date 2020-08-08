<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios\RefreshClientsDeal;


use Bitrix\Sale\Exchange\Integration\Service\Batchable;
use \Bitrix\Sale\Exchange\Integration\Service\Scenarios;

class Contact
{
	public function refreshById($id, array $params)
	{
		$contact = new Scenarios\RefreshClient\Contact();
		$userList = $contact->resolve($params);
		if(count($userList)>0)
		{
			$contact->refresh($params);

			$contacts = $this->itemsGet($id);

			if(count($contacts)>0)
			{
				$this->updates($id, $userList, $contacts);
			}
			else
			{
				$this->adds($id, $userList);
			}
		}
	}

	public function itemsGet($id)
	{
		return Batchable\Deal::contactItemsGet($id);
	}

	public function updates($id, array $userList, array $contacts)
	{
		return Batchable\Deal::dealContactUpdates($id, $userList, $contacts);
	}

	public function adds($id, array $userList)
	{
		return Batchable\Deal::dealContactAdds($id, $userList);
	}
}