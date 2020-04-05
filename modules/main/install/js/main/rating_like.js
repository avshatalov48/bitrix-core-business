if (!BXRL)
{
	var BXRL = {};
	var BXRLW = null;
	var lastVoteRepo = {};
	var BXRLParams = {
		pathToUserProfile: null
	};
}

RatingLike = function(likeId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax)
{
	this.enabled = true;
	this.likeId = likeId;
	this.entityTypeId = entityTypeId;
	this.entityId = entityId;
	this.available = (available == 'Y');
	this.userId = userId;
	this.localize = localize;
	this.template = template;
	this.pathToUserProfile = pathToUserProfile;
	this.pathToAjax = typeof(pathToAjax) == "string"? pathToAjax: '/bitrix/components/bitrix/rating.vote/vote.ajax.php';

	this.box = BX('bx-ilike-button-'+likeId);
	if (this.box === null)
	{
		this.enabled = false;
		return false;
	}

	this.button = BX.findChild(this.box, {className:'bx-ilike-left-wrap'}, true, false);
	this.buttonText = BX.findChild(this.button, {className:'bx-ilike-text'}, true, false);
	this.count = BX.findChild(this.box,  {tagName:'span', className:'bx-ilike-right-wrap'}, true, false);
	this.countText	= BX.findChild(this.count, {tagName:'span', className:'bx-ilike-right'}, true, false);
	this.popup = null;
	this.popupId = null;
	this.popupOpenId = null;
	this.popupTimeoutId = null;
	this.popupContent = BX.findChild(BX('bx-ilike-popup-cont-'+likeId), {tagName:'span', className:'bx-ilike-popup'}, true, false);
	this.popupContentPage = 1;
	this.popupListProcess = false;
	this.popupTimeout = false;
	this.likeTimeout = false;

	if (typeof lastVoteRepo[entityTypeId+'_'+entityId] != 'undefined')
	{
		this.lastVote = lastVoteRepo[entityTypeId+'_'+entityId];
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
		lastVoteRepo[entityTypeId+'_'+entityId] = this.lastVote;
	}
};

RatingLike.LiveUpdate = function(params)
{
	if (params.USER_ID == BX.message('USER_ID'))
		return false;

	for(var i in BXRL)
	{
		if (BXRL.hasOwnProperty(i))
		{
			if (BXRL[i].entityTypeId == params.ENTITY_TYPE_ID && BXRL[i].entityId == params.ENTITY_ID)
			{
				var element = BXRL[i];
				element.countText.innerHTML = parseInt(params.TOTAL_POSITIVE_VOTES);
				element.count.insertBefore(
					BX.create("span", { props : { className : "bx-ilike-plus-one" }, style: {width: (element.countText.clientWidth-8)+'px', height: (element.countText.clientHeight-8)+'px'}, html: (params.TYPE == 'ADD'? '+1': '-1')})
					, element.count.firstChild);

				if (element.popup)
				{
					element.popup.close();
					element.popupContentPage = 1;
				}
			}
		}
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

RatingLike.Init = function(likeId)
{
	// like/unlike button
	if (BXRL[likeId].available)
	{
		BX.bind(BXRL[likeId].template == 'standart'? BXRL[likeId].button: BXRL[likeId].buttonText, 'click' ,function(e) {
			var
				cont = null,
				likeNode = null,
				likeThumbNode = null;

			clearTimeout(BXRL[likeId].likeTimeout);

			var active = BX.hasClass(BXRL[likeId].template == 'standart'? this: BXRL[likeId].count, 'bx-you-like');

			if (active)
			{
				BXRL[likeId].buttonText.innerHTML = BXRL[likeId].localize['LIKE_N'];
				BXRL[likeId].countText.innerHTML = parseInt(BXRL[likeId].countText.innerHTML)-1;
				BX.removeClass(BXRL[likeId].template == 'standart'? this: BXRL[likeId].count, 'bx-you-like');

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'cancel')
						RatingLike.Vote(likeId, 'cancel');
				}, 1000);
			}
			else
			{
				BXRL[likeId].buttonText.innerHTML = BXRL[likeId].localize['LIKE_Y'];
				BXRL[likeId].countText.innerHTML = parseInt(BXRL[likeId].countText.innerHTML)+1;
				BX.addClass(BXRL[likeId].template == 'standart'? this: BXRL[likeId].count, 'bx-you-like');

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'plus')
						RatingLike.Vote(likeId, 'plus');
				}, 1000);
			}

			if (BXRL[likeId].template == 'light')
			{
				cont = BXRL[likeId].box;
				likeNode = cont.cloneNode(true);
				likeNode.id = 'like_anim'; // to not dublicate original id

				var type = (BX.findParent(cont, { 'className': 'feed-com-informers' }) ? 'comment' : 'post');

				BX.removeClass(likeNode, 'bx-ilike-button-hover');
				BX.addClass(likeNode, 'bx-like-anim');

				BX.adjust(cont.parentNode, { style: { position: 'relative' } });

				BX.adjust(likeNode, {
					style: {
						position: 'absolute',
						whiteSpace: 'nowrap',
						top: (type == 'comment' ? -1 : 1) + 'px'
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

						new BX.easing({
							duration: 200,
							start: { opacity: 100, scale: (type == 'comment' ? 110 : 115), left: -5, top: 0 },
							finish: { opacity: 0, scale: 200, left: -13, top: (type == 'comment' ? -3 : -2) },
							transition : BX.easing.transitions.linear,
							step: function(state) {
								likeThumbNode.style.transform = "scale(" + state.scale / 100 + ")";
								likeThumbNode.style.opacity = state.opacity / 100;
								likeThumbNode.style.left = state.left + 'px';
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
			BX.PreventDefault(e);
		});
		// Hover/unHover like-button
		BX.bind(BXRL[likeId].box, 'mouseover', function() {BX.addClass(this, 'bx-ilike-button-hover')});
		BX.bind(BXRL[likeId].box, 'mouseout', function() {BX.removeClass(this, 'bx-ilike-button-hover')});

	}
	else
	{
		if (BXRL[likeId].buttonText != undefined)
			BXRL[likeId].buttonText.innerHTML	=	BXRL[likeId].localize['LIKE_D'];
	}
	// get like-user-list

	BX.bind(BXRL[likeId].count, 'mouseover' , function() {
		clearTimeout(BXRL[likeId].popupTimeoutId);
		BXRL[likeId].popupTimeoutId = setTimeout(function(){
			if (BXRLW == likeId)
				return false;
			if (BXRL[likeId].popupContentPage == 1)
				RatingLike.List(likeId, 1);
			BXRL[likeId].popupTimeoutId = setTimeout(function() {
				RatingLike.OpenWindow(likeId);
			}, 400);
		}, 400);
	});
	BX.bind(BXRL[likeId].count, 'mouseout' , function() {
		clearTimeout(BXRL[likeId].popupTimeoutId);
	});
	BX.bind(BXRL[likeId].count, 'click' , function() {
		clearTimeout(BXRL[likeId].popupTimeoutId);
		if (BXRL[likeId].popupContentPage == 1)
			RatingLike.List(likeId, 1);
		RatingLike.OpenWindow(likeId);
	});

	BX.bind(BXRL[likeId].box, 'mouseout' , function() {
		clearTimeout(BXRL[likeId].popupTimeout);
		BXRL[likeId].popupTimeout = setTimeout(function(){
			if (BXRL[likeId].popup !== null)
			{
				BXRL[likeId].popup.close();
				BXRLW = null;
			}
		}, 1000);
	});
	BX.bind(BXRL[likeId].box, 'mouseover' , function() {
		clearTimeout(BXRL[likeId].popupTimeout);
	});
};

RatingLike.OpenWindow = function(likeId)
{
	if (parseInt(BXRL[likeId].countText.innerHTML) == 0)
		return false;

	if (BXRL[likeId].popup == null)
	{
		BXRL[likeId].popup = new BX.PopupWindow('ilike-popup-'+likeId, (BXRL[likeId].template == 'standart'? BXRL[likeId].count: BXRL[likeId].box), {
			lightShadow : true,
			offsetLeft: 5,
			autoHide: true,
			closeByEsc: true,
			zIndex: 2005,
			bindOptions: {position: "top"},
			events : {
				onPopupClose : function() { BXRLW = null; },
				onPopupDestroy : function() {  }
			},
			content : BX('bx-ilike-popup-cont-'+likeId)
		});
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

	if (BXRLW != null)
		BXRL[BXRLW].popup.close();

	BXRLW = likeId;
	BXRL[likeId].popup.show();

	RatingLike.AdjustWindow(likeId);
};

RatingLike.Vote = function(likeId, voteAction)
{
	BX.ajax({
		url: BXRL[likeId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {'RATING_VOTE' : 'Y', 'RATING_VOTE_TYPE_ID' : BXRL[likeId].entityTypeId, 'RATING_VOTE_ENTITY_ID' : BXRL[likeId].entityId, 'RATING_VOTE_ACTION' : voteAction, 'sessid': BX.bitrix_sessid()},
		onsuccess: function(data)	{
			BXRL[likeId].lastVote = data.action;
			lastVoteRepo[BXRL[likeId].entityTypeId + '_' + BXRL[likeId].entityId] = data.action;
			BXRL[likeId].countText.innerHTML = data.items_all;
			BXRL[likeId].popupContentPage = 1;

			BXRL[likeId].popupContent.innerHTML = '';
			spanTag0 = document.createElement("span");
			spanTag0.className = "bx-ilike-wait";
			BXRL[likeId].popupContent.appendChild(spanTag0);
			RatingLike.AdjustWindow(likeId);

			if(BX('ilike-popup-'+likeId) && BX('ilike-popup-'+likeId).style.display == "block")
				RatingLike.List(likeId, null);
		},
		onfailure: function(data)	{}
	});
	return false;
};

RatingLike.List = function(likeId, page)
{
	if (parseInt(BXRL[likeId].countText.innerHTML) == 0)
		return false;

	if (page == null)
		page = BXRL[likeId].popupContentPage;
	BXRL[likeId].popupListProcess = true;
	BX.ajax({
		url: BXRL[likeId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {'RATING_VOTE_LIST' : 'Y', 'RATING_VOTE_TYPE_ID' : BXRL[likeId].entityTypeId, 'RATING_VOTE_ENTITY_ID' : BXRL[likeId].entityId, 'RATING_VOTE_LIST_PAGE' : page, 'PATH_TO_USER_PROFILE' : BXRL[likeId].pathToUserProfile, 'sessid': BX.bitrix_sessid()},
		onsuccess: function(data)
		{
			BXRL[likeId].countText.innerHTML = data.items_all;

			if ( parseInt(data.items_page) == 0 )
				return false;

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

			RatingLike.AdjustWindow(likeId);
			RatingLike.PopupScroll(likeId);

			BXRL[likeId].popupListProcess = false;
		},
		onfailure: function(data)	{}
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
	BX.bind(BXRL[likeId].popupContent, 'scroll' , function() {
		if (this.scrollTop > (this.scrollHeight - this.offsetHeight) / 1.5)
		{
			RatingLike.List(likeId, null);
			BX.unbindAll(this);
		}
	});
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

