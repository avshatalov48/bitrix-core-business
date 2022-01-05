import {Type} from 'main.core';
import {BaseEvent} from 'main.core.events';

import {MoreButton} from './morebutton';

export class Forum
{
	static cssClass = {
	};

	static processSpoilerToggle(event: BaseEvent)
	{
		let [ params ] = event.getCompatData();
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		if (!Type.isDomNode(params.node))
		{
			return;
		}

		const outerBlock = params.node.closest('.feed-post-block');
		if (!outerBlock)
		{
			return;
		}

		const bodyBlock = outerBlock.querySelector('.feed-post-text-block-inner-inner');
		if (!bodyBlock)
		{
			return;
		}

		const moreBlock = outerBlock.querySelector('.feed-post-text-more');

		MoreButton.recalcPost({
			bodyBlock: bodyBlock,
			informerBlock: moreBlock,
		});
	}
}
