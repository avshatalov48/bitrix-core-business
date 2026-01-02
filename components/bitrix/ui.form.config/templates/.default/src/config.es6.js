import { Loc } from 'main.core';
import { Button } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';
/**
 * @bxjs_lang_path template.php
 */

export class Config
{
	static isOpen = false;
	static subscribedItems = [];

	static popupIsOpen(): boolean
	{
		return this.isOpen;
	}

	static setPopupClose(): void
	{
		this.isOpen = false;
	}

	static setPopupOpen(): void
	{
		this.isOpen = true;
	}

	static isSubscribed(scopeId): boolean
	{
		return Config.subscribedItems.includes(scopeId);
	}

	static addSubscribed(scopeId): void
	{
		Config.subscribedItems.push(scopeId);
	}

	static showConfirmDialog(title, acceptButtonText, acceptFunc): void
	{
		MessageBox.confirm(
			title,
			acceptFunc,
			acceptButtonText,
			undefined,
			undefined,
			true,
		);
	}
}
