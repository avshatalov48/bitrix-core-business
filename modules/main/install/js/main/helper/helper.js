BX.namespace("BX.Helper");

BX.Helper =
{
	frameOpenUrl : '',
	frameNode : null,
	openBtn : null,
	popupLoader : null,
	langId: null,
	ajaxUrl: '',
	currentStepId: '',
	notifyBlock : null,
	notifyNum: '',
	notifyText: '',
	notifyId: 0,
	notifyButton: '',
	isAdmin: "N",
	version: 2,

	init : function(params)
	{
		this.frameOpenUrl = params.frameOpenUrl || '';
		this.langId = params.langId || '';
		this.openBtn = params.helpBtn;
		this.notifyBlock = params.notifyBlock;
		this.ajaxUrl = params.ajaxUrl || '';
		this.currentStepId = params.currentStepId || '';
		this.notifyData = params.notifyData || null;
		this.runtimeUrl = params.runtimeUrl || null;
		this.notifyUrl = params.notifyUrl || '';
		this.helpUrl = params.helpUrl || '';
		this.notifyNum = params.notifyNum || '';
		this.isAdmin = (params.isAdmin && params.isAdmin === 'Y') ? 'Y' : 'N';

		if(this.openBtn)
		{
			this.openBtn.addEventListener('click', function() {
				if (BX.Helper.isOpen())
				{
					BX.Helper.close();
				}
				else
				{
					BX.Helper.show();
				}

				BX.Helper.setBlueHeroView();
			});

		}

		BX.bind(window, 'message', BX.proxy(function(event)
		{
			if(!!event.origin && event.origin.indexOf('bitrix') === -1)
			{
				return;
			}

			if (!event.data || typeof(event.data) !== "object")
			{
				return;
			}

			if(event.data.action === "CloseHelper")
			{
				this.close();
			}

			if(event.data.action === "SetCounter")
			{
				BX.Helper.setNotification(event.data.num);
				BX.Helper.showNotification(event.data.num);
			}

			if(event.data.action === "GetVersion")
			{
				this.frameNode.contentWindow.postMessage({action: 'throwVersion', version: this.version}, '*');
			}

			if(event.data.action === "OpenChat")
			{
				BXIM.openMessenger(event.data.user_id);
			}

			if(event.data.action === "getMenuStructure")
			{
				if (BX.getClass("BX.Bitrix24.LeftMenuClass"))
				{
					if (typeof BX.Bitrix24.LeftMenuClass.getStructureForHelper === "function")
					{
						var structure = BX.Bitrix24.LeftMenuClass.getStructureForHelper();
						this.frameNode.contentWindow.postMessage({action: 'throwMenu', menu: structure}, '*');
					}
				}
			}

			if (event.data.action === "getNewArticleCount")
			{
				var newArticleInfo = {
					action: 'throwNewArticleCount',
					articleCount: this.notifyNum
				};
				if(this.notifyData)
				{
					newArticleInfo.lastTimestampCheckNewArticle = this.notifyData.counter_update_date
				}
				this.frameNode.contentWindow.postMessage(newArticleInfo, '*');
			}
		}, this));

		if (params.needCheckNotify === "Y")
		{
			this.checkNotification();
		}

		if (this.notifyNum > 0)
		{
			BX.Helper.showNotification(this.notifyNum);
		}
	},

	show: function(additionalParam)
	{
		if (this.isOpen())
		{
			return;
		}

		var url = this.frameOpenUrl + ((this.frameOpenUrl.indexOf("?") < 0) ? "?" : "&") +
			(BX.type.isNotEmptyString(additionalParam) ? additionalParam : "");

		if (this.getFrame().src !== url)
		{
			this.getFrame().src = url;
		}

		BX.SidePanel.Instance.open(this.getSliderId(), {
			contentCallback: function(slider) {
				var promise = new BX.Promise();
				promise.fulfill(this.getContent());
				return promise;
			}.bind(this),
			width: 860,
			cacheable: false,
			events: {
				onCloseComplete: function() {
					BX.Helper.close();
				},
				onLoad: function () {
					BX.Helper.showFrame();
				},
				onClose: function () {
					BX.Helper.frameNode.contentWindow.postMessage({action: 'onCloseWidget'}, '*');
				}
			}
		});

		if(this.isAdmin === 'N' && this.openBtn)
		{
			BX.addClass(this.openBtn, 'help-block-active');
		}
	},

	close: function()
	{
		var slider = this.getSlider();
		if (slider)
		{
			slider.close();
		}

		if (this.isAdmin === 'N')
		{
			if (this.openBtn)
			{
				BX.removeClass(this.openBtn, 'help-block-active');
			}
			this.getFrame().classList.remove("helper-panel-iframe-show");
		}
	},

	getContent: function()
	{
		if (this.content)
		{
			return this.content;
		}

		this.content = BX.create('div', {
			attrs: {
				className: 'helper-container'
			},
			children: [
				this.getLoader(),
				this.getFrame()
			]
		});
		return this.content;
	},

	getFrame: function()
	{
		if (this.frameNode)
		{
			return this.frameNode;
		}

		this.frameNode = BX.create('iframe', {
			attrs: {
				className: 'helper-panel-iframe',
				src: "about:blank"
			}
		});

		return this.frameNode;
	},

	showFrame: function()
	{
		setTimeout(function(){
			this.getFrame().classList.add("helper-panel-iframe-show");
		}.bind(this), 600);
	},

	getLoader: function()
	{
		if (this.popupLoader)
		{
			return this.popupLoader;
		}

		this.popupLoader = BX.create('div',{
			attrs:{className:'bx-help-popup-loader'},
			children : [BX.create('div', {
				attrs:{className:'bx-help-popup-loader-text'},
				text : BX.message("MAIN_HELPER_LOADER")
			})]
		});

		return this.popupLoader;
	},

	getSliderId: function()
	{
		return "main:helper";
	},

	getSlider: function()
	{
		return BX.SidePanel.Instance.getSlider(this.getSliderId());
	},

	isOpen: function()
	{
		return this.getSlider() && this.getSlider().isOpen();
	},

	setBlueHeroView : function()
	{
		if (!this.currentStepId)
			return;

		BX.ajax.post(
			this.ajaxUrl,
			{
				sessid:  BX.bitrix_sessid(),
				action: "setView",
				currentStepId: this.currentStepId
			},
			function() {}
		);
	},

	showNotification : function(num)
	{
		if (!isNaN(parseFloat(num)) && isFinite(num) && num > 0)
		{
			var numBlock = '<div class="help-cl-count"><span class="help-cl-count-digit">' + (num > 99 ? '99+' : num) + '</span></div>';
		}
		else
		{
			numBlock = "";
		}
		this.notifyBlock.innerHTML = numBlock;
		this.notifyNum = num;
	},

	showFlyingHero : function(url)
	{
		if (!url)
			return;

		BX.ajax({
			method : "GET",
			dataType: 'html',
			url: this.helpUrl + url,
			data: {},
			onsuccess: BX.proxy(function(res)
			{
				if (res)
				{
					BX.load([this.runtimeUrl], function () {
						eval(res);
					});
				}
			}, this)
		});
	},

	setNotification : function(num, time)
	{
		BX.ajax({
			method: "POST",
			dataType: 'json',
			url: this.ajaxUrl,
			data:
			{
				sessid:  BX.bitrix_sessid(),
				action: "setNotify",
				num: num,
				time: time
			},
			onsuccess: BX.proxy(function (res) {

			}, this)
		});
	},

	checkNotification : function()
	{
		BX.ajax({
			method : "POST",
			dataType: 'json',
			url: this.notifyUrl,
			data: this.notifyData,
			onsuccess: BX.proxy(function(res)
			{
				if (!isNaN(res.num))
				{
					this.setNotification(res.num);
					this.showNotification(res.num);

					if (res.id)
					{
						this.notifyId = res.id;
						this.notifyText = res.body;
						this.notifyButton = res.button;
					}

					if (res.url)
						this.showFlyingHero(res.url);
				}
				else
				{
					this.setNotification('', 'hour');
				}
			}, this),
			onfailure: BX.proxy(function(){
				this.setNotification('', 'hour');
			}, this)
		});
	},

	showAnimatedHero : function() //with finger
	{
		if (!BX.browser.IsIE8())
		{
			BX.load(["/bitrix/js/main/helper/runtime.js", "/bitrix/js/main/helper/hero_object.js"], function() {
				var block = BX.create("div", {attrs: {"className": "bx-help-start", "id": "bx-help-start"}});

				if(BX.admin && BX.admin.panel)
				{
					block.style.top = BX.admin.panel.DIV.offsetHeight+50+"px";
				}

				document.body.appendChild(block);
				var stage = new swiffy.Stage(block, swiffyobject, {});
				stage.setBackground(null);

				setTimeout(function(){
					stage.start();
				}, 300);

				setTimeout(function(){
					block.style.display = 'none';
				},7300);
			});
		}
	}
};
