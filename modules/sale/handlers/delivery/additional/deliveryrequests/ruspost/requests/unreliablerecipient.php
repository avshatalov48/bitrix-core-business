<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;

/**
 * Class UnreliableRecipient
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Normalizes names
 * https://otpravka.pochta.ru/specification#/nogroup-unreliable_recipient
 */
class UnreliableRecipient extends Base
{
	protected $path = "/1.0/unreliable-recipient";
	protected $type = HttpClient::HTTP_POST;
}