import { Type, Loc } from 'main.core';
import Uuid from '../helpers/uuid';

import type { NotificationOptions } from './notification-options';

/**
 * @memberof BX.UI.NotificationManager
 */
export default class Notification
{
	static SEPARATOR: string = 'u1F9D1';

	constructor(options: NotificationOptions)
	{
		this.setUid(options.id);
		this.setCategory(options.category);
		this.setTitle(options.title);
		this.setText(options.text);
		this.setIcon(options.icon);
		this.setInputPlaceholderText(options.inputPlaceholderText);
		this.createButtons(options.button1Text, options.button2Text);
	}

	static encodeIdToUid(id: string): string
	{
		return id + Notification.SEPARATOR + Uuid.getV4();
	}

	static decodeUidToId(uid: string): string
	{
		let id = uid.split(Notification.SEPARATOR);
		id.pop();

		return id.join();
	}

	setUid(id: string): void
	{
		if (!Type.isStringFilled(id))
		{
			throw new Error(`NotificationManager: Cannot create a notification without an ID`);
		}

		this.uid = Notification.encodeIdToUid(id);
	}

	getUid(): string
	{
		return this.uid;
	}

	getId(): string
	{
		return Notification.decodeUidToId(this.uid);
	}

	setCategory(category: string): void
	{
		this.category = Type.isStringFilled(category) ? category : '';
	}

	getCategory(): ?string
	{
		return this.category;
	}

	setTitle(title: string): void
	{
		this.title = Type.isStringFilled(title) ? title : '';
	}

	getTitle(): ?string
	{
		return this.title;
	}

	setText(text: string): void
	{
		this.text = Type.isStringFilled(text) ? text : '';
	}

	getText(): ?string
	{
		return this.text;
	}

	setIcon(icon: string): void
	{
		this.icon = Type.isStringFilled(icon) ? icon : '';
	}

	getIcon(): ?string
	{
		return this.icon;
	}

	setInputPlaceholderText(inputPlaceholderText: string): void
	{
		if (Type.isString(inputPlaceholderText))
		{
			this.inputPlaceholderText = inputPlaceholderText;
		}
	}

	getInputPlaceholderText(): ?string
	{
		return this.inputPlaceholderText;
	}

	createButtons(button1Text, button2Text)
	{
		if (this.getInputPlaceholderText())
		{
			this.setButton1Text(Loc.getMessage('UI_NOTIFICATION_MANAGER_REPLY'));
			this.setButton2Text(Loc.getMessage('UI_NOTIFICATION_MANAGER_CLOSE'));
		}
		else
		{
			this.setButton1Text(button1Text);
			this.setButton2Text(button2Text);
		}
	}

	setButton1Text(button1Text: string): void
	{
		if (Type.isStringFilled(button1Text))
		{
			this.button1Text = button1Text;
		}
	}

	getButton1Text(): ?string
	{
		return this.button1Text;
	}

	setButton2Text(button2Text: string): void
	{
		if (Type.isStringFilled(button2Text))
		{
			this.button2Text = button2Text;
		}
	}

	getButton2Text(): ?string
	{
		return this.button2Text;
	}
}
