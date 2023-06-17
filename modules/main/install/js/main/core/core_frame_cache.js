(function (window)
{
	if (window.BX.frameCache) return;

	var BX = window.BX;
	var localStorageKey = "compositeCache";
	var lolalStorageTTL = 1440;
	var compositeMessageIds = ["bitrix_sessid", "USER_ID", "SERVER_TIME", "USER_TZ_OFFSET", "USER_TZ_AUTO"];
	var compositeDataFile = "/bitrix/tools/composite_data.php";
	var sessidWasUpdated = false;

	BX.frameCache = function()
	{
	};

	if (BX.browser.IsIE8())
	{
		BX.frameCache.localStorage = new BX.localStorageIE8();
	}
	else if (typeof(localStorage) !== "undefined")
	{
		BX.frameCache.localStorage = new BX.localStorage();
	}
	else
	{
		BX.frameCache.localStorage = {
			set : BX.DoNothing,
			get : function() { return null; },
			remove : BX.DoNothing
		};
	}

	BX.frameCache.localStorage.prefix = function()
	{
		return "bx-";
	};

	BX.frameCache.init = function()
	{
		this.cacheDataBase = null;
		this.tableParams =
		{
			tableName: "composite",
			fields: [
				{name: "id", unique: true},
				"content",
				"hash",
				"props"
			]
		};

		this.frameDataReceived = false;
		this.frameDataInserted = false;

		if (BX.type.isString(window.frameDataString) && window.frameDataString.length > 0)
		{
			BX.frameCache.onFrameDataReceived(window.frameDataString);
		}

		this.vars = window.frameCacheVars ? window.frameCacheVars : {
			dynamicBlocks: {},
			page_url: "",
			params: {},
			storageBlocks: []
		};

		//local storage warming up
		var lsCache = BX.frameCache.localStorage.get(localStorageKey) || {};
		for (var i = 0; i < compositeMessageIds.length; i++)
		{
			var messageId = compositeMessageIds[i];
			if (typeof(BX.message[messageId]) != "undefined")
			{
				lsCache[messageId] = BX.message[messageId];
			}
		}
		BX.frameCache.localStorage.set(localStorageKey, lsCache, lolalStorageTTL);

		BX.addCustomEvent("onBXMessageNotFound", function(mess)
		{
			if (BX.util.in_array(mess, compositeMessageIds))
			{
				var cache = BX.frameCache.localStorage.get(localStorageKey);
				if (cache && typeof(cache[mess]) != "undefined")
				{
					BX.message[mess] = cache[mess];
				}
				else
				{
					BX.frameCache.getCompositeMessages();
				}
			}
		});

		if (!window.frameUpdateInvoked)
		{
			this.update(false);
			window.frameUpdateInvoked = true;
		}

		if (window.frameRequestStart)
		{
			BX.ready(function() {
				BX.onCustomEvent("onCacheDataRequestStart");
				BX.frameCache.tryUpdateSessid();
			});
		}

		if (window.frameRequestFail)
		{
			BX.ready(function() {
				setTimeout(function() {
					BX.onCustomEvent("onFrameDataRequestFail", [window.frameRequestFail]);
				}, 0);
			});
		}

		BX.frameCache.insertBanner();
	};

	BX.frameCache.getCompositeMessages = function()
	{
		try {
			BX.ajax({
				method: "GET",
				dataType: "json",
				url: compositeDataFile,
				async : false,
				data:  '',
				onsuccess: function(json)
				{
					BX.frameCache.setCompositeVars(json);
				}
			});
		}
		catch (exeption)
		{
			BX.debug("Composite sync request failed.");
		}
	};

	BX.frameCache.setCompositeVars = function(vars)
	{
		if (!vars)
		{
			return;
		}
		else if (vars.lang)
		{
			vars = vars.lang;
		}

		var lsCache = BX.frameCache.localStorage.get(localStorageKey) || {};
		for (var name in vars)
		{
			if (vars.hasOwnProperty(name))
			{
				BX.message[name] = vars[name];

				if (BX.util.in_array(name, compositeMessageIds))
				{
					lsCache[name] = vars[name];
				}
			}
		}

		BX.frameCache.localStorage.set(localStorageKey, lsCache, lolalStorageTTL);
	};

	BX.frameCache.insertBlock = function(block, callback)
	{
		if (!BX.type.isFunction(callback))
		{
			callback = function() {};
		}

		if (!block)
		{
			callback();
			return;
		}

		var container = null;
		var dynamicStart = null;
		var dynamicEnd = null;

		var autoContainerPrefix = "bxdynamic_";
		if (block.ID.substr(0, autoContainerPrefix.length) === autoContainerPrefix)
		{
			dynamicStart = BX(block.ID + "_start");
			dynamicEnd = BX(block.ID + "_end");
			if (!dynamicStart || !dynamicEnd)
			{
				BX.debug("Dynamic area " + block.ID + " was not found");
				callback();
				return;
			}
		}
		else
		{
			container = BX(block.ID);
			if (!container)
			{
				BX.debug("Container " + block.ID + " was not found");
				callback();
				return;
			}
		}

		let htmlWasInserted = false;
		let scriptsLoaded = false;
		const assets = getAssets();

		processStrings();
		processAssets(() => {
			scriptsLoaded = true;
			insertHTML();
		});

		function processAssets(callback)
		{
			let styles = assets.styles;
			if (BX.type.isArray(block.PROPS.CSS) && block.PROPS.CSS.length > 0)
			{
				styles = block.PROPS.CSS.concat(styles);
			}

			let scripts = assets.externalJS;
			if (BX.type.isArray(block.PROPS.JS) && block.PROPS.JS.length > 0)
			{
				scripts = scripts.concat(block.PROPS.JS);
			}

			const items = styles.concat(scripts);
			if (items.length > 0)
			{
				BX.load(items, callback)
			}
			else
			{
				callback();
			}
		}

		function insertHTML()
		{
			if (container)
			{
				if (block.PROPS.USE_ANIMATION)
				{
					container.style.opacity = 0;
					container.innerHTML = block.CONTENT;
					(new BX.easing({
						duration : 1500,
						start : { opacity: 0 },
						finish : { opacity: 100 },
						transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
						step : function(state){
							container.style.opacity = state.opacity / 100;
						},
						complete : function() {
							container.style.cssText = '';
						}
					})).animate();
				}
				else
				{
					container.innerHTML = block.CONTENT;
				}
			}
			else
			{
				BX.frameCache.removeNodes(dynamicStart, dynamicEnd);
				dynamicStart.insertAdjacentHTML("afterEnd", block.CONTENT);
			}

			htmlWasInserted = true;
			if (scriptsLoaded)
			{
				processInlineJS();
			}
		}

		function processStrings()
		{
			if (BX.Type.isStringFilled(assets.html))
			{
				document.head.insertAdjacentHTML("beforeend", assets.html);
			}

			BX.evalGlobal(assets.inlineJS.join(";"));
		}

		function getAssets()
		{
			var result = { styles: [], inlineJS: [], externalJS: [], html: "" };
			if (!BX.type.isArray(block.PROPS.STRINGS) || block.PROPS.STRINGS.length < 1)
			{
				return result;
			}

			var parts = BX.processHTML(block.PROPS.STRINGS.join(""), false);
			for (var i = 0, l = parts.SCRIPT.length; i < l; i++)
			{
				var script = parts.SCRIPT[i];
				if (script.isInternal)
				{
					result.inlineJS.push(script.JS);
				}
				else
				{
					result.externalJS.push(script.JS);
				}
			}

			result.styles = parts.STYLE;
			result.html = parts.HTML;

			return result;
		}

		function processInlineJS()
		{
			scriptsLoaded = true;
			if (htmlWasInserted)
			{
				BX.ajax.processRequestData(block.CONTENT, {scriptsRunFirst: false, dataType: "HTML"});

				if (BX.type.isArray(block.PROPS.BUNDLE_JS))
				{
					BX.setJSList(block.PROPS.BUNDLE_JS);
				}

				if (BX.type.isArray(block.PROPS.BUNDLE_CSS))
				{
					BX.setCSSList(block.PROPS.BUNDLE_CSS);
				}

				callback();
			}
		}
	};

	BX.frameCache.removeNodes = function(fromElement, toElement)
	{
		var startFound = false;
		var parent = fromElement.parentNode;
		var nodes = Array.prototype.slice.call(parent.childNodes);
		for (var i = 0, length = nodes.length; i < length; i++)
		{
			if (startFound)
			{
				if (nodes[i] === toElement)
				{
					break;
				}
				else
				{
					parent.removeChild(nodes[i]);
				}
			}
			else if (nodes[i] === fromElement)
			{
				startFound = true;
			}
		}
	};

	BX.frameCache.update = function(makeRequest, noInvoke)
	{
		noInvoke = !!noInvoke;
		makeRequest = typeof(makeRequest) == "undefined" ? true : makeRequest;
		if (makeRequest)
		{
			this.requestData();
		}

		if (!noInvoke)
		{
			BX.ready(BX.proxy(function() {
				if (!this.frameDataReceived)
				{
					this.invokeCache();
				}
			}, this));
		}
	};

	BX.frameCache.invokeCache = function()
	{
		//getting caching dynamic blocks
		if (this.vars.storageBlocks && this.vars.storageBlocks.length > 0)
		{
			BX.onCustomEvent(this, "onCacheInvokeBefore", [this.vars.storageBlocks]);
			this.readCacheWithID(this.vars.storageBlocks, BX.proxy(this.insertFromCache, this));
		}
	};

	BX.frameCache.handleResponse = function(json)
	{
		if (json == null)
			return;

		BX.onCustomEvent("onFrameDataReceivedBefore", [json]);

		if (json.dynamicBlocks && json.dynamicBlocks.length > 0)//we have dynamic blocks
		{
			this.insertBlocks(json.dynamicBlocks, false);
			this.writeCache(json.dynamicBlocks);
		}

		BX.onCustomEvent("onFrameDataReceived", [json]);

		if (
			json.isManifestUpdated == "1"
			&& this.vars.CACHE_MODE === "APPCACHE"
			&& window.applicationCache
			&& (
				window.applicationCache.status == window.applicationCache.IDLE
				|| window.applicationCache.status == window.applicationCache.UPDATEREADY
			)
		) //the manifest has been changed
		{
			window.applicationCache.update();
		}

		if (json.htmlCacheChanged === true && this.vars.CACHE_MODE === "HTMLCACHE")
		{
			BX.onCustomEvent("onHtmlCacheChanged", [json]);
		}

		if (BX.type.isArray(json.spread))
		{
			for (var i = 0; i < json.spread.length; i++)
			{
				new Image().src = json.spread[i];
			}
		}

	};

	BX.frameCache.requestData = function()
	{
		var headers = [
			{ name: "BX-ACTION-TYPE", value: "get_dynamic" },
			{ name: "BX-REF", value: document.referrer },
			{ name: "BX-CACHE-MODE", value: this.vars.CACHE_MODE },
			{ name: "BX-CACHE-BLOCKS", value: this.vars.dynamicBlocks ? JSON.stringify(this.vars.dynamicBlocks) : "" }
		];

		if (this.vars.AUTO_UPDATE === false && this.vars.AUTO_UPDATE_TTL && this.vars.AUTO_UPDATE_TTL > 0)
		{
			var lastModified = Date.parse(document.lastModified);
			if (!isNaN(lastModified))
			{
				var now = new Date().getTime();
				if ((lastModified + this.vars.AUTO_UPDATE_TTL * 1000) < now)
				{
					headers.push({ name: "BX-INVALIDATE-CACHE", value: "Y" });
				}
			}
		}

		if (this.vars.CACHE_MODE === "APPCACHE")
		{
			headers.push({ name: "BX-APPCACHE-PARAMS", value: JSON.stringify(this.vars.PARAMS) });
			headers.push({ name: "BX-APPCACHE-URL", value: this.vars.PAGE_URL ? this.vars.PAGE_URL : "" });
		}

		BX.onCustomEvent("onCacheDataRequestStart");

		var requestURI = window.location.href;
		var index = requestURI.indexOf("#");
		if (index > 0)
		{
			requestURI = requestURI.substring(0, index);
		}
		requestURI += (requestURI.indexOf("?") >= 0 ? "&" : "?") + "bxrand=" + new Date().getTime();

		BX.ajax({
			timeout: 60,
			method: "GET",
			url: requestURI,
			data: {},
			headers: headers,
			skipBxHeader : true,
			processData: false,
			onsuccess: BX.proxy(BX.frameCache.onFrameDataReceived, this),
			onfailure: function()
			{
				window.frameRequestFail = {
					error: true,
					reason: "bad_response",
					url : requestURI,
					xhr: this.xhr,
					status: this.xhr ? this.xhr.status : 0
				};

				BX.onCustomEvent("onFrameDataRequestFail", [window.frameRequestFail]);
			}
		});
	};

	BX.frameCache.onFrameDataReceived = function(response)
	{
		var result = null;
		try
		{
			eval("result = " + response);
		}
		catch (e)
		{
			var error = {
				error: true,
				reason: "bad_eval",
				response: response
			};

			window.frameRequestFail = error;

			BX.ready(function() {
				setTimeout(function() {
					BX.onCustomEvent("onFrameDataRequestFail", [error]);
				}, 0);
			});

			return;
		}

		this.frameDataReceived = true;

		if (result && BX.type.isNotEmptyString(result.redirect_url))
		{
			window.location = result.redirect_url;
			return;
		}

		if (result && result.error === true)
		{
			window.frameRequestFail = result;

			BX.ready(BX.proxy(function() {
				setTimeout(BX.proxy(function() {
					BX.onCustomEvent("onFrameDataRequestFail", [result]);
				}, this), 0);
			}, this));

			return;
		}

		BX.frameCache.setCompositeVars(result);
		BX.ready(BX.proxy(function() {
			this.handleResponse(result);
			this.tryUpdateSessid();
		}, this));
	};

	BX.frameCache.insertFromCache = function(resultSet, transaction)
	{
		if (!this.frameDataReceived)
		{
			var items = resultSet.items;
			if (items.length > 0)
			{
				for (var i = 0; i < items.length; i++)
				{
					items[i].PROPS = JSON.parse(items[i].PROPS);
				}

				this.insertBlocks(items, true);
			}

			BX.onCustomEvent(this, "onCacheInvokeAfter", [this.vars.storageBlocks, resultSet]);
		}
	};

	BX.frameCache.insertBlocks = function(blocks, fromCache)
	{
		var blocksToInsert = new Set();
		for (var i = 0; i < blocks.length; i++)
		{
			var block = blocks[i];
			BX.onCustomEvent("onBeforeDynamicBlockUpdate", [block, fromCache]);

			if (block.PROPS.AUTO_UPDATE === false)
			{
				continue;
			}

			blocksToInsert.add(block);
		}

		let inserted = 0;

		const finalize = () => {
			if (window.performance)
			{
				var entries = performance.getEntries();
				for (var i = 0; i < entries.length; i++)
				{
					var entry = entries[i];
					if (entry.initiatorType === 'xmlhttprequest' && entry.name && entry.name.match(/bxrand=[0-9]+/))
					{
						// uses in ba.js
						this.requestTiming = entry;
					}
				}

				if (window.performance.measure)
				{
					window.performance.measure('Composite:LCP');

					var lcpEntries = performance.getEntriesByName('Composite:LCP');
					if (lcpEntries.length > 0 && lcpEntries[0].duration)
					{
						// uses in ba.js
						this.lcp = Math.ceil(lcpEntries[0].duration);
					}
				}
			}

			BX.onCustomEvent("onFrameDataProcessed", [blocks, fromCache]);
			this.frameDataInserted = true;
		};

		const handleBlockInsertion = () => {
			if (++inserted === blocksToInsert.size)
			{
				finalize();
			}
		};

		if (blocksToInsert.size === 0)
		{
			finalize();
		}
		else
		{
			blocksToInsert.forEach(function(block) {

				if (block && block.HASH && block.PROPS && block.PROPS.ID)
				{
					this.vars.dynamicBlocks[block.PROPS.ID] = block.HASH;
				}

				this.insertBlock(block, handleBlockInsertion);
			}, this);
		}
	};

	BX.frameCache.writeCache = function(blocks)
	{
		for (var i = 0; i < blocks.length; i++)
		{
			if (blocks[i].PROPS.USE_BROWSER_STORAGE === true)
			{
				this.writeCacheWithID(
					blocks[i].ID,
					blocks[i].CONTENT,
					blocks[i].HASH,
					JSON.stringify(blocks[i].PROPS)
				);
			}
		}
	};

	BX.frameCache.openDatabase = function()
	{
		var isDatabaseOpened = (this.cacheDataBase != null);

		if(!isDatabaseOpened)
		{
			this.cacheDataBase = new BX.Dexie("composite");
			if(this.cacheDataBase != null)
			{
				this.cacheDataBase.version(1).stores({
					composite: '&ID,CONTENT,HASH,PROPS'
				});
				isDatabaseOpened = true;
			}
		}

		return isDatabaseOpened;
	};

	BX.frameCache.writeCacheWithID = function(id, content, hash, props)
	{
		if(BX.frameCache.openDatabase())
		{
			if (typeof props == "object")
			{
				props = JSON.stringify(props);
			}

			this.cacheDataBase.composite.put({
				ID: id,
				CONTENT: content,
				HASH: hash,
				PROPS : props
			});
		}
	};

	BX.frameCache.readCacheWithID = function(id, callback)
	{
		if(BX.frameCache.openDatabase())
		{
			this.cacheDataBase.composite
				.where("ID").anyOf(id).toArray()
				.then((function(items){
					callback({items:items});
				}).bind(this));
		}
		else if(typeof callback != "undefined")
		{
			callback({items:[]});
		}
	};

	BX.frameCache.insertBanner = function()
	{
		if (!this.vars.banner || !BX.type.isNotEmptyString(this.vars.banner.text))
		{
			return;
		}

		BX.ready(BX.proxy(function() {
			var banner = BX.create("a", {
				props : {
					className : "bx-composite-btn" + (
						BX.type.isNotEmptyString(this.vars.banner.style) ?
						" bx-btn-" + this.vars.banner.style :
						""
					),
					href : this.vars.banner.url
				},
				attrs : {
					target : "_blank"
				},
				text : this.vars.banner.text
			});

			if (BX.type.isNotEmptyString(this.vars.banner.bgcolor))
			{
				banner.style.backgroundColor = this.vars.banner.bgcolor;
				if (BX.util.in_array(this.vars.banner.bgcolor.toUpperCase(), ["#FFF", "#FFFFFF", "WHITE"]))
				{
					BX.addClass(banner, "bx-btn-border");
				}
			}

			var container = BX("bx-composite-banner");
			if (container)
			{
				container.appendChild(banner);
			}
			else
			{
				BX.addClass(banner, "bx-composite-btn-fixed");
				document.body.appendChild(BX.create("div", {
					style : { position: "relative" },
					children: [ banner ]
				}));
			}
		}, this));
	};

	BX.frameCache.tryUpdateSessid = function()
	{
		if (sessidWasUpdated)
		{
			return;
		}

		var name = "bitrix_sessid";
		var sessid = false;

		if (typeof(BX.message[name]) != "undefined")
		{
			sessid = BX.message[name];
		}
		else
		{
			var cache = BX.frameCache.localStorage.get(localStorageKey) || {};
			if (typeof(cache[name]) != "undefined")
			{
				sessid = cache[name];
			}
		}

		if (sessid !== false)
		{
			sessidWasUpdated = true;
			this.updateSessid(sessid);
		}
	};

	BX.frameCache.updateSessid = function(sessid)
	{
		var inputs = document.getElementsByName("sessid");
		for (var i = 0; i < inputs.length; i++)
		{
			inputs[i].value = sessid;
		}
	};

	//initialize
	BX.frameCache.init();

})(window);

