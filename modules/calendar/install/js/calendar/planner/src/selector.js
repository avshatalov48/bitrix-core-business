"use strict";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Util} from 'calendar.util';
import {Type, Dom, Tag} from 'main.core';

export class Selector extends EventEmitter
{
	DOM = {};
	selectMode = false;
	currentDateFrom = new Date();
	currentDateTo = new Date();
	currentFullDay = false;
	useAnimation = true;

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Planner.Selector');

		this.selectMode = params.selectMode;
		this.getPosByDate = params.getPosByDate;
		this.getDateByPos = params.getDateByPos;
		this.getPosDateMap = params.getPosDateMap;
		this.getTimelineWidth = params.getTimelineWidth;
		this.getScaleInfo = params.getScaleInfo;
		this.solidStatus = params.solidStatus;

		this.useAnimation = params.useAnimation !== false;
		this.DOM.timelineWrap = params.timelineWrap;

		this.render();
	}

	render()
	{
		this.DOM.wrap = Tag.render`
			<div class="calendar-planner-timeline-selector" data-bx-planner-meta="selector">
				<span data-bx-planner-meta="selector-resize-left" class="calendar-planner-timeline-drag-left"></span>
				<span class="calendar-planner-timeline-selector-grip"></span>
				<span data-bx-planner-meta="selector-resize-right" class="calendar-planner-timeline-drag-right"></span>
			</div>`;

		// prefent draging selector and activating uploader controll in livefeed
		this.DOM.wrap.ondrag = BX.False;
		this.DOM.wrap.ondragstart = BX.False;

		this.DOM.titleNode = Tag.render`<div class="calendar-planner-selector-notice" style="display: none"></div>`;

		if (this.selectMode)
		{
			result.controlWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-planner-selector-control"></div>`);
		}
	}

	getWrap()
	{
		return this.DOM.wrap;
	}

	getTitleNode()
	{
		return this.DOM.titleNode;
	}

	/**
	 *
	 *
	 * @params array array of parameters
	 * @params[from]
	 * @params[to]
	 * @params[updateScaleType] bool
	 * @params[updateScaleLimits] bool
	 *
	 * @return null
	 */
	update(params = {})
	{
		if (!Type.isPlainObject(params))
			params = {};

		params.updateScaleType = !!params.updateScaleType;
		params.updateScaleLimits = !!params.updateScaleLimits;
		params.animation = !!params.animation;

		let
			rebuildTimeline = false,
			from = Type.isDate(params.from) ? params.from : BX.parseDate(params.from) || this.currentDateFrom,
			to = Type.isDate(params.to) ? params.to : BX.parseDate(params.to) || this.currentDateTo,
			fullDay = params.fullDay !== undefined ? params.fullDay : this.currentFullDay;

		if (Type.isDate(from) && Type.isDate(to))
		{
			this.currentDateFrom = from;
			this.currentDateTo = to;
			this.currentFullDay = fullDay;

			//this.SetFullDayMode(fullDay);
			if (fullDay)
			{
				to = new Date(to.getTime() + Util.getDayLength());
				from.setHours(0, 0, 0,0);
				to.setHours(0, 0, 0,0);

				// if (this.scaleType !== '1day')
				// {
				// 	this.setScaleType('1day');
				// 	rebuildTimeline = true;
				// }
			}

			// Update limits of scale
			// if (params.updateScaleLimits && this.scaleType !== '1day')
			// {
			// 	let timeFrom = parseInt(from.getHours()) + Math.floor(from.getMinutes() / 60);
			// 	let timeTo = parseInt(to.getHours()) + Math.ceil(to.getMinutes() / 60);
			//
			// 	if (Util.formatDate(from) !== Util.formatDate(to))
			// 	{
			// 		this.extendScaleTime(0, 24);
			// 		rebuildTimeline = true;
			// 	}
			// 	else
			// 	{
			// 		if (timeFrom < this.shownScaleTimeFrom)
			// 		{
			// 			this.extendScaleTime(timeFrom, false);
			// 			rebuildTimeline = true;
			// 		}
			//
			// 		if (timeTo > this.shownScaleTimeTo)
			// 		{
			// 			this.extendScaleTime(false, timeTo);
			// 			rebuildTimeline = true;
			// 		}
			//
			// 		if (rebuildTimeline)
			// 		{
			// 			this.adjustCellWidth();
			// 		}
			// 	}
			// }

			//if (params.RRULE)
			//{
			//	this.HandleRecursion(params);
			//}

			// if (rebuildTimeline)
			// {
			// 	this.RebuildPlanner({updateSelector: false});
			// }

			// Update selector
			this.show(from, to, {animation: params.animation, focus: params.focus});
		}
	}

	show(from, to, params)
	{
		let
			animation = params.animation && this.useAnimation !== false,
			focus = params.focus,
			alignCenter = params.alignCenter !== false;

		this.DOM.wrap.style.display = 'block';

		if (Type.isDate(from) && Type.isDate(to))
		{
			let
				fromPos = this.getPosByDate(from),
				toPos = this.getPosByDate(to);

			this.DOM.wrap.style.width = (toPos - fromPos) + 'px';

			if (animation && this.DOM.wrap.style.left && !this.currentFullDay)
			{
				this.transit({
					toX: fromPos,
					triggerChangeEvents: false,
					focus: focus === true
				});
			}
			else
			{
				this.DOM.wrap.style.left = fromPos + 'px';
				this.DOM.wrap.style.width = (toPos - fromPos) + 'px';
				this.focus(false, 200, alignCenter);
				this.checkStatus(fromPos);
			}
		}
	}

	hide()
	{
		this.DOM.wrap.style.display = 'none';
	}

	startMove()
	{
		this.selectorIsDraged = true;
		this.selectorRoundedPos = false;
		this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
		this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;

		Dom.addClass(document.body, 'calendar-planner-unselectable');
	}

	move(x)
	{
		if (this.selectorIsDraged)
		{
			let selectorWidth = parseInt(this.DOM.wrap.style.width), pos = this.selectorStartLeft + x;

			// Correct cursor position acording to changes of scrollleft
			pos -= this.selectorStartScrollLeft - this.DOM.timelineWrap.scrollLeft;

			if (this.getPosDateMap()[pos])
			{
				this.selectorRoundedPos = pos;
			}
			else
			{
				let roundedPos = Selector.roundPos(pos);
				if (this.getPosDateMap()[roundedPos])
				{
					this.selectorRoundedPos = roundedPos;
				}
			}

			let checkedPos = this.checkPosition(this.selectorRoundedPos);
			if (checkedPos !== this.selectorRoundedPos)
			{
				this.selectorRoundedPos = checkedPos;
				pos = checkedPos;
			}

			this.DOM.wrap.style.left = pos + 'px';
			this.showTitle({fromPos: pos, toPos: this.selectorRoundedPos + selectorWidth});

			this.checkStatus(this.selectorRoundedPos, true);
		}
	}

	endMove()
	{
		if (this.selectorIsDraged && this.selectorRoundedPos)
		{
			this.DOM.wrap.style.left = this.selectorRoundedPos + 'px';
			this.selectorRoundedPos = false;
			this.hideTitle();
			this.setValue(this.selectorRoundedPos);
		}
		this.selectorIsDraged = false;
	}

	startResize()
	{
		this.selectorIsResized = true;
		this.selectorRoundedPos = false;

		this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
		this.selectorStartWidth = parseInt(this.DOM.wrap.style.width);
		this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;
	}

	resize(x)
	{
		if (this.selectorIsResized)
		{
			let
				toDate,
				timeTo,
				width = this.selectorStartWidth + x;

			// Correct cursor position according to changes of scrollLeft
			width -= this.selectorStartScrollLeft - this.DOM.timelineWrap.scrollLeft;
			let rightPos = Math.min(this.selectorStartLeft + width, this.getTimelineWidth());

			toDate = this.getDateByPos(rightPos, true);

			if (this.fullDayMode)
			{
				timeTo = parseInt(toDate.getHours()) + Math.round((toDate.getMinutes() / 60) * 10) / 10;
				toDate.setHours(0, 0, 0, 0);
				if (timeTo > 12)
				{
					toDate = new Date(toDate.getTime() + Util.getDayLength());
					toDate.setHours(0, 0, 0, 0);
				}
				rightPos = this.getPosByDate(toDate);
				width = rightPos - this.selectorStartLeft;

				if (width <= 10)
				{
					toDate = this.getDateByPos(this.selectorStartLeft);
					toDate = new Date(toDate.getTime() + Util.getDayLength());
					toDate.setHours(0, 0, 0, 0);
					width = this.getPosByDate(toDate) - this.selectorStartLeft;
					rightPos = this.selectorStartLeft + width;
				}
			}
			else if (this.shownScaleTimeFrom !== 0 || this.shownScaleTimeTo !== 24)
			{
				let fromDate = this.getDateByPos(this.selectorStartLeft);
				if (toDate && fromDate && Util.formatDate(fromDate) !== Util.formatDate(toDate))
				{
					toDate = new Date(fromDate.getTime());
					toDate.setHours(this.shownScaleTimeTo, 0, 0, 0);
					rightPos = this.getPosByDate(toDate);
					width = rightPos - this.selectorStartLeft;
				}
			}

			if (this.getPosDateMap()[rightPos])
			{
				this.selectorRoundedRightPos = rightPos;
			}
			else
			{
				let roundedPos = Selector.roundPos(rightPos);
				if (this.getPosDateMap()[roundedPos])
				{
					this.selectorRoundedRightPos = roundedPos;
				}
			}

			this.DOM.wrap.style.width = width + 'px';
			this.showTitle({fromPos: this.selectorStartLeft, toPos: this.selectorRoundedRightPos});
			this.checkStatus(this.selectorStartLeft, true);
		}
	}

	endResize()
	{
		if (this.selectorIsResized && this.selectorRoundedRightPos)
		{
			this.DOM.wrap.style.width = (this.selectorRoundedPos - parseInt(this.DOM.wrap.style.left)) + 'px';
			this.selectorRoundedRightPos = false;
			this.hideTitle();
			this.setValue();
		}
		this.selectorIsResized = false;
	}

	isDragged()
	{
		return this.selectorIsResized || this.selectorIsDraged;
	}

	checkStatus(selectorPos, checkPosition)
	{
		if (this.solidStatus)
		{
			Dom.removeClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
			Dom.removeClass(this.mainContWrap, 'calendar-planner-selector-warning');
			Dom.addClass(this.DOM.wrap, 'solid');
		}
		else
		{
			if (!selectorPos)
			{
				selectorPos = Selector.roundPos(this.DOM.wrap.style.left);
			}

			let fromDate, toDate;
			if (checkPosition === true || !this.currentDateFrom)
			{
				let
					selectorWidth = parseInt(this.DOM.wrap.style.width),
					fromPos = selectorPos,
					toPos = fromPos + selectorWidth;

				if (!fromPos && !toPos && !selectorWidth && this.lastFromDate)
				{
					fromDate = this.lastFromDate;
					toDate = this.lastToDate;
				}
				else
				{
					fromDate = this.getDateByPos(fromPos);
					toDate = this.getDateByPos(toPos, true);
					this.lastFromDate = fromDate;
					this.lastToDate = toDate;
				}
			}
			else
			{
				fromDate = this.currentDateFrom;
				toDate = this.currentDateTo;
			}

			this.emit('doCheckStatus', new BaseEvent({data: {dateFrom: fromDate,dateTo: toDate}}));
		}
	}

	setSelectorStatus(status)
	{
		this.selectorIsFree = status;
		if (this.selectorIsFree)
		{
			Dom.removeClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
		}
		else
		{
			Dom.addClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
		}
	}

	setValue(selectorPos, selectorWidth)
	{
		if (!selectorPos)
		{
			selectorPos = parseInt(this.DOM.wrap.style.left);
		}
		selectorPos = Math.max(0, selectorPos);

		if (!selectorWidth)
		{
			selectorWidth = parseInt(this.DOM.wrap.style.width);
		}

		if (selectorPos + selectorWidth > parseInt(this.getTimelineWidth()))
		{
			selectorPos = parseInt(this.getTimelineWidth()) - selectorWidth;
		}

		let
			dateFrom = this.getDateByPos(selectorPos),
			dateTo = this.getDateByPos(selectorPos + selectorWidth, true);

		if (dateFrom && dateTo)
		{
			this.currentDateFrom = dateFrom;
			this.currentDateTo = dateTo;

			if (this.fullDayMode)
			{
				dateTo = new Date(dateTo.getTime() - Util.getDayLength());
			}

			this.currentFullDay = this.fullDayMode;

			this.emit('onChange', new BaseEvent({data: {
				dateFrom: dateFrom,
				dateTo: dateTo,
				fullDay: this.fullDayMode
			}}));
		}
	}

	checkPosition(fromPos, selectorWidth, toPos)
	{
		let scaleInfo = this.getScaleInfo();
		if (scaleInfo.shownTimeFrom !== 0 || scaleInfo.shownTimeTo !== 24
		&& (scaleInfo.type !== '1day' || this.fullDayMode))
		{
			if (!fromPos)
				fromPos = parseInt(this.DOM.wrap.style.left);

			if (!selectorWidth)
				selectorWidth = parseInt(this.DOM.wrap.style.width);

			if (!toPos)
				toPos = fromPos + selectorWidth;

			if (toPos > parseInt(this.getTimelineWidth()))
			{
				fromPos = parseInt(this.getTimelineWidth()) - selectorWidth;
			}
			else
			{
				let
					fromDate = this.getDateByPos(fromPos),
					toDate = this.getDateByPos(toPos, true),
					timeFrom, timeTo,
					scaleTimeFrom = parseInt(scaleInfo.shownTimeFrom),
					scaleTimeTo = parseInt(scaleInfo.shownTimeTo);

				if (fromDate && toDate)
				{
					if (this.fullDayMode)
					{
						timeFrom = parseInt(fromDate.getHours()) + Math.round((fromDate.getMinutes() / 60) * 10) / 10;
						fromDate.setHours(0, 0, 0, 0);

						if (timeFrom > 12)
						{
							fromDate = new Date(fromDate.getTime() + Util.getDayLength());
							fromDate.setHours(0, 0, 0, 0);
						}

						fromPos = this.getPosByDate(fromDate);
					}
					else if (fromDate.getDay() !== toDate.getDay())
					{
						timeFrom = parseInt(fromDate.getHours()) + Math.round((fromDate.getMinutes() / 60) * 10) / 10;
						timeTo = parseInt(toDate.getHours()) + Math.round((toDate.getMinutes() / 60) * 10) / 10;
						if (Math.abs(scaleTimeTo - timeFrom) > Math.abs(scaleTimeFrom - timeTo))
						{
							fromDate.setHours(scaleInfo.shownTimeTo, 0, 0,0);
							fromPos = this.getPosByDate(fromDate) - selectorWidth;
						}
						else
						{
							toDate.setHours(scaleInfo.shownTimeFrom, 0, 0,0);
							fromPos = this.getPosByDate(toDate);
						}
					}
				}
			}
		}
		return fromPos;
	}

	transit(params = {})
	{
		let
			fromX = params.fromX || parseInt(this.DOM.wrap.style.left),
			toX = Selector.roundPos(params.toX || fromX),
			triggerChangeEvents = params.triggerChangeEvents !== false,
			focus = !!params.focus,
			width = parseInt(this.DOM.wrap.offsetWidth);

		// triggerChangeEvents - it means that selector transition (animation caused from mouse ebents)
		if (toX > (fromX + width) && triggerChangeEvents)
		{
			toX -= width;
		}

		if (fromX !== toX)
		{
			if (this.animation)
			{
				this.animation.stop();
			}

			this.animation = new BX.easing({
				duration: 300,
				start: {left: fromX},
				finish: {left: toX},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {this.DOM.wrap.style.left = state.left + 'px'},
				complete: () => {
					this.animation = null;
					let
						fromPos = parseInt(this.DOM.wrap.style.left),
						checkedPos = this.checkPosition(fromPos);

					if (checkedPos !== fromPos)
					{
						this.DOM.wrap.style.left = checkedPos + 'px';
					}

					if (triggerChangeEvents)
					{
						this.setValue(checkedPos);
					}

					if (focus)
					{
						this.focus(true, 300);
					}

					setTimeout(() => {
						this.show(
							this.currentDateFrom,
							this.currentDateTo,
							{
								animation: false,
								focus: focus,
								alignCenter: false
							}
						);
					}, 200);

					this.checkStatus(checkedPos);
				}
			});

			this.animation.animate();
		}
		else
		{
			if (triggerChangeEvents)
			{
				this.setValue();
			}

			if (focus === true)
			{
				this.focus(true, 300);
			}

			this.checkStatus();
		}
	}

	showTitle(params = {})
	{
		let
			fromPos = params.fromPos,
			toPos = params.toPos,
			selectorTitle = params.selectorTitle || this.getTitleNode(),
			selector = params.selector || this.DOM.wrap,
			timelineWidth = this.getTimelineWidth(),
			fromDate, toDate;

		if (fromPos && toPos)
		{
			if (toPos > timelineWidth)
			{
				fromPos = timelineWidth - parseInt(selector.style.width);
				toPos = timelineWidth;
			}

			fromDate = this.getDateByPos(fromPos);
			toDate = this.getDateByPos(toPos, true);
			if (fromDate && toDate)
			{
				if (this.fullDayMode)
				{
					if (Math.abs(toDate.getTime() - fromDate.getTime() - Util.getDayLength()) < 1000)
					{
						selectorTitle.innerHTML = BX.date.format('d F, D', fromDate.getTime() / 1000);
					}
					else
					{
						selectorTitle.innerHTML =
							BX.date.format('d F', fromDate.getTime() / 1000)
							+ ' - '
							+ BX.date.format('d F', toDate.getTime() / 1000);
					}
				}
				else
				{
					selectorTitle.removeAttribute('style');
					selectorTitle.innerHTML = Util.formatTime(fromDate) + ' - ' + Util.formatTime(toDate);
				}

				if (this.selectMode && this.lastTouchedEntry)
				{
					let
						entriesListWidth = this.compactMode ? 0 : this.entriesListWidth,
						selectorTitleLeft = parseInt(selector.style.left) - this.DOM.timelineWrap.scrollLeft + entriesListWidth + parseInt(selector.style.width) / 2,
						selectorTitleTop = parseInt(this.timelineDataCont.offsetTop) + parseInt(this.lastTouchedEntry.style.top) - 12;

					selectorTitle.style.top = selectorTitleTop + 'px';
					selectorTitle.style.left = selectorTitleLeft + 'px';
				}
				else
				{
					selector.appendChild(selectorTitle);
				}
			}
		}

		if (selectorTitle === this.selectorTitle)
		{
			if (selectorTitle.style.display === 'none' || this.selectorHideTimeout)
			{
				this.selectorHideTimeout = clearTimeout(this.selectorHideTimeout);
				// Opacity animation
				this.selectorTitle.style.display = '';
				this.selectorTitle.style.opacity = 0;
				new BX.easing({
					duration: 400,
					start: {opacity: 0},
					finish: {opacity: 100},
					transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
					step: (state)=>{this.selectorTitle.style.opacity = state.opacity / 100;},
					complete: ()=>{this.selectorTitle.removeAttribute('style');}
				}).animate();
			}
		}
		else
		{
			selectorTitle.removeAttribute('style');
		}
	}

	hideTitle(params = {})
	{
		if (!Type.isPlainObject(params))
			params = {};

		let
			timeoutName = params.selectorIndex === undefined ? 'selectorHideTimeout' : 'selectorHideTimeout_' + params.selectorIndex,
			selectorTitle = params.selectorTitle || this.getTitleNode();

		if (this[timeoutName])
			this[timeoutName] = clearTimeout(this[timeoutName]);

		if (params.timeout !== false)
		{
			this[timeoutName] = setTimeout(() => {
				params.timeout = false;
				this.hideTitle(params);
			}, 700);
		}
		else
		{
			// Opacity animation
			selectorTitle.style.display = '';
			selectorTitle.style.opacity = 1;
			new BX.easing({
				duration: 400,
				start: {opacity: 100},
				finish: {opacity: 0},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: (state) => {selectorTitle.style.opacity = state.opacity / 100;},
				complete: () => {
					selectorTitle.removeAttribute('style');
					selectorTitle.style.display = 'none';
				}
			}).animate();
		}
	}

	static roundPos(x)
	{
		return Math.round(parseFloat(x));
	}

	focus(animation = true, timeout = 300, alignCenter)
	{
		alignCenter = alignCenter === true;



		if (this.focusTimeout)
		{
			this.focusTimeout = !!clearTimeout(this.focusTimeout);
		}

		if (this.useAnimation === false)
		{
			animation = false;
		}

		if (timeout)
		{
			this.focusTimeout = setTimeout(() => {this.focus(animation, false, alignCenter);}, timeout);
		}
		else
		{
			const
				screenDelta = 10,
				selectorLeft = parseInt(this.DOM.wrap.style.left),
				selectorWidth = parseInt(this.DOM.wrap.style.width),
				viewWidth = parseInt(this.DOM.timelineWrap.offsetWidth),
				viewLeft = parseInt(this.DOM.timelineWrap.scrollLeft),
				viewRight = viewLeft + viewWidth;

			let newScrollLeft = viewLeft;

			if (selectorLeft < viewLeft + screenDelta
				|| selectorLeft > viewRight - screenDelta
				|| alignCenter
			)
			{
				// Selector is smaller than view - we puting it in the middle of the view
				if (selectorWidth <= viewWidth)
				{
					newScrollLeft = Math.max(Math.round(selectorLeft - ((viewWidth - selectorWidth) / 2 )), screenDelta);

				}
				else // Selector is wider, so we adjust by left side
				{
					newScrollLeft = Math.max(Math.round(selectorLeft - screenDelta), screenDelta);
				}
			}

			if (newScrollLeft !== viewLeft)
			{
				if (animation === false)
				{
					this.DOM.timelineWrap.scrollLeft = newScrollLeft;
				}
				else
				{
					new BX.easing({
						duration: 300,
						start: {scrollLeft: this.DOM.timelineWrap.scrollLeft},
						finish: {scrollLeft: newScrollLeft},
						transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
						step: (state)=>{this.DOM.timelineWrap.scrollLeft = state.scrollLeft;},
						complete: ()=>{}
					}).animate();
				}
			}
		}
	}

	getDuration()
	{
		let duration = Math.round((this.currentDateTo - this.currentDateFrom) / 1000) * 1000;

		if (this.fullDayMode)
		{
			duration += Util.getDayLength();
		}

		return duration;
	}

	getDateFrom()
	{
		return this.currentDateFrom;
	}

	getDateTo()
	{
		return this.currentDateTo;
	}
}