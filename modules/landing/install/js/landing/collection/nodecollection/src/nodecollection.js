import {BaseCollection} from 'landing.collection.basecollection';

/**
 * @memberOf BX.Landing.Collection
 */
export class NodeCollection extends BaseCollection
{
	getByNode(node: HTMLElement): ?BX.Landing.Block.Node
	{
		return this.find((item) => item.node === node);
	}

	getBySelector(selector: string): ?BX.Landing.Block.Node
	{
		return this.find((item) => item.selector === selector);
	}

	add(node: BX.Landing.Block.Node)
	{
		if (!!node && node instanceof BX.Landing.Block.Node)
		{
			super.add(node);
		}
	}

	matches(selector: string): NodeCollection
	{
		return this.filter((item) => {
			return item.node && selector.indexOf(':') === -1 && item.node.matches(selector);
		});
	}

	notMatches(selector: string): NodeCollection
	{
		return this.filter((item) => {
			return item.node && !item.node.matches(selector);
		});
	}

	getVisible(): NodeCollection
	{
		return this.filter((item) => !item.hidden);
	}
}