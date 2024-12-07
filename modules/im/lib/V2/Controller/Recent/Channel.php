<?php

namespace Bitrix\Im\V2\Controller\Recent;

use Bitrix\Im\V2\Chat\OpenChannelChat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Recent\Recent;
use Bitrix\Intranet\ActionFilter\IntranetUser;
use Bitrix\Main\Loader;

class Channel extends BaseController
{

	/**
	 * @restMethod im.v2.Recent.Channel.tail
	 */
	public function tailAction(int $limit = 50, array $filter = []): ?array
	{
		$limit = $this->getLimit($limit);
		$recent = Recent::getOpenChannels($limit, $filter['lastMessageId'] ?? null);

		return $this->toRestFormatWithPaginationData([$recent], $limit, $recent->count());
	}

	/**
	 * @restMethod im.v2.Recent.Channel.extendPullWatch
	 */
	public function extendPullWatchAction(): ?array
	{
		OpenChannelChat::extendPullWatchToCommonList();

		return ['result' => true];
	}

	protected function getDefaultPreFilters()
	{
		$prefilters = parent::getDefaultPreFilters();

		if (Loader::includeModule('intranet'))
		{
			$prefilters[] = new IntranetUser();
		}

		return $prefilters;
	}
}