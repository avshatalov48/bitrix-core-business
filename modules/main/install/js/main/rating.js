if (!BXRS)
{
	var BXRS = {};
	var BXRSW = {};
} 

Rating = function(voteId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax)
{	
	this.enabled = true;
	this.voteId = voteId;
	this.entityTypeId = entityTypeId;
	this.entityId = entityId;
	this.available = available == 'Y'? true: false;
	this.userId = userId;
	this.localize = localize;	
	this.template = template;
	this.light = template == 'light'? true: false;
	this.pathToUserProfile = pathToUserProfile;
	this.pathToAjax = typeof(pathToAjax) == "string"? pathToAjax: '/bitrix/components/bitrix/rating.vote/vote.ajax.php';
	
	this.box = template == 'light'? BX('bx-rating-'+voteId): BX('rating-vote-'+voteId);
	if (this.box === null)
	{
		this.enabled = false;
		return false;
	}

	this.keySigned = this.box.getAttribute('data-vote-key-signed') || '';

	if (!this.light)
	{
		this.buttonPlus = BX('rating-vote-'+voteId+'-plus');
		this.buttonMinus = BX('rating-vote-'+voteId+'-minus');
		this.result = BX('rating-vote-'+voteId+'-result');
	}
	else
	{
		this.buttonPlus = BX.findChild(this.box, {className:'bx-rating-yes'}, true, false);
		this.buttonMinus = BX.findChild(this.box, {className:'bx-rating-no'}, true, false);
		this.buttonPlusCount = BX.findChild(this.buttonPlus, {className:'bx-rating-yes-count'}, true, false);
		this.buttonMinusCount = BX.findChild(this.buttonMinus, {className:'bx-rating-no-count'}, true, false);
		this.buttonPlusText = BX.findChild(this.buttonPlus, {className:'bx-rating-yes-text'}, true, false);
		this.buttonMinusText = BX.findChild(this.buttonMinus, {className:'bx-rating-no-text'}, true, false);
	
		this.popupPlus = null;
		this.popupMinus = null;
		this.popupTimeoutId = null;
		this.popupContentPlus = BX.findChild(BX('bx-rating-popup-cont-'+voteId+'-plus'), {tagName:'span', className:'bx-ilike-popup'}, true, false);
		this.popupContentMinus = BX.findChild(BX('bx-rating-popup-cont-'+voteId+'-minus'), {tagName:'span', className:'bx-ilike-popup'}, true, false);
		this.popupContentPagePlus = 1;	
		this.popupContentPageMinus = 1;	
		this.popupListProcess = false;	
		this.popupTimeout = false;	
	}
	
	this.voteProcess = false;
}

Rating.Set = function(voteId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax)
{
	if (template === undefined)
		template = 'standart';

	if (!BXRS[voteId] || BXRS[voteId].tryToSet <= 5)
	{
		var tryToSend = BXRS[voteId] && BXRS[voteId].tryToSet? BXRS[voteId].tryToSet: 1;
		BXRS[voteId] = new Rating(voteId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax);
		if (BXRS[voteId].enabled)
		{
			Rating.Init(voteId);
		}
		else
		{
			setTimeout(function(){
				BXRS[voteId].tryToSet = tryToSend+1;
				Rating.Set(voteId, entityTypeId, entityId, available, userId, localize, template, pathToUserProfile, pathToAjax);
			}, 500);
		}
	}
};

Rating.Init = function(voteId)
{
	if (BXRS[voteId].available)
	{
		BX.bind(BXRS[voteId].light? BXRS[voteId].buttonPlusText: BXRS[voteId].buttonPlus, 'click' , function()	{
			if (BXRS[voteId].voteProcess)
				return false;
				
			BXRS[voteId].voteProcess = true;	
			BX.addClass(BXRS[voteId].buttonPlus, BXRS[voteId].light? 'bx-rating-load': 'rating-vote-load');
			if (BX.hasClass(BXRS[voteId].buttonPlus, BXRS[voteId].light? 'bx-rating-yes-active': 'rating-vote-plus-active'))
				Rating.Vote(voteId, 'plus', 'cancel');
			else
				Rating.Vote(voteId, 'plus', 'plus');

			return false;
		});
		
		BX.bind(BXRS[voteId].buttonPlus, 'mouseover', function() {BX.addClass(this, BXRS[voteId].light? 'bx-rating-hover': 'rating-vote-hover')});
		BX.bind(BXRS[voteId].buttonPlus, 'mouseout', function() {BX.removeClass(this, BXRS[voteId].light? 'bx-rating-hover': 'rating-vote-hover')});
		
		if (false && BXRS[voteId].light)
		{
			Rating.PopupScroll(voteId, 'plus');
			
			BX.bind(BXRS[voteId].buttonPlus, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);
				BXRS[voteId].popupTimeoutId = setTimeout(function(){
					if (BXRSW['plus'] == voteId)
						return false;
					if (BXRS[voteId].popupContentPagePlus == 1)
						Rating.List(voteId, 1, 'plus');
					BXRS[voteId].popupTimeoutId = setTimeout(function() {
						Rating.OpenWindow(voteId, 'plus');
					}, 1000);
				}, 400);
			});
			BX.bind(BXRS[voteId].buttonPlus, 'mouseout' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);
				BXRS[voteId].popupTimeout = setTimeout(function(){
					if (BXRS[voteId].popupPlus !== null)
					{
						BXRS[voteId].popupPlus.close();
						BXRSW['plus'] = null;
					}
				}, 1000);
			});
			BX.bind(BXRS[voteId].buttonPlusCount, 'click' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);	
				if (BXRS[voteId].popupContentPagePlus == 1)
					Rating.List(voteId, 1, 'plus');
				Rating.OpenWindow(voteId, 'plus');
			});
			BX.bind(BXRS[voteId].buttonPlusCount, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeout);
			});
			BX.bind(BXRS[voteId].buttonPlusText, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeout);
			});
		}
		
		BX.bind(BXRS[voteId].light? BXRS[voteId].buttonMinusText: BXRS[voteId].buttonMinus, 'click' , function() {
			if (BXRS[voteId].voteProcess)
				return false;
			
			BXRS[voteId].voteProcess = true;
			BX.addClass(BXRS[voteId].buttonMinus, BXRS[voteId].light? 'bx-rating-load': 'rating-vote-load');
			if (BX.hasClass(BXRS[voteId].buttonMinus, BXRS[voteId].light? 'bx-rating-no-active': 'rating-vote-minus-active'))
			{
				Rating.Vote(voteId, 'minus', 'cancel');
			}
			else
			{
				Rating.Vote(voteId, 'minus', 'minus');
			}
			return false;
		});
		
		BX.bind(BXRS[voteId].buttonMinus, 'mouseover', function() {BX.addClass(this, BXRS[voteId].light? 'bx-rating-hover': 'rating-vote-hover')});
		BX.bind(BXRS[voteId].buttonMinus, 'mouseout', function() {BX.removeClass(this, BXRS[voteId].light? 'bx-rating-hover': 'rating-vote-hover')});
		
		if (false && BXRS[voteId].light)
		{
			Rating.PopupScroll(voteId, 'minus');
			
			BX.bind(BXRS[voteId].buttonMinus, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);
				BXRS[voteId].popupTimeoutId = setTimeout(function(){
					if (BXRSW['minus'] == voteId)
						return false;
					if (BXRS[voteId].popupContentPageMinus == 1)
						Rating.List(voteId, 1, 'minus');
					BXRS[voteId].popupTimeoutId = setTimeout(function() {
						Rating.OpenWindow(voteId, 'minus');
					}, 1000);
				}, 400);
			});
			BX.bind(BXRS[voteId].buttonMinus, 'mouseout' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);
				BXRS[voteId].popupTimeout = setTimeout(function(){
					if (BXRS[voteId].popupMinus !== null)
					{
						BXRS[voteId].popupMinus.close();
						BXRSW['minus'] = null;
					}
				}, 1000);
			});
			BX.bind(BXRS[voteId].buttonMinusCount, 'click' , function() {
				clearTimeout(BXRS[voteId].popupTimeoutId);	
				if (BXRS[voteId].popupContentPageMinus == 1)
					Rating.List(voteId, 1, 'minus');
				Rating.OpenWindow(voteId, 'minus');
			});
			BX.bind(BXRS[voteId].buttonMinusCount, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeout);
			});
			BX.bind(BXRS[voteId].buttonMinusText, 'mouseover' , function() {
				clearTimeout(BXRS[voteId].popupTimeout);
			});
		}
	}
}

Rating.UpdateStatus = function(voteId, button, action)
{
	BXRS[voteId].buttonPlus.title = (action == 'cancel' || button == 'minus' ? BXRS[voteId].localize['PLUS']: BXRS[voteId].localize['CANCEL']); 
	BXRS[voteId].buttonMinus.title = (action == 'cancel' || button == 'plus' ? BXRS[voteId].localize['MINUS']: BXRS[voteId].localize['CANCEL']); 				
	BX.removeClass(BXRS[voteId].buttonPlus, BXRS[voteId].light? (button == 'plus'? 'bx-rating-load': 'bx-rating-yes-active'): (button == 'plus'? 'rating-vote-load': 'rating-vote-plus-active'));
	BX.removeClass(BXRS[voteId].buttonMinus, BXRS[voteId].light? (button == 'plus'? 'bx-rating-no-active': 'bx-rating-load'): (button == 'plus'? 'rating-vote-minus-active': 'rating-vote-load'));	
	if (action == 'cancel')
		BX.removeClass(button == 'plus'? BXRS[voteId].buttonPlus: BXRS[voteId].buttonMinus, BXRS[voteId].light? 'bx-rating-'+(button == 'plus'? 'yes': 'no')+'-active': 'rating-vote-'+button+'-active');
	else
		BX.addClass(button == 'plus'? BXRS[voteId].buttonPlus: BXRS[voteId].buttonMinus, BXRS[voteId].light? 'bx-rating-'+(button == 'plus'? 'yes': 'no')+'-active': 'rating-vote-'+button+'-active');
}

Rating.Vote = function(voteId, button, action)
{
	BX.ajax({
		url: BXRS[voteId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {
			RATING_VOTE: 'Y',
			RATING_RESULT: 'Y',
			RATING_VOTE_TYPE_ID: BXRS[voteId].entityTypeId,
			RATING_VOTE_ENTITY_ID: BXRS[voteId].entityId,
			RATING_VOTE_KEY_SIGNED: BXRS[voteId].keySigned,
			RATING_VOTE_ACTION: action,
			sessid: BX.bitrix_sessid(),
		},
		onsuccess: function(data)
		{
			if (BXRS[voteId].light)
			{
				BXRS[voteId].buttonPlusCount.innerHTML = data['resultPositiveVotes'];
				BXRS[voteId].buttonMinusCount.innerHTML = data['resultNegativeVotes'];			
				if (data['action'] == 'cancel')
					BX.removeClass(BXRS[voteId].box, 'bx-rating-active');
				else
					BX.addClass(BXRS[voteId].box, 'bx-rating-active');
			}
			else
			{
				BXRS[voteId].result.title = data['resultTitle'];
				BXRS[voteId].result.innerHTML = data['resultValue'];
				BX.removeClass(BXRS[voteId].result, data['resultStatus'] == 'minus' ? 'rating-vote-result-plus' : 'rating-vote-result-minus');
				BX.addClass(BXRS[voteId].result, data['resultStatus'] == 'minus' ? 'rating-vote-result-minus' : 'rating-vote-result-plus');
			}
			
			Rating.UpdateStatus(voteId, button, action);
			BXRS[voteId].voteProcess = false;
		},
		onfailure: function(data)
		{
			BX.removeClass(button == 'minus' ? BXRS[voteId].buttonMinus : BXRS[voteId].buttonPlus,  BXRS[voteId].light? 'bx-rating-load': 'rating-vote-load');
		}
	});

	return false;
}

Rating.OpenWindow = function(voteId, action)
{
	if (parseInt(action == 'plus'? BXRS[voteId].buttonPlusCount.innerHTML: BXRS[voteId].buttonMinusCount.innerHTML) == 0)
		return false;
	
	if ((action == 'plus'? BXRS[voteId].popupPlus: BXRS[voteId].popupMinus) == null)	
	{
		__popup = new BX.PopupWindow('rating-popup-'+voteId+'-'+action, action == 'plus'? BXRS[voteId].buttonPlusCount: BXRS[voteId].buttonMinusCount, { 	content:BX('bx-rating-popup-cont-'+voteId+(action == 'plus'? '-plus': '-minus')), lightShadow:true, autoHide:true, zIndex: 2500 });			
		if (action == 'plus')
			BXRS[voteId].popupPlus = __popup;
		else
			BXRS[voteId].popupMinus = __popup;
			
		BX.bind(BX('rating-popup-'+voteId+'-'+action), 'mouseout' , function() {
			clearTimeout(BXRS[voteId].popupTimeout);
			BXRS[voteId].popupTimeout = setTimeout(function(){
				if (action == 'plus')
					BXRS[voteId].popupPlus.close();
				else
					BXRS[voteId].popupMinus.close();
				BXRSW[action] = null;
			}, 1000);		
		});
		
		BX.bind(BX('rating-popup-'+voteId+'-'+action), 'mouseover' , function() {
			clearTimeout(BXRS[voteId].popupTimeout);
		});
	}
	else if(BX('rating-popup-'+voteId+'-'+action).style.display == "block")
	{
		if (action == 'plus')
			BXRS[voteId].popupPlus.close();
		else
			BXRS[voteId].popupMinus.close();
		BXRSW[action] = null;
		return false
	}
	
	if (BXRSW['plus'] != null)
	{
		BXRS[BXRSW['plus']].popupPlus.close();
		BXRSW['plus'] = null;
	}
	if (BXRSW['minus'] != null)
	{
		BXRS[BXRSW['minus']].popupMinus.close();
		BXRSW['minus'] = null;
	}
	
	if (action == 'plus')
		BXRS[voteId].popupPlus.show();
	else
		BXRS[voteId].popupMinus.show();
	
	BXRSW = voteId;
	
	if (action == 'plus')
		BXRS[voteId].popupPlus.setAngle({	position:'bottom'	});
	else
		BXRS[voteId].popupMinus.setAngle({	position:'bottom'	});
		
	Rating.AdjustWindow(voteId, action);
}

Rating.List = function(voteId, page, action)
{
	if (parseInt(action == 'plus'? BXRS[voteId].buttonPlusCount.innerHTML: BXRS[voteId].buttonMinusCount.innerHTML) == 0)
		return false;
	
	if (page == null)
		page = action == 'plus'? BXRS[voteId].popupContentPagePlus: BXRS[voteId].popupContentPageMinus;
	
	BXRS[voteId].popupListProcess = true;
	BX.ajax({
		url: BXRS[voteId].pathToAjax,
		method: 'POST',
		dataType: 'json',
		data: {'RATING_VOTE_LIST' : 'Y', 'RATING_VOTE_LIST_TYPE' : action, 'RATING_VOTE_TYPE_ID' : BXRS[voteId].entityTypeId, 'RATING_VOTE_ENTITY_ID' : BXRS[voteId].entityId, 'RATING_VOTE_LIST_PAGE' : page, 'PATH_TO_USER_PROFILE' : BXRS[voteId].pathToUserProfile, 'sessid': BX.bitrix_sessid()},
		onsuccess: function(data)
		{
			//BXRS[voteId].buttonPlusCount.innerHTML = data.items_all;	
			
			if ( parseInt(data.items_page) == 0 )
				return false;
								
			if (page == 1)
			{
				spanTag0 = document.createElement("span"); 
				spanTag0.className = "bx-ilike-bottom_scroll";
				if (action == 'plus')
				{
					BXRS[voteId].popupContentPlus.innerHTML = '';
					BXRS[voteId].popupContentPlus.appendChild(spanTag0);
				}
				else
				{
					BXRS[voteId].popupContentMinus.innerHTML = '';
					BXRS[voteId].popupContentMinus.appendChild(spanTag0);
				}
			}
			if (action == 'plus')
				BXRS[voteId].popupContentPagePlus += 1;
			else
				BXRS[voteId].popupContentPageMinus += 1;

			for (var i in data.items) {					
				aTag = document.createElement("a"); 
				aTag.className = "bx-ilike-popup-img";
				aTag.href = data.items[i]['URL'];
				aTag.target = "_blank";
					
					spanTag1 = document.createElement("span"); 
					spanTag1.className = "bx-ilike-popup-avatar";
					spanTag1.innerHTML = data.items[i]['PHOTO'];
					aTag.appendChild(spanTag1);
					
					spanTag2 = document.createElement("span"); 
					spanTag2.className = "bx-ilike-popup-name";
					spanTag2.appendChild(document.createTextNode(BX.util.htmlspecialcharsback(data.items[i]['FULL_NAME'])));
					aTag.appendChild(spanTag2);
				if (action == 'plus')	
					BXRS[voteId].popupContentPlus.appendChild(aTag);	
				else
					BXRS[voteId].popupContentMinus.appendChild(aTag);	
			}

			Rating.AdjustWindow(voteId, action);
			Rating.PopupScroll(voteId, action);
			
			BXRS[voteId].popupListProcess = false;
		},	
		onfailure: function(data)	{} 
	});
	return false;
}

Rating.AdjustWindow = function(voteId, action)
{
	children = BX.findChild(action == 'plus'? BXRS[voteId].popupContentPlus: BXRS[voteId].popupContentMinus, {className:'bx-ilike-popup-img'}, true, true);
	if (children !== null)
	{
		iOffsetHeight = BX.browser.IsIE()? 5: 0;
		for (var i in children) {	
			iOffsetHeight += children[i].offsetHeight;
		}
	}
	else 
		iOffsetHeight = BX.browser.IsIE()? 35: 30;

	if (iOffsetHeight < 121)
		if (action == 'plus')	
			BXRS[voteId].popupContentPlus.style.height = iOffsetHeight+'px';
		else
			BXRS[voteId].popupContentMinus.style.height = iOffsetHeight+'px';
	else
		if (action == 'plus')	
			BXRS[voteId].popupContentPlus.style.height = '121px';
		else
			BXRS[voteId].popupContentMinus.style.height = '121px';

	var offsetTop = 5;
		
	arScroll = BX.GetWindowScrollPos();
	if (action == 'plus')
		iLeft = BXRS[voteId].popupPlus.bindElementPos.left-10;
	else
		iLeft = BXRS[voteId].popupMinus.bindElementPos.left-10;
		
	iLeftAngle = 0;
	if (action == 'plus')
		iWindow = iLeft+BXRS[voteId].popupPlus.popupContainer.offsetWidth;
	else
		iWindow = iLeft+BXRS[voteId].popupMinus.popupContainer.offsetWidth;
	
	iBody = document.body.clientWidth + arScroll.scrollLeft;
	
	if (iWindow>iBody)
	{
		iLeft = iLeft-(iWindow-iBody);
		if (action == 'plus')
			BXRS[voteId].popupPlus.setAngle({ offset : (iWindow-iBody)+iLeftAngle });
		else
			BXRS[voteId].popupMinus.setAngle({ offset : (iWindow-iBody)+iLeftAngle });
	} 
	else if (iLeft<0)
	{
		if (action == 'plus')
			BXRS[voteId].popupPlus.setAngle({ offset : (iLeft)+iLeftAngle });
		else
			BXRS[voteId].popupMinus.setAngle({ offset : (iLeft)+iLeftAngle });
		iLeft = 0;
	}
	if (action == 'plus')
		BX.adjust(BX('rating-popup-'+voteId+'-'+action), {style: {	top: BXRS[voteId].popupPlus.bindElementPos.top-(BXRS[voteId].popupPlus.popupContainer.offsetHeight+offsetTop) + "px",		left: iLeft+"px"	}});
	else
		BX.adjust(BX('rating-popup-'+voteId+'-'+action), {style: {	top: BXRS[voteId].popupMinus.bindElementPos.top-(BXRS[voteId].popupMinus.popupContainer.offsetHeight+offsetTop) + "px",		left: iLeft+"px"	}});
}

Rating.PopupScroll = function(voteId, action)
{
	BX.bind(action == 'plus'? BXRS[voteId].popupContentPlus: BXRS[voteId].popupContentMinus, 'scroll' , function() {
		if (this.scrollTop > (this.scrollHeight - this.offsetHeight) / 1.5)
		{
			Rating.List(voteId, null, action);
			BX.unbindAll(this);
		}
	});
}

