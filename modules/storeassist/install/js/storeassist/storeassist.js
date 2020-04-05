BX.namespace("BX.Storeassist.Admin");

BX.Storeassist.Admin = {
	showDocumentation : function(url)
	{
		if (!url)
			return;

		BX.PopupWindowManager.create("storeassistDoc", null, {
			autoHide: false,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : false,
			draggable: {restrict:true},
			closeByEsc: true,
			contentColor : "white",
			contentNoPaddings : true,
			closeIcon: { right : "12px", top : "10px"},
			titleBar: {content: BX.create("span", {html: '<a href="'+url+'" target=_blank style="color:grey;">' + BX.message("STOREAS_OPEN_NEW_TAB") + '</a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="BX.PopupWindowManager.getCurrentPopup().close();" style="color:grey;">' + BX.message("STOREAS_CLOSE") + '</a>', style:{"display": "block","padding": "14px 41px","text-align": "right", "font-size":"14px"}})},
			content: "<div class='bx-storas-waiter'><iframe onload='BX.removeClass(this.parentNode, \"bx-storas-waiter\")' src='" + url + "?video=small' width='757px' height='700px' border='0' frameborder='0'></iframe></div>",
			events: {
				onPopupClose: function()
				{
					this.destroy();
				}
			}
		}).show();
	},

	toggleStep : function(stepCode)
	{
		if (!stepCode)
			return;

		var stepBlock = document.querySelector('[data-role="step'+stepCode+'"]');
		var containerBlock = document.querySelector('[data-role="container'+stepCode+'"]');
		var toggleText = document.querySelector('[data-role="toggle'+stepCode+'"]');

		if (BX.hasClass(stepBlock, "close"))
		{
			if (toggleText)
				toggleText.innerHTML = BX.message("STOREAS_HIDE");

			BX.removeClass(stepBlock, "close");
			BX.addClass(stepBlock, "open");

			containerBlock.style.opacity = 0;
			containerBlock.style.display = "block";
			containerBlock.style.padding = 0;
			containerBlock.style.margin = 0;
			containerBlock.style.height = "";
			var finishHeight = containerBlock.offsetHeight;

			containerBlock.style.height = 0;
			containerBlock.style.opacity = "";
			containerBlock.style.display = "";
			containerBlock.style.padding = "";
			containerBlock.style.margin = "";

			new BX.easing({
				duration : 600,
				start : { height : 0 },
				finish : { height : finishHeight },
				transition : BX.easing.transitions.linear,
				step : function(state){
					containerBlock.style.height = state.height + "px";
				},
				complete : function() {

				}
			}).animate();

			BX.userOptions.save("storeassist", 'step_toggle',  stepCode, 'Y');
		}
		else if (BX.hasClass(stepBlock, "open"))
		{
			if (toggleText)
				toggleText.innerHTML = BX.message("STOREAS_SHOW");

			new BX.easing({
				duration : 600,
				start : { height : containerBlock.offsetHeight },
				finish : { height : 0 },
				transition : BX.easing.transitions.linear,
				step : function(state){
					containerBlock.style.height = state.height + "px";
				},
				complete : function() {
					BX.removeClass(stepBlock, "open");
					BX.addClass(stepBlock, "close");
				}
			}).animate();

			BX.userOptions.save("storeassist", 'step_toggle',  stepCode, 'N');
		}
	},

	setOption : function(pageId, status)
	{
		if (!pageId)
			return;

		status = (status == "Y") ? "Y" : "N";

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: '/bitrix/tools/storeassist.php',
			data: {
				pageId: pageId,
				status: status,
				action: "setOption",
				sessid: BX.bitrix_sessid()
			},
			onsuccess: function(json)
			{
				if (json.success == "Y")
					location.reload();
			}
		});
	},

	percentMoveInit : function(percentRuleSlider, currentPercent)
	{
		if (!percentRuleSlider || !currentPercent)
			return;

		this.percentRuleSlider = percentRuleSlider;
		this.currentPercent = currentPercent;

		var percentRuleWrap = this.percentRuleSlider.parentNode.parentNode.parentNode;
		this.percentStatuses = BX.findChildren(percentRuleWrap, {className:"adm-s-thermometer-block-status"}, true);
		this.currentStatus = BX.findChild(percentRuleWrap, {className:"active"}, true, false);

		BX.bind(this.percentRuleSlider, "mousedown", BX.proxy(function(event){
			this.movePercentSlider(event)
		}, this));
	},

	getPageX : function(e)
	{
		e = e || window.event;
		var pageX = null;

		if (e.pageX != null)
		{
			pageX = e.pageX;
		}
		else if (e.clientX != null)
		{
			var html = document.documentElement;
			var body = document.body;

			pageX = e.clientX + (html.scrollLeft || body && body.scrollLeft || 0);
			pageX -= html.clientLeft || 0;
		}

		return pageX;
	},

	getXCoord : function(elem)
	{
		var box = elem.getBoundingClientRect();
		var body = document.body;
		var docElem = document.documentElement;

		var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
		var clientLeft = docElem.clientLeft || body.clientLeft || 0;
		var left = box.left + scrollLeft - clientLeft;

		return Math.round(left);
	},

	movePercentSlider : function(event)
	{
		this.percentRuleSlider.ondragstart = function() {
			return false;
		};

		document.onmousemove = BX.proxy(function(event) {
			var pageX = this.getPageX(event);
			var trackerXCoord = this.getXCoord(this.percentRuleSlider.parentNode);
			var newLeft = pageX - trackerXCoord;

			if (newLeft < 0)
			{
				newLeft = 0;
			}
			var rightEdge = this.percentRuleSlider.parentNode.offsetWidth;
			if (newLeft > rightEdge)
			{
				newLeft = rightEdge;
			}
			this.newLeft = ((newLeft*100)/rightEdge);
			this.percentRuleSlider.style.left = this.newLeft + "%";
			BX.firstChild(this.percentRuleSlider).innerHTML = Math.round(this.newLeft) + "%";

			//show rule's legenda
			var activeColor = "red";
			if (this.newLeft > 18 && this.newLeft <= 36)
				activeColor = "orange";
			else if (this.newLeft > 36 && this.newLeft <= 54)
				activeColor = "yellow";
			else if (this.newLeft > 54 && this.newLeft <= 72)
				activeColor = "green";
			else if (this.newLeft > 72 && this.newLeft <= 90)
				activeColor = "lightgreen";
			else if (this.newLeft > 90)
				activeColor = "blue";	

			for (var i=0; i<this.percentStatuses.length; i++)
			{
				if (BX.hasClass(this.percentStatuses[i], activeColor))
					BX.addClass(this.percentStatuses[i], "active");
				else
					BX.removeClass(this.percentStatuses[i], "active");
			}

		}, this);

		document.onmouseup = BX.proxy(function() {
			document.onmousemove = document.onmouseup = null;

			BX.addClass(this.percentRuleSlider, "animate");
			BX.firstChild(this.percentRuleSlider).innerHTML = this.currentPercent + "%";

			for (var i=0; i<this.percentStatuses.length; i++)
			{
				BX.removeClass(this.percentStatuses[i], "active");
			}
			BX.addClass(this.currentStatus, "active");

			this.percentRuleSlider.style.left = this.currentPercent + "%";
			setTimeout(BX.proxy(function(){BX.removeClass(this.percentRuleSlider, "animate");}, this), 550);
		}, this);
	}
};

