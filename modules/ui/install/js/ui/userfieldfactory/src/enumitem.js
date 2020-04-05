/**
 * @memberof BX.UI.UserFieldFactory
 */
export class EnumItem
{
	constructor(value = null)
	{
		this.value = value;
	}

	setNode(node: Element)
	{
		this.node = node;
	}

	getNode(): ?Element
	{
		return this.node;
	}

	getInput(): ?Element
	{
		const node = this.getNode();
		if(!node)
		{
			return null;
		}
		if(node instanceof HTMLInputElement)
		{
			return node;
		}
		return node.querySelector('input');
	}

	getValue(): string
	{
		const input = this.getInput();
		if(input && input.value)
		{
			return input.value;
		}

		return this.value || '';
	}
}