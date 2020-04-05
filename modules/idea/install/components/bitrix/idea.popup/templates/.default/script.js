;(function(){
	BX.Idea = (!!BX.Idea ? BX.Idea : {});
	if (!!top.BX.Idea.Popup || !!BX.Idea.Popup)
		return;
	var popup = null;
	BX.Idea.show = function(page)
	{
		if (popup === null)
			popup = new BX.Idea.Popup();
		return popup.show(page);
	};
	BX.Idea.add = function()
	{
		BX.Idea.show('add');
	};
	BX.Idea.set = function(node)
	{
		//Hide Selection Menu
		var tab = node.parentNode.firstChild, content;
		do {
			if (tab.tagName == node.tagName && tab.hasAttribute("id"))
			{
				content = BX(tab.id + '-content');
				if (tab == node)
				{
					if(!BX.hasClass(tab, 'status-item-selected'))
						BX.addClass(tab, 'status-item-selected');
					BX.removeClass(tab, 'status-item');
					BX.show(content);
				}
				else
				{
					if(!BX.hasClass(tab, 'status-item'))
						BX.addClass(tab, 'status-item');
					BX.removeClass(tab, 'status-item-selected');
					BX.hide(content);
				}
			}
		} while ((tab = tab.nextSibling) && !!tab);
	};
	BX.Idea.Popup = function()
	{
		this.popup = null;
		this.debug = false;
		this.__parseAnswer = BX.delegate(this.parseAnswer, this);
		this.defaultContent = BX.message('IDEA_POPUP_WAIT');
	};
	BX.Idea.Popup.prototype =
	{
		bindButtons : function()
		{
			var form = BX.findChild(this.popup.contentContainer, {tagName : "FORM"}, true);
			if (!!form && form.name.indexOf("REPLIER") == 0)
			{
				this.popup.setButtons([
					new BX.PopupWindowButton( {
						id : "popupIdeaAccept",
						text : BX.message("IDEA_POPUP_APPLY"),
						className : "popup-window-button-accept",
						events : {
							click : BX.proxy(function(){
								BX.onCustomEvent(form, "OnSubmitForm", []);
								BX.ajax.submitAjax(form, {
									method : "POST",
									processData : false,
									onsuccess : BX.delegate(function(result) {
										if (result.indexOf('bxIdeaId') >= 0)
											top.BX.Idea.show('list');
										else
											this.__parseAnswer(result);
									}, this)
								});
							}, this)
						}
					} ),
					new BX.PopupWindowButtonLink( {
						id : "popupIdeaCancel",
						text: BX.message("IDEA_POPUP_CANCEL"),
						className: "popup-window-button-link-cancel",
						events: { click : function()
						{
							BX.Idea.show('list');
						}}
					} )
				]);
				return true;
			}
			this.popup.setButtons([]);
			return false;
		},
		parseAnswer : function(result)
		{
			var ob = BX.processHTML(result, false);
			this.popup.setContent(ob.HTML);
			BX.defer(function() {
				top.BX.ajax.processScripts(ob.SCRIPT);
			})();
			this.bindButtons();
		},
		init : function()
		{
			return this.popup;
		},
		show : function(page)
		{
			page = (page == 'add' ? 'GET_ADD_FORM' : 'GET_LIST');

			if (this.popup === null)
			{
				this.popup = BX.PopupWindowManager.create(
					'popupIdea',
					null,
					{
						className : "idea-popup-container",
						autoHide : false,
						lightShadow : true,
						closeIcon : false,
						closeByEsc : true,
						zIndex : -200,
						content : '',
						overlay : {},
						events : {
							onAfterPopupShow : BX.delegate(function()
							{
								var form1 = BX.findChild(this.popup.contentContainer, {tagName : "FORM"}, true);
								BX.remove(form1);
								this.popup.setContent(this.defaultContent);
								BX.ajax({
									method: 'GET',
									processData: false,
									url: this.url,
									onsuccess: this.__parseAnswer
								});
							}, this)
						}
					}
				);
			}
			this.url = window.location.href + (window.location.href.indexOf("?") > 0 ? "&" : "?") + BX.ajax.prepareData({ AJAX : 'Y', ACTION : page });
			this.popup.show();
		}
	};
	BX.ready(function(){
		BX.bind(BX('idea-side-button'), "click", function(){ BX.Idea.show('list'); });
	});
})();