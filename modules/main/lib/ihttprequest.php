<?php
namespace Bitrix\Main;

interface IHttpRequest
{
	public function getQueryString($name);
	public function getPostData($name);
	public function getFile($name);
	public function getCookie($name);
	public function getRequestUri();
	public function getRequestMethod();
	public function getUserAgent();
	public function getAcceptedLanguages();
	public function getHttpHost();
	public function isHttps();
}