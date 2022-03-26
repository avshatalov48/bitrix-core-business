<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('mail');

class CMailContactAvatarComponent extends \CBitrixComponent
{

	public function executeComponent()
	{
		$page = 'avatar_default';
		$mailContact = $this->arParams['mailContact'];
		$this->arResult['avatarSize'] = ($this->arParams['avatarSize'] && is_numeric($this->arParams['avatarSize'])) ? (int)$this->arParams['avatarSize'] : 22;
		if (!empty($mailContact) && is_array($mailContact))
		{
			$fileId = !empty($mailContact['FILE_ID']) ? $mailContact['FILE_ID'] : 0;
			if (!empty($mailContact['ICON']) && is_array($mailContact['ICON'])
				&& !empty($mailContact['ICON']['COLOR']) && !empty($mailContact['ICON']['INITIALS']))
			{
				$color = $mailContact['ICON']['COLOR'];
				$initials = $mailContact['ICON']['INITIALS'];
			}
		}
		$email = $this->arParams['email'];
		$name = $this->arParams['name'];

		$s = $this->arResult['avatarSize'];
		$this->arResult['initialsFontSize'] = floor($s / 2) - abs($s % 2 - floor($s / 2) % 2);

		if ($fileId && is_numeric($fileId))
		{
			$image = \CFile::resizeImageGet(
				$fileId, ['width' => $this->arResult['avatarSize'], 'height' => $this->arResult['avatarSize']],
				BX_RESIZE_IMAGE_EXACT, false
			);
			if ($image['src'])
			{
				$this->arResult['image'] = $image;
				$page = 'avatar';
			}
		}
		else
		{
			if ($email && $name && $color)
			{
				$page = 'icon';
				$this->arResult['COLOR'] = $color;
				$this->arResult['INITIALS'] = \Bitrix\Mail\Helper\MailContact::getInitials($email, $name);
			}
			elseif ($color && $email)
			{
				$page = 'icon';
				$this->arResult['COLOR'] = $color;
				$this->arResult['INITIALS'] = \Bitrix\Mail\Helper\MailContact::getInitials($email);
			}
			elseif ($color && $initials)
			{
				$page = 'icon';
				$this->arResult['COLOR'] = $color;
				$this->arResult['INITIALS'] = $initials;
			}
		}

		$this->includeComponentTemplate($page);
	}
}