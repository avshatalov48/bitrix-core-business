/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	class BaseEvent extends main_core.Event.BaseEvent {
	  constructor(data) {
	    super({
	      data
	    });
	  }
	}

	class DragStartSensorEvent extends BaseEvent {}

	class Sensor extends main_core_events.EventEmitter {
	  constructor(container = [], options = {}) {
	    super();
	    this.originalDragStartEvent = null;
	    this.dragStartEvent = null;
	    this.setEventNamespace('BX.UI.DragAndDrop.Draggable.Sensor');
	    const {
	      dropzone
	    } = options;
	    this.containers = main_core.Type.isArray(container) ? [...container] : [container];
	    this.dropzones = main_core.Type.isArrayLike(dropzone) ? [...dropzone] : [dropzone];
	    this.options = {
	      delay: 0,
	      ...options
	    };
	  }
	  getDocument() {
	    return this.options.context.document;
	  }
	  addContainer(...containers) {
	    this.containers = [...this.containers, ...containers];
	  }
	  removeContainer(...containers) {
	    this.containers = this.containers.filter(container => {
	      return !containers.includes(container);
	    });
	  }
	  getContainerByChild(childElement) {
	    return this.containers.find(container => {
	      return container.contains(childElement);
	    });
	  }
	  addDropzone(...dropzones) {
	    this.dropzones = [...this.dropzones, ...dropzones];
	  }
	  removeDropzone(...dropzones) {
	    this.dropzones = this.dropzones.filter(dropzone => {
	      return !dropzones.includes(dropzone);
	    });
	  }
	  getDropzoneByChild(childElement) {
	    return this.dropzones.find(dropzone => {
	      return dropzone.contains(childElement);
	    });
	  }

	  // eslint-disable-next-line class-methods-use-this
	  getElementFromPoint(x, y) {
	    return this.getDocument().elementFromPoint(x, y);
	  }

	  // eslint-disable-next-line class-methods-use-this
	  preventDefaultEventAction(event) {
	    if (event.cancelable) {
	      event.preventDefault();
	    }
	  }
	  isDragging() {
	    return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	  }
	  enable() {
	    return this;
	  }
	  disable() {
	    return this;
	  }
	  getDragElementByChild(child) {
	    if (child) {
	      const {
	        dragElement,
	        elementsPreventingDrag
	      } = this.options;
	      if ((elementsPreventingDrag != null ? elementsPreventingDrag : []).some(selector => child.closest(selector))) {
	        return null;
	      }
	      return child.closest(dragElement) || null;
	    }
	    return null;
	  }
	}

	class DragMoveSensorEvent extends BaseEvent {}

	class DragEndSensorEvent extends BaseEvent {}

	class DragDropSensorEvent extends BaseEvent {}

	class MouseSensor extends Sensor {
	  constructor(container = [], options = {}) {
	    super(container, options);
	    this.mousedownTimeoutId = null;
	    this.onMouseDown = this.onMouseDown.bind(this);
	    this.onMouseMove = this.onMouseMove.bind(this);
	    this.onMouseUp = this.onMouseUp.bind(this);
	    this.onDragStart = this.onDragStart.bind(this);
	  }
	  enable() {
	    main_core.Event.bind(this.getDocument(), 'mousedown', this.onMouseDown, {
	      capture: true
	    });
	  }
	  disable() {
	    main_core.Event.unbind(this.getDocument(), 'mousedown', this.onMouseDown, {
	      capture: true
	    });
	  }
	  startHandleMouseUp() {
	    main_core.Event.bind(this.getDocument(), 'mouseup', this.onMouseUp);
	  }
	  stopHandleMouseUp() {
	    main_core.Event.unbind(this.getDocument(), 'mouseup', this.onMouseUp);
	  }
	  startHandleMouseMove() {
	    main_core.Event.bind(this.getDocument(), 'mousemove', this.onMouseMove);
	  }
	  stopHandleMouseMove() {
	    main_core.Event.unbind(this.getDocument(), 'mousemove', this.onMouseMove);
	  }
	  startPreventContextMenu() {
	    main_core.Event.bind(this.getDocument(), 'contextmenu', this.preventDefaultEventAction, {
	      capture: true
	    });
	  }
	  stopPreventContextMenu() {
	    main_core.Event.unbind(this.getDocument(), 'contextmenu', this.preventDefaultEventAction, {
	      capture: true
	    });
	  }
	  startPreventNativeDragAndDrop() {
	    main_core.Event.bind(this.getDocument(), 'dragstart', this.preventDefaultEventAction);
	  }
	  stopPreventNativeDragAndDrop() {
	    main_core.Event.unbind(this.getDocument(), 'dragstart', this.preventDefaultEventAction);
	  }
	  onMouseDown(event) {
	    if (!event.ctrlKey && !event.metaKey && !event.button) {
	      this.originalDragStartEvent = event;
	      const container = this.getContainerByChild(event.target);
	      if (container) {
	        const dragElement = this.getDragElementByChild(event.target);
	        if (dragElement) {
	          this.startHandleMouseUp();
	          this.startPreventNativeDragAndDrop();
	          this.mousedownTimeoutId = setTimeout(() => {
	            this.onDragStart();
	          }, this.options.delay);
	        }
	      }
	    }
	  }
	  onDragStart() {
	    const sourceContainer = this.getContainerByChild(this.originalDragStartEvent.target);
	    this.dragStartEvent = new DragStartSensorEvent({
	      clientX: this.originalDragStartEvent.clientX,
	      clientY: this.originalDragStartEvent.clientY,
	      originalSource: this.originalDragStartEvent.target,
	      originalEvent: this.originalDragStartEvent,
	      sourceContainer
	    });
	    this.emit('drag:start', this.dragStartEvent);
	    if (this.isDragging()) {
	      this.startPreventContextMenu();
	      this.startHandleMouseMove();
	    }
	  }
	  onMouseMove(originalEvent) {
	    if (this.isDragging()) {
	      const {
	        clientX,
	        clientY
	      } = originalEvent;
	      const over = this.getElementFromPoint(clientX, clientY);
	      const overContainer = this.getContainerByChild(over);
	      const {
	        originalSource,
	        sourceContainer
	      } = this.dragStartEvent.data;
	      const dragMoveEvent = new DragMoveSensorEvent({
	        clientX,
	        clientY,
	        originalSource,
	        sourceContainer,
	        over,
	        overContainer,
	        originalEvent
	      });
	      this.emit('drag:move', dragMoveEvent);
	    }
	  }
	  onMouseUp(originalEvent) {
	    clearTimeout(this.mousedownTimeoutId);
	    this.stopHandleMouseUp();
	    this.stopPreventNativeDragAndDrop();
	    if (this.isDragging()) {
	      const {
	        clientX,
	        clientY
	      } = originalEvent;
	      const over = this.getElementFromPoint(clientX, clientY);
	      const overContainer = this.getContainerByChild(over);
	      const {
	        originalSource,
	        sourceContainer
	      } = this.dragStartEvent.data;
	      const dragEndEvent = new DragEndSensorEvent({
	        clientX,
	        clientY,
	        originalSource,
	        sourceContainer,
	        over,
	        overContainer,
	        originalEvent
	      });
	      this.emit('drag:end', dragEndEvent);
	      if (!dragEndEvent.isDefaultPrevented()) {
	        const dropzone = this.getDropzoneByChild(over);
	        if (dropzone) {
	          const dragDropEvent = new DragDropSensorEvent({
	            clientX,
	            clientY,
	            originalSource,
	            sourceContainer,
	            over,
	            overContainer,
	            originalEvent,
	            dropzone
	          });
	          this.emit('drag:drop', dragDropEvent);
	        }
	      }
	      this.stopPreventContextMenu();
	      this.stopHandleMouseMove();
	    }
	    this.originalDragStartEvent = null;
	  }
	}

	let preventScrolling = false;
	main_core.Event.bind('touchmove', event => {
	  if (preventScrolling) {
	    event.preventDefault();
	  }
	}, {
	  passive: false
	});
	class TouchSensor extends Sensor {
	  constructor(container = [], options = {}) {
	    super(container, options);
	    this.tapTimeoutId = null;
	    this.touchMoved = false;
	    this.onTouchStart = this.onTouchStart.bind(this);
	    this.onTouchEnd = this.onTouchEnd.bind(this);
	    this.onTouchMove = this.onTouchMove.bind(this);
	    this.onDragStart = this.onDragStart.bind(this);
	  }
	  enable() {
	    main_core.Event.bind(this.getDocument(), 'touchstart', this.onTouchStart);
	  }
	  disable() {
	    main_core.Event.unbind(this.getDocument(), 'touchstart', this.onTouchStart);
	  }
	  isTouchMoved() {
	    return this.touchMoved;
	  }

	  // eslint-disable-next-line class-methods-use-this
	  startPreventScrolling() {
	    preventScrolling = true;
	  }

	  // eslint-disable-next-line class-methods-use-this
	  stopPreventScrolling() {
	    preventScrolling = false;
	  }
	  startPreventContextMenu() {
	    main_core.Event.bind(this.getDocument(), 'contextmenu', this.preventDefaultEventAction, {
	      capture: true
	    });
	  }
	  stopPreventContextMenu() {
	    main_core.Event.unbind(this.getDocument(), 'contextmenu', this.preventDefaultEventAction, {
	      capture: true
	    });
	  }
	  startHandleTouchEvents() {
	    main_core.Event.bind(this.getDocument(), 'touchmove', this.onTouchMove);
	    main_core.Event.bind(this.getDocument(), 'touchend', this.onTouchEnd);
	    main_core.Event.bind(this.getDocument(), 'touchcancel', this.onTouchEnd);
	  }
	  stopHandleTouchEvents() {
	    main_core.Event.unbind(this.getDocument(), 'touchmove', this.onTouchMove);
	    main_core.Event.unbind(this.getDocument(), 'touchend', this.onTouchEnd);
	    main_core.Event.unbind(this.getDocument(), 'touchcancel', this.onTouchEnd);
	  }
	  onTouchStart(event) {
	    const container = this.getContainerByChild(event.target);
	    if (container) {
	      const dragElement = this.getDragElementByChild(event.target);
	      if (dragElement) {
	        this.originalDragStartEvent = event;
	        this.startHandleTouchEvents();
	        this.startPreventContextMenu();
	        this.startPreventScrolling();
	        this.tapTimeoutId = setTimeout(() => {
	          if (!this.isTouchMoved()) {
	            this.onDragStart();
	          }
	        }, this.options.delay);
	      }
	    }
	  }
	  onDragStart() {
	    const touch = this.originalDragStartEvent.touches[0] || this.originalDragStartEvent.changedTouches[0];
	    const sourceContainer = this.getContainerByChild(this.originalDragStartEvent.target);
	    this.dragStartEvent = new DragStartSensorEvent({
	      clientX: touch.clientX,
	      clientY: touch.clientY,
	      originalSource: this.originalDragStartEvent.target,
	      originalEvent: this.originalDragStartEvent,
	      sourceContainer
	    });
	    this.emit('drag:start', this.dragStartEvent);
	  }
	  onTouchMove(originalEvent) {
	    this.touchMoved = true;
	    if (this.isDragging()) {
	      const touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
	      const {
	        clientX,
	        clientY
	      } = touch;
	      const over = this.getElementFromPoint(clientX, clientY);
	      const overContainer = this.getContainerByChild(over);
	      const {
	        originalSource,
	        sourceContainer
	      } = this.dragStartEvent.data;
	      const dragMoveEvent = new DragMoveSensorEvent({
	        clientX,
	        clientY,
	        originalSource,
	        sourceContainer,
	        over,
	        overContainer,
	        originalEvent
	      });
	      this.emit('drag:move', dragMoveEvent);
	    }
	  }
	  onTouchEnd(originalEvent) {
	    clearTimeout(this.tapTimeoutId);
	    this.stopPreventScrolling();
	    this.stopPreventContextMenu();
	    this.stopHandleTouchEvents();
	    if (this.isDragging()) {
	      const touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
	      const {
	        clientX,
	        clientY
	      } = touch;
	      const over = this.getElementFromPoint(clientX, clientY);
	      const overContainer = this.getContainerByChild(over);
	      const {
	        originalSource,
	        sourceContainer
	      } = this.dragStartEvent.data;
	      const dragEndEvent = new DragEndSensorEvent({
	        clientX,
	        clientY,
	        originalSource,
	        sourceContainer,
	        over,
	        overContainer,
	        originalEvent
	      });
	      this.emit('drag:end', dragEndEvent);
	      if (!dragEndEvent.isDefaultPrevented()) {
	        const dropzone = this.getDropzoneByChild(over);
	        if (dropzone) {
	          const dragDropEvent = new DragDropSensorEvent({
	            clientX,
	            clientY,
	            originalSource,
	            sourceContainer,
	            over,
	            overContainer,
	            originalEvent,
	            dropzone
	          });
	          this.emit('drag:drop', dragDropEvent);
	        }
	      }
	    }
	    this.originalDragStartEvent = null;
	    this.dragStartEvent = null;
	    this.touchMoved = false;
	  }
	}

	class DragBeforeStartEvent extends BaseEvent {}

	class DragStartEvent extends BaseEvent {}

	class DragMoveEvent extends BaseEvent {}

	class DragOverEvent extends BaseEvent {}

	class DragOverContainerEvent extends BaseEvent {}

	class DragEnterEvent extends BaseEvent {}

	class DragEnterContainerEvent extends BaseEvent {}

	class DragOutEvent extends BaseEvent {}

	class DragOutContainerEvent extends BaseEvent {}

	class DragEndEvent extends BaseEvent {}

	class DragOverDropzoneEvent extends BaseEvent {}

	class DragEnterDropzoneEvent extends BaseEvent {}

	class DragOutDropzoneEvent extends BaseEvent {}

	class DragDropEvent extends BaseEvent {}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	const defaultSensors = [MouseSensor, TouchSensor];
	const optionsKey = Symbol('options');
	const sensorsKey = Symbol('sensors');
	const containersKey = Symbol('containers');
	const dropzonesKey = Symbol('dropzones');

	/**
	 * @namespace BX.UI.DragAndDrop
	 */
	class Draggable extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super(options);
	    this[optionsKey] = {
	      delay: 0,
	      sensors: [],
	      draggable: '.ui-draggable--item',
	      type: 'move',
	      transitionDuration: 150,
	      dropzone: [],
	      context: window,
	      offset: {
	        x: 0,
	        y: 0
	      }
	    };
	    this[containersKey] = [];
	    this[dropzonesKey] = [];
	    this[sensorsKey] = [];
	    this.dragStartEvent = null;
	    this.setEventNamespace('BX.UI.DragAndDrop.Draggable');
	    this.cache = new main_core.Cache.MemoryCache();
	    this.onDragStart = this.onDragStart.bind(this);
	    this.onDragMove = this.onDragMove.bind(this);
	    this.onDragEnd = this.onDragEnd.bind(this);
	    this.onDragDrop = this.onDragDrop.bind(this);
	    if (main_core.Type.isArray(options.container) || main_core.Type.isDomNode(options.container) || options.container instanceof NodeList) {
	      if (options.container instanceof NodeList) {
	        this.addContainer(...options.container);
	      } else {
	        this.addContainer(...[options.container].flat());
	      }
	    } else {
	      throw new Error('Option container not a HTMLElement, Array of HTMLElement or NodeList');
	    }
	    if (!main_core.Type.isNil(options.dropzone) && (main_core.Type.isArray(options.dropzone) || main_core.Type.isDomNode(options.dropzone) || options.dropzone instanceof NodeList)) {
	      if (options.dropzone instanceof NodeList) {
	        this.addDropzone(...options.dropzone);
	      } else {
	        this.addDropzone(...[options.dropzone].flat());
	      }
	    }
	    this.setOptions({
	      ...this.getOptions(),
	      ...options
	    });
	    const {
	      sensors
	    } = this.getOptions();
	    this.addSensor(...defaultSensors, ...sensors);
	  }
	  getDocument() {
	    return this.getOptions().context.document;
	  }
	  getOptions() {
	    return this[optionsKey];
	  }
	  setOptions(options) {
	    this[optionsKey] = {
	      ...options
	    };
	    if (!main_core.Type.isString(this[optionsKey].dragElement)) {
	      this[optionsKey].dragElement = this[optionsKey].draggable;
	    }
	    if (!main_core.Type.isPlainObject(this[optionsKey].offset)) {
	      this[optionsKey].offset = {
	        x: 0,
	        y: 0
	      };
	    }
	    if (!main_core.Type.isNumber(this[optionsKey].offset.x)) {
	      this[optionsKey].offset.x = 0;
	    }
	    if (!main_core.Type.isNumber(this[optionsKey].offset.y)) {
	      this[optionsKey].offset.y = 0;
	    }
	    this.invalidateCache();
	  }
	  isDragging() {
	    return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	  }
	  getSensors() {
	    return this[sensorsKey];
	  }
	  addSensor(...sensors) {
	    const initializedSensors = sensors.map(CurrentSensor => {
	      const instance = new CurrentSensor(this.getContainers(), this.getOptions());
	      instance.subscribe('drag:start', this.onDragStart);
	      instance.subscribe('drag:move', this.onDragMove);
	      instance.subscribe('drag:end', this.onDragEnd);
	      instance.subscribe('drag:drop', this.onDragDrop);
	      instance.enable();
	      return instance;
	    });
	    this[sensorsKey] = [...this.getSensors(), ...initializedSensors];
	  }
	  destroy() {
	    this.removeSensor(...this.getSensors());
	  }
	  removeSensor(...sensors) {
	    const removedSensors = this.getSensors().filter(sensor => {
	      return sensors.includes(sensor);
	    });
	    removedSensors.forEach(sensor => {
	      sensor.unsubscribe('drag:start', this.onDragStart);
	      sensor.unsubscribe('drag:move', this.onDragMove);
	      sensor.unsubscribe('drag:end', this.onDragEnd);
	      sensor.unsubscribe('drag:drop', this.onDragDrop);
	      sensor.disable();
	    });
	    this[sensorsKey] = this.getSensors().filter(sensor => {
	      return !removedSensors.includes(sensor);
	    });
	  }
	  getContainers() {
	    return this[containersKey];
	  }
	  getContainerByChild(childElement) {
	    return this.getContainers().find(container => {
	      return container.contains(childElement);
	    });
	  }
	  addContainer(...containers) {
	    this[containersKey] = [...this.getContainers(), ...containers];
	    this[containersKey].forEach(container => {
	      main_core.Dom.addClass(container, 'ui-draggable--container');
	    });
	    this.getSensors().forEach(sensor => {
	      sensor.addContainer(...containers);
	    });
	    this.invalidateContainersCache();
	  }
	  removeContainer(...containers) {
	    this[containersKey] = this.getContainers().filter(container => {
	      return !containers.includes(container);
	    });
	    this.getSensors().forEach(sensor => {
	      sensor.removeContainer(...containers);
	    });
	    this.invalidateContainersCache();
	  }
	  getDropzones() {
	    return this[dropzonesKey];
	  }
	  getDropzoneByChild(childElement) {
	    return this.getDropzones().find(dropzone => {
	      return dropzone.contains(childElement);
	    });
	  }
	  addDropzone(...dropzones) {
	    this[dropzonesKey] = [...this.getDropzones(), ...dropzones];
	    this[dropzonesKey].forEach(dropzone => {
	      main_core.Dom.addClass(dropzone, 'ui-draggable--dropzone');
	    });
	    this.getSensors().forEach(sensor => {
	      sensor.addDropzone(...dropzones);
	    });
	  }
	  removeDropzone(...dropzones) {
	    this[dropzonesKey] = this.getContainers().filter(dropzone => {
	      return !dropzones.includes(dropzone);
	    });
	    this.getSensors().forEach(sensor => {
	      sensor.removeDropzone(...dropzones);
	    });
	  }
	  getDraggableElements() {
	    return this.cache.remember('draggableElements', () => {
	      return this.getContainers().reduce((acc, container) => {
	        return [...acc, ...this.getDraggableElementsOfContainer(container)];
	      }, []);
	    });
	  }
	  getDraggableElementsOfContainer(container) {
	    return this.cache.remember(container, () => {
	      const draggableSelector = this.getOptions().draggable;
	      const notDraggable = ':not(.ui-draggable--draggable)';
	      const notDropPreview = ':not(.ui-draggable--drop-preview)';
	      const filter = `${notDraggable}${notDropPreview}`;
	      const selector = `${draggableSelector}${filter}`;
	      const elements = [...container.querySelectorAll(selector)];
	      return elements.filter(element => element.parentElement === container);
	    });
	  }
	  getLastDraggableElementOfContainer(container) {
	    const draggableElements = this.getDraggableElementsOfContainer(container);
	    return draggableElements[draggableElements.length - 1] || null;
	  }
	  getElementIndex(element) {
	    return this.getDraggableElements().indexOf(element);
	  }
	  getDropPreview() {
	    return this.cache.remember('dropPreview', () => {
	      const {
	        type
	      } = this.getOptions();
	      const source = this.getSource();
	      if (source === null) {
	        return main_core.Tag.render(_t || (_t = _`<div></div>`));
	      }
	      const sourceRect = this.getSourceClientRect();
	      let dropPreview = null;
	      if (type === Draggable.CLONE) {
	        dropPreview = main_core.Runtime.clone(source);
	        main_core.Dom.addClass(dropPreview, 'ui-draggable--drop-preview-clone');
	      } else {
	        dropPreview = main_core.Tag.render(_t2 || (_t2 = _`<div></div>`));
	      }
	      main_core.Dom.addClass(dropPreview, 'ui-draggable--drop-preview');
	      main_core.Dom.style(dropPreview, {
	        width: `${sourceRect.width}px`,
	        height: `${sourceRect.height}px`
	      });
	      return dropPreview;
	    });
	  }
	  move(element, {
	    x = 0,
	    y = 0
	  }) {
	    const {
	      transitionDuration
	    } = this.getOptions();
	    requestAnimationFrame(() => {
	      main_core.Dom.style(element, {
	        transform: `translate3d(${x}px, ${y}px, 0px)`,
	        transition: `all ${transitionDuration}ms ease 0s`
	      });
	    });
	  }

	  /**
	   * @private
	   */
	  setSource(element) {
	    this.cache.set('source', element || null);
	  }

	  /**
	   * @private
	   */
	  getSource() {
	    return this.cache.get('source') || null;
	  }

	  /**
	   * @private
	   */
	  getSourceClientRect() {
	    return this.cache.remember('sourceClientRect', () => {
	      return this.cache.get('source').getBoundingClientRect();
	    });
	  }

	  /**
	   * @private
	   */
	  adjustDropPreview(target, options = {}) {
	    const {
	      x = false,
	      y = false,
	      force = true,
	      skipOffset = false,
	      transition = true
	    } = options;
	    const dropPreview = this.getDropPreview();
	    const targetRect = main_core.Dom.getRelativePosition(target, target.parentElement);
	    const dropPreviewRect = main_core.Dom.getRelativePosition(dropPreview, dropPreview.parentElement);
	    let offset = 0;
	    if (dropPreviewRect.height !== 0 && !skipOffset) {
	      if (targetRect.height > dropPreviewRect.height) {
	        if (targetRect.top > dropPreviewRect.top) {
	          offset = targetRect.height - dropPreviewRect.height;
	        }
	      } else if (targetRect.top > dropPreviewRect.top) {
	        offset = -Math.abs(targetRect.height - dropPreviewRect.height);
	      }
	    }
	    const {
	      transitionDuration
	    } = this.getOptions();
	    const adjustPosition = () => {
	      const style = {
	        transition: transition ? `all ${transitionDuration}ms ease 0ms` : 'null'
	      };
	      if (y) {
	        style.top = `${targetRect.top + offset}px`;
	      }
	      if (x) {
	        style.left = `${targetRect.left}px`;
	      }
	      main_core.Dom.style(dropPreview, style);
	    };
	    if (force) {
	      adjustPosition();
	    } else {
	      requestAnimationFrame(adjustPosition);
	    }
	  }
	  showDropPreviewAfter(element) {
	    const elementRect = main_core.Dom.getRelativePosition(element, element.parentElement);
	    const marginBottom = main_core.Text.toNumber(main_core.Dom.style(element, 'margin-bottom'));
	    const marginTop = main_core.Text.toNumber(main_core.Dom.style(element, 'margin-top'));
	    const bottom = elementRect.bottom + marginBottom + marginTop;
	    const {
	      transitionDuration
	    } = this.getOptions();
	    requestAnimationFrame(() => {
	      main_core.Dom.style(this.getDropPreview(), {
	        top: `${bottom}px`,
	        transition: `all ${transitionDuration}ms ease 0s`
	      });
	    });
	  }
	  pushDraggableElementToContainer(element, container) {
	    const lastDraggableElement = this.getLastDraggableElementOfContainer(container);
	    if (lastDraggableElement) {
	      main_core.Dom.insertAfter(element, lastDraggableElement);
	    } else {
	      main_core.Dom.append(element, container);
	    }
	    this.invalidateContainersCache();
	  }
	  resetDraggableElementsPosition(container, {
	    transition = true
	  } = {}) {
	    const draggableElements = (() => {
	      if (container) {
	        return this.getDraggableElementsOfContainer(container);
	      }
	      return this.getDraggableElements();
	    })();
	    draggableElements.forEach(element => {
	      main_core.Dom.style(element, {
	        transform: null,
	        transition: transition ? undefined : 'none'
	      });
	    });
	  }
	  resetDraggableElementsTransition(container) {
	    const draggableElements = (() => {
	      if (container) {
	        return this.getDraggableElementsOfContainer(container);
	      }
	      return this.getDraggableElements();
	    })();
	    draggableElements.forEach(element => {
	      main_core.Dom.style(element, {
	        transition: null
	      });
	    });
	  }
	  getSortOffsetY() {
	    return this.cache.remember('sortOffsetY', () => {
	      const source = this.getSource();
	      const sourceRect = this.getSourceClientRect();
	      const marginTop = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-top'));
	      const marginBottom = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-bottom'));
	      return Math.round(sourceRect.height + (marginTop + marginBottom));
	    });
	  }
	  getSortOffsetX() {
	    return this.cache.remember('sortOffsetX', () => {
	      const source = this.getSource();
	      const sourceRect = this.getSourceClientRect();
	      const marginLeft = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-left'));
	      const marginRight = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-right'));
	      return sourceRect.width + (marginLeft + marginRight);
	    });
	  }

	  // eslint-disable-next-line class-methods-use-this
	  getElementMiddlePoint(element) {
	    const elementRect = element.getBoundingClientRect();
	    return {
	      x: elementRect.left + elementRect.width / 2,
	      y: elementRect.top + elementRect.height / 2
	    };
	  }
	  getDraggableElementByChild(child) {
	    return child.closest(this.getOptions().draggable);
	  }
	  splitDraggableElementsListByPoint(container, point) {
	    let useRect = true;
	    return this.getDraggableElementsOfContainer(container).reduce((acc, element) => {
	      if (useRect) {
	        const elementMiddlePoint = this.getElementMiddlePoint(element);
	        if (elementMiddlePoint.y < point.y) {
	          acc[0].push(element);
	        } else {
	          acc[1].push(element);
	          useRect = false;
	        }
	      } else {
	        acc[1].push(element);
	      }
	      return acc;
	    }, [[], []]);
	  }
	  invalidateContainersCache() {
	    this.cache.delete('draggableElements');
	    this.getContainers().forEach(container => this.cache.delete(container));
	  }
	  invalidateCache() {
	    this.cache.delete('source');
	    this.cache.delete('sourceClientRect');
	    this.cache.delete('dropPreview');
	    this.cache.delete('sortOffsetY');
	    this.cache.delete('sortOffsetX');
	    this.cache.delete('sourceLeftOffset');
	    this.cache.delete('sourceLeftMargin');
	    this.invalidateContainersCache();
	  }
	  isDepthEditorEnabled() {
	    const {
	      depth,
	      type
	    } = this.getOptions();
	    return main_core.Type.isPlainObject(depth) && (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE);
	  }
	  getDepthProperty() {
	    const {
	      depth
	    } = this.getOptions();
	    return depth.property || 'margin-left';
	  }
	  getDepthMargin() {
	    const {
	      depth
	    } = this.getOptions();
	    return main_core.Text.toNumber(depth.margin) || 20;
	  }
	  getElementDepth(element) {
	    return main_core.Text.toNumber(main_core.Dom.attr(element, 'data-depth'));
	  }
	  setElementDepth(element, depth) {
	    main_core.Dom.attr(element, 'data-depth', depth);
	    const depthMargin = this.getDepthMargin();
	    const sourceMargin = this.getSourceLeftMargin();
	    const margin = depthMargin * depth + sourceMargin;
	    main_core.Dom.style(element, this.getDepthProperty(), `${margin}px`);
	  }
	  getStartSourceDepth() {
	    return this.dragStartEvent.data.sourceDepth;
	  }
	  getSourceWidth() {
	    return this.getSourceClientRect().width;
	  }
	  getSourceLeftOffset() {
	    return this.cache.remember('sourceLeftOffset', () => {
	      const source = this.getSource();
	      const sourceRect = main_core.Dom.getRelativePosition(source, source.parentElement);
	      const sourceMargin = this.getStartSourceDepth() * this.getDepthMargin();
	      return sourceRect.left - sourceMargin;
	    });
	  }
	  getSourceLeftMargin() {
	    return this.cache.remember('sourceLeftMargin', () => {
	      const source = this.getSource();
	      const sourceDepth = this.getStartSourceDepth();
	      const depthMargin = this.getDepthMargin();
	      const sourceDepthMargin = sourceDepth * depthMargin;
	      const sourceMargin = main_core.Text.toNumber(main_core.Dom.style(source, this.getDepthProperty()));
	      return sourceMargin - sourceDepthMargin;
	    });
	  }
	  setDropPreviewDepth(depth) {
	    const sourceDepth = this.getStartSourceDepth();
	    const sourceWidth = this.getSourceWidth();
	    const depthMargin = this.getDepthMargin();
	    const sourceLeftOffset = this.getSourceLeftOffset();
	    const dropPreviewWidth = (() => {
	      const depthDiff = Math.abs(sourceDepth - depth);
	      if (depth > sourceDepth) {
	        return sourceWidth - depthDiff * depthMargin;
	      }
	      if (depth < sourceDepth) {
	        return sourceWidth + depthDiff * depthMargin;
	      }
	      return sourceWidth;
	    })();
	    main_core.Dom.style(this.getDropPreview(), {
	      left: `${depth * depthMargin + sourceLeftOffset}px`,
	      width: `${dropPreviewWidth}px`
	    });
	  }
	  calcDepthByOffset(offsetX) {
	    const startSourceDepth = this.getStartSourceDepth();
	    const depthMargin = this.getDepthMargin();
	    const sourceDepthMargin = startSourceDepth * depthMargin;
	    return Math.max(0, Math.floor((offsetX + sourceDepthMargin) / depthMargin));
	  }
	  getChildren(parent) {
	    const parentDepth = this.getElementDepth(parent);
	    const parentRect = parent.getBoundingClientRect();
	    const container = this.getContainerByChild(parent);
	    const [, nextElements] = this.splitDraggableElementsListByPoint(container, {
	      x: parentRect.left,
	      y: parentRect.bottom
	    });
	    let stop = false;
	    return nextElements.reduce((acc, element) => {
	      if (!stop) {
	        const currentDepth = this.getElementDepth(element);
	        if (currentDepth > parentDepth) {
	          return [...acc, element];
	        }
	        stop = true;
	      }
	      return acc;
	    }, []);
	  }
	  getPreviousElement(element) {
	    const elementRect = element.getBoundingClientRect();
	    const container = this.getContainerByChild(element);
	    const [prevElements] = this.splitDraggableElementsListByPoint(container, {
	      x: elementRect.left,
	      y: elementRect.top
	    });
	    if (main_core.Type.isArrayFilled(prevElements)) {
	      return prevElements.pop();
	    }
	    return null;
	  }
	  onDragStart(event) {
	    const {
	      originalSource,
	      sourceContainer,
	      clientX,
	      clientY
	    } = event.data;
	    const source = this.getDraggableElementByChild(originalSource);
	    const dragBeforeStartEvent = new DragBeforeStartEvent({
	      clientX,
	      clientY,
	      source,
	      sourceContainer,
	      originalSource
	    });
	    this.emit('beforeStart', dragBeforeStartEvent);
	    if (dragBeforeStartEvent.isDefaultPrevented()) {
	      event.preventDefault();
	      return;
	    }
	    this.setSource(source);
	    const sourceDepth = this.getElementDepth(source);
	    const sourceRect = this.getSourceClientRect();
	    const pointerOffsetX = clientX - sourceRect.left;
	    const pointerOffsetY = clientY - sourceRect.top;
	    const {
	      type
	    } = this.getOptions();
	    let draggable = source;
	    if (type !== Draggable.HEADLESS) {
	      const clone = main_core.Runtime.clone(source);
	      main_core.Dom.style(clone, 'margin', 0);
	      draggable = main_core.Tag.render(_t3 || (_t3 = _`<div>${0}</div>`), clone);
	      main_core.Dom.style(draggable, {
	        width: `${sourceRect.width}px`,
	        height: `${sourceRect.height}px`,
	        top: `${clientY - pointerOffsetY + this.getOptions().offset.y}px`,
	        left: `${clientX - pointerOffsetX + this.getOptions().offset.x}px`
	      });
	      main_core.Dom.addClass(draggable, 'ui-draggable--draggable');
	      this.pushDraggableElementToContainer(draggable, sourceContainer);
	      if (this.isDepthEditorEnabled()) {
	        const children = this.getChildren(source);
	        this.childrenElements = children;
	        if (children.length > 0) {
	          main_core.Dom.append(main_core.Runtime.clone(clone), draggable);
	          children.forEach(element => {
	            main_core.Dom.style(element, 'display', 'none');
	          });
	        }
	      }
	    }
	    const dropPreview = this.getDropPreview();
	    if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	      this.pushDraggableElementToContainer(dropPreview, sourceContainer);
	      this.adjustDropPreview(source, {
	        force: true,
	        x: true,
	        y: true,
	        transition: false
	      });
	    }
	    main_core.Dom.addClass(source, 'ui-draggable--source');
	    main_core.Dom.addClass(this.getDocument().body, 'ui-draggable--disable-user-select');
	    main_core.Dom.addClass(this.getDocument().body, `ui-draggable--type-${this.getOptions().type}`);
	    const sourceIndex = this.getElementIndex(source);
	    this.dragStartEvent = new DragStartEvent({
	      clientX,
	      clientY,
	      pointerOffsetX,
	      pointerOffsetY,
	      draggable,
	      dropPreview,
	      source,
	      sourceIndex,
	      sourceContainer,
	      sourceDepth,
	      originalSource
	    });
	    this.emit('start', this.dragStartEvent);
	    if (this.dragStartEvent.isDefaultPrevented()) {
	      event.preventDefault();
	    }
	  }

	  // eslint-disable-next-line max-lines-per-function
	  onDragMove(event) {
	    if (!this.isDragging()) {
	      return;
	    }
	    const {
	      clientX,
	      clientY,
	      sourceContainer,
	      originalSource
	    } = event.data;
	    const {
	      clientX: startClientX,
	      clientY: startClientY,
	      pointerOffsetX,
	      pointerOffsetY,
	      source,
	      sourceIndex,
	      draggable,
	      dropPreview
	    } = this.dragStartEvent.data;
	    const offsetX = clientX - startClientX;
	    const offsetY = clientY - startClientY;
	    const dragMoveEvent = new DragMoveEvent({
	      clientX,
	      clientY,
	      offsetX,
	      offsetY,
	      pointerOffsetX,
	      pointerOffsetY,
	      draggable,
	      dropPreview,
	      source,
	      sourceIndex,
	      sourceContainer,
	      originalSource
	    });
	    this.emit('move', dragMoveEvent);
	    if (dragMoveEvent.isDefaultPrevented()) {
	      event.preventDefault();
	    }
	    if (!main_core.Type.isDomNode(event.data.over)) {
	      return;
	    }
	    const originalOver = event.data.over;
	    const over = this.getDraggableElementByChild(originalOver);
	    const overContainer = this.getContainerByChild(originalOver);
	    const {
	      type
	    } = this.getOptions();
	    if (type !== Draggable.HEADLESS) {
	      main_core.Dom.style(draggable, {
	        top: `${clientY - pointerOffsetY + this.getOptions().offset.y}px`,
	        left: `${clientX - pointerOffsetX + this.getOptions().offset.x}px`
	      });
	      if (overContainer && overContainer.contains(source) && !this.stopMove) {
	        const sortOffsetY = this.getSortOffsetY();
	        const draggableElements = this.getDraggableElementsOfContainer(overContainer);
	        const localSourceIndex = draggableElements.indexOf(source);
	        draggableElements.forEach((element, index) => {
	          if (element !== source) {
	            // eslint-disable-next-line @bitrix24/bitrix24-rules/no-style
	            const currentTransform = element.style.transform;
	            const elementMiddlePoint = this.getElementMiddlePoint(element);
	            if (elementMiddlePoint.y === 0) {
	              return;
	            }
	            if (index > localSourceIndex && clientY > elementMiddlePoint.y && currentTransform !== `translate3d(0px, ${-sortOffsetY}px, 0px)`) {
	              this.adjustDropPreview(element, {
	                y: true
	              });
	              this.move(element, {
	                y: -sortOffsetY
	              });
	              this.insertType = 'after';
	              this.insertElement = element;
	            }
	            if (index < localSourceIndex && clientY < elementMiddlePoint.y && currentTransform !== `translate3d(0px, ${sortOffsetY}px, 0px)`) {
	              this.adjustDropPreview(element, {
	                y: true
	              });
	              this.move(element, {
	                y: sortOffsetY
	              });
	              this.insertType = 'before';
	              this.insertElement = element;
	            }
	            if ((index < localSourceIndex && clientY > elementMiddlePoint.y || index > localSourceIndex && clientY < elementMiddlePoint.y) && currentTransform !== 'translate3d(0px, 0px, 0px)' && currentTransform !== '') {
	              this.adjustDropPreview(element, {
	                y: true
	              });
	              this.move(element, {
	                y: 0
	              });
	              this.insertElement = element;
	              if (index < localSourceIndex && clientY > elementMiddlePoint.y) {
	                this.insertType = 'after';
	              }
	              if (index > localSourceIndex && clientY < elementMiddlePoint.y) {
	                this.insertType = 'before';
	              }
	            }
	          }
	        });
	      }
	    }
	    if (this.isDepthEditorEnabled()) {
	      let currentDepth = this.calcDepthByOffset(offsetX);
	      const parentElement = this.getPreviousElement(dropPreview);
	      if (parentElement) {
	        const prevDepth = this.getElementDepth(parentElement);
	        const minDepth = 0;
	        const maxDepth = Math.max(minDepth, prevDepth + 1);
	        currentDepth = Math.max(minDepth, Math.min(currentDepth, maxDepth));
	      } else {
	        currentDepth = 0;
	      }
	      this.setDropPreviewDepth(currentDepth);
	      this.currentDepth = currentDepth;
	    }
	    if (main_core.Type.isDomNode(over) && source !== over) {
	      const dragOverEvent = new DragOverEvent({
	        ...dragMoveEvent.data,
	        over,
	        originalOver,
	        overContainer
	      });
	      this.emit('over', dragOverEvent);
	      if (!dragOverEvent.isDefaultPrevented()) {
	        main_core.Dom.addClass(over, 'ui-draggable--over');
	      }
	      if (over !== this.lastOver) {
	        const dragEnterEvent = new DragEnterEvent({
	          ...dragMoveEvent.data,
	          enter: over,
	          enterContainer: overContainer
	        });
	        this.emit('enter', dragEnterEvent);
	      }
	    }
	    this.lastOver = this.lastOver || over;
	    if (!over || over !== this.lastOver) {
	      if (this.lastOver) {
	        const outContainer = this.getContainerByChild(this.lastOver);
	        const dragOutEvent = new DragOutEvent({
	          ...dragMoveEvent,
	          out: this.lastOver,
	          outContainer
	        });
	        this.emit('out', dragOutEvent);
	        main_core.Dom.removeClass(this.lastOver, 'ui-draggable--over');
	      }
	      this.lastOver = over;
	    }
	    const sourceOver = this.getDocument().elementFromPoint(clientX, clientY);
	    const dropzoneOver = this.getDropzoneByChild(sourceOver);
	    if (dropzoneOver) {
	      const dragOverDropzoneEvent = new DragOverDropzoneEvent({
	        ...dragMoveEvent.data,
	        dropzone: dropzoneOver
	      });
	      this.emit('dropzone:over', dragOverDropzoneEvent);
	      if (dropzoneOver !== this.lastOverDropzone) {
	        const dragEnterDropzoneEvent = new DragEnterDropzoneEvent({
	          ...dragMoveEvent.data,
	          dropzone: dropzoneOver
	        });
	        this.emit('dropzone:enter', dragEnterDropzoneEvent);
	      }
	    }
	    this.lastOverDropzone = this.lastOverDropzone || dropzoneOver;
	    if (dropzoneOver !== this.lastOverDropzone) {
	      const dragOutDropzoneEvent = new DragOutDropzoneEvent({
	        ...dragMoveEvent.data,
	        dropzone: this.lastOverDropzone
	      });
	      this.emit('dropzone:out', dragOutDropzoneEvent);
	      this.lastOverDropzone = dropzoneOver;
	    }
	    if (overContainer) {
	      const dragOverContainerEvent = new DragOverContainerEvent({
	        ...dragMoveEvent.data,
	        over: overContainer
	      });
	      this.emit('container:over', dragOverContainerEvent);
	      if (overContainer !== this.lastOverContainer) {
	        const dragEnterContainerEvent = new DragEnterContainerEvent({
	          ...dragMoveEvent.data,
	          enter: overContainer
	        });
	        this.emit('container:enter', dragEnterContainerEvent);
	        if (!overContainer.contains(source)) {
	          const lastContainer = this.getContainerByChild(source);
	          const [beforeElements, afterElements] = this.splitDraggableElementsListByPoint(overContainer, {
	            x: clientX,
	            y: clientY
	          });
	          if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	            this.stopMove = true;
	            setTimeout(() => {
	              this.stopMove = false;
	            }, 300);
	            this.pushDraggableElementToContainer(this.getDropPreview(), overContainer);
	          }
	          if (type !== Draggable.HEADLESS) {
	            this.pushDraggableElementToContainer(source, overContainer);
	          }
	          if (main_core.Type.isArrayFilled(beforeElements)) {
	            const lastElement = beforeElements[beforeElements.length - 1];
	            if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	              this.showDropPreviewAfter(lastElement);
	            }
	            this.insertType = 'after';
	            this.insertElement = lastElement;
	          } else if (main_core.Type.isArrayFilled(afterElements)) {
	            const [firstElement] = afterElements;
	            if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	              this.adjustDropPreview(afterElements);
	            }
	            this.insertType = 'before';
	            this.insertElement = firstElement;
	          }
	          this.resetDraggableElementsTransition(lastContainer);
	          this.resetDraggableElementsPosition(lastContainer);
	          if (type !== Draggable.HEADLESS && main_core.Type.isArrayFilled(afterElements)) {
	            const sortOffsetY = this.getSortOffsetY();
	            afterElements.forEach(element => {
	              this.move(element, {
	                y: sortOffsetY
	              });
	            });
	          }
	        }
	      }
	    }
	    this.lastOverContainer = this.lastOverContainer || overContainer;
	    if (overContainer !== this.lastOverContainer) {
	      const dragOutContainerEvent = new DragOutContainerEvent({
	        ...dragMoveEvent.data,
	        out: this.lastOverContainer
	      });
	      this.emit('container:out', dragOutContainerEvent);
	      this.lastOverContainer = overContainer;
	    }
	  }
	  onDragEnd(event) {
	    const dragEndEvent = new DragEndEvent({
	      ...this.dragStartEvent.data,
	      clientX: event.data.clientX,
	      clientY: event.data.clientY,
	      end: this.lastOver,
	      endContainer: this.lastOverContainer
	    });
	    const {
	      source,
	      draggable
	    } = this.dragStartEvent.data;
	    if (this.getOptions().type !== Draggable.HEADLESS) {
	      main_core.Dom.remove(draggable);
	    }
	    main_core.Dom.removeClass(source, 'ui-draggable--source');
	    this.getDraggableElements().forEach(element => {
	      main_core.Dom.removeClass(element, 'ui-draggable--draggable');
	      main_core.Dom.removeClass(element, 'ui-draggable--over');
	    });
	    main_core.Dom.remove(this.getDropPreview());
	    this.resetDraggableElementsPosition();
	    this.resetDraggableElementsTransition();
	    if (this.getOptions().type !== Draggable.HEADLESS && main_core.Type.isString(this.insertType)) {
	      if (this.insertType === 'after') {
	        main_core.Dom.insertAfter(source, this.insertElement);
	      } else {
	        main_core.Dom.insertBefore(source, this.insertElement);
	      }
	    }
	    if (this.isDepthEditorEnabled()) {
	      const startSourceDepth = this.getStartSourceDepth();
	      const depthDiff = (() => {
	        if (main_core.Type.isNumber(this.currentDepth)) {
	          return this.currentDepth - startSourceDepth;
	        }
	        return 0;
	      })();
	      let lastElement = source;
	      this.childrenElements.forEach(element => {
	        const currentDepth = this.getElementDepth(element);
	        this.setElementDepth(element, currentDepth + depthDiff);
	        main_core.Dom.insertAfter(element, lastElement);
	        main_core.Dom.style(element, 'display', null);
	        lastElement = element;
	      });
	      if (main_core.Type.isNumber(this.currentDepth)) {
	        this.setElementDepth(source, this.currentDepth);
	      }
	    }
	    this.lastOver = null;
	    this.lastOverContainer = null;
	    this.insertType = null;
	    this.lastOverDropzone = null;
	    this.childrenElements = [];
	    this.currentDepth = null;
	    this.invalidateCache();
	    main_core.Dom.removeClass(this.getDocument().body, 'ui-draggable--disable-user-select');
	    main_core.Dom.removeClass(this.getDocument().body, `ui-draggable--type-${this.getOptions().type}`);
	    this.emit('end', dragEndEvent); // todo test in default
	  }

	  onDragDrop(event) {
	    const dragDropEvent = new DragDropEvent({
	      ...this.dragStartEvent.data,
	      clientX: event.data.clientX,
	      clientY: event.data.clientY,
	      dropzone: event.data.dropzone
	    });
	    this.emit('drop', dragDropEvent);
	  }
	}
	Draggable.MOVE = 'move';
	Draggable.CLONE = 'clone';
	Draggable.DROP_PREVIEW = 'drop-preview';
	Draggable.HEADLESS = 'headless';

	exports.Draggable = Draggable;
	exports.DragStartEvent = DragStartEvent;
	exports.DragMoveEvent = DragMoveEvent;
	exports.DragOutEvent = DragOutEvent;
	exports.DragOutContainerEvent = DragOutContainerEvent;
	exports.DragEndEvent = DragEndEvent;
	exports.DragOverEvent = DragOverEvent;
	exports.DragOverContainerEvent = DragOverContainerEvent;
	exports.DragEnterEvent = DragEnterEvent;
	exports.DragEnterContainerEvent = DragEnterContainerEvent;

}((this.BX.UI.DragAndDrop = this.BX.UI.DragAndDrop || {}),BX.Event,BX));
//# sourceMappingURL=draggable.bundle.js.map
