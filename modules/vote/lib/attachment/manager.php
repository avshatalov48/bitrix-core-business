<?php
namespace Bitrix\Vote\Attachment;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Vote;

Loc::loadMessages(__FILE__);

final class Manager
{

	/**
	 * @param int $id Attachment ID.
	 * @return Attach
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function loadFromAttachId($id)
	{
		return new Attach($id);
	}

	/**
	 * @param array $attach Data array from DB.
	 * @param $id Vote ID.
	 * @return array|Attach
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function loadFromVoteId(array $attach, $id)
	{
		$attach = new Attach($attach);
		$attach->setVote($id);
		return $attach;
	}

	/**
	 * @param array $attach Data array from DB.
	 * @param array $voteParams Array(
	"TITLE" => "ABC...",
	"QUESTIONS" => array(
	1 => array(
	"ID" => 0,
	"QUESTION" => "Question text",
	"QUESTION_TYPE" => "text"||"html",
	"ANSWERS" => array(
	array(
	"ID" => 0,
	"MESSAGE" => "Answer text",
	"MESSAGE_TYPE" => "text"||"html",
	"FIELD_TYPE" => 0||1||2||3||4||
	)
	)
	);.
	 * @return Attach
	 */
	public static function loadEmptyAttach(array $attach, array $voteParams)
	{
		$attach = new Attach($attach);
		$attach->setStorage($voteParams["CHANNEL_ID"]);
		return $attach;
	}

	/**
	 * @param array $filter Array in terms ORM.
	 * @return Attach[]
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public static function loadFromEntity(array $filter)
	{
		$filter = array_change_key_case($filter, CASE_UPPER);
		if (empty($filter))
			throw new ArgumentNullException("filter");

		$return = array();
		$res = Attach::getData($filter);
		if (is_array($res))
		{
			foreach ($res as $attach)
			{
				$res = new Attach($attach[0]);
				$res->setVote($attach[1]["ID"]);
				$return[$attach[0]["ID"]] = $res;
			}
		}
		return $return;
	}

	/**
	 * Deletes attach by Filter.
	 * @param array $filter Array in terms of ORM.
	 * @return void
	 */
	public static function detachByFilter(array $filter)
	{
		$votes = self::loadFromEntity($filter);
		foreach ($votes as $v)
			$v->delete();
	}
}

