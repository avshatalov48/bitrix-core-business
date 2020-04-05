;(function(window){
	if (!!window["JSPublicIdea"])
		return;
	window["JSPublicIdea"] = {
		/*low*/
		RequestURL: window.location.pathname,
		LoadStatusList: function()
		{
			BX.ajax({
				url: this.RequestURL + '?AJAX=IDEA&ACTION=GET_STATUS_LIST&sessid='+BX.bitrix_sessid(),
				method: 'GET',
				dataType: 'json',
				processData: true,
				onsuccess: function(data)
				{
					BX.onCustomEvent(this, 'IdeaOnLoadStatusList', [data]);
				}
			});
		},
		SetStatus: function(IdeaId, StatusId)
		{
			BX.ajax({
				url: this.RequestURL + '?AJAX=IDEA&ACTION=SET_STATUS&IDEA_ID=' + IdeaId + '&STATUS_ID=' + StatusId + '&sessid=' + BX.bitrix_sessid(),
				method: 'GET',
				dataType: 'json',
				processData: true,
				onsuccess: function(data){
					BX.onCustomEvent(this, 'IdeaOnSetStatus', [data, IdeaId, StatusId]);
				}
			});
		},

		/*ext*/
		arStatuses: {},
		arDialog:{},

		IsEmptyStatusList: function()
		{
			var res = true;
			for (var i in this.arStatuses)
			{
				if (this.arStatuses.hasOwnProperty(i))
				{
					res = false;
					break;
				}
			}
			return res;
		},

		ShowStatusDialog: function(IdeaStatusNode, IdeaId)
		{
			IdeaId = IdeaId||0;
			if(IdeaId == 0)
				return;

			var CallBack = function()
			{
				var j = 0;
				var Items = '';
				for(var i in JSPublicIdea.arStatuses)
				{
					if (JSPublicIdea.arStatuses.hasOwnProperty(i))
					{
						if(j>0)
							Items += '<div class="popup-window-hr"><i></i></div>';

						Items += ('<div class="js-idea-popup-status-item idea-action-cursor' + (j==0?' js-idea-popup-status-item-1st':'') + '" onclick="JSPublicIdea.SetStatus(' + IdeaId + ', ' + JSPublicIdea.arStatuses[i].ID + ')">'+
							'<div class="status-color-' + JSPublicIdea.arStatuses[i].XML_ID.toLowerCase() + '">' + JSPublicIdea.arStatuses[i].VALUE + '</div>' +
						'</div>');
						j++;
					}
				}

				if(!JSPublicIdea.arDialog["STATUS_DIALOG_" + IdeaId])
					JSPublicIdea.arDialog["STATUS_DIALOG_" + IdeaId] = new BX.PopupWindow(
						'status-dialog-' + IdeaId,
						IdeaStatusNode,
						{
							content: Items,
							lightShadow: true,
							autoHide: true,
							zIndex: 2500,
							offsetTop: 5,
							offsetLeft: -13
						}
					);
				JSPublicIdea.arDialog["STATUS_DIALOG_" + IdeaId].show();
			};

			if(this.IsEmptyStatusList())
			{
				BX.addCustomEvent('IdeaOnLoadStatusList', CallBack);
				this.LoadStatusList();
				return;
			}

			CallBack();
		},

		/*LS*/
		LifeSearchCache:{},
		LifeSearchQuery: '',
		LifeSearchProcessing: false,
		LifeSearchWaiter: function(activity)
		{
			var display = activity=='Y' ?'visible' :'hidden';

			var LSInputField = BX('bx-idea-waiter-big-lifesearch');
			if(LSInputField)
				LSInputField.style.visibility = display;
		},
		LifeSearch: function(SearchQuery)
		{
			this.LifeSearchQuery = SearchQuery;

			var LSCloseButton = BX('bx-idea-close-button-lifesearch');
			if(LSCloseButton)
				LSCloseButton.style.visibility = SearchQuery.length>0 ?'visible' :'hidden';

			var IdeaContentNode = BX('idea-posts-content');
			if(this.LifeSearchCache[SearchQuery] && IdeaContentNode)
			{
				IdeaContentNode.innerHTML = this.LifeSearchCache[SearchQuery];
				var innerContent = BX.findChildren(IdeaContentNode, {id:'idea-posts-content'}, false);
				if(innerContent && innerContent[0] && typeof innerContent[0].innerHTML != 'undefined')
				{
					IdeaContentNode.innerHTML = innerContent[0].innerHTML;
					this.LifeSearchWaiter('N');
				}
				return;
			}

			if(this.LifeSearchProcessing)
				return;

			this.LifeSearchProcessing = true;

			BX.ajax({
				url: this.RequestURL + '?AJAX=IDEA&ACTION=GET_LIFE_SEARCH&LIFE_SEARCH_QUERY=' + BX.util.urlencode(SearchQuery),
				method: 'GET',
				dataType: 'json',
				processData: true,
				onsuccess: function(SearchQuery){
					return function(data){
						BX.onCustomEvent(this, 'IdeaOnLifeSearch', [data, SearchQuery]);
					}
				}(SearchQuery)
			});

			this.LifeSearchWaiter('Y');
		},

		/*Subscribe*/
		SetSubscribe: function(IdeaId, self)
		{
			BX.ajax({
				url: this.RequestURL + '?AJAX=IDEA&ACTION=SUBSCRIBE&IDEA_ID=' + IdeaId + '&sessid=' + BX.bitrix_sessid(),
				method: 'GET',
				dataType: 'json',
				processData: true,
				onsuccess: function(data){
					BX.onCustomEvent(this, 'IdeaOnSetSubscribe', [data, self]);
				}
			});
		},

		DeleteSubscribe: function(IdeaId, self)
		{
			BX.ajax({
				url: this.RequestURL + '?AJAX=IDEA&ACTION=UNSUBSCRIBE&IDEA_ID=' + IdeaId + '&sessid=' + BX.bitrix_sessid(),
				method: 'GET',
				dataType: 'json',
				processData: true,
				onsuccess: function(data){
					BX.onCustomEvent(this, 'IdeaOnDeleteSubscribe', [data, self]);
				}
			});
		}
	};

	var subscribeFunction = function()
		{
			var IDNode = BX.findChildren(this, {tagName: "span"}, false);
			if(IDNode)
			{
				var IdeaId = IDNode[0].className.substr('idea-post-subscribe-'.length);
				if(IdeaId && IdeaId>0)
					JSPublicIdea.SetSubscribe(IdeaId, this)
			}
		},
		unsubscribeFunction = function()
		{
			var IDNode = BX.findChildren(this, {tagName: "span"}, false);
			if(IDNode)
			{
				var IdeaId = IDNode[0].className.substr('idea-post-subscribe-'.length);
				if(IdeaId && IdeaId>0)
					JSPublicIdea.DeleteSubscribe(IdeaId, this)
			}
		};

	//Custom Handlers
	BX.addCustomEvent('IdeaOnSetSubscribe', function(data, self) {
		if(data.SUCCESS == 'Y')
		{
			var IDNode = BX.findChildren(self, {tagName: "span"}, false);
			if(IDNode)
			{
				IDNode[0].innerHTML = data.CONTENT;
				BX.unbindAll(self);
				BX.bind(self, "click", unsubscribeFunction);
			}
		}
	});
	BX.addCustomEvent('IdeaOnDeleteSubscribe', function(data, self) {
		if(data.SUCCESS == 'Y')
		{
			var IDNode = BX.findChildren(self, {tagName: "span"}, false);
			if(IDNode)
			{
				IDNode[0].innerHTML = data.CONTENT;
				BX.unbindAll(self);
				BX.bind(self, "click", subscribeFunction);
			}
		}
	});

	BX.addCustomEvent('IdeaOnLoadStatusList', function(data){
		if(data.SUCCESS == 'Y' && !!data.STATUSES)
		{
			for(var i in data.STATUSES)
			{
				if (data.STATUSES.hasOwnProperty(i))
				{
					if(typeof(data.STATUSES[i]) != 'object')
						continue;
					JSPublicIdea.arStatuses[i] = data.STATUSES[i];
				}
			}
		}
	});

	BX.addCustomEvent('IdeaOnSetStatus', function(data, IdeaId, StatusId){
		if(data.SUCCESS == 'Y')
		{
			var StatusNode = BX('status-' + IdeaId);
			if(StatusNode)
			{
				StatusNode.innerHTML = JSPublicIdea.arStatuses[StatusId].VALUE;
				StatusNode.parentNode.className = StatusNode.parentNode.className.replace(/(status-color-)[^ ]+/ig, "$1" + JSPublicIdea.arStatuses[StatusId].XML_ID.toLowerCase());
				if(JSPublicIdea.arDialog["STATUS_DIALOG_" + IdeaId])
					JSPublicIdea.arDialog["STATUS_DIALOG_" + IdeaId].close();
			}
		}
	});

	BX.addCustomEvent('IdeaOnLifeSearch', function(data, SearchQuery){
		JSPublicIdea.LifeSearchProcessing = false;
		var IdeaContentNode = BX('idea-posts-content');
		if(data.SUCCESS == 'Y' && IdeaContentNode)
		{
			JSPublicIdea.LifeSearchCache[SearchQuery] = data.CONTENT;
			if(IdeaContentNode)
			{
				IdeaContentNode.innerHTML = JSPublicIdea.LifeSearchCache[SearchQuery];
				var innerContent = BX.findChildren(IdeaContentNode, {id:'idea-posts-content'}, false);
				if(innerContent && innerContent[0] && typeof innerContent[0].innerHTML != 'undefined')
					IdeaContentNode.innerHTML = innerContent[0].innerHTML;
			}

			if(SearchQuery != JSPublicIdea.LifeSearchQuery)
			{
				JSPublicIdea.LifeSearch(JSPublicIdea.LifeSearchQuery);
				return;
			}
		}

		JSPublicIdea.LifeSearchWaiter('N');
	});

	//Prepare life search buttons
	BX.ready(function(){
		var LSCloseButton = BX('bx-idea-close-button-lifesearch');
		var LSInputField = BX('bx-idea-lifesearch-field');
		if(LSCloseButton)
		{
			//Set NULL cache
			//var LifeSearchCacheNULL = BX('idea-posts-content');
			//if(LifeSearchCacheNULL)
			//	JSPublicIdea.LifeSearchCache[''] = LifeSearchCacheNULL.innerHTML;
			//Set Start Search Event
			BX.bind(LSInputField, 'keyup', function(){
				JSPublicIdea.LifeSearch(this.value);
			});
			//Set Clear Search Event
			BX.bind(LSCloseButton, 'click', function(){
				JSPublicIdea.LifeSearch('');
				if(LSInputField)
					LSInputField.value = '';
			});
		}

		if(LSInputField && LSInputField.value.length>0)
			LSCloseButton.style.visibility = 'visible';

		/*Subscribe*/
		var NodeID, Subscribe = BX.findChildren(document, {className: "idea-post-subscribe"}, true);
		if(Subscribe)
		{
			for(NodeID in Subscribe)
			{
				if (Subscribe.hasOwnProperty(NodeID))
				{
					BX.bind(Subscribe[NodeID], "click", subscribeFunction);
				}
			}
		}

		var UnSubscribe = BX.findChildren(document, {className: "idea-post-unsubscribe"}, true);
		if(UnSubscribe)
		{
			for(NodeID in UnSubscribe)
			{
				if (UnSubscribe.hasOwnProperty(NodeID))
				{
					BX.bind(UnSubscribe[NodeID], "click", unsubscribeFunction);
				}
			}
		}
	});
})(window);