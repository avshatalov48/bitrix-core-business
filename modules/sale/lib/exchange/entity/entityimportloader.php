<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Crm\InvoiceTable;
use Bitrix\Main;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ISettings;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Internals\UserPropsTable;

/**
 * Class EntityImportLoader
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class EntityImportLoader
{
    /** @var ISettings */
    protected $settings = null;

    /**
     * @return array
     * @throws Main\ArgumentException
     */
    protected static function getFields()
    {
        throw new Main\ArgumentException('The method is not implemented.');
    }

    /**
     * @return string
     */
    protected static function getExternalField()
    {
        return 'ID_1C';
    }

    /**
     * @param $number
     * @return null
     * @throws Main\ArgumentException
     */
	public function getByNumber($number)
	{
		if($number === "")
		{
			throw new Main\ArgumentException('Is not defined', 'ID');
		}
		$entity = static::getEntityTable();
		/** TODO: only EntityType::ORDER */
		$accountNumberPrefix = $this->settings->prefixFor(EntityType::ORDER);

		if(is_numeric($number))
		{
			if($r = $entity::getById($number)->fetch())
				return $r;

			if($r = $entity::getList(array(
				'select' => array('ID'),
				'filter' => array('ID_1C' => $number),
				'order' => array('ID' => 'DESC')))->fetch()
			)
				return $r;


			if($r = $entity::getList(array(
				'select' => array('ID'),
				'filter' => array('ACCOUNT_NUMBER' => $number),
				'order' => array('ID' => 'DESC')))->fetch()
			)
				return $r;

			if ($accountNumberPrefix !== "")
			{
				if(strpos($number, $accountNumberPrefix) === 0)
				{
					$number = substr($number, strlen($accountNumberPrefix));
					if ($r = $entity::getById($number)->fetch())
						return $r;
				}
			}
		}
		else
		{
			if ($r = $entity::getList(array(
				'select' => array('ID'),
				'filter' => array('ID_1C' => $number),
				'order' => array('ID' => 'DESC')))->fetch()
			)
				return $r;

			if ($r = $entity::getList(array(
				'select' => array('ID'),
				'filter' => array('ACCOUNT_NUMBER' => $number),
				'order' => array('ID' => 'DESC')))->fetch()
			)
				return $r;

			if($accountNumberPrefix != "")
			{
				if(strpos($number, $accountNumberPrefix) === 0)
				{
					$number = substr($number, strlen($accountNumberPrefix));
					if($r = $entity::getById($number)->fetch())
						return $r;

					if($r = $entity::getList(array(
						'select' => array('ID'),
						'filter' => array('ACCOUNT_NUMBER' => $number),
						'order' => array('ID' => 'DESC')))->fetch()
					)
						return $r;
				}
			}
		}
		return null;
	}

    /**
     * @param $xmlId
     * @return null
     * @throws Main\ArgumentException
     */
    public function getByExternalId($xmlId)
    {
        if($xmlId === "")
        {
            throw new Main\ArgumentException('Is not defined', 'XML_1C_DOCUMENT_ID');
        }

        $entity = static::getEntityTable();

        if($r = $entity::getList(array(
            'select' => static::getFields(),
            'filter' => array(static::getExternalField() => $xmlId),
            'order' => array('ID' => 'DESC')))->fetch()
        )
        {
            return $r;
        }

        return null;
    }

    /**
     * @return Main\Entity\DataManager
     * @throws Main\ArgumentException
     */
    protected static function getEntityTable()
    {
        throw new Main\ArgumentException('The method is not implemented.');
    }

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings)
    {
        $this->settings = $settings;
    }
}

class OrderImportLoader extends EntityImportLoader
{
    protected static function getFields()
    {
        return array(
            'ID',
            'ID_1C'
        );
    }

    protected static function getEntityTable()
    {
        return new OrderTable();
    }
}

class PaymentImportLoader extends EntityImportLoader
{
    protected static function getFields()
    {
        return array(
            'ID',
            'ID_1C',
            'ORDER_ID'
        );
    }

    protected static function getEntityTable()
    {
        return new PaymentTable();
    }
}

class ShipmentImportLoader extends EntityImportLoader
{
    protected static function getFields()
    {
        return array(
            'ID',
            'ID_1C',
            'ORDER_ID'
        );
    }

    protected static function getEntityTable()
    {
        return new ShipmentTable();
    }
}

/**
 * Class ProfileImportLoader
 * @package Bitrix\Sale\Exchange\Entity
 * @deprecated
 */
class ProfileImportLoader extends EntityImportLoader
{
    /**
     * @return string
     */
    protected static function getExternalField()
    {
        return 'XML_ID';
    }

    protected static function getFields()
    {
        return array(
            'ID'
        );
    }

    protected static function getEntityTable()
    {
        return new UserPropsTable();
    }
}

class UserProfileImportLoader extends EntityImportLoader
{
	/**
	 * @param $number
	 * @return null
	 * @throws Main\ArgumentException
	 */
	public function getByNumber($number)
	{
		return null;
	}

	/**
	 * @return string
	 */
	protected static function getExternalField()
	{
		return 'XML_ID';
	}

	protected static function getFields()
	{
		return array(
			'ID'
		);
	}

	protected static function getEntityTable()
	{
		return new Main\UserTable();
	}

	public function getByExternalId($xmlId)
	{
		$result = parent::getByExternalId($xmlId);

		if(empty($result))
		{
			$result = self::getUserByCode($xmlId);
		}

		return $result;
	}

	/**
	 * @param $code
	 * @return array
	 */
	static public function getUserByCode($code)
	{
		$result = array();
		$code = rtrim($code);

		$userCode = explode("#", $code);
		if(intval($userCode[0]) > 0)
		{
			$r = \CUser::GetByID($userCode[0]);
			if ($arUser = $r->Fetch())
			{
				if(rtrim(htmlspecialcharsback(substr(htmlspecialcharsbx($arUser["ID"] . "#" . $arUser["LOGIN"] . "#" . $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " " . $arUser["SECOND_NAME"]), 0, 80))) == $code)
					$result = $arUser;
			}
		}
		return $result;
	}
}

class InvoiceImportLoader extends OrderImportLoader
{
	protected static function getEntityTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\InvoiceTable();
	}
}

class PaymentInvoiceImportLoader extends PaymentImportLoader
{
	protected static function getEntityTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\PaymentTable();
	}
}

class ShipmentInvoiceImportLoader extends ShipmentImportLoader
{
	protected static function getEntityTable()
	{
		return new \Bitrix\Crm\Invoice\Internals\ShipmentTable();
	}
}