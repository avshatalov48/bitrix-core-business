if (!BXRL)
{
	var BXRL = {};

	BXMobileApp.addCustomEvent("onPull-main", function(data) {
		if (data.command == 'rating_vote')
		{
			RatingLike.LiveUpdate(data.params);
		}
	});
}

RatingLike = function(likeId, entityTypeId, entityId, available)
{	
	this.enabled = true;
	this.entityTypeId = entityTypeId;
	this.entityId = entityId;
	this.available = (available == 'Y');

	this.box = BX('bx-ilike-box-' + likeId);
	if (this.box === null)
	{
		this.enabled = false;
		return false;
	}

	this.button = BX('bx-ilike-button-' + likeId);
	if (!this.button)
	{
		this.button = BX('rating_button');
	}

	this.count = BX.findChild(this.button, { tagName: 'div', className: 'post-item-inform-right' }, true, false);
	this.countText = BX.findChild(this.box, {tagName:'span', className:'post-item-inform-right-text'}, true, false);
	this.buttonCountText = BX.findChild(this.button, {tagName:'span', className:'post-item-inform-right-text'}, true, false);
	this.likeTimeout = false;
	this.lastVote = BX.hasClass(this.button, 'post-item-inform-likes-active') ? 'plus' : 'cancel';
};

RatingLike.Set = function(likeId, entityTypeId, entityId, available)
{
	BXRL[likeId] = new RatingLike(likeId, entityTypeId, entityId, available);
	if (BXRL[likeId].enabled)
	{
		RatingLike.Init(likeId);
	}
};

RatingLike.Init = function(likeId)
{
	// like/unlike button
	if (BXRL[likeId].available)
	{
		BX.unbindAll(BXRL[likeId].button);
		BX.bind(BXRL[likeId].button, 'click', function(e)
		{
			app.exec("callVibration");

			clearTimeout(BXRL[likeId].likeTimeout);
			var newValue = null;
			var action = null;

			if (BX.hasClass(BXRL[likeId].button, 'post-item-inform-likes-active'))
			{
				newValue = parseInt(BXRL[likeId].countText.innerHTML) - 1;
				action = 'cancel';

				BXRL[likeId].countText.innerHTML = newValue;
				if (BXRL[likeId].buttonCountText)
				{
					BXRL[likeId].buttonCountText.innerHTML = newValue;
				}
				BX.removeClass(BXRL[likeId].button, 'post-item-inform-likes-active');

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'cancel')
					{
						RatingLike.Vote(likeId, 'cancel');
					}
				}, 1000);
			}
			else
			{
				newValue = parseInt(BXRL[likeId].countText.innerHTML) + 1;
				action = 'plus';

				BXRL[likeId].countText.innerHTML = newValue;
				if (BXRL[likeId].buttonCountText)
				{
					BXRL[likeId].buttonCountText.innerHTML = newValue;
				}
				BX.addClass(BXRL[likeId].button, 'post-item-inform-likes-active');

				var likeNode = BX.clone(BXRL[likeId].button);
				BX.adjust(BXRL[likeId].button.parentNode, { style: { position: 'relative' } });
				BX.adjust(likeNode, { style: { position: 'absolute' } });
				BX.adjust(BXRL[likeId].button, { style: { visibility: 'hidden' } });

				BX.prepend(likeNode, BXRL[likeId].button.parentNode);

				new BX.easing({
					duration: 120,
					start: { top: 0, scale: 100 },
					finish: { top: -2, scale: 130 },
					transition : BX.easing.transitions.quad,
					step: function(state) {
						likeNode.style.transform = "scale(" + state.scale / 100 + ")";
						likeNode.style.top = state.top + 'px';
					},
					complete: function() {
						new BX.easing({
							duration: 120,
							start: { top: -2, scale: 130 },
							finish: { top: 0, scale: 100 },
							transition : BX.easing.transitions.quad,
							step: function(state) {
								likeNode.style.transform = "scale(" + state.scale / 100 + ")";
								likeNode.style.top = state.top + 'px';
							},
							complete: function() {
								likeNode.parentNode.removeChild(likeNode);
								BX.adjust(BXRL[likeId].button, { style: { visibility: 'visible' } });
								BX.adjust(BXRL[likeId].button.parentNode, { style: { position: 'static' } });
							}
						}).animate();
					}
				}).animate();

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'plus')
					{
						RatingLike.Vote(likeId, 'plus');
					}
				}, 1000);
			}

			var ratingFooter = BX('rating-footer');

			if (
				!ratingFooter
				&& typeof BXRL[likeId].button.parentNode.id != 'undefined'
			)
			{
				var arMatch = BXRL[likeId].button.parentNode.id.match(/^rating_button_([\d]+)$/i);
				if (arMatch != null)
				{
					ratingFooter = BX('rating-footer_' + arMatch[1]);
				}
			}

			if (ratingFooter)
			{
				var youNode = BX.findChild(ratingFooter, {className: 'rating-footer-you'}, true, false);
				var youAndOthersNode = BX.findChild(ratingFooter, {className: 'rating-footer-youothers'}, true, false);
				var othersNode = BX.findChild(ratingFooter, {className: 'rating-footer-others'}, true, false);

				oMSL.recalcRatingFooter({
					obYouNode: youNode,
					obYouAndOthersNode: youAndOthersNode,
					obOthersNode: othersNode,
					bSelf: true,
					voteAction: action,
					val: newValue
				});
			}

			BX.PreventDefault(e);
		});
	}
};

RatingLike.Vote = function(likeId, voteAction)
{
	var BMAjaxWrapper = new MobileAjaxWrapper;
	BMAjaxWrapper.Wrap({
		type: 'json',
		method: 'POST',
		url: BX.message('SITE_DIR') + 'mobile/ajax.php?mobile_action=like',
		data: {
			RATING_VOTE: 'Y',
			RATING_VOTE_TYPE_ID: BXRL[likeId].entityTypeId,
			RATING_VOTE_ENTITY_ID: BXRL[likeId].entityId,
			RATING_VOTE_ACTION: voteAction,
			sessid: BX.bitrix_sessid()
		},
		callback: function(data)
		{
			if (
				typeof data != 'undefined'
				&& typeof data.action != 'undefined'
				&& typeof data.items_all != 'undefined'
			)
			{
				BXRL[likeId].lastVote = data.action;
				BXRL[likeId].countText.innerHTML = data.items_all;
				if (BXRL[likeId].buttonCountText)
				{
					BXRL[likeId].buttonCountText.innerHTML = data.items_all;
				}

				var counterNode = BXRL[likeId].box.parentNode;
				var oldValue = counterNode.getAttribute('data-counter');

				if (oldValue === null)
				{
					counterNode = BX('rating_button_cont');
					if (counterNode)
					{
						oldValue = counterNode.getAttribute('data-counter');
					}
				}

				if (oldValue !== null)
				{
					oldValue = parseInt(oldValue);
					counterNode.setAttribute('data-counter', ((voteAction == 'plus') ? (oldValue + 1) : (oldValue - 1)));
				}

				if (
					typeof (oMSL) != 'undefined'
					&& typeof (oMSL.logId) != 'undefined'
					&& oMSL.logId
				)
				{
					BXMobileApp.onCustomEvent('onLogEntryRatingLike', {
						rating_id: likeId,
						voteAction: voteAction,
						logId: oMSL.logId
					}, true);
				}
			}
			else
			{
				var newValue = 0;
				if (voteAction == 'plus')
				{
					newValue = parseInt(BXRL[likeId].countText.innerHTML) - 1;
					BX.removeClass(BXRL[likeId].button, 'post-item-inform-likes-active');
				}
				else
				{
					newValue = parseInt(BXRL[likeId].countText.innerHTML) + 1;
					BX.addClass(BXRL[likeId].button, 'post-item-inform-likes-active');
				}
				BXRL[likeId].countText.innerHTML = newValue;
				if (BXRL[likeId].buttonCountText)
				{
					BXRL[likeId].buttonCountText.innerHTML = newValue;
				}
			}
		},
		callback_failure: function(data)
		{
			var newValue = 0;
			if (voteAction == 'plus')
			{
				newValue = parseInt(BXRL[likeId].countText.innerHTML) - 1;
				BX.removeClass(BXRL[likeId].button, 'post-item-inform-likes-active');
			}
			else
			{
				newValue = parseInt(BXRL[likeId].countText.innerHTML) + 1;
				BX.addClass(BXRL[likeId].button, 'post-item-inform-likes-active');
			}
			BXRL[likeId].countText.innerHTML = newValue;
			if (BXRL[likeId].buttonCountText)
			{
				BXRL[likeId].buttonCountText.innerHTML = newValue;
			}
		}
	});
	return false;
};

RatingLike.List = function(likeId)
{
	app.openTable({
		callback: function() {},
		url: (BX.message('MobileSiteDir') ? BX.message('MobileSiteDir') : '/') + 'mobile/index.php?mobile_action=get_likes&RATING_VOTE_TYPE_ID=' + BXRL[likeId].entityTypeId + '&RATING_VOTE_ENTITY_ID=' + BXRL[likeId].entityId + '&URL=' + BX.message('RVPathToUserProfile'),
		markmode: false,
		showtitle: false,
		modal: false,
		cache: false,
		outsection: false,
		cancelname: BX.message('RVListBack')
	});

	return false;
};

RatingLike.LiveUpdate = function(params)
{
	if (params.USER_ID == BX.message('USER_ID'))
	{
		return false;
	}

	for(var i in BXRL)
	{
		if (
			BXRL[i].entityTypeId == params.ENTITY_TYPE_ID
			&& BXRL[i].entityId == params.ENTITY_ID
		)
		{
			oMSL.onLogEntryRatingLike({
				ratingId: i,
				voteAction: (params.TYPE == 'ADD' ? 'plus' : 'cancel'),
				logId: 0,
				userId: params.USER_ID
			});
		}
	}

};
