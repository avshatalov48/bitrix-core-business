<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;

/**
 * Class CleanAddress
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Normalize addresses
 * https://otpravka.pochta.ru/specification#/nogroup-normalization_adress
 */
class CleanAddress extends Base
{
	protected $path = "/1.0/clean/address";
	protected $type = HttpClient::HTTP_POST;
}