"use strict";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Util} from 'calendar.util';
import {Type, Dom, Tag} from 'main.core';
import { EventDragAndDrop } from 'calendar.ui.tools.draganddrop';

export class Selector extends EventEmitter
{
	DOM = {};
	selectMode = false;
	currentDateFrom = new Date();
	currentDateTo = new Date();
	currentFullDay = false;
	useAnimation = true;
	beforeBeginChange = false;

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Planner.Selector');

		this.getPosByDate = params.getPosByDate;
		this.getDateByPos = params.getDateByPos;
		this.getPosDateMap = params.getPosDateMap;
		this.getTimelineWidth = params.getTimelineWidth;
		this.getScaleInfo = params.getScaleInfo;
		this.solidStatus = params.solidStatus;
		this.vacationOffset = 0;

		this.eventDragAndDrop = new EventDragAndDrop(params.getDateByPos, params.getPosByDate, params.getEvents);

		this.useAnimation = params.useAnimation !== false;

		this.render();
	}

	render()
	{
		this.DOM.timeNodes = {};
		this.DOM.timeWrap = Tag.render`
			<div class="calendar-planner-selector-notices-container"></div>
		`;

		this.DOM.wrap = Tag.render`
			<div class="calendar-planner-timeline-selector" data-bx-planner-meta="selector">
				<span data-bx-planner-meta="selector-resize-left" class="calendar-planner-timeline-drag-left"></span>
				<span class="calendar-planner-timeline-selector-grip"></span>
				<span data-bx-planner-meta="selector-resize-right" class="calendar-planner-timeline-drag-right"></span>
				${this.DOM.timeWrap}
				<div class="calendar-planner-timeline-selector-background"></div>
				${this.renderMoreButton()}
			</div>
		`;

		// prefent draging selector and activating uploader controll in livefeed
		this.DOM.wrap.ondrag = BX.False;
		this.DOM.wrap.ondragstart = BX.False;

		this.DOM.titleNode = Tag.render`<div class="calendar-planner-selector-notice" style="display: none"></div>`;
	}

	renderMoreButton()
	{
		this.DOM.moreButton = Tag.render`
			<div class="calendar-planner-timeline-selector-more-button" style="display: none;"></div>
		`;

		return this.DOM.moreButton;
	}

	shake()
	{
		const shakeClass = 'calendar-planner-selector-shake';
		Dom.addClass(this.DOM.wrap, shakeClass);
		clearTimeout(this.shakeTimeout);
		this.shakeTimeout = setTimeout(() => Dom.removeClass(this.DOM.wrap, shakeClass), 400);
	}

	clearTimeNodes()
	{
		for (const offset in this.DOM.timeNodes)
		{
			this.destroyTimeNode(offset);
		}
	}

	setVacationOffset(offset)
	{
		this.vacationOffset = offset;
	}

	showTimeNode(offsetTop, time, timezone, entryId, isWarning = false)
	{
		this.destroyTimeNode(offsetTop);

		const warningClass = isWarning ? '--warning' : '';

		this.DOM.timeNodes[offsetTop] = Tag.render`
			<div 
			class="calendar-planner-timeline-side-notice --left ${warningClass}" 
			id="timeline-side-notice-${entryId}" 
			style="top: ${offsetTop}px" 
			title="${timezone}"
			>${time}</div>
		`;
		this.DOM.timeWrap.append(this.DOM.timeNodes[offsetTop]);

		this.updateTimeWrapWidth();
	}

	updateTimeWrapWidth()
	{
		const width = Object.values(this.DOM.timeNodes).reduce((acc, el) => Math.max(acc, el?.offsetWidth), 0) + 5;

		this.DOM.timeWrap.style.width = `${width}px`;
		this.DOM.timeWrap.style.marginLeft = `${-width}px`;
		this.DOM.timeWrap.style.left = `${this.vacationOffset}px`;
	}

	destroyTimeNode(offset)
	{
		if (Type.isElementNode(this.DOM.timeNodes[offset]))
		{
			this.DOM.timeNodes[offset].remove();
			this.DOM.timeNodes[offset] = null;
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
		{
			params = {};
		}

		params.updateScaleType = !!params.updateScaleType;
		params.updateScaleLimits = !!params.updateScaleLimits;
		params.animation = !!params.animation;

		let from = Type.isDate(params.from) ? params.from : BX.parseDate(params.from) || this.currentDateFrom;
		let to = Type.isDate(params.to) ? params.to : BX.parseDate(params.to) || this.currentDateTo;
		this.fullDayMode = params.fullDay !== undefined ? params.fullDay : this.currentFullDay;

		if (Type.isDate(from) && Type.isDate(to))
		{
			this.currentFullDay = this.fullDayMode;

			if (this.fullDayMode)
			{
				from.setHours(0, 0, 0, 0);
				const dayCount = Math.ceil((to.getTime() - from.getTime() + 1) / (1000 * 3600 * 24));
				to = new Date(from.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
				to.setHours(23, 55, 0, 0);
			}

			this.boundaryFrom = from;
			this.currentDateFrom = from;
			this.currentDateTo = to;

			// Update selector
			this.show(
				from,
				to,
				{
					animation: params.animation,
					focus: params.focus
				}
			);
		}

		const isSelectorInThePast = this.currentDateTo.getTime() < this.getScaleInfo().scaleDateFrom.getTime();
		const isSelectorInTheFuture = this.currentDateFrom.getTime() > this.getScaleInfo().scaleDateTo.getTime();
		if (isSelectorInThePast || isSelectorInTheFuture)
		{
			this.DOM.wrap.style.display = 'none';
		}
	}

	show(from, to, params)
	{
		const animation = params.animation && this.useAnimation !== false;
		const focus = params.focus === true;
		const alignCenter = params.alignCenter !== false;

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
					// triggerChangeEvents: false, //if everything is broken - uncomment
					focus: focus
				});
			}
			else
			{
				this.DOM.wrap.style.left = fromPos + 'px';
				this.DOM.wrap.style.width = (toPos - fromPos) + 'px';
				if (focus)
				{
					this.focus(true, 200, alignCenter);
				}
				this.checkStatus(fromPos, true);
			}
		}
	}

	hide()
	{
		this.DOM.wrap.style.display = 'none';
	}

	startMove()
	{
		document.addEventListener('pointermove', this.preventDefault, { passive:false });
		this.selectorIsDraged = true;
		this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
		this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;

		this.eventDragAndDrop.onDragStart(this.currentDateTo.getTime() - this.currentDateFrom.getTime(), this.selectorStartLeft);

		Dom.addClass(document.body, 'calendar-planner-unselectable');

		this.beforeBeginChange = true;
	}

	move(x)
	{
		if (this.selectorIsDraged)
		{
			let pos = this.selectorStartLeft + x;

			// Correct cursor position acording to changes of scrollleft
			pos -= this.selectorStartScrollLeft - this.DOM.timelineWrap.scrollLeft;
			pos = this.checkPosition(pos);

			if (!this.getDateByPos(pos) || !this.getDateByPos(pos + parseInt(this.DOM.wrap.style.width)))
			{
				return;
			}

			let boundary = this.eventDragAndDrop.getDragBoundary(pos);

			const valueChanged = boundary.from.getTime() !== this.boundaryFrom.getTime();
			if (valueChanged && this.beforeBeginChange)
			{
				this.emit('onBeginChange');
				this.beforeBeginChange = false;
			}

			boundary = this.getAutoScrollBoundary(boundary, valueChanged);
			boundary = this.getConstrainedBoundary(boundary);

			this.setBoundary(boundary);
		}
	}

	getAutoScrollBoundary(boundary, valueChanged)
	{
		const boundaryLeft = boundary.position - this.DOM.timelineWrap.scrollLeft;
		const containerLeft = this.getPosByDate(this.getScaleInfo().scaleDateFrom);
		const boundaryRight = boundaryLeft + boundary.size;
		const containerRight = this.DOM.timelineFixedWrap.offsetWidth;

		if (boundaryRight > containerRight)
		{
			this.scrollSpeed = this.getSpeed(boundaryRight, containerRight);
			boundary.position = containerRight + this.DOM.timelineWrap.scrollLeft - boundary.size;
			this.setAutoScrollInterval(boundary, 1);
		}
		else if (boundaryLeft < containerLeft)
		{
			this.scrollSpeed = this.getSpeed(boundaryLeft, containerLeft);
			boundary.position = containerLeft + this.DOM.timelineWrap.scrollLeft;
			this.setAutoScrollInterval(boundary, -1);
		}
		else
		{
			this.stopAutoScroll(valueChanged);
		}

		return boundary;
	}

	getSpeed(x1, x2)
	{
		return Math.floor(Math.sqrt(Math.abs(x1 - x2))) + 1;
	}

	setAutoScrollInterval(boundary, direction)
	{
		if (!this.scrollInterval)
		{
			this.scrollInterval = setInterval(() => {
				if (!this.getDateByPos(boundary.position + this.scrollSpeed * direction)
					|| !this.getDateByPos(boundary.position + boundary.size + this.scrollSpeed * direction)
				)
				{
					this.stopAutoScroll();
					return;
				}

				this.DOM.timelineWrap.scrollLeft += this.scrollSpeed * direction;
				boundary.position += this.scrollSpeed * direction;
				boundary.from = this.getDateByPos(boundary.position);
				boundary.to = this.getDateByPos(boundary.position + boundary.size);
				this.eventDragAndDrop.setFinalTimeInterval(boundary.from, boundary.to);
				this.setBoundary(boundary);
			}, 13);
		}
	}

	stopAutoScroll(valueChanged = true)
	{
		clearInterval(this.scrollInterval);
		this.scrollInterval = false;

		if (valueChanged || !this.beforeBeginChange)
		{
			this.emit('onStopAutoScroll');
		}
	}

	setBoundary(boundary)
	{
		if (boundary.wasMagnetized)
		{
			this.DOM.wrap.style.transition = 'left .05s, width .1s';
		}
		else
		{
			this.DOM.wrap.style.transition = 'width .1s';
		}

		this.DOM.wrap.style.width = boundary.size + 'px';
		this.DOM.wrap.style.left = boundary.position + 'px';

		this.showTitle(boundary.from, boundary.to);

		this.checkStatus(boundary.position, true);

		this.boundaryFrom = boundary.from;
	}

	getConstrainedBoundary(boundary)
	{
		if (boundary.wasMagnetized || this.fullDayMode)
		{
			return boundary;
		}

		let from = new Date(boundary.from.getTime());
		let to = new Date(boundary.to.getTime());
		const duration = to.getTime() - from.getTime();
		let position = boundary.position;
		let size = boundary.size;
		let wasMagnetized = false;

		if (from.getHours() < this.getScaleInfo().shownTimeFrom)
		{
			from.setHours(this.getScaleInfo().shownTimeFrom, 0, 0, 0);
			to = new Date(from.getTime() + duration);
			wasMagnetized = true;
			position = this.getPosByDate(from);
			size = this.getPosByDate(to) - position;
		}

		if (to.getHours() > this.getScaleInfo().shownTimeTo
			|| (to.getHours() === this.getScaleInfo().shownTimeTo && to.getMinutes() > 0))
		{
			to.setHours(this.getScaleInfo().shownTimeTo, 0, 0, 0);
			from = new Date(to.getTime() - duration);
			wasMagnetized = true;
			position = this.getPosByDate(from);
			size = this.getPosByDate(to) - position;
		}

		return { from, to, position, size, wasMagnetized };
	}

	endMove()
	{
		document.removeEventListener('pointermove', this.preventDefault, { passive:false });
		this.stopAutoScroll();
		if (this.selectorIsDraged)
		{
			this.selectorIsDraged = false;

			const left = this.getPosByDate(this.eventDragAndDrop.getFinalFrom());
			const right = this.getPosByDate(this.eventDragAndDrop.getFinalTo());

			const finalBoundary = this.getConstrainedBoundary({
				from: this.eventDragAndDrop.getFinalFrom(),
				to: this.eventDragAndDrop.getFinalTo(),
				position: left,
				size: right - left
			});

			this.DOM.wrap.style.left = finalBoundary.position + 'px';
			this.DOM.wrap.style.width = finalBoundary.size + 'px';
			this.DOM.wrap.style.transition = 'none';

			this.checkStatus(left, true);
			this.hideTitle();
			this.setValue();
		}
		this.selectorIsDraged = false;
	}

	startResize()
	{
		document.addEventListener('pointermove', this.preventDefault, { passive:false });

		this.selectorIsResized = true;

		this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
		this.selectorStartWidth = parseInt(this.DOM.wrap.style.width);
		this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;

		this.beforeBeginChange = true;
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
			if (rightPos < this.selectorStartLeft)
			{
				rightPos = this.selectorStartLeft;
			}

			toDate = this.getDateByPos(rightPos, true);

			if (this.fullDayMode)
			{
				if (toDate.getTime() - this.currentDateFrom.getTime() < Util.getDayLength())
				{
					toDate = new Date(this.currentDateFrom.getTime() + Util.getDayLength());
				}

				timeTo = parseInt(toDate.getHours()) + Math.round((toDate.getMinutes() / 60) * 10) / 10;
				toDate.setHours(0, 0, 0, 0);
				if (timeTo > 12)
				{
					toDate = new Date(toDate.getTime() + Util.getDayLength());
					toDate.setHours(0, 0, 0, 0);
				}
				rightPos = this.getPosByDate(toDate);
			}
			else if (this.getScaleInfo().shownTimeFrom !== 0 || this.getScaleInfo().shownTimeTo !== 24)
			{
				let fromDate = this.getDateByPos(this.selectorStartLeft);
				if (toDate && fromDate && fromDate.getDate() !== toDate.getDate())
				{
					toDate = new Date(fromDate.getTime());
					toDate.setHours(this.getScaleInfo().shownTimeTo, 0, 0, 0);
					rightPos = this.getPosByDate(toDate);
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
			if (this.selectorRoundedRightPos < this.selectorStartLeft)
			{
				this.selectorRoundedRightPos = this.selectorStartLeft;
			}

			if (!this.fullDayMode && this.selectorRoundedRightPos - this.DOM.timelineWrap.scrollLeft > this.DOM.timelineFixedWrap.offsetWidth)
			{
				this.selectorRoundedRightPos = this.DOM.timelineWrap.scrollLeft + this.DOM.timelineFixedWrap.offsetWidth;
			}

			width = this.selectorRoundedRightPos - this.selectorStartLeft;

			this.DOM.wrap.style.width = width + 'px';
			this.showTitle(this.getDateByPos(this.selectorStartLeft), this.getDateByPos(this.selectorRoundedRightPos));
			this.checkStatus(this.selectorStartLeft, true);

			if (this.beforeBeginChange)
			{
				this.emit('onBeginChange');
				this.beforeBeginChange = false;
			}
		}
	}

	endResize()
	{
		document.removeEventListener('pointermove', this.preventDefault, { passive:false });
		if (this.selectorIsResized)
		{
			this.selectorIsResized = false;

			let left = parseInt(this.DOM.wrap.style.left);
			let right = left + parseInt(this.DOM.wrap.style.width);
			const from = this.getDateByPos(left);
			const to = this.getDateByPos(right);
			left = this.getPosByDate(from);
			right = this.getPosByDate(to);
			this.DOM.wrap.style.width = (right - left) + 'px';

			this.checkStatus(left, true);
			this.hideTitle();
			this.setValue();
		}
		this.selectorIsResized = false;
	}

	preventDefault(e)
	{
		e.preventDefault();
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

			this.emit(
				'doCheckStatus',
				new BaseEvent(
				{
						data: {
							dateFrom: fromDate,
							dateTo: toDate
						}
					}
				)
			);
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

	setValue(selectorPos = null, duration = null)
	{
		if (!selectorPos)
		{
			selectorPos = parseInt(this.DOM.wrap.style.left);
		}
		selectorPos = Math.max(0, selectorPos);
		const selectorWidth = parseInt(this.DOM.wrap.style.width);

		if (selectorPos + selectorWidth > parseInt(this.getTimelineWidth()))
		{
			selectorPos = parseInt(this.getTimelineWidth()) - selectorWidth;
		}

		const dateFrom = this.getDateByPos(selectorPos);
		let dateTo;
		if (duration)
		{
			dateTo = new Date(dateFrom.getTime() + duration);
		}
		else
		{
			dateTo = this.getDateByPos(selectorPos + selectorWidth, true);
		}

		if (dateFrom && dateTo)
		{
			if (this.fullDayMode)
			{
				const dayCount = Math.ceil((dateTo.getTime() - dateFrom.getTime()) / (1000 * 3600 * 24));
				dateTo = new Date(dateFrom.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
				dateTo.setHours(23, 55, 0, 0);
			}
			if (!this.fullDayMode && dateFrom.getDate() !== dateTo.getDate() && dateTo.getHours() !== 0 && dateTo.getMinutes() !== 0)
			{
				const duration = this.currentDateTo.getTime() - this.currentDateFrom.getTime();
				dateTo = new Date(dateFrom.getTime() + duration);
			}

			this.currentDateFrom = dateFrom;
			this.currentDateTo = dateTo;
			this.currentFullDay = this.fullDayMode;
			this.boundaryFrom = this.currentDateFrom;

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
		if (!this.fullDayMode && scaleInfo.shownTimeFrom === 0 && scaleInfo.shownTimeTo === 24)
		{
			return fromPos;
		}

		fromPos = fromPos || parseInt(this.DOM.wrap.style.left);
		selectorWidth = selectorWidth || parseInt(this.DOM.wrap.style.width);
		toPos = toPos || (fromPos + selectorWidth);
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
					if (fromDate.getHours() > 12)
					{
						fromDate = new Date(fromDate.getTime() + Util.getDayLength());
					}
					fromDate.setHours(0, 0, 0, 0);

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
		return Math.max(fromPos, 0);
	}

	transit(params = {})
	{
		this.DOM.wrap.style.display = 'block';

		let duration;
		if (Type.isDate(params.leftDate) && Type.isDate(params.rightDate))
		{
			if (this.fullDayMode)
			{
				const dayCount = Math.ceil((this.currentDateTo.getTime() - this.currentDateFrom.getTime()) / (1000 * 3600 * 24));
				params.leftDate.setHours(0, 0, 0, 0);
				params.rightDate = new Date(params.leftDate.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
				params.rightDate.setHours(23, 55, 0, 0);
			}
			duration = params.rightDate.getTime() - params.leftDate.getTime();
			const fromPos = this.getPosByDate(params.leftDate);
			const toPos = this.getPosByDate(params.rightDate);
			params.toX = fromPos;
			this.DOM.wrap.style.width = (toPos - fromPos) + 'px';
		}

		let
			fromX = params.fromX ?? parseInt(this.DOM.wrap.style.left),
			toX = Selector.roundPos(params.toX ?? fromX),
			triggerChangeEvents = params.triggerChangeEvents !== false,
			focus = !!params.focus;

		if (fromX !== toX)
		{
			if (this.animation)
			{
				this.animation.stop();
			}

			this.emit('onStartTransit');

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
						this.setValue(checkedPos, duration);
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
				this.setValue(false, duration);
			}

			if (focus === true)
			{
				this.focus(true, 300);
			}

			this.checkStatus();
		}
	}

	showTitle(from, to)
	{
		let
			fromDate = new Date(from.getTime()),
			toDate = new Date(to.getTime()),
			selectorTitle = this.getTitleNode(),
			selector = this.DOM.wrap;

		if (this.fullDayMode)
		{
			toDate = new Date(toDate.getTime() - 5 * 60 * 1000);
			if (toDate.getDate() === fromDate.getDate())
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

		selector.appendChild(selectorTitle);

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