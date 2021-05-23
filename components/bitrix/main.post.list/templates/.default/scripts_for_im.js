;(function(){
	window["UC"] = (!!window["UC"] ? window["UC"] : {});
	if (!!window["UC"]["Informer"])
		return;

	/* NotifyManager */
	window.SPC = function()
	{
		this.stack = [];
		this.stackTimeout = null;
		this.stackPopup = {};
		this.stackPopupTimeout = {};
		this.stackPopupTimeout2 = {};
		this.stackPopupId = 0;
		this.stackOverflow = false;

		this.notifyShow = 0;
		this.notifyHideTime = 5000;
		this.notifyHeightCurrent = 10;
		this.notifyHeightMax = 0;
		this.notifyGarbageTimeout = null;
		this.notifyAutoHide = true;
		this.notifyAutoHideTimeout = null;
	};
	var SPC = window.SPC;
	/**
	 * @return boolean
	 */
	SPC.prototype.add = function(params)
	{
		if (typeof(params) != "object" || !params.html)
			return false;

		if (BX.type.isDomNode(params.html))
			params.html = params.html.outerHTML;
		this.stack.push(params);

		if (!this.stackOverflow)
			this.setShowTimer(300);
		return true;
	};

	SPC.prototype.remove = function(stackId)
	{
		delete this.stack[stackId];
	};

	SPC.prototype.show = function()
	{
		this.notifyHeightMax = document.body.offsetHeight;

		var windowSize = BX.GetWindowInnerSize();
		for (var i = 0; i < this.stack.length; i++)
		{
			if (typeof(this.stack[i]) == 'undefined')
				continue;

			/* show notify to calc width & height */
			var notifyPopup = new BX.PopupWindow('bx-sbpc-notify-flash-'+this.stackPopupId, {top: 0, left: 0}, {
				lightShadow : true,
				zIndex: 200,
				events : {
					onPopupClose : BX.delegate(function() {
						BX.proxy_context.popupContainer.style.opacity = 0;
						this.notifyShow--;
						this.notifyHeightCurrent -= BX.proxy_context.popupContainer.offsetHeight+10;
						this.stackOverflow = false;
						setTimeout(BX.delegate(function() {
							this.destroy();
						}, BX.proxy_context), 1500);
					}, this),
					onPopupDestroy : BX.delegate(function() {
						BX.unbindAll(BX.findChild(BX.proxy_context.popupContainer, {className : "bx-spbc-notifier-item-delete"}, true));
						BX.unbindAll(BX.proxy_context.popupContainer);
						delete this.stackPopup[BX.proxy_context.uniquePopupId];
						delete this.stackPopupTimeout[BX.proxy_context.uniquePopupId];
						delete this.stackPopupTimeout2[BX.proxy_context.uniquePopupId];
					}, this)
				},
				bindOnResize: false,
				content : BX.create("div", {props : { className: "bx-notifyManager-item-sbpc"}, html: this.stack[i].html})
			});
			notifyPopup.notifyParams = this.stack[i];
			notifyPopup.notifyParams.id = i;
			notifyPopup.show();

			/* move notify out monitor */
			BX.removeClass(notifyPopup.popupContainer.firstChild, 'popup-window');
			notifyPopup.popupContainer.style.left = 10+'px';
			notifyPopup.popupContainer.style.opacity = 0;

			if (this.notifyHeightMax < this.notifyHeightCurrent+notifyPopup.popupContainer.offsetHeight+10)
			{
				if (this.notifyShow > 0)
				{
					notifyPopup.destroy();
					this.stackOverflow = true;
					break;
				}
			}

			/* move notify to top-right */
			BX.addClass(notifyPopup.popupContainer, 'bx-notifyManager-animation-spbc');

			(new BX.easing({
				duration : 500,
				start : { opacity: 0},
				finish : { opacity : 100},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step : function(state){
					if (BX(notifyPopup) && BX(notifyPopup.popupContainer))
						notifyPopup.popupContainer.style.opacity = state.opacity / 100;
				}
			})).animate();

			notifyPopup.popupContainer.style.top = windowSize.innerHeight - this.notifyHeightCurrent - notifyPopup.popupContainer.offsetHeight-10+'px';

			this.notifyHeightCurrent = this.notifyHeightCurrent+notifyPopup.popupContainer.offsetHeight+10;
			this.stackPopupId++;
			this.notifyShow++;
			this.remove(i);

			/* notify events */
			this.stackPopupTimeout[notifyPopup.uniquePopupId] = null;

			BX.bind(notifyPopup.popupContainer, "mouseover", BX.delegate(function() {
				this.clearAutoHide();
			}, this));

			BX.bind(notifyPopup.popupContainer, "mouseout", BX.delegate(function() {
				this.setAutoHide(this.notifyHideTime/2);
			}, this));

			BX.bind(notifyPopup.popupContainer, "contextmenu", BX.delegate(function(e){
				if (this.stackPopup[BX.proxy_context.id].notifyParams.tag)
					this.closeByTag(this.stackPopup[BX.proxy_context.id].notifyParams.tag);
				else
					this.stackPopup[BX.proxy_context.id].close();

				return BX.PreventDefault(e);
			}, this));

			var arLinks = BX.findChildren(notifyPopup.popupContainer, {tagName : "a"}, true);
			for (var j = 0; j < arLinks.length; j++)
			{
				if (arLinks[j].href != '#')
					arLinks[j].target = "_blank";
			}

			BX.bind(BX.findChild(notifyPopup.popupContainer, {className : "bx-spbc-notifier-item-delete"}, true), 'click', BX.delegate(function(e){
				var id = BX.proxy_context.parentNode.parentNode.parentNode.parentNode.id.replace('popup-window-content-', '');

				if (this.stackPopup[id].notifyParams.close)
					this.stackPopup[id].notifyParams.close(this.stackPopup[id]);

				this.stackPopup[id].close();

				if (this.notifyAutoHide === false)
				{
					this.clearAutoHide();
					this.setAutoHide(this.notifyHideTime/2);
				}
				return BX.PreventDefault(e);
			}, this));

			if (notifyPopup.notifyParams.click)
			{
				notifyPopup.popupContainer.style.cursor = 'pointer';
				BX.bind(notifyPopup.popupContainer, 'click', BX.delegate(function(e){
					this.notifyParams.click(this);
					return BX.PreventDefault(e);
				}, notifyPopup));
			}
			this.stackPopup[notifyPopup.uniquePopupId] = notifyPopup;
		}

		if (this.stack.length > 0)
		{
			this.clearAutoHide(true);
			this.setAutoHide(this.notifyHideTime);
		}
		this.garbage();
	};

	SPC.prototype.closeByTag = function(tag)
	{
		for (var i = 0; i < this.stack.length; i++)
		{
			if (typeof(this.stack[i]) != 'undefined' && this.stack[i].tag == tag)
			{
				delete this.stack[i];
			}
		}
		for (i in this.stackPopup)
		{
			if (this.stackPopup.hasOwnProperty(i))
				if (this.stackPopup[i].notifyParams.tag == tag)
					this.stackPopup[i].close()
		}
	};

	SPC.prototype.setShowTimer = function(time)
	{
		clearTimeout(this.stackTimeout);
		this.stackTimeout = setTimeout(BX.delegate(this.show, this), time);
	};

	SPC.prototype.setAutoHide = function(time)
	{
		this.notifyAutoHide = true;
		clearTimeout(this.notifyAutoHideTimeout);
		this.notifyAutoHideTimeout = setTimeout(BX.delegate(function(){
			for (var i in this.stackPopupTimeout)
			{
				if (this.stackPopupTimeout.hasOwnProperty(i))
				{
					this.stackPopupTimeout[i] = setTimeout(BX.delegate(function(){
						this.close();
					}, this.stackPopup[i]), time-1000);
					this.stackPopupTimeout2[i] = setTimeout(BX.delegate(function(){
						this.setShowTimer(300);
					}, this), time-700);
				}
			}
		}, this), 1000);
	};

	SPC.prototype.clearAutoHide = function(force)
	{
		clearTimeout(this.notifyGarbageTimeout);
		this.notifyAutoHide = false;
		force = (force===true);
		var i;
		if (force)
		{
			clearTimeout(this.stackTimeout);
			for (i in this.stackPopupTimeout)
			{
				if (this.stackPopupTimeout.hasOwnProperty(i))
				{
					clearTimeout(this.stackPopupTimeout[i]);
					clearTimeout(this.stackPopupTimeout2[i]);
				}
			}
		}
		else
		{
			clearTimeout(this.notifyAutoHideTimeout);
			this.notifyAutoHideTimeout = setTimeout(BX.delegate(function(){
				clearTimeout(this.stackTimeout);
				for (var i in this.stackPopupTimeout)
				{
					if (this.stackPopupTimeout.hasOwnProperty(i))
					{
						clearTimeout(this.stackPopupTimeout[i]);
						clearTimeout(this.stackPopupTimeout2[i]);
					}
				}
			}, this), 300);
		}
	};

	SPC.prototype.garbage = function()
	{
		clearTimeout(this.notifyGarbageTimeout);
		this.notifyGarbageTimeout = setTimeout(BX.delegate(function(){
			var newStack = [];
			for (var i = 0; i < this.stack.length; i++)
			{
				if (typeof(this.stack[i]) != 'undefined')
					newStack.push(this.stack[i]);
			}
			this.stack = newStack;
		}, this), 10000);
	};

	SPC.prototype.check = function(id, data, tag, text) {
		if (id[1] <= 0 || !window["UC"]["Informer"] || !BX.type.isNotEmptyString(text))
			return;
		var entityId = /(\d+)/g.exec(id[0]),
			node = BX('record-' + id.join('-') + '-cover');
		entityId = (!!entityId ? parseInt(entityId) : 0);
		if (entityId <= 0 || !node)
			return false;
		else if (BX.util.in_array(entityId, window["UC"]["InformerTags"][tag]))
			return true;

		window["UC"]["InformerTags"][tag].push(entityId);
		var res = (!!data && !!data["messageFields"] ? data["messageFields"] : false);
		if (!res)
			return;

		var curPos = BX.pos(node),
			scroll = BX.GetWindowScrollPos(),
			size = BX.GetWindowInnerSize();
		if(curPos.top < scroll.scrollTop || curPos.top > (scroll.scrollTop +size.innerHeight - 20))
		{
			setTimeout(function() {
					if(parseInt(res["AUTHOR"]["ID"]) != parseInt(BX.message("USER_ID")))
					{
						var element = BX.create("div", {props : { className: "bx-spbc-notifier-item"}, children : [
								BX.create('span', {props : { className : "bx-spbc-notifier-item-content" }, children : [
									BX.create('span', {props : { className : "bx-spbc-notifier-item-avatar" }, children : [
										(!!res["AUTHOR"]["AVATAR"] ?
											BX.create('img', {props : { className : "bx-spbc-notifier-item-avatar-img" }, attrs : {src : res["AUTHOR"]["AVATAR"]}}) :
											"")
									]}),
									BX.create("a", {attrs : {href : '#'}, props : { className: "bx-spbc-notifier-item-delete"}}),
									BX.create('span', {props : { className : "bx-spbc-notifier-item-name" }, html: res["AUTHOR"]["NAME"]}),
									BX.create('span', {props : { className : "bx-spbc-notifier-item-time" }, html: res["POST_TIME"]}),
									BX.create('span', {props : { className : "bx-spbc-notifier-item-text" }}),
									BX.create('span', {props : { className : "bx-spbc-notifier-item-text2" }, html: '"' + text + '"'})
								]})
							]}),
							scroll = BX.GetWindowScrollPos();
						window["UC"]["Informer"].add({
							'html': element,
							'tag': 'im-record-' + id.join("-"),
							'click': BX.delegate(function() {
								var arNodePos = BX.pos(BX('record-'+id.join('-')));
								(new BX.easing({
									duration : 500,
									start : { scroll : scroll.scrollTop },
									finish : { scroll : arNodePos.top-100 },
									transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
									step : function(state){
										window.scrollTo(0, state.scroll);
									}
								})).animate();
							}, this)
						});
					}
				}, 50);
		}
	};

	SPC.NativeNotify = function()
	{
		return (window.webkitNotifications && window.webkitNotifications.checkPermission() == 0);
	};

	window["UC"]["Informer"] = new SPC();
	window["UC"]["InformerTags"] = {};

	SPC.notifyManagerShow = function()
	{
		BX.ready(function(){
			BX.addCustomEvent("onNotifyManagerShow", function(params) {
				if(params.originalTag)
				{
					var i = params.originalTag.lastIndexOf("|"),
						tag = params.originalTag.substr(0, i);
					if (!!window["UC"]["InformerTags"][tag])
					{
						var res = parseInt(params.originalTag.substr(i+1));
						window["UC"]["InformerTags"][tag].push(res);

					}
				}
			});
		});
	};
})();
