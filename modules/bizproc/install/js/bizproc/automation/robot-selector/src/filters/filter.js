import type { FilterData } from 'ui.entity-catalog';

export class Filter
{
	#applied: boolean = false;

	constructor()
	{
		if (this.constructor === Filter)
		{
			throw new Error('Object of Abstract Class cannot be created');
		}
	}

	getId(): string
	{
		throw new Error("Abstract Method has no implementation");
	}

	getText(): string
	{
		throw new Error("Abstract Method has no implementation");
	}

	getAction(): Function
	{
		throw new Error("Abstract Method has no implementation");
	}

	setApplied(applied = false): this
	{
		this.#applied = applied;

		return this;
	}

	getApplied(): boolean
	{
		return this.#applied;
	}

	getData(): FilterData
	{
		return {
			id: this.getId(),
			text: this.getText(),
			action: this.getAction(),
			applied: this.getApplied(),
		};
	}
}