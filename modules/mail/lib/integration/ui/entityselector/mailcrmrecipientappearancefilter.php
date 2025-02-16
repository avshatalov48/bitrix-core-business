<?php

namespace Bitrix\Mail\Integration\UI\EntitySelector;

use Bitrix\Main\Loader;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\Crm\Multifield\Type;

class MailCrmRecipientAppearanceFilter extends BaseFilter
{
	public function __construct()
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return true;
	}

	private static function buildTitle(string $name, string $email): string
	{
		$emailIsEquivalentToName = $email === $name;

		return ($emailIsEquivalentToName ? $name : $name . ' (' . $email . ')');
	}

	private static function buildSubtitle(string $name, string $email): string
	{
		$emailIsEquivalentToName = $email === $name;

		return ($emailIsEquivalentToName ? '' : $email);
	}

	public function apply(array $items, Dialog $dialog): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$companyCount = 0;
		$contactCount = 0;
		$leadCount = 0;

		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			$email = '';
			$title = $item->getTitle();

			switch ($item->getEntityId())
			{
				case 'company':
				{
					if ($companyCount < MailCrmRecipientProvider::ITEMS_LIMIT)
					{
						$item->addTab(MailCrmRecipientProvider::PROVIDER_ENTITY_ID);
						$companyCount++;
					}
				}
				case 'contact':
				{
					if ($contactCount < MailCrmRecipientProvider::ITEMS_LIMIT)
					{
						$item->addTab(MailCrmRecipientProvider::PROVIDER_ENTITY_ID);
						$contactCount++;
					}
				}
				case 'lead':
				{
					if ($leadCount < MailCrmRecipientProvider::ITEMS_LIMIT)
					{
						$item->addTab(MailCrmRecipientProvider::PROVIDER_ENTITY_ID);
						$leadCount++;
					}

					$customDataValues = $item->getCustomData()->getValues();

					if (isset($customDataValues['entityInfo']['advancedInfo']['multiFields']))
					{
						$fields = $customDataValues['entityInfo']['advancedInfo']['multiFields'];

						foreach ($fields as $field)
						{
							if ($field['TYPE_ID'] === Type\Email::ID)
							{
								$email = $field['VALUE'];
								$item->setTagOptions([
									'title' => self::buildTitle($title, $email),
								]);
								$item->setSubtitle(self::buildSubtitle($title, $email));
								$customDataValues['email'] = $email;
							}
						}
					}

					$customDataValues['entityId'] = $item->getId();

					$customDataValues['entityType'] = $item->getEntityId();

					$customDataValues['name'] = $item->getTitle();

					if ($customDataValues['name'] === '')
					{
						$customDataValues['name'] = $email;
					}

					$item->setCustomData($customDataValues);

					break;
				}
			}
		}
	}
}