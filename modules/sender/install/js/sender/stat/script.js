;(function (window)
{
	if (!window.BX)
	{
		window.BX = {};
	}

	if (!window.BX.Sender)
	{
		window.BX.Sender = {};
	}

	if (window.BX.Sender.Statistics)
	{
		return;
	}

	/*
	* Base controller for statistics page
	* */
	BX.Sender.Statistics = function (){};
	BX.Sender.Statistics.prototype = {
		filters: [],
		blocks: [],
		filterUrl: '/bitrix/admin/sender_statistics.php',
		onResponseData: function (data)
		{
			BX.onCustomEvent(this, 'onDataLoad', [data]);
		},

		onScroll: function ()
		{
			this.callBlockFunction('onScroll');
		},

		callBlockFunction: function (functionName, params)
		{
			params = params || {};
			this.blocks.forEach(function (block) {
				block[functionName](params);
			}, this);
		},

		getFilterQueryData: function ()
		{
			var queryData = {};
			this.filters.forEach(function (filter) {
				if (!filter.value)
				{
					return;
				}

				queryData[filter.name] = filter.value;
			});

			return queryData;
		},

		filter: function ()
		{
			// animation
			BX.onCustomEvent(this, 'onDataRequest', []);

			// ajax query
			var queryData = this.getFilterQueryData();
			queryData.action = 'get_data';
			queryData.sessid = BX.bitrix_sessid();
			BX.ajax({
				url: this.filterUrl,
				method: 'POST',
				data: queryData,
				dataType: 'json',
				onsuccess: BX.proxy(this.onResponseData, this)
			});
		},

		getFilter: function (name)
		{
			var filtered = this.filters.filter(function (filter) {
				return filter.name == name;
			});
			return filtered.length > 0 ? filtered[0] : null;
		},

		addBlocks: function (blockNames)
		{
			blockNames.forEach(function (blockName) {
				var className = 'StatisticsBlock' + blockName;
				if (!BX.Sender.hasOwnProperty(className))
				{
					throw new Error('Class "BX.Sender.' + className + '" not found for block "' + blockName + '"');
				}
				var block = new BX.Sender[className]();
				this.blocks.push(block);
			}, this);
		},

		addFilters: function (filterDataList)
		{
			filterDataList.forEach(function (filterData) {
				filterData.caller = this;
				filterData.onFilter = BX.proxy(this.filter, this);
				this.filters.push(new BX.Sender.StatisticsFilter(filterData));
			}, this);
		},

		init: function (params)
		{
			this.mess = params.mess;
			this.context = params.context;

			var itemInitParams = params;
			itemInitParams.caller = this;
			this.callBlockFunction('onInit', itemInitParams);

			BX.bind(window, 'scroll', BX.proxy(BX.throttle(this.onScroll, 350),this));
		}
	};

	/*
	 * Controller for posting statistics page
	 * */
	var postingsStats = function (params)
	{
		this.load = function (params)
		{
			this.instance = new BX.Sender.Statistics();
			this.instance.filterUrl = '/bitrix/admin/sender_mailing_stat.php';
			this.instance.addBlocks(this.getBlocks(params));
			this.instance.addFilters(this.getFilters(params));
			this.instance.init(params);
		};

		this.getBlocks = function (params)
		{
			return ['Counters', 'ClickMap', 'ReadByTime'];
		};

		this.getFilters = function (params)
		{
			// filter by chainId
			var popupItems = params.chainList.map(function (chain) {
				return {
					id: chain.ID,
					title: chain.NAME,
					className: 'bx-sender-stat-popup-item-chain',
					text: '' +
						'<span class="bx-sender-stat-popup-item-chain-date">' + BX.util.htmlspecialchars(chain.DATE_SENT_FORMATTED) + '</span>' +
						'<span class="bx-sender-stat-popup-item-chain-name">' + BX.util.htmlspecialchars(chain.NAME) + '</span>'
				};
			}, this);
			popupItems.push({delimiter: true});
			popupItems.push({
				id: 'all',
				text: params.mess.allPostings,
				onclick: BX.proxy(function() {
					var url = '/bitrix/admin/sender_mailing_chain_admin.php';
					url += '?MAILING_ID=' + parseInt(this.instance.getFilter('mailingId').value);
					window.location.href = url;
				}, this)
			});

			return [
				{
					name: 'chainId',
					value: params.chainId,
					node: BX('sender_stat_filter_chain_id'),
					items: popupItems
				},
				{
					name: 'mailingId',
					value: params.mailingId
				},
				{
					name: 'postingId',
					value: params.postingId
				}
			];
		};
	};

	BX.Sender.PostingsStats = new postingsStats();


	/*
	 * Controller for global statistics page
	 * */
	var globalStats = function (params)
	{
		this.load = function (params)
		{
			this.instance = new BX.Sender.Statistics();
			this.instance.addBlocks(this.getBlocks(params));
			this.instance.addFilters(this.getFilters(params));
			this.instance.init(params);
		};

		this.getBlocks = function (params)
		{
			return ['Counters', 'Efficiency', 'ChainList', 'CountersDynamic'];
		};

		this.getFilters = function (params)
		{
			return params.filters.map(function (filter) {

				var popupItems = filter.list.map(function (item) {
					var name = BX.util.htmlspecialchars(item.NAME || '');
					return {
						id: item.ID,
						text: name,
						title: name
					};
				}, this);

				return {
					name: filter.name,
					value: filter.value,
					node: BX('sender_stat_filter_' + filter.name.toLowerCase()),
					items: popupItems
				};

			}, this);
		};
	};

	BX.Sender.GlobalStats = new globalStats();

	/*
	 * Filter object for filtrating data
	 * */
	BX.Sender.StatisticsFilter = function (params)
	{
		this.caller = params.caller;
		this.name = params.name;
		this.value = params.value;
		this.node = params.node || null;
		this.items = params.items || null;
		this.onFilter = params.onFilter || null;
		this.popup = null;

		if (this.node)
		{
			BX.bind(this.node, 'click', BX.proxy(this.show, this));
		}

		if (this.items)
		{
			this.items.filter(function (item) {
				var value = (this.value == '' || this.value === null) ? 'all' : this.value;
				return value == item.id;
			}, this).forEach(this.setCurrentItem, this);
		}
	};
	BX.Sender.StatisticsFilter.prototype = {
		show: function ()
		{
			if (!this.popup)
			{
				var popupItems = this.items.map(function (item) {
					if (!item.onclick)
					{
						item.onclick = BX.proxy(this.onClick, this);
					}

					return item;
				}, this);

				this.popup = this.createPopup('sender_stat_filter_' + this.name, this.node, popupItems);
			}

			if (this.popup.show)
			{
				this.popup.show();
			}
			else
			{
				this.popup.popupWindow.show();
			}
		},

		setCurrentItem: function (item)
		{
			if (!this.node)
			{
				return;
			}

			this.node.innerText = item.title;
			this.value = item.id;
		},

		onClick: function (e, item)
		{
			this.setCurrentItem(item);

			this.popup.close();

			if (this.onFilter)
			{
				this.onFilter();
			}
		},

		createPopup: function(popupId, button, items, params)
		{
			params = params || {};
			return BX.PopupMenu.create(
				popupId,
				button,
				items,
				{
					autoHide: true,
					offsetLeft: params.offsetLeft ? params.offsetLeft : -21,
					offsetTop: params.offsetTop ? params.offsetTop : -3,
					angle:
					{
						position: "top",
						offset: 42
					},
					events:
					{
						//onPopupClose : BX.delegate(this.onPopupClose, this)
					}
				}
			);
		},

		val: function (value)
		{
			if (typeof value != 'undefined')
			{
				this.value = value;
			}

			return this.value;
		}
	};


	/*
	 * Base object for data block
	 * */
	BX.Sender.StatisticsBlock = function ()
	{

	};
	BX.Sender.StatisticsBlock.prototype = {
		name: 'default',
		attributeBlock: 'data-bx-block',
		attributePoint: 'data-bx-point',
		attributeLoader: 'data-bx-view-loader',
		attributeDataView: 'data-bx-view-data',
		pointNodes: null,
		blockNodeList: null,
		onInit: function (params)
		{
			this.caller = params.caller;

			var blockNodeList;
			if (BX.Sender.StatisticsBlock.prototype.blockNodeList === null)
			{
				blockNodeList = this.caller.context.querySelectorAll('[' + this.attributeBlock + ']');
				blockNodeList = BX.convert.nodeListToArray(blockNodeList);
				BX.Sender.StatisticsBlock.prototype.blockNodeList = blockNodeList;
			}
			else
			{
				blockNodeList = BX.Sender.StatisticsBlock.prototype.blockNodeList;
			}

			this.context = blockNodeList.filter(function (blockNode) {
				return blockNode.getAttribute(this.attributeBlock) == this.name;
			}, this)[0];

			if (this.context)
			{
				this.loaderNode = this.context.querySelector('[' + this.attributeLoader + ']');
				this.dataViewNode = this.context.querySelector('[' + this.attributeDataView + ']');
			}

			if (this.pointNodes === null)
			{
				this.pointNodes = this.context.querySelectorAll('[' + this.attributePoint + ']');
				this.pointNodes = BX.convert.nodeListToArray(this.pointNodes);
			}

			BX.addCustomEvent(this.caller, 'onDataLoad', BX.proxy(this.onDataLoad, this));
			BX.addCustomEvent(this.caller, 'onDataRequest', BX.proxy(this.fadeOut, this));
			BX.addCustomEvent(this.caller, 'onScroll', BX.proxy(this.onScroll, this));

			this.init(params);
		},
		onScroll: function ()
		{
		},
		fadeOut: function ()
		{
			if (this.loaderNode)
			{
				this.dataViewNode.style.display = 'none';
				this.loaderNode.style.display = '';
			}

			this.pointNodes.forEach(function (pointNode) {
				if (this.getDisplayDataType(pointNode))
				{
					return;
				}
				BX.addClass(pointNode, 'bx-sender-loader');
				var loaderNode = document.createElement('SPAN');
				loaderNode.className = 'bx-sender-loader-sm';
				pointNode.innerHTML = '';
				pointNode.appendChild(loaderNode);
			}, this);
		},
		fadeIn: function ()
		{
			if (this.loaderNode)
			{
				this.loaderNode.style.display = 'none';
				this.dataViewNode.style.display = '';
			}
		},
		onDataLoad: function (data)
		{
			this.loadData(data);
			this.fadeIn();
		},
		init: function (config)
		{

		},
		loadData: function (data)
		{

		},
		getDisplayDataType: function (node)
		{
			var dataPath = node.getAttribute(this.attributePoint);
			var act = dataPath.split(':');
			return act[1] ? act[1] : null;
		},
		setDisplayData: function (node, data)
		{
			var dataPath = node.getAttribute(this.attributePoint);
			var act = dataPath.split(':');
			var source = act[0].split('/');
			var type = act[1];

			var value;
			source.forEach(function (key) {
				if (value === null)
				{
					return;
				}

				if (typeof value == 'undefined')
				{
					if (!data.hasOwnProperty(key))
					{
						value = null;
						return;
					}

					value = data[key];
				}
				else
				{
					if (!value.hasOwnProperty(key))
					{
						value = null;
						return;
					}
					value = value[key];
				}
			});

			switch (type)
			{
				case 'width':
					node.style.width = parseInt(parseFloat(value) * 100) + '%';
					break;
				case 'href':
					node.href = BX.util.strip_tags(value);
					break;
				default:
					node.innerText = value;
					break;
			}

			BX.removeClass(node, 'bx-sender-loader');
		},
		updateDisplayData: function (data)
		{
			this.pointNodes.forEach(function (pointNode) {
				this.setDisplayData(pointNode, data);
			}, this);
		}
	};

	/*
	 * Utility for inherit base object
	 * */
	function extendItem(functions)
	{
		var f = function(){};
		BX.extend(f, BX.Sender.StatisticsBlock);
		for (var functionName in functions)
		{
			if (!functions.hasOwnProperty(functionName))
			{
				continue;
			}

			f.prototype[functionName] = functions[functionName];
		}

		return f;
	}

	/*
	 * Click map block
	 * */
	BX.Sender.StatisticsBlockClickMap = extendItem({
		name: 'ClickMap',
		init: function (params)
		{
			this.linkParams = params.posting.linkParams || '';
			this.clickList = params.clickList;
			this.frameNode = this.context.querySelector('[data-bx-click-map]');
			BX.bind(this.frameNode, 'load',  BX.proxy(this.draw, this));

			this.isNodeReloaded = false;
			this.onScroll();
		},
		onScroll: function ()
		{
			if (!BX.LazyLoad.isElementVisibleOnScreen(this.context))
			{
				return;
			}

			if (this.isNodeReloaded)
			{
				return;
			}

			this.reloadFrame();
		},
		reloadFrame: function ()
		{
			this.fadeOut();

			var source = this.caller.filterUrl;
			source += '?action=get_template&ID=' + this.caller.getFilter('chainId').value;
			source += '&sessid=' + BX.bitrix_sessid();
			source += '&r=' + 1*(new Date());
			this.frameNode.src = source;
			this.isNodeReloaded = true;
		},
		loadData: function (data)
		{
			this.isNodeReloaded = false;
			this.linkParams = data.posting.linkParams || '';
			this.clickList = data.clickList;
			this.onScroll();
		},
		draw: function ()
		{
			this.fadeIn();

			var frameDocument = this.frameNode.contentDocument;
			this.frameNode.style.height = frameDocument.body.scrollHeight + 'px';
			var heatMap = new BX.HeatMap({
				'document': frameDocument
			});

			var nodeList = BX.convert.nodeListToArray(
				frameDocument.body.querySelectorAll('a')
			);

			if (this.linkParams)
			{
				this.linkParams = this.linkParams.trim();
				if (this.linkParams.indexOf('?') === 0)
				{
					this.linkParams = this.linkParams.substring(1);
				}
				if (this.linkParams.indexOf('&') === 0)
				{
					this.linkParams = this.linkParams.substring(1);
				}
			}

			this.clickList.forEach(function (link) {
				var nodes = nodeList.filter(function (node) {
					var href = node.href;
					if (this.linkParams)
					{
						href += (href.indexOf('?') >=0 ? '&' : '?') + this.linkParams;
					}
					return href == link.URL;
				}, this);
				if (nodes.length == 0)
				{
					return;
				}

				heatMap.addItem({
					value: link.CNT,
					baloon: link.URL,
					anchorNode: nodes[0]
				});
			}, this);
			heatMap.draw();
		}
	});

	/*
	 * Counters block
	 * */
	BX.Sender.StatisticsBlockCounters = extendItem({
		name: 'Counters',
		init: function (params)
		{
			this.isNodeReloaded = false;
			this.onScroll();
		},
		/*
		fadeIn: function ()
		{
			//<span class="bx-sender-loader-sm"></span>
			this.updateDisplayData(data);
		},
		fadeOut: function ()
		{
			this.updateDisplayData(data);
		},
		*/
		loadData: function (data)
		{
			this.updateDisplayData(data);
		}
	});

	/*
	 * Chain list
	 * */
	BX.Sender.StatisticsBlockChainList = extendItem({
		name: 'ChainList',
		init: function (params)
		{
			this.chainList = params.chainList;
			this.isNodeReloaded = true;
			this.onScroll();

			this.postingsNode = this.context.querySelector('[data-bx-view-data-postings]');
			this.updateDisplayChainListContainer();
		},
		loadData: function (data)
		{
			this.updateDisplayChainList(data.chainList);
		},
		updateDisplayChainList: function (chainList)
		{
			var postingTemplate = BX('sender-stat-template-last-posting');
			postingTemplate = postingTemplate.innerHTML;
			var htmlList = chainList.map(function (chain) {
				var html = postingTemplate;
				for (var key in chain)
				{
					var val = BX.util.htmlspecialchars(chain[key]);
					html = html.replace(new RegExp("%" + key + "%",'g'), val);
				}
				return html;
			}, this);

			this.postingsNode.innerHTML = htmlList.join('');
			this.updateDisplayChainListContainer();
		},
		updateDisplayChainListContainer: function () {
			this.postingsNode.style.display = this.postingsNode.children.length > 0 ? '' : 'none';
		}
	});

	/*
	 * Efficiency
	 * */
	BX.Sender.StatisticsBlockEfficiency = extendItem({
		name: 'Efficiency',
		init: function (params)
		{
			this.isNodeReloaded = true;
			this.onScroll();

			this.efficiencyPointerNode = this.context.querySelector('[data-bx-view-data-eff]');
			this.efficiencyValueNode = this.context.querySelector('[data-bx-view-data-eff-val]');

			if (params.efficiency)
			{
				this.updateDisplayEfficiency(params.efficiency);
			}
		},
		loadData: function (data)
		{
			this.updateDisplayEfficiency(data.efficiency);
		},
		updateDisplayEfficiency: function (efficiency)
		{
			this.efficiencyPointerNode.style.left = efficiency.PERCENT_VALUE + '%';
			this.efficiencyValueNode.innerText = efficiency.VALUE + '%';
		}
	});

	/*
	 * Read by time block
	 * */
	BX.Sender.StatisticsBlockReadByTime = extendItem({
		name: 'ReadByTime',
		init: function (params)
		{
			this.readByTimeList = params.readByTimeList;

			this.isNodeReloaded = false;
			this.onScroll();
		},
		onScroll: function ()
		{
			if (!BX.LazyLoad.isElementVisibleOnScreen(this.context))
			{
				return;
			}

			if (this.isNodeReloaded)
			{
				return;
			}

			this.requestData();
		},

		requestData: function ()
		{
			this.fadeOut();
			this.isNodeReloaded = true;
			BX.ajax({
				url: this.caller.filterUrl,
				method: 'POST',
				data: {
					chainId: this.caller.getFilter('chainId').value,
					action: 'get_read_by_time',
					sessid: BX.bitrix_sessid()
				},
				dataType: 'json',
				onsuccess: BX.proxy(function (data){
					this.readByTimeList = data.readingByTimeList;
					this.draw();
				}, this)
			});
		},
		loadData: function (data)
		{
			this.isNodeReloaded = false;
			this.onScroll();
		},
		draw: function ()
		{
			this.fadeIn();

			if (this.chart)
			{
				this.chart.dataProvider = this.readByTimeList;
				this.chart.validateData();
				return;
			}

			this.chart = window.AmCharts.makeChart(this.dataViewNode, {
				"type": "serial",
				"theme": "light",
				"dataProvider": this.readByTimeList,
				"valueAxes": [ {
					"gridColor": "#FFFFFF",
					"gridAlpha": 0.2,
					"dashLength": 0,
					"labelFrequency": 2,
					"labelFunction": function (valueText) {
						//return valueText;
						if (parseFloat(valueText) == parseInt(valueText))
						{
							return parseInt(valueText);
						}
						return '';
					}
				} ],
				"gridAboveGraphs": true,
				"startDuration": 1,
				"graphs": [ {
					"balloonText": this.caller.mess.readByTimeBalloon
						.replace('%time%', '[[category]]')
						.replace('%cnt%', '<b>[[value]]</b>'),
					"fillAlphas": 0.8,
					"lineAlpha": 0.2,
					"type": "column",
					"valueField": "CNT_DISPLAY"
				} ],
				"chartCursor": {
					"categoryBalloonEnabled": false,
					"cursorAlpha": 0,
					"zoomable": false
				},
				"categoryField": "DAY_HOUR_DISPLAY",
				"categoryAxis": {
					"gridPosition": "start",
					"gridAlpha": 0,
					"tickPosition": "start",
					"tickLength": 20
				},
				"export": {
					"enabled": false
				}
			});
		}
	});


	/*
	 * Counters dynamic block
	 * */
	BX.Sender.StatisticsBlockCountersDynamic = extendItem({
		name: 'CountersDynamic',
		data: {},
		attributeChartNode: 'data-bx-chart',
		attributeTextView: 'data-bx-view-text',
		init: function (params)
		{
			this.data = params.countersDynamic || {};

			this.charts = this.context.querySelectorAll('[' + this.attributeChartNode + ']');
			this.charts = BX.convert.nodeListToArray(this.charts);
			this.charts = this.charts.map(function (chartNode) {
				return {
					name: chartNode.getAttribute(this.attributeChartNode),
					node: chartNode,
					loaderNode: chartNode.querySelector('[' + this.attributeLoader + ']'),
					dataViewNode: chartNode.querySelector('[' + this.attributeDataView + ']'),
					textViewNode: chartNode.querySelector('[' + this.attributeTextView + ']'),
					instance: null
				};
			}, this);

			this.isNodeReloaded = false;
			this.onScroll();
		},
		onScroll: function ()
		{
			if (!BX.LazyLoad.isElementVisibleOnScreen(this.context))
			{
				return;
			}

			if (this.isNodeReloaded)
			{
				return;
			}

			this.requestData();
		},
		fadeOut: function ()
		{
			this.charts.forEach(function (chart) {
				if (chart.loaderNode)
				{
					chart.textViewNode.style.display = 'none';
					chart.dataViewNode.style.display = 'none';
					chart.loaderNode.style.display = '';
				}
			});
		},
		fadeIn: function ()
		{
			this.charts.forEach(function (chart) {
				if (chart.loaderNode)
				{
					chart.textViewNode.style.display = 'none';
					chart.loaderNode.style.display = 'none';
					chart.dataViewNode.style.display = '';
				}
			});
		},
		showText: function (chart)
		{
			chart.loaderNode.style.display = 'none';
			chart.dataViewNode.style.display = 'none';
			chart.textViewNode.style.display = '';
		},
		requestData: function ()
		{
			this.fadeOut();
			this.isNodeReloaded = true;

			// ajax query
			var queryData = this.caller.getFilterQueryData();
			queryData.action = 'get_counters_dynamic';
			queryData.sessid = BX.bitrix_sessid();

			BX.ajax({
				url: this.caller.filterUrl,
				method: 'POST',
				data: queryData,
				dataType: 'json',
				onsuccess: BX.proxy(function (data){
					this.data = data.countersDynamic;
					this.draw();
				}, this)
			});
		},
		loadData: function (data)
		{
			this.isNodeReloaded = false;
			this.onScroll();
		},
		draw: function ()
		{
			this.charts.forEach(this.drawChart, this);
		},
		drawChart: function (chart)
		{
			if (!this.data[chart.name])
			{
				this.showText(chart);
				return;
			}

			var data = this.data[chart.name];
			data.forEach(function (d) {
				if (BX.type.isNumber(d.DATE))
				{
					d.DATE = BX.date.format('j M', d.DATE);
				}
				else
				{
					d.DATE = 0;
				}
			});

			this.fadeIn();

			if (chart.instance)
			{
				chart.instance.dataProvider = data;
				chart.instance.validateData();
				return;
			}

			chart.instance = window.AmCharts.makeChart(chart.dataViewNode, {
				"type": "serial",
				"theme": "light",
				"dataProvider": data,
				"valueAxes": [ {
					"gridColor": "#FFFFFF",
					"gridAlpha": 0.2,
					"dashLength": 0,
					"unit": "%"
				} ],
				"gridAboveGraphs": true,
				"startDuration": 1,
				"graphs": [{
					"balloonText": '[[category]]: <b>[[value]]%</b>',
					//"fillAlphas": 0.8,
					//"lineAlpha": 0.2,
					"bullet": "round",
					"bulletSize": 8,
					"lineColor": "#637bb6",
					"lineThickness": 2,
					"type": "smoothedLine",
					"valueField": "PERCENT_VALUE_DISPLAY"
				}],
				"chartCursor": {
					"categoryBalloonEnabled": false,
					"cursorAlpha": 0,
					"zoomable": false
				},
				"categoryField": "DATE",
				"categoryAxis": {
					"gridPosition": "start",
					"gridAlpha": 0,
					"tickPosition": "start",
					"tickLength": 20
				},
				"export": {
					"enabled": false
				}
			});
		}
	});


})(window);