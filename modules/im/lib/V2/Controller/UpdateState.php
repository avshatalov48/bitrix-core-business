<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Controller\Filter\UpdateStatus;
use Bitrix\Main\Engine\CurrentUser;

class UpdateState extends BaseController
{
	public function configureActions()
	{
		return [
			'getStateData' => [
				'+prefilters' => [
					new UpdateStatus(),
				],
			],
		];
	}

	/**
	 * @restMethod im.v2.UpdateState.getStateData
	 */
	public function getStateDataAction(CurrentUser $user, ?string $siteId = null): ?array
	{
		$updateService = new \Bitrix\Im\V2\UpdateState();
		$result = $updateService->getUpdateStateData($user, $siteId);

		return $result;
	}
}