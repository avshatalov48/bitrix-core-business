import { Type } from 'main.core';
import type Dialog from '../dialog';

export default class BaseFooter
{
	dialog: Dialog = null;

	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		this.options = Type.isPlainObject(options) ? options : {};
		this.dialog = dialog;
	}

	getDialog(): Dialog
	{
		return this.dialog;
	}

	getOptions(): { [option: string]: any }
	{
		return this.options;
	}

	getOption(option: string, defaultValue?: any): any
	{
		if (!Type.isUndefined(this.options[option]))
		{
			return this.options[option];
		}
		else if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}

		return null;
	}

	render(): HTMLElement
	{
		throw new Error('You must implement render() method.');
	}
}