import {Type} from 'main.core';
import type { GroupData } from 'ui.entity-catalog';

export class Group
{
	#customData: Object = {};
	#selected: boolean = false;
	#disabled: boolean = false;
	#compare: Function = null;

	constructor()
	{
		if (this.constructor === Group)
		{
			throw new Error('Object of Abstract Class cannot be created');
		}
	}

	getId(): string
	{
		throw new Error("Abstract Method has no implementation");
	}

	getName(): string
	{
		throw new Error("Abstract Method has no implementation");
	}

	getIcon(): string
	{
		return '';
	}

	getTags(): Array
	{
		return [];
	}

	getAdviceTitle(): string
	{
		return '';
	}

	getAdviceAvatar(): string
	{
		return '';
	}

	setCustomData(customData = {}): this
	{
		this.#customData = customData;

		return this;
	}

	getCustomData(): Object
	{
		return this.#customData;
	}

	setSelected(selected = false): this
	{
		this.#selected = selected;

		return this;
	}

	getSelected(): boolean
	{
		return this.#selected;
	}

	setDisabled(disabled = false): this
	{
		this.#disabled = disabled;

		return this;
	}

	getDisabled(): boolean
	{
		return this.#disabled;
	}

	setCompare(compare: Function): this
	{
		this.#compare = compare;

		return this;
	}

	getCompare(): ?Function
	{
		return this.#compare;
	}

	getData(): GroupData
	{
		const data = {
			id: this.getId(),
			name: this.getName(),
			icon: this.getIcon(),
			tags: this.getTags(),
			adviceTitle: this.getAdviceTitle(),
			adviceAvatar: this.getAdviceAvatar(),
			customData: this.getCustomData(),
			selected: this.getSelected(),
			disabled: this.getDisabled(),
		};

		if (Type.isFunction(this.getCompare()))
		{
			data.compare = this.getCompare();
		}

		return data;
	}
}