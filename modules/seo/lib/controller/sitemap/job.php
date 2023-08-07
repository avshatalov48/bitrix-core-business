<?php

namespace Bitrix\Seo\Controller\Sitemap;

use Bitrix\Main;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Seo\Sitemap;

final class Job extends Main\Engine\Controller
{
	public function addAction(int $sitemapId): AjaxJson
	{
		try {
			$job = Sitemap\Job::addJob($sitemapId);

			return $job
				? self::createSuccess($job->getData())
				: self::createError("Can't add job {$sitemapId}")
			;
		}
		catch (Main\SystemException $e)
		{
			return self::createError($e->getMessage() . " Sitemap id: {$sitemapId}");
		}
	}

	public function doAction(int $sitemapId): AjaxJson
	{
		try
		{
			$job = Sitemap\Job::findJob($sitemapId);
			if (!$job)
			{
				$job = Sitemap\Job::addJob($sitemapId);
			}

			$res = $job->doStep();
			if ($res->isSuccess())
			{
				return self::createSuccess($job->getData());
			}

			return self::createError(implode('; ', $res->getErrors()));
		}
		catch (Main\SystemException $e)
		{
			return self::createError($e->getMessage() . " Sitemap id: {$sitemapId}");
		}
	}

	protected static function createSuccess(mixed $data): AjaxJson
	{
		return AjaxJson::createSuccess($data);
	}

	protected static function createError(string $message): AjaxJson
	{
		$errorCollection = new Main\ErrorCollection();
		$errorCollection[] = new Main\Error($message);

		return AjaxJson::createError($errorCollection);
	}
}