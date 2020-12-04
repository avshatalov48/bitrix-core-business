<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Objectify;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\EventLog\Internal\EventLogTable;

/**
 * @method int getId()
 * @method boolean getActive()
 * @method string getName()
 * @method string getAuditTypeId()
 * @method string getItemId()
 * @method int getUserId()
 * @method string getRemoteAddr()
 * @method string getUserAgent()
 * @method string getRequestUri()
 * @method int getCheckInterval()
 * @method int getAlertCount()
 * @method Main\Type\DateTime getDateChecked()
 * @method $this setActive($arg)
 * @method $this setName($arg)
 * @method $this setAuditTypeId($arg)
 * @method $this setItemId($arg)
 * @method $this setUserId($arg)
 * @method $this setRemoteAddr($arg)
 * @method $this setUserAgent($arg)
 * @method $this setRequestUri($arg)
 * @method $this setCheckInterval($arg)
 * @method $this setAlertCount($arg)
 * @method $this setDateChecked($arg)
 */
class Notification
{
	protected $data;
	protected $eventCount = 0;

	/**
	 * Creates (wake-ups) a notification object.
	 * @param int $id
	 */
	public function __construct($id = 0)
	{
		if($id > 0)
		{
			$this->data = Internal\LogNotificationTable::wakeUpObject($id);
		}
		else
		{
			$this->data = Internal\LogNotificationTable::createObject();
		}
	}

	/**
	 * Fills data from DB.
	 */
	public function fill()
	{
		if($this->data->state <> Objectify\State::RAW)
		{
			$this->data->fill(Fields\FieldTypeMask::SCALAR);
		}
	}

	/**
	 * Fills the actions collection from DB.
	 */
	public function fillActions()
	{
		if($this->data->state <> Objectify\State::RAW)
		{
			$this->data->fillActions();
		}
	}

	/**
	 * Saves data to DB.
	 * @return Main\ORM\Data\Result
	 */
	public function save()
	{
		//save to DB, including the actions collection
		$result = $this->data->save();

		if($result->isSuccess())
		{
			$agent = static::getAgentName($this->getId());

			if($result instanceof Main\ORM\Data\UpdateResult)
			{
				\CAgent::RemoveAgent($agent, "main");
			}
			\CAgent::AddAgent($agent, "main", "N", $this->getCheckInterval()*60);
		}

		return $result;
	}

	/**
	 * Deletes data from DB.
	 * @return Main\ORM\Data\Result
	 */
	public function delete()
	{
		$id = $this->getId();

		$result = $this->data->delete();

		if($result->isSuccess())
		{
			\CAgent::RemoveAgent(static::getAgentName($id), "main");
		}

		return $result;
	}

	/**
	 * Sets values from an array.
	 * @param array $values
	 */
	public function setFromArray(array $values)
	{
		foreach($this->data->entity->getFields() as $fieldName => $field)
		{
			if(!isset($values[$fieldName]))
			{
				continue;
			}
			if($fieldName == "ID")
			{
				continue;
			}
			if(!($field instanceof Fields\ScalarField))
			{
				continue;
			}

			$value = $values[$fieldName];
			if($field instanceof Fields\BooleanField)
			{
				$value = ($value == "Y");
			}
			$this->data->set($fieldName, $value);
		}
	}

	/**
	 * Sets actions from an array.
	 * @param array $values
	 */
	public function setActionsFromArray(array $values)
	{
		//set the actions collection from the array
		if($this->data->state <> Objectify\State::RAW)
		{
			$this->data->removeAllActions();
		}
		foreach($values as $postAction)
		{
			if($postAction["ID"] > 0)
			{
				$action = Internal\LogNotificationActionTable::wakeUpObject($postAction["ID"]);
			}
			else
			{
				$action = Internal\LogNotificationActionTable::createObject();
			}
			$action->setNotificationType($postAction["NOTIFICATION_TYPE"]);
			$action->setRecipient($postAction["RECIPIENT"]);
			$action->setAdditionalText($postAction["ADDITIONAL_TEXT"]);

			$this->data->addToActions($action);
		}
	}

	/**
	 * Returns an array of actions.
	 * @return Action[]
	 */
	public function getActions()
	{
		$actions = [];
		if(($collection = $this->data->getActions()))
		{
			foreach($collection as $record)
			{
				$actions[$record->getId()] = Action::createByType(
					$record->getNotificationType(),
					$record->getRecipient(),
					$record->getAdditionalText()
				);
			}
		}
		return $actions;
	}

	/**
	 * It's magic...
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function __call($name, $arguments)
	{
		if(($first = substr($name, 0, 3)) == "get" || $first == "set")
		{
			/** @noinspection PhpUndefinedMethodInspection */
			return $this->data->__call($name, $arguments);
		}

		throw new Main\SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $name, get_called_class()
		));
	}

	/**
	 * @param int $n
	 * @return $this
	 */
	public function setEventCount($n)
	{
		$this->eventCount = $n;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getEventCount()
	{
		return $this->eventCount;
	}

	/**
	 * Agent function.
	 * @param int $id Notification ID
	 * @return string
	 */
	public static function checkConditions($id)
	{
		$notification = new static($id);
		$notification->fill();

		if($notification->getAuditTypeId() == '')
		{
			//nonexistent
			return '';
		}

		$interval = intval($notification->getCheckInterval());
		$dateInterval = new Main\Type\DateTime();
		$dateInterval->add("-T{$interval}M");

		$notification->setDateChecked(new Main\Type\DateTime());
		$result = $notification->data->save();
		if(!$result->isSuccess())
		{
			return '';
		}

		$filter = Query\Query::filter()
			->where("AUDIT_TYPE_ID", $notification->getAuditTypeId())
			->where("TIMESTAMP_X", ">", $dateInterval);

		if($notification->getItemId() <> '')
		{
			$filter->whereLike("ITEM_ID", '%'.$notification->getItemId().'%');
		}
		if($notification->getUserId() > 0)
		{
			$filter->where("USER_ID", $notification->getUserId());
		}
		if($notification->getRemoteAddr() <> '')
		{
			$filter->whereLike("REMOTE_ADDR", '%'.$notification->getRemoteAddr().'%');
		}
		if($notification->getUserAgent() <> '')
		{
			$filter->whereLike("USER_AGENT", '%'.$notification->getUserAgent().'%');
		}
		if($notification->getRequestUri() <> '')
		{
			$filter->whereLike("REQUEST_URI", '%'.$notification->getRequestUri().'%');
		}

		$eventCount = EventLogTable::query()
			->addSelect(new Fields\ExpressionField('CNT', 'COUNT(1)'))
			->where($filter)
			->fetch();

		if($eventCount["CNT"] >= $notification->getAlertCount())
		{
			//notification triggered
			$notification->setEventCount($eventCount["CNT"]);
			$notification->send();
		}

		return static::getAgentName($id);
	}

	/**
	 * Sends the notification via its actions.
	 */
	public function send()
	{
		$this->fillActions();
		foreach($this->getActions() as $action)
		{
			$action->send($this);
		}
	}

	/**
	 * @param int $id
	 * @return string
	 */
	protected static function getAgentName($id)
	{
		return '\Bitrix\Main\EventLog\Notification::checkConditions('.$id.');';
	}
}
