<?
namespace Bitrix\Rest;


class AccessException
	extends RestException
{
	const MESSAGE = 'Access denied!';
	const CODE = 'ACCESS_DENIED';

	public function __construct($msg = '', \Exception $previous = null)
	{
		parent::__construct(
			static::MESSAGE.($msg === '' ? '' : (' '.$msg)),
			static::CODE,
			\CRestServer::STATUS_FORBIDDEN,
			$previous
		);
	}
}
?>