BX.ready(
	function ()
	{
		var chartAppHistory = null;
		var chartAppColors = [];
		var maxCountSegment = 30;
		var sortListID = [];
		var chartSelectedColors = [];
		var chartPresetColors = [
			'2FC6F6',
			'55D0E0',
			'9DCF00',
			'F7A700',
			'AF9245',
			'177CE2',
			'1EAE43',
			'D2000D',
			'828B95',
			'B7EB81',
			'2066B0',
			'B7EB81',
			'47E4C2',
			'525C69',
			'FD6ABB',
			'DEE1E8',
			'7AA5DA',
			'8C5725',
			'6B52CC',
			'FF799C'
		];

		function sortChartColumn(a, b)
		{
			if (a.end < b.end)
			{
				return 1;
			}
			if (a.end > b.end)
			{
				return -1;
			}
			return 0;
		}

		function sortChartColumnByListID(a, b)
		{
			a = sortListID.indexOf(a.id);
			b = sortListID.indexOf(b.id);
			if (a < b)
			{
				return -1;
			}
			if (a > b)
			{
				return 1;
			}
			return 0;
		}

		function setNoDataChart()
		{
			var container = BX('appHistoryChart');
			BX.cleanNode(container);

			var noDataBlock = BX.create('div', {
				attrs: {
					className: 'main-grid-empty-block'
				}
			});
			var noDataInner = BX.create('div', {
				attrs: {
					className: 'main-grid-empty-inner'
				}
			});
			BX.append(BX.create('div', {
				attrs: {
					className: 'main-grid-empty-image'
				}
			}), noDataInner);
			BX.append(BX.create('div', {
				attrs: {
					className: 'main-grid-empty-text'
				},
				text: BX.message('REST_STATISTIC_EMPTY_DATA')
			}), noDataInner);
			BX.append(noDataInner, noDataBlock);
			BX.append(noDataBlock, container);
		}

		function drawChartRestStatistic(dataProvider, idList)
		{
			if (typeof dataProvider == 'object')
			{
				var haveMaxCount = false;
				dataProvider.forEach(
					function (chart, i)
					{
						if (dataProvider[i].segments.length > 0 && !sortListID.length > 0)
						{
							dataProvider[i].segments.sort(sortChartColumn);
							dataProvider[i].segments.forEach(
								function (item, j)
								{
									sortListID[j] = item.id;
								}
							);
							idList.forEach(
								function (item, j)
								{
									if (sortListID.indexOf(item) === -1)
									{
										sortListID[sortListID.length] = item;
									}
								}
							);
						}
						else
						{
							dataProvider[i].segments.sort(sortChartColumnByListID);
						}

						dataProvider[i].segments.forEach(
							function (item, j)
							{
								if (j > maxCountSegment)
								{
									dataProvider[i].segments[maxCountSegment]['end'] += dataProvider[i].segments[j]['end'];
								}
								else
								{
									var color = '000000';
									if (typeof chartSelectedColors[item.id] !== 'undefined')
									{
										color = chartSelectedColors[item.id];
									}
									else
									{
										if (chartAppColors.length === 0)
										{
											chartAppColors = chartPresetColors.slice();
										}
										color = chartAppColors[0];
										chartAppColors.splice(0, 1);
										chartSelectedColors[item.id] = color;
									}
									dataProvider[i].segments[j].color = '#' + color;
								}
							}
						);
						if (dataProvider[i].segments.length > maxCountSegment)
						{
							haveMaxCount = true;
							dataProvider[i].segments[maxCountSegment].title = CRestStatisticComponent.langRemain;
							key = maxCountSegment+1;
							dataProvider[i].segments.splice(key, dataProvider[i].segments.length);
						}
					}
				);

				if (haveMaxCount === true)
				{
					var summSegment = [];
					var summRemain = [];
					dataProvider.forEach(
						function (chart, i)
						{
							summSegment[i] = 0;
							summRemain[i] = 0;
							if(dataProvider[i].segments.length > 0)
							{
								for (var j = 0; j < dataProvider[i].segments.length-1; j++)
								{
									summSegment[i] += dataProvider[i].segments[j].end;
								}
								summRemain[i] = dataProvider[i].segments[dataProvider[i].segments.length-1].end;
							}
						}
					);
					var maxRemain = Math.max.apply(null, summRemain);
					dataProvider.forEach(
						function (chart, i)
						{
							if(summSegment[i]*1.5 < maxRemain )
							{
								dataProvider[i].segments[dataProvider[i].segments.length-1].end += summSegment[i];
								dataProvider[i].segments[dataProvider[i].segments.length-1].title = CRestStatisticComponent.langLotOf;
								dataProvider[i].segments.splice(0,dataProvider[i].segments.length-1);
							}
						}
					);
				}

				if(dataProvider.length > 0)
				{
					if (chartAppHistory !== null)
					{
						chartAppHistory.dataProvider = dataProvider;
						chartAppHistory.validateData();
					}
					else
					{
						AmCharts.makeChart(
							"appHistoryChart",
							{
								"type": "gantt",
								"theme": "light",

								"valueAxis": {
									"stackType": "regular",
									"autoGridCount": true,
									"title": CRestStatisticComponent.langQuery,
									"axisAlpha": 0.3,
									"gridAlpha": 0
								},
								"balloon": {
									"borderThickness": 0,
									"shadowAlpha": 0,
									"lineAlpha": 0
								},
								"graph": {
									"fillAlphas": 0.8,
									"lineAlpha": 0.3,
									"labelColor": "#fff",
									"labelPosition": "middle",
									"lineColor": "#fff",
									"type": "column",
									"columnWidth": 0.5,
									"useLineColorForBulletBorder": false,
									"balloonFunction":
										function (item)
										{
											return '<div class="restStatisticChartBalloon">'
												+ '<div class="restStatisticChartBalloonTitle"><span>' + item.graph.customData.title + '</span></div>' +
												'<div class="restStatisticChartBalloonCount"><b>' + item.graph.customData.end + '</b></div>' +
												'</div>';
										}
								},
								"rotate": false,
								"categoryField": "category",
								"segmentsField": "segments",
								"colorField": "color",
								"startField": "start",
								"endField": "end",
								"chartCursor": {
									"cursorColor": "#55bb76",
									"valueBalloonsEnabled": false,
									"cursorAlpha": 0,
									"valueLineAlpha": 0.5,
									"valueLineBalloonEnabled": false,
									"valueLineEnabled": false,
									"zoomable": false,
									"valueZoomable": false
								},
								"categoryAxis": {
									"gridPosition": "start",
									"axisAlpha": 0,
									"gridAlpha": 0,
									"labelRotation": 45,
									"position": "left"
								},
								"dataProvider": dataProvider
							}
						);
					}
				}
				else
				{
					setNoDataChart();
				}
			}
			else
			{
				setNoDataChart();
			}
		}

		function ajaxDrawChartRestStatistic()
		{
			BX.ajax.runComponentAction(
				'bitrix:rest.statistic',
				'getChartData',
				{
					mode: 'class',
					signedParameters: CRestStatisticComponent.signetParameters
				}
			).then(
				function (response)
				{
					if (
						typeof response.data == 'object' &&
						typeof response.data.dataProvider == 'object' &&
						typeof response.data.idList == 'object'
					)
					{
						drawChartRestStatistic(response.data.dataProvider, response.data.idList);
					}
					else
					{
						setNoDataChart();
					}
				}
			);
		}

		BX.addCustomEvent(
			'BX.Main.Filter:apply',
			BX.delegate(
				function (command, params)
				{
					if (typeof CRestStatisticComponent == 'object' && CRestStatisticComponent.filterName == command)
					{
						ajaxDrawChartRestStatistic();
					}
					else
					{
						setNoDataChart();
					}
				}
			)
		);
		ajaxDrawChartRestStatistic();
	}
);