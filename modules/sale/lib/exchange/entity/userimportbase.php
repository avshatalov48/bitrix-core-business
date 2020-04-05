<?php
namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\IBusinessValueProvider;
use Bitrix\Sale\Order;

abstract class UserImportBase extends ImportBase
{
	const EXTERNAL_AUTH_ID = 'sale';

	/** @var  ImportBase */
	protected $entity;

	/**
	 * @param ImportBase $entity
	 */
	public function setEntity(ImportBase $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * @return ImportBase
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @param $personalTypeId
	 * @param $profile
	 * @param $property
	 * @return array
	 */
	public function getPropertyOrdersByConfig($personalTypeId, $profile, $property)
	{
		$result = array();

		if($fieldsConfig = $this->getFieldsConfig($personalTypeId, $profile))
		{
			if(is_array($fieldsConfig))
			{
				foreach($fieldsConfig as $k => $v)
				{
					if(!isset($v['VALUE']))
						continue;

					if(!empty($property[$k]))
					{
						$result[$v["VALUE"]] = $property[$k];
					}

					if(empty($result[$v["VALUE"]]) && !empty($profile[$v["VALUE"]]))
					{
						$result[$v["VALUE"]] = $profile[$v["VALUE"]];
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param $orgFormId
	 * @param array $userProps
	 * @return bool
	 */
	public function getFieldsConfig($orgFormId, $userProps=array())
	{
		if(intval($orgFormId)<=0)
			return false;

		$config = $this->getConfig();

		if(empty($config[$orgFormId]))
			return false;

		$fields = $config[$orgFormId];
		foreach($fields as $k => $v)
		{
			if(empty($v) ||
				((empty($v["VALUE"]) || $v["TYPE"] != "PROPERTY") &&
					(empty($userProps) || (is_array($v) && is_string($v["VALUE"]) && empty($userProps[$v["VALUE"]])))
				)
			)
			{
				unset($fields[$k]);
			}

		}
		return $fields;
	}

	/**
	 * @return null
	 */
	public function getConfig()
	{
		static $config = null;

		if($config === null)
		{
			if($personTypes = $this->getListPersonType($this->settings->getSiteId()))
			{
				$r = \CSaleExport::GetList(array(), array("PERSON_TYPE_ID" => $personTypes));
				while($ar = $r->Fetch())
				{
					$config[$ar["PERSON_TYPE_ID"]] = unserialize($ar["VARS"]);
				}
			}
		}
		return $config;
	}

	/**
	 * @param $siteId
	 * @return array
	 */
	public function getListPersonType($siteId)
	{
		static $personType = null;

		if($personType === null)
		{
			$r = \CSalePersonType::GetList(array(), array("ACTIVE" => "Y", "LIDS" => $siteId));
			while($ar = $r->Fetch())
			{
				$personType[] = $ar["ID"];
			}
		}
		return $personType;
	}

	/**
	 * @return bool
	 */
	public function isFiz()
	{
		$fields = $this->getFieldValues();
		return ($fields["TRAITS"]["TYPE"] == "FIZ");
	}

	/**
	 * @param $fields
	 * @return int|null|string
	 */
	public function resolvePersonTypeId($fields)
	{
		$config = $this->getConfig();

		if(is_array($config))
		{
			foreach($config as $id => $value)
			{
				if(($value["IS_FIZ"] == "Y" && $this->isFiz()) ||
					($value["IS_FIZ"] == "N" && !$this->isFiz()))
				{
					return $id;
				}
			}
		}

		return null;
	}

	/**
	 * @param $personTypeId
	 * @return mixed
	 */
	public static function getPropertyOrdersByPersonalTypeId($personTypeId)
	{
		static $result = null;

		if($result[$personTypeId] === null)
		{
			$dbOrderProperties = \CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
					"PERSON_TYPE_ID" => $personTypeId,
					"ACTIVE" => "Y",
					"UTIL" => "N",
					"USER_PROPS" => "Y",
				),
				false,
				false,
				array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT", "MULTIPLE")
			);
			while ($arOrderProperties = $dbOrderProperties->Fetch())
				$result[$personTypeId][] = $arOrderProperties;
		}

		return $result[$personTypeId];
	}

	/**
	 * @param $fields
	 * @param $arErrors
	 * @return bool|int|string
	 */
	public function registerUser($fields, &$arErrors)
	{
		$userFields = array(
			"NAME" => $fields["ITEM_NAME"],
			"EMAIL" => $fields["CONTACT"]["MAIL_NEW"],
		);

		if (strlen($userFields["NAME"]) <= 0)
			$userFields["NAME"] = $fields["CONTACT"]["CONTACT_PERSON"];

		$userFields["NAME"] = ($this->isFiz() ? $userFields["NAME"]:array("NAME"=>$userFields["NAME"]));

		$emServer = $_SERVER["SERVER_NAME"];
		if(strpos($_SERVER["SERVER_NAME"], ".") === false)
			$emServer .= ".bx";

		if (strlen($userFields["EMAIL"]) <= 0)
			$userFields["EMAIL"] = "buyer" . time() . GetRandomCode(2) . "@" . $emServer;

		$id = \CSaleUser::DoAutoRegisterUser($userFields["EMAIL"], $userFields["NAME"], $this->settings->getSiteId(), $arErrors, array("XML_ID"=>$fields["XML_ID"], "EXTERNAL_AUTH_ID"=>self::EXTERNAL_AUTH_ID));

		$obUser = new \CUser;
		if(strlen($fields["CONTACT"]["PHONE"])>0)
			$obUser->Update($id, array('WORK_PHONE'=>$fields["CONTACT"]["PHONE"]), true);

		return $id;
	}

	/**
	 * @param $id
	 * @param $xmlIdUser
	 * @param $xmlIdFields
	 * @return bool
	 * @internal
	 */
	static public function updateEmptyXmlId($id, $xmlId)
	{
		$result = false;

		if(intval($id)>0)
		{
			$user = UserTable::getById($id);
			if($fields = $user->fetch())
			{
				if($fields['XML_ID'] == '' && $fields['XML_ID'] <> $xmlId)
				{
					$user = new \CUser;
					$result = $user->Update($id, array('XML_ID'=>$xmlId), true);
				}
			}
		}

		return $result;
	}

	public function initFields()
	{
		$this->setFields(array(
			'TRAITS'=> $this->getFieldsTraits(),
		));
	}

	/**
	 * @param IBusinessValueProvider $entity
	 * @return Order
	 */
	static protected function getBusinessValueOrderProvider(IBusinessValueProvider $entity)
	{
		if(!($entity instanceof Order))
			throw new ArgumentException("entity must be instanceof Order");

		return $entity;
	}
}