this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Ui = this.BX.Calendar.Ui || {};
(function (exports,calendar_util) {
	'use strict';

	/**
	 * @interface
	 */

	/**
	 * @implements DragAndDropInterface
	 */
	class EventDragAndDrop {
	  // stickDistanceInMinutes = 30;

	  constructor(getDateByPos, getPosByDate, getEvents) {
	    this.maxTryDuration = 50;
	    this.stickDistanceInPercents = 34;
	    this.magnetizeDistanceInMinutes = 15;
	    this.getDateByPos = getDateByPos;
	    this.getPosByDate = getPosByDate;
	    this.getEvents = getEvents;
	  }
	  setFinalTimeInterval(from, to) {
	    this.finalFrom = new Date(from.getTime());
	    this.finalTo = new Date(to.getTime());
	  }
	  getFinalFrom() {
	    return this.finalFrom;
	  }
	  getFinalTo() {
	    return this.finalTo;
	  }
	  onDragStart(duration, startPosition = 0) {
	    this.savedDuration = duration;
	    this.tryDuration = 0;
	    this.startBoundary = this.getBoundaryFromPositionAndDuration(startPosition, duration);
	    this.setFinalTimeInterval(this.startBoundary.from, this.startBoundary.to);
	  }
	  getDragBoundary(position) {
	    const diff = this.previousPosition ? this.previousPosition - position : 0;
	    this.previousPosition = position;
	    this.calculateTryDuration(diff);
	    this.resetMagnetStamps(diff);
	    let boundary = this.getBoundaryFromPositionAndDuration(position, this.savedDuration);
	    if (this.doMagnetize()) {
	      boundary = this.getMagnetizedBoundary(boundary, diff);
	    }
	    boundary.size = this.getPosByDate(boundary.to) - this.getPosByDate(boundary.from);
	    this.finalFrom = boundary.from;
	    this.finalTo = boundary.to;
	    return boundary;
	  }
	  getBoundaryFromPositionAndDuration(position, duration) {
	    const from = this.getDateByPos(position);
	    const to = from ? new Date(from.getTime() + duration) : null;
	    return {
	      from,
	      to,
	      position,
	      wasMagnetized: false
	    };
	  }
	  getMagnetizedBoundary(boundary, diff) {
	    let position = boundary.position;
	    let duration = this.savedDuration;
	    const magnetizedTimeInterval = this.getMagnetizedTimeInterval(boundary.from, boundary.to);
	    const magnetizedFromPosition = this.getPosByDate(magnetizedTimeInterval.from);
	    const wasMagnetized = magnetizedTimeInterval.wasMagnetizedTo || magnetizedTimeInterval.wasMagnetizedFrom;
	    if (wasMagnetized) {
	      position = magnetizedFromPosition;
	      duration = magnetizedTimeInterval.to.getTime() - magnetizedTimeInterval.from.getTime();
	    }

	    // stabilize magnets
	    if (!this.endMagnetStamp && boundary.to.getTime() < magnetizedTimeInterval.to.getTime() && diff < 0) {
	      this.endMagnetStamp = position;
	    }
	    if (this.endMagnetStamp) {
	      if (this.endMagnetStamp + 5 < position) {
	        this.endMagnetStamp = null;
	      } else {
	        position = this.endMagnetStamp;
	      }
	    }
	    if (!this.startMagnetStamp && boundary.from.getTime() > magnetizedTimeInterval.from.getTime() && diff > 0) {
	      this.startMagnetStamp = position;
	    }
	    if (this.startMagnetStamp) {
	      if (magnetizedFromPosition < this.startMagnetStamp) {
	        this.startMagnetStamp = null;
	      } else {
	        position = this.startMagnetStamp;
	      }
	    }
	    const from = this.getDateByPos(position);
	    const to = new Date(from.getTime() + duration);
	    return {
	      from,
	      to,
	      position,
	      wasMagnetized
	    };
	  }

	  /**
	   * stick - behavior when dragged event intersects with another event
	   * and we push dragged event to top or to bottom of another event
	   *
	   * magnetize - behavior when dragged event is close to another event
	   * and we move it closer to another event
	   */
	  getMagnetizedTimeInterval(fromDate, toDate) {
	    let wasMagnetizedFrom = false;
	    let wasMagnetizedTo = false;
	    let from = new Date(fromDate.getTime());
	    let to = new Date(toDate.getTime());
	    const duration = to.getTime() - from.getTime();
	    const magnetDistance = this.magnetizeDistanceInMinutes * 60 * 1000;
	    const stickDistance = this.stickDistanceInPercents / 100 * this.savedDuration;

	    // find lowest fromDate and highest toDate
	    let fromDateMax = new Date(from.getTime());
	    fromDateMax.setHours(0, 0, 0, 0);
	    let toDateMin = new Date(to.getTime());
	    toDateMin.setHours(23, 59, 0, 0);
	    const events = this.getEvents();
	    for (const event of events) {
	      const eventFrom = event.from.getTime();
	      const eventTo = event.to.getTime();
	      if (eventTo > fromDateMax.getTime() && eventTo <= to.getTime()) {
	        fromDateMax = new Date(eventTo);
	      }
	      if (eventFrom < toDateMin.getTime() && eventFrom >= from.getTime()) {
	        toDateMin = new Date(eventFrom);
	      }
	    }

	    // if too many events
	    if (fromDateMax.getTime() >= toDateMin.getTime()) {
	      return {
	        from,
	        to,
	        wasMagnetizedFrom,
	        wasMagnetizedTo
	      };
	    }

	    // stick
	    if (fromDateMax.getTime() > from.getTime() && fromDateMax.getTime() - from.getTime() <= stickDistance) {
	      from = new Date(fromDateMax.getTime());
	      to = new Date(from.getTime() + duration);
	      wasMagnetizedFrom = true;
	    }
	    if (toDateMin.getTime() < to.getTime() && to.getTime() - toDateMin.getTime() <= stickDistance) {
	      to = new Date(toDateMin.getTime());
	      from = new Date(to.getTime() - duration);
	      wasMagnetizedTo = true;
	    }
	    if (fromDateMax.getTime() > from.getTime() && fromDateMax.getTime() - from.getTime() <= stickDistance) {
	      from = new Date(fromDateMax.getTime());
	      to = new Date(from.getTime() + duration);
	      wasMagnetizedFrom = true;
	    }
	    if (wasMagnetizedFrom && wasMagnetizedTo) {
	      from = new Date(fromDateMax.getTime());
	      to = new Date(toDateMin.getTime());
	    }

	    // magnetize
	    if (!wasMagnetizedFrom && !wasMagnetizedTo) {
	      if (from.getTime() > fromDateMax.getTime() && from.getTime() - fromDateMax.getTime() <= magnetDistance) {
	        wasMagnetizedFrom = true;
	      }
	      if (toDateMin.getTime() > to.getTime() && toDateMin.getTime() - to.getTime() <= magnetDistance) {
	        wasMagnetizedTo = true;
	      }
	      if (wasMagnetizedFrom && !wasMagnetizedTo) {
	        from = new Date(fromDateMax.getTime());
	        to = new Date(from.getTime() + duration);
	      }
	      if (wasMagnetizedTo && !wasMagnetizedFrom) {
	        to = new Date(toDateMin.getTime());
	        from = new Date(to.getTime() - duration);
	      }
	    }
	    return {
	      from,
	      to,
	      wasMagnetizedFrom,
	      wasMagnetizedTo
	    };
	  }
	  resetMagnetStamps(diff) {
	    if (diff > 0) {
	      this.endMagnetStamp = null;
	    }
	    if (diff < 0) {
	      this.startMagnetStamp = null;
	    }
	  }
	  calculateTryDuration(diff) {
	    if (Math.abs(diff) < 1) {
	      this.tryDuration++;
	    }
	    if (Math.abs(diff) > 3) {
	      this.tryDuration = 0;
	    }
	  }
	  doMagnetize() {
	    return !calendar_util.Util.isAnyModifierKeyPressed() && !this.isUserTryingToTurnOffMagnet();
	  }
	  isUserTryingToTurnOffMagnet() {
	    return this.tryDuration > this.maxTryDuration;
	  }
	}

	/**
	 * @implements DragAndDropInterface
	 */
	class ResizeDragAndDrop {
	  constructor(getDateByPos, getPosByDate) {
	    this.getDateByPos = getDateByPos;
	    this.getPosByDate = getPosByDate;
	  }
	  getFinalFrom() {
	    return this.from;
	  }
	  getFinalTo() {
	    return this.to;
	  }
	  onDragStart(timeInterval, minSize = 0, isStartResizer = false) {
	    this.from = timeInterval.from;
	    this.to = timeInterval.to;
	    this.isStartResizer = isStartResizer;
	    this.direction = isStartResizer ? -1 : 1;
	    this.positionStart = this.getPosByDate(this.from);
	    this.positionEnd = this.getPosByDate(this.to);
	    this.size = this.positionEnd - this.positionStart;
	    this.minSize = minSize;
	  }
	  getDragBoundary(dy) {
	    const size = Math.max(this.size + dy * this.direction, this.minSize);
	    if (this.isStartResizer) {
	      this.positionStart = this.positionEnd - size;
	      const timeFrom = this.getDateByPos(this.positionStart);
	      this.from.setHours(timeFrom.getHours(), timeFrom.getMinutes(), 0, 0);
	    } else {
	      this.positionEnd = this.positionStart + size;
	      const timeTo = this.getDateByPos(this.positionEnd);
	      this.to.setHours(timeTo.getHours(), timeTo.getMinutes(), 0, 0);
	    }
	    return {
	      from: this.from,
	      to: this.to,
	      position: this.positionStart,
	      size
	    };
	  }
	}

	exports.EventDragAndDrop = EventDragAndDrop;
	exports.ResizeDragAndDrop = ResizeDragAndDrop;

}((this.BX.Calendar.Ui.Tools = this.BX.Calendar.Ui.Tools || {}),BX.Calendar));
//# sourceMappingURL=draganddrop.bundle.js.map
