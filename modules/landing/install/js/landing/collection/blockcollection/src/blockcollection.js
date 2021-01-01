import {BaseCollection} from 'landing.collection.basecollection';

/**
 * @memberOf BX.Landing.Collection
 */
export class BlockCollection extends BaseCollection
{
	getByNode(node: HTMLElement): ?BX.Landing.Block
	{
		return this.find((block) => block.node === node);
	}

	getByChildNode(node: HTMLElement): ?BX.Landing.Block
	{
		return this.find((block) => block.node.contains(node));
	}
}