import { Type, Loc, Dom, Runtime, pos, GetWindowSize } from 'main.core';
import { EventEmitter } from 'main.core.events'
import {Lottie} from 'ui.lottie';

import { RatingManager } from './manager';
import { RatingLike } from './like';
import { ListPopup } from './listpopup';
import likeAnimatedEmojiData from '../animations/em_01.json';
import laughAnimatedEmojiData from '../animations/em_02.json';
import wonderAnimatedEmojiData from '../animations/em_03.json';
import cryAnimatedEmojiData from '../animations/em_04.json';
import angryAnimatedEmojiData from '../animations/em_05.json';
import facepalmAnimatedEmojiData from '../animations/em_06.json';
import kissAnimatedEmojiData from '../animations/em_07.json';

export class RatingRender
{
	static reactionsList = [ 'like', 'kiss', 'laugh', 'wonder', 'cry', 'angry', 'facepalm' ];
	static reactionsAnimationData = {
		like: likeAnimatedEmojiData,
		kiss: kissAnimatedEmojiData,
		laugh: laughAnimatedEmojiData,
		wonder: wonderAnimatedEmojiData,
		cry: cryAnimatedEmojiData,
		angry: angryAnimatedEmojiData,
		facepalm: facepalmAnimatedEmojiData,
	}
	static popupCurrentReaction = false;
	static popupPagesList = [];
	static popupSizeInitialized = false;
	static blockShowPopup = false;
	static blockShowPopupTimeout = false;
	static afterClickBlockShowPopup = false;
	static afterClickHandler = null;
	static touchStartPosition = null;
	static touchCurrentPosition = {
		x: null,
		y: null,
	};
	static currentReactionNodeHover = null;

	static touchMoveDeltaY = null;
	static touchScrollTop = 0;

	static hasMobileTouchMoved = null;
	static mobileOverlay = null;

	static reactionsPopup = null;
	static reactionsPopupAnimation = null;
	static reactionsPopupAnimation2 = null;
	static reactionsPopupLikeId = null;
	static reactionsPopupMouseOutHandler = null;
	static reactionsPopupOpacityState = 0;
	static reactionsPopupTouchStartIn = null;
	static reactionsPopupPositionY = null;
	static blockTouchEndByScroll = false;

	static reactionsPopupMobileTouchEndHandler = this.reactionsPopupMobileTouchEnd.bind(this);
	static reactionsPopupMobileTouchMoveHandler = this.reactionsPopupMobileTouchMove.bind(this);
	static reactionsPopupMobileHideHandler = this.reactionsPopupMobileHide.bind(this);

	static getTopUsersText(params)
	{
		const currentUserId = Number(Loc.getMessage('USER_ID'));
		const you = (!Type.isUndefined(params.you) ? !!params.you : false);
		const topList = (!Type.isUndefined(params.top) && Type.isArray(params.top) ? params.top : []);
		const more = (!Type.isUndefined(params.more) ? Number(params.more) : 0);
		let result = '';

		if (
			topList.length <= 0
			&& !you
			&& (
				RatingManager.mobile
				|| more <= 0
			)
		)
		{
			return result;
		}

		if (RatingManager.mobile)
		{
			if (you)
			{
				topList.push({
					ID: currentUserId,
					NAME_FORMATTED: Loc.getMessage('RATING_LIKE_TOP_TEXT3_YOU'),
					WEIGHT: 1,
				});
			}

			result = Loc.getMessage(`RATING_LIKE_TOP_TEXT3_${(topList.length > 1 ? '2' : '1')}`)
				.replace('#OVERFLOW_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '')
				.replace('#OVERFLOW_END#', RatingManager.mobile ? '</span>' : '');
		}
		else
		{
			result = Loc.getMessage(`RATING_LIKE_TOP_TEXT2_${(you ? 'YOU_' : '')}${(topList.length)}${(more > 0 ? '_MORE' : '')}`)
				.replace('#OVERFLOW_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '')
				.replace('#OVERFLOW_END#', RatingManager.mobile ? '</span>' : '')
				.replace('#MORE_START#', RatingManager.mobile ? '<span class="feed-post-emoji-text-item-more">' : '&nbsp;')
				.replace('#MORE_END#', RatingManager.mobile ? '</span>' : '');
		}

		if (RatingManager.mobile)
		{
			topList.sort((a, b) => {
				if (parseInt(a.ID) === currentUserId)
				{
					return -1;
				}

				if (parseInt(b.ID) === currentUserId)
				{
					return 1;
				}

				if (parseFloat(a.WEIGHT) === parseFloat(b.WEIGHT))
				{
					return 0;
				}

				return (parseFloat(a.WEIGHT) > parseFloat(b.WEIGHT) ? -1 : 1);
			});

			const userNameList = topList.map((item) => {
				return item.NAME_FORMATTED;
			});

			let userNameBegin = '';
			let userNameEnd = '';

			if (userNameList.length === 1)
			{
				userNameBegin = userNameList.pop();
				userNameEnd = '';
			}
			else
			{
				userNameBegin = userNameList.slice(0, userNameList.length - 1)
					.join(Loc.getMessage('RATING_LIKE_TOP_TEXT3_USERLIST_SEPARATOR').replace(/#USERNAME#/g, ''));
				userNameEnd = userNameList[userNameList.length - 1];
			}

			result = result.replace('#USER_LIST_BEGIN#', userNameBegin)
				.replace('#USER_LIST_END#', userNameEnd);
		}
		else
		{
			topList.forEach((item, i) => {

				result = result.replace(
					`#USER_${(Number(i) + 1)}#`,
					`<span class="feed-post-emoji-text-item">${item.NAME_FORMATTED}</span>`
				);
			});

			result = result.replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">' + more + '</span>');
		}

		return result;
	}

	static getUserReaction(params)
	{
		return (
			Type.isDomNode(params.userReactionNode)
				? params.userReactionNode.getAttribute('data-value')
				: ''
		);
	}

	static setReaction(params)
	{
		if (
			Type.isUndefined(params.rating)
			|| !Type.isStringFilled(params.likeId)
		)
		{
			return;
		}

		const action = (Type.isStringFilled(params.action) ? params.action : 'add');
		if (!['add', 'cancel', 'change'].includes(action))
		{
			return;
		}

		const likeId = params.likeId;
		const rating = params.rating;
		const userReaction = (Type.isStringFilled(params.userReaction) ? params.userReaction : Loc.getMessage('RATING_LIKE_REACTION_DEFAULT'));
		const userReactionOld = (Type.isStringFilled(params.userReactionOld) ? params.userReactionOld : Loc.getMessage('RATING_LIKE_REACTION_DEFAULT'));
		if (
			action === 'change'
			&& userReaction === userReactionOld
		)
		{
			return;
		}

		const totalCount = (!Type.isUndefined(params.totalCount) ? Number(params.totalCount) : null);
		const currentUserId = Number(Loc.getMessage('USER_ID'));
		const userId = (!Type.isUndefined(params.userId) ? Number(params.userId) : currentUserId);

		const userReactionNode = this.getNode(rating.userReactionNode);
		const reactionsNode = this.getNode(rating.reactionsNode);
		const topPanel = this.getNode(rating.topPanel);
		const topPanelContainer = this.getNode(rating.topPanelContainer);
		const topUsersText = this.getNode(rating.topUsersText);
		const countText = this.getNode(rating.countText);
		const buttonText = this.getNode(rating.buttonText);

		if (
			userId === currentUserId // not pull
			&& userReactionNode
		)
		{
			userReactionNode.setAttribute('data-value', [ 'add', 'change' ].includes(action) ? userReaction : '');
		}

		let i = 0;
		let elements = [];
		let elementsNew = [];

		if (
			totalCount !== null
			&& topPanel
			&& topUsersText
			&& reactionsNode
		)
		{
			if (totalCount > 0)
			{
				topPanelContainer.classList.add('feed-post-emoji-top-panel-container-active');

				if (!topPanel.classList.contains('feed-post-emoji-container-toggle'))
				{
					topPanel.classList.add('feed-post-emoji-container-toggle');
					topUsersText.classList.add('feed-post-emoji-move-to-right');
					reactionsNode.classList.add('feed-post-emoji-icon-box-show');
				}
			}
			else if (totalCount <= 0)
			{
				topPanelContainer.classList.remove('feed-post-emoji-top-panel-container-active');

				if (topPanel.classList.contains('feed-post-emoji-container-toggle'))
				{
					topPanel.classList.remove('feed-post-emoji-container-toggle');
					topUsersText.classList.remove('feed-post-emoji-move-to-right');
					reactionsNode.classList.remove('feed-post-emoji-icon-box-show');
				}
			}
		}

		if (
			totalCount !== null
			&& countText
		)
		{
			if (
				totalCount <= 0
				&& !countText.classList.contains('feed-post-emoji-text-counter-invisible')
			)
			{
				countText.classList.add('feed-post-emoji-text-counter-invisible');
			}
			else if (
				totalCount > 0
				&& countText.classList.contains('feed-post-emoji-text-counter-invisible')
			)
			{
				countText.classList.remove('feed-post-emoji-text-counter-invisible');
			}
		}

		if (reactionsNode)
		{
			const reactionsContainer = reactionsNode.querySelector('.feed-post-emoji-icon-container');
			elements = reactionsNode.querySelectorAll('.feed-post-emoji-icon-item');

			if (reactionsContainer)
			{
				let found = false;
				let newValue = false;

				elements.forEach((element) => {

					const reactionValue = element.getAttribute('data-reaction');
					const reactionCount = Number(element.getAttribute('data-value'));

					if (reactionValue === userReaction)
					{
						found = true;
						if (action === 'cancel')
						{
							newValue = (reactionCount > 0 ? reactionCount - 1 : 0);
						}
						else if ([ 'add', 'change' ].includes(action))
						{
							newValue = reactionCount + 1;
						}

						if (newValue > 0 && newValue > reactionCount)
						{
							elementsNew.push({
								reaction: reactionValue,
								count: newValue,
								animate: {
									type: 'pop',
								},
							});
						}
						else if (newValue > 0)
						{
							elementsNew.push({
								reaction: reactionValue,
								count: reactionCount,
								animate: false,
							});
						}
					}
					else if (
						action === 'change'
						&& reactionValue === userReactionOld
					)
					{
						newValue = (reactionCount > 0 ? reactionCount - 1 : 0);

						if (newValue > 0)
						{
							elementsNew.push({
								reaction: reactionValue,
								count: newValue,
								animate: false,
							});
						}
					}
					else
					{
						elementsNew.push({
							reaction: reactionValue,
							count: reactionCount,
							animate: false,
						});
					}
				});

				if (
					['add', 'change'].includes(action)
					&& !found
				)
				{
					elementsNew.push({
						reaction: userReaction,
						count: 1,
						animate: true,
					});
				}

				Dom.clean(reactionsContainer);

				if (topPanel)
				{
					if (elementsNew.length > 0)
					{
						topPanel.classList.add('feed-post-emoji-container-nonempty');
					}
					else
					{
						topPanel.classList.remove('feed-post-emoji-container-nonempty');
					}

					if (RatingManager.mobile)
					{
						const commentNode = topPanel.closest('.post-comment-block');
						if (commentNode)
						{
							if (elementsNew.length > 0)
							{
								commentNode.classList.add('comment-block-rating-nonempty');
							}
							else
							{
								commentNode.classList.remove('comment-block-rating-nonempty');
							}
						}
					}
				}

				this.drawReactions({
					likeId: likeId,
					container: reactionsContainer,
					data: elementsNew,
				});
			}
		}

		if (
			userId === currentUserId
			&& buttonText
		)
		{
			if ([ 'add', 'change' ].includes(action))
			{
				buttonText.innerHTML = Loc.getMessage(`RATING_LIKE_EMOTION_${userReaction.toUpperCase()}_CALC`);
				if (RatingManager.mobile)
				{
					buttonText.parentElement.className = '';
					buttonText.parentElement.classList.add(
						'bx-ilike-left-wrap',
						'bx-you-like-button',
						`bx-you-like-button-${userReaction.toLowerCase()}`
					);
				}
			}
			else
			{
				buttonText.innerHTML = Loc.getMessage('RATING_LIKE_EMOTION_LIKE_CALC');
				if (RatingManager.mobile)
				{
					buttonText.parentElement.className = 'bx-ilike-left-wrap';
				}
			}
		}
	}

	static drawReactions(params)
	{
		const container = (Type.isDomNode(params.container) ? params.container : null);
		const data = (Type.isArray(params.data) ? params.data : []);
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '')
		if (
			!container
			|| !Type.isStringFilled(likeId)
		)
		{
			return;
		}

		const reactionEvents = (
			RatingManager.mobile
				? {}
				: {
					click: this.resultReactionClick.bind(this),
					mouseenter: this.resultReactionMouseEnter.bind(this),
					mouseleave: this.resultReactionMouseLeave.bind(this),
				}
		);

		Dom.clean(container);

		const reactionsData = {};

		data.forEach((element, i) => {

			const classList = [
				'feed-post-emoji-icon-item',
				`feed-post-emoji-icon-item-${(i+1)}`,
			];

			if (element?.animate)
			{
				if (element.animate?.type === 'pop')
				{
					classList.push('feed-post-emoji-animation-pop');
				}
				else if (i >= 1)
				{
					classList.push('feed-post-emoji-icon-animate');
				}
				else if (data.length == 1)
				{
					classList.push('feed-post-emoji-animation-pop');
				}
			}

			const emojiContainer = Dom.create('div', {
				props: {
					id: `bx-ilike-result-reaction-${element.reaction}-${likeId}`,
					className: classList.join(' '),
				},
				attrs: {
					'data-reaction': element.reaction,
					'data-value': element.count,
					'data-like-id': likeId,
					title: Loc.getMessage(`RATING_LIKE_EMOTION_${element.reaction.toUpperCase()}_CALC`),
				},
				events: reactionEvents,
			});

			const animation = Lottie.loadAnimation({
				animationData: this.reactionsAnimationData[element.reaction],
				container: emojiContainer,
				loop: false,
				autoplay: false,
				renderer: 'svg',
				rendererSettings: {
					viewBoxOnly: true,
				}
			});

			if (Boolean(element.animate))
			{
				setTimeout(() => {
					animation.play();
				}, 200);
			}

			container.appendChild(emojiContainer);

			reactionsData[element.reaction] = element.count;
		});

		container.setAttribute('data-reactions-data', JSON.stringify(reactionsData));
	}

	static showReactionsPopup(params)
	{
		const bindElement = this.getNode(params.bindElement);
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');

		if (
			!bindElement
			|| !Type.isStringFilled(likeId)
		)
		{
			return false;
		}

		this.reactionsPopupLikeId = likeId;

		if (this.reactionsPopup === null)
		{
			const reactionsNodesList = [];

			this.reactionsList.forEach((currentEmotion, index) => {

				const emojiItem = Dom.create('div', {
					props: {
						className: `feed-post-emoji-icon-item`,
					},
					attrs: {
						'data-reaction': currentEmotion,
						title: Loc.getMessage(`RATING_LIKE_EMOTION_${currentEmotion.toUpperCase()}_CALC`),
					},
				});

				Lottie.loadAnimation({
					renderer: 'svg',
					container: emojiItem,
					animationData: this.reactionsAnimationData[currentEmotion],
				});

				reactionsNodesList.push(emojiItem);
			});

			this.reactionsPopup = Dom.create('div', {
				props: {
					className: `feed-post-emoji-popup-container ${(RatingManager.mobile ? '--mobile' : '')}`,
				},
				children: [
					Dom.create('div', {
						props: {
							className: 'feed-post-emoji-icon-inner',
						},
						children: reactionsNodesList,
					})
				],
			});

			this.reactionsPopup.addEventListener((RatingManager.mobile ? 'touchend' : 'click'), (e) => {

				const reactionNode = (
					(e.target.classList.contains('feed-post-emoji-icon-item'))
						? e.target
						: e.target.closest('.feed-post-emoji-icon-item')
				);

				if (reactionNode)
				{
					RatingLike.ClickVote(
						e,
						this.reactionsPopupLikeId,
						reactionNode.getAttribute('data-reaction'),
						true
					);
				}

				e.preventDefault();
			});

			Dom.append(this.reactionsPopup, document.body);
		}
		else if (this.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible'))
		{
			this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible');
		}
		else if (
			RatingManager.mobile
			&& this.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible-final-mobile')
		)
		{
			this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final-mobile');
		}
		else
		{
			return;
		}

		this.reactionsPopupMouseOutHandler = this.getReactionsPopupMouseOutHandler(likeId);

		const bindElementPosition = pos(bindElement);

		if (
			bindElement.closest('.feed-com-informers-bottom')
			&& bindElement.closest('.iframe-comments-cont, .task-iframe-popup')
		)
		{
			bindElementPosition.left += 100;
		}

		const inverted = ((bindElementPosition.top - GetWindowSize().scrollTop) < 80);
		const deltaY = (inverted ? 15 : -45);

		if (inverted)
		{
			this.reactionsPopup.classList.add('feed-post-emoji-popup-inverted');
		}
		else
		{
			this.reactionsPopup.classList.remove('feed-post-emoji-popup-inverted');
		}

		const likeInstance = RatingLike.getInstance(likeId);

		if (RatingManager.mobile)
		{
			this.touchMoveDeltaY = (inverted ? 60 : -45);
			Dom.adjust(this.reactionsPopup, {
				style: {
					left: '12px',
					top: ((inverted ? (bindElementPosition.top - 23) : (bindElementPosition.top - 28)) + deltaY) + 'px',
					width: '330px',
					borderRadius: '61px',
				},
			});

			this.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final');
			this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
			this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
			likeInstance.box.classList.add('feed-post-emoji-control-active');
			this.reactionsPopupMobileDisableScroll();
		}
		else
		{
			this.reactionsPopupAnimation = new BX.easing({
				duration: 300,
				start: {
					width: 100,
					left: (bindElementPosition.left + (bindElementPosition.width / 2) - 50),
					top: ((inverted ? bindElementPosition.top - 30 : bindElementPosition.top + 30 ) + deltaY),
					borderRadius: 0,
					opacity: 0,
				},
				finish: {
					width: 300,
					left: (bindElementPosition.left + (bindElementPosition.width / 2) - 133),
					top: (bindElementPosition.top + deltaY - 5),
					borderRadius: 50,
					opacity: 100,
				},
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.cubic),
				step: (state) => {
					if (!this.reactionsPopup)
					{
						this.reactionsPopupAnimation.stop();
						return;
					}
					this.reactionsPopup.style.width = `${state.width}px`;
					this.reactionsPopup.style.left = `${state.left}px`;
					this.reactionsPopup.style.top = `${state.top}px`;
					this.reactionsPopup.style.borderRadius = `${state.borderRadius}px`;
					this.reactionsPopup.style.opacity = state.opacity / 100;
					this.reactionsPopupOpacityState = state.opacity;
				},
				complete: () => {
					if (!this.reactionsPopup)
					{
						return;
					}
					this.reactionsPopup.style.opacity = '';
					this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
					likeInstance.box.classList.add('feed-post-emoji-control-active');
					if (Type.isFunction(params.onComplete))
					{
						params.onComplete();
					}
				},
			});
			this.reactionsPopupAnimation.animate();

			setTimeout(() => {

				if (!this.reactionsPopup)
				{
					return;
				}

				const reactions = this.reactionsPopup.querySelectorAll('.feed-post-emoji-icon-item');

				this.reactionsPopupAnimation2 = new BX.easing({
					duration: 140,
					start: {
						opacity: 0,
					},
					finish: {
						opacity: 100
					},
					transition : BX.easing.transitions.cubic,
					step: (state) => {
						reactions[0].style.opacity = state.opacity / 100;
						reactions[1].style.opacity = state.opacity / 100;
						reactions[2].style.opacity = state.opacity / 100;
						reactions[3].style.opacity = state.opacity / 100;
						reactions[4].style.opacity = state.opacity / 100;
						reactions[5].style.opacity = state.opacity / 100;
						reactions[6].style.opacity = state.opacity / 100;
					},
					complete: () => {
						this.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
						reactions[0].style.opacity = '';
						reactions[1].style.opacity = '';
						reactions[2].style.opacity = '';
						reactions[3].style.opacity = '';
						reactions[4].style.opacity = '';
						reactions[5].style.opacity = '';
						reactions[6].style.opacity = '';
					},
				});
				this.reactionsPopupAnimation2.animate();
			}, 100);
		}

		if (!this.reactionsPopup.classList.contains('feed-post-emoji-popup-active'))
		{
			this.reactionsPopup.classList.add('feed-post-emoji-popup-active');
		}

		if (!RatingManager.mobile)
		{
			document.addEventListener('mousemove', this.reactionsPopupMouseOutHandler);
		}
		else
		{
			this.touchScrollTop = GetWindowSize().scrollTop;
			this.hasMobileTouchMoved = null;

			window.addEventListener('touchend', this.reactionsPopupMobileTouchEndHandler);
			window.addEventListener('touchmove', this.reactionsPopupMobileTouchMoveHandler);
		}
	}

	static reactionsPopupMobileTouchEnd(e)
	{
		const coords = {
			x: e.changedTouches[0].pageX, // e.touches[0].clientX + window.pageXOffset
			y: e.changedTouches[0].pageY, // e.touches[0].clientY + window.pageYOffset
		};

		if (this.hasMobileTouchMoved === true)
		{
			let userReaction = null;
			const reactionNode = this.reactionsPopupMobileGetHoverNode(coords.x, coords.y);

			if (
				reactionNode
				&& (userReaction = reactionNode.getAttribute('data-reaction'))
			)
			{
				RatingLike.ClickVote(
					e,
					this.reactionsPopupLikeId,
					userReaction,
					true
				);
			}
			this.reactionsPopupMobileHideHandler();
		}
		else // show reactions popup and handle clicks
		{
			window.addEventListener('touchend', this.reactionsPopupMobileHideHandler);
		}

		window.removeEventListener('touchend', this.reactionsPopupMobileTouchEndHandler);
		window.removeEventListener('touchmove', this.reactionsPopupMobileTouchMoveHandler);

		this.touchStartPosition = null;
		e.preventDefault();
	}

	static reactionsPopupMobileTouchMove(e)
	{
		const coords = {
			x: e.touches[0].pageX, // e.touches[0].clientX + window.pageXOffset
			y: e.touches[0].pageY, // e.touches[0].clientY + window.pageYOffset
		};


		this.touchCurrentPosition = {
			x: coords.x,
			y: coords.y,
		};

		if (this.touchStartPosition === null)
		{
			this.touchStartPosition = {
				x: coords.x,
				y: coords.y,
			};
		}
		else
		{
			if (this.hasMobileTouchMoved !== true)
			{
				this.hasMobileTouchMoved = !this.reactionsPopupMobileCheckTouchMove();
			}
		}

		if (this.hasMobileTouchMoved === true)
		{
			const reactionNode = this.reactionsPopupMobileGetHoverNode(coords.x, coords.y);
			if (reactionNode)
			{
				if (
					this.currentReactionNodeHover
					&& this.currentReactionNodeHover !== reactionNode
				)
				{
					this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
				}
				this.reactionsPopupMobileAddHover(reactionNode);
				this.currentReactionNodeHover = reactionNode;
			}
			else if (this.currentReactionNodeHover)
			{
				this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
			}
		}
		else
		{
			if (this.currentReactionNodeHover)
			{
				this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);
			}
		}
	}

	static blockReactionsPopup()
	{
		if (this.blockShowPopupTimeout)
		{
			window.clearTimeout(this.blockShowPopupTimeout);
		}

		this.blockShowPopup = true;
		this.blockShowPopupTimeout = setTimeout(() => {
			this.blockShowPopup = false;
		}, 500);
	}

	static hideReactionsPopup(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : false);

		if (this.reactionsPopup)
		{
			if (RatingManager.mobile)
			{
				this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
				this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
				this.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
				this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
				this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
				this.reactionsPopupMobileEnableScroll();
				Dom.remove(this.reactionsPopup);
				this.reactionsPopup = null;
			}
			else
			{
				if (this.reactionsPopupAnimation)
				{
					this.reactionsPopupAnimation.stop();
				}
				if (this.reactionsPopupAnimation2)
				{
					this.reactionsPopupAnimation2.stop();
				}

				this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible');

				this.reactionsPopupAnimation4 = new BX.easing({
					duration: 500,
					start: {
						opacity: this.reactionsPopupOpacityState,
					},
					finish: {
						opacity: 0,
					},
					transition: BX.easing.transitions.linear,
					step: (state) => {
						this.reactionsPopup.style.opacity = state.opacity / 100;
						this.reactionsPopupOpacityState = state.opacity;
					},
					complete: () => {
						this.reactionsPopup.style.opacity = '';
						this.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
						this.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
						this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
						this.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
						Dom.remove(this.reactionsPopup);
						this.reactionsPopup = null;
					},
				});

				this.reactionsPopupAnimation4.animate();
			}

			this.reactionsPopupLikeId = null;

			if (likeId)
			{
				RatingLike.getInstance(likeId).box.classList.remove('feed-post-emoji-control-active');
			}
		}

		this.reactionsPopupMobileRemoveHover(this.currentReactionNodeHover);

		if (likeId)
		{
			this.bindReactionsPopup({
				likeId: likeId,
			});
		}
	}

	static reactionsPopupMobileCheckTouchMove()
	{
		if (this.touchStartPosition === null)
		{
			return true;
		}
		else
		{
			if (
				Math.abs(this.touchCurrentPosition.x - this.touchStartPosition.x) > 5
				|| Math.abs(this.touchCurrentPosition.y - this.touchStartPosition.y) > 5
			)
			{
				return false;
			}
		}

		return true;
	}

	static reactionsPopupMobileHide(e)
	{
		window.removeEventListener('touchend', this.reactionsPopupMobileHideHandler);
		if (this.reactionsPopupLikeId)
		{
			this.hideReactionsPopup({
				likeId: this.reactionsPopupLikeId,
			});

			if (e)
			{
				e.preventDefault();
			}
		}
	}

	static reactionsPopupMobileGetHoverNode(x, y)
	{
		const nodeAboveFinger = document.elementFromPoint(x, (y + this.touchMoveDeltaY - this.touchScrollTop));
		const nodeBelowFinger = document.elementFromPoint(x, (y - this.touchScrollTop));

		const iconNodeAboveFinger = nodeAboveFinger?.closest('[data-reaction]');
		const iconNodeBelowFinger = nodeBelowFinger?.closest('[data-reaction]');

		const reactionNode = iconNodeAboveFinger || iconNodeBelowFinger;

		const userReaction = reactionNode?.getAttribute('data-reaction');

		return Type.isStringFilled(userReaction) ? reactionNode : null;
	}

	static reactionsPopupMobileAddHover(reactionNode)
	{
		if (!reactionNode)
		{
			return;
		}

		reactionNode.classList.add('feed-post-emoji-icon-item-hover');
	}

	static reactionsPopupMobileRemoveHover(reactionNode)
	{
		if (!reactionNode)
		{
			return;
		}

		reactionNode.classList.remove('feed-post-emoji-icon-item-hover');
	}

	static reactionsPopupMobileEnableScroll()
	{
		document.removeEventListener('touchmove', this.touchMoveScrollListener, { passive: false });
		EventEmitter.emit('onPullDownEnable');

		if (this.mobileOverlay !== null)
		{
			Dom.clean(this.mobileOverlay);
			Dom.remove(this.mobileOverlay);

			this.mobileOverlay = null;
		}
	}

	static reactionsPopupMobileDisableScroll()
	{
		document.addEventListener('touchmove', this.touchMoveScrollListener, { passive: false });
		if (app)
		{
			app.exec('disableTabScrolling');
		}
		EventEmitter.emit('onPullDownDisable');

		if (!Type.isNull(this.mobileOverlay))
		{
			return;
		}

		this.mobileOverlay = Dom.create('DIV', {
			props: {
				className: 'feed-post-emoji-popup-mobile-overlay',
			},
		});
		setTimeout(() => {
			if (Type.isNull(this.mobileOverlay))
			{
				return
			}

			Dom.append(this.mobileOverlay, document.body);
		}, 1000); // to avoid blink
	}

	static bindReactionsPopup(params) {

		if (RatingManager.mobile)
		{
			return false;
		}

		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');

		if (!Type.isStringFilled(likeId))
		{
			return false;
		}

		const likeInstance = RatingLike.getInstance(likeId);
		if (!likeInstance)
		{
			return false;
		}

		likeInstance.mouseOverHandler = Runtime.debounce(this.getMouseOverHandler(likeId), 500);

		likeInstance.box.addEventListener('mouseenter', likeInstance.mouseOverHandler);
		likeInstance.box.addEventListener('mouseleave', this.blockReactionsPopup);
	}

	static touchMoveScrollListener(e)
	{
		e.preventDefault();
	}

	static getReactionsPopupMouseOutHandler(likeId)
	{
		return (e) => {

			if (!this.reactionsPopup)
			{
				document.removeEventListener('mousemove', this.reactionsPopupMouseOutHandler)
				this.reactionsPopupMouseOutHandler = null;
				return;
			}

			const popupPosition = this.reactionsPopup.getBoundingClientRect();
			const inverted = this.reactionsPopup.classList.contains('feed-post-emoji-popup-inverted');

			if (
				e.clientX >= popupPosition.left
				&& e.clientX <= popupPosition.right
				&& e.clientY >= popupPosition.top - (inverted ? 25 : 0)
				&& e.clientY <= (popupPosition.bottom + (inverted ? 0 : 25))
			)
			{
				return;
			}

			this.blockReactionsPopup();
			this.hideReactionsPopup({
				likeId: likeId
			});

			document.removeEventListener('mousemove', this.reactionsPopupMouseOutHandler)
			this.reactionsPopupMouseOutHandler = null;
		};
	}

	static getMouseOverHandler(likeId)
	{
		return () => {

			const likeInstance = RatingLike.getInstance(likeId);

			if (
				this.reactionsPopup
				&& !this.reactionsPopup?.classList.contains('feed-post-emoji-popup-invisible')
				&& !(
					RatingManager.mobile
					&& this.reactionsPopup?.classList.contains('feed-post-emoji-popup-invisible-final-mobile')
				)
			)
			{
				return;
			}

			if (!this.afterClickBlockShowPopup)
			{
				if (this.blockShowPopup)
				{
					return;
				}

				if (RatingManager.mobile)
				{
					app.exec('callVibration');
				}

				this.showReactionsPopup({
					bindElement: likeInstance.box,
					likeId: likeId,
					onComplete: () => {
						likeInstance.box.removeEventListener('mouseenter', likeInstance.mouseOverHandler);
						likeInstance.box.removeEventListener('mouseleave', this.blockReactionsPopup.bind(this));
					},
				});
			}
		};
	}

	static buildPopupContent(params)
	{
		const clear = (params.clear ? Boolean(params.clear) : false);
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');
		const rating = params.rating;
		const requestReaction = (Type.isStringFilled(params.reaction) ? params.reaction : '');
		const page = (Number(params.page) > 0 ? Number(params.page) : 1);
		const data = params.data;
		const reaction = false;

		const reactionsList = [];
		let reactionsCount = 0;

		if (
			clear
			&& page === 1
		)
		{
			this.clearPopupContent({
				likeId: likeId,
			});
		}

		this.popupCurrentReaction = (Type.isStringFilled(requestReaction) ? requestReaction : 'all');

		if (
			requestReaction.length <= 0
			|| requestReaction == 'all'
		) // first current tab
		{
			this.popupSizeInitialized = false;
			document.getElementById(`bx-ilike-popup-cont-${likeId}`).style.height = 'auto';
			document.getElementById(`bx-ilike-popup-cont-${likeId}`).style.minWidth = 'auto';
		}

		if (!Type.isStringFilled(requestReaction))
		{
			this.popupPagesList = {};
		}

		this.popupPagesList[(requestReaction == '' ? 'all' : requestReaction)] = (page + 1);

		if (Type.isPlainObject(data.reactions))
		{
			Object.entries(data.reactions).forEach(([ reaction, count ]) => {
				if (Number(count) <= 0)
				{
					return;
				}

				reactionsList.push({
					reaction: reaction,
					count: Number(count)
				});
				reactionsCount++;
			});
		}

		const tabsNode = Dom.create('span', {
			props: {
				className: 'bx-ilike-popup-head',
			},
		});

		if (reactionsCount > 1)
		{
			const headClassList = [ 'bx-ilike-popup-head-item' ];
			if (!Type.isStringFilled(requestReaction) || requestReaction == 'all')
			{
				headClassList.push('bx-ilike-popup-head-item-current');
			}

			tabsNode.appendChild(Dom.create('span', {
				props: {
					className: headClassList.join(' '),
				},
				children: [
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-head-icon feed-post-emoji-icon-all',
						},
					}),
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-head-text',
						},
						html: Loc.getMessage('RATING_LIKE_POPUP_ALL').replace('#CNT#', Number(data.items_all)),
					})
				],
				events: {
					click: ((e) => {
						this.changePopupTab({
							likeId: likeId,
							rating: rating,
							reaction: 'all',
						});
						e. preventDefault();
					}),
				},
			}));
		}

		if (reactionsCount === 0)
		{
			reactionsList.push({
				reaction: Loc.getMessage('RATING_LIKE_REACTION_DEFAULT'),
				count: Number(data.items_all),
			});
		}

		reactionsList.sort((a, b) => {
			const sample = {
				like: 0,
				kiss: 1,
				laugh: 2,
				wonder: 3,
				cry: 4,
				angry: 5,
				facepalm: 6,
			};
			if (sample[a.reaction] < sample[b.reaction])
			{
				return -1;
			}
			if (sample[a.reaction] > sample[b.reaction])
			{
				return 1;
			}
			return 0;
		});

		reactionsList.forEach((reactionData) => {

			const headItemClassList = [ 'bx-ilike-popup-head-item' ];
			if (requestReaction === reactionData.reaction)
			{
				headItemClassList.push('bx-ilike-popup-head-item-current');
			}

			tabsNode.appendChild(Dom.create('span', {
				props: {
					className: headItemClassList.join(' '),
				},
				attrs: {
					title: Loc.getMessage(`RATING_LIKE_EMOTION_${reactionData.reaction.toUpperCase()}_CALC`),
				},
				children: [
					Dom.create('span', {
						props: {
							className: [
								'bx-ilike-popup-head-icon',
								'feed-post-emoji-icon-item',
								`feed-post-emoji-icon-${reactionData.reaction}`,
							].join(' '),
						}
					}),
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-head-text',
						},
						html: reactionData.count,
					})
				],
				events: {
					click: (e) => {

						const popupContent = document.getElementById(`bx-ilike-popup-cont-${likeId}`);
						const popupContentPosition = popupContent.getBoundingClientRect();

						if (
							requestReaction.length <= 0
							|| requestReaction === 'all'
						) // first current tab
						{
							this.popupSizeInitialized = true;
							popupContent.style.height = `${popupContentPosition.height}px`;
							popupContent.style.minWidth = `${popupContentPosition.width}px`;
						}
						else
						{
							if (popupContentPosition.width > Number(popupContent.style.minWidth))
							{
								popupContent.style.minWidth = `${popupContentPosition.width}px`;
							}
						}

						this.changePopupTab({
							likeId: likeId,
							rating: rating,
							reaction: reactionData.reaction,
						});
						e. preventDefault();
					},
				},
			}));
		});

		let usersNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');
		let usersNodeExists = false;

		if (!usersNode)
		{
			usersNode = Dom.create('span', {
				props: {
					className: 'bx-ilike-popup-content-container',
				}
			});
		}
		else
		{
			usersNodeExists = true;
		}

		usersNode.querySelectorAll('.bx-ilike-popup-content').forEach((contentNode) => {
			contentNode.classList.add('bx-ilike-popup-content-invisible');
		});

		let reactionUsersNode = usersNode.querySelector(`.bx-ilike-popup-content-${this.popupCurrentReaction}`);
		if (!reactionUsersNode)
		{
			reactionUsersNode = Dom.create('span', {
				props: {
					className: [
						'bx-ilike-popup-content',
						`bx-ilike-popup-content-${this.popupCurrentReaction}`,
					].join(' '),
				}
			});
			usersNode.appendChild(reactionUsersNode);
		}
		else
		{
			reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
		}

		data.items.forEach((item) => {

			const userItemClassList = [ 'bx-ilike-popup-user-item' ];
			if (Type.isStringFilled(item.USER_TYPE))
			{
				userItemClassList.push(`bx-ilike-popup-user-item-${item.USER_TYPE}`);
			}

			reactionUsersNode.appendChild(Dom.create('a', {
				props: {
					className: userItemClassList.join(' '),
				},
				attrs: {
					href: item.URL,
					target: '_blank',
				},
				children: [
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-user-icon',
						},
						style: (
							Type.isStringFilled(item.PHOTO_SRC)
								? {
									'background-image': `url("${encodeURI(item.PHOTO_SRC)}")`,
								}
								: {}
						)
					}),
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-user-name'
						},
						html: item.FULL_NAME,
					}),
					Dom.create('span', {
						props: {
							className: 'bx-ilike-popup-user-status',
						},
					}),
				],
			}));
		});


		const waitNode = rating.popupContent.querySelector('.bx-ilike-wait');
		if (waitNode)
		{
			Dom.clean(waitNode);
			Dom.remove(waitNode);
		}
		const tabsNodeOld = rating.popupContent.querySelector('.bx-ilike-popup-head');
		if (tabsNodeOld)
		{
			tabsNodeOld.parentNode.insertBefore(tabsNode, tabsNodeOld);
			tabsNodeOld.parentNode.removeChild(tabsNodeOld);
		}
		else
		{
			rating.popupContent.appendChild(tabsNode);
		}

		if (!usersNodeExists)
		{
			rating.popupContent.appendChild(usersNode);
		}
	}

	static clearPopupContent(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');

		const likeInstance = RatingLike.getInstance(likeId);
		likeInstance.popupContent.innerHTML = '';
		document.getElementById(`bx-ilike-popup-cont-${likeId}`).style.height = 'auto';
		document.getElementById(`bx-ilike-popup-cont-${likeId}`).style.minWidth = 'auto';
		likeInstance.popupContent.appendChild(Dom.create('span', {
			props: {
				className: 'bx-ilike-wait',
			},
		}));
	}

	static changePopupTab(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');
		const rating = params.rating;
		const reaction = (Type.isStringFilled(params.reaction) ? params.reaction : '');

		const contentContainerNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');
		if (!contentContainerNode)
		{
			return false;
		}

		const reactionUsersNode = contentContainerNode.querySelector('.bx-ilike-popup-content-' + reaction);
		if (reactionUsersNode)
		{
			this.popupCurrentReaction = (Type.isStringFilled(reaction) ? reaction : 'all');

			rating.popupContent.querySelectorAll('.bx-ilike-popup-head-item').forEach((tabNode) => {
				tabNode.classList.remove('bx-ilike-popup-head-item-current');
				const reactionTabNode = tabNode.querySelector(`.feed-post-emoji-icon-${reaction}`);
				if (reactionTabNode)
				{
					tabNode.classList.add('bx-ilike-popup-head-item-current');
				}
			});

			contentContainerNode.querySelectorAll('.bx-ilike-popup-content').forEach((contentNode) => {
				contentNode.classList.add('bx-ilike-popup-content-invisible');
			});
			reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
		}
		else
		{
			ListPopup.List(likeId, 1, reaction);
		}
	}

	static buildPopupContentNoReactions(params)
	{
		const page = (Number(params.page) > 0 ? Number(params.page) : 1);
		const likeInstance = (!Type.isUndefined(params.rating) ? params.rating : null);
		const data = params.data;

		if (!likeInstance)
		{
			return false;
		}

		if (page === 1)
		{
			likeInstance.popupContent.innerHTML = '';
			likeInstance.popupContent.appendChild(Dom.create('span', {
				props: {
					className: 'bx-ilike-bottom_scroll',
				}
			}));
		}

		likeInstance.popupContentPage += 1;

		data.items.forEach((item) => {
			let avatarNode = null;

			if (Type.isStringFilled(item.PHOTO_SRC))
			{
				avatarNode = Dom.create('img', {
					attrs: {
						src: encodeURI(item.PHOTO_SRC),
					},
					props: {
						className: 'bx-ilike-popup-avatar-img',
					},
				});
			}
			else
			{
				avatarNode = Dom.create('img', {
					attrs: {
						src: '/bitrix/images/main/blank.gif',
					},
					props: {
						className: 'bx-ilike-popup-avatar-img bx-ilike-popup-avatar-img-default',
					},
				});
			}

			const imgClassList = [
				'bx-ilike-popup-img',
			];
			if (Type.isStringFilled(item.USER_TYPE))
			{
				imgClassList.push(`bx-ilike-popup-img-${item.USER_TYPE}`);
			}

			likeInstance.popupContent.appendChild(
				Dom.create('a', {
					attrs: {
						href: item.URL,
						target: '_blank',
					},
					props: {
						className: imgClassList.join(' '),
					},
					children: [
						Dom.create('span', {
							props: {
								className: 'bx-ilike-popup-avatar-new',
							},
							children: [
								avatarNode,
								Dom.create('span', {
									props: {
										className: 'bx-ilike-popup-avatar-status-icon',
									},
								})
							]
						}),
						Dom.create('span', {
							props: {
								className: 'bx-ilike-popup-name-new',
							},
							html: item.FULL_NAME,
						}),
					],
				})
			);
		});
	}

	static afterClick(params)
	{
		const likeId = (Type.isStringFilled(params.likeId) ? params.likeId : '');

		if (!Type.isStringFilled(likeId))
		{
			return;
		}

		this.afterClickBlockShowPopup = true;

		this.afterClickHandler = this.getAfterClickHandler(likeId);

		RatingLike.getInstance(likeId).box.addEventListener('mouseleave', this.afterClickHandler);
	}

	static getAfterClickHandler(likeId)
	{
		return () => {
			this.afterClickBlockShowPopup = false;

			RatingLike.getInstance(likeId).box.removeEventListener('mouseleave', this.afterClickHandler);
		};
	}

	static resultReactionClick(e)
	{
		const likeId = e.currentTarget.getAttribute('data-like-id');
		let reaction = e.currentTarget.getAttribute('data-reaction');

		if (!Type.isSet(reaction))
		{
			reaction = '';
		}

		ListPopup.onResultClick({
			likeId: likeId,
			event: e,
			reaction: reaction,
		});

		e.stopPropagation();
	}

	static resultReactionMouseEnter(e)
	{
		const likeId = e.currentTarget.getAttribute('data-like-id');
		const reaction = e.currentTarget.getAttribute('data-reaction');

		ListPopup.onResultMouseEnter({
			likeId: likeId,
			event: e,
			reaction: reaction,
		});
	}

	static resultReactionMouseLeave(e)
	{
		const likeId = e.currentTarget.getAttribute('data-like-id');
		const reaction = e.currentTarget.getAttribute('data-reaction');

		ListPopup.onResultMouseLeave({
			likeId: likeId,
			reaction: reaction,
		});
	}

	static openMobileReactionsPage(params)
	{
		BXMobileApp.PageManager.loadPageBlank({
			url: `${Loc.getMessage('SITE_DIR')}mobile/like/result.php`,
			title: Loc.getMessage('RATING_LIKE_RESULTS'),
			backdrop: {
				mediumPositionPercent: 65,
			},
			cache: true,
			data: {
				entityTypeId: params.entityTypeId,
				entityId: params.entityId,
			}
		});
	}

	static onRatingLike(eventData)
	{
		RatingLike.repo.forEach((likeInstance, likeId) => {
			if (
				likeInstance.entityTypeId !== eventData.entityTypeId
				&& Number(likeInstance.entityId) !== Number(eventData.entityId)
			)
			{
				return;
			}

			let voteAction = (Type.isStringFilled(eventData.voteAction) ? eventData.voteAction.toUpperCase() : 'ADD');
			voteAction = (voteAction === 'PLUS' ? 'ADD' : voteAction);

			if (
				Number(eventData.userId) === Number(Loc.getMessage('USER_ID'))
				&& likeInstance.button
			)
			{
				if (voteAction === 'CANCEL')
				{
					likeInstance.button.classList.remove('bx-you-like-button');

				}
				else
				{
					likeInstance.button.classList.add('bx-you-like-button');
				}
			}

			RatingLike.Draw(likeId, {
				TYPE: voteAction,
				USER_ID: eventData.userId,
				ENTITY_TYPE_ID: eventData.entityTypeId,
				ENTITY_ID: eventData.entityId,
				USER_DATA: eventData.userData,
				REACTION: eventData.voteReaction,
				REACTION_OLD: eventData.voteReactionOld,
				TOTAL_POSITIVE_VOTES: eventData.itemsAll,
			});
		});
	}

	static onMobileCommentsGet()
	{
		const ratingEmojiSelectorPopup = document.querySelector('.feed-post-emoji-popup-container');
		if (ratingEmojiSelectorPopup)
		{
			ratingEmojiSelectorPopup.style.top = 0;
			ratingEmojiSelectorPopup.style.left = 0;
			ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active');
			ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active-final');
			ratingEmojiSelectorPopup.classList.remove('feed-post-emoji-popup-active-final-item');
			ratingEmojiSelectorPopup.classList.add('feed-post-emoji-popup-invisible-final');
			ratingEmojiSelectorPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
		}
	}

	static getNode(node)
	{
		if (Type.isDomNode(node))
		{
			return node;
		}
		else if (Type.isStringFilled(node))
		{
			return document.getElementById(node);
		}
		else
		{
			return null;
		}
	}
}
