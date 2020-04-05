<?php
namespace Bitrix\Forum\Replica;

use Bitrix\Main\Loader;

if (Loader::includeModule('replica'))
{
	class TopicHandler extends \Bitrix\Replica\Client\BaseHandler
	{
		protected $tasksForumId = 0;

		protected $tableName = "b_forum_topic";
		protected $moduleId = "forum";

		protected $primary = array(
			"ID" => "auto_increment",
		);
		protected $predicates = array(
		);
		protected $translation = array(
			"ID" => "b_forum_topic.ID",
			"USER_START_ID" => "b_user.ID",
			"LAST_POSTER_ID" => "b_user.ID",
			//"LAST_MESSAGE_ID" => "b_forum_message.ID", //TODO
			"ABS_LAST_POSTER_ID" => "b_user.ID",
			//"ABS_LAST_MESSAGE_ID" => "b_forum_message.ID", //TODO
			"RESPONSIBLE_ID" => "b_user.ID",
			"OWNER_ID" => "b_user.ID",
		);
		protected $children = array(
			"ID" => "b_forum_message.TOPIC_ID",
		);

		/**
		 * TopicHandler constructor.
		 *
		 */
		public function __construct()
		{
			if (\Bitrix\Main\Loader::includeModule('tasks'))
			{
				$this->tasksForumId = (int)\CTasksTools::GetForumIdForIntranet();
			}
		}

		/**
		 * Forum event onAfterTopicAdd handler.
		 *
		 * @param integer $id Forum topic identifier.
		 * @param array &$data Topic record.
		 *
		 * @return void
		 * @see \CForumTopic::Add()
		 */
		public function onAfterTopicAdd($id, &$data)
		{
			\Bitrix\Replica\Db\Operation::writeInsert($this->tableName, array('ID'), array('ID' => $id));
		}

		/**
		 * Forum event onAfterTopicUpdate handler.
		 *
		 * @param integer $id Forum topic identifier.
		 * @param array $newTopic Topic record after update.
		 * @param array $oldTopic Topic record before update.
		 *
		 * @return void
		 * @see \CForumTopic::Update()
		 */
		public function onAfterTopicUpdate($id, $newTopic, $oldTopic)
		{
			\Bitrix\Replica\Db\Operation::writeUpdate($this->tableName, array('ID'), array('ID' => $id));
		}

		/**
		 * Forum event onAfterTopicDelete handler.
		 *
		 * @param integer &$id Forum topic identifier.
		 * @param array $topic Topic record before delete.
		 *
		 * @return void
		 * @see \CForumTopic::Delete()
		 */
		public function onAfterTopicDelete(&$id, $topic)
		{
			\Bitrix\Replica\Db\Operation::writeDelete($this->tableName, array('ID'), array('ID' => $id));
		}

		/**
		 * Method will be invoked before new database record inserted.
		 * When an array returned the insert will be cancelled and map for
		 * returned record will be added.
		 *
		 * @param array &$newRecord All fields of inserted record.
		 *
		 * @return null|array
		 */
		public function beforeInsertTrigger(array &$newRecord)
		{
			unset($newRecord["SOCNET_GROUP_ID"]);
			if ($this->tasksForumId > 0 && $newRecord['FORUM_ID'] === 'tasks_forum')
			{
				$newRecord['FORUM_ID'] = $this->tasksForumId;
				if (preg_match("/^TASK_(.+)\$/", $newRecord["XML_ID"], $match))
				{
					$mapper = \Bitrix\Replica\Mapper::getInstance();
					$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $match[1]);
					if ($taskId)
					{
						$newRecord['XML_ID'] = 'TASK_'.$taskId;
					}
				}
			}
			return null;
		}

		/**
		 * Method will be invoked before an database record updated.
		 *
		 * @param array $oldRecord All fields before update.
		 * @param array &$newRecord All fields after update.
		 *
		 * @return void
		 */
		public function beforeUpdateTrigger(array $oldRecord, array &$newRecord)
		{
			unset($newRecord["SOCNET_GROUP_ID"]);

			if ($this->tasksForumId > 0 && $newRecord['FORUM_ID'] === 'tasks_forum')
			{
				$newRecord['FORUM_ID'] = $this->tasksForumId;
				if (preg_match("/^TASK_(.+)\$/", $newRecord["XML_ID"], $match))
				{
					$mapper = \Bitrix\Replica\Mapper::getInstance();
					$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $match[1]);
					if ($taskId)
					{
						$newRecord['XML_ID'] = 'TASK_'.$taskId;
					}
				}
			}
		}

		/**
		 * Called before insert operation log write. You may return false and not log write will take place.
		 *
		 * @param array $record Database record.
		 *
		 * @return boolean
		 */
		public function beforeLogInsert(array $record)
		{
			if ($this->tasksForumId > 0 && $record['FORUM_ID'] == $this->tasksForumId)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Called before record transformed for log writing.
		 *
		 * @param array &$record Database record.
		 *
		 * @return void
		 */
		public function beforeLogFormat(array &$record)
		{
			if ($this->tasksForumId > 0 && $record['FORUM_ID'] == $this->tasksForumId)
			{
				$record['FORUM_ID'] = 'tasks_forum';
				if (preg_match("/^TASK_([0-9]+)\$/", $record["XML_ID"], $match))
				{
					$mapper = \Bitrix\Replica\Mapper::getInstance();
					$guid = $mapper->getLogGuid("b_tasks.ID", $match[1]);
					if ($guid)
						$record['XML_ID'] = 'TASK_'.$guid;
				}
			}
		}
	}
}
