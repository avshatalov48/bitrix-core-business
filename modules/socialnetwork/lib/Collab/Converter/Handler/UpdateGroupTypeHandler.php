<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Item\Workgroup\AvatarType;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class UpdateGroupTypeHandler extends AbstractHandler
{
	public function __construct(private readonly Type $type)
	{
	}

	public function execute(AbstractConverterCommand $command): Result
	{
		$result = new Result();
		$fields = ['TYPE' => $this->type->value];

		if ($this->type === Type::Collab)
		{
			$fields['AVATAR_TYPE'] = AvatarType::None->value;
		}
		$updateResult = \CSocNetGroup::Update($command->getGroup()->getId(), $fields);

		if ($updateResult === false)
		{
			$result->addError(new Error('Error updating group type.'));
		}

		return $result;
	}
}
