import { Type, Dom, Loc, ajax } from 'main.core';

import { RatingManager } from './manager';
import { RatingRender } from './render';
import { ListPopup } from './listpopup';

import './css/reaction.css';
import './css/mobile.css';

type Params = {
	likeId: string,
	keySigned: string,
	entityTypeId: string,
	entityId: number,
	available: number,
	userId?: number,
	localize?: Object,
	template?: string,
	pathToUserProfile?: string,
	mobile?: boolean | 'true' | 'false'
}

export class RatingLike
{
	static repo = new Map();
	static lastVoteRepo = new Map();
	static lastReactionRepo = new Map();
	static additionalParams = new Map();

	constructor(params: Params)
	{
		this.likeId = Type.isStringFilled(params.likeId) ? params.likeId : '';
		this.keySigned = Type.isStringFilled(params.keySigned) ? params.keySigned : '';
		this.entityTypeId = Type.isStringFilled(params.entityTypeId) ? params.entityTypeId : '';
		this.entityId = !Type.isUndefined(params.entityId) ? Number(params.entityId) : 0;
		this.available = Type.isStringFilled(params.available) ? params.available === 'Y' : false;
		this.userId = !Type.isUndefined(params.userId) ? Number(params.userId) : 0;
		this.localize = Type.isPlainObject(params.localize) ? params.localize : {};
		this.template = Type.isStringFilled(params.template) ? params.template : '';
		this.pathToUserProfile = Type.isStringFilled(params.pathToUserProfile) ? params.pathToUserProfile : '';

		const key = `${this.entityTypeId}_${this.entityId}`;

		this.enabled = true;

		this.box = document.getElementById(`bx-ilike-button-${this.likeId}`);
		if (this.box === null)
		{
			this.enabled = false;
			return false;
		}

		this.box.setAttribute('data-rating-vote-id', this.likeId);

		if (this.keySigned === '')
		{
			const keySigned = this.box.getAttribute('data-vote-key-signed');
			this.keySigned = keySigned ? keySigned : '';
		}

		this.button = this.box.querySelector('.bx-ilike-left-wrap');
		this.buttonText = this.button.querySelector('.bx-ilike-text');
		this.count = this.box.querySelector('span.bx-ilike-right-wrap');
		if (!this.count)
		{
			this.count = document.getElementById(`bx-ilike-count-${this.likeId}`);
		}
		this.countText = this.count.querySelector('.bx-ilike-right');

		this.topPanelContainer = document.getElementById(`feed-post-emoji-top-panel-container-${this.likeId}`);
		this.topPanel = document.getElementById(`feed-post-emoji-top-panel-${this.likeId}`);
		this.topUsersText = document.getElementById(`bx-ilike-top-users-${this.likeId}`);
		this.topUsersDataNode = document.getElementById(`bx-ilike-top-users-data-${this.likeId}`);
		this.userReactionNode = document.getElementById(`bx-ilike-user-reaction-${this.likeId}`);
		this.reactionsNode = document.getElementById(`feed-post-emoji-icons-${this.likeId}`);

		this.popup = null;
		this.popupId = null;
		this.popupTimeoutIdShow = null;
		this.popupTimeoutIdList = null;

		this.popupContent = document.getElementById(`bx-ilike-popup-cont-${this.likeId}`)
			.querySelector('span.bx-ilike-popup')
		;
		this.popupContentPage = 1;
		this.popupTimeout = false;
		this.likeTimeout = false;
		this.mouseOverHandler = null;
		this.version = (Type.isDomNode(this.topPanel) ? 2 : 1);
		this.mouseInShowPopupNode = {};
		this.listXHR = null;

		if (
			this.template === 'light'
			&& Type.isDomNode(this.reactionsNode)
		)
		{
			const container = this.reactionsNode.querySelector('.feed-post-emoji-icon-container');
			if (container)
			{
				let reactionsData = container.getAttribute('data-reactions-data');
				try
				{
					reactionsData = JSON.parse(reactionsData);

					const elementsNew = [];
					Object.entries(reactionsData).forEach(([reaction, count]) => {
						elementsNew.push({
							reaction: reaction,
							count: count,
							animate: false,
						});
					});

					RatingRender.drawReactions({
						likeId: this.likeId,
						container: container,
						data: elementsNew,
					});
				}
				catch (e)
				{
				}
			}
		}

		if (!Type.isUndefined(RatingLike.lastVoteRepo.get(key)))
		{
			this.lastVote = RatingLike.lastVoteRepo.get(key);

			const ratingNode = (this.template === 'standart' ? this.button : this.count);
			if (this.lastVote === 'plus')
			{
				ratingNode.classList.add('bx-you-like');
			}
			else
			{
				ratingNode.classList.remove('bx-you-like');
			}
		}
		else
		{
			this.lastVote = (
				(this.template === 'standart' ? this.button : this.count).classList.contains('bx-you-like')
					? 'plus'
					: 'cancel'
			);
			RatingLike.lastVoteRepo.set(key, this.lastVote);
		}

		if (!Type.isUndefined(RatingLike.lastReactionRepo.get(key)))
		{
			this.lastReaction = RatingLike.lastReactionRepo.get(key);
			this.count.setAttribute('data-myreaction', this.lastReaction);
		}
		else
		{
			const lastReaction = this.count.getAttribute('data-myreaction');
			this.lastReaction = (Type.isStringFilled(lastReaction) ? lastReaction : 'like');
			RatingLike.lastReactionRepo.set(key, this.lastReaction);
		}

		if (this.topPanelContainer)
		{
			RatingManager.addEntity(key, this);
		}

		return this;
	}

	static setInstance(likeId: string, likeInstance)
	{
		this.repo.set(likeId, likeInstance);
		window.BXRL[likeId] = likeInstance;
	}

	static getInstance(likeId: string)
	{
		return this.repo.get(likeId);
	}

	static ClickVote(e, likeId, userReaction, forceAdd)
	{
		if (Type.isUndefined(userReaction))
		{
			userReaction = 'like';
		}

		const likeInstance = this.getInstance(likeId);
		const container = (likeInstance.template === 'standart' ? e.target : likeInstance.count);

		if (
			likeInstance.version === 2
			&& likeInstance.userReactionNode
		)
		{
			RatingRender.hideReactionsPopup({
				likeId: likeId,
			});
			RatingRender.blockReactionsPopup();
			document.removeEventListener('mousemove', RatingRender.reactionsPopupMouseOutHandler);
		}

		clearTimeout(likeInstance.likeTimeout);

		const active = container.classList.contains('bx-you-like');

		forceAdd = !!forceAdd;

		let change = false;
		let userReactionOld = false;

		if (active && !forceAdd)
		{
			userReaction = (
				likeInstance.version === 2
					? RatingRender.getUserReaction({
						userReactionNode: likeInstance.userReactionNode
					})
					: false
			);

			likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_N'];
			likeInstance.countText.innerHTML = Number(likeInstance.countText.innerHTML)-1;

			container.classList.remove('bx-you-like');
			likeInstance.button.classList.remove('bx-you-like-button');

			if (userReaction)
			{
				likeInstance.button.classList.remove(`bx-you-like-button-${userReaction}`);
			}

			likeInstance.likeTimeout = setTimeout(() => {
				if (likeInstance.lastVote != 'cancel')
				{
					this.Vote(likeId, 'cancel', userReaction);
				}
			}, 1000);
		}
		else if (active && forceAdd)
		{
			change = true;
			userReactionOld = (
				likeInstance.version === 2
					? RatingRender.getUserReaction({ userReactionNode: likeInstance.userReactionNode })
					: false
			);

			if (userReaction != userReactionOld)
			{
				if (userReactionOld)
				{
					likeInstance.button.classList.remove(`bx-you-like-button-${userReactionOld}`);
				}
				likeInstance.button.classList.add(`bx-you-like-button-${userReaction}`);

				likeInstance.likeTimeout = setTimeout(() => {
					this.Vote(likeId, 'change', userReaction, userReactionOld);
				}, 1000);
			}
		}
		else if (!active)
		{
			likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_Y'];
			likeInstance.countText.innerHTML = Number(likeInstance.countText.innerHTML) + 1;
			container.classList.add('bx-you-like');

			likeInstance.button.classList.add('bx-you-like-button');
			likeInstance.button.classList.add(`bx-you-like-button-${userReaction}`);

			likeInstance.likeTimeout = setTimeout(() => {
				if (likeInstance.lastVote !== 'plus')
				{
					this.Vote(likeId, 'plus', userReaction);
				}
				else if (userReaction !== likeInstance.lastReaction) // http://jabber.bx/view.php?id=99339
				{
					this.Vote(likeId, 'change', userReaction, likeInstance.lastReaction);
				}
			}, 1000);
		}

		if (likeInstance.version === 2)
		{
			if (change)
			{
				RatingRender.setReaction({
					likeId: likeId,
					rating: likeInstance,
					action: 'change',
					userReaction: userReaction,
					userReactionOld: userReactionOld,
					totalCount: Number(likeInstance.countText.innerHTML),
				});
			}
			else
			{
				RatingRender.setReaction({
					likeId: likeId,
					rating: likeInstance,
					action: (active ? 'cancel' : 'add'),
					userReaction: userReaction,
					totalCount: Number(likeInstance.countText.innerHTML),
				});
			}
		}

		if (
			!change
			&& likeInstance.version === 2
		)
		{
			const dataUsers = (
				likeInstance.topUsersDataNode
					? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users'))
					: false
			);

			if (dataUsers)
			{
				dataUsers.TOP = Object.values(dataUsers.TOP);

				likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
					you: !active,
					top: dataUsers.TOP,
					more: dataUsers.MORE,
				});
			}
		}

		if (
			likeInstance.template === 'light'
			&& !likeInstance.userReactionNode
		)
		{
			const cont = likeInstance.box;
			const likeNode = cont.cloneNode(true);

			likeNode.id = 'like_anim'; // to not dublicate original id

			let type = 'normal';
			if (cont.closest('.feed-com-informers-bottom'))
			{
				type = 'comment';
			}
			else if (cont.closest('.feed-post-informers'))
			{
				type = 'post';
			}

			likeNode.classList.remove('bx-ilike-button-hover')
			likeNode.classList.add('bx-like-anim')

			Dom.adjust(cont.parentNode, { style: { position: 'relative' } });

			Dom.adjust(likeNode, {
				style: {
					position: 'absolute',
					whiteSpace: 'nowrap',
					top: (type === 'post' ? '1px' : (type === 'comment' ? '0' : '')),
				}
			});

			Dom.adjust(cont, { style: { visibility: 'hidden' } });
			Dom.prepend(likeNode, cont.parentNode);

			new BX.easing({
				duration: 140,
				start: { scale: 100 },
				finish: { scale: (type === 'comment' ? 110 : 115) },
				transition : BX.easing.transitions.quad,
				step: (state) => {
					likeNode.style.transform = `scale(${state.scale / 100})`;
				},
				complete: () => {
					const likeThumbNode = Dom.create('SPAN', {
						props: {
							className: (active ? 'bx-ilike-icon' : 'bx-ilike-icon bx-ilike-icon-orange'),
						},
					});

					Dom.adjust(likeThumbNode, {
						style: {
							position: 'absolute',
							whiteSpace: 'nowrap',
						},
					});

					Dom.prepend(likeThumbNode, cont.parentNode);

					new BX.easing({
						duration: 140,
						start: { scale: (type == 'comment' ? 110 : 115) },
						finish: { scale: 100 },
						transition : BX.easing.transitions.quad,
						step: (state) => {
							likeNode.style.transform = `scale(${state.scale / 100})`;
						},
						complete: () => {}
					}).animate();

					const propsStart = {
						opacity: 100,
						scale: (type === 'comment' ? 110 : 115),
						top: 0,
					};
					const propsFinish = {
						opacity: 0,
						scale: 200,
						top: (type === 'comment' ? -3 : -2),
					};

					if (type !== 'comment')
					{
						propsStart.left = -5;
						propsFinish.left = -13;
					}

					new BX.easing({
						duration: 200,
						start: propsStart,
						finish: propsFinish,
						transition : BX.easing.transitions.linear,
						step: (state) => {
							likeThumbNode.style.transform = `scale(${state.scale / 100})`;
							likeThumbNode.style.opacity = state.opacity / 100;
							if (type !== 'comment')
							{
								likeThumbNode.style.left = `${state.left}px`;
							}
							likeThumbNode.style.top = `${state.top}px`;
						},
						complete: () => {
							likeNode.parentNode.removeChild(likeNode);
							likeThumbNode.parentNode.removeChild(likeThumbNode);

							Dom.adjust(cont.parentNode, { style: { position: 'static' } });
							Dom.adjust(cont, { style: { visibility: 'visible' } });
						}
					}).animate();

				}
			}).animate();
		}

		likeInstance.box.classList.remove('bx-ilike-button-hover');
	}

	static Draw(likeId, params)
	{
		const likeInstance = this.getInstance(likeId);
		likeInstance.countText.innerHTML = Number(params.TOTAL_POSITIVE_VOTES);

		if (
			!Type.isUndefined(params.TYPE)
			&& !Type.isUndefined(params.USER_ID)
			&& Number(params.USER_ID) > 0
			&& !Type.isUndefined(params.USER_DATA)
			&& !Type.isUndefined(params.USER_DATA.WEIGHT)
		)
		{
			const userWeight = parseFloat(params.USER_DATA.WEIGHT);

			const usersData = (
				likeInstance.topUsersDataNode
					? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users'))
					: false
			);

			if (
				params.TYPE != 'CHANGE'
				&& Type.isPlainObject(usersData)
			)
			{
				usersData.TOP = Object.values(usersData.TOP);
				let recalcNeeded = (usersData.TOP.length < 2);

				Object.values(usersData.TOP).forEach((item) => {
					if (recalcNeeded)
					{
						return;
					}

					if (
						(
							params.TYPE === 'ADD'
							&& userWeight > item.WEIGHT
						)
						|| (
							params.TYPE === 'CANCEL'
							&& params.USER_ID === item.ID
						)
					)
					{
						recalcNeeded = true;
					}
				});


				if (recalcNeeded)
				{
					if (
						params.TYPE === 'ADD'
						&& Number(params.USER_ID) !== Number(Loc.getMessage('USER_ID'))
					)
					{
						if (!usersData.TOP.find((a) => {
							return Number(a.ID) === Number(params.USER_ID)
						}))
						{
							usersData.TOP.push({
								ID: Number(params.USER_ID),
								NAME_FORMATTED: params.USER_DATA.NAME_FORMATTED,
								WEIGHT: parseFloat(params.USER_DATA.WEIGHT),
							});
						}
					}
					else if (params.TYPE === 'CANCEL')
					{
						usersData.TOP = usersData.TOP.filter((a) => {
							return Number(a.ID) !== Number(params.USER_ID);
						});
					}

					usersData.TOP.sort((a, b) => {
						if (parseFloat(a.WEIGHT) === parseFloat(b.WEIGHT))
						{
							return 0;
						}

						return (parseFloat(a.WEIGHT) > parseFloat(b.WEIGHT)) ? -1 : 1;
					});

					if (
						usersData.TOP.length > 2
						&& params.TYPE === 'ADD'
					)
					{
						usersData.TOP.pop();
						usersData.MORE++;
					}
				}
				else
				{
					if (params.TYPE === 'ADD')
					{
						usersData.MORE = (
							!Type.isUndefined(usersData.MORE)
								? Number(usersData.MORE) + 1
								: 1
						);
					}
					else if (params.TYPE === 'CANCEL')
					{
						usersData.MORE = (
							!Type.isUndefined(usersData.MORE)
							&& Number(usersData.MORE) > 0
								? Number(usersData.MORE) - 1
								: 0
						);
					}
				}

				likeInstance.topUsersDataNode.setAttribute('data-users', JSON.stringify(usersData));

				if (likeInstance.topUsersText)
				{
					likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
						you: (
							Number(params.USER_ID) === Number(Loc.getMessage('USER_ID'))
								? params.TYPE !== 'CANCEL'
								: likeInstance.count.classList.contains('bx-you-like')
						),
						top: usersData.TOP,
						more: usersData.MORE,
					});
				}
			}

			if (
				Type.isStringFilled(params.REACTION)
				&& Type.isStringFilled(params.REACTION_OLD)
				&& params.TYPE === 'CHANGE'
			)
			{
				RatingRender.setReaction({
					likeId: likeId,
					rating: likeInstance,
					action: 'change',
					userReaction: params.REACTION,
					userReactionOld: params.REACTION_OLD,
					totalCount: params.TOTAL_POSITIVE_VOTES,
					userId: params.USER_ID,
				});
			}
			else if (
				Type.isStringFilled(params.REACTION)
				&& [ 'ADD', 'CANCEL' ].includes(params.TYPE)
			)
			{
				RatingRender.setReaction({
					likeId: likeId,
					rating: likeInstance,
					userReaction: params.REACTION,
					action: (params.TYPE === 'ADD' ? 'add' : 'cancel'),
					totalCount: params.TOTAL_POSITIVE_VOTES,
					userId: params.USER_ID,
				});
			}
		}

		if (likeInstance.topPanel)
		{
			likeInstance.topPanel.setAttribute('data-popup', 'N');
		}

		if (!likeInstance.userReactionNode)
		{
			likeInstance.count.insertBefore(
				Dom.create('span', {
					props: {
						className: 'bx-ilike-plus-one',
					},
					style: {
						width: `${(element.countText.clientWidth - 8)}px`,
						height: `${(element.countText.clientHeight - 8)}px`,
					},
					html: (params.TYPE === 'ADD'? '+1': '-1'),
				}),
				element.count.firstChild
			);
		}

		if (likeInstance.popup)
		{
			likeInstance.popup.close();
			likeInstance.popupContentPage = 1;
		}
	}

	static Vote(likeId, voteAction, voteReaction, voteReactionOld)
	{
		if (!Type.isStringFilled(voteReaction))
		{
			voteReaction = 'like';
		}

		const ajaxInstance = (RatingManager.mobile ? new MobileAjaxWrapper : ajax);
		const likeInstance = this.getInstance(likeId);

		const successCallback = (response) => {

			const data = response.data;

			likeInstance.lastVote = data.action;
			likeInstance.lastReaction = voteReaction;

			const key = `${likeInstance.entityTypeId}_${likeInstance.entityId}`;

			this.lastVoteRepo.set(key, data.action);
			this.lastReactionRepo.set(key, data.voteReaction);

			likeInstance.countText.innerHTML = data.items_all;
			likeInstance.popupContentPage = 1;
			likeInstance.popupContent.innerHTML = '';
			likeInstance.popupContent.appendChild(Dom.create('span', {
				props: {
					className: 'bx-ilike-wait',
				},
			}));

			if (likeInstance.topPanel)
			{
				likeInstance.topPanel.setAttribute('data-popup', 'N');
			}

			ListPopup.AdjustWindow(likeId);

			const popup = document.getElementById(`ilike-popup-${likeId}`)
			if (
				popup
				&& popup.style.display === 'block'
			)
			{
				ListPopup.List(likeId, null, '', true);
			}

			if (
				likeInstance.version >= 2
				&& RatingManager.mobile
			)
			{
				BXMobileApp.onCustomEvent('onRatingLike', {
					action: data.action,
					ratingId: likeId,
					entityTypeId : likeInstance.entityTypeId,
					entityId: likeInstance.entityId,
					voteAction: voteAction,
					voteReaction: voteReaction,
					voteReactionOld: voteReactionOld,
					userId: Loc.getMessage('USER_ID'),
					userData: (!Type.isUndefined(data.user_data) ? data.user_data : null),
					itemsAll: data.items_all,
				}, true);
			}
		};

		const failureCallback = () => {

			const dataUsers = ((likeInstance.topUsersDataNode)
				? JSON.parse(likeInstance.topUsersDataNode.getAttribute('data-users'))
				: false
			);

			if (likeInstance.version == 2)
			{
				if (voteAction === 'change')
				{
					RatingRender.setReaction({
						likeId: likeId,
						rating: likeInstance,
						action: voteAction,
						userReaction: voteReaction,
						userReactionOld: voteReactionOld,
						totalCount: Number(likeInstance.countText.innerHTML),
					});
				}
				else
				{
					RatingRender.setReaction({
						likeId: likeId,
						rating: likeInstance,
						action: (voteAction === 'cancel' ? 'add' : 'cancel'),
						userReaction: voteReaction,
						totalCount: (
							voteAction == 'cancel'
								? Number(likeInstance.countText.innerHTML) + 1
								: Number(likeInstance.countText.innerHTML) - 1
						)
					});
				}

				if (likeInstance.buttonText)
				{
					if (voteAction === 'add')
					{
						likeInstance.buttonText.innerHTML = Loc.getMessage('RATING_LIKE_EMOTION_LIKE_CALC');
					}
					else if (voteAction === 'change')
					{
						likeInstance.buttonText.innerHTML = Loc.getMessage(`RATING_LIKE_EMOTION_${voteReactionOld.toUpperCase()}_CALC`);
					}
					else
					{
						likeInstance.buttonText.innerHTML = Loc.getMessage(`RATING_LIKE_EMOTION_${voteReaction.toUpperCase()}_CALC`);
					}
				}
			}

			if (
				dataUsers
				&& voteAction !== 'change'
				&& likeInstance.version == 2
			)
			{
				likeInstance.topUsersText.innerHTML = RatingRender.getTopUsersText({
					you: (voteAction === 'cancel'), // negative
					top: Object.values(dataUsers.TOP),
					more: dataUsers.MORE
				});
			}
		};

		const analyticsLabel ={
			b24statAction: 'addLike',
		}

		if (
			likeInstance.version >= 2
			&& RatingManager.mobile
		)
		{
			analyticsLabel.b24statContext = 'mobile';
		}

		ajaxInstance.runAction('main.rating.vote', {
			data: {
				params: {
					RATING_VOTE_TYPE_ID: likeInstance.entityTypeId,
					RATING_VOTE_KEY_SIGNED: likeInstance.keySigned,
					RATING_VOTE_ENTITY_ID: likeInstance.entityId,
					RATING_VOTE_ACTION: voteAction,
					RATING_VOTE_REACTION: voteReaction,
				},
			},
			analyticsLabel: analyticsLabel,
		}).then(
			successCallback,
			failureCallback
		);

		return false;
	}

	static LiveUpdate(params)
	{
		if (Number(params.USER_ID) === Number(Loc.getMessage('USER_ID')))
		{
			return false;
		}

		this.repo.forEach((likeInstance, likeId) => {
			if (
				likeInstance.entityTypeId !== params.ENTITY_TYPE_ID
				|| Number(likeInstance.entityId) !== Number(params.ENTITY_ID)
			)
			{
				return;
			}

			this.Draw(likeId, params);
		});


		RatingManager.live(params);
	}

	static Set(params: Params)
	{
		const mobile = !!params.mobile;

		if (params.template === undefined)
		{
			params.template = 'standart';
		}

		if (this.additionalParams.get('pathToUserProfile'))
		{
			params.pathToUserProfile = this.additionalParams.get('pathToUserProfile');
		}

		let likeInstance = this.getInstance(params.likeId);

		if (likeInstance && likeInstance.tryToSet > 5)
		{
			return;
		}

		const tryToSend = likeInstance && likeInstance.tryToSet ? likeInstance.tryToSet : 1;

		likeInstance = new RatingLike(params);
		this.setInstance(
			params.likeId,
			likeInstance
		);

		if (likeInstance.enabled)
		{
			this.Init(
				params.likeId,
				{
					mobile: mobile,
				}
			);
		}
		else
		{
			setTimeout(() => {
				likeInstance.tryToSet = tryToSend + 1;
				this.Set(params);
			}, 500);
		}
	}

	static setParams(params)
	{
		if (!Type.isUndefined(params.pathToUserProfile))
		{
			this.additionalParams.set('pathToUserProfile', params.pathToUserProfile);
		}
	}

	static Init(likeId, params)
	{
		params = (!Type.isUndefined(params) ? params : {});

		RatingManager.init(params);

		const likeInstance = this.getInstance(likeId);

		// like/unlike button
		if (likeInstance.available)
		{
			let eventNode = (
				likeInstance.template === 'standart'
					? likeInstance.button
					: likeInstance.buttonText
			);

			if (!RatingManager.mobile)
			{
				const eventNodeNew = eventNode.closest('.feed-new-like');
				if (eventNodeNew)
				{
					eventNode = eventNodeNew;
				}
			}

			if (
				likeInstance.version >= 2
				&& RatingManager.mobile
			)
			{
				eventNode.removeEventListener('touchstart', this.mobileTouchStartHandler);
				eventNode.addEventListener('touchstart', this.mobileTouchStartHandler);
			}

			const eventName = (RatingManager.mobile ? 'touchend' : 'click');
			eventNode.removeEventListener(eventName, this.buttonClickHandler);
			eventNode.addEventListener(eventName, this.buttonClickHandler);

			if (!RatingManager.mobile)
			{
				// Hover/unHover like-button
				likeInstance.box.addEventListener('mouseover', () => {
					likeInstance.box.classList.add('bx-ilike-button-hover');
				});
				likeInstance.box.addEventListener('mouseout', () => {
					likeInstance.box.classList.remove('bx-ilike-button-hover');
				});
			}
			else
			{
				likeInstance.topPanel.removeEventListener('click', this.mobileTopPanelClickHandler);
				likeInstance.topPanel.addEventListener('click', this.mobileTopPanelClickHandler);
			}
		}
		else if (Type.isDomNode(likeInstance.buttonText))
		{
			likeInstance.buttonText.innerHTML = likeInstance.localize['LIKE_D'];
			likeInstance.buttonText.classList.add('bx-ilike-text-unavailable');
		}
		// get like-user-list
		const clickShowPopupNode = (likeInstance.topUsersText ? likeInstance.topUsersText : likeInstance.count);

		if (!RatingManager.mobile)
		{
			clickShowPopupNode.addEventListener('mouseenter', (e) => {
				ListPopup.onResultMouseEnter({
					likeId: likeId,
					event: e,
					nodeId: e.currentTarget.id,
				});
			});

			clickShowPopupNode.addEventListener('mouseleave', (e) => {
				ListPopup.onResultMouseLeave({
					likeId: likeId,
				});
			})

			clickShowPopupNode.addEventListener('click', (e) => {
				ListPopup.onResultClick({
					likeId: likeId,
					event: e,
					nodeId: e.currentTarget.id,
				});
			})
		}

		if (
			likeInstance.version === 2
			&& likeInstance.available
			&& likeInstance.userReactionNode
		)
		{
			RatingRender.bindReactionsPopup({
				likeId: likeId,
			});
		}
	}

	static mobileTouchStartHandler()
	{
		RatingManager.startScrollTop = (
			(
				document.documentElement
				&& document.documentElement.scrollTop
			)
			|| document.body.scrollTop
		);
	}

	static buttonClickHandler(e)
	{
		const likeInstanceNode = e.currentTarget.closest('[data-rating-vote-id]');
		if (!Type.isDomNode(likeInstanceNode))
		{
			return;
		}

		const likeId = likeInstanceNode.getAttribute('data-rating-vote-id');
		if (!Type.isStringFilled(likeId))
		{
			return;
		}

		const likeInstance = RatingLike.getInstance(likeId);

		if (
			likeInstance.version >= 2
			&& RatingManager.mobile
			&& RatingRender.blockTouchEndByScroll
		)
		{
			RatingRender.blockTouchEndByScroll = false;
			return;
		}

		if (
			likeInstance.version < 2
			|| !RatingManager.mobile
			|| !RatingRender.reactionsPopupLikeId
		)
		{
			if (
				likeInstance.version >= 2
				&& RatingManager.mobile
			)
			{
				const currentScrollTop = (
					(
						document.documentElement
						&& document.documentElement.scrollTop
					)
					|| document.body.scrollTop
				);

				if (Math.abs(currentScrollTop - RatingManager.startScrollTop) > 2)
				{
					return;
				}
			}

			RatingLike.ClickVote(e, likeId);
		}

		if (likeInstance.version == 2)
		{
			RatingRender.afterClick({
				likeId: likeId,
			});
		}

		e.preventDefault();
	}

	static mobileTopPanelClickHandler(e)
	{
		const likeInstanceNode = e.currentTarget.querySelector('[data-like-id]');
		if (!Type.isDomNode(likeInstanceNode))
		{
			return;
		}

		const likeId = likeInstanceNode.getAttribute('data-like-id');
		if (!Type.isStringFilled(likeId))
		{
			return;
		}

		const likeInstance = RatingLike.getInstance(likeId);

		RatingRender.openMobileReactionsPage({
			entityTypeId: likeInstance.entityTypeId,
			entityId: likeInstance.entityId,
		});
		e.stopPropagation();
	}
}
