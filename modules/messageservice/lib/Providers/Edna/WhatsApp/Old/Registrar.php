<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp\Old;

class Registrar extends \Bitrix\MessageService\Providers\Edna\WhatsApp\Registrar
{
	public function getExternalManageUrl(): string
	{
		return 'https://im.edna.ru/';
	}

}