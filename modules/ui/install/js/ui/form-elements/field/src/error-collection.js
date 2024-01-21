import { Loc } from 'main.core';
import {OrderedArray} from "main.core.collections";

export class ErrorCollection extends OrderedArray
{
	constructor(errors: Array = [], comparator: Function = null)
	{
		super(comparator);

		this.addItems(errors);
	}

	addItems(items: [T])
	{
		for (const item of items)
		{
			this.add(item);
		}
	}

	merge(errorCollection: ErrorCollection): ErrorCollection
	{
		this.addItems(errorCollection.getAll());

		return this;
	}

	static showSystemError(text: string): void
	{
		top.BX.UI.Notification.Center.notify({
			content: text,
			position: 'bottom-right',
			category: 'menu-self-item-popup',
			autoHideDelay: 3000,
		});
	}
}