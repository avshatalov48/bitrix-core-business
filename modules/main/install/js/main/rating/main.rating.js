/**
* @bxjs_lang_path config.php
*/
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

	reactionsList: ['like', 'kiss', 'laugh', 'wonder', 'cry', 'angry', 'facepalm'],
	popupCurrentReaction: false,
	popupPagesList: [],
	popupSizeInitialized: false,
	blockShowPopup: false,
	blockShowPopupTimeout: false,
	afterClickBlockShowPopup: false,
	touchStartPosition: null,
	touchCurrentPosition: {
		x: null,
		y: null
	},
	currentReactionNodeHover: null,
	touchMoveDeltaY: null,
	touchScrollTop: 0,
	hasMobileTouchMoved: null,
	mobileOverlay: null,

	getTopUsersText: function(params)
	{
		var you = (typeof params.you != 'undefined' ? !!params.you : false);
		var topList = (typeof params.top != 'undefined' && BX.type.isArray(params.top) ? params.top : []);
		var more = (typeof params.more != 'undefined' ? parseInt(params.more) : 0);
		var result = '';

		if (
			topList.length <= 0
			&& !you
			&& (
				BXRL.manager.mobile
				|| more <= 0
			)
		)
		{
			return result;
		}

		if (BXRL.manager.mobile)
		{
			if (you)
			{
				topList.push({
					ID: parseInt(BX.message('USER_ID')),
					NAME_FORMATTED: BX.message('RATING_LIKE_TOP_TEXT3_YOU'),
					WEIGHT: 1
				});
			}

			result = BX.message('RATING_LIKE_TOP_TEXT3_' + (topList.length > 1 ? '2' : '1')).
				replace("#OVERFLOW_START#", BXRL.manager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '').
				replace("#OVERFLOW_END#", BXRL.manager.mobile ? '</span>' : '');
		}
		else
		{
			result = BX.message('RATING_LIKE_TOP_TEXT2_' + (you ? 'YOU_' : '') + (topList.length) + (more > 0 ? '_MORE' : '')).
				replace("#OVERFLOW_START#", BXRL.manager.mobile ? '<span class="feed-post-emoji-text-item-overflow">' : '').
				replace("#OVERFLOW_END#", BXRL.manager.mobile ? '</span>' : '').
				replace("#MORE_START#", BXRL.manager.mobile ? '<span class="feed-post-emoji-text-item-more">' : '&nbsp;').
				replace("#MORE_END#", BXRL.manager.mobile ? '</span>' : '');
		}

		if (BXRL.manager.mobile)
		{
			topList.sort(function(a, b) {
				if(parseFloat(a.ID) === parseInt(BX.message('USER_ID')))
				{
					return -1;
				}

				if(parseInt(b.ID) === parseInt(BX.message('USER_ID')))
				{
					return 1;
				}

				if (parseFloat(a.WEIGHT) === parseFloat(b.WEIGHT))
				{
					return 0;
				}

				return (parseFloat(a.WEIGHT) > parseFloat(b.WEIGHT) ? -1 : 1);
			});

			var userNameList = topList.map(function(item) {
				return item.NAME_FORMATTED;
			});

			var
				userNameBegin = '',
				userNameEnd = '';

			if (userNameList.length === 1)
			{
				userNameBegin = userNameList.pop();
				userNameEnd = '';
			}
			else
			{
				userNameBegin = userNameList.slice(0, userNameList.length-1).join(BX.message('RATING_LIKE_TOP_TEXT3_USERLIST_SEPARATOR').replace(/#USERNAME#/g, ''));
				userNameEnd = userNameList[userNameList.length-1];
			}

			result = result.replace('#USER_LIST_BEGIN#', userNameBegin).replace('#USER_LIST_END#', userNameEnd);
		}
		else
		{
			for(var i in topList)
			{
				if (!topList.hasOwnProperty(i))
				{
					continue;
				}

				result = result.replace('#USER_' + (parseInt(i) + 1) + '#', '<span class="feed-post-emoji-text-item">' + topList[i].NAME_FORMATTED + '</span>');
			}
			result = result.replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">' + more + '</span>');
		}

		return result;
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
			var reactionsContainer = reactionsNode.querySelector('.feed-post-emoji-icon-container');
			elements = reactionsNode.querySelectorAll('.feed-post-emoji-icon-item');

			elementsNew = [];

			if(reactionsContainer)
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

					if (BXRL.manager.mobile)
					{
						var commentNode = BX.findParent(topPanel, { className: 'post-comment-block'});
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

				var reactionEvents = (
					BXRL.manager.mobile
					? {}
					: {
						click: BXRL.render.resultReactionClick,
						mouseenter: BXRL.render.resultReactionMouseEnter,
						mouseleave: BXRL.render.resultReactionMouseLeave
					}
				);

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
							events: reactionEvents
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
							events: reactionEvents
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
				if (BXRL.manager.mobile)
				{
					buttonText.parentElement.className = 'bx-ilike-left-wrap bx-you-like-button bx-you-like-button-' + userReaction.toLowerCase();
				}
			}
			else
			{
				BX(buttonText).innerHTML = BX.message('RATING_LIKE_EMOTION_LIKE_CALC');
				if (BXRL.manager.mobile)
				{
					buttonText.parentElement.className = 'bx-ilike-left-wrap';
				}
			}
		}
	},

	reactionsPopup: null,
	reactionsPopupAnimation: null,
	reactionsPopupAnimation2: null,
	reactionsPopupLikeId: null,
	reactionsPopupMouseOutHandler: null,
	reactionsPopupOpacityState: 0,
	reactionsPopupTouchStartIn: null,
	reactionsPopupPositionY: null,
	blockTouchEndByScroll: false,

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
				if (!BXRL.render.reactionsList.hasOwnProperty(i))
				{
					continue;
				}

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
					className: 'feed-post-emoji-popup-container' + (BXRL.manager.mobile ? ' feed-post-emoji-popup-container-mobile' : '')
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

			BX.bind(BXRL.render.reactionsPopup, (BXRL.manager.mobile ? 'touchend' : 'click'), function(e)
			{
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
			});


			BX.append(BXRL.render.reactionsPopup, document.body);
		}
		else if (BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible'))
		{
			BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible');
		}
		else if (
			BXRL.manager.mobile
			&& BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-invisible-final-mobile')
		)
		{
			BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final-mobile');
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

		if (
			BX.findParent(bindElement, { className: 'feed-com-informers-bottom'})
			&& (
				BX.findParent(bindElement, { className: 'iframe-comments-cont'})
				|| BX.findParent(bindElement, { className: 'task-iframe-popup'})
			)
		)
		{
			bindElementPosition.left += 100;
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

		var deltaY = (inverted ? 15 : -45);

		if (BXRL.manager.mobile)
		{
			BXRL.render.touchMoveDeltaY = (inverted ? 60 : -45);
			BX.adjust(BXRL.render.reactionsPopup, { style: {
				left: '12px',
				top: ((inverted ? (bindElementPosition.top - 23) : (bindElementPosition.top - 28)) + deltaY) + 'px',
				width: '330px',
				borderRadius: '61px'
			} });

			BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-invisible-final');
			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active-final');
			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
			BXRL[likeId].box.classList.add('feed-post-emoji-control-active');
			BXRL.render.reactionsPopupMobileDisableScroll();
		}
		else
		{
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
					width: 305,
					left: (bindElementPosition.left + (bindElementPosition.width / 2) - 133),
					top: (bindElementPosition.top + deltaY - 5),
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

					var reactions = BXRL.render.reactionsPopup.querySelectorAll('.feed-post-emoji-icon-item');

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
							reactions[6].style.opacity = state.opacity/100;
						},
						complete: function() {
							BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active-final-item');
							reactions[0].style.opacity = '';
							reactions[1].style.opacity = '';
							reactions[2].style.opacity = '';
							reactions[3].style.opacity = '';
							reactions[4].style.opacity = '';
							reactions[5].style.opacity = '';
							reactions[6].style.opacity = '';
						}
					});
					BXRL.render.reactionsPopupAnimation2.animate();
				},
				100
			);
		}

		if (!BXRL.render.reactionsPopup.classList.contains('feed-post-emoji-popup-active'))
		{
			BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-active');
		}

		if (!BXRL.manager.mobile)
		{
			BX.bind(document, 'mousemove', BXRL.render.reactionsPopupMouseOutHandler);
		}
		else
		{
			BXRL.render.touchScrollTop = BX.GetWindowSize().scrollTop;
			BXRL.render.hasMobileTouchMoved = null;

			window.addEventListener("touchend", BXRL.render.reactionsPopupMobileTouchEndHandler);
			window.addEventListener("touchmove", BXRL.render.reactionsPopupMobileTouchMoveHandler);
		}
	},

	reactionsPopupMobileTouchEndHandler: function(e)
	{
		var coords = {
			x: e.changedTouches[0].pageX, // e.touches[0].clientX + window.pageXOffset
			y: e.changedTouches[0].pageY // e.touches[0].clientY + window.pageYOffset
		};

		if (BXRL.render.hasMobileTouchMoved === true)
		{
			var
				userReaction = null,
				reactionNode = BXRL.render.reactionsPopupMobileGetHoverNode(coords.x, coords.y);

			if (
				reactionNode
				&& (userReaction = reactionNode.getAttribute('data-reaction'))
			)
			{
				RatingLike.ClickVote(
					BXRL.render.reactionsPopupLikeId,
					userReaction,
					true
				);
			}
			BXRL.render.reactionsPopupMobileHide();
		}
		else // show reactions popup and handle clicks
		{
			window.addEventListener("touchend", BXRL.render.reactionsPopupMobileHide);
		}

		window.removeEventListener("touchend", BXRL.render.reactionsPopupMobileTouchEndHandler);
		window.removeEventListener("touchmove", BXRL.render.reactionsPopupMobileTouchMoveHandler);

		BXRL.render.touchStartPosition = null;
		e.preventDefault();
	},

	reactionsPopupMobileHide: function(e)
	{
		window.removeEventListener("touchend", BXRL.render.reactionsPopupMobileHide);
		if (BXRL.render.reactionsPopupLikeId)
		{
			BXRL.render.hideReactionsPopup({
				likeId: BXRL.render.reactionsPopupLikeId
			});
			if (e)
			{
				e.preventDefault();
			}
		}
	},

	reactionsPopupMobileCheckTouchMove: function()
	{
		if (BXRL.render.touchStartPosition === null)
		{
			return true;
		}
		else
		{
			if (
				Math.abs(BXRL.render.touchCurrentPosition.x - BXRL.render.touchStartPosition.x) > 5
				|| Math.abs(BXRL.render.touchCurrentPosition.y - BXRL.render.touchStartPosition.y) > 5
			)
			{
				return false;
			}
		}

		return true;
	},

	reactionsPopupMobileTouchMoveHandler: function(e)
	{
		var coords = {
			x: e.touches[0].pageX, // e.touches[0].clientX + window.pageXOffset
			y: e.touches[0].pageY // e.touches[0].clientY + window.pageYOffset
		};


		BXRL.render.touchCurrentPosition = {
			x: coords.x,
			y: coords.y
		};

		if (BXRL.render.touchStartPosition === null)
		{
			BXRL.render.touchStartPosition = {
				x: coords.x,
				y: coords.y
			};
		}
		else
		{
			if (BXRL.render.hasMobileTouchMoved !== true)
			{
				BXRL.render.hasMobileTouchMoved = !BXRL.render.reactionsPopupMobileCheckTouchMove();
			}
		}

		if (BXRL.render.hasMobileTouchMoved === true)
		{
			var reactionNode = BXRL.render.reactionsPopupMobileGetHoverNode(coords.x, coords.y);
			if (reactionNode)
			{
				if (
					BXRL.render.currentReactionNodeHover
					&& BXRL.render.currentReactionNodeHover != reactionNode
				)
				{
					BXRL.render.reactionsPopupMobileRemoveHover(BXRL.render.currentReactionNodeHover);
				}
				BXRL.render.reactionsPopupMobileAddHover(reactionNode);
				BXRL.render.currentReactionNodeHover = reactionNode;
			}
			else if (BXRL.render.currentReactionNodeHover)
			{
				BXRL.render.reactionsPopupMobileRemoveHover(BXRL.render.currentReactionNodeHover);
			}
		}
		else
		{
			if (BXRL.render.currentReactionNodeHover)
			{
				BXRL.render.reactionsPopupMobileRemoveHover(BXRL.render.currentReactionNodeHover);
			}
		}
	},

	reactionsPopupMobileGetHoverNode: function(x, y)
	{
		var
			reactionNode = null,
			userReaction = null,
			result = null;

		if (
			(
				(reactionNode = document.elementFromPoint(x, (y + BXRL.render.touchMoveDeltaY - BXRL.render.touchScrollTop)))
				&& (userReaction = reactionNode.getAttribute('data-reaction'))
				&& BX.type.isNotEmptyString(userReaction)
			) // icon above/below a finger
			|| (
				(reactionNode = document.elementFromPoint(x, (y - BXRL.render.touchScrollTop)))
				&& (userReaction = reactionNode.getAttribute('data-reaction'))
				&& BX.type.isNotEmptyString(userReaction)
			) // icon is under a finger
		)
		{
			result = reactionNode;
		}

		return result;
	},

	reactionsPopupMobileAddHover: function(reactionNode)
	{
		if (reactionNode)
		{
			reactionNode.classList.add('feed-post-emoji-icon-item-hover');
		}
	},

	reactionsPopupMobileRemoveHover: function(reactionNode)
	{
		if (reactionNode)
		{
			reactionNode.classList.remove('feed-post-emoji-icon-item-hover');
		}
	},

	reactionsPopupMobileDisableScroll: function()
	{
		document.addEventListener('touchmove', BXRL.render.touchMoveScrollListener, { passive: false });
		BX.onCustomEvent("onPullDownDisable", {});

		if (BXRL.render.mobileOverlay === null)
		{
			BXRL.render.mobileOverlay = BX.create('DIV', {
				props: {
					className: 'feed-post-emoji-popup-mobile-overlay'
				}
			});
			setTimeout(function() {
				if (BXRL.render.mobileOverlay !== null)
				{
					BX.append(BXRL.render.mobileOverlay, document.body);
				}
			}, 1000); // to avoid blink
		}
	},

	reactionsPopupMobileEnableScroll: function()
	{
		document.removeEventListener('touchmove', BXRL.render.touchMoveScrollListener, { passive: false });
		BX.onCustomEvent("onPullDownEnable", {});

		if (BXRL.render.mobileOverlay !== null)
		{
			BX.cleanNode(BXRL.render.mobileOverlay, true);
			BXRL.render.mobileOverlay = null;
		}
	},

	touchMoveScrollListener: function(e) {
		e.preventDefault();
	},

	hideReactionsPopup: function(params)
	{
		var likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false);

		if (BXRL.render.reactionsPopup)
		{
			if (BXRL.manager.mobile)
			{
				BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final');
				BXRL.render.reactionsPopup.classList.add('feed-post-emoji-popup-invisible-final-mobile');
				BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active');
				BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final');
				BXRL.render.reactionsPopup.classList.remove('feed-post-emoji-popup-active-final-item');
				BXRL.render.reactionsPopupMobileEnableScroll();
			}
			else
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
			}

			BXRL.render.reactionsPopupLikeId = null;

			if (likeId)
			{
				BXRL[likeId].box.classList.remove('feed-post-emoji-control-active');
			}
		}

		BXRL.render.reactionsPopupMobileRemoveHover(BXRL.render.currentReactionNodeHover);

		if (likeId)
		{
			BXRL.render.bindReactionsPopup({
				likeId: likeId
			});
		}
	},

	bindReactionsPopup: function(params) {

		if (BXRL.manager.mobile)
		{
			return false;
		}

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
				if (BXRL.manager.mobile)
				{
					app.exec("callVibration");
				}

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
				angry: 5,
				facepalm: 6
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

		var usersNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');
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

		var contentNodes = usersNode.querySelectorAll('.bx-ilike-popup-content');

		for(i = 0; i < contentNodes.length; i++)
		{
			contentNodes[i].classList.add('bx-ilike-popup-content-invisible');
		}

		var reactionUsersNode = usersNode.querySelector('.bx-ilike-popup-content-' + this.popupCurrentReaction);
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

		var waitNode = rating.popupContent.querySelector('.bx-ilike-wait');
		if (waitNode)
		{
			BX.cleanNode(waitNode, true);
		}
		var tabsNodeOld = rating.popupContent.querySelector('.bx-ilike-popup-head');
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

		var contentContainerNode = rating.popupContent.querySelector('.bx-ilike-popup-content-container');
		if (!contentContainerNode)
		{
			return false;
		}

		var reactionUsersNode = contentContainerNode.querySelector('.bx-ilike-popup-content-' + reaction);
		if (reactionUsersNode)
		{
			this.popupCurrentReaction = (BX.type.isNotEmptyString(reaction) ? reaction : 'all');

			var tabNodes = rating.popupContent.querySelectorAll('.bx-ilike-popup-head-item');
			for(i = 0; i < tabNodes.length; i++)
			{
				tabNodes[i].classList.remove('bx-ilike-popup-head-item-current');
				reactionTabNode = tabNodes[i].querySelector('.feed-post-emoji-icon-' + reaction);
				if (reactionTabNode)
				{
					tabNodes[i].classList.add('bx-ilike-popup-head-item-current');
				}
			}

			var contentNodes = contentContainerNode.querySelectorAll('.bx-ilike-popup-content');
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

		if (!BX.type.isNotEmptyString(reaction))
		{
			reaction = '';
		}

		RatingLike.onResultClick({
			likeId: likeId,
			event: e,
			reaction: reaction
		});

		e.stopPropagation();
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
	},

	openMobileReactionsPage: function (params) {
		BXMobileApp.PageManager.loadPageBlank({
			url: BX.message('SITE_DIR') + 'mobile/like/result.php',
			title: BX.message("RATING_LIKE_RESULTS"),
			backdrop:{
				mediumPositionPercent:65
			},
			cache: true,
			data: {
				entityTypeId: params.entityTypeId,
				entityId: params.entityId
			}
		});
	},

	onRatingLike: function(eventData)
	{
		for(var i in BXRL)
		{
			if (!BXRL.hasOwnProperty(i))
			{
				continue;
			}

			if (
				BXRL[i].entityTypeId == eventData.entityTypeId
				&& BXRL[i].entityId == eventData.entityId
			)
			{
				var voteAction = (BX.type.isNotEmptyString(eventData.voteAction) ? eventData.voteAction.toUpperCase() : 'ADD');
				voteAction = (voteAction == 'PLUS' ? 'ADD' : voteAction);

				if (
					eventData.userId == BX.message('USER_ID')
					&& BXRL[i].button
				)
				{
					if (voteAction == 'CANCEL')
					{
						BX.removeClass(BXRL[i].button, 'bx-you-like-button');
					}
					else
					{
						BX.addClass(BXRL[i].button, 'bx-you-like-button');
					}
				}

				RatingLike.Draw(i, {
					TYPE: voteAction,
					USER_ID: eventData.userId,
					ENTITY_TYPE_ID: eventData.entityTypeId,
					ENTITY_ID: eventData.entityId,
					USER_DATA: eventData.userData,
					REACTION: eventData.voteReaction,
					REACTION_OLD: eventData.voteReactionOld,
					TOTAL_POSITIVE_VOTES: eventData.itemsAll
				});
			}
		}

	},

	onMobileCommentsGet: function()
	{
		var ratingEmojiSelectorPopup = document.querySelector('.feed-post-emoji-popup-container');
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
};

BXRL.manager = {

	mobile: false,
	inited: false,
	displayHeight: 0,
	startScrollTop: 0,
	entityList: [],
	ratingNodeList: {},
	delayedList: {},

	init: function(params)
	{
		if (typeof params == 'undefined')
		{
			params = {};
		}

		if (this.inited)
		{
			return;
		}

		this.mobile = (typeof params.mobile != 'undefined' && !!params.mobile);

		this.inited = true;

		this.setDisplayHeight();

		if (!this.mobile)
		{
			window.addEventListener("scroll",  BX.throttle(function() {
				this.getInViewScope();
			}, 80, this), { passive: true });

			window.addEventListener("resize",  BX.delegate(this.setDisplayHeight, this));
		}

		BX.addCustomEvent('onBeforeMobileLivefeedRefresh', BXRL.render.reactionsPopupMobileHide);
		BX.addCustomEvent('BX.MobileLF:onCommentsGet', BXRL.render.onMobileCommentsGet);

		if (this.mobile)
		{
			// new one
			BXMobileApp.addCustomEvent("onRatingLike", BXRL.render.onRatingLike);
		}
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
