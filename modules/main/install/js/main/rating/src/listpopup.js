import { Type, Dom, Event, ajax } from 'main.core';
import { Popup } from 'main.popup';
import { EventEmitter } from 'main.core.events';

import { RatingManager } from './manager';
import { RatingLike } from './like';
import { RatingRender } from './render';

export class ListPopup
{
	static popupLikeId = null;

	static removeOnCloseHandler = this.removeOnClose.bind(this);

	static getListPopup(params)
	{
		const likeId = params.likeId;
		const likeInstance = RatingLike.getInstance(likeId);
		const target = params.target;
		const reaction = params.reaction;
		const nodeId = params.nodeId;

		if (this.popupLikeId === likeId)
		{
			return false;
		}

		if (likeInstance.popupContentPage != 1)
		{
			return;
		}

		this.List(likeId, 1, reaction, true);

		likeInstance.popupTimeoutIdShow = setTimeout(() => {
			this.getListPopupShow({
				likeId: likeId,
				reaction: reaction,
				target: target,
				nodeId: nodeId,
			})
		}, 100);
	}

	static getListPopupShow(params)
	{
		const likeId = params.likeId;
		const likeInstance = RatingLike.getInstance(likeId);
		const target = params.target;
		const reaction = params.reaction;
		const nodeId = params.nodeId;

		likeInstance.resultPopupAnimation = true;

		setTimeout(() => {
			this.getListPopupAnimation({
				likeId: likeId,
			});
		}, 500);

		if (likeInstance.mouseInShowPopupNode[reaction])
		{
			this.OpenWindow(
				likeId,
				null,
				target,
				nodeId
			);
		}
	}

	static getListPopupAnimation(params)
	{
		const likeId = params.likeId;
		const likeInstance = RatingLike.getInstance(likeId);

		likeInstance.resultPopupAnimation = false;
	}

	static OpenWindow(likeId, clickEvent, target, targetId)
	{
		const likeInstance = RatingLike.getInstance(likeId);

		if (Number(likeInstance.countText.innerHTML) === 0)
		{
			return;
		}

		const bindNode = (
			likeInstance.template === 'standart'
				? likeInstance.count
				: (
					likeInstance.version === 2
						? (
							Type.isDomNode(target)
								? target
								: (
									Type.isStringFilled(targetId) && document.getElementById(targetId)
										? document.getElementById(targetId)
										: null
								)
						)
						: likeInstance.box
				)
		);

		if (!Type.isDomNode(bindNode))
		{
			return;
		}

		if (likeInstance.popup == null)
		{
			const globalZIndex = this.getGlobalIndex(bindNode);

			const popupClassNameList = [];
			if (likeInstance.topPanel)
			{
				popupClassNameList.push('bx-ilike-wrap-block-react-wrap');
			}
			if (RatingManager.mobile)
			{
				popupClassNameList.push('bx-ilike-mobile-wrap');
			}

			likeInstance.popup = new Popup({
				id: `ilike-popup-${likeId}`,
				bindElement: bindNode,
				lightShadow : true,
				offsetTop: 0,
				offsetLeft: (
					!Type.isUndefined(clickEvent)
					&& !Type.isNull(clickEvent)
					&& !Type.isUndefined(clickEvent.offsetX)
						? (clickEvent.offsetX - 100)
						: (likeInstance.version == 2 ? -30 : 5)
				),
				autoHide: true,
				closeByEsc: true,
				zIndexAbsolute: (globalZIndex > 1000 ? globalZIndex + 1 : 1000),
				bindOptions: {
					position: 'top',
				},
				animation: 'fading-slide',
				events: {
					onPopupClose: () => {
						this.popupLikeId = null;
					},
					onPopupDestroy: () => {},
				},
				content : document.getElementById(`bx-ilike-popup-cont-${likeId}`),
				className: popupClassNameList.join(' '),
			});

			if (
				!likeInstance.topPanel
				&& !RatingManager.mobile
			)
			{
				likeInstance.popup.setAngle({});

				document.getElementById(`ilike-popup-${likeId}`).addEventListener('mouseout', () => {
					clearTimeout(likeInstance.popupTimeout);
					likeInstance.popupTimeout = setTimeout(() => {
						likeInstance.popup.close();
					}, 1000);
				});

				document.getElementById(`ilike-popup-${likeId}`).addEventListener('mouseover', () => {
					clearTimeout(likeInstance.popupTimeout);
				});
			}
		}
		else
		{
			if (
				!Type.isUndefined(clickEvent)
				&& !Type.isNull(clickEvent)
				&& !Type.isUndefined(clickEvent.offsetX)
			)
			{
				likeInstance.popup.offsetLeft = (clickEvent.offsetX - 100);
			}

			likeInstance.popup.setBindElement(bindNode);
		}

		if (this.popupLikeId !== likeId)
		{
			const popupLikeInstance = RatingLike.getInstance(this.popupLikeId);
			if (popupLikeInstance)
			{
				popupLikeInstance.popup.close();
			}
		}

		this.popupLikeId = likeId;

		likeInstance.popup.show();

		this.AdjustWindow(likeId);
	}

	static getGlobalIndex(element)
	{
		let index = 0;
		let propertyValue = '';

		do
		{
			propertyValue = Dom.style(element, 'z-index');
			if (propertyValue !== 'auto')
			{
				index = !Number.isNaN(parseInt(propertyValue)) ? index : 0;
			}
			element = element.offsetParent;
		}
		while (
			element
			&& element.tagName !== 'BODY'
			);

		return index;
	}

	static removeOnClose()
	{
		EventEmitter.unsubscribe(BX.SidePanel.Instance.getTopSlider().getWindow(), 'SidePanel.Slider:onClose', this.removeOnCloseHandler);

		const popupLikeInstance = RatingLike.getInstance(this.popupLikeId);
		if (popupLikeInstance)
		{
			popupLikeInstance.popup.close();
		}
	}

	static AdjustWindow(likeId)
	{
		let likeInstance = RatingLike.getInstance(likeId);

		if (!likeInstance.popup)
		{
			return;
		}

		likeInstance.popup.bindOptions.forceBindPosition = true;
		likeInstance.popup.adjustPosition();
		likeInstance.popup.bindOptions.forceBindPosition = false;
	}

	static PopupScroll(likeId)
	{
		const likeInstance = RatingLike.getInstance(likeId);

		let contentContainerNodeList = likeInstance.popupContent.querySelectorAll('.bx-ilike-popup-content'); // reactions
		if (contentContainerNodeList.length <= 0)
		{
			contentContainerNodeList = [ likeInstance.popupContent ];
		}

		contentContainerNodeList.forEach((contentContainerNode) => {
			contentContainerNode.addEventListener('scroll', (e) => {
				if (e.target.scrollTop <= (e.target.scrollHeight - e.target.offsetHeight) / 1.5)
				{
					return;
				}
				this.List(likeId, null, (likeInstance.version == 2 ? RatingRender.popupCurrentReaction : false));
				Event.unbindAll(e.target);
			});
		});
	}

	static List(likeId, page, reaction, clear)
	{
		const likeInstance = RatingLike.getInstance(likeId);

		if (Number(likeInstance.countText.innerHTML) === 0)
		{
			return false;
		}

		reaction = (Type.isStringFilled(reaction) ? reaction : '');

		if (Type.isNull(page))
		{
			page = (
				likeInstance.version === 2
					? (
						!Type.isUndefined(RatingRender.popupPagesList[reaction])
							? RatingRender.popupPagesList[reaction]
							: 1
					)
					: likeInstance.popupContentPage
			);
		}

		if (
			clear
			&& Number(page) === 1
			&& likeInstance.version === 2
		)
		{
			RatingRender.clearPopupContent({
				likeId: likeId,
			});
		}

		if (likeInstance.listXHR)
		{
			likeInstance.listXHR.abort();
		}

		ajax.runAction('main.rating.list', {
			data: {
				params: {
					RATING_VOTE_TYPE_ID: likeInstance.entityTypeId,
					RATING_VOTE_KEY_SIGNED: likeInstance.keySigned,
					RATING_VOTE_ENTITY_ID: likeInstance.entityId,
					RATING_VOTE_LIST_PAGE: page,
					RATING_VOTE_REACTION: (reaction === 'all' ? '' : reaction),
					PATH_TO_USER_PROFILE: likeInstance.pathToUserProfile,
				},
			},
			onrequeststart: (xhr) => {
				likeInstance.listXHR = xhr;
			},
		}).then((result) => {
				this.onListSuccess(result.data, {
					likeId: likeId,
					reaction: reaction,
					page: page,
					clear: clear,
				});
			},
			() => {}
		);

		return false;
	}

	static onListSuccess(data, params)
	{
		if (!data)
		{
			return false;
		}

		const likeInstance = RatingLike.getInstance(params.likeId);

		likeInstance.countText.innerHTML = data.items_all;

		if (Number(data.items_page) === 0)
		{
			if (Number(data.list_page) === 1)
			{
				likeInstance.popup.close();
			}
			return false;
		}

		if (likeInstance.version === 2)
		{
			RatingRender.buildPopupContent({
				likeId: params.likeId,
				reaction: params.reaction,
				rating: likeInstance,
				page: params.page,
				data: data,
				clear: params.clear,
			});
			likeInstance.topPanel.setAttribute('data-popup', 'Y');
		}
		else
		{
			RatingRender.buildPopupContentNoReactions({
				rating: likeInstance,
				page: params.page,
				data: data,
			});
		}

		this.AdjustWindow(params.likeId);
		this.PopupScroll(params.likeId);
	}

	static onResultClick(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : false);
		const clickEvent = (!Type.isUndefined(params.event) ? params.event : false);
		const reaction = (Type.isStringFilled(params.reaction) ? params.reaction : '');
		const likeInstance = RatingLike.getInstance(likeId);

		if (likeInstance.resultPopupAnimation)
		{
			return;
		}

		if (
			likeInstance.popup
			&& likeInstance.popup.isShown()
		)
		{
			likeInstance.popup.close();
		}
		else
		{
			clearTimeout(likeInstance.popupTimeoutIdList);
			clearTimeout(likeInstance.popupTimeoutIdShow);

			if (
				likeInstance.popupContentPage == 1
				&& (
					likeInstance.topPanel.getAttribute('data-popup') !== 'Y'
					|| likeInstance.popupCurrentReaction != reaction
				)
			)
			{
				this.List(likeId, 1, reaction, true);
			}

			this.OpenWindow(
				likeId,
				(clickEvent.currentTarget === likeInstance.count ? null : clickEvent),
				clickEvent.currentTarget,
				clickEvent.currentTarget.id
			);
		}
	}

	static onResultMouseEnter(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : false);
		const mouseEnterEvent = (!Type.isUndefined(params.event) ? params.event : null);
		const reaction = (Type.isStringFilled(params.reaction) ? params.reaction : '');
		const nodeId = (
			mouseEnterEvent && Type.isStringFilled(mouseEnterEvent.currentTarget.id)
				? mouseEnterEvent.currentTarget.id
				: ''
		);

		const likeInstance = RatingLike.getInstance(likeId);

		likeInstance.mouseInShowPopupNode[reaction] = true;

		clearTimeout(likeInstance.popupTimeoutIdList);
		clearTimeout(likeInstance.popupTimeoutIdShow);

		likeInstance.popupTimeoutIdList = setTimeout(() => {
			this.getListPopup({
				likeId: likeId,
				target: mouseEnterEvent.currentTarget,
				reaction: reaction,
				nodeId: nodeId,
			});
		}, 300);
	}

	static onResultMouseLeave(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : false);
		const reaction = (Type.isStringFilled(params.reaction) ? params.reaction : '');
		const likeInstance = RatingLike.getInstance(likeId);

		likeInstance.mouseInShowPopupNode[reaction] = false;
		likeInstance.resultPopupAnimation = false;
	}
}
