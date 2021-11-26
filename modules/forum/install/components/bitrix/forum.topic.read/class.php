<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config;
use Bitrix\Main;
use Bitrix\Forum;


Loc::loadMessages(__FILE__);

final class ForumTopicRead extends CBitrixComponent
{
	/** @var  Main\ErrorCollection */
	protected $errorCollection;

	public function __construct($component = null)
	{
		parent::__construct($component);

		Main\Loader::includeModule("forum");

		$this->errorCollection = new Main\ErrorCollection();
	}

	protected function checkRequiredParams()
	{
		if (!Main\Loader::includeModule("forum"))
		{
			throw new Main\NotSupportedException(Loc::getMessage("F_NO_MODULE"));
		}
		$mid = intval($this->arParams["MID"]);
		$tid = intval($this->arParams["TID"]);
		$fid = intval($this->arParams["FID"]);

		if ($mid > 0 && ($res = Forum\MessageTable::getById($mid)->fetch()))
		{
			$topic = Forum\Topic::getById($res["TOPIC_ID"]);
		}
		else
		{
			$mid = 0;
			$topic = Forum\Topic::getById($tid);
		}
		if ($topic["STATE"] == Forum\Topic::STATE_LINK)
		{
			$topic = Forum\Topic::getById($topic["TOPIC_ID"]);
		}

		if (!($topic instanceof forum\Topic))
		{
			throw new Main\ObjectNotFoundException('Topic is not found.');
		}

		if ($tid != $topic->getId() || $fid != $topic->getForumId())
		{
			$url = $mid > 0 ? $this->arParams["URL_TEMPLATES_MESSAGE"] : $this->arParams["~URL_TEMPLATES_READ"];
			$url = CComponentEngine::MakePathFromTemplate($url,
				[
					"FID" => $topic->getForumId(),
					"TITLE_SEO" => $topic["TITLE_SEO"],
					"TID" => $topic->getId(),
					"MID" => $mid > 0 ? $mid : "s"
				]
			);
			LocalRedirect($url, false, "301 Moved Permanently");
		}
	}

	public function executeComponent()
	{
		try
		{
			$this->checkRequiredParams();
			return $this->__includeComponent();
		}
		catch (Main\ObjectNotFoundException $e)
		{
			CHTTP::SetStatus("404 Not Found");
			ShowError($e->getMessage());
		}
		catch (Main\ArgumentNullException $e)
		{
			CHTTP::SetStatus("404 Not Found");
			ShowError($e->getMessage());
		}
		catch(Exception $e)
		{
			$exceptionHandling = Config\Configuration::getValue("exception_handling");
			if($exceptionHandling["debug"])
			{
				throw $e;
			}
			else
			{
				ShowError($e->getMessage());
			}
		}
	}
}