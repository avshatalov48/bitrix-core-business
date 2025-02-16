<?php

namespace Bitrix\Mail\Integration\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\Tab;

class MailUserRecipientAppearanceFilter extends BaseFilter
{
	public const MAIL_RECIPIENT_USER_TAB = 'mail-recipient-user';

	public function __construct()
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return true;
	}

	protected static function addUserTab(Dialog $dialog): void
	{
		$icon =
			'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20fill%3D%22'.
			'none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M15.953%2018.654a29.847%'.
			'2029.847%200%2001-6.443.689c-2.672%200-5.212-.339-7.51-.948.224-1.103.53-2.573.672-3.106.238-.896'.
			'%201.573-1.562%202.801-2.074.321-.133.515-.24.71-.348.193-.106.386-.213.703-.347.036-.165.05-.333.'.
			'043-.5l.544-.064s.072.126-.043-.614c0%200-.61-.155-.64-1.334%200%200-.458.148-.486-.566a1.82%201.'.
			'82%200%2000-.08-.412c-.087-.315-.164-.597.233-.841l-.287-.74S5.87%204.583%207.192%204.816c-.537-.'.
			'823%203.99-1.508%204.29%201.015.119.76.119%201.534%200%202.294%200%200%20.677-.075.225%201.17%200'.
			'%200-.248.895-.63.693%200%200%20.062%201.133-.539%201.325%200%200%20.043.604.043.645l.503.074s-.01'.
			'4.503.085.557c.458.287.96.505%201.488.645%201.561.383%202.352%201.041%202.352%201.617%200%200%20.6'.
			'41%202.3.944%203.802z%22%20fill%3D%22%23ABB1B8%22/%3E%3Cpath%20d%3D%22M21.47%2016.728c-.36.182-.73'.
			'.355-1.112.52h-3.604c-.027-.376-.377-1.678-.58-2.434-.081-.299-.139-.513-.144-.549-.026-.711-1.015-'.
			'1.347-2.116-1.78a1.95%201.95%200%2000.213-.351c.155-.187.356-.331.585-.42l.017-.557-1.208-.367s-.31'.
			'-.14-.342-.14c.036-.086.08-.168.134-.245.023-.06.17-.507.17-.507-.177.22-.383.415-.614.58.211-.363.'.
			'39-.743.536-1.135a7.02%207.02%200%2000.192-1.15%2016.16%2016.16%200%2001.387-2.093c.125-.343.346-.64'.
			'7.639-.876a3.014%203.014%200%20011.46-.504h.062c.525.039%201.03.213%201.462.504.293.229.514.532.64.8'.
			'76.174.688.304%201.387.387%202.092.037.38.104.755.201%201.124.145.4.322.788.527%201.161a3.066%203.06'.
			'6%200%2001-.614-.579s.113.406.136.466c.063.09.119.185.167.283-.03%200-.342.141-.342.141l-1.208.367.0'.
			'17.558c.23.088.43.232.585.419.073.179.188.338.337.466.292.098.573.224.84.374.404.219.847.36%201.306.'.
			'416.463.074.755.8.755.8l.037.729.093%201.811z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E';

		$dialog->addTab(new Tab([
			'id' => self::MAIL_RECIPIENT_USER_TAB,
			'title' => Loc::getMessage("MAIL_RECIPIENT_USER_TAB_TITLE"),
			'icon' => [
				'default' => $icon,
				'selected' => str_replace('ABB1B8', 'fff', $icon),
			]
		]));
	}

	public function apply(array $items, Dialog $dialog): void
	{
		$usersCount = 0;

		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			$email = '';

			switch ($item->getEntityId())
			{
				case 'user':
				{
					$usersCount++;

					$fields = $item->getCustomData()->getValues();

					$item->getCustomData()->set('entityType', self::MAIL_RECIPIENT_USER_TAB);

					$item->addTab(self::MAIL_RECIPIENT_USER_TAB);

					if (isset($fields['email']))
					{
						$email = (string)$fields['email'];
					}

					break;
				}
			}

			$item->setSubtitle($email);
		}

		if ($usersCount > 0 && is_null($dialog->getTab(self::MAIL_RECIPIENT_USER_TAB)))
		{
			self::addUserTab($dialog);
		}
	}
}