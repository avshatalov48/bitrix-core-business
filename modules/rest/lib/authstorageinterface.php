<?php
namespace Bitrix\Rest;


interface AuthStorageInterface
{
	public function store(array $authResult);
	public function rewrite(array $authResult);
	public function restore($accessToken);
}