import {BaseCollection} from 'landing.collection.basecollection';

/**
 * @memberOf BX.Landing.Collection
 */
export class CardCollection extends BaseCollection
{
	getByNode(node: HTMLElement): ?BX.Landing.Block.Card
	{
		return this.find((card) => card.node === node);
	}

	getBySelector(selector: string): ?BX.Landing.Block.Card
	{
		return this.find((card) => card.selector === selector);
	}

	add(card: BX.Landing.Block.Card)
	{
		if (!!card && card instanceof BX.Landing.Block.Card)
		{
			super.add(card);
		}
	}

	matches(selector: string): CardCollection
	{
		return this.filter((item) => item.node.matches(selector));
	}

	notMatches(selector: string): CardCollection
	{
		return this.filter((item) => !item.node.matches(selector));
	}
}