<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 19.02.18
 * Time: 16:33
 */

namespace Bitrix\Rest;


interface AuthProviderInterface
{
	public function authorizeClient($clientId, $userId, $state = '');
	public function get($clientId, $scope, $additionalParams, $userId);
}