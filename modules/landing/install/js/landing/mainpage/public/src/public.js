import { EventEmitter } from 'main.core.events';

export class Public extends EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.Mainpage.Public');

		this.initializeBlocks();
	}

	initializeBlocks()
	{
		const blocks = Array.from(document.getElementsByClassName("block-wrapper"));
		if (blocks.length > 0)
		{
			blocks.forEach((block) => {
				const eventData = [];
				eventData['block'] = block;
				BX.onCustomEvent("BX.Landing.Block:init", [eventData]);
			});
		}
	}
}