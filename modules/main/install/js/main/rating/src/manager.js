import { Type, Runtime } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import { RatingRender } from './render';
import { ListPopup } from './listpopup';

export class RatingManager
{
	static mobile = false;
	static initialized = false;
	static displayHeight = 0;
	static startScrollTop = 0;
	static entityList = [];
	static ratingNodeList = new Map();
	static delayedList = new Map();

	static init(params)
	{
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		if (this.initialized)
		{
			return;
		}

		this.mobile = (!Type.isUndefined(params.mobile) && !!params.mobile);

		this.initialized = true;

		this.setDisplayHeight();

		if (!this.mobile)
		{
			window.addEventListener('scroll', Runtime.throttle(() => {
				this.getInViewScope();
			}, 80), { passive: true });

			window.addEventListener('resize', this.setDisplayHeight.bind(this));
		}

		EventEmitter.subscribe('onBeforeMobileLivefeedRefresh', RatingRender.reactionsPopupMobileHide)
		EventEmitter.subscribe('BX.MobileLF:onCommentsGet', RatingRender.onMobileCommentsGet)

		if (this.mobile)
		{
			// new one
			BXMobileApp.addCustomEvent('onRatingLike', RatingRender.onRatingLike);
		}

		if (this.mobile)
		{
			BXMobileApp.addCustomEvent('onPull-main', (data) => {
				if (data.command == 'rating_vote')
				{
					RatingLike.LiveUpdate(data.params);
				}
			});
		}
		else
		{
			EventEmitter.subscribe('onPullEvent-main', (event: BaseEvent) => {
				const [ command, params ] = event.getCompatData();

				if (command === 'rating_vote')
				{
					RatingLike.LiveUpdate(params);
				}
			});

			if (
				!Type.isUndefined(window.BX.SidePanel)
				&& BX.SidePanel.Instance.getTopSlider()
			)
			{
				EventEmitter.subscribe(
					BX.SidePanel.Instance.getTopSlider().getWindow(),
					'SidePanel.Slider:onCloseComplete',
					ListPopup.removeOnCloseHandler
				);
			}
		}
	}

	static setDisplayHeight()
	{
		this.displayHeight = document.documentElement.clientHeight;
	}

	static getInViewScope()
	{
		let ratingNode = null;
		this.delayedList.forEach((value, key) => {

			ratingNode = BX(this.getNode(key));

			if (!ratingNode)
			{
				return;
			}

			if (this.isNodeVisibleOnScreen(ratingNode))
			{
				this.fireAnimation(key);
			}
		});
	}

	static addNode(entityId, node)
	{
		if (
			!Type.isDomNode(node)
//			|| !Type.isUndefined(this.ratingNodeList.get(entityId))
		)
		{
			return;
		}

		this.ratingNodeList.set(entityId, node);
	}


	static getNode(entityId)
	{
		const node = this.ratingNodeList.get(entityId);
		return (!Type.isUndefined(node) ? node : false);
	}

	static isNodeVisibleOnScreen(node)
	{
		const coords = node.getBoundingClientRect();
		const visibleAreaTop = Number(this.displayHeight / 10);
		const visibleAreaBottom = Number(this.displayHeight * 9 / 10);

		return (
			(
				(
					coords.top > 0
					&& coords.top < visibleAreaBottom
				)
				|| (
					coords.bottom > visibleAreaTop
					&& coords.bottom < this.displayHeight
				)
			)
			&& (
				this.mobile
				|| !(
					(
						coords.top < visibleAreaTop
						&& coords.bottom < visibleAreaTop
				)
				|| (
					coords.top > visibleAreaBottom
					&& coords.bottom > visibleAreaBottom
				)
				)
			)
		);
	}

	static fireAnimation(key)
	{
		this.delayedList.delete(key);
	}

	static addEntity(entityId, ratingObject)
	{
		if (
			!this.entityList.includes(entityId)
			&& ratingObject.topPanelContainer
			)
			{
				this.entityList.push(entityId);
				this.addNode(entityId, ratingObject.topPanelContainer);
			}
	}

	static live(params)
	{
		if (
			Type.isUndefined(params.TYPE)
			|| params.TYPE !== 'ADD'
			|| !Type.isStringFilled(params.ENTITY_TYPE_ID)
			|| Type.isUndefined(params.ENTITY_ID)
			|| Number(params.ENTITY_ID) <= 0
		)
		{
			return;
		}

		const key = `${params.ENTITY_TYPE_ID}_${params.ENTITY_ID}`;
		if (!this.checkEntity(key))
		{
			return;
		}

		const ratingNode = this.getNode(key);
		if (!ratingNode)
		{
			return false;
		}

		if (this.isNodeVisibleOnScreen(ratingNode))
		{
			this.fireAnimation(key);
		}
		else
		{
			this.addDelayed(params)
		}
	}

	static checkEntity(entityId)
	{
		return this.entityList.includes(entityId);
	}

	static addDelayed(liveParams)
	{
		if (
			!Type.isStringFilled(liveParams.ENTITY_TYPE_ID)
			|| Type.isUndefined(liveParams.ENTITY_ID)
			|| Number(liveParams.ENTITY_ID) <= 0
		)
		{
			return;
		}

		const key = `${liveParams.ENTITY_TYPE_ID}_${liveParams.ENTITY_ID}`;

		let delayedListItem = this.delayedList.get(key);
		if (Type.isUndefined(delayedListItem))
		{
			delayedListItem = [];
		}

		delayedListItem.push(liveParams);
		this.delayedList.set(key, delayedListItem);
	}
}
