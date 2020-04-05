<?php
namespace Bitrix\Blog\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class BlogComment extends Base
{
	const BLOG_COMMENT_COPY_ERROR = "BLOG_COMMENT_COPY_ERROR";

	protected $ufEntityObject = "BLOG_COMMENT";
	protected $ufDiskFileField = "UF_BLOG_COMMENT_FILE";

	/**
	 * Adds comment.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return comment id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$commentId = \CBlogComment::add($fields);

		if (!$commentId)
		{
			$this->result->addError(new Error("Blog comment hasn't been added", self::BLOG_COMMENT_COPY_ERROR));
		}

		return $commentId;
	}

	/**
	 * Returns comment fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = \CBlogComment::getlist([], ["ID" => $entityId], false, false, ["*"]);

		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new comment.
	 *
	 * @param Container $container
	 * @param array $fields List comment fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		unset($fields["ID"]);

		if ($container->getParentId())
		{
			$fields["POST_ID"] = $container->getParentId();
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

	public function update($entityId, array $fields)
	{
		return \CBlogComment::update($entityId, $fields);
	}

	public function getText($entityId)
	{
		$queryObject = \CBlogComment::getlist([], ["ID" => $entityId], false, false, ["POST_TEXT"]);

		if ($fields = $queryObject->fetch())
		{
			return ["POST_TEXT", $fields["POST_TEXT"]];
		}
		else
		{
			return ["POST_TEXT", ""];
		}
	}
}