<?php

namespace Bitrix\Bizproc\UserType;

use Bitrix\Main;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;

class MailSender extends Bizproc\BaseType\Select
{
	public static function getName(): string
	{
		return Loc::getMessage('BP_FIELDTYPE_MAIL_SENDER') ?: parent::getName();
	}

	protected static function getFieldOptions(Bizproc\FieldType $fieldType)
	{
		$options = static::makeMailboxListSelectOptions(static::getMailboxList());
		return static::normalizeOptions($options);
	}

	public static function renderControlSingle(Bizproc\FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		return static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	public static function renderControlMultiple(Bizproc\FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		return static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$providers = static::getMailboxList();
		$fieldName = htmlspecialcharsbx(static::generateControlName($field));

		$isPublicControl = $renderMode & FieldType::RENDER_MODE_PUBLIC;

		$typeValue = $value;
		if (is_array($typeValue))
		{
			$typeValue = (string)current($value);
		}

		$valueHtml = htmlspecialcharsbx((string)$typeValue);
		$nodeAttributes = '';

		if ($allowSelection && $isPublicControl)
		{
			$nodeAttributes = sprintf(
				'data-role="inline-selector-target" data-select-mode="replace" data-property="%s" ',
				htmlspecialcharsbx(Main\Web\Json::encode($fieldType->getProperty()))
			);
		}

		if (!$isPublicControl)
		{
			$nodeAttributes .= 'style="opacity: 1" ';
		}

		$controlId = htmlspecialcharsbx(static::generateControlId($field));
		$className = htmlspecialcharsbx(static::generateControlClassName($fieldType, $field));
		$placeholder = htmlspecialcharsbx(Loc::getMessage('BP_FIELDTYPE_MAIL_SENDER_AUTO'));

		$node = <<<HTML
			<input
				id="{$controlId}" 
				type="text"
				readonly="readonly"
				name="{$fieldName}"
				value="{$valueHtml}"
				class="{$className} bizproc-type-control-select-wide"
				placeholder="{$placeholder}"
				{$nodeAttributes}
			>
HTML;

		if ($allowSelection && !$isPublicControl)
		{
			$node .= static::renderControlSelectorButton($controlId, $fieldType, 'replace');
		}

		return $node . static::getJs($providers, $controlId);
	}

	private static function getMailboxList()
	{
		$mailboxes = Main\Mail\Sender::prepareUserMailboxes();

		return $mailboxes;
	}

	private static function makeMailboxListSelectOptions(array $mailboxes)
	{
		$options = [];
		foreach ($mailboxes as $mailbox)
		{
			$boxValue = sprintf(
				$mailbox['name'] ? '%s <%s>' : '%s%s',
				$mailbox['name'], $mailbox['email']
			);

			$options[$boxValue] = $boxValue;
		}

		return $options;
	}

	private static function getJs(array $mailboxes, string $controlId)
	{
		$mailboxesJs = Main\Web\Json::encode($mailboxes);
		$controlIdJs = \CUtil::JSEscape($controlId);

		$textAuto = \CUtil::JSEscape(Loc::getMessage('BP_FIELDTYPE_MAIL_SENDER_AUTO'));
		$textAdd = \CUtil::JSEscape(Loc::getMessage('BP_FIELDTYPE_MAIL_SENDER_ADD'));

		return <<<HTML
			<script>
				BX.ready(function()
				{
					var mailboxSelectorValue = document.getElementById('{$controlIdJs}');
					if (!mailboxSelectorValue)
					{
						return;
					}
			
					var mailboxes = {$mailboxesJs};
			
					var setMailbox = function(value)
					{
						mailboxSelectorValue.value = value;
					};
			
					var getMenuItems = function()
					{
						var i, menuItems = [{
							text: '{$textAuto}',
							onclick: function(e, item)
							{
								this.popupWindow.close();
								setMailbox('');
							}
						}];
			
						for (i = 0; i < mailboxes.length; ++i)
						{
							var mailbox = mailboxes[i];
							var mailboxName = mailbox['name'].length > 0
								? mailbox['name'] + ' <' + mailbox['email'] + '>'
								: mailbox['email'];
			
							menuItems.push({
								text: BX.util.htmlspecialchars(mailboxName),
								value: mailboxName,
								onclick: function(e, item)
								{
									this.popupWindow.close();
									setMailbox(item.value);
								}
							});
						}
			
						if (window.BXMainMailConfirm)
						{
							if (menuItems.length > 0)
							{
								menuItems.push({delimiter: true});
							}
			
							menuItems.push({
								text: '{$textAdd}',
								onclick: function(e, item)
								{
									this.popupWindow.close();
									window.BXMainMailConfirm.showForm(function(mailbox)
									{
										mailboxes.push(mailbox);
										setMailbox(mailbox['name'].length > 0
											? mailbox['name'] + ' <' + mailbox['email'] + '>'
											: mailbox['email']);
									});
								}
							});
						}
			
						return menuItems;
					};
			
					BX.bind(mailboxSelectorValue, 'click', function(e)
						{
							e.preventDefault();
							var menuId = 'crm-sma-mailboxes' + Math.random();
							BX.PopupMenu.show(
								menuId,
								this,
								getMenuItems(),
								{
									autoHide: true,
									offsetLeft: (BX.pos(this)['width'] / 2),
									angle: { position: 'top', offset: 0 },
									overlay: { backgroundColor: 'transparent' },
									events:
									{
										onPopupClose: function()
										{
											this.destroy();
										}
									}
								},
							);
						}
					);
				});
			</script>
HTML;
	}
}
