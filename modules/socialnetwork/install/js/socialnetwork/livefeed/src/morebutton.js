import {Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import {FeedInstance} from './feed';

export class MoreButton
{
	static cssClass = {
		post: 'feed-post-block',
		postShort: 'feed-post-block-short',
		postSeparator: 'feed-post-block-separator',
		postText: 'feed-post-text-block',
		postTextInner: 'feed-post-text-block-inner',
		postTextInnerInner: 'feed-post-text-block-inner-inner',
		more: 'feed-post-text-more',
		comment: 'feed-com-text',
	};

	static recalcPost(params)
	{
		if (!Type.isDomNode(params.informerBlock))
		{
			return;
		}

		const blockHeight = (!Type.isUndefined(params.arPos) ? params.arPos.height : params.bodyBlock.offsetHeight);

		const postBlock = params.informerBlock.closest(`.${this.cssClass.post}`);
		if (!postBlock)
		{
			return;
		}

		if (blockHeight <= 284)
		{
			postBlock.classList.add(this.cssClass.postShort)
			postBlock.classList.add(this.cssClass.postSeparator)
		}
		else
		{
			postBlock.classList.remove(this.cssClass.postShort)
		}
	}

	static recalcPostsList()
	{
		const buttonsList = FeedInstance.getMoreButtons();
		buttonsList.forEach((buttonData, index, object) => {

			if (
				!Type.isPlainObject(buttonData)
				|| !Type.isStringFilled(buttonData.bodyBlockID)
			)
			{
				return;
			}

			const bodyNode = document.getElementById(buttonData.bodyBlockID)
			if (!bodyNode)
			{
				return;
			}

			if (Type.isStringFilled(buttonData.outerBlockID))
			{
				const outerNode = document.getElementById(buttonData.outerBlockID);
				if (outerNode)
				{
					if (outerNode.offsetWidth < bodyNode.offsetWidth)
					{
						const innerNode = outerNode.querySelector(`div.${this.cssClass.postTextInner}`);
						innerNode.style.overflowX = 'scroll';
					}
				}
			}

			this.recalcPost({
				arPos: { height: (bodyNode.offsetHeight + bodyNode.offsetTop)},
				informerBlock: (Type.isStringFilled(buttonData.informerBlockID) ? document.getElementById(buttonData.informerBlockID) : null)
			});
			object.splice(index, 1);
		});

		const feedContainer = document.getElementById('log_internal_container');
		if (!feedContainer)
		{
			return;
		}

		const onLoadImageList = feedContainer.querySelectorAll('[data-bx-onload="Y"]');
		onLoadImageList.forEach((imageNode) => {
			imageNode.addEventListener('load', (e) => {

				let outerBlock = e.currentTarget.closest(`.${this.cssClass.comment}`);
				if (!outerBlock) // post
				{
					outerBlock = e.currentTarget.closest(`.${this.cssClass.post}`);
					if (outerBlock)
					{
						const bodyBlock = outerBlock.querySelector(`.${this.cssClass.postTextInnerInner}`);
						if (bodyBlock)
						{
							this.recalcPost({
								bodyBlock: bodyBlock,
								informerBlock: outerBlock.querySelector(`.${this.cssClass.more}`),
							});
						}
					}
				}

				e.currentTarget.setAttribute('data-bx-onload', 'N');
			});
		});
	}

	static recalcCommentsList()
	{
		EventEmitter.emit('OnUCMoreButtonListRecalc', new BaseEvent({
			compatData: [],
		}));
	}

	static clearCommentsList()
	{
		EventEmitter.emit('OnUCMoreButtonListClear', new BaseEvent({
			compatData: [],
		}));
	}

	static expand(textBlock)
	{
		if (!Type.isDomNode(textBlock))
		{
			return;
		}

		const postBlock = textBlock.closest(`.${this.cssClass.post}`);
		if (!postBlock)
		{
			return;
		}

		postBlock.classList.add(this.cssClass.postShort);
		postBlock.classList.add(this.cssClass.postSeparator);
	}

	/*
	is not used actually by disk uf
	*/
	static lazyLoadCheckVisibility(image)
	{
		if (
			!Type.isPlainObject(image)
			|| !Type.isDomNode(image.node)
		)
		{
			return true;
		}

		const imageNode = image.node;

		let textType = 'comment';

		let textBlock = imageNode.closest(`.${this.cssClass.comment}`);
		if (!textBlock)
		{
			textType = 'post';
			textBlock = imageNode.closest(`.${this.cssClass.postText}`);
		}

		if (!textBlock)
		{
			return true;
		}

		const moreBlock = textBlock.querySelector(`div.${this.cssClass.more}`);
		if (
			!moreBlock
			|| moreBlock.style.display === 'none'
		)
		{
			return true;
		}

		return imageNode.parentNode.parentNode.offsetTop < (textType === 'comment' ? 220 : 270);
	}
}
