<?php

namespace Bitrix\Seo\Media\Services;

use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Media;


/**
 * Class MediaVkontakte
 */
class MediaVkontakte extends Retargeting\BaseApiObject
{
	public const TYPE_CODE = Media\Service::TYPE_VKONTAKTE;

	protected static $listRowMap = [
		'ID' => 'ID',
		'NAME' => 'NAME',
		'LOCALE' => 'LOCALE',
	];

	public function getVideo($videoId): Response
	{
		$res = $this->getRequest()->send([
			'methodName' => 'media.video.get',
			'parameters' => [
				'videos' => $videoId,
			]
		]);

		return $res;
	}
}