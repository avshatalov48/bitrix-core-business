(function (window)
{
	if (window.BX.frameCache) return;

	var BX = window.BX;
	var localStorageKey = "compositeCache";
	var localStorageKeyPullConfig = "pullConfigCache";
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

		this.frameData = null;
		if (BX.type.isString(window.frameDataString) && window.frameDataString.length > 0)
		{
			BX.frameCache.onFrameDataReceived(window.frameDataString);
		}

		this.vars = window.frameCacheVars ? window.frameCacheVars : {
			page_url: "",
			params: {},
			storageBlocks: []
		};

		this.lastReplacedBlocks = false;

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

	/**
	 * @param {object} vars
	 * @param {object} [vars.pull] - pull configuration
	 */
	BX.frameCache.setPullConfigVars = function(vars)
	{
		if (!vars || !vars.pull)
		{
			return;
		}

		var cachedPullConfig = BX.frameCache.localStorage.get(localStorageKeyPullConfig);
		var isPullChannelsHaveBeenChanged = false;

		if(cachedPullConfig != null)
		{
			var channels = vars.pull.CHANNEL_ID.split("/");
			var cachedChannels = cachedPullConfig.CHANNEL_ID.split("/");
			isPullChannelsHaveBeenChanged = ((channels[0] != cachedChannels[0]) || (channels[1] != cachedChannels[1]))
		}
		else
		{
			isPullChannelsHaveBeenChanged = true;
		}

		var dt = vars.pull.CHANNEL_DT.split("/");
		var nearChannelExpireTime = Math.min(parseInt(dt[0]),parseInt(dt[1]))+43200;
		var currentTime = Math.round((new Date).getTime() / 1000);
		var ttl = nearChannelExpireTime - currentTime;

		BX.frameCache.localStorage.set(localStorageKeyPullConfig, vars.pull, ttl);

		if(isPullChannelsHaveBeenChanged)
		{
			BX.onCustomEvent("pullConfigHasBeenChanged", [vars.pull]);
		}
	};



	BX.frameCache.getPullConfig = function()
	{
		if(this.frameData && this.frameData.pull)
			return this.frameData.pull;
		else
		{
			return BX.frameCache.localStorage.get(localStorageKeyPullConfig);
		}
	};

	BX.frameCache.processData = function(block)
	{
		if (!block)
		{
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
				return;
			}
		}
		else
		{
			container = BX(block.ID);
			if (!container)
			{
				BX.debug("Container " + block.ID + " was not found");
				return;
			}
		}

		var htmlWasInserted = false;
		var scriptsLoaded = false;
		var assets = getAssets();
		processCSS(insertHTML);
		processStrings();
		processExternalJS(processInlineJS);

		function processCSS(callback)
		{
			var styles = assets.styles;
			if (BX.type.isArray(block.PROPS.CSS) && block.PROPS.CSS.length > 0)
			{
				styles = BX.util.array_merge(block.PROPS.CSS, styles);
			}

			styles.length > 0 ? BX.load(styles, callback) : callback();
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
			BX.evalGlobal(assets.inlineJS.join(";"));
		}

		function getAssets()
		{
			var result = { styles: [], inlineJS: [], externalJS: []};
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

			return result;
		}

		function processExternalJS(callback)
		{
			var scripts = assets.externalJS;
			if (BX.type.isArray(block.PROPS.JS) && block.PROPS.JS.length > 0)
			{
				scripts = BX.util.array_merge(scripts, block.PROPS.JS);
			}
			scripts.length > 0 ? BX.load(scripts, callback) : callback();
		}

		function processInlineJS()
		{
			scriptsLoaded = true;
			if (htmlWasInserted)
			{
				BX.ajax.processRequestData(block.CONTENT, {scriptsRunFirst: false, dataType: "HTML"});
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
				if (!this.frameData)
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

		if (json.isManifestUpdated == "1" && this.vars.CACHE_MODE === "APPCACHE")//the manifest has been changed
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
			{ name: "BX-CACHE-MODE", value: this.vars.CACHE_MODE }
		];

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
				var response = {
					error: true,
					reason: "bad_response",
					url : requestURI,
					xhr: this.xhr,
					status: this.xhr ? this.xhr.status : 0
				};

				BX.onCustomEvent("onFrameDataRequestFail", [response]);
			}
		});
	};

	BX.frameCache.onFrameDataReceived = function(response)
	{
		var result = null;
		try
		{
			eval("result = " + response);
			this.frameData = result;
		}
		catch (e)
		{
			BX.ready(function() {
				setTimeout(function() {
					BX.onCustomEvent("onFrameDataRequestFail", [{
						error: true,
						reason: "bad_eval",
						response: response
					}]);
				}, 0);
			});

			return;
		}

		if (this.frameData && BX.type.isNotEmptyString(this.frameData.redirect_url))
		{
			window.location = this.frameData.redirect_url;
			return;
		}

		if (this.frameData && this.frameData.error === true)
		{
			BX.ready(BX.proxy(function() {
				setTimeout(BX.proxy(function() {
					BX.onCustomEvent("onFrameDataRequestFail", [this.frameData]);
				}, this), 0);
			}, this));

			return;
		}

		BX.frameCache.setCompositeVars(this.frameData);
		BX.frameCache.setPullConfigVars(this.frameData);
		BX.ready(BX.proxy(function() {
			this.handleResponse(this.frameData);
			this.tryUpdateSessid();
		}, this));
	};

	BX.frameCache.insertFromCache = function(resultSet, transaction)
	{
		if (!this.frameData)
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
		var useHash = this.lastReplacedBlocks.length != 0;

		for (var i = 0; i < blocks.length; i++)
		{
			var block = blocks[i];
			BX.onCustomEvent("onBeforeDynamicBlockUpdate", [block, fromCache]);

			if (block.PROPS.AUTO_UPDATE === false)
			{
				continue;
			}

			var skip = false;
			if (useHash)
			{
				for (var j = 0; j < this.lastReplacedBlocks.length; j++)
				{
					if (this.lastReplacedBlocks[j].ID == block.ID && this.lastReplacedBlocks[j].HASH == block.HASH)
					{
						skip = true;
						break;
					}
				}
			}

			if (!skip)
			{
				this.processData(block)
			}
		}

		BX.onCustomEvent("onFrameDataProcessed", [blocks, fromCache]);
		this.lastReplacedBlocks = blocks;
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
			this.cacheDataBase = BX.dataBase.create({
				name: "Database",
				displayName: "BXCacheBase",
				capacity: 1024 * 1024 * 4,
				version: "1.0"
			});

			if(this.cacheDataBase != null)
			{
				this.cacheDataBase.createTable(this.tableParams);
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
				props = JSON.stringify(props)
			}

			this.cacheDataBase.getRows(
				{
					tableName: this.tableParams.tableName,
					filter: {id: id},
					success: BX.proxy(
						function(res)
						{
							if (res.items.length > 0)
							{
								this.cacheDataBase.updateRows(
									{
										tableName: this.tableParams.tableName,
										updateFields: {
											content: content,
											hash: hash,
											props : props
										},
										filter: {
											id: id
										},
										fail:function(e){
											//console.error("Update cache error: ", e);
										}

									}
								);
							}
							else
							{
								this.cacheDataBase.addRow(
									{
										tableName: this.tableParams.tableName,
										insertFields:
										{
											id: id,
											content: content,
											hash: hash,
											props : props
										}
									}
								);
							}

						}, this),
					fail: BX.proxy(function(e)
					{
						this.cacheDataBase.addRow
						(
							{
								tableName: this.tableParams.tableName,
								insertFields: {
									id: id,
									content: content,
									hash: hash,
									props : props
								},
								fail: function(error)
								{
									//console.error("Add cache error: ", error);
								}
							}
						);
					}, this)
				}
			);
		}


	};

	BX.frameCache.readCacheWithID = function(id, callback)
	{
		if(BX.frameCache.openDatabase())
		{
			this.cacheDataBase.getRows
			(
				{
					tableName: this.tableParams.tableName,
					filter: {id: id},
					success: BX.proxy(callback, this)
				}
			);
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

