<?php

namespace Bitrix\Forum\Comments;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Forum\Internals\Error\Error;

Loc::loadMessages(__FILE__);

class Feed extends BaseObject
{
	const ERROR_PARAMS_MESSAGE = 'params0004';
	const ERROR_PERMISSION = 'params0005';

	private function checkTopic()
	{
		if (empty($this->topic))
		{
			$this->topic = $this->createTopic();
		}
		return ($this->topic !== null);
	}

	public function moveEventCommentsToNewXmlId(string $newEntityXmlId): bool
	{
		if (is_null($this->topic))
		{
			return true;
		}

		$forumId = $this->getForum()['ID'];

		$rows = \Bitrix\Forum\MessageTable::query()
			->setSelect(['ID'])
			->where('FORUM_ID', $forumId)
			->where('TOPIC_ID', $this->topic['ID']);

		$comments = $rows->fetchAll();
		if (empty($comments))
		{
			return true;
		}

		$newFeed = new \Bitrix\Forum\Comments\Feed($forumId, [
			'type' => 'EV',
			'id' => $this->getEntity()->getId(),
			'xml_id' => $newEntityXmlId,
		]);

		if (!$newFeed->checkTopic())
		{
			return false;
		}

		$newTopicId = $newFeed->getTopic()['ID'];
		$commentsIds = array_map('intval', array_column($comments, 'ID'));
		\Bitrix\Forum\MessageTable::updateMulti($commentsIds, [
			'TOPIC_ID' => $newTopicId,
		]);

		return true;
	}

	/**
	 * Returns true if entity allows adding
	 * @return bool
	 */
	public function canAdd()
	{
		return $this->getEntity()->canAdd($this->getUser()->getId());
	}

	/**
	 * Returns true if entity allows reading
	 * @return bool
	 */
	public function canRead()
	{
		return $this->getEntity()->canRead($this->getUser()->getId());
	}

	/**
	 * Returns true if entity allows editing
	 * @return bool
	 */
	public function canEdit()
	{
		return $this->getEntity()->canEdit($this->getUser()->getId());
	}

	/**
	 * @param integer $commentId Message ID in b_forum_message to edit.
	 * @return bool
	 */
	public function canEditComment($commentId)
	{
		return Comment::createFromId($this, $commentId)->canEdit();
	}

	/**
	 * Returns true if entity allows deleting.
	 * @return bool
	 */
	public function canDelete()
	{
		return $this->getEntity()->canEdit($this->getUser()->getId());
	}

	/**
	 * @param  integer $commentId Message ID in b_forum_message to delete.
	 * @return bool
	 */
	public function canDeleteComment($commentId)
	{
		return Comment::createFromId($this, $commentId)->canEdit();
	}

	/**
	 * @return bool
	 */
	public function canModerate()
	{
		return $this->getEntity()->canModerate($this->getUser()->getId());
	}
	/**
	 * @return bool
	 */
	public function canEditOwn()
	{
		return $this->getEntity()->canEditOwn($this->getUser()->getId());
	}

	/**
	 * Add a comment like from a person
	 * @param array $params Fields for new message to add in table b_forum_message.
	 * @return array|bool
	 */
	public function add(array $params)
	{
		if (!$this->canAdd())
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_RIGHTS1"), self::ERROR_PERMISSION));
		}
		else if ($this->checkTopic())
		{
			$comment = Comment::create($this);
			if (empty($params["SERVICE_TYPE"]))
			{
				$comment->appendUserFields($params);
			}
			$comment->add($params);

			if ($comment->hasErrors())
			{
				$this->errorCollection->add($comment->getErrors());
			}
			else
			{
				return $comment->getComment();
			}
		}

		return false;
	}

	/**
	 * Add a comment in general
	 * @param array $params Fields for new message to add in table b_forum_message.
	 * @return array|null
	 */
	public function addComment(array $params): ?array
	{
		if ($this->checkTopic())
		{
			$comment = Comment::create($this);
			$comment->add($params);
			if ($comment->hasErrors())
			{
				$this->errorCollection->add($comment->getErrors());
			}
			else
			{
				return $comment->getComment();
			}
		}

		return null;
	}

	public function addServiceComment(
		array $data,
		int $serviceType = Service\Manager::TYPE_FORUM_DEFAULT,
		?array $serviceData = null
	): ?array
	{
		$data['SERVICE_TYPE'] = $serviceType;
		if ($serviceData !== null)
		{
			$data['SERVICE_DATA'] = json_encode($serviceData);
		}
		return $this->addComment($data);
	}

	/**
	 * Edit a comment
	 * @param integer $id Message id.
	 * @param array $params Fields to edit message.
	 * @return array|bool
	 */
	public function edit($id, array $params)
	{
		$comment = Comment::createFromId($this, $id);
		if (!$this->canEdit() && !$comment->canEdit())
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_RIGHTS2"), self::ERROR_PERMISSION));
		else
		{
			if (empty($params["SERVICE_TYPE"]))
			{
				$comment->appendUserFields($params);
			}
			$comment->edit($params);
			if ($comment->hasErrors())
				$this->errorCollection->add($comment->getErrors());
			else
				return $comment->getComment();
		}
		return false;
	}

	/**
	 * Delete a comment
	 * @param integer $id Message id.
	 * @return array|bool
	 */
	public function delete($id)
	{
		$comment = Comment::createFromId($this, $id);
		if (!$this->canDelete() && !$comment->canDelete())
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_RIGHTS3"), self::ERROR_PERMISSION));
		else
		{
			$comment->delete();
			if ($comment->hasErrors())
				$this->errorCollection->add($comment->getErrors());
			else
				return $comment->getComment();
		}
		return false;
	}

	/**
	 * Moderate comment with id
	 * @param integer $id Message id.
	 * @param boolean $show State for moderating: true - show, false - hide.
	 * @return array|bool
	 */
	public function moderate($id, $show)
	{
		$comment = Comment::createFromId($this, $id);
		if (!$this->canModerate())
			$this->errorCollection->addOne(new Error(Loc::getMessage("FORUM_CM_RIGHTS4"), self::ERROR_PERMISSION));
		else
		{
			$comment->moderate($show);
			if ($comment->hasErrors())
				$this->errorCollection->add($comment->getErrors());
			else
				return $comment->getComment();
		}
		return false;
	}
	/**
	 * Render comment through the component and send into pull
	 * @param integer $id Message id.
	 * @param array $params Params for component including.
	 * @return bool
	 */
	public function send($id, array $params)
	{
		ob_start();
		try{
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"bitrix:forum.comments",
				"bitrix24",
				[
					"FORUM_ID" => $this->getForum()["ID"],
					"ENTITY_TYPE" => $this->getEntity()->getType(),
					"ENTITY_ID" => $this->getEntity()->getId(),
					"ENTITY_XML_ID" => $this->getEntity()->getXmlId(),
					"MID" => $id,
					"ACTION" => "SEND",
					"SHOW_POST_FORM" => "N"] + $params + [
					"SHOW_RATING" => "Y",
					"URL_TEMPLATES_PROFILE_VIEW" => "",
					"CHECK_ACTIONS" => "N",
					"RECIPIENT_ID" => $this->getUser()->getId()],
				null,
				array("HIDE_ICONS" => "Y")
			);
			$result = true;
		}
		catch (\Throwable $e)
		{
			$result = false;
		}
		ob_get_clean();
		return $result;
	}
	/**
	 * Mainly this function for forum entity. In this case params have to from the list: A < E < I < M < Q < U < Y
	 * A - NO ACCESS		E - READ			I - ANSWER
	 * M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS
	 * @param string $permission A,E,I,M,Q,U,Y.
	 * @return $this
	 */
	public function setPermission($permission)
	{
		$this->getEntity()->setPermission($this->getUser()->getId(), $permission);
		return $this;
	}

	/**
	 * @param boolean $allow True or false.
	 * @return $this
	 */
	public function setEditOwn($allow)
	{
		$this->getEntity()->setEditOwn($allow);
		return $this;
	}

	/**
	 * Returns permission From list: A < E < I < M < Q < U < Y.
	 * @return string
	 */
	public function getPermission()
	{
		return $this->getEntity()->getPermission($this->getUser()->getId());
	}
}
