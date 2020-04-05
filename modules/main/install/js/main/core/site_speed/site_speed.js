BX.namespace("BX.Main.SiteSpeed");

BX.Main.SiteSpeed = (function() {
	"use strict";

	var SiteSpeed = function(privateKey, accountId) {
		this.privateKey = privateKey;
		this.accountId = accountId;
		this.statServerUrl = document.location.protocol + "//www.1c-bitrix.ru/buy_tmp/ba.php";
		this.invervals = [
			{ title : BX.message("JS_SITE_SPEED_VERY_FAST"), color: "#d0df6c", inverval : 500},
			{ title : BX.message("JS_SITE_SPEED_FAST"), color: "#b3c636", inverval : 1000},
			{ title : BX.message("JS_SITE_SPEED_NOT_FAST"), color: "#f0d53e", inverval : 1500},
			{ title : BX.message("JS_SITE_SPEED_SLOW"), color: "#f0b23e", inverval : 2000},
			{ title : BX.message("JS_SITE_SPEED_VERY_SLOW"), color: "#f2921e", inverval : 2500}
		];
	};

	SiteSpeed.prototype.drawIndicator = function(data, divId)
	{
		if (!data || data.result === false || !BX.type.isNumber(data.cnt))
		{
			return null;
		}

		var sitePageIndex = BX.type.isNumber(data["p50"]) ? data["p50"] : -1;

		BX(divId).style.display = "block";

		var graphs = [];
		var dataProvider = { label : ""};

		var start = 0;
		var maxInterval = 0;

		for (var i = 0; i < this.invervals.length; i++)
		{
			maxInterval = Math.max(maxInterval, this.invervals[i].inverval);
			graphs.push({
				"fillAlphas": 0.9,
				"fontSize": 11,
				"labelText": this.invervals[i].title,
				"lineAlpha": 0.5,
				"color": "#000000",
				"lineColor": this.invervals[i].color,
				"title": this.invervals[i].title,
				"type": "column",
				"valueField": i
			});

			var end = this.invervals[i].inverval - start;
			start = this.invervals[i].inverval;
			dataProvider[i] = end;
		}

		var chart = AmCharts.makeChart(divId, {
			"type": "serial",
			"theme": "none",
			"rotate": true,
			"dataProvider": [dataProvider],
			"valueAxes": [{
				"id": "intervals",
				"stackType": "100%",
				"axisAlpha": 0.5,
				"gridAlpha": 0,
				"labelFunction" : function(value, valueText, valueAxis) {
					var label = "";
					if (value === 0)
					{
						label = "0";
					}
					else
					{
						//TODO
						if (value % 20)
						{
							return "";
						}
						else
						{
							label = BX.Main.SiteSpeed.formatMilliseconds(value/100 * maxInterval, 1);
						}
					}

					return label + " " + BX.message("JS_SITE_SPEED_SECONDS_UNIT");
				}
			}],

			"categoryField" : "label",
			"categoryAxis": {
				"gridPosition": "start",
				"axisAlpha": 0,
				"gridAlpha": 0,
				"position": "left"
			},

			"graphs": graphs,
			"marginTop": 0,
			"marginRight": 15,
			"marginLeft": 10,
			"marginBottom": 30,
			"autoMargins": false,

			"chartCursor": {
				enabled: false
			}
		});

		if (sitePageIndex > 0 && sitePageIndex < maxInterval)
		{
			var valueAxis = chart.getValueAxisById("intervals");
			var guide = new AmCharts.Guide();
			guide.value = (sitePageIndex / maxInterval * 100);
			guide.lineColor = "#000000";
			guide.lineAlpha = 1;
			guide.fillAlpha = 0.2;
			guide.fillColor = "#000000";
			guide.dashLength = 4;
			guide.inside = true;
			guide.above = true;
			guide.lineThickness = 2;
			guide.position = "top";
			valueAxis.addGuide(guide);
			chart.validateNow();
		}

		return chart;
	};

	SiteSpeed.prototype.drawHisto = function(data, divId)
	{
		if (!data || data.result === false || !BX.type.isNumber(data.cnt))
		{
			return null;
		}

		BX(divId).style.display = "block";

		var guideCategory = null;
		for (var i = 0; i < data["steps"].length; i++)
		{
			if (data["p50"] < data["steps"][i])
			{
				guideCategory = data["steps"][i-1];
				break;
			}
		}

		if (guideCategory === null)
		{
			guideCategory = data["steps"][data["steps"].length - 1];
		}

		var dataProvider = [];
		for (var key in data["histo"])
		{
			var cnt = data["histo"][key]["cnt"];
			var cmpCnt = data["histo"][key]["cmpCnt"];
			var cmpPercent = cnt > 0 ? (cmpCnt/cnt*100).toFixed(1) : 0;
			dataProvider.push({
				cnt : cnt,
				title : key,
				cmpCnt : cmpCnt,
				cmpPercent : cmpPercent
			});
		}

		var color = this.invervals[this.invervals.length-1].color;
		for (i = 0; i < this.invervals.length; i++)
		{
			if (data["p50"] < this.invervals[i].inverval)
			{
				color = this.invervals[i].color;
				break;
			}
		}

		var histo = AmCharts.makeChart(divId, {
			"type": "serial",
			"theme": "none",
			"pathToImages":"/bitrix/js/main/amcharts/3.21/images/",
			"dataProvider": dataProvider,
			"startDuration": 1,
			"balloon": {
				"maxWidth": 700,
				"textAlign": "left"
			},
			"graphs": [{
				"balloonText": "<b>[[category]]: [[value]]</b>",
				"balloonFunction" : BX.proxy(function(dataItem, amGraph) {

					var value = dataItem.values["value"];
					var percent = data["cnt"] > 0 ? (value/data["cnt"]*100).toFixed(1) : 0;

					return "<b>" + BX.message("JS_SITE_SPEED_HITS") + ": " +
						value + " (" + percent +"%)</b><br><br>" +
						this.getStatTable(data["histo"][dataItem["category"]]["hits"], "dit");

				}, this),
				"colorField": "color",
				"fillAlphas": 0.9,
				"lineAlpha": 0.2,
				"lineColor" : color,
				"type": "column",
				"valueField": "cnt"
			}],
			"chartCursor": {
				"categoryBalloonEnabled": false,
				"cursorAlpha": 0,
				"zoomable": false
			},
			"valueAxes": [{
				"precision": 0
			}],
			"categoryField": "title",
			"categoryAxis": {
				"gridPosition": "start",
				"labelFunction" : function(value, valueText, valueAxis) {
					return BX.Main.SiteSpeed.formatMilliseconds(value, 1) + " " + BX.message("JS_SITE_SPEED_SECONDS_UNIT");
				},
				"guides": [{
					category : guideCategory,
					lineColor: "#000000",
					lineAlpha: 1,
					fillAlpha: 0.2,
					fillColor: "#CC0000",
					dashLength: 2,
					inside: true,
					above: true,
					lineThickness : 2,
					position: "top",
					labelRotation: 90,
					label: BX.message("JS_SITE_SPEED_INDEX")
				}]
			},

			"amExport":{}
		});

		if (BX.type.isNumber(data.compositeHits) && data.compositeHits > 0)
		{
			histo.addGraph({
				"bullet": "round",
				"lineThickness": 3,
				"bulletSize": 7,
				"bulletBorderAlpha": 1,
				"bulletColor": "#FFFFFF",
				"useLineColorForBulletBorder": true,
				"bulletBorderThickness": 3,
				"fillAlphas": 0,
				"lineAlpha": 1,
				"title": "Composite",
				"valueField": "cmpCnt",
				"balloonFunction" : function(dataItem, amGraph) {
					return BX.message("JS_SITE_SPEED_COMPOSITE_HITS") + ": " +
						dataItem.dataContext["cmpCnt"] +
						" (" + dataItem.dataContext["cmpPercent"] + "%)";
				}
			});
		}

		return histo;
	};

	SiteSpeed.prototype.drawGraph = function(data, divId)
	{
		if (!BX.type.isArray(data) || data.length < 1)
		{
			return null;
		}

		BX(divId).style.display = "block";

		AmCharts.shortMonthNames = [];
		for (var i = 1; i <= 12; i++)
		{
			AmCharts.shortMonthNames.push(BX.message("MON_" + i));
		}

		return AmCharts.makeChart(divId, {
			"type": "serial",
			"theme": "none",
			"pathToImages": "/bitrix/js/main/amcharts/3.21/images/",
			"dataDateFormat" : "YYYY-MM-DD JJ:NN:SS",
			"valueAxes": [{
				"stackType": "regular",
				"axisAlpha": 0,
				"position": "left",
				"labelFunction" : function(value, valueText, valueAxis) {
					if (value == 0)
					{
						return 0;
					}
					return BX.Main.SiteSpeed.formatMilliseconds(value, 2) + " " + BX.message("JS_SITE_SPEED_SECONDS_UNIT");
				}
			}],
			"legend": {
				"equalWidths": true,
				"position": "top",
				"valueAlign": "left",
				"markerType": "bubble",
				"switchType": "v"
			},
			"categoryField": "date_datetime",
			"categoryAxis": {
				"parseDates": true,
				"equalSpacing" : false,

				"minPeriod" : "ss",
				"dateFormats": [{
					period: 'fff',
					format: 'JJ:NN'
				}, {
					period: 'ss',
					format: 'JJ:NN'
				}, {
					period: 'mm',
					format: 'JJ:NN'
				}, {
					period: 'hh',
					format: 'JJ:NN'
				}, {
					period: 'DD',
					format: 'MMM D'
				}, {
					period: 'WW',
					format: 'MMM D'
				}, {
					period: 'MM',
					format: 'MMM'
				}, {
					period: 'YYYY',
					format: 'MMM'
				}]
			},
			"dataProvider": data,
			"balloon": {
				"maxWidth": 700,
				"textAlign": "left"
			},
			"graphs": [
				{
					"hidden": true,
					"fillAlphas": 0.6,
					"lineAlpha": 0.4,
					"title": "DNS",
					"valueField": "dns",
					"balloonText": ""
				},
				{
					"hidden": true,
					"fillAlphas": 0.6,
					"lineAlpha": 0.4,
					"title": BX.message("JS_SITE_SPEED_TCP"),
					"valueField": "tcp",
					"balloonText": ""
				},
				{
					"fillAlphas": 0.6,
					"lineAlpha": 0.4,
					"title": BX.message("JS_SITE_SPEED_RESPONSE_TIME"),
					"valueField": "srt",
					"balloonText": ""

				},
				{
					"hidden" : true,
					"fillAlphas": 0.6,
					"lineAlpha": 0.4,
					"title": BX.message("JS_SITE_SPEED_DOWNLOAD_TIME"),
					"valueField": "pdt",
					"balloonText": ""
				},
				{
					"switchable" : false,
					"fillAlphas": 0.6,
					"lineAlpha": 0.4,
					"title": BX.message("JS_SITE_SPEED_PROCESSING_TIME"),
					"valueField": "prc",
					"balloonText": ""
				},
				{
					"stackable" : false,
					"switchable" : true,
					"lineAlpha": 0.4,
					"title": BX.message("JS_SITE_SPEED_INTERACTIVE_TIME"),
					"valueField": "dit",

					"bullet": "round",
					"lineThickness": 3,
					"bulletSize": 7,
					"bulletBorderAlpha": 1,
					"useLineColorForBulletBorder": true,
					"bulletBorderThickness": 3,

					"balloonFunction" : BX.proxy(function(dataItem, amGraph) {

						var attrs = [
							["dit", BX.message("JS_SITE_SPEED_INTERACTIVE_TIME")],
							["srt", BX.message("JS_SITE_SPEED_RESPONSE_TIME")],
							["dns", "DNS"],
							["tcp", BX.message("JS_SITE_SPEED_TCP")],
							["pdt", BX.message("JS_SITE_SPEED_DOWNLOAD_TIME")],
							["prc", BX.message("JS_SITE_SPEED_PROCESSING_TIME")]
						];

						var result = "<div class=\"site-speed-balloon-stat\">";
						for (var i = 0; i < attrs.length; i++)
						{
							var name = attrs[i][1];
							var value = typeof(dataItem.dataContext[attrs[i][0]]) !== "undefined" ? dataItem.dataContext[attrs[i][0]] : -1;
							result += "<div class=\"site-speed-balloon-stat-item\"><b>" +
										name + ":</b>&nbsp;" + (value === 0 ? 0 : BX.Main.SiteSpeed.formatMilliseconds(value, 3)) +
										" " + BX.message("JS_SITE_SPEED_SECONDS_UNIT") +
										"</div>";
						}

						result += "</div>";

						result += this.getStatTable(dataItem.dataContext["hits"], "dit");
						return result;

					}, this)
				}
			],

			"plotAreaBorderAlpha": 0,
			"chartCursor": {
				"cursorAlpha": 1,
				"zoomable" : true,
				"categoryBalloonEnabled": false
			}
		});
	};

	SiteSpeed.prototype.getHistoData = function(host, callback, callbackFailure)
	{
		BX.ajax({
			method: "POST",
			dataType: "json",
			url: this.statServerUrl,
			data : {
				license : this.privateKey,
				op : "hit_attr_distrib",
				attr: "dit",
				domain : host,
				aid: this.accountId,
				tmz: new Date().getTimezoneOffset()
			},
			onsuccess: callback,
			onfailure: callbackFailure
		});
	};

	SiteSpeed.prototype.getLastHits = function(host, callback, callbackFailure)
	{
		BX.ajax({
			method: "POST",
			dataType: "json",
			url: this.statServerUrl,
			data : {
				license: this.privateKey,
				op: "domain_last_hits",
				domain: host,
				aid: this.accountId,
				tmz: new Date().getTimezoneOffset()
			},
			onsuccess: callback,
			onfailure: callbackFailure
		});
	};

	SiteSpeed.prototype.getStatTable = function(hits, sortParam)
	{
		if (!BX.type.isArray(hits))
		{
			return "";
		}

		var table = '<table class="site-speed-hits-table">';

		table +=
			'<tr>' +
				'<th class="site-speed-page-column">' + BX.message("JS_SITE_SPEED_PAGE") + '</th>' +
				'<th class="site-speed-interactive-column">' + BX.message("JS_SITE_SPEED_INTERACTIVE") + '</th>' +
				'<th class="site-speed-response-column">' + BX.message("JS_SITE_SPEED_RESPONSE") + '</th>' +
				'<th class="site-speed-processing-column">' + BX.message("JS_SITE_SPEED_PROCESSING") + '</th>' +
				'<th class="site-speed-composite-column">' + BX.message("JS_SITE_SPEED_COMPOSITE") + '</th>' +
				'</tr>';

		hits.sort(function(a, b) {

			if (parseInt(a[sortParam]) < parseInt(b[sortParam]))
			{
				return 1;
			}

			if (parseInt(a[sortParam]) > parseInt(b[sortParam]))
			{
				return -1;
			}

			return 0;
		});

		for (var i = 0; i < hits.length; i++)
		{
			var hit = hits[i];
			table +=
				'<tr>' +
					'<td class="site-speed-page-column">' + (i+1) + '.&nbsp;' + BX.util.htmlspecialchars(decodeURIComponent(hit["ru"])) + '</td>' +
					'<td class="site-speed-interactive-column">' + BX.Main.SiteSpeed.formatMilliseconds(hit["dit"], 3) + '</td>' +
					'<td class="site-speed-response-column">' + BX.Main.SiteSpeed.formatMilliseconds(hit["srt"], 3) + '</td>' +
					'<td class="site-speed-processing-column">' + BX.Main.SiteSpeed.formatMilliseconds(hit["prc"], 3) + '</td>' +
					'<td class="site-speed-composite-column">' + (hit["com"] == 1 ? BX.message("JS_SITE_SPEED_COMPOSITE_YES") : BX.message("JS_SITE_SPEED_COMPOSITE_NO")) + '</td>' +
					'</tr>';
		}
		table += '</table>';

		return table;
	};

	SiteSpeed.prototype.getInvervals = function() {
		return this.invervals;
	};

	SiteSpeed.prototype.getInverval = function(index) {
		for (var i = 0; i < this.invervals.length; i++)
		{
			if (index < this.invervals[i].inverval)
			{
				return this.invervals[i];
			}
		}

		return this.invervals[this.invervals.length-1];
	};

	SiteSpeed.formatMilliseconds = function(milliseconds, precision) {

		milliseconds = parseInt(milliseconds, 10);
		if (!BX.type.isNumber(milliseconds) || milliseconds < 0)
		{
			return -1;
		}
		else if (milliseconds === 0)
		{
			return 0;
		}

		precision = precision || 2;
		return (milliseconds/1000).toFixed(precision);
	};

	return SiteSpeed;

})();
