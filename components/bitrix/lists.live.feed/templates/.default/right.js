;(function(){

	window.BXfpListsSelectCallback = function(item)
	{
		BXfpListsMedalSelectCallback(item, 'lists');
	};

	window.BXfpListsMedalLinkName = function(name, type)
	{
		if (type != 'lists')
			type = 'medal';

		if (BX.SocNetLogDestination.getSelectedCount(name) <= 0)
			BX('bx-'+type+'-tag').innerHTML = BX.message("LISTS_ADD_STAFF");
		else
			BX('bx-'+type+'-tag').innerHTML = BX.message("LISTS_ADD_STAFF_MORE");
	};

	window.BXfpListsMedalSelectCallback = function(item, type)
	{
		if (type != 'lists')
			type = 'medal';

		var prefix = 'U';

		BX('feed-add-post-'+type+'-item').appendChild(
			BX.create("span", {
				attrs : { 'data-id' : item.id },
				props : { className : "feed-add-post-"+type+" feed-add-post-destination-users" },
				children: [
					BX.create("input", {
						attrs : { 'type' : 'hidden', 'name' : 'LISTS'+'['+prefix+'][]', 'value' : item.id }
					}),
					BX.create("span", {
						props : { 'className' : "feed-add-post-"+type+"-text" },
						html : item.name
					}),
					BX.create("span", {
						props : { 'className' : "feed-add-post-del-but"},
						events : {
							'click' : function(e){
								BX.SocNetLogDestination.deleteItem(item.id, 'users', window["BXSocNetLogListsFormName"]);
								BXfpListsUnSelectCallback(item);
								BX.PreventDefault(e)
							},
							'mouseover' : function(){
								BX.addClass(this.parentNode, 'feed-add-post-'+type+'-hover')
							},
							'mouseout' : function(){
								BX.removeClass(this.parentNode, 'feed-add-post-'+type+'-hover')
							}
						}
					})
				]
			})
		);

		BX('feed-add-post-'+type+'-input').value = '';
		BXfpListsMedalLinkName(window["BXSocNetLogListsFormName"], type);
	};

	window.BXfpListsUnSelectCallback = function(item)
	{
		BXfpListsMedalUnSelectCallback(item, 'lists');
	};

	window.BXfpListsMedalUnSelectCallback = function(item, type)
	{
		var elements = BX.findChildren(BX('feed-add-post-'+type+'-item'), {attribute: {'data-id': ''+item.id+''}}, true);
		if (elements != null)
		{
			for (var j = 0; j < elements.length; j++)
				BX.remove(elements[j]);
		}
		BX('feed-add-post-'+type+'-input').value = '';
		BXfpListsMedalLinkName(window["BXSocNetLogListsFormName"], type);
	};

	window.BXfpListsOpenDialogCallback = function()
	{
		BX.style(BX('feed-add-post-lists-input-box'), 'display', 'inline-block');
		BX.style(BX('bx-lists-tag'), 'display', 'none');
		BX.focus(BX('feed-add-post-lists-input'));
	};

	window.BXfpListsCloseDialogCallback = function()
	{
		if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-lists-input').value.length <= 0)
		{
			BX.style(BX('feed-add-post-lists-input-box'), 'display', 'none');
			BX.style(BX('bx-lists-tag'), 'display', 'inline-block');
			BX.SocNetLogDestination.BXfpDisableBackspace();
		}
	};

	window.BXfpListsCloseSearchCallback = function()
	{
		if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-lists-input').value.length > 0)
		{
			BX.style(BX('feed-add-post-lists-input-box'), 'display', 'none');
			BX.style(BX('bx-lists-tag'), 'display', 'inline-block');
			BX('feed-add-post-lists-input').value = '';
			BX.SocNetLogDestination.BXfpDisableBackspace();
		}
	};

	/**
	 * @return boolean
	 */
	window.BXfpListsSearch = function(event)
	{
		if(event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18)
			return false;

		if (event.keyCode == 13)
		{
			BX.SocNetLogDestination.selectFirstSearchItem(window["BXSocNetLogListsFormName"]);
			return true;
		}
		if (event.keyCode == 27)
		{
			BX('feed-add-post-lists-input').value = '';
			BX.style(BX('bx-lists-tag'), 'display', 'inline');
		}
		else
		{
			BX.SocNetLogDestination.search(BX('feed-add-post-lists-input').value, true, window["BXSocNetLogListsFormName"]);
		}

		if (!BX.SocNetLogDestination.isOpenDialog() && BX('feed-add-post-lists-input').value.length <= 0)
		{
			BX.SocNetLogDestination.openDialog(window["BXSocNetLogListsFormName"]);
		}
		else
		{
			if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
				BX.SocNetLogDestination.closeDialog();
		}
		if (event.keyCode == 8)
		{
			BX.SocNetLogDestination.sendEvent = true;
		}
		return true;
	};

	/**
	 * @return boolean
	 */
	window.BXfpListsSearchBefore = function(event)
	{
		if (event.keyCode == 8 && BX('feed-add-post-lists-input').value.length <= 0)
		{
			BX.SocNetLogDestination.sendEvent = false;
			BX.SocNetLogDestination.deleteLastItem(window["BXSocNetLogListsFormName"]);
		}
		return true;
	};
})();