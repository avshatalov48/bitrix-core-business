import {Type} from 'main.core';

import {MoreButton} from './morebutton';

export class Forum
{
	static cssClass = {
	};

	static processSpoilerToggle(params)
	{
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
