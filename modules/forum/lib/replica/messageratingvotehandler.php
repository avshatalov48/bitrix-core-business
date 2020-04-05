<?php
namespace Bitrix\Forum\Replica;

use Bitrix\Main\Loader;

if (Loader::includeModule('replica'))
{
	class MessageRatingVoteHandler extends \Bitrix\Replica\Client\RatingVoteHandler
	{
		protected $entityTypeId = "FORUM_POST";
		protected $entityIdTranslation = "b_forum_message.ID";
	}
}
