/**
 * @memberof BX.UI.UserFieldFactory
 */
export class EnumItem
{
	constructor(value = null, id = null)
	{
		this.value = value;
		this.id = id;
	}

	setNode(node: Element)
	{
		this.node = node;
	}

	getId(): ?number
	{
		return this.id;
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