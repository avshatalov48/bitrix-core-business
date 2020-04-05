if (!BXRL)
{
	var BXRL = {};
	var BXRLW = null;
	var lastVoteRepo = {};
	var lastReactionRepo = {};
	var BXRLParams = {
		pathToUserProfile: null
	};
}

RatingLike = function(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax)
{
	var key = entityTypeId+'_'+entityId;

	this.enabled = true;
	this.likeId = likeId;
	this.entityTypeId = entityTypeId;
	this.entityId = entityId;
	this.available = (available == 'Y');
	this.userId = userId;
	this.localize = localize;
	this.template = template;
	this.pathToUserProfile = pathToUserProfile;
	this.pathToAjax = (
		BX.type.isNotEmptyString(pathToAjax)
			? pathToAjax
			: '/bitrix/components/bitrix/rating.vote/vote.ajax.php'
	);

	this.box = BX('bx-ilike-button-'+likeId);
	if (this.box === null)
	{
		this.enabled = false;
		return false;
	}

	this.button = BX.findChild(this.box, { className: 'bx-ilike-left-wrap' }, true, false);
	this.buttonText = BX.findChild(this.button, { className: 'bx-ilike-text' }, true, false);
	this.count = BX.findChild(this.box,  { tagName: 'span', className: 'bx-ilike-right-wrap' }, true, false);
	if (!this.count)
	{
		this.count = BX('bx-ilike-count-' + likeId);
	}
	this.countText = BX.findChild(this.count, { className: 'bx-ilike-right' }, true, false);
	this.topPanelContainer = BX('feed-post-emoji-top-panel-container-' + likeId);
	this.topPanel = BX('feed-post-emoji-top-panel-' + likeId);
	this.topUsersText = BX('bx-ilike-top-users-' + likeId);
	this.topUsersDataNode = BX('bx-ilike-top-users-data-' + likeId);
	this.userReactionNode = BX('bx-ilike-user-reaction-' + likeId);
	this.reactionsNode = BX('feed-post-emoji-icons-' + likeId);
	this.popup = null;
	this.popupId = null;
	this.popupTimeoutIdShow = null;
	this.popupTimeoutIdList = null;
	this.popupContent = BX.findChild(BX('bx-ilike-popup-cont-' + likeId), {tagName:'span', className:'bx-ilike-popup'}, true, false);
	this.popupContentPage = 1;
	this.popupTimeout = false;
	this.likeTimeout = false;
	this.mouseOverHandler = null;
	this.version = (BXRL.render && this.topPanel ? 2 : 1);
	this.mouseInShowPopupNode = {};

	if (typeof lastVoteRepo[key] != 'undefined')
	{
		this.lastVote = lastVoteRepo[key];
		var ratingNode = (template == 'standart' ? this.button: this.count);
		if (this.lastVote == 'plus')
		{
			BX.addClass(ratingNode, 'bx-you-like');
		}
		else
		{
			BX.removeClass(ratingNode, 'bx-you-like');
		}
	}
	else
	{
		this.lastVote = BX.hasClass(template == 'standart'? this.button: this.count, 'bx-you-like')? 'plus': 'cancel';
		lastVoteRepo[key] = this.lastVote;
	}

	if (typeof lastReactionRepo[key] != 'undefined')
	{
		this.lastReaction = lastReactionRepo[key];
		this.count.setAttribute('data-myreaction', this.lastReaction);
	}
	else
	{
		var lastReaction = this.count.getAttribute('data-myreaction');
		this.lastReaction = (BX.type.isNotEmptyString(lastReaction) ? lastReaction : 'like');
		lastReactionRepo[key] = this.lastReaction;
	}

	if (
		this.topPanelContainer
		&& typeof BXRL.manager != 'undefined'
	)
	{
		BXRL.manager.addEntity(key, this);
	}
};

RatingLike.LiveUpdate = function(params)
{
	if (params.USER_ID == BX.message('USER_ID'))
	{
		return false;
	}

	for(var i in BXRL)
	{
		if (!BXRL.hasOwnProperty(i))
		{
			continue;
		}

		if (
			BXRL[i].entityTypeId == params.ENTITY_TYPE_ID
			&& BXRL[i].entityId == params.ENTITY_ID
		)
		{
			var element = BXRL[i];
			element.countText.innerHTML = parseInt(params.TOTAL_POSITIVE_VOTES);

			if (
				typeof params.TYPE != 'undefined'
				&& typeof params.USER_ID != 'undefined'
				&& parseInt(params.USER_ID) > 0
				&& typeof params.USER_DATA != 'undefined'
				&& typeof params.USER_DATA.WEIGHT != 'undefined'
			)
			{
				var userWeight = parseFloat(params.USER_DATA.WEIGHT);

				var usersData = (
					BXRL[i].topUsersDataNode
						? JSON.parse(BXRL[i].topUsersDataNode.getAttribute('data-users'))
						: false
				);

				if (
					params.TYPE != 'CHANGE'
					&& BX.type.isPlainObject(usersData)
				)
				{
					var recalcNeeded = (usersData.TOP.length >= 2 ? false : true);

					for(var k in usersData.TOP)
					{
						if (!usersData.TOP.hasOwnProperty(k))
						{
							continue;
						}

						if (
							(
								params.TYPE == 'ADD'
								&& userWeight > usersData.TOP[k].WEIGHT
							)
							|| (
								params.TYPE == 'CANCEL'
								&& params.USER_ID == usersData.TOP[k].ID
							)
						)
						{
							recalcNeeded = true;
						}
					}

					if (recalcNeeded)
					{
						if (params.TYPE == 'ADD')
						{
							usersData.TOP.push({
								ID: parseInt(params.USER_ID),
								NAME_FORMATTED: params.USER_DATA.NAME_FORMATTED,
								WEIGHT: parseFloat(params.USER_DATA.WEIGHT)
							});
						}
						else if (params.TYPE == 'CANCEL')
						{
							usersData.TOP = usersData.TOP.filter(function(a) {
								return a.ID != params.USER_ID
							});
						}

						usersData.TOP.sort(function(a, b) {
							if (a.WEIGHT == b.WEIGHT) { return 0; } return (a.WEIGHT > b.WEIGHT) ? -1 : 1;
						});

						if (
							usersData.TOP.length > 2
							&& params.TYPE == 'ADD'
						)
						{
							usersData.TOP.pop();
							usersData.MORE++;
						}
					}
					else
					{
						if (params.TYPE == 'ADD')
						{
							usersData.MORE = (
								typeof usersData.MORE != 'undefined'
									? parseInt(usersData.MORE) + 1
									: 1
							);
						}
						else if (params.TYPE == 'CANCEL')
						{
							usersData.MORE = (
								typeof usersData.MORE != 'undefined'
								&& parseInt(usersData.MORE) > 0
									? parseInt(usersData.MORE) - 1
									: 0
							);
						}
					}

					BXRL[i].topUsersDataNode.setAttribute('data-users', JSON.stringify(usersData));

					if (BXRL[i].topUsersText)
					{
						BXRL[i].topUsersText.innerHTML = BXRL.render.getTopUsersText({
							you: BX.hasClass(BXRL[i].count, 'bx-you-like'),
							top: usersData.TOP,
							more: usersData.MORE
						});
					}
				}

				if (
					BX.type.isNotEmptyString(params.REACTION)
					&& BX.type.isNotEmptyString(params.REACTION_OLD)
					&& params.TYPE == 'CHANGE'
				)
				{
					BXRL.render.setReaction({
						likeId: i,
						rating: BXRL[i],
						action: 'change',
						userReaction: params.REACTION,
						userReactionOld: params.REACTION_OLD,
						totalCount: params.TOTAL_POSITIVE_VOTES,
						userId: params.USER_ID
					});
				}

				else if (
					BX.type.isNotEmptyString(params.REACTION)
					&& BX.util.in_array(params.TYPE, ['ADD', 'CANCEL'])
				)
				{
					BXRL.render.setReaction({
						likeId: i,
						rating: BXRL[i],
						userReaction: params.REACTION,
						action: (params.TYPE == 'ADD' ? 'add' : 'cancel'),
						totalCount: params.TOTAL_POSITIVE_VOTES,
						userId: params.USER_ID
					});
				}
			}

			if (BXRL[i].topPanel)
			{
				BXRL[i].topPanel.setAttribute('data-popup', 'N');
			}

			if (!BXRL[i].userReactionNode)
			{
				element.count.insertBefore(
					BX.create("span", { props : { className : "bx-ilike-plus-one" }, style: {width: (element.countText.clientWidth-8)+'px', height: (element.countText.clientHeight-8)+'px'}, html: (params.TYPE == 'ADD'? '+1': '-1')})
					, element.count.firstChild);
			}

			if (element.popup)
			{
				element.popup.close();
				element.popupContentPage = 1;
			}
		}
	}

	if (typeof BXRL.manager != 'undefined')
	{
		BXRL.manager.live(params);
	}
};

RatingLike.Set = function(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax)
{
	if (template === undefined)
		template = 'standart';

	if (BXRLParams.pathToUserProfile)
	{
		pathToUserProfile = BXRLParams.pathToUserProfile;
	}

	if (!BXRL[likeId] || BXRL[likeId].tryToSet <= 5)
	{
		var tryToSend = BXRL[likeId] && BXRL[likeId].tryToSet? BXRL[likeId].tryToSet: 1;
		BXRL[likeId] = new RatingLike(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax);
		if (BXRL[likeId].enabled)
		{
			RatingLike.Init(likeId);
		}
		else
		{
			setTimeout(function(){
				BXRL[likeId].tryToSet = tryToSend+1;
				RatingLike.Set(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax);
			}, 500);
		}
	}
};

RatingLike.ClickVote = function(likeId, userReaction, forceAdd)
{
	var
		cont = null,
		likeNode = null,
		likeThumbNode = null;

	if (typeof userReaction == 'undefined')
	{
		userReaction = 'like';
	}

	if (
		BXRL[likeId].version == 2
		&& BXRL[likeId].userReactionNode
	)
	{
		BXRL.render.hideReactionsPopup({
			likeId: likeId
		});
		BXRL.render.blockReactionsPopup();
		BX.unbind(document, 'mousemove', BXRL.render.reactionsPopupMouseOutHandler);
	}

	clearTimeout(BXRL[likeId].likeTimeout);

	var active = (
		BX.hasClass(
			(BXRL[likeId].template == 'standart' ? this : BXRL[likeId].count),
			'bx-you-like'
		)
	);

	forceAdd = !!forceAdd;
	var
		change = false,
		userReactionOld = false;

	if (active && !forceAdd)
	{
		userReaction = (
			BXRL[likeId].version == 2
				? BXRL.render.getUserReaction({
					userReactionNode: BXRL[likeId].userReactionNode
				})
				: false
		);

		BXRL[likeId].buttonText.innerHTML = BXRL[likeId].localize['LIKE_N'];
		BXRL[likeId].countText.innerHTML = parseInt(BXRL[likeId].countText.innerHTML)-1;
		BX.removeClass(BXRL[likeId].template == 'standart'? this: BXRL[likeId].count, 'bx-you-like');
		BX.removeClass(BXRL[likeId].button, 'bx-you-like-button');

		BXRL[likeId].likeTimeout = setTimeout(function() {
			if (BXRL[likeId].lastVote != 'cancel')
			{
				RatingLike.Vote(likeId, 'cancel', userReaction);
			}
		}, 1000);
	}
	else if (active && forceAdd)
	{
		change = true;
		userReactionOld = (
			BXRL[likeId].version == 2
				? BXRL.render.getUserReaction({
					userReactionNode: BXRL[likeId].userReactionNode
				})
				: false
		);

		if (userReaction != userReactionOld)
		{
			BXRL[likeId].likeTimeout = setTimeout(function(){
				RatingLike.Vote(likeId, 'change', userReaction, userReactionOld);
			}, 1000);
		}
	}
	else if (!active)
	{
		BXRL[likeId].buttonText.innerHTML = BXRL[likeId].localize['LIKE_Y'];
		BXRL[likeId].countText.innerHTML = parseInt(BXRL[likeId].countText.innerHTML) + 1;
		BX.addClass(BXRL[likeId].template == 'standart'? this: BXRL[likeId].count, 'bx-you-like');
		BX.addClass(BXRL[likeId].button, 'bx-you-like-button');

		BXRL[likeId].likeTimeout = setTimeout(function(){
			if (BXRL[likeId].lastVote != 'plus')
			{
				RatingLike.Vote(likeId, 'plus', userReaction);
			}
			else if (userReaction != BXRL[likeId].lastReaction) // http://jabber.bx/view.php?id=99339
			{
				RatingLike.Vote(likeId, 'change', userReaction, BXRL[likeId].lastReaction);
			}
		}, 1000);
	}

	if (BXRL[likeId].version == 2)
	{
		if (change)
		{
			BXRL.render.setReaction({
				likeId: likeId,
				rating: BXRL[likeId],
				action: 'change',
				userReaction: userReaction,
				userReactionOld: userReactionOld,
				totalCount: parseInt(BXRL[likeId].countText.innerHTML)
			});
		}
		else
		{
			BXRL.render.setReaction({
				likeId: likeId,
				rating: BXRL[likeId],
				action: (active ? 'cancel' : 'add'),
				userReaction: userReaction,
				totalCount: parseInt(BXRL[likeId].countText.innerHTML)
			});
		}
	}

	if (
		!change
		&& BXRL[likeId].version == 2
	)
	{
		var dataUsers = (
			BXRL[likeId].topUsersDataNode
				? JSON.parse(BXRL[likeId].topUsersDataNode.getAttribute('data-users'))
				: false
		);

		if (dataUsers)
		{
			BXRL[likeId].topUsersText.innerHTML = BXRL.render.getTopUsersText({
				you: !active,
				top: dataUsers.TOP,
				more: dataUsers.MORE
			});
		}
	}

	if (
		BXRL[likeId].template == 'light'
		&& !BXRL[likeId].userReactionNode
	)
	{
		cont = BXRL[likeId].box;
		likeNode = cont.cloneNode(true);
		likeNode.id = 'like_anim'; // to not dublicate original id

		var type = 'normal';
		if (BX.findParent(cont, { 'className': 'feed-com-informers-bottom' }))
		{
			type = 'comment';
		}
		else if (BX.findParent(cont, { 'className': 'feed-post-informers' }))
		{
			type = 'post';
		}

		BX.removeClass(likeNode, 'bx-ilike-button-hover');
		BX.addClass(likeNode, 'bx-like-anim');

		BX.adjust(cont.parentNode, { style: { position: 'relative' } });

		BX.adjust(likeNode, {
			style: {
				position: 'absolute',
				whiteSpace: 'nowrap',
				top: (type == 'post' ? '1px' : (type == 'comment' ? '0' : ''))
			}
		});

		BX.adjust(cont, { style: { visibility: 'hidden' } });
		BX.prepend(likeNode, cont.parentNode);

		new BX.easing({
			duration: 140,
			start: { scale: 100 },
			finish: { scale: (type == 'comment' ? 110 : 115) },
			transition : BX.easing.transitions.quad,
			step: function(state) {
				likeNode.style.transform = "scale(" + state.scale / 100 + ")";
			},
			complete: function() {
				likeThumbNode = BX.create('SPAN', {
					props: {
						className: (active ? 'bx-ilike-icon' : 'bx-ilike-icon bx-ilike-icon-orange')
					}
				});

				BX.adjust(likeThumbNode, {
					style: {
						position: 'absolute',
						whiteSpace: 'nowrap'
					}
				});

				BX.prepend(likeThumbNode, cont.parentNode);

				new BX.easing({
					duration: 140,
					start: { scale: (type == 'comment' ? 110 : 115) },
					finish: { scale: 100 },
					transition : BX.easing.transitions.quad,
					step: function(state) {
						likeNode.style.transform = "scale(" + state.scale / 100 + ")";
					},
					complete: function() {
					}
				}).animate();

				var propsStart = { opacity: 100, scale: (type == 'comment' ? 110 : 115), top: 0 };
				var propsFinish = { opacity: 0, scale: 200, top: (type == 'comment' ? -3 : -2) };

				if (type != 'comment')
				{
					propsStart.left = -5;
					propsFinish.left = -13;
				}

				new BX.easing({
					duration: 200,
					start: propsStart,
					finish: propsFinish,
					transition : BX.easing.transitions.linear,
					step: function(state) {
						likeThumbNode.style.transform = "scale(" + state.scale / 100 + ")";
						likeThumbNode.style.opacity = state.opacity / 100;
						if (type != 'comment')
						{
							likeThumbNode.style.left = state.left + 'px';
						}
						likeThumbNode.style.top = state.top + 'px';
					},
					complete: function() {
						likeNode.parentNode.removeChild(likeNode);
						likeThumbNode.parentNode.removeChild(likeThumbNode);

						BX.adjust(cont.parentNode, { style: { position: 'static' } });
						BX.adjust(cont, { style: { visibility: 'visible' } });
					}
				}).animate();

			}
		}).animate();
	}

	BX.removeClass(this.box, 'bx-ilike-button-hover');
};

RatingLike.Init = function(likeId)
{
	// like/unlike button
	if (BXRL[likeId].available)
	{
		BX.bind(
			(
				BXRL[likeId].template == 'standart'
					? BXRL[likeId].button
					: BXRL[likeId].buttonText
			),
			'click',
			BX.delegate(function(e) {
				RatingLike.ClickVote(likeId);
				if (BXRL[likeId].version == 2)
				{
					BXRL.render.afterClick({
						likeId: likeId
					});
				}
				e.preventDefault();
			}, this)
		);

		// Hover/unHover like-button
		BX.bind(BXRL[likeId].box, 'mouseover', function() {BX.addClass(this, 'bx-ilike-button-hover')});
		BX.bind(BXRL[likeId].box, 'mouseout', function() {BX.removeClass(this, 'bx-ilike-button-hover')});

	}
	else
	{
		if (BXRL[likeId].buttonText != undefined)
		{
			BXRL[likeId].buttonText.innerHTML = BXRL[likeId].localize['LIKE_D'];
		}
	}
	// get like-user-list

	var clickShowPopupNode = (
		BXRL[likeId].topUsersText
			? BXRL[likeId].topUsersText
			: BXRL[likeId].count
	);

	BX.bind(clickShowPopupNode, 'mouseenter', function(e)
	{
		RatingLike.onResultMouseEnter({
			likeId: likeId,
			event: e,
			nodeId: e.currentTarget.id
		});
	});

	BX.bind(clickShowPopupNode, 'mouseleave', BX.proxy(function()
	{
		RatingLike.onResultMouseLeave({
			likeId: likeId
		});
	}, { likeId: likeId }));

	BX.bind(clickShowPopupNode, 'click' , function(e)
	{
		RatingLike.onResultClick({
			likeId: likeId,
			event: e,
			nodeId: e.currentTarget.id
		});
	});

	if (
		BXRL[likeId].version == 2
		&& BXRL[likeId].available
		&& BXRL[likeId].userReactionNode
	)
	{
		BXRL.render.bindReactionsPopup({
			likeId: likeId
		});
	}

	if (typeof BXRL.manager != 'undefined')
	{
		BXRL.manager.init();
	}
};

RatingLike.onResultClick = function(params)
{
	var
		likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false),
		clickEvent = (typeof params.event != 'undefined' ? params.event : null),
		reaction = (BX.type.isNotEmptyString(params.reaction) ? params.reaction : '');

	if (BXRL[likeId].resultPopupAnimation)
	{
		return;
	}

	if (
		BXRL[likeId].popup
		&& BXRL[likeId].popup.isShown()
	)
	{
		BXRL[likeId].popup.close();
	}
	else
	{
		clearTimeout(BXRL[likeId].popupTimeoutIdList);
		clearTimeout(BXRL[likeId].popupTimeoutIdShow);

		if (
			BXRL[likeId].popupContentPage == 1
			&& (
				clickEvent.currentTarget.getAttribute('data-popup') != 'Y'
				|| BXRL[likeId].popupCurrentReaction != reaction
			)
		)
		{
			RatingLike.List(likeId, 1, reaction, true);
		}
		RatingLike.OpenWindow(
			likeId,
			(clickEvent.currentTarget == BXRL[likeId].count ? null : clickEvent),
			clickEvent.currentTarget,
			clickEvent.currentTarget.id
		);
	}
};

RatingLike.onResultMouseEnter = function(params)
{
	var
		likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false),
		mouseEnterEvent = (typeof params.event != 'undefined' ? params.event : null),
		reaction = (BX.type.isNotEmptyString(params.reaction) ? params.reaction : ''),
		nodeId = (mouseEnterEvent && BX.type.isNotEmptyString(mouseEnterEvent.currentTarget.id) ? mouseEnterEvent.currentTarget.id : '');

	BXRL[likeId].mouseInShowPopupNode[reaction] = true;

	clearTimeout(BXRL[likeId].popupTimeoutIdList);
	clearTimeout(BXRL[likeId].popupTimeoutIdShow);

	BXRL[likeId].popupTimeoutIdList = setTimeout(BX.proxy(function() {

		if (BXRLW == this.likeId)
		{
			return false;
		}

		if (
			BXRL[this.likeId].popupContentPage == 1
			&& this.target.getAttribute('data-popup') != 'Y'
		)
		{
			RatingLike.List(this.likeId, 1, this.reaction, true);
		}

		BXRL[this.likeId].popupTimeoutIdShow = setTimeout(BX.proxy(function() {

			BXRL[this._likeId].resultPopupAnimation = true;

			var _likeId = this._likeId;
			setTimeout(function() {
				BXRL[_likeId].resultPopupAnimation = false;
			}, 500);

			if (BXRL[this._likeId].mouseInShowPopupNode[this._reaction])
			{
				RatingLike.OpenWindow(
					this._likeId,
					null,
					this._target,
					this._nodeId
				);
			}
		}, {
			_likeId: this.likeId,
			_reaction: this.reaction,
			_target: this.target,
			_nodeId: this.nodeId
		}), 100);
	}, {
		likeId: likeId,
		target: mouseEnterEvent.currentTarget,
		reaction: reaction,
		nodeId: nodeId
	}), 300);
};

RatingLike.onResultMouseLeave = function(params) {

	var
		likeId = (BX.type.isNotEmptyString(params.likeId) ? params.likeId : false),
		reaction = (BX.type.isNotEmptyString(params.reaction) ? params.reaction : '');

	BXRL[likeId].mouseInShowPopupNode[reaction] = false;
	BXRL[likeId].resultPopupAnimation = false;
};

RatingLike.OpenWindow = function(likeId, clickEvent, target, targetId)
{
	if (parseInt(BXRL[likeId].countText.innerHTML) == 0)
	{
		return;
	}

	var bindNode = (
		BXRL[likeId].template == 'standart'
			? BXRL[likeId].count
			: (
				BXRL[likeId].version == 2
					? (
						BX(target)
						? BX(target)
						: (BX.type.isNotEmptyString(targetId) && BX(targetId) ? BX(targetId): null)
					)
					: BXRL[likeId].box
			)
	);

	if (!BX(bindNode))
	{
		return;
	}

	if (BXRL[likeId].popup == null)
	{
		var globalZIndex = RatingLike.getGlobalIndex(BX(bindNode));
		BXRL[likeId].popup = new BX.PopupWindow('ilike-popup-'+likeId, bindNode, {
			lightShadow : true,
			offsetTop: 0,
			offsetLeft: (
				typeof clickEvent != 'undefined'
				&& clickEvent != null
				&& typeof clickEvent.offsetX != 'undefined'
					? (clickEvent.offsetX - 100)
					: (BXRL[likeId].version == 2 ? -30 : 5)
			),
			autoHide: true,
			closeByEsc: true,
			zIndexAbsolute: (globalZIndex > 1000 ? globalZIndex + 1 : 1000),
			bindOptions: { position: "top" },
			animationOptions: {
				show: {
					type: 'opacity-transform'
				},
				close: {
					type: 'opacity'
				}
			},
			events : {
				onPopupClose : function() { BXRLW = null; },
				onPopupDestroy : function() {  }
			},
			content : BX('bx-ilike-popup-cont-'+likeId),
			className: (BXRL[likeId].topPanel ? 'bx-ilike-wrap-block-react-wrap' : '')
		});

		if (!BXRL[likeId].topPanel)
		{
			BXRL[likeId].popup.setAngle({});

			BX.bind(BX('ilike-popup-'+likeId), 'mouseout' , function() {
				clearTimeout(BXRL[likeId].popupTimeout);
				BXRL[likeId].popupTimeout = setTimeout(function(){
					BXRL[likeId].popup.close();
				}, 1000);
			});

			BX.bind(BX('ilike-popup-'+likeId), 'mouseover' , function() {
				clearTimeout(BXRL[likeId].popupTimeout);
			});
		}
	}
	else
	{
		if (
			typeof clickEvent != 'undefined'
			&& clickEvent != null
			&& typeof clickEvent.offsetX != 'undefined'
		)
		{
			BXRL[likeId].popup.offsetLeft = (clickEvent.offsetX - 100);
		}

		if (BX(bindNode))
		{
			BXRL[likeId].popup.setBindElement(bindNode);
		}
	}


	if (
		BXRLW != null
		&& BXRLW != likeId
	)
	{
		BXRL[BXRLW].popup.close();
	}

	BXRLW = likeId;

	BXRL[likeId].popup.show();

	if (
		typeof BX.SidePanel != 'undefined'
		&& BX.SidePanel.Instance.getTopSlider()
	)
	{
		BX.addCustomEvent(
			BX.SidePanel.Instance.getTopSlider().getWindow(),
			"SidePanel.Slider:onClose",
			function removeOnCloseHandler()
			{
				BX.removeCustomEvent(BX.SidePanel.Instance.getTopSlider().getWindow(), "SidePanel.Slider:onClose", removeOnCloseHandler);
				if (typeof BXRL[BXRLW] != 'undefined')
				{
					BXRL[BXRLW].popup.close();
				}
			}
		);
	}

	RatingLike.AdjustWindow(likeId);
};

RatingLike.getGlobalIndex = function(element)
{
	var index = 0,
		propertyValue = "";

	do
	{
		propertyValue = BX.style(element, "z-index");
		if (propertyValue !== "auto")
		{
			index = BX.type.stringToInt(propertyValue);
		}
		element = element.offsetParent;
	}
	while (
		element && element.tagName !== "BODY"
	);

	return index;
};

RatingLike.Vote = function(likeId, voteAction, voteReaction, voteReactionOld)
{
	if (!BX.type.isNotEmptyString(voteReaction))
	{
		voteReaction = 'like';
	}

	BX.ajax({
		url: BXRL[likeId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {
			RATING_VOTE : 'Y',
			RATING_VOTE_TYPE_ID : BXRL[likeId].entityTypeId,
			RATING_VOTE_ENTITY_ID : BXRL[likeId].entityId,
			RATING_VOTE_ACTION : voteAction,
			RATING_VOTE_REACTION : voteReaction,
			sessid: BX.bitrix_sessid()
		},
		onsuccess: function(data) {
			BXRL[likeId].lastVote = data.action;
			BXRL[likeId].lastReaction = voteReaction;

			lastVoteRepo[BXRL[likeId].entityTypeId + '_' + BXRL[likeId].entityId] = data.action;
			lastReactionRepo[BXRL[likeId].entityTypeId + '_' + BXRL[likeId].entityId] = data.voteReaction;

			BXRL[likeId].countText.innerHTML = data.items_all;
			BXRL[likeId].popupContentPage = 1;

			BXRL[likeId].popupContent.innerHTML = '';
			spanTag0 = document.createElement("span");
			spanTag0.className = "bx-ilike-wait";
			BXRL[likeId].popupContent.appendChild(spanTag0);

			if (BXRL[likeId].topPanel)
			{
				BXRL[likeId].topPanel.setAttribute('data-popup', 'N');
			}

			RatingLike.AdjustWindow(likeId);

			if(
				BX('ilike-popup-'+likeId)
				&& BX('ilike-popup-'+likeId).style.display == "block"
			)
			{
				RatingLike.List(likeId, null, '', true);
			}
		},
		onfailure: function(data) {

			var dataUsers = ((BXRL[likeId].topUsersDataNode)
					? JSON.parse(BXRL[likeId].topUsersDataNode.getAttribute('data-users'))
					: false
			);

			if (BXRL[likeId].version == 2)
			{
				if (voteAction == 'change')
				{
					BXRL.render.setReaction({
						likeId: likeId,
						rating: BXRL[likeId],
						action: voteAction,
						userReaction: voteReaction,
						userReactionOld: voteReactionOld,
						totalCount: parseInt(BXRL[likeId].countText.innerHTML)
					});
				}
				else
				{
					BXRL.render.setReaction({
						likeId: likeId,
						rating: BXRL[likeId],
						action: (voteAction == 'cancel' ? 'add' : 'cancel'),
						userReaction: voteReaction,
						totalCount: (
							voteAction == 'cancel'
								? parseInt(BXRL[likeId].countText.innerHTML) + 1
								: parseInt(BXRL[likeId].countText.innerHTML) - 1
						)
					});
				}

				if (BXRL[likeId].buttonText)
				{
					if (voteAction == 'add')
					{
						BXRL[likeId].buttonText.innerHTML = BX.message('RATING_LIKE_EMOTION_LIKE_CALC');
					}
					else if (voteAction == 'change')
					{
						BXRL[likeId].buttonText.innerHTML = BX.message('RATING_LIKE_EMOTION_' + voteReactionOld.toUpperCase() + '_CALC');
					}
					else
					{
						BXRL[likeId].buttonText.innerHTML = BX.message('RATING_LIKE_EMOTION_' + voteReaction.toUpperCase() + '_CALC');
					}
				}
			}

			if (
				dataUsers
				&& voteAction != 'change'
				&& BXRL[likeId].version == 2
			)
			{
				BXRL[likeId].topUsersText.innerHTML = BXRL.render.getTopUsersText({
					you: (voteAction == 'cancel'), // negative
					top: dataUsers.TOP,
					more: dataUsers.MORE
				});
			}
		}
	});
	return false;
};

RatingLike.List = function(likeId, page, reaction, clear)
{
	if (parseInt(BXRL[likeId].countText.innerHTML) == 0)
	{
		return false;
	}

	reaction = (BX.type.isNotEmptyString(reaction) ? reaction : '');

	if (page == null)
	{
		page = (
			BXRL[likeId].version == 2
				? (typeof BXRL.render.popupPagesList[reaction] != 'undefined' ? BXRL.render.popupPagesList[reaction] : 1)
				: BXRL[likeId].popupContentPage
		);
	}

	if (
		clear
		&& page == 1
		&& BXRL[likeId].version == 2
	)
	{
		BXRL.render.clearPopupContent({
			likeId: likeId
		});
	}

	BX.ajax({
		url: BXRL[likeId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {
			RATING_VOTE_LIST : 'Y',
			RATING_VOTE_TYPE_ID : BXRL[likeId].entityTypeId,
			RATING_VOTE_ENTITY_ID : BXRL[likeId].entityId,
			RATING_VOTE_LIST_PAGE : page,
			RATING_VOTE_REACTION : (reaction == 'all' ? '' : reaction),
			PATH_TO_USER_PROFILE : BXRL[likeId].pathToUserProfile,
			sessid: BX.bitrix_sessid()
		},
		onsuccess: function(data)
		{
			BXRL[likeId].countText.innerHTML = data.items_all;

			if (parseInt(data.items_page) == 0)
			{
				return false;
			}

			if (BXRL[likeId].version == 2)
			{
				BXRL.render.buildPopupContent({
					likeId: likeId,
					reaction: reaction,
					rating: BXRL[likeId],
					page: page,
					data: data,
					clear: clear
				});
				BXRL[likeId].topPanel.setAttribute('data-popup', 'Y');
			}
			else
			{
				if (page == 1)
				{
					BXRL[likeId].popupContent.innerHTML = '';
					spanTag0 = document.createElement("span");
					spanTag0.className = "bx-ilike-bottom_scroll";
					BXRL[likeId].popupContent.appendChild(spanTag0);
				}
				BXRL[likeId].popupContentPage += 1;

				var avatarNode = null;

				for (var i = 0; i < data.items.length; i++)
				{
					if (data.items[i]['PHOTO_SRC'].length > 0)
					{
						avatarNode = BX.create("IMG", {
							attrs: {src: data.items[i]['PHOTO_SRC']},
							props: {className: "bx-ilike-popup-avatar-img"}
						});
					}
					else
					{
						avatarNode = BX.create("IMG", {
							attrs: {src: '/bitrix/images/main/blank.gif'},
							props: {className: "bx-ilike-popup-avatar-img bx-ilike-popup-avatar-img-default"}
						});
					}

					BXRL[likeId].popupContent.appendChild(
						BX.create("A", {
							attrs: {
								href: data.items[i]['URL'],
								target: '_blank'
							},
							props: {
								className: "bx-ilike-popup-img" + (!!data.items[i]['USER_TYPE'] ? " bx-ilike-popup-img-" + data.items[i]['USER_TYPE'] : "")
							},
							children: [
								BX.create("SPAN", {
									props: {
										className: "bx-ilike-popup-avatar-new"
									},
									children: [
										avatarNode,
										BX.create("SPAN", {
											props: {className: "bx-ilike-popup-avatar-status-icon"}
										})
									]
								}),
								BX.create("SPAN", {
									props: {
										className: "bx-ilike-popup-name-new"
									},
									html: data.items[i]['FULL_NAME']
								})
							]
						})
					);
				}
			}

			RatingLike.AdjustWindow(likeId);
			RatingLike.PopupScroll(likeId);
		},
		onfailure: function(data) {}
	});
	return false;
};

RatingLike.AdjustWindow = function(likeId)
{
	if (BXRL[likeId].popup != null)
	{
		BXRL[likeId].popup.bindOptions.forceBindPosition = true;
		BXRL[likeId].popup.adjustPosition();
		BXRL[likeId].popup.bindOptions.forceBindPosition = false;
	}
};

RatingLike.PopupScroll = function(likeId)
{
	var contentContainerNodeList = BX.findChildren(BXRL[likeId].popupContent, { className: 'bx-ilike-popup-content' }, true); // reactions
	if (contentContainerNodeList.length <= 0)
	{
		contentContainerNodeList = [ BXRL[likeId].popupContent ];
	}

	var contentContainerNode = null;

	for (var i = 0; i < contentContainerNodeList.length; i++)
	{
		contentContainerNode = contentContainerNodeList[i];

		BX.bind(contentContainerNode, 'scroll' , function() {
			if (this.scrollTop > (this.scrollHeight - this.offsetHeight) / 1.5)
			{
				RatingLike.List(likeId, null, (BXRL[likeId].version == 2 ? BXRL.render.popupCurrentReaction : false));
				BX.unbindAll(this);
			}
		});
	}
};

RatingLike.setParams = function(params)
{
	if (typeof params != 'undefined')
	{
		if (typeof params.pathToUserProfile != 'undefined')
		{
			BXRLParams.pathToUserProfile = params.pathToUserProfile;
		}
	}
};

