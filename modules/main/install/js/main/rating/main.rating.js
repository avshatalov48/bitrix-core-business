(function() {

var BX = window.BX;
BX.namespace("BXRL");

if (typeof BXRL.render != 'undefined')
{
	return;
}

BX.namespace("BXRL.render");
BX.namespace("BXRL.manager");

BXRL.render = {

	reactionsList: ['like', 'kiss', 'laugh', 'wonder', 'cry', 'angry'],
	popupCurrentReaction: false,
	popupPagesList: [],
	popupSizeInitialized: false,
	blockShowPopup: false,
	blockShowPopupTimeout: false,
	afterClickBlockShowPopup: false,

	getTopUsersText: function(params)
	{
		var you = (typeof params.you != 'undefined' ? !!params.you : false);
		var topList = (typeof params.top != 'undefined' && BX.type.isArray(params.top) ? params.top : []);
		var more = (typeof params.more != 'undefined' ? parseInt(params.more) : 0);

		if (
			!you
			&& topList.length <= 0
			&& more <= 0
		)
		{
			return '';
		}

		var result = BX.message('RATING_LIKE_TOP_TEXT_' + (you ? 'YOU_' : '') + (topList.length) + (more > 0 ? '_MORE' : ''));

		for(var i in topList)
		{
			if (!topList.hasOwnProperty(i))
			{
				continue;
			}

			result = result.replace('#USER_' + (parseInt(i) + 1) + '#', '<span class="feed-post-emoji-text-item">' + topList[i].NAME_FORMATTED + '</span>');
		}

		return result.replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">' + more + '</span>');
	},

	getUserReaction: function(params)
	{
		var result = '';
		var userReactionNode = (BX(params.userReactionNode) ? BX(params.userReactionNode) : false);

		if (userReactionNode)
		{
			result = userReactionNode.getAttribute('data-value');
		}

		return result;
	},

	setReaction: function(params)
	{
		if (
			typeof params.rating == 'undefined'
			|| !BX.type.isNotEmptyString(params.likeId)
		)
		{
			return;
		}

		var
			likeId = params.likeId,
			rating = params.rating,
			action = (BX.type.isNotEmptyString(params.action) ? params.action : 'add'),
			userReaction = (BX.type.isNotEmptyString(params.userReaction) ? params.userReaction : BX.message('RATING_LIKE_REACTION_DEFAULT')),
			userReactionOld = (BX.type.isNotEmptyString(params.userReactionOld) ? params.userReactionOld : BX.message('RATING_LIKE_REACTION_DEFAULT')),
			totalCount = (typeof params.totalCount != 'undefined' ? parseInt(params.totalCount) : null),
			userId = (typeof params.userId != 'undefined' ? parseInt(params.userId) : parseInt(BX.message('USER_ID')));

		if (!BX.util.in_array(action, ['add', 'cancel', 'change']))
		{
			return;
		}

		if (
			action == 'change'
			&& userReaction == userReactionOld
		)
		{
			return;
		}

		var userReactionNode = (BX(rating.userReactionNode) ? BX(rating.userReactionNode) : false);
		var reactionsNode = (BX(rating.reactionsNode) ? BX(rating.reactionsNode) : false);
		var topPanel = (BX(rating.topPanel) ? BX(rating.topPanel) : false);
		var topPanelContainer = (BX(rating.topPanelContainer) ? BX(rating.topPanelContainer) : false);
		var topUsersText = (BX(rating.topUsersText) ? BX(rating.topUsersText) : false);
		var countText = (BX(rating.countText) ? BX(rating.countText) : false);
		var buttonText = (BX(rating.buttonText) ? BX(rating.buttonText) : false);

		if (
			userId == BX.message('USER_ID') // not pull
			&& userReactionNode
		)
		{
			userReactionNode.setAttribute('data-value', (BX.util.in_array(action, ['add', 'change']) ? userReaction : ''));
		}

		var
			i = 0,
			elements = false,
			elementsNew = false,
			reactionValue = false,
			reactionCount = false;

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
			var reactionsContainer = BX.findChild(reactionsNode, { className: 'feed-post-emoji-icon-container'});

			elements = BX.findChildren(
				reactionsNode,
				{ className: 'feed-post-emoji-icon-item' },
				true
			);

			elementsNew = [];

			if(
				BX.type.isArray(elements)
				&& reactionsContainer
			)
			{
				var found = false,
					newValue = false;

				for (i = 0; i < elements.length; i++)
				{
					reactionValue = elements[i].getAttribute('data-reaction');
					reactionCount = parseInt(elements[i].getAttribute('data-value'));

					if (reactionValue == userReaction)
					{
						found = true;
						if (action == 'cancel')
						{
							newValue = (reactionCount > 0 ? reactionCount - 1 : 0);
						}
						else if (BX.util.in_array(action, ['add', 'change']))
						{
							newValue = reactionCount + 1;
						}

						if (newValue > 0)
						{
							elementsNew.push({
								reaction: reactionValue,
								count: newValue,
								animate: false
							});
						}
					}
					else if (
						action == 'change'
						&& reactionValue == userReactionOld
					)
					{
						newValue = (reactionCount > 0 ? reactionCount - 1 : 0);

						if (newValue > 0)
						{
							elementsNew.push({
								reaction: reactionValue,
								count: newValue,
								animate: false
							});
						}
					}
					else
					{
						elementsNew.push({
							reaction: reactionValue,
							count: reactionCount,
							animate: false
						});
					}
				}

				if (
					BX.util.in_array(action, ['add', 'change'])
					&& !found
				)
				{
					elementsNew.push({
						reaction: userReaction,
						count: 1,
						animate: true
					});
				}

				BX.cleanNode(reactionsContainer);

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
				}

				for(i = 0; i < elementsNew.length; i++)
				{
					if (i >= 1)
					{
						reactionsContainer.appendChild(BX.create('span', {
							props: {
								id: 'bx-ilike-result-reaction-' + elementsNew[i].reaction + '-' + likeId,
								className: 'feed-post-emoji-icon-item '+(elementsNew[i].animate ? 'feed-post-emoji-icon-animate' : '') + ' feed-post-emoji-icon-' + elementsNew[i].reaction + ' feed-post-emoji-icon-item-' + (i+1)
							},
							attrs: {
								'data-reaction': elementsNew[i].reaction,
								'data-value': elementsNew[i].count,
								'data-like-id': likeId,
								title: BX.message('RATING_LIKE_EMOTION_' + elementsNew[i].reaction.toUpperCase() + '_CALC')
							},
							events: {
								click: BXRL.render.resultReactionClick,
								mouseenter: BXRL.render.resultReactionMouseEnter,
								mouseleave: BXRL.render.resultReactionMouseLeave
							}
						}));
					}
					else
					{
						reactionsContainer.appendChild(BX.create('span', {
							props: {
								id: 'bx-ilike-result-reaction-' + elementsNew[i].reaction + '-' + likeId,
								className: 'feed-post-emoji-icon-item '+(elementsNew.length == 1 && elementsNew[i].animate ? 'feed-post-emoji-animation-pop' : '')+' feed-post-emoji-icon-' + elementsNew[i].reaction + ' feed-post-emoji-icon-item-' + (i+1)
							},
							attrs: {
								'data-reaction': elementsNew[i].reaction,
								'data-value': elementsNew[i].count,
								'data-like-id': likeId,
								title: BX.message('RATING_LIKE_EMOTION_' + elementsNew[i].reaction.toUpperCase() + '_CALC')
							},
							events: {
								click: BXRL.render.resultReactionClick,
								mouseenter: BXRL.render.resultReactionMouseEnter,
								mouseleave: BXRL.render.resultReactionMouseLeave
							}
						}));
					}
				}
			}
		}

		if (
			userId == BX.message('USER_ID')
			&& BX(buttonText)
		)
		{
			if (BX.util.in_array(action, ['add', 'change']))
			{
				BX(buttonText).innerHTML = BX.message('RATING_LIKE_EMOTION_' + userReaction.toUpperCase() + '_CALC');
/*
				BXRL.render.animateReactionText({
					rating: rating
				});
*/
			}
			else
			{
				BX(buttonText).innerHTML = BX.message('RATING_LIKE_EMOTION_LIKE_CALC');
			}
		}
	},

	animateReactionText: function(params)
	{
		var rating = params.rating;
		var buttonText = (BX(rating.buttonText) ? BX(rating.buttonText) : false);

		likeNode = buttonText.cloneNode(true);
		likeNode.id = 'like_anim'; // to not dublicate original id

		BX.removeClass(likeNode, 'bx-ilike-button-hover');
		BX.addClass(likeNode, 'bx-like-anim');

		BX.adjust(buttonText.parentNode, { style: { position: 'relative' } });

		var type = 'normal';
		if (BX.findParent(buttonText, { 'className': 'feed-com-informers-bottom' }))
		{
			type = 'comment';
		}
		else if (BX.findParent(buttonText, { 'className': 'feed-post-informers' }))
		{
			type = 'post';
		}

		BX.adjust(likeNode, {
			style: {
				position: 'absolute',
				whiteSpace: 'nowrap',
				top: (type == 'comment' ? '-3px' : '')
			}
		});

		BX.adjust(buttonText, { style: { visibility: 'hidden' } });
		BX.prepend(likeNode, buttonText.parentNode);

		new BX.easing({
			duration: 140,
			start: { scale: 100 },
			finish: { scale: 115 },
			transition : BX.easing.transitions.quad,
			step: function(state) {
				likeNode.style.transform = "scale(" + state.scale / 100 + ")";
			},
			complete: function() {

				new BX.easing({
					duration: 140,
					start: { scale: 115 },
					finish: { scale: 100 },
					transition : BX.easing.transitions.quad,
					step: function(state) {
						likeNode.style.transform = "scale(" + state.scale / 100 + ")";
					},
					complete: function() {
						likeNode.parentNode.removeChild(likeNode);

						BX.adjust(buttonText.parentNode, { style: { position: 'static' } });
						BX.adjust(buttonText, { style: { visibility: 'visible' } });
					}
				}).animate();
			}
		}).animate();
	},

	reactionsPopup: null,
	reactionsPopupAnimation: null,
	reactionsPopupAnimation2: null,
	reactionsPopupLikeId: null,
	reactionsPopupMouseOutHandler: null,
	reactionsPopupOpacityState: 0,

	showReactionsPopup: function(params)
	{
		var
			bindElement = (BX(params.bindElement) ? BX(params.bindElement) : false),
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		if (
			!bindElement
			|| !likeId
		)
		{
			return false;
		}

		BXRL.render.reactionsPopupLikeId = likeId;

		if (BXRL.render.reactionsPopup == null)
		{
			var reactionsNodesList = [];

			for(var i in BXRL.render.reactionsList)
			{
				var currentEmotion = BXRL.render.reactionsList[i];

				reactionsNodesList.push(BX.create('div', {
					props: {
						className: 'feed-post-emoji-icon-item feed-post-emoji-icon-' + currentEmotion
					},
					attrs: {
						'data-reaction': currentEmotion,
						title: BX.message('RATING_LIKE_EMOTION_' + currentEmotion.toUpperCase() + '_CALC')
					}
				}));
			}

			BXRL.render.reactionsPopup = BX.create('div', {
				props: {
					className: 'feed-post-emoji-popup-container'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'feed-post-emoji-icon-inner'
						},
						children: reactionsNodesList
					})
				]
			});

			BX.adjust(BXRL.render.reactionsPopup, {
				events: {
					click: function(e) {
						var reactionNode = false;
						if (e.target.classList.contains('feed-post-emoji-icon-item'))
						{
							reactionNode = e.target;
						}
						else
						{
							reactionNode = BX.findParent(e.target, {className: 'feed-post-emoji-icon-item'}, BXRL.render.reactionsPopup);
						}

						if (reactionNode)
						{
							RatingLike.ClickVote(
								BXRL.render.reactionsPopupLikeId,
								reactionNode.getAttribute('data-reaction'),
								true
							);
						}
						e.preventDefault();
					}
				}
			});

			BX.append(BXRL.render.reactionsPopup, document.body);
		}
		else if (BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible'))
		{
			BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible');
		}
		else
		{
			return;
		}

		BXRL.render.reactionsPopupMouseOutHandler = BX.proxy(function(e) {

			var popupPosition = BXRL.render.reactionsPopup.getBoundingClientRect();
			var inverted = BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-inverted');

			if (
				e.clientX >= popupPosition.left
				&& e.clientX <= popupPosition.right
				&& e.clientY >= popupPosition.top - (inverted ? 25 : 0)
				&& e.clientY <= (popupPosition.bottom + (inverted ? 0 : 25))
			)
			{
				return;
			}

			BXRL.render.blockReactionsPopup();
			BXRL.render.hideReactionsPopup({
				likeId: this.likeId
			});

			BX.unbind(document, 'mousemove', BXRL.render.reactionsPopupMouseOutHandler);
			BXRL.render.reactionsPopupMouseOutHandler = null;
		}, { likeId: likeId });

		var bindElementPosition = BX.pos(bindElement);

		if (BX.findParent(bindElement, { className: 'iframe-comments-cont'}))
		{
			bindElementPosition.left =+ 100;
		}

		var inverted = ((bindElementPosition.top - BX.GetWindowSize().scrollTop) < 80);

		if (inverted)
		{
			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-inverted');
		}
		else
		{
			BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-inverted');
		}

		var deltaY = (inverted ? 15 : -50);

		BXRL.render.reactionsPopupAnimation = new BX.easing({
			duration: 300,
			start: {
				width: 100,
				left: (bindElementPosition.left + (bindElementPosition.width / 2) - 50),
				top: ((inverted ? bindElementPosition.top - 30 : bindElementPosition.top + 30 ) + deltaY),
				borderRadius: 0,
				opacity: 0
			},
			finish: {
				width: 271,
				left: (bindElementPosition.left + (bindElementPosition.width / 2) - 133),
				top: (bindElementPosition.top + deltaY),
				borderRadius: 50,
				opacity: 100
			},
			transition : BX.easing.makeEaseInOut(BX.easing.transitions.cubic),
			step: function(state) {
				BXRL.render.reactionsPopup.style.width = state.width + 'px';
				BXRL.render.reactionsPopup.style.left = state.left + 'px';
				BXRL.render.reactionsPopup.style.top = state.top + 'px';
				BXRL.render.reactionsPopup.style.borderRadius = state.borderRadius + 'px';
				BXRL.render.reactionsPopup.style.opacity = state.opacity/100;
				BXRL.render.reactionsPopupOpacityState = state.opacity;
			},
			complete: function() {
				BXRL.render.reactionsPopup.style.opacity = '';
				BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
				BXRL[likeId].box.classList.add('feed-post-emoji-control-active');
			}
		});
		BXRL.render.reactionsPopupAnimation.animate();

		setTimeout(function() {
				var reactions = BX.findChildren(
					BXRL.render.reactionsPopup,
					{ className: 'feed-post-emoji-icon-item' },
					true
				);
				BXRL.render.reactionsPopupAnimation2 = new BX.easing({
					duration: 140,
						start: {
							opacity: 0
						},
						finish: {
							opacity: 100
						},
						transition : BX.easing.transitions.cubic,
						step: function(state) {
							reactions[0].style.opacity = state.opacity/100;
							reactions[1].style.opacity = state.opacity/100;
							reactions[2].style.opacity = state.opacity/100;
							reactions[3].style.opacity = state.opacity/100;
							reactions[4].style.opacity = state.opacity/100;
							reactions[5].style.opacity = state.opacity/100;
						},
						complete: function() {
							BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
							reactions[0].style.opacity = '';
							reactions[1].style.opacity = '';
							reactions[2].style.opacity = '';
							reactions[3].style.opacity = '';
							reactions[4].style.opacity = '';
							reactions[5].style.opacity = '';
					}
				});
				BXRL.render.reactionsPopupAnimation2.animate();
			},
			100
		);

		if (!BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-active'))
		{
			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active');
		}

		BX.bind(document, 'mousemove', BXRL.render.reactionsPopupMouseOutHandler);
	},

	hideReactionsPopup: function(params)
	{
		var
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		if (BXRL.render.reactionsPopup)
		{
			if (BXRL.render.reactionsPopupAnimation)
			{
				BXRL.render.reactionsPopupAnimation.stop();
			}
			if (BXRL.render.reactionsPopupAnimation2)
			{
				BXRL.render.reactionsPopupAnimation2.stop();
			}

			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-invisible');

			BXRL.render.reactionsPopupAnimation4 = new BX.easing({
				duration: 500,
				start: {
					opacity: BXRL.render.reactionsPopupOpacityState
				},
				finish: {
					opacity: 0
				},
				transition : BX.easing.transitions.linear,
				step: function(state) {
					BXRL.render.reactionsPopup.style.opacity = state.opacity/100;
					BXRL.render.reactionsPopupOpacityState = state.opacity;
				},
				complete: function() {
					BXRL.render.reactionsPopup.style.opacity = '';
					BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');

					BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
					BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
					BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
				}
			});
			BXRL.render.reactionsPopupAnimation4.animate();

			BXRL[likeId].box.classList.remove('feed-post-emoji-control-active');
		}

		BXRL.render.bindReactionsPopup({
			likeId: likeId
		});
	},

	bindReactionsPopup: function(params) {
		var
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		if (
			!likeId
			|| typeof BXRL[likeId] == 'undefined'
			|| !BXRL[likeId]
		)
		{
			return false;
		}

		BXRL[likeId].mouseOverHandler = BX.debounce(function() {

			if (BXRL.render.afterClickBlockShowPopup)
			{
				BX.unbind(BXRL[this.likeId].box, 'mouseenter', BXRL[this.likeId].mouseOverHandler);
				BX.unbind(BXRL[this.likeId].box, 'mouseleave', BXRL.render.blockReactionsPopup);
				return;
			}

			if (!BXRL.render.blockShowPopup)
			{
				BXRL.render.showReactionsPopup({
					bindElement: BXRL[this.likeId].box,
					likeId: this.likeId
				});
				BX.unbind(BXRL[this.likeId].box, 'mouseenter', BXRL[this.likeId].mouseOverHandler);
				BX.unbind(BXRL[this.likeId].box, 'mouseleave', BXRL.render.blockReactionsPopup);
			}
		}, 500, {
			likeId: likeId
		});

		BX.bind(BXRL[likeId].box, 'mouseenter', BXRL[likeId].mouseOverHandler);
		BX.bind(BXRL[likeId].box, 'mouseleave', BXRL.render.blockReactionsPopup);
	},

	buildPopupContent: function(params)
	{
		var
			clear = (!!params.clear ? params.clear : false),
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false),
			rating = params.rating,
			requestReaction = (BX.type.isNotEmptyString(params.reaction) ? params.reaction : ''),
			page = (parseInt(params.page) > 0 ? params.page : 1),
			data = params.data,
			reaction = false;

		var
			reactionsList = [],
			reactionsCount = 0,
			lastReaction = null,
			i = null;

		if (
			clear
			&& page == 1
		)
		{
			BXRL.render.clearPopupContent({
				likeId: likeId
			});
		}

		this.popupCurrentReaction = (BX.type.isNotEmptyString(requestReaction) ? requestReaction : 'all');

		if (
			requestReaction.length <= 0
			|| requestReaction == 'all'
		) // first current tab
		{
			BXRL.render.popupSizeInitialized = false;
			BX('bx-ilike-popup-cont-' + likeId).style.height = 'auto';
			BX('bx-ilike-popup-cont-' + likeId).style.minWidth = 'auto';
		}

		if (!BX.type.isNotEmptyString(requestReaction))
		{
			this.popupPagesList = {};
		}

		this.popupPagesList[(requestReaction == '' ? 'all' : requestReaction)] = (page + 1);

		if (typeof data.reactions != 'undefined')
		{
			for(reaction in data.reactions)
			{
				if (
					!data.reactions.hasOwnProperty(reaction)
					|| parseInt(data.reactions[reaction]) <= 0
				)
				{
					continue;
				}

				reactionsList.push({
					reaction: reaction,
					count: parseInt(data.reactions[reaction])
				});
				reactionsCount++;
				lastReaction = reaction;
			}
		}

		var tabsNode = BX.create('span', {
			props: {
				className: 'bx-ilike-popup-head'
			}
		});

		if (reactionsCount > 1)
		{
			tabsNode.appendChild(BX.create('span', {
				props: {
					className: 'bx-ilike-popup-head-item' + (!BX.type.isNotEmptyString(requestReaction) || requestReaction == 'all' ? ' bx-ilike-popup-head-item-current' : '')
				},
				children: [
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-head-icon feed-post-emoji-icon-all'
						}
					}),
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-head-text'
						},
						html: BX.message('RATING_LIKE_POPUP_ALL').replace('#CNT#', parseInt(data.items_all))
					})
				],
				events: {
					click: BX.proxy(function(e) {
						BXRL.render.changePopupTab({
							likeId: this.likeId,
							rating: this.rating,
							reaction: 'all'
						});
						e. preventDefault();
					}, {
						likeId: likeId,
						rating: rating
					})
				}
			}));
		}

		if (reactionsCount == 0)
		{
			reactionsList.push({
				reaction: BX.message('RATING_LIKE_REACTION_DEFAULT'),
				count: parseInt(data.items_all)
			});
		}

		reactionsList.sort(function(a, b) {
			var sample = {
				like: 0,
				kiss: 1,
				laugh: 2,
				wonder: 3,
				cry: 4,
				angry: 5
			};
			return sample[a.reaction] > sample[b.reaction];
		});

		for(var ind = 0; ind < reactionsList.length; ind++)
		{
			tabsNode.appendChild(BX.create('span', {
				props: {
					className: 'bx-ilike-popup-head-item' + (requestReaction == reactionsList[ind].reaction ? ' bx-ilike-popup-head-item-current' : '')
				},
				attrs: {
					title: BX.message('RATING_LIKE_EMOTION_' + reactionsList[ind].reaction.toUpperCase() + '_CALC')
				},
				children: [
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-head-icon feed-post-emoji-icon-item feed-post-emoji-icon-' + reactionsList[ind].reaction
						}
					}),
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-head-text'
						},
						html: reactionsList[ind].count
					})
				],
				events: {
					click: BX.proxy(function(e) {

						var popupContent = BX('bx-ilike-popup-cont-' + this.likeId);
						var popupContentPosition = popupContent.getBoundingClientRect();

						if (
							requestReaction.length <= 0
							|| requestReaction == 'all'
						) // first current tab
						{
							BXRL.render.popupSizeInitialized = true;
							popupContent.style.height = popupContentPosition.height + 'px';
							popupContent.style.minWidth = popupContentPosition.width + 'px';
						}
						else
						{
							if (popupContentPosition.width > parseInt(popupContent.style.minWidth))
							{
								popupContent.style.minWidth = popupContentPosition.width + 'px';
							}
						}

						BXRL.render.changePopupTab({
							likeId: this.likeId,
							rating: this.rating,
							reaction: this.reaction
						});
						e. preventDefault();
					}, {
						likeId: likeId,
						rating: rating,
						reaction: reactionsList[ind].reaction
					})
				}
			}));
		}

		var usersNode = BX.findChild(rating.popupContent, { className: 'bx-ilike-popup-content-container' });
		var usersNodeExists = false;

		if (!usersNode)
		{
			usersNode = BX.create('span', {
				props: {
					className: 'bx-ilike-popup-content-container'
				}
			});
		}
		else
		{
			usersNodeExists = true;
		}

		var contentNodes = BX.findChildren(usersNode, { className: 'bx-ilike-popup-content' });

		for(i = 0; i < contentNodes.length; i++)
		{
			contentNodes[i].classList.add('bx-ilike-popup-content-invisible');
		}

		var reactionUsersNode = BX.findChild(usersNode, { className: 'bx-ilike-popup-content-' + this.popupCurrentReaction });
		if (!reactionUsersNode)
		{
			reactionUsersNode = BX.create('span', {
				props: {
					className: 'bx-ilike-popup-content bx-ilike-popup-content-' + this.popupCurrentReaction
				}
			});
			usersNode.appendChild(reactionUsersNode);
		}
		else
		{
			reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
		}

		for (i = 0; i < data.items.length; i++)
		{
			reactionUsersNode.appendChild(BX.create('a', {
				props: {
					className: 'bx-ilike-popup-user-item' + (BX.type.isNotEmptyString(data.items[i]['USER_TYPE']) ? " bx-ilike-popup-user-item-" + data.items[i]['USER_TYPE'] : "")
				},
				attrs: {
					href: data.items[i]['URL'],
					target: '_blank'
				},
				children: [
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-user-icon'
						},
						style: (
							BX.type.isNotEmptyString(data.items[i]['PHOTO_SRC'])
								? {
									'background-image': 'url("' + data.items[i]['PHOTO_SRC'] + '")'
								}
								: {}
						)
					}),
					BX.create('span', {
						props: {
							className: 'bx-ilike-popup-user-name'
						},
						html: data.items[i]['FULL_NAME']
					}),
					BX.create("SPAN", {
						props: {
							className: "bx-ilike-popup-user-status"
						}
					})
				]
			}));
		}

		var waitNode = BX.findChild(rating.popupContent, { className: 'bx-ilike-wait' });
		if (waitNode)
		{
			BX.cleanNode(waitNode, true);
		}
		var tabsNodeOld = BX.findChild(rating.popupContent, { className: 'bx-ilike-popup-head' });
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
	},

	clearPopupContent: function(params)
	{
		var
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		BXRL[likeId].popupContent.innerHTML = '';
		BX('bx-ilike-popup-cont-' + likeId).style.height = 'auto';
		BX('bx-ilike-popup-cont-' + likeId).style.minWidth = 'auto';
		BXRL[likeId].popupContent.appendChild(BX.create('span', {
			props: {
				className: 'bx-ilike-wait'
			}
		}));
	},

	blockReactionsPopup: function()
	{
		if (BXRL.render.blockShowPopupTimeout)
		{
			window.clearTimeout(BXRL.render.blockShowPopupTimeout);
		}
		BXRL.render.blockShowPopup = true;
		BXRL.render.blockShowPopupTimeout = setTimeout(function() {
			BXRL.render.blockShowPopup = false;
		}, 500);
	},

	changePopupTab: function(params)
	{
		var
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false),
			rating = params.rating,
			reaction = (BX.type.isNotEmptyString(params.reaction) ? params.reaction : ''),
			i = false,
			reactionTabNode = false;

		var contentContainerNode = BX.findChild(rating.popupContent, { className: 'bx-ilike-popup-content-container' });
		if (!contentContainerNode)
		{
			return false;
		}

		var reactionUsersNode = BX.findChild(contentContainerNode, { className: 'bx-ilike-popup-content-' + reaction });
		if (reactionUsersNode)
		{
			this.popupCurrentReaction = (BX.type.isNotEmptyString(reaction) ? reaction : 'all');

			var tabNodes = BX.findChildren(rating.popupContent, { className: 'bx-ilike-popup-head-item' }, true);
			for(i = 0; i < tabNodes.length; i++)
			{
				tabNodes[i].classList.remove('bx-ilike-popup-head-item-current');
				reactionTabNode = BX.findChild(tabNodes[i], { className: 'feed-post-emoji-icon-' + reaction });
				if (reactionTabNode)
				{
					tabNodes[i].classList.add('bx-ilike-popup-head-item-current');
				}
			}

			var contentNodes = BX.findChildren(contentContainerNode, { className: 'bx-ilike-popup-content' });
			for(i = 0; i < contentNodes.length; i++)
			{
				contentNodes[i].classList.add('bx-ilike-popup-content-invisible');
			}
			reactionUsersNode.classList.remove('bx-ilike-popup-content-invisible');
		}
		else
		{
			RatingLike.List(likeId, 1, reaction);
		}
	},

	afterClick: function (params)
	{
		var
			likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		if (!likeId)
		{
			return;
		}

		BXRL.render.afterClickBlockShowPopup = true;

		BXRL.render.afterClickHandler = BX.proxy(function(e) {
				BXRL.render.afterClickBlockShowPopup = false;
				BX.unbind(BXRL[this.likeId].box, 'mouseleave', BXRL.render.afterClickHandler);
			}, {
			likeId: likeId
			}
		);

		BX.bind(BXRL[likeId].box, 'mouseleave', BXRL.render.afterClickHandler);
	},

	resultReactionClick: function (e) {

		var
			likeId = e.currentTarget.getAttribute('data-like-id'),
			reaction = e.currentTarget.getAttribute('data-reaction');

		RatingLike.onResultClick({
			likeId: likeId,
			event: e,
			reaction: reaction
		});
	},

	resultReactionMouseEnter: function (e) {

		var
			likeId = e.currentTarget.getAttribute('data-like-id'),
			reaction = e.currentTarget.getAttribute('data-reaction');

		RatingLike.onResultMouseEnter({
			likeId: likeId,
			event: e,
			reaction: reaction
		});
	},

	resultReactionMouseLeave: function (e) {

		var
			likeId = e.currentTarget.getAttribute('data-like-id'),
			reaction = e.currentTarget.getAttribute('data-reaction');

		RatingLike.onResultMouseLeave({
			likeId: likeId,
			reaction: reaction
		});
	}
};

BXRL.manager = {

	inited: false,
	displayHeight: 0,
	entityList: [],
	ratingNodeList: {},
	delayedList: {},

	init: function()
	{
		if (this.inited)
		{
			return;
		}

		this.inited = true;

		this.setDisplayHeight();

		window.addEventListener("scroll",  BX.throttle(function() {
			this.getInViewScope();
		}, 80, this), { passive: true });

		window.addEventListener("resize",  BX.delegate(this.setDisplayHeight, this));
	},

	addEntity: function(entityId, ratingObject)
	{
		if (
			!BX.util.in_array(entityId, this.entityList)
			&& ratingObject.topPanelContainer
		)
		{
			this.entityList.push(entityId);
			this.addNode(entityId, ratingObject.topPanelContainer);
		}
	},

	checkEntity: function(entityId)
	{
		return BX.util.in_array(entityId, this.entityList);
	},

	addNode: function(entityId, node)
	{
		if (
			BX(node)
			&& typeof this.ratingNodeList[entityId] != 'undefined'
		)
		{
			return;
		}

		this.ratingNodeList[entityId] = node;
	},

	getNode: function(entityId)
	{
		var result = false;

		if (typeof this.ratingNodeList[entityId] != undefined)
		{
			result = this.ratingNodeList[entityId];
		}

		return result;
	},

	setDisplayHeight: function()
	{
		this.displayHeight = document.documentElement.clientHeight;
	},

	getInViewScope: function()
	{
		var ratingNode = null;
		for(var key in this.delayedList)
		{
			if (!this.delayedList.hasOwnProperty(key))
			{
				continue;
			}

			ratingNode = BX(this.getNode(key));

			if (!ratingNode)
			{
				continue;
			}

			if (this.isNodeVisibleOnScreen(ratingNode))
			{
				this.fireAnimation(key, ratingNode, this.delayedList[key]);
			}
		}
	},

	isNodeVisibleOnScreen: function(node)
	{
		var coords = node.getBoundingClientRect();
		var visibleAreaTop = parseInt(this.displayHeight/10);
		var visibleAreaBottom = parseInt(this.displayHeight * 9/10);

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
	},

	live: function(params)
	{
		if (
			typeof params.TYPE == 'undefined'
			|| params.TYPE != 'ADD'
			|| !BX.type.isNotEmptyString(params.ENTITY_TYPE_ID)
			|| typeof params.ENTITY_ID == 'undefined'
			|| parseInt(params.ENTITY_ID) <= 0
		)
		{
			return;
		}

		var key = params.ENTITY_TYPE_ID + '_' + params.ENTITY_ID;
		if (!this.checkEntity(key))
		{
			return;
		}

		var ratingNode = this.getNode(key);
		if (!ratingNode)
		{
			return false;
		}

		if (this.isNodeVisibleOnScreen(ratingNode))
		{
			this.fireAnimation(key, ratingNode, params);
		}
		else
		{
			this.addDelayed(params)
		}
	},

	addDelayed: function(liveParams)
	{
		if (
			!BX.type.isNotEmptyString(liveParams.ENTITY_TYPE_ID)
			|| typeof liveParams.ENTITY_ID == 'undefined'
			|| parseInt(liveParams.ENTITY_ID) <= 0
		)
		{
			return;
		}

		var key = liveParams.ENTITY_TYPE_ID + '_' + liveParams.ENTITY_ID;

		if (typeof this.delayedList[key] == 'undefined')
		{
			this.delayedList[key] = [];
		}

		this.delayedList[key].push(liveParams);
	},

	fireAnimation: function(key, node, data)
	{
		if (typeof this.delayedList[key] != 'undefined')
		{
			delete this.delayedList[key];
		}
	}
};

})();
