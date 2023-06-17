<?php
namespace Bitrix\Forum\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Comment extends CopyImplementer
{
	const COMMENT_COPY_ERROR = "COMMENT_COPY_ERROR";

	protected $ufEntityObject = "FORUM_MESSAGE";
	protected $ufDiskFileField = "UF_FORUM_MESSAGE_DOC";

	/**
	 * @param Container $container
	 * @param array $fields
	 * @return int message id.
	 */
	public function add(Container $container, array $fields)
	{
		$messageId = \CForumMessage::add($fields);

		if (!$messageId)
		{
			$this->result->addError(new Error("Error creating a new comment", self::COMMENT_COPY_ERROR));
		}

		return $messageId;
	}

	public function update($commentId, array $fields)
	{
		return \CForumMessage::update($commentId, $fields);
	}

	/**
	 * Returns entity fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$message = \CForumMessage::getByIDEx($entityId);
		return ($message ? $message : []);
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
		unset($fields["ID"]);

		if ($container->getParentId())
		{
			$fields["TOPIC_ID"] = $container->getParentId();
		}

		$dictionary = $container->getDictionary();

		if (!empty($dictionary["XML_ID"]))
		{
			$fields["XML_ID"] = $dictionary["XML_ID"];
		}

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId Entity id.
	 * @param int $copiedEntityId Copied entity id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		$this->copyUfFields($entityId, $copiedEntityId, $this->ufEntityObject);

		return new Result();
	}

	/**
	 * Updates identifiers who's added to text.
	 *
	 * @param int $id Id of the entity whose text will be updated.
	 * @param array $attachedIds
	 * @param callable $auxiliaryCallback
	 */
	public function updateAttachedIdsInText(int $id, array $attachedIds, callable $auxiliaryCallback): void
	{
		list($field, $text) = $this->getText($id);

		$detailText = call_user_func_array($auxiliaryCallback, [
			$text,
			$this->ufEntityObject,
			$id,
			$this->ufDiskFileField,
			$attachedIds
		]);

		$this->update($id, [$field => $detailText]);
	}

	protected function getText($commentId)
	{
		$queryObject = \CForumMessage::getlist([], [
			"ID" => $commentId], false, 0, ["SELECT" => ["POST_MESSAGE"]]);

		if ($fields = $queryObject->fetch())
		{
			return ["POST_MESSAGE", $fields["POST_MESSAGE"]];
		}
		else
		{
			return ["POST_MESSAGE", ""];
		}
	}
}