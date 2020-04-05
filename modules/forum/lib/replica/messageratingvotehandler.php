<?php
namespace Bitrix\Forum\Replica;

class MessageRatingVoteHandler extends \Bitrix\Replica\Client\RatingVoteHandler
{
	protected $entityTypeId = "FORUM_POST";
	protected $entityIdTranslation = "b_forum_message.ID";
}
