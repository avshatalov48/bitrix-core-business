<?php
namespace Bitrix\Forum\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Topic extends CopyImplementer
{
	const TOPIC_COPY_ERROR = "TOPIC_COPY_ERROR";

	/**
	 * @var EntityCopier|null
	 */
	private $commentCopier;

	/**
	 * @param EntityCopier $commentCopier
	 */
	public function setCommentCopier(EntityCopier $commentCopier): void
	{
		$this->commentCopier = $commentCopier;
	}

	/**
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool Added topic id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$topicId = \CForumTopic::add($fields);

		if (!$topicId)
		{
			$this->result->addError(new Error("Error creating a new topic", self::TOPIC_COPY_ERROR));
		}

		return $topicId;
	}

	/**
	 * Returns topic fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$topic = \CForumTopic::GetByIDEx($entityId);

		return ($topic ? $topic : []);
	}

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		$fields = $this->cleanDataToCopy($fields);

		$dictionary = $container->getDictionary();

		if (!empty($dictionary["XML_ID"]))
		{
			$fields["XML_ID"] = $dictionary["XML_ID"];
		}

		return $fields;
	}

	/**
	 * Starts copying messages.
	 *
	 * @param Container $container
	 * @param int $entityId Topic id.
	 * @param int $copiedEntityId Copied topic id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		if (!$this->commentCopier)
		{
			return new Result();
		}

		$containerCollection = new ContainerCollection();

		$queryObject = \CForumMessage::getList([], ["TOPIC_ID" => $entityId]);
		while ($forumMessage = $queryObject->Fetch())
		{
			$container = new Container($forumMessage["ID"]);
			$container->setParentId($copiedEntityId);
			$containerCollection[] = $container;
		}

		$results = [];

		if (!$containerCollection->isEmpty())
		{
			$results[] = $this->commentCopier->copy($containerCollection);
		}

		return $this->getResult($results);
	}

	private function cleanDataToCopy(array $fields)
	{
		unset($fields["ID"]);
		unset($fields["POSTS"]);
		unset($fields["START_DATE"]);
		unset($fields["LAST_POST_DATE"]);
		unset($fields["ABS_LAST_POST_DATE"]);

		return $fields;
	}
}