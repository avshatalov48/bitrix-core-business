;(function ()
{
	"use strict";
	/**
	 * @namespace BX.ActivityTileWidget
	 */
	BX.namespace('BX.ActivityTileWidget');

	/**
	 *
	 * @param options
	 * @constructor
	 */
	BX.ActivityTileWidget = function (options)
	{
		this.container = options.renderTo;
		this.options = options;
		this.items = options.items;
		this.days = options.labelY;
		this.hours = options.labelX;
		this.popup = null;
		this.item = null;
		this.scaleVertical = null;
		this.scaleHorizontal = null;
		this.handlerHybridWidget = null;
		this.handlerHoryzontalWidget = null;
		this.handlerVerticalWidget =  null;
	};

	BX.ActivityTileWidget.prototype =
		{
			/**
			 *
			 * @param {Object} items
			 * @returns {number}
			 */
			getMaxActivity: function (items)
			{

				var arrayItems = this.items;

				if(items !== undefined)
				{
					arrayItems = items;
				}

				var maxActivity = 0;

				for (var item in arrayItems)
				{
					if(arrayItems.hasOwnProperty(item))
					{
						arrayItems[item].active > maxActivity ? maxActivity = arrayItems[item].active : null;
					}
				}

				return maxActivity;
			},

			/**
			 *
			 * @returns {DocumentFragment|*}
			 */
			getDayScale: function ()
			{
				var daysBlock = document.createDocumentFragment();
				var className = 'reports-activity-day';
				var classDay;

				for (var day in this.days)
				{
					if(this.days.hasOwnProperty(day))
					{
						classDay = '';
						this.days[day].light ? classDay = ' reports-activity-day-light' : null;

						daysBlock.appendChild(BX.create('div',{
							attrs: {
								className: className + classDay
							},
							children: [
								BX.create('div', {
									attrs: {
										className: 'reports-activity-scale-item'
									},
									text: this.days[day].name
								})
							]
						}))
					}
				}

				return daysBlock;
			},

			/**
			 *
			 * @returns {DocumentFragment|*}
			 */
			getHourScale: function ()
			{
				var hoursBlock = document.createDocumentFragment();
				var className = 'reports-activity-hour';
				var classWorkTime;
				var classAnchor;

				for (var hour in this.hours)
				{
					if(this.hours.hasOwnProperty(hour))
					{
						classWorkTime = '';
						classAnchor = '';
						this.hours[hour].show ? classAnchor = ' reports-activity-hour-show' : null;
						this.hours[hour].active ? classWorkTime = ' reports-activity-hour-work-time' : null;

						hoursBlock.appendChild(BX.create('div',{
							attrs: {
								className: className + classAnchor + classWorkTime
							},
							children: [
								BX.create('div', {
									attrs: {
										className: 'reports-activity-scale-item'
									},
									text: this.hours[hour].name === 0 ? '0' : this.hours[hour].name
								})
							]
						}))
					}
				}

				return hoursBlock;
			},

			/**
			 *
			 * @returns {DocumentFragment|*}
			 */
			getActivityScale: function ()
			{
				var activityScaleBlock = document.createDocumentFragment();
				var numberParam = 0;

				for (var i = 1; i <= 3; i++)
				{
					i === 2 ? numberParam = Math.round(this.getMaxActivityArray() / 2) : null;
					i === 3 ? numberParam = this.getMaxActivityArray() : null;

					activityScaleBlock.appendChild(BX.create('div', {
						attrs: {
							className: 'reports-activity-active'
						},
						children: [
							BX.create('div', {
								attrs: {
									className: 'reports-activity-scale-item'
								},
								text: numberParam === 0 ? '0' : numberParam
							})
						]
					}))
				}

				return activityScaleBlock
			},

			/**
			 *
			 * @returns {DocumentFragment|*}
			 */
			getActivityDayScale: function (type)
			{
				var activityScaleBlock = document.createDocumentFragment();
				var numberParam = 0;

				for (var i = 1; i <= 3; i++)
				{
					i === 2 ? numberParam = Math.round(this.getMaxActivity(this.getMaxDayActivity()) / 2) : null;
					i === 3 ? numberParam = this.getMaxActivity(this.getMaxDayActivity()) : null;

					activityScaleBlock.appendChild(BX.create('div', {
						attrs: {
							className: 'reports-activity-active'
						},
						children: [
							BX.create('div', {
								attrs: {
									className: 'reports-activity-scale-item'
								},
								text: numberParam === 0 ? '0' : numberParam
							})
						]
					}))
				}

				return activityScaleBlock
			},

			/**
			 *
			 * @returns {Array}
			 */
			getMaxDayActivity: function ()
			{
				var daysActivity = [];

				for (var active in this.days)
				{
					if(this.days.hasOwnProperty(active))
					{
						daysActivity.push(
							{
								active: this.getDayTotalActivity(this.days[active])
							}
						)
					}
				}

				return daysActivity
			},

			/**
			 *
			 * @returns {Array}
			 */
			getMaxHourActivity: function ()
			{
				var daysActivity = [];

				for (var active in this.hours)
				{
					if(this.days.hasOwnProperty(active))
					{
						daysActivity.push(
							{
								active: this.getHourTotalActivity(this.hours[active])
							}
						)
					}
				}

				return daysActivity
			},

			/**
			 *
			 * @returns {Element}
			 */
			getTotalActivityGraph: function ()
			{
				var tableBlock = BX.create('div', {
					attrs: {
						className: 'reports-activity-table'
					}
				});

				for (var row in this.days)
				{
					if(this.days.hasOwnProperty(row))
					{
						var tr = BX.create('div', {
							attrs: {
								className: 'reports-activity-table-row'
							}
						});

						for (var col in this.hours)
						{
							if(this.hours.hasOwnProperty(col))
							{
								var td = BX.create('div', {
									attrs: {
										className: 'reports-activity-table-cell'
									},
									style: {
										animationDelay: Math.random().toFixed(2) + 's'
									}
								});

								td.appendChild(this.getTotalActivityItem(this.days[row], this.hours[col]));
								tr.appendChild(td);
							}
						}

						tableBlock.appendChild(tr)
					}
				}

				return tableBlock
			},

			/**
			 *
			 * @param {Object} day
			 * @param {Object} hour
			 * @returns {Element}
			 */
			getTotalActivityItem: function (day, hour)
			{
				var itemBlock = BX.create('div', {
					attrs: {
						className: 'reports-activity-table-item'
					}
				});

				var itemBlockBind = BX.create('div', {
					attrs: {
						className: 'reports-activity-table-item-bind'
					}
				});

				itemBlock.appendChild(itemBlockBind);

				var itemObj = {};

				for (var item in this.items)
				{
					if(this.items.hasOwnProperty(item))
					{
						if(day.id === this.items[item].labelYid && hour.id === this.items[item].labelXid)
						{
							itemObj = this.items[item];

							BX.style(itemBlock, 'opacity', this.getOpacity(this.items[item].active));
							BX.bind(itemBlock, 'mouseenter', function ()
							{
								this.showPopup(itemBlockBind, itemObj)
							}.bind(this));
							BX.bind(itemBlock, 'mouseleave', this.destroyPopup.bind(this))
						}
					}
				}

				return itemBlock
			},

			/**
			 *
			 * @param {Element} targetNode
			 * @param {Object} param
			 */
			showPopup: function (targetNode, param)
			{
				var workTime = (param.labelXid - 1) + ':00 - ' + param.labelXid + ':00';

				if(param.labelYid)
				{
					workTime = this.days[param.labelYid - 1].name;
				}

				if(param.labelYid && param.labelXid)
				{
					workTime = this.days[param.labelYid - 1].name + ', ' + (param.labelXid - 1) + ':00 - ' + param.labelXid + ':00';
				}

				var content = BX.create('div', {
					attrs:{ className: 'reports-activity-popup' },
					children: [
						BX.create('div', {
							attrs: {
								className: 'reports-activity-popup-work-time'
							},
							text: workTime
						}),
						BX.create('div', {
							attrs: { className: 'reports-activity-popup-active' },
							children: [
								BX.create('span', {
									attrs: { className: 'reports-activity-popup-active-marker' }
								}),
								BX.create('span', {
									attrs: {
										className: 'reports-activity-popup-active-value'
									},
									text: param.active
								}),
								BX.create('span', {
									attrs: {
										className: 'reports-activity-popup-active-value-text'
									},
									text: BX.message('ACTIVITY_WIDGET_VALUE_COMMENT')
								})
							]
						})
					]
				});

				this.popup = new BX.PopupWindow('reports-activity-popup', targetNode, {
					className: 'reports-activity-popup-pointer-events',
					content: content,
					angle: {
						position: 'bottom',
						offset : 20
					},
					offsetTop: -9,
					zIndex: 9999,
					bindOptions: {
						position: 'top'
					}
				});

				this.popup.show()
			},

			destroyPopup: function ()
			{
				this.popup.destroy();
				this.popup = null;
			},

			/**
			 *
			 * @param {number} active
			 * @returns {string}
			 */
			getOpacity: function (active)
			{
				var activityIndex = Math.round((100 / this.getMaxActivity(this.items)) * active);
				var opacity = '.' + activityIndex;

				activityIndex <= 20 ? opacity = '.15' : null;
				(activityIndex > 20) && (activityIndex <= 40 ) ? opacity = '.3' : null;
				(activityIndex > 40) && (activityIndex <= 60 ) ? opacity = '.5' : null;
				(activityIndex > 60) && (activityIndex <= 80 ) ? opacity = '.7' : null;
				(activityIndex > 80) && (activityIndex <= 100 ) ? opacity = '.9' : null;
				activityIndex > 100 ? opacity = '1' : null;


				return opacity
			},

			/**
			 *
			 * @param {Object} hourObj
			 * @returns {number}
			 */
			getHourTotalActivity: function (hourObj)
			{
				var hourActivity = 0;

				for (var item in this.items)
				{
					if(this.items.hasOwnProperty(item))
					{
						if (this.items[item].labelXid === hourObj.id)
						{
							hourActivity += this.items[item].active
						}
					}
				}

				return hourActivity
			},

			/**
			 *
			 * @param {Object} dayObj
			 * @returns {number}
			 */
			getDayTotalActivity: function (dayObj)
			{
				var dayActivity = 0;

				for (var item in this.items)
				{
					if(this.items.hasOwnProperty(item))
					{
						if (this.items[item].labelYid === dayObj.id)
						{
							dayActivity += this.items[item].active
						}
					}
				}

				return dayActivity
			},

			/**
			 *
			 * @returns {Element}
			 */
			getHorizontalWidget: function ()
			{
				var horizontalWidget = BX.create('div', {
					attrs: { className: 'reports-activity-horizontal-widget' }
				});

				for (var col in this.hours)
				{
					if(this.hours.hasOwnProperty(col))
					{
						horizontalWidget.appendChild(this.getHorizontalWidgetItem(this.hours[col]))
					}
				}

				return horizontalWidget
			},

			getMaxActivityArray: function() {
				var maxActivity = 0;

				for (var cols in this.hours)
				{
					if(this.hours.hasOwnProperty(cols))
					{
						maxActivity < this.getHourTotalActivity(this.hours[cols]) ? maxActivity = this.getHourTotalActivity(this.hours[cols]) : null
					}
				}

				return maxActivity
			},

			/**
			 *
			 * @param {Object} colObj
			 * @returns {Element}
			 */
			getHorizontalWidgetItem: function (colObj)
			{
				var columnHeight = (100 / this.getMaxActivityArray()) * this.getHourTotalActivity(colObj);
				var targetBlock = BX.create('div', {
					attrs: { className: 'reports-activity-horizontal-widget-item-bind' }
				});
				var events =  {
					mouseenter: function ()
					{
						this.showPopup(
							targetBlock,
							{
								labelXid: colObj.id,
								active: this.getHourTotalActivity(colObj)
							}
						);
					}.bind(this),
					mouseleave: this.destroyPopup.bind(this)
				};

				return  BX.create('div', {
					attrs: {
						className: (columnHeight === 0 ) ? 'reports-activity-horizontal-widget-item-empty' : 'reports-activity-horizontal-widget-item'
					},
					style: {
						maxHeight: columnHeight + '%',
						animationDelay: Math.random().toFixed(2) + 's'
					},
					children: [
						targetBlock
					],
					events: columnHeight === 0 ? null : events
				});
			},

			/**
			 *
			 * @returns {Element}
			 */
			getVerticalWidget: function ()
			{
				var verticalWidget = BX.create('div', {
					attrs: { className: 'reports-activity-vertical-widget' }
				});

				for (var row in this.days)
				{
					if(this.days.hasOwnProperty(row))
					{
						verticalWidget.appendChild(
							this.getVerticalWidgetItem(this.days[row], this.getMaxActivity(this.getMaxDayActivity()))
						)
					}
				}

				return verticalWidget
			},

			/**
			 *
			 * @param {Object} rowObj
			 * @param {number} maxActivity
			 * @returns {Element}
			 */
			getVerticalWidgetItem: function (rowObj, maxActivity)
			{
				var rowWidth = (100 / maxActivity) * this.getDayTotalActivity(rowObj);
				var targetBlock = BX.create('div', {
					attrs: { className: 'reports-activity-vertical-widget-item-bind' }
				});
				var events =  {
					mouseenter: function ()
					{
						this.showPopup(
							targetBlock,
							{
								labelYid: rowObj.id,
								active: this.getDayTotalActivity(rowObj)
							}
						);
					}.bind(this),
					mouseleave: this.destroyPopup.bind(this)
				};

				return  BX.create('div', {
					attrs: {
						className: (rowWidth === 0 ) ? 'reports-activity-vertical-widget-item-empty' : 'reports-activity-vertical-widget-item'
					},
					style: {
						maxWidth: rowWidth + '%',
						animationDelay: Math.random().toFixed(2) + 's'
					},
					children: [
						targetBlock
					],
					events: rowWidth === 0 ? null : events
				});
			},

			/**
			 *
			 * @returns {Element}
			 */
			getHandler: function ()
			{
				var handlerContainer = BX.create('div',{
					attrs: { className: 'reports-activity-handler' }
				});

				this.handlerHybridWidget = BX.create('div',{
					attrs: {
						className: 'reports-activity-handler-item reports-activity-handler-item-active'
					},
					text: BX.message('ACTIVITY_WIDGET_DAY_AND_HOUR_TITLE'),
					events: {
						click: function ()
						{
							if(this.handlerHybridWidget.classList.contains('reports-activity-handler-item-active'))
							{
								return
							}

							BX.removeClass(this.handlerVerticalWidget, 'reports-activity-handler-item-active');
							BX.removeClass(this.handlerHoryzontalWidget, 'reports-activity-handler-item-active');
							BX.addClass(this.handlerHybridWidget, 'reports-activity-handler-item-active');
							BX.cleanNode(this.widgetContainer);
							BX.cleanNode(this.scaleVertical);
							BX.cleanNode(this.scaleHorizontal);
							BX.removeClass(this.scaleVertical, 'reports-activity-widget-left-reverse');
							this.scaleHorizontal.appendChild(this.getHourScale());
							this.scaleVertical.appendChild(this.getDayScale());
							this.widgetContainer.appendChild(this.getTotalActivityGraph())
						}.bind(this)
					}
				});

				this.handlerVerticalWidget = BX.create('div',{
					attrs: {
						className: 'reports-activity-handler-item'
					},
					text: BX.message('ACTIVITY_WIDGET_HOUR_TITLE'),
					events: {
						click: function ()
						{
							if(this.handlerVerticalWidget.classList.contains('reports-activity-handler-item-active'))
							{
								return
							}

							BX.removeClass(this.handlerHybridWidget, 'reports-activity-handler-item-active');
							BX.removeClass(this.handlerHoryzontalWidget, 'reports-activity-handler-item-active');
							BX.addClass(this.handlerVerticalWidget, 'reports-activity-handler-item-active');
							BX.cleanNode(this.widgetContainer);
							BX.cleanNode(this.scaleVertical);
							BX.cleanNode(this.scaleHorizontal);
							BX.addClass(this.scaleVertical, 'reports-activity-widget-left-reverse');
							this.scaleVertical.appendChild(this.getActivityScale());
							this.scaleHorizontal.appendChild(this.getHourScale());
							this.widgetContainer.appendChild(this.getHorizontalWidget())
						}.bind(this)
					}
				});

				this.handlerHoryzontalWidget = BX.create('div',{
					attrs: {
						className: 'reports-activity-handler-item'
					},
					text: BX.message('ACTIVITY_WIDGET_DAY_TITLE'),
					events: {
						click: function ()
						{
							if(this.handlerHoryzontalWidget.classList.contains('reports-activity-handler-item-active'))
							{
								return
							}

							BX.removeClass(this.handlerHybridWidget, 'reports-activity-handler-item-active');
							BX.removeClass(this.handlerVerticalWidget, 'reports-activity-handler-item-active');
							BX.addClass(this.handlerHoryzontalWidget, 'reports-activity-handler-item-active');
							BX.cleanNode(this.widgetContainer);
							BX.cleanNode(this.scaleHorizontal);
							BX.cleanNode(this.scaleVertical);
							BX.removeClass(this.scaleVertical, 'reports-activity-widget-left-reverse');
							this.scaleHorizontal.appendChild(this.getActivityDayScale());
							this.scaleVertical.appendChild(this.getDayScale());
							this.widgetContainer.appendChild(this.getVerticalWidget())
						}.bind(this)
					}
				});

				handlerContainer.appendChild(this.handlerHybridWidget);
				handlerContainer.appendChild(this.handlerVerticalWidget);
				handlerContainer.appendChild(this.handlerHoryzontalWidget);

				return handlerContainer
			},

			/**
			 *
			 * @returns {Element}
			 */
			getWorkTimeBlock: function ()
			{
				var workHours = [];

				for(var hour in this.hours)
				{
					if(this.hours.hasOwnProperty(hour))
					{
						this.hours[hour].active ? workHours.push(this.hours[hour].name) : null
					}
				}

				return BX.create('div', {
					attrs: {
						className: 'reports-activity-work-time'
					},
					html: BX.message('ACTIVITY_WIDGET_WORK_HOURS_TITLE') + workHours[0] + ':00' + ' - ' + workHours[workHours.length - 1] + ':00'
				})
			},

			render: function ()
			{
				this.container.appendChild(
					BX.create('div', {
						attrs: { className: 'reports-activity' },
						children: [
							this.getHandler(),
							BX.create('div',{
								attrs: { className: 'reports-activity-widget' },
								children: [
									this.scaleVertical = BX.create('div', {
										attrs: { className: 'reports-activity-widget-left' }
									}),
									BX.create('div', {
										attrs: { className: 'reports-activity-widget-right' },
										children: [
											this.widgetContainer = BX.create('div', {
												attrs: { className: 'reports-activity-widget-container' }
											}),
											this.scaleHorizontal = BX.create('div', {
												attrs: { className: 'reports-activity-widget-horizontal-scale' }
											})
										]
									})
								]
							}),
							this.getWorkTimeBlock()
						]
					})
				);

				this.scaleVertical.appendChild(this.getDayScale());
				this.scaleHorizontal.appendChild(this.getHourScale());
				this.widgetContainer.appendChild(this.getTotalActivityGraph());
			}
		};
})();
