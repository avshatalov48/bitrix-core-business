import { Dom } from 'main.core';

export class BaseField
{
	#fieldId: string;
	#gridId: ?string;

	constructor(params: { fieldId: string, gridId: string})
	{
		this.#fieldId = params.fieldId;
		this.#gridId = params.gridId ?? null;
	}

	getGridId(): ?string
	{
		return this.#gridId;
	}

	getFieldId(): string
	{
		return this.#fieldId;
	}

	getGrid(): BX.Main.grid | null
	{
		let grid = null;

		if (this.#gridId)
		{
			grid = BX.Main.gridManager.getById(this.#gridId);
		}

		return grid?.instance;
	}

	getFieldNode(): HTMLElement
	{
		return document.getElementById(this.getFieldId());
	}

	appendToFieldNode(element: HTMLElement): void
	{
		Dom.append(element, this.getFieldNode());
	}
}
