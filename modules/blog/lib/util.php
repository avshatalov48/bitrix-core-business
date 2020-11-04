<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Blog;

use Bitrix\Main\Config\Option;

/**
 * Class Util
 * @package Bitrix\Blog
 */
class Util
{
	/**
	 * Returns blog/image_max_width option value
	 * @return integer
	 */
	public static function getImageMaxWidth()
	{
		return Option::get("blog", "image_max_width", 800);
	}

	/**
	 * Returns blog/image_max_height option value
	 * @return integer
	 */
	public static function getImageMaxHeight()
	{
		return Option::get("blog", "image_max_height", 1000);
	}

	public static function sendBlogPing($params = array())
	{
		$serverName = (
			is_array($params)
			&& !empty($params['serverName'])
				? $params['serverName']
				: ''
		);

		$siteId = (
			is_array($params)
			&& !empty($params['siteId'])
				? $params['siteId']
				: SITE_ID
		);

		$pathToBlog = (
			is_array($params)
			&& !empty($params['pathToBlog'])
				? $params['pathToBlog']
				: ''
		);

		$blogFields = (
			is_array($params)
			&& !empty($params['blogFields'])
			&& is_array($params['blogFields'])
				? $params['blogFields']
				: array()
		);

		if (
			empty($pathToBlog)
			|| empty($blogFields)
		)
		{
			return false;
		}

		if (Option::get("blog","send_blog_ping", "N") == "Y")
		{
			if($serverName == '')
			{
				$res = \CSite::getById($siteId);
				$siteFields = $res->fetch();

				$serverName = htmlspecialcharsEx($siteFields["SERVER_NAME"]);

				if (empty($serverName))
				{
					$serverName = (
						defined("SITE_SERVER_NAME")
						&& SITE_SERVER_NAME <> ''
							? SITE_SERVER_NAME
							: Option::get("main", "server_name", "")
					);

					if (empty($serverName))
					{
						$serverName = $_SERVER["SERVER_NAME"];
					}
				}
			}

			\CBlog::sendPing($blogFields["NAME"], "http://".$serverName.\CComponentEngine::makePathFromTemplate(
				htmlspecialcharsBack($pathToBlog),
				array(
					"blog" => $blogFields["URL"],
					"user_id" => $blogFields["OWNER_ID"],
					"group_id" => $blogFields["SOCNET_GROUP_ID"]
				)
			));

			return true;
		}

		return false;
	}
}

