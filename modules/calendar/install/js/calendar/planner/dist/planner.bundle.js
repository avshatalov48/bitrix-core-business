this.BX = this.BX || {};
(function (exports,main_core_events,calendar_util,main_core,calendar_ui_tools_draganddrop,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	class Selector extends main_core_events.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.DOM = {};
	    this.selectMode = false;
	    this.currentDateFrom = new Date();
	    this.currentDateTo = new Date();
	    this.currentFullDay = false;
	    this.useAnimation = true;
	    this.magnetDuration = 50;
	    this.stickDistanceInMinutes = 30;
	    this.magnetizeDistanceInMinutes = 15;
	    this.setEventNamespace('BX.Calendar.Planner.Selector');
	    this.selectMode = params.selectMode;
	    this.getPosByDate = params.getPosByDate;
	    this.getDateByPos = params.getDateByPos;
	    this.getPosDateMap = params.getPosDateMap;
	    this.getTimelineWidth = params.getTimelineWidth;
	    this.getScaleInfo = params.getScaleInfo;
	    this.solidStatus = params.solidStatus;
	    this.eventDragAndDrop = new calendar_ui_tools_draganddrop.EventDragAndDrop(params.getDateByPos, params.getPosByDate, params.getEvents);
	    this.useAnimation = params.useAnimation !== false;
	    this.DOM.timelineWrap = params.timelineWrap;
	    this.DOM.timelineFixedWrap = params.timelineFixedWrap;
	    this.render();
	  }
	  render() {
	    this.DOM.wrap = main_core.Tag.render(_t || (_t = _`
			<div class="calendar-planner-timeline-selector" data-bx-planner-meta="selector">
				<span data-bx-planner-meta="selector-resize-left" class="calendar-planner-timeline-drag-left"></span>
				<span class="calendar-planner-timeline-selector-grip"></span>
				<span data-bx-planner-meta="selector-resize-right" class="calendar-planner-timeline-drag-right"></span>
			</div>`));

	    // prefent draging selector and activating uploader controll in livefeed
	    this.DOM.wrap.ondrag = BX.False;
	    this.DOM.wrap.ondragstart = BX.False;
	    this.DOM.titleNode = main_core.Tag.render(_t2 || (_t2 = _`<div class="calendar-planner-selector-notice" style="display: none"></div>`));
	    if (this.selectMode) {
	      result.controlWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t3 || (_t3 = _`<div class="calendar-planner-selector-control"></div>`)));
	    }
	  }
	  getWrap() {
	    return this.DOM.wrap;
	  }
	  getTitleNode() {
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
	  update(params = {}) {
	    if (!main_core.Type.isPlainObject(params)) {
	      params = {};
	    }
	    params.updateScaleType = !!params.updateScaleType;
	    params.updateScaleLimits = !!params.updateScaleLimits;
	    params.animation = !!params.animation;
	    let from = main_core.Type.isDate(params.from) ? params.from : BX.parseDate(params.from) || this.currentDateFrom;
	    let to = main_core.Type.isDate(params.to) ? params.to : BX.parseDate(params.to) || this.currentDateTo;
	    this.fullDayMode = params.fullDay !== undefined ? params.fullDay : this.currentFullDay;
	    if (main_core.Type.isDate(from) && main_core.Type.isDate(to)) {
	      this.currentFullDay = this.fullDayMode;
	      if (this.fullDayMode) {
	        from.setHours(0, 0, 0, 0);
	        const dayCount = Math.ceil((to.getTime() - from.getTime() + 1) / (1000 * 3600 * 24));
	        to = new Date(from.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
	        to.setHours(23, 55, 0, 0);
	      }
	      this.currentDateFrom = from;
	      this.currentDateTo = to;

	      // Update selector
	      this.show(from, to, {
	        animation: params.animation,
	        focus: params.focus
	      });
	    }
	    const isSelectorInThePast = this.currentDateTo.getTime() < this.getScaleInfo().scaleDateFrom.getTime();
	    const isSelectorInTheFuture = this.currentDateFrom.getTime() > this.getScaleInfo().scaleDateTo.getTime();
	    if (isSelectorInThePast || isSelectorInTheFuture) {
	      this.DOM.wrap.style.display = 'none';
	    }
	  }
	  show(from, to, params) {
	    const animation = params.animation && this.useAnimation !== false;
	    const focus = params.focus === true;
	    const alignCenter = params.alignCenter !== false;
	    this.DOM.wrap.style.display = 'block';
	    if (main_core.Type.isDate(from) && main_core.Type.isDate(to)) {
	      let fromPos = this.getPosByDate(from),
	        toPos = this.getPosByDate(to);
	      this.DOM.wrap.style.width = toPos - fromPos + 'px';
	      if (animation && this.DOM.wrap.style.left && !this.currentFullDay) {
	        this.transit({
	          toX: fromPos,
	          triggerChangeEvents: false,
	          focus: focus
	        });
	      } else {
	        this.DOM.wrap.style.left = fromPos + 'px';
	        this.DOM.wrap.style.width = toPos - fromPos + 'px';
	        if (focus) {
	          this.focus(true, 200, alignCenter);
	        }
	        this.checkStatus(fromPos, true);
	      }
	    }
	  }
	  hide() {
	    this.DOM.wrap.style.display = 'none';
	  }
	  startMove() {
	    document.addEventListener('pointermove', this.preventDefault, {
	      passive: false
	    });
	    this.selectorIsDraged = true;
	    this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
	    this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;
	    this.eventDragAndDrop.onDragStart(this.currentDateTo.getTime() - this.currentDateFrom.getTime(), this.selectorStartLeft);
	    main_core.Dom.addClass(document.body, 'calendar-planner-unselectable');
	  }
	  move(x) {
	    if (this.selectorIsDraged) {
	      let pos = this.selectorStartLeft + x;

	      // Correct cursor position acording to changes of scrollleft
	      pos -= this.selectorStartScrollLeft - this.DOM.timelineWrap.scrollLeft;
	      pos = this.checkPosition(pos);
	      if (!this.getDateByPos(pos) || !this.getDateByPos(pos + parseInt(this.DOM.wrap.style.width))) {
	        return;
	      }
	      let boundary = this.eventDragAndDrop.getDragBoundary(pos);
	      boundary = this.getAutoScrollBoundary(boundary);
	      boundary = this.getConstrainedBoundary(boundary);
	      this.setBoundary(boundary);
	    }
	  }
	  getAutoScrollBoundary(boundary) {
	    const boundaryLeft = boundary.position - this.DOM.timelineWrap.scrollLeft;
	    const containerLeft = this.getPosByDate(this.getScaleInfo().scaleDateFrom);
	    const boundaryRight = boundaryLeft + boundary.size;
	    const containerRight = this.DOM.timelineFixedWrap.offsetWidth;
	    if (boundaryRight > containerRight) {
	      this.scrollSpeed = this.getSpeed(boundaryRight, containerRight);
	      boundary.position = containerRight + this.DOM.timelineWrap.scrollLeft - boundary.size;
	      this.setAutoScrollInterval(boundary, 1);
	    } else if (boundaryLeft < containerLeft) {
	      this.scrollSpeed = this.getSpeed(boundaryLeft, containerLeft);
	      boundary.position = containerLeft + this.DOM.timelineWrap.scrollLeft;
	      this.setAutoScrollInterval(boundary, -1);
	    } else {
	      this.stopAutoScroll();
	    }
	    return boundary;
	  }
	  getSpeed(x1, x2) {
	    return Math.floor(Math.sqrt(Math.abs(x1 - x2))) + 1;
	  }
	  setAutoScrollInterval(boundary, direction) {
	    if (!this.scrollInterval) {
	      this.scrollInterval = setInterval(() => {
	        if (!this.getDateByPos(boundary.position + this.scrollSpeed * direction) || !this.getDateByPos(boundary.position + boundary.size + this.scrollSpeed * direction)) {
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
	  stopAutoScroll() {
	    clearInterval(this.scrollInterval);
	    this.scrollInterval = false;
	  }
	  setBoundary(boundary) {
	    if (boundary.wasMagnetized) {
	      this.DOM.wrap.style.transition = 'left .05s, width .1s';
	    } else {
	      this.DOM.wrap.style.transition = 'width .1s';
	    }
	    this.DOM.wrap.style.width = boundary.size + 'px';
	    this.DOM.wrap.style.left = boundary.position + 'px';
	    this.showTitle(boundary.from, boundary.to);
	    this.checkStatus(boundary.position, true);
	  }
	  getConstrainedBoundary(boundary) {
	    if (boundary.wasMagnetized || this.fullDayMode) {
	      return boundary;
	    }
	    let from = new Date(boundary.from.getTime());
	    let to = new Date(boundary.to.getTime());
	    const duration = to.getTime() - from.getTime();
	    let position = boundary.position;
	    let size = boundary.size;
	    let wasMagnetized = false;
	    if (from.getHours() < this.getScaleInfo().shownTimeFrom) {
	      from.setHours(this.getScaleInfo().shownTimeFrom, 0, 0, 0);
	      to = new Date(from.getTime() + duration);
	      wasMagnetized = true;
	      position = this.getPosByDate(from);
	      size = this.getPosByDate(to) - position;
	    }
	    if (to.getHours() > this.getScaleInfo().shownTimeTo || to.getHours() === this.getScaleInfo().shownTimeTo && to.getMinutes() > 0) {
	      to.setHours(this.getScaleInfo().shownTimeTo, 0, 0, 0);
	      from = new Date(to.getTime() - duration);
	      wasMagnetized = true;
	      position = this.getPosByDate(from);
	      size = this.getPosByDate(to) - position;
	    }
	    return {
	      from,
	      to,
	      position,
	      size,
	      wasMagnetized
	    };
	  }
	  endMove() {
	    document.removeEventListener('pointermove', this.preventDefault, {
	      passive: false
	    });
	    this.stopAutoScroll();
	    if (this.selectorIsDraged) {
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
	  startResize() {
	    document.addEventListener('pointermove', this.preventDefault, {
	      passive: false
	    });
	    this.selectorIsResized = true;
	    this.selectorStartLeft = parseInt(this.DOM.wrap.style.left);
	    this.selectorStartWidth = parseInt(this.DOM.wrap.style.width);
	    this.selectorStartScrollLeft = this.DOM.timelineWrap.scrollLeft;
	  }
	  resize(x) {
	    if (this.selectorIsResized) {
	      let toDate,
	        timeTo,
	        width = this.selectorStartWidth + x;

	      // Correct cursor position according to changes of scrollLeft
	      width -= this.selectorStartScrollLeft - this.DOM.timelineWrap.scrollLeft;
	      let rightPos = Math.min(this.selectorStartLeft + width, this.getTimelineWidth());
	      if (rightPos < this.selectorStartLeft) {
	        rightPos = this.selectorStartLeft;
	      }
	      toDate = this.getDateByPos(rightPos, true);
	      if (this.fullDayMode) {
	        timeTo = parseInt(toDate.getHours()) + Math.round(toDate.getMinutes() / 60 * 10) / 10;
	        toDate.setHours(0, 0, 0, 0);
	        if (timeTo > 12) {
	          toDate = new Date(toDate.getTime() + calendar_util.Util.getDayLength());
	          toDate.setHours(0, 0, 0, 0);
	        }
	        rightPos = this.getPosByDate(toDate);
	      } else if (this.getScaleInfo().shownTimeFrom !== 0 || this.getScaleInfo().shownTimeTo !== 24) {
	        let fromDate = this.getDateByPos(this.selectorStartLeft);
	        if (toDate && fromDate && fromDate.getDate() !== toDate.getDate()) {
	          toDate = new Date(fromDate.getTime());
	          toDate.setHours(this.getScaleInfo().shownTimeTo, 0, 0, 0);
	          rightPos = this.getPosByDate(toDate);
	        }
	      }
	      if (this.getPosDateMap()[rightPos]) {
	        this.selectorRoundedRightPos = rightPos;
	      } else {
	        let roundedPos = Selector.roundPos(rightPos);
	        if (this.getPosDateMap()[roundedPos]) {
	          this.selectorRoundedRightPos = roundedPos;
	        }
	      }
	      if (this.selectorRoundedRightPos < this.selectorStartLeft) {
	        this.selectorRoundedRightPos = this.selectorStartLeft;
	      }
	      if (this.selectorRoundedRightPos - this.DOM.timelineWrap.scrollLeft > this.DOM.timelineFixedWrap.offsetWidth) {
	        this.selectorRoundedRightPos = this.DOM.timelineWrap.scrollLeft + this.DOM.timelineFixedWrap.offsetWidth;
	      }
	      width = this.selectorRoundedRightPos - this.selectorStartLeft;
	      this.DOM.wrap.style.width = width + 'px';
	      this.showTitle(this.getDateByPos(this.selectorStartLeft), this.getDateByPos(this.selectorRoundedRightPos));
	      this.checkStatus(this.selectorStartLeft, true);
	    }
	  }
	  endResize() {
	    document.removeEventListener('pointermove', this.preventDefault, {
	      passive: false
	    });
	    if (this.selectorIsResized) {
	      this.selectorIsResized = false;
	      let left = parseInt(this.DOM.wrap.style.left);
	      let right = left + parseInt(this.DOM.wrap.style.width);
	      const from = this.getDateByPos(left);
	      const to = this.getDateByPos(right);
	      left = this.getPosByDate(from);
	      right = this.getPosByDate(to);
	      this.DOM.wrap.style.width = right - left + 'px';
	      this.checkStatus(left, true);
	      this.hideTitle();
	      this.setValue();
	    }
	    this.selectorIsResized = false;
	  }
	  preventDefault(e) {
	    e.preventDefault();
	  }
	  isDragged() {
	    return this.selectorIsResized || this.selectorIsDraged;
	  }
	  checkStatus(selectorPos, checkPosition) {
	    if (this.solidStatus) {
	      main_core.Dom.removeClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
	      main_core.Dom.removeClass(this.mainContWrap, 'calendar-planner-selector-warning');
	      main_core.Dom.addClass(this.DOM.wrap, 'solid');
	    } else {
	      if (!selectorPos) {
	        selectorPos = Selector.roundPos(this.DOM.wrap.style.left);
	      }
	      let fromDate, toDate;
	      if (checkPosition === true || !this.currentDateFrom) {
	        let selectorWidth = parseInt(this.DOM.wrap.style.width),
	          fromPos = selectorPos,
	          toPos = fromPos + selectorWidth;
	        if (!fromPos && !toPos && !selectorWidth && this.lastFromDate) {
	          fromDate = this.lastFromDate;
	          toDate = this.lastToDate;
	        } else {
	          fromDate = this.getDateByPos(fromPos);
	          toDate = this.getDateByPos(toPos, true);
	          this.lastFromDate = fromDate;
	          this.lastToDate = toDate;
	        }
	      } else {
	        fromDate = this.currentDateFrom;
	        toDate = this.currentDateTo;
	      }
	      this.emit('doCheckStatus', new main_core_events.BaseEvent({
	        data: {
	          dateFrom: fromDate,
	          dateTo: toDate
	        }
	      }));
	    }
	  }
	  setSelectorStatus(status) {
	    this.selectorIsFree = status;
	    if (this.selectorIsFree) {
	      main_core.Dom.removeClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
	    } else {
	      main_core.Dom.addClass(this.DOM.wrap, 'calendar-planner-timeline-selector-warning');
	    }
	  }
	  setValue(selectorPos = null, duration = null) {
	    if (!selectorPos) {
	      selectorPos = parseInt(this.DOM.wrap.style.left);
	    }
	    selectorPos = Math.max(0, selectorPos);
	    const selectorWidth = parseInt(this.DOM.wrap.style.width);
	    if (selectorPos + selectorWidth > parseInt(this.getTimelineWidth())) {
	      selectorPos = parseInt(this.getTimelineWidth()) - selectorWidth;
	    }
	    const dateFrom = this.getDateByPos(selectorPos);
	    let dateTo;
	    if (duration) {
	      dateTo = new Date(dateFrom.getTime() + duration);
	    } else {
	      dateTo = this.getDateByPos(selectorPos + selectorWidth, true);
	    }
	    if (dateFrom && dateTo) {
	      if (this.fullDayMode) {
	        const dayCount = Math.ceil((dateTo.getTime() - dateFrom.getTime()) / (1000 * 3600 * 24));
	        dateTo = new Date(dateFrom.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
	        dateTo.setHours(23, 55, 0, 0);
	      }
	      if (!this.fullDayMode && dateFrom.getDate() !== dateTo.getDate() && dateTo.getHours() !== 0 && dateTo.getMinutes() !== 0) {
	        const duration = this.currentDateTo.getTime() - this.currentDateFrom.getTime();
	        dateTo = new Date(dateFrom.getTime() + duration);
	      }
	      this.currentDateFrom = dateFrom;
	      this.currentDateTo = dateTo;
	      this.currentFullDay = this.fullDayMode;
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          dateFrom: dateFrom,
	          dateTo: dateTo,
	          fullDay: this.fullDayMode
	        }
	      }));
	    }
	  }
	  checkPosition(fromPos, selectorWidth, toPos) {
	    let scaleInfo = this.getScaleInfo();
	    if (!this.fullDayMode && scaleInfo.shownTimeFrom === 0 && scaleInfo.shownTimeTo === 24) {
	      return fromPos;
	    }
	    fromPos = fromPos || parseInt(this.DOM.wrap.style.left);
	    selectorWidth = selectorWidth || parseInt(this.DOM.wrap.style.width);
	    toPos = toPos || fromPos + selectorWidth;
	    if (toPos > parseInt(this.getTimelineWidth())) {
	      fromPos = parseInt(this.getTimelineWidth()) - selectorWidth;
	    } else {
	      let fromDate = this.getDateByPos(fromPos),
	        toDate = this.getDateByPos(toPos, true),
	        timeFrom,
	        timeTo,
	        scaleTimeFrom = parseInt(scaleInfo.shownTimeFrom),
	        scaleTimeTo = parseInt(scaleInfo.shownTimeTo);
	      if (fromDate && toDate) {
	        if (this.fullDayMode) {
	          if (fromDate.getHours() > 12) {
	            fromDate = new Date(fromDate.getTime() + calendar_util.Util.getDayLength());
	          }
	          fromDate.setHours(0, 0, 0, 0);
	          fromPos = this.getPosByDate(fromDate);
	        } else if (fromDate.getDay() !== toDate.getDay()) {
	          timeFrom = parseInt(fromDate.getHours()) + Math.round(fromDate.getMinutes() / 60 * 10) / 10;
	          timeTo = parseInt(toDate.getHours()) + Math.round(toDate.getMinutes() / 60 * 10) / 10;
	          if (Math.abs(scaleTimeTo - timeFrom) > Math.abs(scaleTimeFrom - timeTo)) {
	            fromDate.setHours(scaleInfo.shownTimeTo, 0, 0, 0);
	            fromPos = this.getPosByDate(fromDate) - selectorWidth;
	          } else {
	            toDate.setHours(scaleInfo.shownTimeFrom, 0, 0, 0);
	            fromPos = this.getPosByDate(toDate);
	          }
	        }
	      }
	    }
	    return Math.max(fromPos, 0);
	  }
	  transit(params = {}) {
	    var _params$fromX, _params$toX;
	    this.DOM.wrap.style.display = 'block';
	    let duration;
	    if (main_core.Type.isDate(params.leftDate) && main_core.Type.isDate(params.rightDate)) {
	      if (this.fullDayMode) {
	        const dayCount = Math.ceil((this.currentDateTo.getTime() - this.currentDateFrom.getTime()) / (1000 * 3600 * 24));
	        params.leftDate.setHours(0, 0, 0, 0);
	        params.rightDate = new Date(params.leftDate.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
	        params.rightDate.setHours(23, 55, 0, 0);
	      }
	      duration = params.rightDate.getTime() - params.leftDate.getTime();
	      const fromPos = this.getPosByDate(params.leftDate);
	      const toPos = this.getPosByDate(params.rightDate);
	      params.toX = fromPos;
	      this.DOM.wrap.style.width = toPos - fromPos + 'px';
	    }
	    let fromX = (_params$fromX = params.fromX) != null ? _params$fromX : parseInt(this.DOM.wrap.style.left),
	      toX = Selector.roundPos((_params$toX = params.toX) != null ? _params$toX : fromX),
	      triggerChangeEvents = params.triggerChangeEvents !== false,
	      focus = !!params.focus;
	    if (fromX !== toX) {
	      if (this.animation) {
	        this.animation.stop();
	      }
	      this.animation = new BX.easing({
	        duration: 300,
	        start: {
	          left: fromX
	        },
	        finish: {
	          left: toX
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          this.DOM.wrap.style.left = state.left + 'px';
	        },
	        complete: () => {
	          this.animation = null;
	          let fromPos = parseInt(this.DOM.wrap.style.left),
	            checkedPos = this.checkPosition(fromPos);
	          if (checkedPos !== fromPos) {
	            this.DOM.wrap.style.left = checkedPos + 'px';
	          }
	          if (triggerChangeEvents) {
	            this.setValue(checkedPos, duration);
	          }
	          if (focus) {
	            this.focus(true, 300);
	          }
	          setTimeout(() => {
	            this.show(this.currentDateFrom, this.currentDateTo, {
	              animation: false,
	              focus: focus,
	              alignCenter: false
	            });
	          }, 200);
	          this.checkStatus(checkedPos);
	        }
	      });
	      this.animation.animate();
	    } else {
	      if (triggerChangeEvents) {
	        this.setValue(false, duration);
	      }
	      if (focus === true) {
	        this.focus(true, 300);
	      }
	      this.checkStatus();
	    }
	  }
	  showTitle(from, to) {
	    let fromDate = new Date(from.getTime()),
	      toDate = new Date(to.getTime()),
	      selectorTitle = this.getTitleNode(),
	      selector = this.DOM.wrap;
	    if (this.fullDayMode) {
	      toDate = new Date(toDate.getTime() - 5 * 60 * 1000);
	      if (toDate.getDate() === fromDate.getDate()) {
	        selectorTitle.innerHTML = BX.date.format('d F, D', fromDate.getTime() / 1000);
	      } else {
	        selectorTitle.innerHTML = BX.date.format('d F', fromDate.getTime() / 1000) + ' - ' + BX.date.format('d F', toDate.getTime() / 1000);
	      }
	    } else {
	      selectorTitle.removeAttribute('style');
	      selectorTitle.innerHTML = calendar_util.Util.formatTime(fromDate) + ' - ' + calendar_util.Util.formatTime(toDate);
	    }
	    if (this.selectMode && this.lastTouchedEntry) {
	      let entriesListWidth = this.compactMode ? 0 : this.entriesListWidth,
	        selectorTitleLeft = parseInt(selector.style.left) - this.DOM.timelineWrap.scrollLeft + entriesListWidth + parseInt(selector.style.width) / 2,
	        selectorTitleTop = parseInt(this.timelineDataCont.offsetTop) + parseInt(this.lastTouchedEntry.style.top) - 12;
	      selectorTitle.style.top = selectorTitleTop + 'px';
	      selectorTitle.style.left = selectorTitleLeft + 'px';
	    } else {
	      selector.appendChild(selectorTitle);
	    }
	    if (selectorTitle === this.selectorTitle) {
	      if (selectorTitle.style.display === 'none' || this.selectorHideTimeout) {
	        this.selectorHideTimeout = clearTimeout(this.selectorHideTimeout);
	        // Opacity animation
	        this.selectorTitle.style.display = '';
	        this.selectorTitle.style.opacity = 0;
	        new BX.easing({
	          duration: 400,
	          start: {
	            opacity: 0
	          },
	          finish: {
	            opacity: 100
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	          step: state => {
	            this.selectorTitle.style.opacity = state.opacity / 100;
	          },
	          complete: () => {
	            this.selectorTitle.removeAttribute('style');
	          }
	        }).animate();
	      }
	    } else {
	      selectorTitle.removeAttribute('style');
	    }
	  }
	  hideTitle(params = {}) {
	    if (!main_core.Type.isPlainObject(params)) params = {};
	    let timeoutName = params.selectorIndex === undefined ? 'selectorHideTimeout' : 'selectorHideTimeout_' + params.selectorIndex,
	      selectorTitle = params.selectorTitle || this.getTitleNode();
	    if (this[timeoutName]) this[timeoutName] = clearTimeout(this[timeoutName]);
	    if (params.timeout !== false) {
	      this[timeoutName] = setTimeout(() => {
	        params.timeout = false;
	        this.hideTitle(params);
	      }, 700);
	    } else {
	      // Opacity animation
	      selectorTitle.style.display = '';
	      selectorTitle.style.opacity = 1;
	      new BX.easing({
	        duration: 400,
	        start: {
	          opacity: 100
	        },
	        finish: {
	          opacity: 0
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	        step: state => {
	          selectorTitle.style.opacity = state.opacity / 100;
	        },
	        complete: () => {
	          selectorTitle.removeAttribute('style');
	          selectorTitle.style.display = 'none';
	        }
	      }).animate();
	    }
	  }
	  static roundPos(x) {
	    return Math.round(parseFloat(x));
	  }
	  focus(animation = true, timeout = 300, alignCenter) {
	    alignCenter = alignCenter === true;
	    if (this.focusTimeout) {
	      this.focusTimeout = !!clearTimeout(this.focusTimeout);
	    }
	    if (this.useAnimation === false) {
	      animation = false;
	    }
	    if (timeout) {
	      this.focusTimeout = setTimeout(() => {
	        this.focus(animation, false, alignCenter);
	      }, timeout);
	    } else {
	      const screenDelta = 10,
	        selectorLeft = parseInt(this.DOM.wrap.style.left),
	        selectorWidth = parseInt(this.DOM.wrap.style.width),
	        viewWidth = parseInt(this.DOM.timelineWrap.offsetWidth),
	        viewLeft = parseInt(this.DOM.timelineWrap.scrollLeft),
	        viewRight = viewLeft + viewWidth;
	      let newScrollLeft = viewLeft;
	      if (selectorLeft < viewLeft + screenDelta || selectorLeft > viewRight - screenDelta || alignCenter) {
	        // Selector is smaller than view - we puting it in the middle of the view
	        if (selectorWidth <= viewWidth) {
	          newScrollLeft = Math.max(Math.round(selectorLeft - (viewWidth - selectorWidth) / 2), screenDelta);
	        } else
	          // Selector is wider, so we adjust by left side
	          {
	            newScrollLeft = Math.max(Math.round(selectorLeft - screenDelta), screenDelta);
	          }
	      }
	      if (newScrollLeft !== viewLeft) {
	        if (animation === false) {
	          this.DOM.timelineWrap.scrollLeft = newScrollLeft;
	        } else {
	          new BX.easing({
	            duration: 300,
	            start: {
	              scrollLeft: this.DOM.timelineWrap.scrollLeft
	            },
	            finish: {
	              scrollLeft: newScrollLeft
	            },
	            transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	            step: state => {
	              this.DOM.timelineWrap.scrollLeft = state.scrollLeft;
	            },
	            complete: () => {}
	          }).animate();
	        }
	      }
	    }
	  }
	  getDuration() {
	    let duration = Math.round((this.currentDateTo - this.currentDateFrom) / 1000) * 1000;
	    if (this.fullDayMode) {
	      duration += calendar_util.Util.getDayLength();
	    }
	    return duration;
	  }
	  getDateFrom() {
	    return this.currentDateFrom;
	  }
	  getDateTo() {
	    return this.currentDateTo;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20,
	  _t21,
	  _t22,
	  _t23,
	  _t24,
	  _t25,
	  _t26,
	  _t27,
	  _t28,
	  _t29,
	  _t30,
	  _t31,
	  _t32,
	  _t33,
	  _t34,
	  _t35,
	  _t36,
	  _t37,
	  _t38,
	  _t39,
	  _t40,
	  _t41,
	  _t42,
	  _t43,
	  _t44,
	  _t45,
	  _t46,
	  _t47,
	  _t48,
	  _t49,
	  _t50,
	  _t51,
	  _t52,
	  _t53,
	  _t54;
	class Planner extends main_core_events.EventEmitter {
	  // in days
	  // in days
	  // in days
	  // ms

	  // in days

	  constructor(params = {}) {
	    super();
	    this.DOM = {};
	    this.config = {};
	    this.entryStatusMap = {
	      h: 'user-status-h',
	      y: 'user-status-y',
	      q: 'user-status-q',
	      n: 'user-status-n'
	    };
	    this.scaleTypes = ['15min', '30min', '1hour', '2hour', '1day'];
	    this.savedScaleType = null;
	    this.SCALE_OFFSET_BEFORE = 0;
	    this.SCALE_OFFSET_AFTER = 13;
	    this.EXPAND_OFFSET = 3;
	    this.EXPAND_DELAY = 2000;
	    this.REBUILD_DELAY = 100;
	    this.maxTimelineSize = 300;
	    this.MIN_ENTRY_ROWS = 3;
	    this.MAX_ENTRY_ROWS = 300;
	    this.width = 700;
	    this.height = 84;
	    this.minWidth = 700;
	    this.minHeight = 84;
	    this.workTime = [8, 18];
	    this.scrollStep = 10;
	    this.shown = false;
	    this.built = false;
	    this.locked = false;
	    this.shownScaleTimeFrom = 24;
	    this.shownScaleTimeTo = 0;
	    this.timelineCellWidthOrig = false;
	    this.proposeTimeLimit = 14;
	    this.expandTimelineDelay = 600;
	    this.limitScaleSizeMode = false;
	    this.useAnimation = true;
	    this.checkTimeCache = {};
	    this.entriesIndex = new Map();
	    this.solidStatus = false;
	    this.setEventNamespace('BX.Calendar.Planner');
	    this.config = params;
	    this.id = params.id;
	    this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat || 'd F, l';
	    this.userId = parseInt(params.userId || main_core.Loc.getMessage('USER_ID'));
	    this.DOM.wrap = params.wrap;
	    this.SCALE_TIME_FORMAT = BX.isAmPmMode() ? 'g a' : 'G';
	    this.expandTimelineDebounce = main_core.Runtime.debounce(this.expandTimeline, this.EXPAND_DELAY, this);
	    this.setConfig(params);
	  }
	  show() {
	    if (this.currentFromDate && this.currentToDate) {
	      const hourFrom = this.currentFromDate.getHours();
	      const hourTo = this.currentToDate.getHours() + Math.ceil(this.currentToDate.getMinutes() / 60);
	      this.extendScaleTimeLimits(hourFrom, hourTo);
	    }
	    if (this.currentFromDate && this.currentToDate) {
	      this.updateScaleLimitsFromEntry(this.currentFromDate, this.currentToDate);
	    }
	    if (this.hideAnimation) {
	      this.hideAnimation.stop();
	      this.hideAnimation = null;
	    }
	    if (!this.isBuilt()) {
	      this.build();
	      this.bindEventHandlers();
	    } else {
	      this.resizePlannerWidth(this.width);
	    }
	    this.buildTimeline();
	    this.DOM.wrap.style.display = '';
	    if (this.adjustWidth) {
	      this.resizePlannerWidth(this.DOM.timelineInnerWrap.offsetWidth);
	    }
	    this.selector.update({
	      from: this.currentFromDate,
	      to: this.currentToDate,
	      animation: false
	    });
	    if (this.currentFromDate && this.currentToDate && this.currentFromDate.getTime() >= this.scaleDateFrom.getTime() && this.currentToDate.getTime() <= this.scaleDateTo.getTime()) {
	      this.selector.focus(false, 0, true);
	    }
	    if (this.readonly) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-readonly');
	    } else {
	      main_core.Dom.removeClass(this.DOM.mainWrap, 'calendar-planner-readonly');
	    }
	    if (this.compactMode) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-compact');
	    } else {
	      main_core.Dom.removeClass(this.DOM.mainWrap, 'calendar-planner-compact');
	    }
	    this.DOM.entriesOuterWrap.style.display = this.compactMode ? 'none' : '';
	    {
	      if (parseInt(this.DOM.wrap.style.height) < this.height) {
	        this.DOM.wrap.style.height = this.height + 'px';
	      }
	      this.adjustHeight();
	    }
	    this.shown = true;
	  }
	  setConfig(params) {
	    this.todayLocMessage = main_core.Loc.getMessage('EC_PLANNER_TODAY');
	    this.setScaleType(params.scaleType);

	    // showTimelineDayTitle
	    if (params.showTimelineDayTitle !== undefined) {
	      this.showTimelineDayTitle = !!params.showTimelineDayTitle;
	    } else if (this.showTimelineDayTitle === undefined) {
	      this.showTimelineDayTitle = true;
	    }

	    // compactMode
	    if (params.compactMode !== undefined) {
	      this.compactMode = !!params.compactMode;
	    } else if (this.compactMode === undefined) {
	      this.compactMode = false;
	    }

	    // readonly
	    if (params.readonly !== undefined) {
	      this.readonly = !!params.readonly;
	    } else if (this.readonly === undefined) {
	      this.readonly = false;
	    }
	    if (this.compactMode) {
	      let compactHeight = 50;
	      if (this.showTimelineDayTitle && !this.isOneDayScale()) compactHeight += 20;
	      this.height = this.minHeight = compactHeight;
	    }

	    // Select mode
	    if (params.selectEntriesMode !== undefined) {
	      this.selectMode = !!params.selectEntriesMode;
	    } else if (this.selectMode === undefined) {
	      this.selectMode = false;
	    }
	    if (main_core.Type.isInteger(params.SCALE_OFFSET_BEFORE)) {
	      this.SCALE_OFFSET_BEFORE = parseInt(params.SCALE_OFFSET_BEFORE);
	    }
	    if (main_core.Type.isInteger(params.SCALE_OFFSET_AFTER)) {
	      this.SCALE_OFFSET_AFTER = parseInt(params.SCALE_OFFSET_AFTER);
	    }
	    if (main_core.Type.isInteger(params.maxTimelineSize)) {
	      this.maxTimelineSize = parseInt(params.maxTimelineSize);
	    }
	    if (main_core.Type.isInteger(params.minEntryRows)) {
	      this.MIN_ENTRY_ROWS = parseInt(params.minEntryRows);
	    }
	    if (main_core.Type.isInteger(params.maxEntryRows)) {
	      this.MAX_ENTRY_ROWS = parseInt(params.maxEntryRows);
	    }
	    if (main_core.Type.isInteger(params.width)) {
	      this.width = parseInt(params.width);
	    }
	    if (main_core.Type.isInteger(params.height)) {
	      this.height = parseInt(params.height);
	    }
	    if (main_core.Type.isInteger(params.minWidth)) {
	      this.minWidth = parseInt(params.minWidth);
	    }
	    if (main_core.Type.isInteger(params.minHeight)) {
	      this.minHeight = parseInt(params.minHeight);
	    }
	    this.width = Math.max(this.minWidth, this.width);
	    this.height = Math.max(this.minHeight, this.height);
	    if (main_core.Type.isArray(params.workTime)) {
	      this.workTime = params.workTime;
	    }
	    this.extendScaleTime(this.workTime[0], this.workTime[1]);
	    this.weekHolidays = params.weekHolidays || this.weekHolidays || [];
	    this.yearHolidays = params.yearHolidays || this.yearHolidays || [];
	    this.accuracy = params.accuracy || this.accuracy || 300; // 5 min
	    this.clickSelectorScaleAccuracy = params.clickSelectorScaleAccuracy || this.accuracy; // 5 min
	    this.selectorAccuracy = parseInt(params.selectorAccuracy) || this.selectorAccuracy || 300; // 5 min
	    this.entriesListWidth = parseInt(params.entriesListWidth) || this.entriesListWidth || 200;
	    this.timelineCellWidth = params.timelineCellWidth || this.timelineCellWidth || 40;
	    this.solidStatus = params.solidStatus === true;
	    this.showEntiesHeader = params.showEntiesHeader === undefined ? true : !!params.showEntiesHeader;
	    this.showEntryName = params.showEntryName === undefined ? true : !!params.showEntryName;
	    if (this.isOneDayScale() && this.timelineCellWidth < 100) {
	      this.timelineCellWidthOrig = this.timelineCellWidth;
	      this.timelineCellWidth = 100;
	    } else if (this.timelineCellWidthOrig && !this.isOneDayScale()) {
	      this.timelineCellWidth = this.timelineCellWidthOrig;
	      this.timelineCellWidthOrig = false;
	    }
	    if (this.allowAdjustCellWidth === undefined || params.allowAdjustCellWidth !== undefined) {
	      this.allowAdjustCellWidth = this.readonly && this.compactMode && params.allowAdjustCellWidth !== false;
	    }
	    if (params.locked !== undefined) {
	      this.locked = params.locked;
	    }
	    this.adjustCellWidth();

	    // Scale params
	    this.setScaleLimits(params.scaleDateFrom, params.scaleDateTo);
	  }
	  updateScaleLimitsFromEntry(from, to) {
	    if (from.getTime() > this.scaleDateTo.getTime() || to.getTime() < this.scaleDateFrom.getTime()) {
	      this.setScaleLimits(new Date(from.getTime()), new Date(to.getTime() + calendar_util.Util.getDayLength() * this.SCALE_OFFSET_AFTER));
	    }
	  }
	  setScaleLimits(scaleDateFrom, scaleDateTo) {
	    if (scaleDateFrom !== undefined) {
	      this.scaleDateFrom = main_core.Type.isDate(scaleDateFrom) ? scaleDateFrom : calendar_util.Util.parseDate(scaleDateFrom);
	    }
	    if (!main_core.Type.isDate(this.scaleDateFrom)) {
	      if (this.compactMode && this.readonly) {
	        this.scaleDateFrom = new Date();
	      } else {
	        this.scaleDateFrom = new Date(new Date().getTime() - calendar_util.Util.getDayLength() * this.SCALE_OFFSET_BEFORE);
	      }
	    }
	    this.scaleDateFrom.setHours(this.isOneDayScale() ? 0 : this.shownScaleTimeFrom, 0, 0, 0);
	    if (scaleDateTo !== undefined) {
	      this.scaleDateTo = BX.type.isString(scaleDateTo) ? calendar_util.Util.parseDate(scaleDateTo) : scaleDateTo;
	    }
	    if (!main_core.Type.isDate(this.scaleDateTo)) {
	      if (this.compactMode && this.readonly) {
	        this.scaleDateTo = new Date();
	      } else {
	        this.scaleDateTo = new Date(new Date().getTime() + calendar_util.Util.getDayLength() * this.SCALE_OFFSET_AFTER);
	      }
	    }
	    this.scaleDateTo.setHours(this.isOneDayScale() ? 0 : this.shownScaleTimeTo, 0, 0, 0);
	  }
	  extendScaleTimeLimits(fromTime, toTime) {
	    if (fromTime !== false && !isNaN(parseInt(fromTime))) {
	      this.shownScaleTimeFrom = Math.min(parseInt(fromTime), this.shownScaleTimeFrom, 23);
	      this.shownScaleTimeFrom = Math.max(this.shownScaleTimeFrom, 0);
	      if (this.scaleDateFrom) {
	        this.scaleDateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	      }
	    }
	    if (toTime !== false && !isNaN(parseInt(toTime))) {
	      this.shownScaleTimeTo = Math.max(parseInt(toTime), this.shownScaleTimeTo, 1);
	      this.shownScaleTimeTo = Math.min(this.shownScaleTimeTo, 24);
	      if (this.scaleDateTo) {
	        this.scaleDateTo.setHours(this.shownScaleTimeTo, 0, 0, 0);
	      }
	    }
	    if (this.shownScaleTimeFrom % 2 !== 0) {
	      this.shownScaleTimeFrom--;
	    }
	    if (this.shownScaleTimeTo % 2 !== 0) {
	      this.shownScaleTimeTo++;
	    }
	  }
	  SetLoadedDataLimits(from, to) {
	    if (from) {
	      this.loadedDataFrom = from.getTime ? from : calendar_util.Util.parseDate(from);
	    }
	    if (to) {
	      this.loadedDataTo = to.getTime ? to : calendar_util.Util.parseDate(to);
	    }
	  }
	  extendScaleTime(fromTime, toTime) {
	    const savedTimeFrom = this.shownScaleTimeFrom;
	    const savedTimeTo = this.shownScaleTimeTo;
	    this.extendScaleTimeLimits(fromTime, toTime);
	    if (fromTime === false && toTime !== false) {
	      setTimeout(() => {
	        this.extendTimelineToRight(savedTimeTo, this.shownScaleTimeTo);
	      }, 200);
	    }
	    if (fromTime !== false && toTime === false) {
	      setTimeout(() => {
	        this.extendTimelineToLeft(this.shownScaleTimeFrom, savedTimeFrom);
	      }, 200);
	    }
	    if (fromTime !== false && toTime !== false) {
	      this.rebuildDebounce();
	    }
	  }
	  adjustCellWidth() {
	    if (this.allowAdjustCellWidth) {
	      this.timelineCellWidth = Math.round(this.width / ((this.shownScaleTimeTo - this.shownScaleTimeFrom) * 3600 / this.scaleSize));
	    }
	  }
	  build() {
	    if (!main_core.Type.isDomNode(this.DOM.wrap)) {
	      throw new TypeError("Wrap is not DOM node");
	    }
	    this.DOM.wrap.style.width = this.width + 'px';

	    // Left part - list of users and other resources
	    let entriesListWidth = this.compactMode ? 0 : this.entriesListWidth;

	    // Timeline with accessibility information
	    this.DOM.mainWrap = this.DOM.wrap.appendChild(BX.create('DIV', {
	      props: {
	        className: 'calendar-planner-main-container calendar-planner-main-container-resource'
	      },
	      style: {
	        minHeight: this.minHeight + 'px',
	        height: this.height + 'px',
	        width: this.width + 'px'
	      }
	    }));
	    if (!this.showEntryName) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-entry-icons-only');
	    }
	    if (this.readonly) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-readonly');
	    }
	    this.DOM.entriesOuterWrap = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-planner-user-container" style="width: ${0}px; height: ${0}px;"></div>
		`), entriesListWidth, this.height));
	    calendar_util.Util.preventSelection(this.DOM.entriesOuterWrap);
	    if (this.compactMode) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-compact');
	      this.DOM.entriesOuterWrap.style.display = 'none';
	    }
	    if (this.isOneDayScale()) {
	      main_core.Dom.addClass(this.DOM.entriesOuterWrap, 'calendar-planner-no-daytitle');
	    } else {
	      main_core.Dom.removeClass(this.DOM.entriesOuterWrap, 'calendar-planner-no-daytitle');
	    }
	    if (this.showEntiesHeader !== false) {
	      this.DOM.entrieListHeader = this.DOM.entriesOuterWrap.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="calendar-planner-header"></div>
			`))).appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<div class="calendar-planner-general-info"></div>
			`))).appendChild(main_core.Tag.render(_t4 || (_t4 = _$1`
				<div class="calendar-planner-users-header"></div>
			`)));
	      this.entriesListTitleCounter = this.DOM.entrieListHeader.appendChild(main_core.Tag.render(_t5 || (_t5 = _$1`
				<span class="calendar-planner-users-item">
					${0}
				</span>
			`), main_core.Loc.getMessage('EC_PL_ATTENDEES_TITLE') + ' ')).appendChild(main_core.Tag.render(_t6 || (_t6 = _$1`<span></span>`)));
	    }
	    this.DOM.entrieListWrap = this.DOM.entriesOuterWrap.appendChild(main_core.Tag.render(_t7 || (_t7 = _$1`
			<div class="calendar-planner-user-container-inner"></div>
		`)));

	    // Fixed cont with specific width and height
	    this.DOM.timelineFixedWrap = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t8 || (_t8 = _$1`
			<div class="calendar-planner-timeline-wrapper" style="height: ${0}px"></div>
		`), this.height));
	    if (this.isLocked()) {
	      this.lock();
	    }

	    // overflow-y: hidden;
	    this.DOM.timelineVerticalConstraint = this.DOM.timelineFixedWrap.appendChild(main_core.Tag.render(_t9 || (_t9 = _$1`
			<div class="calendar-planner-timeline-constraint"></div>
		`)));

	    // Movable cont - used to move scale and data containers easy and at the same time
	    this.DOM.timelineInnerWrap = this.DOM.timelineVerticalConstraint.appendChild(main_core.Tag.render(_t10 || (_t10 = _$1`
			<div class="calendar-planner-timeline-inner-wrapper" data-bx-planner-meta="timeline"></div>
		`)));

	    // Scale container
	    this.DOM.timelineScaleWrap = this.DOM.timelineInnerWrap.appendChild(main_core.Tag.render(_t11 || (_t11 = _$1`
			<div class="calendar-planner-time"></div>
		`)));
	    calendar_util.Util.preventSelection(this.DOM.timelineScaleWrap);

	    // Accessibility container
	    this.DOM.timelineDataWrap = this.DOM.timelineInnerWrap.appendChild(main_core.Tag.render(_t12 || (_t12 = _$1`
			<div class="calendar-planner-timeline-container" style="height: ${0}px"></div>
		`), this.height));
	    // Container with accessibility entries elements
	    this.DOM.accessibilityWrap = this.DOM.timelineDataWrap.appendChild(main_core.Tag.render(_t13 || (_t13 = _$1`
			<div class="calendar-planner-acc-wrap"></div>
		`)));
	    if (this.isTodayButtonEnabled()) {
	      this.DOM.timelineVerticalConstraint.addEventListener('scroll', this.updateTodayButtonVisibility.bind(this));
	    }

	    // Selector
	    this.selector = new Selector({
	      selectMode: this.selectMode,
	      timelineFixedWrap: this.DOM.timelineFixedWrap,
	      timelineWrap: this.DOM.timelineVerticalConstraint,
	      getPosByDate: this.getPosByDate.bind(this),
	      getDateByPos: this.getDateByPos.bind(this),
	      getEvents: this.getAllEvents.bind(this),
	      getPosDateMap: () => {
	        return this.posDateMap;
	      },
	      useAnimation: this.useAnimation,
	      solidStatus: this.solidStatus,
	      getScaleInfo: () => {
	        return {
	          scale: this.scaleType,
	          shownTimeFrom: this.shownScaleTimeFrom,
	          shownTimeTo: this.shownScaleTimeTo,
	          scaleDateFrom: this.scaleDateFrom,
	          scaleDateTo: this.scaleDateTo
	        };
	      },
	      getTimelineWidth: () => {
	        return parseInt(this.DOM.timelineInnerWrap.style.width);
	      }
	    });
	    this.DOM.timelineDataWrap.appendChild(this.selector.getWrap());
	    this.DOM.mainWrap.appendChild(this.selector.getTitleNode());
	    this.selector.subscribe('onChange', this.handleSelectorChanges.bind(this));
	    this.selector.subscribe('doCheckStatus', this.doCheckSelectorStatus.bind(this));
	    if (this.selectMode) {
	      this.selectedEntriesWrap = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t14 || (_t14 = _$1`
				<div class="calendar-planner-timeline-select-entries-wrap"></div>
			`)));
	      this.hoverRow = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t15 || (_t15 = _$1`
				<div class="calendar-planner-timeline-hover-row" style="top: 0; width: ${0}px"></div>
			`), parseInt(this.DOM.mainWrap.offsetWidth)));
	      main_core.Event.bind(document, 'mousemove', this.mouseMoveHandler.bind(this));
	    }
	    if (!this.compactMode) {
	      this.DOM.settingsButton = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t16 || (_t16 = _$1`<div class="calendar-planner-settings-icon-container" title="${0}"><span class="calendar-planner-settings-title">${0}</span><span class="calendar-planner-settings-icon"></span></div>`), main_core.Loc.getMessage('EC_PL_SETTINGS_SCALE'), main_core.Loc.getMessage('EC_PL_SETTINGS_SCALE')));
	      main_core.Event.bind(this.DOM.settingsButton, 'click', () => this.showSettingsPopup());
	    }
	    this.built = true;
	  }
	  buildTimeline(clearCache) {
	    if (this.isBuilt() && (this.lastTimelineKey !== this.getTimelineShownKey() || clearCache === true)) {
	      main_core.Dom.clean(this.DOM.timelineScaleWrap);
	      this.scaleData = this.getScaleData();
	      let outerDayCont,
	        dayTitle,
	        cont = this.DOM.timelineScaleWrap;
	      this.futureDayTitles = [];
	      this.todayButtonPivotDay = undefined;
	      for (let i = 0; i < this.scaleData.length; i++) {
	        if (this.showTimelineDayTitle && !this.isOneDayScale()) {
	          if (this.scaleDayTitles[this.scaleData[i].daystamp]) {
	            cont = this.scaleDayTitles[this.scaleData[i].daystamp];
	          } else {
	            const date = new Date(this.scaleData[i].timestamp);
	            date.setHours(0, 0, 0, 0);
	            const today = new Date();
	            today.setHours(0, 0, 0, 0);
	            outerDayCont = this.DOM.timelineScaleWrap.appendChild(main_core.Tag.render(_t17 || (_t17 = _$1`
							<div class="calendar-planner-time-day-outer"></div>
						`)));
	            let dayTitleClass = 'calendar-planner-time-day-title';
	            if (date.getTime() < today.getTime()) {
	              dayTitleClass += ' calendar-planner-time-day-past';
	            }

	            //F d, l
	            dayTitle = outerDayCont.appendChild(main_core.Tag.render(_t18 || (_t18 = _$1`
							<div class="${0}">
								<span>${0}</span>
								<div class="calendar-planner-time-day-border"></div>
							</div>
						`), dayTitleClass, BX.date.format(this.dayOfWeekMonthFormat, this.scaleData[i].timestamp / 1000)));
	            if (date.getTime() > today.getTime()) {
	              this.futureDayTitles.push(dayTitle.querySelector('span'));
	            }
	            if (date.getTime() === today.getTime() && this.isTodayButtonEnabled()) {
	              this.todayTitleButton = dayTitle.firstElementChild.appendChild(main_core.Tag.render(_t19 || (_t19 = _$1`
								<div class="calendar-planner-today-button"></div>
							`)));
	              this.todayTitleButton.innerHTML = this.todayLocMessage;
	              this.todayTitleButton.addEventListener('click', this.todayButtonClickHandler.bind(this));
	              this.todayButtonPivotDay = outerDayCont;
	            }
	            cont = outerDayCont.appendChild(main_core.Tag.render(_t20 || (_t20 = _$1`
							<div class="calendar-planner-time-day"></div>
						`)));
	            this.scaleDayTitles[this.scaleData[i].daystamp] = cont;
	          }
	        }
	        let className = 'calendar-planner-time-hour-item' + (this.scaleData[i].dayStart ? ' calendar-planner-day-start' : '');
	        if ((this.scaleType === '15min' || this.scaleType === '30min') && this.scaleData[i].title !== '') {
	          className += ' calendar-planner-time-hour-bold';
	        }
	        this.scaleData[i].cell = cont.appendChild(BX.create('DIV', {
	          props: {
	            className: className
	          },
	          style: {
	            width: this.timelineCellWidth + 'px',
	            minWidth: this.timelineCellWidth + 'px'
	          },
	          html: this.scaleData[i].title ? '<i>' + this.scaleData[i].title + '</i>' : ''
	        }));
	        if (!this.isOneDayScale() && this.scaleData[i + 1] && this.scaleData[i + 1].dayStart) {
	          cont.appendChild(main_core.Tag.render(_t21 || (_t21 = _$1`
						<div class="calendar-planner-timeline-border"></div>
					`)));
	        }
	      }
	      let mapDatePosRes = this.mapDatePos();
	      this.posDateMap = mapDatePosRes.posDateMap;
	      const timelineOffset = this.DOM.timelineScaleWrap.offsetWidth;
	      this.DOM.timelineInnerWrap.style.width = timelineOffset + 'px';
	      this.DOM.entrieListWrap.style.top = parseInt(this.DOM.timelineDataWrap.offsetTop) + 10 + 'px';
	      this.lastTimelineKey = this.getTimelineShownKey();
	      this.checkRebuildTimeout(timelineOffset);
	      this.buildTodayButtonWrap();
	    }
	  }
	  buildTodayButtonWrap() {
	    if (!this.isTodayButtonEnabled()) {
	      return;
	    }
	    if (this.todayButton) {
	      this.todayButton.remove();
	    }
	    if (this.todayRightButton) {
	      this.todayRightButton.remove();
	    }
	    if (this.DOM.todayButtonContainer) {
	      this.DOM.todayButtonContainer.remove();
	    }
	    if (this.isOneDayScale()) {
	      return;
	    }
	    const todayButton = this.DOM.entriesOuterWrap.appendChild(main_core.Tag.render(_t22 || (_t22 = _$1`
			<div class="calendar-planner-today-button">${0}</div>
		`), this.todayLocMessage));
	    this.todayButtonWidth = todayButton.offsetWidth;
	    todayButton.innerHTML = this.todayLocMessage + ' &rarr;';
	    this.todayButtonRightWidth = todayButton.offsetWidth;
	    todayButton.innerHTML = this.todayLocMessage + ' &larr;';
	    this.todayButtonLeftWidth = todayButton.offsetWidth;
	    const top = BX.pos(todayButton).top - BX.pos(this.DOM.timelineScaleWrap).top;
	    todayButton.remove();
	    let left = 0;
	    if (this.todayButtonPivotDay) {
	      left = this.todayButtonPivotDay.offsetLeft + this.todayButtonPivotDay.offsetWidth - 10 - this.todayButtonWidth + 1;
	    }
	    const width = this.DOM.timelineScaleWrap.offsetWidth - left;
	    this.DOM.todayButtonContainer = this.DOM.timelineScaleWrap.appendChild(main_core.Tag.render(_t23 || (_t23 = _$1`
			<div class="calendar-planner-today-button-container" style="width: ${0}px; left: ${0}px; top: ${0}px;"></div>
		`), width, left, top));
	    this.todayButton = this.DOM.todayButtonContainer.appendChild(main_core.Tag.render(_t24 || (_t24 = _$1`
			<div class="calendar-planner-today-button" style="width: ${0}px; direction: rtl;">${0}</div>
		`), this.todayButtonWidth, this.todayLocMessage));
	    this.todayRightButton = this.DOM.timelineVerticalConstraint.appendChild(main_core.Tag.render(_t25 || (_t25 = _$1`
			<div class="calendar-planner-today-button" style="right: 0; top: 5px; position: absolute;">${0}</div>
		`), this.todayLocMessage));
	    this.todayButton.addEventListener('click', this.todayButtonClickHandler.bind(this));
	    this.todayRightButton.addEventListener('click', this.todayButtonClickHandler.bind(this));
	    this.updateTodayButtonVisibility(false);
	    if (this.isLocked() && this.DOM.todayButtonContainer) {
	      main_core.Dom.addClass(this.DOM.todayButtonContainer, '--lock');
	    }
	  }
	  getTimelineShownKey() {
	    return 'tm_' + this.scaleDateFrom.getTime() + '_' + this.scaleDateTo.getTime();
	  }
	  checkRebuildTimeout(timelineOffset, timeout = 300) {
	    if (!this._checkRebuildTimeoutCount) {
	      this._checkRebuildTimeoutCount = 0;
	    }
	    if (this.rebuildTimeout) {
	      this.rebuildTimeout = !!clearTimeout(this.rebuildTimeout);
	    }
	    if (this._checkRebuildTimeoutCount <= 10 && main_core.Type.isElementNode(this.DOM.timelineScaleWrap) && main_core.Dom.isShown(this.DOM.timelineScaleWrap)) {
	      this._checkRebuildTimeoutCount++;
	      this.rebuildTimeout = setTimeout(() => {
	        if (timelineOffset !== this.DOM.timelineScaleWrap.offsetWidth) {
	          if (this.rebuildTimeout) {
	            this.rebuildTimeout = !!clearTimeout(this.rebuildTimeout);
	          }
	          this.rebuild();
	          if (this.selector) {
	            this.selector.focus(true, 300);
	          }
	        } else {
	          this.checkRebuildTimeout(timelineOffset, timeout);
	        }
	      }, timeout);
	    } else {
	      delete this._checkRebuildTimeoutCount;
	    }
	  }
	  rebuildDebounce(timeout = this.REBUILD_DELAY) {
	    main_core.Runtime.debounce(this.rebuild, timeout, this)();
	  }
	  extendTimelineToLeft(extendedTimeFrom, extendedTimeTo) {
	    this.extendTimeline(extendedTimeFrom, extendedTimeTo);
	  }
	  extendTimelineToRight(extendedTimeFrom, extendedTimeTo) {
	    this.extendTimeline(extendedTimeFrom, extendedTimeTo, true);
	  }
	  extendTimeline(extendedTimeFrom, extendedTimeTo, isToRight = false) {
	    if (!this.DOM.timelineScaleWrap) {
	      return;
	    }
	    const isToLeft = !isToRight;
	    const dayNodeList = this.DOM.timelineScaleWrap.querySelectorAll('.calendar-planner-time-day');
	    const dayCount = dayNodeList.length;
	    const nodeCountInDay = this.scaleData.length / dayCount;
	    const extendCount = extendedTimeTo - extendedTimeFrom;
	    let cellsInsertedOnLeftCount = 0;
	    const insertedNodes = [];
	    let pivotScaleDatumOfDayIndex = isToRight ? nodeCountInDay - 1 : 0;
	    for (const dayNode of dayNodeList) {
	      const pivotNodeOfDay = isToLeft ? dayNode.children[0] : dayNode.querySelector('.calendar-planner-timeline-border');
	      if (isToLeft) {
	        this.scaleData[pivotScaleDatumOfDayIndex].dayStart = false;
	      }
	      const daystamp = this.scaleData[pivotScaleDatumOfDayIndex].daystamp;
	      let toTimestamp, fromTimestamp;
	      if (isToLeft) {
	        toTimestamp = this.scaleData[pivotScaleDatumOfDayIndex].timestamp / 1000;
	        fromTimestamp = toTimestamp - 3600 * extendCount;
	        if (new Date(fromTimestamp * 1000).getHours() !== extendedTimeFrom) {
	          return;
	        }
	      } else {
	        fromTimestamp = this.scaleData[pivotScaleDatumOfDayIndex].timestamp / 1000 + this.scaleSize;
	        toTimestamp = fromTimestamp + 3600 * extendCount;
	        if (new Date(fromTimestamp * 1000).getHours() !== extendedTimeFrom) {
	          return;
	        }
	      }
	      for (let insertedTimestamp = fromTimestamp, i = 0; insertedTimestamp < toTimestamp; insertedTimestamp += this.scaleSize, i++) {
	        const title = BX.date.format('i', insertedTimestamp) === '00' ? BX.date.format(this.SCALE_TIME_FORMAT, insertedTimestamp) : '';
	        if (insertedTimestamp < this.currentFromDate.getTime() / 1000 - (isToLeft ? 3600 * 12 : 0)) {
	          cellsInsertedOnLeftCount++;
	        }
	        let animationClass = 'expand-width-no-animation';
	        if (isToLeft && insertedTimestamp > this.currentFromDate.getTime() / 1000 - 3600 * 12 && insertedTimestamp < this.currentFromDate.getTime() / 1000 + 3600 * 12 || isToRight && insertedTimestamp > this.currentFromDate.getTime() / 1000 && insertedTimestamp < this.currentFromDate.getTime() / 1000 + 3600 * 24) {
	          animationClass = 'expand-width-0-40';
	        }
	        const insertedCell = BX.create('DIV', {
	          props: {
	            className: 'calendar-planner-time-hour-item' + ' ' + animationClass
	          },
	          style: {
	            width: this.timelineCellWidth + 'px',
	            minWidth: this.timelineCellWidth + 'px'
	          },
	          html: '<i>' + title + '</i>'
	        });
	        insertedNodes.push(insertedCell);
	        dayNode.insertBefore(insertedCell, pivotNodeOfDay);
	        const insertedScaleDatum = {
	          daystamp: daystamp,
	          timestamp: insertedTimestamp * 1000,
	          value: insertedTimestamp * 1000,
	          title: title,
	          dayStart: isToLeft && i === 0,
	          cell: insertedCell
	        };
	        this.scaleData.splice(i + pivotScaleDatumOfDayIndex + (isToRight ? 1 : 0), 0, insertedScaleDatum);
	      }
	      if (isToLeft) {
	        pivotNodeOfDay.classList.remove('calendar-planner-day-start');
	        dayNode.children[0].classList.add('calendar-planner-day-start');
	      }
	      pivotScaleDatumOfDayIndex += nodeCountInDay + extendCount * 3600 / this.scaleSize;
	    }

	    // set scroll for timeline to compensate width of static inserted cells
	    const scroll = this.DOM.timelineVerticalConstraint.scrollLeft;
	    this.DOM.timelineVerticalConstraint.scrollLeft = scroll + cellsInsertedOnLeftCount * this.timelineCellWidth;

	    // get accessibility events for animation
	    const midnight = new Date(this.currentFromDate.getTime());
	    midnight.setHours(0, 0, 0, 0);
	    if (isToRight) {
	      midnight.setDate(midnight.getDate() + 1);
	    }
	    const visibleEvents = this.getVisibleEvents();
	    const animatedEvents = this.getEventsAfter(visibleEvents, midnight);
	    this.update(this.entries, this.accessibility);

	    // update selector and visible events position during the css animation
	    new BX.easing({
	      duration: 200,
	      start: {},
	      finish: {},
	      transition: BX.easing.makeEaseOut(BX.easing.transitions.linear),
	      step: () => {
	        this.buildTodayButtonWrap();
	        this.selector.update();
	        for (const event of animatedEvents) {
	          event.node.style.left = this.getPosByDate(new Date(event.fromTimestamp)) + 'px';
	        }
	      },
	      complete: () => {
	        for (const node of insertedNodes) {
	          node.classList.remove('expand-width-no-animation');
	          node.classList.remove('expand-width-0-40');
	        }
	        this.updateTimelineAfterExtend();
	      }
	    }).animate();
	  }
	  updateTimelineAfterExtend() {
	    let mapDatePosRes = this.mapDatePos();
	    this.posDateMap = mapDatePosRes.posDateMap;
	    const timelineOffset = this.DOM.timelineScaleWrap.offsetWidth;
	    this.DOM.timelineInnerWrap.style.width = timelineOffset + 'px';
	    this.DOM.entrieListWrap.style.top = parseInt(this.DOM.timelineDataWrap.offsetTop) + 10 + 'px';
	    this.lastTimelineKey = this.getTimelineShownKey();
	    this.update(this.entries, this.accessibility);
	    this.adjustHeight();
	    this.resizePlannerWidth(this.width);
	    this.selector.update();
	    this.clearCacheTime();
	    this.buildTodayButtonWrap();
	  }
	  rebuild(params = {}) {
	    if (this.isBuilt()) {
	      this.buildTimeline(true);
	      this.update(this.entries, this.accessibility);
	      this.adjustHeight();
	      this.resizePlannerWidth(this.width);
	      if (params.updateSelector !== false) {
	        this.selector.update(params.selectorParams);
	        this.selector.focus(false, 0, true);
	      }
	      this.clearCacheTime();
	    }
	  }
	  getScaleData() {
	    this.scaleData = [];
	    this.scaleDayTitles = {};
	    let ts,
	      scaleFrom,
	      scaleTo,
	      time,
	      dayStamp,
	      title,
	      curDayStamp = false,
	      timeFrom = this.isOneDayScale() ? 0 : this.shownScaleTimeFrom,
	      timeTo = this.isOneDayScale() ? 0 : this.shownScaleTimeTo;
	    this.scaleDateFrom.setHours(timeFrom, 0, 0, 0);
	    this.scaleDateTo.setHours(timeTo, 0, 0, 0);
	    scaleFrom = this.scaleDateFrom.getTime();
	    scaleTo = this.scaleDateTo.getTime();
	    for (ts = scaleFrom; ts < scaleTo; ts += this.scaleSize * 1000) {
	      time = parseFloat(BX.date.format('H.i', ts / 1000));
	      if (this.isOneDayScale()) title = BX.date.format('d F, D', ts / 1000);else title = BX.date.format('i', ts / 1000) === '00' ? BX.date.format(this.SCALE_TIME_FORMAT, ts / 1000) : '';
	      if (this.isOneDayScale() || time >= timeFrom && time < timeTo) {
	        dayStamp = BX.date.format('d.m.Y', ts / 1000);
	        this.scaleData.push({
	          daystamp: dayStamp,
	          timestamp: ts,
	          value: ts,
	          title: title,
	          dayStart: curDayStamp !== dayStamp
	        });
	        curDayStamp = dayStamp;
	      }
	    }
	    return this.scaleData;
	  }
	  isOneDayScale() {
	    return this.scaleType === '1day';
	  }
	  static prepareAccessibilityItem(entry) {
	    if (!main_core.Type.isDate(entry.from)) {
	      entry.from = calendar_util.Util.parseDate(entry.dateFrom);
	    }
	    if (!main_core.Type.isDate(entry.to)) {
	      entry.to = calendar_util.Util.parseDate(entry.dateTo);
	    }
	    if (!main_core.Type.isDate(entry.from) || !main_core.Type.isDate(entry.to)) {
	      return false;
	    }
	    entry.from.setSeconds(0, 0);
	    entry.fromTimestamp = entry.from.getTime();
	    entry.to.setSeconds(0, 0);
	    entry.toTimestamp = entry.to.getTime();
	    if (!main_core.Type.isDate(entry.toReal)) {
	      // Full day
	      if ((entry.toTimestamp - entry.fromTimestamp) % calendar_util.Util.getDayLength() === 0 && BX.date.format('H:i', entry.toTimestamp / 1000) === '00:00') {
	        entry.toReal = new Date(entry.to.getTime() + calendar_util.Util.getDayLength());
	        entry.toReal.setSeconds(0, 0);
	        entry.toTimestampReal = entry.toReal.getTime();
	      } else {
	        entry.toReal = entry.to;
	        entry.toTimestampReal = entry.toTimestamp;
	      }
	    }
	    return entry;
	  }
	  addAccessibilityItem(entry, wrap) {
	    let timeFrom,
	      timeTo,
	      hidden = false,
	      fromTimestamp = entry.fromTimestamp,
	      toTimestamp = entry.toTimestampReal || entry.toTimestamp,
	      shownScaleTimeFrom = this.isOneDayScale() ? 0 : this.shownScaleTimeFrom,
	      shownScaleTimeTo = this.isOneDayScale() ? 24 : this.shownScaleTimeTo,
	      from = new Date(fromTimestamp),
	      to = new Date(toTimestamp);
	    timeFrom = parseInt(from.getHours()) + from.getMinutes() / 60;
	    timeTo = parseInt(to.getHours()) + to.getMinutes() / 60;
	    if (timeFrom > shownScaleTimeTo) {
	      from = new Date(from.getTime() + calendar_util.Util.getDayLength() - 1);
	      from.setHours(shownScaleTimeFrom, 0, 0, 0);
	      if (from.getTime() >= to.getTime()) {
	        hidden = true;
	      }
	    }
	    if (!hidden && timeFrom < shownScaleTimeFrom) {
	      from.setHours(shownScaleTimeFrom, 0, 0, 0);
	      if (from.getTime() >= to.getTime()) {
	        hidden = true;
	      }
	    }
	    if (!hidden && timeTo > shownScaleTimeTo) {
	      to.setHours(shownScaleTimeTo, 0, 0, 0);
	      if (from.getTime() >= to.getTime()) {
	        hidden = true;
	      }
	    }
	    if (!hidden && timeTo < shownScaleTimeFrom) {
	      to = new Date(to.getTime() - calendar_util.Util.getDayLength() + 1);
	      to.setHours(shownScaleTimeTo, 0, 0, 0);
	      if (from.getTime() >= to.getTime()) {
	        hidden = true;
	      }
	    }
	    if (!hidden && from.getTime() < this.scaleDateTo) {
	      let fromPos = this.getPosByDate(from),
	        toPos = Math.min(this.getPosByDate(to), this.DOM.timelineScaleWrap.offsetWidth);
	      entry.node = wrap.appendChild(BX.create('DIV', {
	        props: {
	          className: 'calendar-planner-acc-entry' + (entry.type && entry.type === 'hr' ? ' calendar-planner-acc-entry-hr' : '')
	        },
	        style: {
	          left: fromPos + 'px',
	          width: Math.max(toPos - fromPos, 3) + 'px'
	        }
	      }));
	      if (entry.title || entry.name) {
	        entry.node.title = entry.title || entry.name;
	      }
	    }
	  }
	  displayEntryRow(entry, accessibility = []) {
	    let rowWrap;
	    if (entry.type === 'moreLink') {
	      rowWrap = this.DOM.entrieListWrap.appendChild(main_core.Tag.render(_t26 || (_t26 = _$1`
				<div class="calendar-planner-user"></div>
			`)));
	      if (this.showEntryName) {
	        this.DOM.showMoreUsersLink = rowWrap.appendChild(main_core.Tag.render(_t27 || (_t27 = _$1`
					<div class="calendar-planner-all-users" title="${0}">
						${0}
					</div>
				`), entry.title || '', entry.name));
	      } else {
	        this.DOM.showMoreUsersLink = rowWrap.appendChild(main_core.Tag.render(_t28 || (_t28 = _$1`
					<div class="calendar-planner-users-more" title="${0}">
						<span class="calendar-planner-users-more-btn"></span>
					</div>
				`), entry.name || ''));
	      }
	      main_core.Event.bind(this.DOM.showMoreUsersLink, 'click', () => this.showMoreUsers());
	    } else if (entry.type === 'lastUsers') {
	      rowWrap = this.DOM.entrieListWrap.appendChild(main_core.Tag.render(_t29 || (_t29 = _$1`	
				<div class="calendar-planner-user"></div>
			`)));
	      if (this.showEntryName) {
	        this.DOM.showMoreUsersLink = rowWrap.appendChild(main_core.Tag.render(_t30 || (_t30 = _$1`
					<div class="calendar-planner-all-users calendar-planner-last-users" title="${0}">
						${0}
					</div>
				`), entry.title || '', entry.name));
	      } else {
	        this.DOM.showMoreUsersLink = rowWrap.appendChild(main_core.Tag.render(_t31 || (_t31 = _$1`
					<div class="calendar-planner-users-more" title="${0}">
						<span class="calendar-planner-users-last-btn"></span>
					</div>
				`), entry.title || entry.name));
	      }
	    } else if (entry.id && entry.type === 'user') {
	      rowWrap = this.DOM.entrieListWrap.appendChild(BX.create('DIV', {
	        attrs: {
	          'data-bx-planner-entry': entry.uid,
	          className: 'calendar-planner-user' + (entry.emailUser ? ' calendar-planner-email-user' : '')
	        }
	      }));
	      if (entry.status && this.entryStatusMap[entry.status]) {
	        rowWrap.appendChild(BX.create('SPAN', {
	          props: {
	            className: 'calendar-planner-user-status-icon ' + this.entryStatusMap[entry.status],
	            title: main_core.Loc.getMessage('EC_PL_STATUS_' + entry.status.toUpperCase())
	          }
	        }));
	      }
	      rowWrap.appendChild(Planner.getEntryAvatarNode(entry));
	      if (this.showEntryName) {
	        rowWrap.appendChild(main_core.Tag.render(_t32 || (_t32 = _$1`
					<span class="calendar-planner-user-name"></span>
				`))).appendChild(BX.create('SPAN', {
	          props: {
	            className: 'calendar-planner-entry-name'
	          },
	          attrs: {
	            'bx-tooltip-user-id': entry.id,
	            'bx-tooltip-classname': 'calendar-planner-user-tooltip'
	          },
	          style: {
	            width: this.entriesListWidth - 42 + 'px'
	          },
	          text: entry.name
	        }));
	      }
	    } else if (entry.id && entry.type === 'room') {
	      rowWrap = this.DOM.entrieListWrap.appendChild(main_core.Tag.render(_t33 || (_t33 = _$1`
				<div class="calendar-planner-user"></div>
			`)));
	      if (this.showEntryName) {
	        rowWrap.appendChild(main_core.Tag.render(_t34 || (_t34 = _$1`
					<span class="calendar-planner-user-name"></span>
				`))).appendChild(main_core.Tag.render(_t35 || (_t35 = _$1`
					<span class="calendar-planner-entry-name" style="width: ${0}px;">
						${0}
					</span>
				`), this.entriesListWidth - 20, main_core.Text.encode(entry.name)));
	      } else {
	        rowWrap.appendChild(main_core.Tag.render(_t36 || (_t36 = _$1`
					<div class="calendar-planner-location-image-icon" title="${0}"></div>
				`), main_core.Text.encode(entry.name)));
	      }
	    } else if (entry.type === 'resource') {
	      if (!this.entriesResourceListWrap || !BX.isNodeInDom(this.entriesResourceListWrap)) {
	        this.entriesResourceListWrap = this.DOM.entrieListWrap.appendChild(main_core.Tag.render(_t37 || (_t37 = _$1`
					<div class="calendar-planner-container-resource">
						<div class="calendar-planner-resource-header">
							<span class="calendar-planner-users-item">${0}</span>
						</div>
					</div>
				`), main_core.Loc.getMessage('EC_PL_RESOURCE_TITLE')));
	      }
	      rowWrap = this.entriesResourceListWrap.appendChild(main_core.Tag.render(_t38 || (_t38 = _$1`
				<div class="calendar-planner-user" data-bx-planner-entry="${0}"></div>
			`), entry.uid));
	      if (this.showEntryName) {
	        rowWrap.appendChild(main_core.Tag.render(_t39 || (_t39 = _$1`
					<span class="calendar-planner-user-name"></span>
				`))).appendChild(main_core.Tag.render(_t40 || (_t40 = _$1`
					<span class="calendar-planner-entry-name" style="width: ${0}px;">
						${0}
					<span>
				`), this.entriesListWidth - 20, main_core.Text.encode(entry.name)));
	      } else {
	        rowWrap.appendChild(main_core.Tag.render(_t41 || (_t41 = _$1`
					<div class="calendar-planner-location-image-icon" title="${0}"></div>
				`), main_core.Text.encode(entry.name)));
	      }
	    } else {
	      rowWrap = this.DOM.entrieListWrap.appendChild(main_core.Tag.render(_t42 || (_t42 = _$1`
				<div class="calendar-planner-user"></div>
			`)));
	      rowWrap.appendChild(main_core.Tag.render(_t43 || (_t43 = _$1`
				<div class="calendar-planner-all-users">${0}</div>
			`), main_core.Text.encode(entry.name)));
	    }
	    let top = rowWrap.offsetTop + 13;
	    let dataRowWrap = this.DOM.accessibilityWrap.appendChild(main_core.Tag.render(_t44 || (_t44 = _$1`
			<div class="calendar-planner-timeline-space" style="top:${0}px" data-bx-planner-entry="${0}"></div>
		`), top, entry.uid || 0));
	    if (this.selectMode) {
	      entry.selectorControlWrap = this.selector.controlWrap.appendChild(main_core.Tag.render(_t45 || (_t45 = _$1`
				<div class="calendar-planner-selector-control-row" data-bx-planner-entry="${0}" style="top: ${0}px;"></div>
			`), entry.uid, top - 4));
	      if (entry.selected) {
	        this.selectEntryRow(entry);
	      }
	    }

	    //this.entriesRowMap.set(entry, rowWrap);
	    this.entriesDataRowMap.set(entry.uid, dataRowWrap);
	    accessibility.forEach(item => {
	      item = Planner.prepareAccessibilityItem(item);
	      if (item) {
	        this.addAccessibilityItem(item, dataRowWrap);
	      }
	    });
	  }
	  static getEntryAvatarNode(entry) {
	    let imageNode;
	    const img = entry.avatar;
	    if (!img || img === "/bitrix/images/1.gif") {
	      let defaultAvatarClass = 'ui-icon-common-user';
	      if (entry.emailUser) {
	        defaultAvatarClass = 'ui-icon-common-user-mail';
	      }
	      if (entry.sharingUser) {
	        defaultAvatarClass += ' ui-icon-common-user-sharing';
	      }
	      imageNode = main_core.Tag.render(_t46 || (_t46 = _$1`<div bx-tooltip-user-id="${0}" bx-tooltip-classname="calendar-planner-user-tooltip" title="${0}" class="ui-icon calendar-planner-user-image-icon ${0}"><i></i></div>`), entry.id, main_core.Text.encode(entry.name), defaultAvatarClass);
	    } else {
	      imageNode = main_core.Tag.render(_t47 || (_t47 = _$1`<div bx-tooltip-user-id="${0}" bx-tooltip-classname="calendar-planner-user-tooltip" title="${0}" class="ui-icon calendar-planner-user-image-icon"><i style="background-image: url('${0}')"></i></div>`), entry.id, main_core.Text.encode(entry.name), encodeURI(entry.avatar));
	    }
	    return imageNode;
	  }
	  selectEntryRow(entry) {
	    if (BX.type.isPlainObject(entry)) {
	      let top = parseInt(entry.dataRowWrap.offsetTop);
	      if (!entry.selectWrap || !BX.isParentForNode(this.selectedEntriesWrap, entry.selectWrap)) {
	        entry.selectWrap = this.selectedEntriesWrap.appendChild(main_core.Tag.render(_t48 || (_t48 = _$1`
					<div class="calendar-planner-timeline-selected"></div>
				`)));
	      }
	      entry.selectWrap.style.display = '';
	      entry.selectWrap.style.top = top + 36 + 'px';
	      entry.selectWrap.style.width = parseInt(this.DOM.mainWrap.offsetWidth) + 5 + 'px';
	      main_core.Dom.addClass(entry.selectorControlWrap, 'active');
	      entry.selected = true;
	      this.clearCacheTime();
	    }
	  }
	  isEntrySelected(entry) {
	    return entry && entry.selected;
	  }
	  deSelectEntryRow(entry) {
	    if (BX.type.isPlainObject(entry)) {
	      if (entry.selectWrap) {
	        entry.selectWrap.style.display = 'none';
	      }
	      if (entry.selectorControlWrap) {
	        main_core.Dom.removeClass(entry.selectorControlWrap, 'active');
	      }
	      entry.selected = false;
	      this.clearCacheTime();
	    }
	  }
	  static getEntryUniqueId(entry) {
	    return ['user', 'room'].includes(entry.type) ? entry.id : entry.type + '-' + entry.id;
	  }
	  getEntryByUniqueId(entryUniqueId) {
	    if (BX.type.isArray(this.entries)) {
	      return this.entries.find(function (entry) {
	        return entry.uid == entryUniqueId;
	      });
	    }
	    return null;
	  }
	  bindEventHandlers() {
	    main_core.Event.bind(this.DOM.wrap, 'click', this.handleClick.bind(this));
	    main_core.Event.bind(this.DOM.wrap, 'contextmenu', this.handleClick.bind(this));
	    main_core.Event.bind(this.DOM.wrap, 'mousedown', this.handleMousedown.bind(this));
	    main_core.Event.bind(document, 'mousemove', this.handleMousemove.bind(this));
	    main_core.Event.bind(document, 'mouseup', this.handleMouseup.bind(this));
	    main_core.Event.bind(this.DOM.timelineFixedWrap, 'onwheel' in document ? 'wheel' : 'mousewheel', this.mouseWheelTimelineHandler.bind(this));
	  }
	  handleClick(e) {
	    if (!e) {
	      e = window.event;
	    }
	    e.preventDefault();
	    const isRightClick = e.which === 3;
	    if (isRightClick || e.target.className === 'calendar-planner-today-button') {
	      return;
	    }
	    this.clickMousePos = this.getMousePos(e);
	    let nodeTarget = e.target || e.srcElement,
	      accuracyMouse = 5;
	    if (this.selectMode && main_core.Dom.hasClass(nodeTarget, 'calendar-planner-selector-control-row')) {
	      let entry = this.getEntryByUniqueId(nodeTarget.getAttribute('data-bx-planner-entry'));
	      if (entry) {
	        if (!this.isEntrySelected(entry)) {
	          this.selectEntryRow(entry);
	        } else {
	          this.deSelectEntryRow(entry);
	        }
	        this.selector.checkStatus();
	        BX.onCustomEvent('OnCalendarPlannerSelectedEntriesOnChange', [{
	          plannerId: this.id,
	          entries: this.entries
	        }]);
	      }
	      return;
	    }
	    if (!this.readonly) {
	      let timeline = this.findTarget(nodeTarget, 'timeline'),
	        selector = this.findTarget(nodeTarget, 'selector');
	      if (timeline && !selector && Math.abs(this.clickMousePos.x - this.mouseDownMousePos.x) < accuracyMouse && Math.abs(this.clickMousePos.y - this.mouseDownMousePos.y) < accuracyMouse) {
	        const left = this.clickMousePos.x - BX.pos(this.DOM.timelineFixedWrap).left + this.DOM.timelineVerticalConstraint.scrollLeft;
	        const mapDatePosRes = this.mapDatePos(this.clickSelectorScaleAccuracy);
	        let selectedDateFrom = this.getDateByPos(left, false, mapDatePosRes.posDateMap);
	        if (!selectedDateFrom) {
	          return;
	        }
	        const selectorTimeLength = this.currentToDate - this.currentFromDate;
	        let selectedDateTo = new Date(selectedDateFrom.getTime() + selectorTimeLength);
	        this.currentFromDate = selectedDateFrom;
	        this.currentToDate = selectedDateTo;
	        this.selector.transit({
	          toX: this.getPosByDate(selectedDateFrom),
	          leftDate: this.currentFromDate,
	          rightDate: this.currentToDate
	        });
	      }
	    }
	  }
	  handleMousedown(e) {
	    if (!e) {
	      e = window.event;
	    }
	    let nodeTarget = e.target || e.srcElement;
	    this.mouseDownMousePos = this.getMousePos(e);
	    this.mouseDown = true;
	    if (!this.readonly) {
	      let selector = this.findTarget(nodeTarget, 'selector');
	      this.startMousePos = this.mouseDownMousePos;
	      if (selector) {
	        if (this.findTarget(nodeTarget, 'selector-resize-right')) {
	          this.selector.startResize();
	        } else {
	          this.selector.startMove();
	        }
	      } else if (this.findTarget(nodeTarget, 'timeline')) {
	        this.startScrollTimeline();
	      }
	    }
	  }
	  handleMouseup() {
	    if (this.selector.isDragged()) {
	      this.selector.endMove();
	      this.selector.endResize();
	    }
	    if (this.timelineIsDraged) {
	      this.endScrollTimeline();
	    }
	    if (this.shown && !this.readonly && this.mouseDown) {
	      this.checkTimelineScroll();
	    }
	    this.mouseDown = false;
	    main_core.Dom.removeClass(document.body, 'calendar-planner-unselectable');
	  }
	  handleMousemove(e) {
	    let mousePos,
	      target = e.target || e.srcElement;
	    if (this.selectMode && target && target.getAttribute && target.getAttribute('data-bx-planner-entry')) {
	      this.lastTouchedEntry = target;
	    }
	    if (this.selector.isDragged()) {
	      mousePos = this.getMousePos(e);
	      this.selector.move(mousePos.x - this.startMousePos.x);
	      this.selector.resize(mousePos.x - this.startMousePos.x);
	    }
	    if (this.timelineIsDraged) {
	      mousePos = this.getMousePos(e);
	      this.scrollTimeline(mousePos.x - this.startMousePos.x);
	    }
	  }
	  mouseWheelTimelineHandler(e) {
	    e = e || window.event;
	    if (this.shown && !this.readonly) {
	      if (main_core.Browser.isMac()) {
	        this.checkTimelineScroll();
	      } else {
	        const delta = e.deltaY || e.detail || e.wheelDelta;
	        if (Math.abs(delta) > 0) {
	          this.DOM.timelineVerticalConstraint.scrollLeft = Math.max(this.DOM.timelineVerticalConstraint.scrollLeft + Math.round(delta / 3), 0);
	          this.checkTimelineScroll();
	          return BX.PreventDefault(e);
	        }
	      }
	    }
	  }
	  updateTodayButtonVisibility(animation = true) {
	    if (this.isOneDayScale()) {
	      return;
	    }
	    this.todayButton.style.transition = animation ? '' : 'none';
	    const today = new Date();
	    today.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	    let parent = this.DOM.entriesOuterWrap;
	    if (this.todayTitleButton) {
	      parent = this.todayTitleButton.parentElement;
	    }
	    const doDisplayTodayButton = today.getTime() < this.scaleDateTo.getTime() && BX.pos(parent).left + 30 < BX.pos(this.DOM.entriesOuterWrap).right;
	    if (doDisplayTodayButton && this.todayButton.style.display !== '') {
	      this.todayButton.style.display = '';
	      this.setFutureDayTitlesOffset(false);
	    }
	    if (!doDisplayTodayButton && this.todayButton.style.display !== 'none') {
	      this.todayButton.style.display = 'none';
	      this.setFutureDayTitlesOffset(false);
	    }
	    const doAddLeftArrow = BX.pos(this.todayTitleButton).right + (this.todayButtonLeftWidth - this.todayButtonWidth) < BX.pos(this.DOM.entriesOuterWrap).right;
	    if (doAddLeftArrow && this.todayButton.innerHTML === this.todayLocMessage) {
	      this.todayButton.innerHTML = this.todayLocMessage + ' &larr;';
	      this.todayButton.style.width = this.todayButtonLeftWidth + 'px';
	      this.setFutureDayTitlesOffset(animation);
	    }
	    if (!doAddLeftArrow && this.todayButton.innerHTML !== this.todayLocMessage) {
	      this.todayButton.innerHTML = this.todayLocMessage;
	      this.todayButton.style.width = this.todayButtonWidth + 'px';
	      this.setFutureDayTitlesOffset(animation);
	    }
	    const isTodayInFuture = today.getTime() > this.scaleDateTo.getTime();
	    const doDisplayTodayRightButton = isTodayInFuture || BX.pos(parent).right > BX.pos(this.DOM.timelineVerticalConstraint).right;
	    if (doDisplayTodayRightButton && this.todayRightButton.style.display !== '') {
	      this.todayRightButton.style.display = '';
	    }
	    if (!doDisplayTodayRightButton && this.todayRightButton.style.display !== 'none') {
	      this.todayRightButton.style.display = 'none';
	    }
	    if (this.todayTitleButton) {
	      if (BX.pos(this.todayTitleButton).right < BX.pos(this.DOM.timelineVerticalConstraint).right) {
	        this.todayTitleButton.style.position = 'sticky';
	      }
	      if (BX.pos(this.todayTitleButton).right > BX.pos(this.DOM.timelineVerticalConstraint).right) {
	        this.todayTitleButton.style.position = '';
	      }
	    }
	    const doAddRightArrow = BX.pos(parent).left > BX.pos(this.DOM.timelineVerticalConstraint).right || isTodayInFuture;
	    if (doAddRightArrow && this.todayRightButton.innerHTML === this.todayLocMessage) {
	      this.todayRightButton.innerHTML = this.todayLocMessage + ' &rarr;';
	      this.todayRightButton.style.width = this.todayButtonRightWidth + 'px';
	    }
	    if (!doAddRightArrow && this.todayRightButton.innerHTML !== this.todayLocMessage) {
	      this.todayRightButton.innerHTML = this.todayLocMessage;
	      this.todayRightButton.style.width = this.todayButtonWidth + 'px';
	    }
	  }
	  setFutureDayTitlesOffset(animation = true) {
	    const left = this.todayButton.style.display === 'none' ? '' : parseInt(this.todayButton.style.width) + 4 + 'px';
	    for (const title of this.futureDayTitles) {
	      title.style.transition = animation ? '' : 'none';
	      title.style.left = left;
	    }
	  }
	  todayButtonClickHandler() {
	    if (!this.isTodayButtonEnabled()) {
	      return;
	    }
	    if (this.todayButtonPivotDay) {
	      const today = new Date();
	      today.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	      new BX.easing({
	        duration: 300,
	        start: {
	          scrollLeft: this.DOM.timelineVerticalConstraint.scrollLeft
	        },
	        finish: {
	          scrollLeft: this.getPosByDate(today)
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	        step: state => {
	          this.DOM.timelineVerticalConstraint.scrollLeft = state.scrollLeft;
	        },
	        complete: () => {}
	      }).animate();
	    } else {
	      this.scaleDateFrom = new Date();
	      this.scaleDateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	      this.scaleDateTo = new Date(new Date().getTime() + calendar_util.Util.getDayLength() * this.SCALE_OFFSET_AFTER);
	      this.scaleDateTo.setHours(this.shownScaleTimeTo, 0, 0, 0);
	      this.rebuild();
	      this.DOM.timelineVerticalConstraint.scrollLeft = 0;
	      this.emit('onExpandTimeline', new main_core_events.BaseEvent({
	        data: {
	          reload: true,
	          dateFrom: this.scaleDateFrom,
	          dateTo: this.scaleDateTo
	        }
	      }));
	    }
	  }
	  isTodayButtonEnabled() {
	    return !this.readonly && !this.compactMode;
	  }
	  checkTimelineScroll() {
	    const minScroll = this.scrollStep;
	    const maxScroll = this.DOM.timelineVerticalConstraint.scrollWidth - this.DOM.timelineFixedWrap.offsetWidth - this.scrollStep;

	    // Check and expand only if it is visible
	    if (this.DOM.timelineFixedWrap.offsetWidth > 0) {
	      const today = new Date();
	      today.setHours(this.scaleDateFrom.getHours(), 0, 0, 0);
	      if (this.DOM.timelineVerticalConstraint.scrollLeft <= minScroll && this.scaleDateFrom.getTime() > today.getTime()) {
	        this.expandTimelineDirection = 'past';
	      }
	      if (this.DOM.timelineVerticalConstraint.scrollLeft >= maxScroll) {
	        this.expandTimelineDirection = 'future';
	      }
	      if (this.expandTimelineDirection) {
	        if (!this.isLoaderShown()) {
	          this.showLoader();
	        }
	        this.expandTimelineDebounce();
	      }
	    }
	  }
	  startScrollTimeline() {
	    this.timelineIsDraged = true;
	    this.timelineStartScrollLeft = this.DOM.timelineVerticalConstraint.scrollLeft;
	  }
	  scrollTimeline(x) {
	    this.DOM.timelineVerticalConstraint.scrollLeft = Math.max(this.timelineStartScrollLeft - x, 0);
	  }
	  endScrollTimeline() {
	    this.timelineIsDraged = false;
	  }
	  findTarget(node, nodeMetaType, parentCont) {
	    if (!parentCont) parentCont = this.DOM.mainWrap;
	    let type = node && node.getAttribute ? node.getAttribute('data-bx-planner-meta') : null;
	    if (type !== nodeMetaType) {
	      if (node) {
	        node = BX.findParent(node, function (n) {
	          return n.getAttribute && n.getAttribute('data-bx-planner-meta') === nodeMetaType;
	        }, parentCont);
	      } else {
	        node = null;
	      }
	    }
	    return node;
	  }
	  getMousePos(e) {
	    if (!e) e = window.event;
	    let x = 0,
	      y = 0;
	    if (e.pageX || e.pageY) {
	      x = e.pageX;
	      y = e.pageY;
	    } else if (e.clientX || e.clientY) {
	      x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
	      y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
	    }
	    return {
	      x: x,
	      y: y
	    };
	  }
	  setScaleType(scaleType) {
	    if (!this.scaleTypes.includes(scaleType)) {
	      scaleType = '1hour';
	    }
	    this.scaleType = scaleType;
	    this.scaleSize = Planner.getScaleSize(scaleType);
	    if (this.isOneDayScale() && this.timelineCellWidth < 100) {
	      this.timelineCellWidthOrig = this.timelineCellWidth;
	      this.timelineCellWidth = 100;
	    } else if (!this.isOneDayScale() && this.timelineCellWidthOrig) {
	      this.timelineCellWidth = this.timelineCellWidthOrig;
	      this.timelineCellWidthOrig = false;
	    }
	    if (this.isOneDayScale()) {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-fulldaymode');
	      if (this.DOM.entriesOuterWrap) {
	        main_core.Dom.addClass(this.DOM.entriesOuterWrap, 'calendar-planner-no-daytitle');
	      }
	    } else {
	      main_core.Dom.removeClass(this.DOM.mainWrap, 'calendar-planner-fulldaymode');
	      if (this.DOM.entriesOuterWrap) {
	        main_core.Dom.removeClass(this.DOM.entriesOuterWrap, 'calendar-planner-no-daytitle');
	      }
	    }
	  }
	  static getScaleSize(scaleType) {
	    let hour = 3600,
	      map = {
	        '15min': Math.round(hour / 4),
	        '30min': Math.round(hour / 2),
	        '1hour': hour,
	        '2hour': hour * 2,
	        '1day': hour * 24
	      };
	    return map[scaleType] || hour;
	  }
	  mapDatePos(accuracy) {
	    if (!accuracy) {
	      accuracy = this.accuracy;
	    }
	    let datePosMap = {};
	    let posDateMap = {};
	    let i, j, tsi, xi, tsj, xj, cellWidth;
	    this.substeps = Math.round(this.scaleSize / accuracy);
	    this.posAccuracy = this.timelineCellWidth / this.substeps;
	    accuracy = accuracy * 1000;
	    let scaleSize = this.scaleData[1].timestamp - this.scaleData[0].timestamp;
	    for (i = 0; i < this.scaleData.length; i++) {
	      tsi = this.scaleData[i].timestamp;
	      xi = parseInt(this.scaleData[i].cell.offsetLeft);
	      cellWidth = parseInt(this.scaleData[i].cell.offsetWidth);
	      if (!datePosMap[tsi]) {
	        datePosMap[tsi] = xi;
	      }
	      posDateMap[xi] = tsi;
	      for (j = 1; j <= cellWidth; j++) {
	        tsj = tsi + Math.round(j * scaleSize / cellWidth / accuracy) * accuracy;
	        xj = xi + j;
	        if (!datePosMap[tsi]) {
	          datePosMap[tsj] = xj;
	        }
	        posDateMap[xj] = tsj;
	        if (j === cellWidth && (!this.scaleData[i + 1] || this.scaleData[i + 1].dayStart)) {
	          datePosMap[xj + '_end'] = tsj;
	        }
	      }
	      if (i + 1 < this.scaleData.length && this.scaleData[i + 1].dayStart) {
	        const borderStart = xi + cellWidth;
	        const borderEnd = parseInt(this.scaleData[i + 1].cell.offsetLeft);
	        const borderTimestamp = tsi + scaleSize;
	        for (let borderX = borderStart; borderX < borderEnd; borderX++) {
	          posDateMap[borderX] = borderTimestamp;
	        }
	      }
	    }
	    return {
	      datePosMap: datePosMap,
	      posDateMap: posDateMap
	    };
	  }
	  getPosByDate(date) {
	    let x = 0;
	    if (date && typeof date !== 'object') {
	      date = calendar_util.Util.parseDate(date);
	    }
	    if (date && typeof date === 'object') {
	      let curInd = 0;
	      const timestamp = date.getTime();
	      for (let i = 0; i < this.scaleData.length; i++) {
	        if (timestamp >= this.scaleData[i].timestamp) {
	          curInd = i;
	        } else {
	          break;
	        }
	      }
	      if (this.scaleData[curInd] && this.scaleData[curInd].cell) {
	        x = this.scaleData[curInd].cell.offsetLeft;
	        const cellWidth = this.scaleData[curInd].cell.offsetWidth;
	        const deltaTs = Math.round((timestamp - this.scaleData[curInd].timestamp) / 1000);
	        if (deltaTs > 0) {
	          x += Math.round(deltaTs * 10 / this.scaleSize * cellWidth) / 10;
	        }
	      }
	    }
	    return x;
	  }
	  getDateByPos(x, end, posDateMap) {
	    if (!posDateMap) {
	      posDateMap = this.posDateMap;
	    }
	    let date,
	      timestamp = end && posDateMap[x + '_end'] ? posDateMap[x + '_end'] : posDateMap[x];
	    if (!timestamp) {
	      x = Math.round(x);
	      timestamp = end && posDateMap[x + '_end'] ? posDateMap[x + '_end'] : posDateMap[x];
	    }
	    if (timestamp) {
	      date = new Date(timestamp);
	    }
	    return date;
	  }
	  showMoreUsers() {
	    this.MIN_ENTRY_ROWS = this.MAX_ENTRY_ROWS;
	    this.update(this.entries, this.accessibility);
	    this.rebuildDebounce();
	  }
	  adjustHeight() {
	    let newHeight = this.DOM.entrieListWrap.offsetHeight + this.DOM.entrieListWrap.offsetTop + 30,
	      currentHeight = parseInt(this.DOM.wrap.style.height) || this.height;
	    if (this.compactMode && currentHeight < newHeight || !this.compactMode) {
	      this.DOM.wrap.style.height = currentHeight + 'px';
	      this.resizePlannerHeight(newHeight, Math.abs(newHeight - currentHeight) > 10);
	    }
	  }
	  resizePlannerHeight(height, animation = false) {
	    this.height = height;
	    if (animation) {
	      // Stop animation before starting another one
	      if (this.resizeAnimation) {
	        this.resizeAnimation.stop();
	        this.resizeAnimation = null;
	      }
	      this.resizeAnimation = new BX.easing({
	        duration: 800,
	        start: {
	          height: parseInt(this.DOM.wrap.style.height)
	        },
	        finish: {
	          height: height
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: state => {
	          this.resizePlannerHeight(state.height, false);
	        },
	        complete: () => {
	          this.resizeAnimation = null;
	        }
	      });
	      this.resizeAnimation.animate();
	    } else {
	      this.DOM.wrap.style.height = height + 'px';
	      this.DOM.mainWrap.style.height = height + 'px';
	      this.DOM.timelineFixedWrap.style.height = height + 'px';
	      let timelineDataContHeight = this.DOM.entrieListWrap.offsetHeight + 3;
	      this.DOM.timelineDataWrap.style.height = timelineDataContHeight + 'px';
	      // Todo: resize selector
	      //this.selector.wrap.style.height = (timelineDataContHeight + 10) + 'px';
	      this.DOM.entriesOuterWrap.style.height = height + 'px';
	      if (this.DOM.proposeTimeButton && this.DOM.proposeTimeButton.style.display !== "none") {
	        this.DOM.proposeTimeButton.style.top = this.DOM.timelineDataWrap.offsetTop + timelineDataContHeight / 2 - 16 + "px";
	      }
	    }
	  }
	  resizePlannerWidth(width, animation) {
	    if (!animation && this.DOM.wrap && this.DOM.mainWrap) {
	      this.DOM.wrap.style.width = width + 'px';
	      let entriesListWidth = this.compactMode ? 0 : this.entriesListWidth;
	      this.DOM.mainWrap.style.width = width + 'px';
	      this.DOM.entriesOuterWrap.style.width = entriesListWidth + 'px';
	    }
	  }
	  expandTimeline(scaleDateFrom, scaleDateTo) {
	    let loadedTimelineSize;
	    let scrollLeft;
	    const prevScaleDateFrom = this.scaleDateFrom;
	    const prevScaleDateTo = this.scaleDateTo;
	    if (!scaleDateFrom) scaleDateFrom = this.scaleDateFrom;
	    if (!scaleDateTo) scaleDateTo = this.scaleDateTo;
	    if (this.expandTimelineDirection === 'past') {
	      scrollLeft = this.DOM.timelineVerticalConstraint.scrollLeft;
	      this.scaleDateFrom = new Date(scaleDateFrom.getTime() - calendar_util.Util.getDayLength() * this.EXPAND_OFFSET);
	      const today = new Date();
	      today.setHours(this.scaleDateFrom.getHours(), 0, 0, 0);
	      if (this.scaleDateFrom.getTime() < today) {
	        this.scaleDateFrom = today;
	      }
	      loadedTimelineSize = (this.scaleDateTo.getTime() - this.scaleDateFrom.getTime()) / calendar_util.Util.getDayLength();
	      if (loadedTimelineSize > this.maxTimelineSize) {
	        this.scaleDateTo = new Date(this.scaleDateFrom.getTime() + calendar_util.Util.getDayLength() * this.maxTimelineSize);
	        this.loadedDataFrom = this.scaleDateFrom;
	        this.loadedDataTo = this.scaleDateTo;
	        this.limitScaleSizeMode = true;
	      }
	    } else if (this.expandTimelineDirection === 'future') {
	      let oldDateTo = this.scaleDateTo;
	      scrollLeft = this.DOM.timelineVerticalConstraint.scrollLeft;
	      this.scaleDateTo = new Date(scaleDateTo.getTime() + calendar_util.Util.getDayLength() * this.EXPAND_OFFSET);
	      loadedTimelineSize = (this.scaleDateTo.getTime() - this.scaleDateFrom.getTime()) / calendar_util.Util.getDayLength();
	      if (loadedTimelineSize > this.maxTimelineSize) {
	        this.scaleDateFrom = new Date(this.scaleDateTo.getTime() - calendar_util.Util.getDayLength() * this.maxTimelineSize);
	        this.loadedDataFrom = this.scaleDateFrom;
	        this.loadedDataTo = this.scaleDateTo;
	        scrollLeft = this.getPosByDate(oldDateTo) - this.DOM.timelineFixedWrap.offsetWidth;
	        setTimeout(() => {
	          this.DOM.timelineVerticalConstraint.scrollLeft = this.getPosByDate(oldDateTo) - this.DOM.timelineFixedWrap.offsetWidth;
	        }, 10);
	        this.limitScaleSizeMode = true;
	      }
	    } else {
	      this.scaleDateFrom = new Date(scaleDateFrom.getTime() - calendar_util.Util.getDayLength() * this.SCALE_OFFSET_BEFORE);
	      this.scaleDateTo = new Date(scaleDateTo.getTime() + calendar_util.Util.getDayLength() * this.SCALE_OFFSET_AFTER);
	    }
	    const reloadData = this.scaleDateFrom.getTime() < prevScaleDateFrom.getTime() || this.scaleDateTo.getTime() > prevScaleDateTo.getTime();
	    this.hideLoader();
	    this.emit('onExpandTimeline', new main_core_events.BaseEvent({
	      data: {
	        reload: reloadData,
	        dateFrom: this.scaleDateFrom,
	        dateTo: this.scaleDateTo
	      }
	    }));
	    const currentPlannerWidth = this.DOM.timelineInnerWrap.offsetWidth;
	    this.rebuild({
	      updateSelector: true
	    });
	    if (this.expandTimelineDirection === 'past') {
	      const widthDiff = this.DOM.timelineInnerWrap.offsetWidth - currentPlannerWidth;
	      this.DOM.timelineVerticalConstraint.scrollLeft = scrollLeft + widthDiff;
	    } else if (scrollLeft !== undefined) {
	      this.DOM.timelineVerticalConstraint.scrollLeft = scrollLeft;
	    }
	    this.expandTimelineDirection = null;
	  }
	  getVisibleEvents() {
	    const visibleEvents = [];
	    const timelineFromPosition = this.DOM.timelineVerticalConstraint.scrollLeft;
	    const timelineToPosition = timelineFromPosition + this.DOM.timelineFixedWrap.offsetWidth;
	    for (const index in this.accessibility) {
	      for (const event of this.accessibility[index]) {
	        const eventFromPosition = this.getPosByDate(new Date(event.fromTimestamp));
	        const eventToPosition = this.getPosByDate(new Date(event.toTimestamp));
	        if (this.doSegmentsIntersect(eventFromPosition, eventToPosition, timelineFromPosition, timelineToPosition) && event.node) {
	          visibleEvents.push(event);
	        }
	      }
	    }
	    return visibleEvents;
	  }
	  getEventsAfter(events, timestamp) {
	    const eventsAfter = [];
	    for (const event of events) {
	      if (event.fromTimestamp >= timestamp) {
	        eventsAfter.push(event);
	      }
	    }
	    return eventsAfter;
	  }
	  update(entries = [], accessibility = {}) {
	    main_core.Dom.clean(this.DOM.entrieListWrap);
	    main_core.Dom.clean(this.DOM.accessibilityWrap);
	    this.entriesDataRowMap = new Map();
	    if (!main_core.Type.isArray(entries)) {
	      return;
	    }
	    this.entries = entries;
	    this.accessibility = accessibility;
	    const userId = parseInt(this.userId);

	    // sort entries list by amount of accessibility data
	    // Entries without accessibility data should be in the end of the array
	    // But first in the list will be meeting room
	    // And second (or first) will be owner-host of the event
	    entries.sort((a, b) => {
	      if (b.status === 'h' || parseInt(b.id) === userId && a.status !== 'h') {
	        return 1;
	      }
	      if (a.status === 'h' || parseInt(a.id) === userId && b.status !== 'h') {
	        return -1;
	      }
	      if (parseInt(a.id) < parseInt(b.id)) {
	        return -1;
	      }
	      return 1;
	    });
	    if (this.selectedEntriesWrap) {
	      main_core.Dom.clean(this.selectedEntriesWrap);
	      if (this.selector && this.selector.controlWrap) {
	        main_core.Dom.clean(this.selector.controlWrap);
	      }
	    }
	    const cutData = [];
	    const cutDataTitle = [];
	    let usersCount = 0;
	    let cutAmount = 0;
	    let dispDataCount = 0;
	    entries.forEach((entry, ind) => {
	      entry.uid = Planner.getEntryUniqueId(entry);
	      let accData = main_core.Type.isArray(accessibility[entry.uid]) ? accessibility[entry.uid] : [];
	      this.entriesIndex.set(entry.uid, entry);
	      if (entry.type === 'user') {
	        usersCount++;
	      }
	      if (ind < this.MIN_ENTRY_ROWS || entries.length === this.MIN_ENTRY_ROWS + 1) {
	        dispDataCount++;
	        this.displayEntryRow(entry, accData);
	      } else {
	        cutAmount++;
	        cutDataTitle.push(entry.name);
	        accData.forEach(item => {
	          item = Planner.prepareAccessibilityItem(item);
	          if (item) {
	            cutData.push(item);
	          }
	        });
	      }
	    });

	    // Update entries title count
	    if (this.entriesListTitleCounter) {
	      this.entriesListTitleCounter.innerHTML = usersCount > this.MAX_ENTRY_ROWS ? '(' + usersCount + ')' : '';
	    }
	    this.emit('onDisplayAttendees', new main_core_events.BaseEvent({
	      data: {
	        usersCount: usersCount
	      }
	    }));
	    if (cutAmount > 0) {
	      if (dispDataCount === this.MAX_ENTRY_ROWS) {
	        this.displayEntryRow({
	          name: main_core.Loc.getMessage('EC_PL_ATTENDEES_LAST') + ' (' + cutAmount + ')',
	          type: 'lastUsers',
	          title: cutDataTitle.join(', ')
	        }, cutData);
	      } else {
	        this.displayEntryRow({
	          name: main_core.Loc.getMessage('EC_PL_ATTENDEES_SHOW_MORE') + ' (' + cutAmount + ')',
	          type: 'moreLink'
	        }, cutData);
	      }
	    }
	    this.clearCacheTime();
	    const status = this.checkTimePeriod(this.currentFromDate, this.currentToDate) === true;
	    this.updateSelectorFromStatus(status);
	    calendar_util.Util.extendPlannerWatches({
	      entries: entries,
	      userId: this.userId
	    });
	    this.adjustHeight();
	  }
	  updateAccessibility(accessibility) {
	    this.accessibility = accessibility;
	    if (main_core.Type.isPlainObject(accessibility)) {
	      let key;
	      for (key in accessibility) {
	        if (accessibility.hasOwnProperty(key) && main_core.Type.isArray(accessibility[key]) && accessibility[key].length) {
	          let wrap = this.entriesDataRowMap.get(key);
	          if (main_core.Type.isDomNode(wrap)) {
	            accessibility[key].forEach(event => {
	              event = Planner.prepareAccessibilityItem(event);
	              if (event) {
	                this.addAccessibilityItem(event, wrap);
	              }
	            });
	          }
	        }
	      }
	    }
	  }
	  updateSelector(from, to, fullDay, options = {}) {
	    if (this.shown && this.selector) {
	      this.setFullDayMode(fullDay);

	      // Update limits of scale
	      if (!this.isOneDayScale()) {
	        if (calendar_util.Util.formatDate(from) !== calendar_util.Util.formatDate(to)) {
	          this.extendScaleTime(0, 24);
	        } else {
	          let timeFrom = parseInt(from.getHours()) + Math.floor(from.getMinutes() / 60);
	          let timeTo = parseInt(to.getHours()) + Math.ceil(to.getMinutes() / 60);
	          let scale = 2;
	          if (timeFrom <= this.shownScaleTimeFrom) {
	            this.extendScaleTime(timeFrom - scale, false);
	          }
	          if (timeTo >= this.shownScaleTimeTo) {
	            this.extendScaleTime(false, timeTo + scale);
	          }
	        }
	      }
	      if (to.getTime() > this.scaleDateTo.getTime() || from.getTime() < this.scaleDateFrom.getTime()) {
	        this.expandTimelineDirection = false;
	        this.expandTimeline(from, to);
	      }
	      this.currentFromDate = from;
	      this.currentToDate = to;
	      if (!this.selector) {
	        return;
	      }
	      if (from.getTime() < this.scaleDateFrom.getTime()) {
	        this.selector.update({
	          from: from,
	          to: to,
	          fullDay: fullDay,
	          focus: options.focus !== false
	        });
	        return;
	      }
	      this.selector.update({
	        from: from,
	        to: to,
	        fullDay: fullDay
	      });
	      if (options.focus !== false) {
	        this.selector.focus(true, 300);
	      }
	    }
	  }
	  handleSelectorChanges(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      let data = event.getData();
	      this.emit('onDateChange', new main_core_events.BaseEvent({
	        data: data
	      }));
	      this.currentFromDate = data.dateFrom;
	      this.currentToDate = data.dateTo;
	      if (this.currentToDate.getHours() < this.shownScaleTimeFrom && !(this.currentToDate.getHours() === 0 && this.currentToDate.getMinutes() === 0)) {
	        this.extendScaleTime(this.currentToDate.getHours(), false);
	      }
	    }
	  }
	  getAllEvents() {
	    const events = [];
	    for (const entryId in this.accessibility) {
	      if (!this.accessibility.hasOwnProperty(entryId) || !main_core.Type.isArray(this.accessibility[entryId])) {
	        continue;
	      }
	      for (const event of this.accessibility[entryId]) {
	        events.push(event);
	      }
	    }
	    return events;
	  }
	  doCheckSelectorStatus(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      const data = event.getData();
	      this.clearCacheTime();
	      const selectorStatus = this.checkTimePeriod(data.dateFrom, data.dateTo) === true;
	      this.updateSelectorFromStatus(selectorStatus);
	    }
	  }
	  updateSelectorFromStatus(status) {
	    this.selector.setSelectorStatus(status);
	    if (this.selector.isDragged()) {
	      this.hideProposeControl();
	    }
	    if (status) {
	      main_core.Dom.removeClass(this.DOM.mainWrap, 'calendar-planner-selector-warning');
	      this.hideProposeControl();
	    } else {
	      main_core.Dom.addClass(this.DOM.mainWrap, 'calendar-planner-selector-warning');
	      if (!this.selector.isDragged()) {
	        this.showProposeControl();
	      }
	    }
	  }
	  proposeTime(params = {}) {
	    if (!main_core.Type.isPlainObject(params)) {
	      params = {};
	    }
	    let curTimestamp = Math.round(this.selector.getDateFrom().getTime() / (this.accuracy * 1000)) * this.accuracy * 1000,
	      curDate = new Date(curTimestamp),
	      duration = this.selector.getDuration(),
	      data = [],
	      k,
	      i;
	    curDate.setSeconds(0, 0);
	    curTimestamp = curDate.getTime();
	    for (k in this.accessibility) {
	      if (this.accessibility.hasOwnProperty(k) && this.accessibility[k] && this.accessibility[k].length > 0) {
	        for (i = 0; i < this.accessibility[k].length; i++) {
	          if (this.accessibility[k][i].toTimestampReal >= curTimestamp) {
	            let item = Planner.prepareAccessibilityItem(this.accessibility[k][i]);
	            if (item) {
	              data.push(item);
	            }
	          }
	        }
	      }
	    }
	    data.sort(function (a, b) {
	      return a.fromTimestamp - b.fromTimestamp;
	    });
	    let ts = curTimestamp;
	    while (true) {
	      let dateFrom = new Date(ts);
	      let dateTo = new Date(ts + duration);
	      if (!this.isOneDayScale()) {
	        let timeFrom = parseInt(dateFrom.getHours() + dateFrom.getMinutes() / 60);
	        let timeTo = parseInt(dateTo.getHours() + dateTo.getMinutes() / 60);
	        if (timeTo === 0) {
	          timeTo = 24;
	        }
	        if (timeFrom <= this.shownScaleTimeFrom) {
	          dateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	          ts = dateFrom.getTime();
	          dateTo = new Date(ts + duration);
	        }
	        if (timeTo > this.shownScaleTimeTo) {
	          dateFrom = new Date(ts + calendar_util.Util.getDayLength() - 1000); // next day
	          dateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	          ts = dateFrom.getTime();
	          dateTo = new Date(ts + duration);
	        }
	      }
	      if (this.fullDayMode) {
	        dateFrom.setHours(0, 0, 0, 0);
	        dateTo.setHours(0, 0, 0, 0);
	      }
	      const checkRes = this.checkTimePeriod(dateFrom, dateTo, data);
	      if (checkRes === true) {
	        if (dateTo.getTime() > this.scaleDateTo.getTime()) {
	          if (dateTo.getTime() - this.scaleDateTo.getTime() > this.proposeTimeLimit * calendar_util.Util.getDayLength() || params.checkedFuture === true) {
	            Planner.showNoResultNotification();
	          } else if (params.checkedFuture !== true) {
	            this.scaleDateTo = new Date(this.scaleDateTo.getTime() + calendar_util.Util.getDayLength() * this.proposeTimeLimit);
	            this.expandTimeline(this.scaleDateFrom, this.scaleDateTo);
	          }
	        } else {
	          if (this.fullDayMode) dateTo = new Date(dateTo.getTime() - calendar_util.Util.getDayLength());
	          this.selector.update({
	            from: dateFrom,
	            to: dateTo,
	            updateScaleType: false,
	            updateScaleLimits: true,
	            animation: true,
	            focus: true
	          });
	          this.emit('onDateChange', new main_core_events.BaseEvent({
	            data: {
	              dateFrom: dateFrom,
	              dateTo: dateTo,
	              fullDay: this.fullDayMode
	            }
	          }));
	        }
	        break;
	      } else if (checkRes && checkRes.toTimestampReal) {
	        ts = checkRes.toTimestampReal;
	        if (this.fullDayMode) {
	          let dt = new Date(ts + calendar_util.Util.getDayLength() - 1000); // next day
	          dt.setHours(0, 0, 0, 0);
	          ts = dt.getTime();
	        }
	      }
	    }
	  }
	  checkTimePeriod(fromDate, toDate, data) {
	    if (!this.currentFromDate) {
	      return true;
	    }
	    const timelineFrom = new Date();
	    timelineFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
	    if (this.fullDayMode) {
	      timelineFrom.setHours(0, 0, 0, 0);
	    }
	    if (fromDate && fromDate.getTime() < timelineFrom.getTime()) {
	      return true;
	    }
	    let result = true;
	    let entry;
	    if (!main_core.Type.isDate(fromDate) || !main_core.Type.isDate(toDate)) {
	      return result;
	    }
	    let fromTimestamp = fromDate.getTime();
	    let toTimestamp = toDate.getTime();
	    const cacheKey = fromTimestamp + '_' + toTimestamp;
	    const accuracy = 3 * 60 * 1000; // 3min

	    if (main_core.Type.isArray(data)) {
	      for (let i = 0; i < data.length; i++) {
	        let item = data[i];
	        if (item.type && item.type === 'hr') {
	          continue;
	        }
	        if (item.fromTimestamp + accuracy <= toTimestamp && (item.toTimestampReal || item.toTimestamp) - accuracy >= fromTimestamp) {
	          result = item;
	          break;
	        }
	      }
	    } else if (main_core.Type.isArray(this.entries)) {
	      let selectorAccuracy = this.selectorAccuracy * 1000,
	        entryId;
	      if (this.checkTimeCache[cacheKey] !== undefined) {
	        result = this.checkTimeCache[cacheKey];
	      } else {
	        for (entryId in this.accessibility) {
	          if (this.accessibility.hasOwnProperty(entryId)) {
	            entry = this.entries.find(function (el) {
	              return el.id === entryId.toString();
	            });
	            if (!entry || this.selectMode && !entry.selected) {
	              continue;
	            }
	            if (main_core.Type.isArray(this.accessibility[entryId])) {
	              for (let i = 0; i < this.accessibility[entryId].length; i++) {
	                let item = this.accessibility[entryId][i];
	                if (item.type && item.type === 'hr') {
	                  continue;
	                }
	                if (item.fromTimestamp + selectorAccuracy <= toTimestamp && (item.toTimestampReal || item.toTimestamp) - selectorAccuracy >= fromTimestamp) {
	                  result = item;
	                  break;
	                }
	              }
	            }
	          }
	        }
	        this.checkTimeCache[cacheKey] = result;
	      }
	    }
	    return result;
	  }
	  clearCacheTime() {
	    this.checkTimeCache = {};
	  }
	  checkEntryTimePeriod(entry, fromDate, toDate) {
	    let data = [],
	      i;
	    if (entry && entry.id && BX.type.isArray(this.accessibility[entry.id])) {
	      for (i = 0; i < this.accessibility[entry.id].length; i++) {
	        let item = Planner.prepareAccessibilityItem(this.accessibility[entry.id][i]);
	        if (item) {
	          data.push(item);
	        }
	      }
	    }
	    return this.checkTimePeriod(fromDate, toDate, data) === true;
	  }
	  showSettingsPopup() {
	    let settingsDialogCont = main_core.Tag.render(_t49 || (_t49 = _$1`<div class="calendar-planner-settings-popup"></div>`));
	    let scaleRow = settingsDialogCont.appendChild(main_core.Tag.render(_t50 || (_t50 = _$1`
			<div class="calendar-planner-settings-row">
				<i>${0}:</i>
			</div>
		`), main_core.Loc.getMessage('EC_PL_SETTINGS_SCALE')));
	    let scaleWrap = scaleRow.appendChild(main_core.Tag.render(_t51 || (_t51 = _$1`
			<span class="calendar-planner-option-container"></span>
		`)));
	    if (this.fullDayMode) {
	      scaleRow.title = main_core.Loc.getMessage('EC_PL_SETTINGS_SCALE_READONLY_TITLE');
	      main_core.Dom.addClass(scaleRow, 'calendar-planner-option-container-disabled');
	    }
	    this.scaleTypes.forEach(scale => {
	      scaleWrap.appendChild(main_core.Tag.render(_t52 || (_t52 = _$1`<span class="calendar-planner-option-tab ${0}" data-bx-planner-scale="${0}">${0}</span>`), scale === this.scaleType ? ' calendar-planner-option-tab-active' : '', scale, main_core.Loc.getMessage('EC_PL_SETTINGS_SCALE_' + scale.toUpperCase())));
	    });

	    // Create and show settings popup
	    let popup = main_popup.PopupWindowManager.create(this.id + "-settings-popup", this.DOM.settingsButton, {
	      autoHide: true,
	      closeByEsc: true,
	      offsetTop: -1,
	      offsetLeft: 7,
	      lightShadow: true,
	      content: settingsDialogCont,
	      zIndex: 4000,
	      angle: {
	        postion: 'top'
	      },
	      cacheable: false
	    });
	    popup.show(true);
	    main_core.Event.bind(scaleWrap, 'click', e => {
	      if (!this.fullDayMode) {
	        let nodeTarget = e.target || e.srcElement,
	          scale = nodeTarget && nodeTarget.getAttribute && nodeTarget.getAttribute('data-bx-planner-scale');
	        if (scale) {
	          this.changeScaleType(scale);
	          popup.close();
	        }
	      }
	    });
	  }
	  changeScaleType(scaleType) {
	    if (scaleType !== this.scaleType) {
	      this.setScaleType(scaleType);
	      this.rebuild();
	    }
	  }
	  setFullDayMode(fullDayMode) {
	    if (fullDayMode !== this.fullDayMode) {
	      this.fullDayMode = fullDayMode;
	      if (fullDayMode && !this.isOneDayScale()) {
	        this.savedScaleType = this.scaleType;
	        this.changeScaleType('1day');
	      } else if (!fullDayMode && this.isOneDayScale() && this.savedScaleType) {
	        this.changeScaleType(this.savedScaleType);
	        this.savedScaleType = null;
	      }
	    }
	  }
	  static showNoResultNotification() {
	    alert(main_core.Loc.getMessage('EC_PL_PROPOSE_NO_RESULT'));
	  }
	  showProposeControl() {
	    if (!this.DOM.proposeTimeButton) {
	      this.DOM.proposeTimeButton = this.DOM.mainWrap.appendChild(main_core.Tag.render(_t53 || (_t53 = _$1`
				<div class="calendar-planner-time-arrow-right">
					<span class="calendar-planner-time-arrow-right-text">
						${0}
					</span>
					<span class="calendar-planner-time-arrow-right-item"></span>
				</div>
			`), main_core.Loc.getMessage('EC_PL_PROPOSE')));
	      main_core.Event.bind(this.DOM.proposeTimeButton, 'click', this.proposeTime.bind(this));
	      if (this.isLocked()) {
	        main_core.Dom.addClass(this.DOM.proposeTimeButton, '--lock');
	      }
	    }
	    this.DOM.proposeTimeButton.style.display = "block";
	    this.DOM.proposeTimeButton.style.top = this.DOM.timelineDataWrap.offsetTop + this.DOM.timelineDataWrap.offsetHeight / 2 - 16 + "px";
	  }
	  hideProposeControl() {
	    if (this.DOM.proposeTimeButton) {
	      this.DOM.proposeTimeButton.style.display = "none";
	    }
	  }
	  mouseMoveHandler(e) {
	    let i,
	      nodes,
	      entryUid,
	      parentTarget,
	      prevEntry,
	      mainContWrap = this.DOM.mainWrap,
	      target = e.target || e.srcElement;
	    entryUid = target.getAttribute('data-bx-planner-entry');
	    if (!entryUid) {
	      parentTarget = BX.findParent(target, function (node) {
	        if (node == mainContWrap || node.getAttribute && node.getAttribute('data-bx-planner-entry')) {
	          return true;
	        }
	      }, mainContWrap);
	      if (parentTarget) {
	        entryUid = target.getAttribute('data-bx-planner-entry');
	      } else {
	        main_core.Dom.removeClass(this.hoverRow, 'show');
	        nodes = this.selector.controlWrap.querySelectorAll('.calendar-planner-selector-control-row.hover');
	        for (i = 0; i < nodes.length; i++) {
	          main_core.Dom.removeClass(nodes[i], 'hover');
	        }
	        prevEntry = this.getEntryByUniqueId(this.howerEntryId);
	        if (prevEntry && prevEntry.selectWrap) {
	          prevEntry.selectWrap.style.opacity = 1;
	        }
	      }
	    }
	    if (entryUid) {
	      if (this.howerEntryId !== entryUid) {
	        this.howerEntryId = entryUid;
	        let entry = this.getEntryByUniqueId(entryUid);
	        if (entry) {
	          let top = parseInt(entry.dataRowWrap.offsetTop);
	          main_core.Dom.addClass(this.hoverRow, 'show');
	          this.hoverRow.style.top = top + 36 + 'px';
	          this.hoverRow.style.width = parseInt(this.DOM.mainWrap.offsetWidth) + 5 + 'px';
	          if (entry.selectorControlWrap) {
	            nodes = this.selector.controlWrap.querySelectorAll('.calendar-planner-selector-control-row.hover');
	            for (i = 0; i < nodes.length; i++) {
	              main_core.Dom.removeClass(nodes[i], 'hover');
	            }
	            main_core.Dom.addClass(entry.selectorControlWrap, 'hover');
	          }
	        }
	      }
	    }
	  }
	  showLoader() {
	    this.hideLoader();
	    this.DOM.loader = this.DOM.mainWrap.appendChild(calendar_util.Util.getLoader(50));
	    main_core.Dom.addClass(this.DOM.loader, 'calendar-planner-main-loader');
	    this.loaderShown = true;
	  }
	  hideLoader() {
	    if (main_core.Type.isDomNode(this.DOM.loader)) {
	      main_core.Dom.remove(this.DOM.loader);
	    }
	    this.loaderShown = false;
	  }
	  isLoaderShown() {
	    return this.loaderShown;
	  }
	  isShown() {
	    return this.shown;
	  }
	  isBuilt() {
	    return this.built;
	  }
	  isLocked() {
	    return this.locked;
	  }
	  lock() {
	    if (!this.DOM.lockScreen) {
	      this.DOM.lockScreen = main_core.Tag.render(_t54 || (_t54 = _$1`
				<div class="calendar-planner-timeline-locker">
					<div class="calendar-planner-timeline-locker-container">
						<div class="calendar-planner-timeline-locker-top">
							<div class="calendar-planner-timeline-locker-icon"></div>
							<div class="calendar-planner-timeline-text">${0}</div>
						</div>
						<div class="calendar-planner-timeline-locker-button">
							<a href="javascript:void(0)" onclick="top.BX.UI.InfoHelper.show('limit_crm_calender_planner');" class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-round">${0}</a>
						</div>
					</div>
				</div>
			`), main_core.Loc.getMessage('EC_PL_LOCKED_TITLE'), main_core.Loc.getMessage('EC_PL_UNLOCK_FEATURE'));
	    }
	    main_core.Dom.addClass(this.DOM.timelineFixedWrap, '--lock');
	    this.DOM.timelineFixedWrap.appendChild(this.DOM.lockScreen);
	  }
	  doSegmentsIntersect(x1, x2, y1, y2) {
	    return x1 >= y1 && x1 <= y2 || x2 >= y1 && x2 <= y2 || x1 <= y1 && x2 >= y2;
	  }
	}

	exports.Planner = Planner;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Event,BX.Calendar,BX,BX.Calendar.Ui.Tools,BX.Main));
//# sourceMappingURL=planner.bundle.js.map
