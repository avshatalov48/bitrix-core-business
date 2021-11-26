<?php

namespace Bitrix\Forum\Comments\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\CommentAux;

class EntityCreated extends Base
{
	const TYPE = 'CREATEENTITY';

	public function getType(): string
	{
		return static::TYPE;
	}

	public function getText(string $text = '', array $params = []): string
	{
		$result = '';

		try
		{
			$data = Json::decode($text);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$data = [];
		}

		if (
			!is_array($data)
			|| empty($data)
			|| !Loader::includeModule('socialnetwork')
		)
		{
			return $result;
		}

		$options = [];
		if (isset($params['suffix']))
		{
			$options['suffix'] = $params['suffix'];
		}

		$socNetProvider = CommentAux\Base::init($this->getSocnetType(), $data, $options);
		$result = $socNetProvider->getText();

		return $result;
	}

	public function canDelete(): bool
	{
		return false;
	}

	protected function getSocnetType(): string
	{
		return CommentAux\CreateEntity::TYPE;
	}
}
