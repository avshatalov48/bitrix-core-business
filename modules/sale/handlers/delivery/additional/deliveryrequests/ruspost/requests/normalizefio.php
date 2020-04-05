<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;

/**
 * Class NormalizeFio
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Normalizes names
 * https://otpravka.pochta.ru/specification#/nogroup-normalization_fio
 */
class NormalizeFio extends Base
{
	protected $path = "/1.0/clean/physical";
	protected $type = HttpClient::HTTP_POST;
}