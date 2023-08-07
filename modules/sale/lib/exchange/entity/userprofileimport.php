<?php
namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Main\Error;
use Bitrix\Main\UserTable;
use Bitrix\Sale;
use Bitrix\Sale\Exchange\EntityType;

class UserProfileImport extends UserImportBase
{
	public function __construct()
	{
		$this->fields = new Sale\Internals\Fields();
	}

	/**
	 * @return int
	 */
	public function getOwnerTypeId()
	{
		return EntityType::USER_PROFILE;
	}

	/**
	 * Adds row to entity table
	 * @param array $params
	 * @return Sale\Result
	 */
	public function add(array $params)
	{
		$result = new Sale\Result();

		$profileId = null;
		$fields = $params['TRAITS'];
		$property = $params["ORDER_PROP"];

		$errorList = [];
		$fields['ID'] = $this->registerUser($fields, $errorList);

		if (!empty($errorList))
		{
			foreach($errorList as $error)
			{
				$result->addError(new Error(str_replace('<br>','', $error['TEXT'])));
			}
		}
		elseif(intval($fields['ID'])>0)
		{
			$propertyOrders = static::getPropertyOrdersByPersonalTypeId($fields["PERSON_TYPE_ID"]);

			if(is_array($propertyOrders))
			{
				foreach($propertyOrders as $filedsProperty)
				{
					$propertyId = $filedsProperty["ID"];
					if(array_key_exists($propertyId, $property))
					{
						$propertyByConfigValue = $property[$propertyId];
						if($profileId == null)
						{
							if(!empty($propertyByConfigValue))
							{
								$profileId = \CSaleOrderUserProps::Add(array(
									"NAME" => $fields["AGENT_NAME"],
									"USER_ID" => $fields['ID'],
									"PERSON_TYPE_ID" => $fields['PERSON_TYPE_ID'],
									"XML_ID" => $fields["XML_ID"],
									"VERSION_1C" => $fields["VERSION_1C"]
								));
							}
						}

						\CSaleOrderUserPropsValue::Add(array(
							"USER_PROPS_ID" => $profileId,
							"ORDER_PROPS_ID" => $propertyId,
							"NAME" => $filedsProperty["NAME"],
							"VALUE" => $propertyByConfigValue
						));
					}
				}
			}
		}

		if($result->isSuccess())
		{
			$user = new static();
			$user->setFields($fields);
			$this->setEntity($user);
		}

		return $result;
	}

	/**
	 * Updates row in entity table
	 * @param array $params
	 * @return Sale\Result
	 */
	public function update(array $params)
	{
		$entity = $this->getEntity();

		if($entity->getId()>0)
			static::updateEmptyXmlId($entity->getId(), $params['TRAITS']['XML_ID']);

		return new Sale\Result();
	}

	/**
	 * Deletes row in entity table by primary key
	 * @param array|null $params
	 * @return Sale\Result
	 */
	public function delete(array $params = null)
	{
		return new Sale\Result();
	}

	/**
	 * @param array $fields
	 * @return Sale\Result
	 */
	protected function checkFields(array $fields)
	{
		return new Sale\Result();
	}

	/**
	 * @param array $fields
	 * @return Sale\Result
	 */
	public function load(array $fields)
	{
		$result = $this->checkFields($fields);

		if($result->isSuccess())
		{
			if(!empty($fields['ID']))
			{
				$user = UserTable::getById($fields['ID']);
				if($fields = $user->fetch())
				{
					$userProfile = new static();
					$userProfile->setFields($fields);

					$this->setEntity($userProfile);
				}
			}
		}

		return $result;
	}

	/**
	 * @return int|null
	 */
	public function getId()
	{
		$entity = $this->getEntity();
		if(!empty($entity))
		{
			return $entity->getField('ID');
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public function isImportable()
	{
		return $this->settings->isImportableFor($this->getOwnerTypeId());
	}

	/**
	 * @param array $fields
	 */
	public function refreshData(array $fields)
	{
	}

	/**
	 * @return string
	 */
	public static function getFieldExternalId()
	{
		return 'XML_ID';
	}
}