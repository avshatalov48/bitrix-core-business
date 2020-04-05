<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

/**
 * Class OrderDocF112EK
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Generates printing form F112EK
 * https://otpravka.pochta.ru/specification#/documents-create_f112
 */
class OrderDocF112EK extends BaseFile
{
	protected $path = "/1.0/forms/{id}/f112pdf";
	protected $type = \Bitrix\Main\Web\HttpClient::HTTP_GET;
}