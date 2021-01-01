import {BaseCollection} from 'landing.collection.basecollection';

/**
 * @memberOf BX.Landing.UI.Collection
 */
export class ButtonCollection extends BaseCollection
{
	add(button: BX.Landing.UI.Button.BaseButton)
	{
		if (!!button && button instanceof BX.Landing.UI.Button.BaseButton)
		{
			super.add(button);
		}
	}

	getByValue(value: any): ?BX.Landing.UI.Button.BaseButton
	{
		return this.find((button) => `${button.layout.value}` === `${value}`);
	}

	getActive(): ?BX.Landing.UI.Button.BaseButton
	{
		return this.find((button) => button.isActive());
	}

	getByNode(node: HTMLElement): ?BX.Landing.UI.Button.BaseButton
	{
		return this.find((button) => button.layout === node);
	}
}