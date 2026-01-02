<?
namespace Bitrix\Rest;

interface RestExceptionInterface
{
	public function output(): array;

	public function getStatus(): string;
}
?>