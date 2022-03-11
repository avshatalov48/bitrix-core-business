this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	var BaseEvent = /*#__PURE__*/function (_Event$BaseEvent) {
	  babelHelpers.inherits(BaseEvent, _Event$BaseEvent);

	  function BaseEvent(data) {
	    babelHelpers.classCallCheck(this, BaseEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseEvent).call(this, {
	      data: data
	    }));
	  }

	  return BaseEvent;
	}(main_core.Event.BaseEvent);

	var DragStartSensorEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragStartSensorEvent, _BaseEvent);

	  function DragStartSensorEvent() {
	    babelHelpers.classCallCheck(this, DragStartSensorEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragStartSensorEvent).apply(this, arguments));
	  }

	  return DragStartSensorEvent;
	}(BaseEvent);

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Sensor = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Sensor, _EventEmitter);

	  function Sensor() {
	    var _this;

	    var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Sensor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Sensor).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "originalDragStartEvent", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dragStartEvent", null);

	    _this.setEventNamespace('BX.UI.DragAndDrop.Draggable.Sensor');

	    var dropzone = options.dropzone;
	    _this.containers = main_core.Type.isArray(container) ? babelHelpers.toConsumableArray(container) : [container];
	    _this.dropzones = main_core.Type.isArrayLike(dropzone) ? babelHelpers.toConsumableArray(dropzone) : [dropzone];
	    _this.options = _objectSpread({
	      delay: 0
	    }, options);
	    return _this;
	  }

	  babelHelpers.createClass(Sensor, [{
	    key: "getDocument",
	    value: function getDocument() {
	      return this.options.context.document;
	    }
	  }, {
	    key: "addContainer",
	    value: function addContainer() {
	      for (var _len = arguments.length, containers = new Array(_len), _key = 0; _key < _len; _key++) {
	        containers[_key] = arguments[_key];
	      }

	      this.containers = [].concat(babelHelpers.toConsumableArray(this.containers), containers);
	    }
	  }, {
	    key: "removeContainer",
	    value: function removeContainer() {
	      for (var _len2 = arguments.length, containers = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        containers[_key2] = arguments[_key2];
	      }

	      this.containers = this.containers.filter(function (container) {
	        return !containers.includes(container);
	      });
	    }
	  }, {
	    key: "getContainerByChild",
	    value: function getContainerByChild(childElement) {
	      return this.containers.find(function (container) {
	        return container.contains(childElement);
	      });
	    }
	  }, {
	    key: "addDropzone",
	    value: function addDropzone() {
	      for (var _len3 = arguments.length, dropzones = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        dropzones[_key3] = arguments[_key3];
	      }

	      this.dropzones = [].concat(babelHelpers.toConsumableArray(this.dropzones), dropzones);
	    }
	  }, {
	    key: "removeDropzone",
	    value: function removeDropzone() {
	      for (var _len4 = arguments.length, dropzones = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	        dropzones[_key4] = arguments[_key4];
	      }

	      this.dropzones = this.dropzones.filter(function (dropzone) {
	        return !dropzones.includes(dropzone);
	      });
	    }
	  }, {
	    key: "getDropzoneByChild",
	    value: function getDropzoneByChild(childElement) {
	      return this.dropzones.find(function (dropzone) {
	        return dropzone.contains(childElement);
	      });
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "getElementFromPoint",
	    value: function getElementFromPoint(x, y) {
	      return this.getDocument().elementFromPoint(x, y);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "preventDefaultEventAction",
	    value: function preventDefaultEventAction(event) {
	      if (event.cancelable) {
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "isDragging",
	    value: function isDragging() {
	      return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      return this;
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      return this;
	    }
	  }, {
	    key: "getDragElementByChild",
	    value: function getDragElementByChild(child) {
	      if (child) {
	        var dragElement = this.options.dragElement;
	        return child.closest(dragElement) || null;
	      }

	      return null;
	    }
	  }]);
	  return Sensor;
	}(main_core_events.EventEmitter);

	var DragMoveSensorEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragMoveSensorEvent, _BaseEvent);

	  function DragMoveSensorEvent() {
	    babelHelpers.classCallCheck(this, DragMoveSensorEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragMoveSensorEvent).apply(this, arguments));
	  }

	  return DragMoveSensorEvent;
	}(BaseEvent);

	var DragEndSensorEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragEndSensorEvent, _BaseEvent);

	  function DragEndSensorEvent() {
	    babelHelpers.classCallCheck(this, DragEndSensorEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragEndSensorEvent).apply(this, arguments));
	  }

	  return DragEndSensorEvent;
	}(BaseEvent);

	var DragDropSensorEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragDropSensorEvent, _BaseEvent);

	  function DragDropSensorEvent() {
	    babelHelpers.classCallCheck(this, DragDropSensorEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragDropSensorEvent).apply(this, arguments));
	  }

	  return DragDropSensorEvent;
	}(BaseEvent);

	var MouseSensor = /*#__PURE__*/function (_Sensor) {
	  babelHelpers.inherits(MouseSensor, _Sensor);

	  function MouseSensor() {
	    var _this;

	    var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, MouseSensor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MouseSensor).call(this, container, options));
	    _this.mousedownTimeoutId = null;
	    _this.onMouseDown = _this.onMouseDown.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMouseMove = _this.onMouseMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMouseUp = _this.onMouseUp.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragStart = _this.onDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(MouseSensor, [{
	    key: "enable",
	    value: function enable() {
	      this.getDocument().addEventListener('mousedown', this.onMouseDown, true);
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      this.getDocument().removeEventListener('mousedown', this.onMouseDown, true);
	    }
	  }, {
	    key: "startHandleMouseUp",
	    value: function startHandleMouseUp() {
	      this.getDocument().addEventListener('mouseup', this.onMouseUp);
	    }
	  }, {
	    key: "stopHandleMouseUp",
	    value: function stopHandleMouseUp() {
	      this.getDocument().removeEventListener('mouseup', this.onMouseUp);
	    }
	  }, {
	    key: "startHandleMouseMove",
	    value: function startHandleMouseMove() {
	      this.getDocument().addEventListener('mousemove', this.onMouseMove);
	    }
	  }, {
	    key: "stopHandleMouseMove",
	    value: function stopHandleMouseMove() {
	      this.getDocument().removeEventListener('mousemove', this.onMouseMove);
	    }
	  }, {
	    key: "startPreventContextMenu",
	    value: function startPreventContextMenu() {
	      this.getDocument().addEventListener('contextmenu', this.preventDefaultEventAction, true);
	    }
	  }, {
	    key: "stopPreventContextMenu",
	    value: function stopPreventContextMenu() {
	      this.getDocument().removeEventListener('contextmenu', this.preventDefaultEventAction, true);
	    }
	  }, {
	    key: "startPreventNativeDragAndDrop",
	    value: function startPreventNativeDragAndDrop() {
	      this.getDocument().addEventListener('dragstart', this.preventDefaultEventAction);
	    }
	  }, {
	    key: "stopPreventNativeDragAndDrop",
	    value: function stopPreventNativeDragAndDrop() {
	      this.getDocument().removeEventListener('dragstart', this.preventDefaultEventAction);
	    }
	  }, {
	    key: "onMouseDown",
	    value: function onMouseDown(event) {
	      var _this2 = this;

	      if (!event.ctrlKey && !event.metaKey && !event.button) {
	        this.originalDragStartEvent = event;
	        var container = this.getContainerByChild(event.target);

	        if (container) {
	          var dragElement = this.getDragElementByChild(event.target);

	          if (dragElement) {
	            this.startHandleMouseUp();
	            this.startPreventNativeDragAndDrop();
	            this.mousedownTimeoutId = setTimeout(function () {
	              _this2.onDragStart();
	            }, this.options.delay);
	          }
	        }
	      }
	    }
	  }, {
	    key: "onDragStart",
	    value: function onDragStart() {
	      var sourceContainer = this.getContainerByChild(this.originalDragStartEvent.target);
	      this.dragStartEvent = new DragStartSensorEvent({
	        clientX: this.originalDragStartEvent.clientX,
	        clientY: this.originalDragStartEvent.clientY,
	        originalSource: this.originalDragStartEvent.target,
	        originalEvent: this.originalDragStartEvent,
	        sourceContainer: sourceContainer
	      });
	      this.emit('drag:start', this.dragStartEvent);

	      if (this.isDragging()) {
	        this.startPreventContextMenu();
	        this.startHandleMouseMove();
	      }
	    }
	  }, {
	    key: "onMouseMove",
	    value: function onMouseMove(originalEvent) {
	      if (this.isDragging()) {
	        var clientX = originalEvent.clientX,
	            clientY = originalEvent.clientY;
	        var over = this.getElementFromPoint(clientX, clientY);
	        var overContainer = this.getContainerByChild(over);
	        var _this$dragStartEvent$ = this.dragStartEvent.data,
	            originalSource = _this$dragStartEvent$.originalSource,
	            sourceContainer = _this$dragStartEvent$.sourceContainer;
	        var dragMoveEvent = new DragMoveSensorEvent({
	          clientX: clientX,
	          clientY: clientY,
	          originalSource: originalSource,
	          sourceContainer: sourceContainer,
	          over: over,
	          overContainer: overContainer,
	          originalEvent: originalEvent
	        });
	        this.emit('drag:move', dragMoveEvent);
	      }
	    }
	  }, {
	    key: "onMouseUp",
	    value: function onMouseUp(originalEvent) {
	      clearTimeout(this.mousedownTimeoutId);
	      this.stopHandleMouseUp();
	      this.stopPreventNativeDragAndDrop();

	      if (this.isDragging()) {
	        var clientX = originalEvent.clientX,
	            clientY = originalEvent.clientY;
	        var over = this.getElementFromPoint(clientX, clientY);
	        var overContainer = this.getContainerByChild(over);
	        var _this$dragStartEvent$2 = this.dragStartEvent.data,
	            originalSource = _this$dragStartEvent$2.originalSource,
	            sourceContainer = _this$dragStartEvent$2.sourceContainer;
	        var dragEndEvent = new DragEndSensorEvent({
	          clientX: clientX,
	          clientY: clientY,
	          originalSource: originalSource,
	          sourceContainer: sourceContainer,
	          over: over,
	          overContainer: overContainer,
	          originalEvent: originalEvent
	        });
	        this.emit('drag:end', dragEndEvent);

	        if (!dragEndEvent.isDefaultPrevented()) {
	          var dropzone = this.getDropzoneByChild(over);

	          if (dropzone) {
	            var dragDropEvent = new DragDropSensorEvent({
	              clientX: clientX,
	              clientY: clientY,
	              originalSource: originalSource,
	              sourceContainer: sourceContainer,
	              over: over,
	              overContainer: overContainer,
	              originalEvent: originalEvent,
	              dropzone: dropzone
	            });
	            this.emit('drag:drop', dragDropEvent);
	          }
	        }

	        this.stopPreventContextMenu();
	        this.stopHandleMouseMove();
	      }

	      this.originalDragStartEvent = null;
	    }
	  }]);
	  return MouseSensor;
	}(Sensor);

	var preventScrolling = false;
	window.addEventListener('touchmove', function (event) {
	  if (preventScrolling) {
	    event.preventDefault();
	  }
	}, {
	  passive: false
	});

	var TouchSensor = /*#__PURE__*/function (_Sensor) {
	  babelHelpers.inherits(TouchSensor, _Sensor);

	  function TouchSensor() {
	    var _this;

	    var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, TouchSensor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TouchSensor).call(this, container, options));
	    _this.tapTimeoutId = null;
	    _this.touchMoved = false;
	    _this.onTouchStart = _this.onTouchStart.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onTouchEnd = _this.onTouchEnd.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onTouchMove = _this.onTouchMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragStart = _this.onDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(TouchSensor, [{
	    key: "enable",
	    value: function enable() {
	      this.getDocument().addEventListener('touchstart', this.onTouchStart);
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      this.getDocument().removeEventListener('touchstart', this.onTouchStart);
	    }
	  }, {
	    key: "isTouchMoved",
	    value: function isTouchMoved() {
	      return this.touchMoved;
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "startPreventScrolling",
	    value: function startPreventScrolling() {
	      preventScrolling = true;
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "stopPreventScrolling",
	    value: function stopPreventScrolling() {
	      preventScrolling = false;
	    }
	  }, {
	    key: "startPreventContextMenu",
	    value: function startPreventContextMenu() {
	      this.getDocument().addEventListener('contextmenu', this.preventDefaultEventAction, true);
	    }
	  }, {
	    key: "stopPreventContextMenu",
	    value: function stopPreventContextMenu() {
	      this.getDocument().removeEventListener('contextmenu', this.preventDefaultEventAction, true);
	    }
	  }, {
	    key: "startHandleTouchEvents",
	    value: function startHandleTouchEvents() {
	      this.getDocument().addEventListener('touchmove', this.onTouchMove);
	      this.getDocument().addEventListener('touchend', this.onTouchEnd);
	      this.getDocument().addEventListener('touchcancel', this.onTouchEnd);
	    }
	  }, {
	    key: "stopHandleTouchEvents",
	    value: function stopHandleTouchEvents() {
	      this.getDocument().removeEventListener('touchmove', this.onTouchMove);
	      this.getDocument().removeEventListener('touchend', this.onTouchEnd);
	      this.getDocument().removeEventListener('touchcancel', this.onTouchEnd);
	    }
	  }, {
	    key: "onTouchStart",
	    value: function onTouchStart(event) {
	      var _this2 = this;

	      var container = this.getContainerByChild(event.target);

	      if (container) {
	        var dragElement = this.getDragElementByChild(event.target);

	        if (dragElement) {
	          this.originalDragStartEvent = event;
	          this.startHandleTouchEvents();
	          this.startPreventContextMenu();
	          this.startPreventScrolling();
	          this.tapTimeoutId = setTimeout(function () {
	            if (!_this2.isTouchMoved()) {
	              _this2.onDragStart();
	            }
	          }, this.options.delay);
	        }
	      }
	    }
	  }, {
	    key: "onDragStart",
	    value: function onDragStart() {
	      var touch = this.originalDragStartEvent.touches[0] || this.originalDragStartEvent.changedTouches[0];
	      var sourceContainer = this.getContainerByChild(this.originalDragStartEvent.target);
	      this.dragStartEvent = new DragStartSensorEvent({
	        clientX: touch.clientX,
	        clientY: touch.clientY,
	        originalSource: this.originalDragStartEvent.target,
	        originalEvent: this.originalDragStartEvent,
	        sourceContainer: sourceContainer
	      });
	      this.emit('drag:start', this.dragStartEvent);
	    }
	  }, {
	    key: "onTouchMove",
	    value: function onTouchMove(originalEvent) {
	      this.touchMoved = true;

	      if (this.isDragging()) {
	        var touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
	        var clientX = touch.clientX,
	            clientY = touch.clientY;
	        var over = this.getElementFromPoint(clientX, clientY);
	        var overContainer = this.getContainerByChild(over);
	        var _this$dragStartEvent$ = this.dragStartEvent.data,
	            originalSource = _this$dragStartEvent$.originalSource,
	            sourceContainer = _this$dragStartEvent$.sourceContainer;
	        var dragMoveEvent = new DragMoveSensorEvent({
	          clientX: clientX,
	          clientY: clientY,
	          originalSource: originalSource,
	          sourceContainer: sourceContainer,
	          over: over,
	          overContainer: overContainer,
	          originalEvent: originalEvent
	        });
	        this.emit('drag:move', dragMoveEvent);
	      }
	    }
	  }, {
	    key: "onTouchEnd",
	    value: function onTouchEnd(originalEvent) {
	      clearTimeout(this.tapTimeoutId);
	      this.stopPreventScrolling();
	      this.stopPreventContextMenu();
	      this.stopHandleTouchEvents();

	      if (this.isDragging()) {
	        var touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
	        var clientX = touch.clientX,
	            clientY = touch.clientY;
	        var over = this.getElementFromPoint(clientX, clientY);
	        var overContainer = this.getContainerByChild(over);
	        var _this$dragStartEvent$2 = this.dragStartEvent.data,
	            originalSource = _this$dragStartEvent$2.originalSource,
	            sourceContainer = _this$dragStartEvent$2.sourceContainer;
	        var dragEndEvent = new DragEndSensorEvent({
	          clientX: clientX,
	          clientY: clientY,
	          originalSource: originalSource,
	          sourceContainer: sourceContainer,
	          over: over,
	          overContainer: overContainer,
	          originalEvent: originalEvent
	        });
	        this.emit('drag:end', dragEndEvent);

	        if (!dragEndEvent.isDefaultPrevented()) {
	          var dropzone = this.getDropzoneByChild(over);

	          if (dropzone) {
	            var dragDropEvent = new DragDropSensorEvent({
	              clientX: clientX,
	              clientY: clientY,
	              originalSource: originalSource,
	              sourceContainer: sourceContainer,
	              over: over,
	              overContainer: overContainer,
	              originalEvent: originalEvent,
	              dropzone: dropzone
	            });
	            this.emit('drag:drop', dragDropEvent);
	          }
	        }
	      }

	      this.originalDragStartEvent = null;
	      this.dragStartEvent = null;
	      this.touchMoved = false;
	    }
	  }]);
	  return TouchSensor;
	}(Sensor);

	var DragBeforeStartEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragBeforeStartEvent, _BaseEvent);

	  function DragBeforeStartEvent() {
	    babelHelpers.classCallCheck(this, DragBeforeStartEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragBeforeStartEvent).apply(this, arguments));
	  }

	  return DragBeforeStartEvent;
	}(BaseEvent);

	var DragStartEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragStartEvent, _BaseEvent);

	  function DragStartEvent() {
	    babelHelpers.classCallCheck(this, DragStartEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragStartEvent).apply(this, arguments));
	  }

	  return DragStartEvent;
	}(BaseEvent);

	var DragMoveEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragMoveEvent, _BaseEvent);

	  function DragMoveEvent() {
	    babelHelpers.classCallCheck(this, DragMoveEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragMoveEvent).apply(this, arguments));
	  }

	  return DragMoveEvent;
	}(BaseEvent);

	var DragOverEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOverEvent, _BaseEvent);

	  function DragOverEvent() {
	    babelHelpers.classCallCheck(this, DragOverEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOverEvent).apply(this, arguments));
	  }

	  return DragOverEvent;
	}(BaseEvent);

	var DragOverContainerEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOverContainerEvent, _BaseEvent);

	  function DragOverContainerEvent() {
	    babelHelpers.classCallCheck(this, DragOverContainerEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOverContainerEvent).apply(this, arguments));
	  }

	  return DragOverContainerEvent;
	}(BaseEvent);

	var DragEnterEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragEnterEvent, _BaseEvent);

	  function DragEnterEvent() {
	    babelHelpers.classCallCheck(this, DragEnterEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragEnterEvent).apply(this, arguments));
	  }

	  return DragEnterEvent;
	}(BaseEvent);

	var DragEnterContainerEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragEnterContainerEvent, _BaseEvent);

	  function DragEnterContainerEvent() {
	    babelHelpers.classCallCheck(this, DragEnterContainerEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragEnterContainerEvent).apply(this, arguments));
	  }

	  return DragEnterContainerEvent;
	}(BaseEvent);

	var DragOutEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOutEvent, _BaseEvent);

	  function DragOutEvent() {
	    babelHelpers.classCallCheck(this, DragOutEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOutEvent).apply(this, arguments));
	  }

	  return DragOutEvent;
	}(BaseEvent);

	var DragOutContainerEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOutContainerEvent, _BaseEvent);

	  function DragOutContainerEvent() {
	    babelHelpers.classCallCheck(this, DragOutContainerEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOutContainerEvent).apply(this, arguments));
	  }

	  return DragOutContainerEvent;
	}(BaseEvent);

	var DragEndEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragEndEvent, _BaseEvent);

	  function DragEndEvent() {
	    babelHelpers.classCallCheck(this, DragEndEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragEndEvent).apply(this, arguments));
	  }

	  return DragEndEvent;
	}(BaseEvent);

	var DragOverDropzoneEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOverDropzoneEvent, _BaseEvent);

	  function DragOverDropzoneEvent() {
	    babelHelpers.classCallCheck(this, DragOverDropzoneEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOverDropzoneEvent).apply(this, arguments));
	  }

	  return DragOverDropzoneEvent;
	}(BaseEvent);

	var DragEnterDropzoneEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragEnterDropzoneEvent, _BaseEvent);

	  function DragEnterDropzoneEvent() {
	    babelHelpers.classCallCheck(this, DragEnterDropzoneEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragEnterDropzoneEvent).apply(this, arguments));
	  }

	  return DragEnterDropzoneEvent;
	}(BaseEvent);

	var DragOutDropzoneEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragOutDropzoneEvent, _BaseEvent);

	  function DragOutDropzoneEvent() {
	    babelHelpers.classCallCheck(this, DragOutDropzoneEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragOutDropzoneEvent).apply(this, arguments));
	  }

	  return DragOutDropzoneEvent;
	}(BaseEvent);

	var DragDropEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(DragDropEvent, _BaseEvent);

	  function DragDropEvent() {
	    babelHelpers.classCallCheck(this, DragDropEvent);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DragDropEvent).apply(this, arguments));
	  }

	  return DragDropEvent;
	}(BaseEvent);

	var _templateObject, _templateObject2, _templateObject3;

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var defaultSensors = [MouseSensor, TouchSensor];
	var optionsKey = Symbol('options');
	var sensorsKey = Symbol('sensors');
	var containersKey = Symbol('containers');
	var dropzonesKey = Symbol('dropzones');
	/**
	 * @namespace BX.UI.DragAndDrop
	 */

	var Draggable = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Draggable, _EventEmitter);

	  function Draggable() {
	    var _this6;

	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Draggable);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Draggable).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), optionsKey, {
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
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), containersKey, []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), dropzonesKey, []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), sensorsKey, []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dragStartEvent", null);

	    _this.setEventNamespace('BX.UI.DragAndDrop.Draggable');

	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onDragStart = _this.onDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragMove = _this.onDragMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragEnd = _this.onDragEnd.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragDrop = _this.onDragDrop.bind(babelHelpers.assertThisInitialized(_this));

	    if (main_core.Type.isArray(options.container) || main_core.Type.isDomNode(options.container) || options.container instanceof NodeList) {
	      if (options.container instanceof NodeList) {
	        var _this2;

	        (_this2 = _this).addContainer.apply(_this2, babelHelpers.toConsumableArray(options.container));
	      } else {
	        var _this3;

	        (_this3 = _this).addContainer.apply(_this3, babelHelpers.toConsumableArray([options.container].flat()));
	      }
	    } else {
	      throw new Error('Option container not a HTMLElement, Array of HTMLElement or NodeList');
	    }

	    if (!main_core.Type.isNil(options.dropzone)) {
	      if (main_core.Type.isArray(options.dropzone) || main_core.Type.isDomNode(options.dropzone) || options.dropzone instanceof NodeList) {
	        if (options.dropzone instanceof NodeList) {
	          var _this4;

	          (_this4 = _this).addDropzone.apply(_this4, babelHelpers.toConsumableArray(options.dropzone));
	        } else {
	          var _this5;

	          (_this5 = _this).addDropzone.apply(_this5, babelHelpers.toConsumableArray([options.dropzone].flat()));
	        }
	      }
	    }

	    _this.setOptions(_objectSpread$1(_objectSpread$1({}, _this.getOptions()), options));

	    var _this$getOptions = _this.getOptions(),
	        sensors = _this$getOptions.sensors;

	    (_this6 = _this).addSensor.apply(_this6, [].concat(defaultSensors, babelHelpers.toConsumableArray(sensors)));

	    return _this;
	  }

	  babelHelpers.createClass(Draggable, [{
	    key: "getDocument",
	    value: function getDocument() {
	      return this.getOptions().context.document;
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this[optionsKey];
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      this[optionsKey] = _objectSpread$1({}, options);

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
	  }, {
	    key: "isDragging",
	    value: function isDragging() {
	      return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	    }
	  }, {
	    key: "getSensors",
	    value: function getSensors() {
	      return this[sensorsKey];
	    }
	  }, {
	    key: "addSensor",
	    value: function addSensor() {
	      var _this7 = this;

	      for (var _len = arguments.length, sensors = new Array(_len), _key = 0; _key < _len; _key++) {
	        sensors[_key] = arguments[_key];
	      }

	      var initializedSensors = sensors.map(function (CurrentSensor) {
	        var instance = new CurrentSensor(_this7.getContainers(), _this7.getOptions());
	        instance.subscribe('drag:start', _this7.onDragStart);
	        instance.subscribe('drag:move', _this7.onDragMove);
	        instance.subscribe('drag:end', _this7.onDragEnd);
	        instance.subscribe('drag:drop', _this7.onDragDrop);
	        instance.enable();
	        return instance;
	      });
	      this[sensorsKey] = [].concat(babelHelpers.toConsumableArray(this.getSensors()), babelHelpers.toConsumableArray(initializedSensors));
	    }
	  }, {
	    key: "removeSensor",
	    value: function removeSensor() {
	      var _this8 = this;

	      for (var _len2 = arguments.length, sensors = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        sensors[_key2] = arguments[_key2];
	      }

	      var removedSensors = this.getSensors().filter(function (sensor) {
	        return sensors.includes(sensor.constructor);
	      });
	      removedSensors.forEach(function (sensor) {
	        sensor.unsubscribe('drag:start', _this8.onDragStart);
	        sensor.unsubscribe('drag:move', _this8.onDragMove);
	        sensor.unsubscribe('drag:end', _this8.onDragEnd);
	        sensor.unsubscribe('drag:drop', _this8.onDragDrop);
	        sensor.enable();
	      });
	      this[sensorsKey] = this.getSensors().filter(function (sensor) {
	        return !removedSensors.includes(sensor);
	      });
	    }
	  }, {
	    key: "getContainers",
	    value: function getContainers() {
	      return this[containersKey];
	    }
	  }, {
	    key: "getContainerByChild",
	    value: function getContainerByChild(childElement) {
	      return this.getContainers().find(function (container) {
	        return container.contains(childElement);
	      });
	    }
	  }, {
	    key: "addContainer",
	    value: function addContainer() {
	      for (var _len3 = arguments.length, containers = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        containers[_key3] = arguments[_key3];
	      }

	      this[containersKey] = [].concat(babelHelpers.toConsumableArray(this.getContainers()), containers);
	      this[containersKey].forEach(function (container) {
	        main_core.Dom.addClass(container, 'ui-draggable--container');
	      });
	      this.getSensors().forEach(function (sensor) {
	        sensor.addContainer.apply(sensor, containers);
	      });
	      this.invalidateContainersCache();
	    }
	  }, {
	    key: "removeContainer",
	    value: function removeContainer() {
	      for (var _len4 = arguments.length, containers = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	        containers[_key4] = arguments[_key4];
	      }

	      this[containersKey] = this.getContainers().filter(function (container) {
	        return !containers.includes(container);
	      });
	      this.getSensors().forEach(function (sensor) {
	        sensor.removeContainer.apply(sensor, containers);
	      });
	      this.invalidateContainersCache();
	    }
	  }, {
	    key: "getDropzones",
	    value: function getDropzones() {
	      return this[dropzonesKey];
	    }
	  }, {
	    key: "getDropzoneByChild",
	    value: function getDropzoneByChild(childElement) {
	      return this.getDropzones().find(function (dropzone) {
	        return dropzone.contains(childElement);
	      });
	    }
	  }, {
	    key: "addDropzone",
	    value: function addDropzone() {
	      for (var _len5 = arguments.length, dropzones = new Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
	        dropzones[_key5] = arguments[_key5];
	      }

	      this[dropzonesKey] = [].concat(babelHelpers.toConsumableArray(this.getDropzones()), dropzones);
	      this[dropzonesKey].forEach(function (dropzone) {
	        main_core.Dom.addClass(dropzone, 'ui-draggable--dropzone');
	      });
	      this.getSensors().forEach(function (sensor) {
	        sensor.addDropzone.apply(sensor, dropzones);
	      });
	    }
	  }, {
	    key: "removeDropzone",
	    value: function removeDropzone() {
	      for (var _len6 = arguments.length, dropzones = new Array(_len6), _key6 = 0; _key6 < _len6; _key6++) {
	        dropzones[_key6] = arguments[_key6];
	      }

	      this[dropzonesKey] = this.getContainers().filter(function (dropzone) {
	        return !dropzones.includes(dropzone);
	      });
	      this.getSensors().forEach(function (sensor) {
	        sensor.removeDropzone.apply(sensor, dropzones);
	      });
	    }
	  }, {
	    key: "getDraggableElements",
	    value: function getDraggableElements() {
	      var _this9 = this;

	      return this.cache.remember('draggableElements', function () {
	        return _this9.getContainers().reduce(function (acc, container) {
	          return [].concat(babelHelpers.toConsumableArray(acc), babelHelpers.toConsumableArray(_this9.getDraggableElementsOfContainer(container)));
	        }, []);
	      });
	    }
	  }, {
	    key: "getDraggableElementsOfContainer",
	    value: function getDraggableElementsOfContainer(container) {
	      var _this10 = this;

	      return this.cache.remember(container, function () {
	        var draggableSelector = _this10.getOptions().draggable;

	        var notDraggable = ':not(.ui-draggable--draggable)';
	        var notDropPreview = ':not(.ui-draggable--drop-preview)';
	        var filter = "".concat(notDraggable).concat(notDropPreview);
	        var selector = "".concat(draggableSelector).concat(filter);
	        var elements = babelHelpers.toConsumableArray(container.querySelectorAll(selector));
	        return elements.filter(function (element) {
	          return element.parentElement === container;
	        });
	      });
	    }
	  }, {
	    key: "getLastDraggableElementOfContainer",
	    value: function getLastDraggableElementOfContainer(container) {
	      var draggableElements = this.getDraggableElementsOfContainer(container);
	      return draggableElements[draggableElements.length - 1] || null;
	    }
	  }, {
	    key: "getElementIndex",
	    value: function getElementIndex(element) {
	      return this.getDraggableElements().indexOf(element);
	    }
	  }, {
	    key: "getDropPreview",
	    value: function getDropPreview() {
	      var _this11 = this;

	      return this.cache.remember('dropPreview', function () {
	        var _this11$getOptions = _this11.getOptions(),
	            type = _this11$getOptions.type;

	        var source = _this11.getSource();

	        if (source === null) {
	          return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	        }

	        var sourceRect = _this11.getSourceClientRect();

	        var dropPreview;

	        if (type === Draggable.CLONE) {
	          dropPreview = main_core.Runtime.clone(source);
	          main_core.Dom.addClass(dropPreview, 'ui-draggable--drop-preview-clone');
	        } else {
	          dropPreview = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	        }

	        main_core.Dom.addClass(dropPreview, 'ui-draggable--drop-preview');
	        main_core.Dom.style(dropPreview, {
	          width: "".concat(sourceRect.width, "px"),
	          height: "".concat(sourceRect.height, "px")
	        });
	        return dropPreview;
	      });
	    }
	  }, {
	    key: "move",
	    value: function move(element, _ref) {
	      var _ref$x = _ref.x,
	          x = _ref$x === void 0 ? 0 : _ref$x,
	          _ref$y = _ref.y,
	          y = _ref$y === void 0 ? 0 : _ref$y;

	      var _this$getOptions2 = this.getOptions(),
	          transitionDuration = _this$getOptions2.transitionDuration;

	      requestAnimationFrame(function () {
	        main_core.Dom.style(element, {
	          transform: "translate3d(".concat(x, "px, ").concat(y, "px, 0px)"),
	          transition: "all ".concat(transitionDuration, "ms ease 0s")
	        });
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setSource",
	    value: function setSource(element) {
	      this.cache.set('source', element || null);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getSource",
	    value: function getSource() {
	      return this.cache.get('source') || null;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getSourceClientRect",
	    value: function getSourceClientRect() {
	      var _this12 = this;

	      return this.cache.remember('sourceClientRect', function () {
	        return _this12.cache.get('source').getBoundingClientRect();
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "adjustDropPreview",
	    value: function adjustDropPreview(target) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var _options$x = options.x,
	          x = _options$x === void 0 ? false : _options$x,
	          _options$y = options.y,
	          y = _options$y === void 0 ? false : _options$y,
	          _options$force = options.force,
	          force = _options$force === void 0 ? true : _options$force,
	          _options$skipOffset = options.skipOffset,
	          skipOffset = _options$skipOffset === void 0 ? false : _options$skipOffset,
	          _options$transition = options.transition,
	          transition = _options$transition === void 0 ? true : _options$transition;
	      var dropPreview = this.getDropPreview();
	      var targetRect = main_core.Dom.getRelativePosition(target, target.parentElement);
	      var dropPreviewRect = main_core.Dom.getRelativePosition(dropPreview, dropPreview.parentElement);
	      var offset = 0;

	      if (dropPreviewRect.height !== 0 && !skipOffset) {
	        if (targetRect.height > dropPreviewRect.height) {
	          if (targetRect.top > dropPreviewRect.top) {
	            offset = targetRect.height - dropPreviewRect.height;
	          }
	        } else if (targetRect.top > dropPreviewRect.top) {
	          offset = -Math.abs(targetRect.height - dropPreviewRect.height);
	        }
	      }

	      var _this$getOptions3 = this.getOptions(),
	          transitionDuration = _this$getOptions3.transitionDuration;

	      var adjustPosition = function adjustPosition() {
	        var style = {
	          transition: transition ? "all ".concat(transitionDuration, "ms ease 0ms") : 'null'
	        };

	        if (y) {
	          style.top = "".concat(targetRect.top + offset, "px");
	        }

	        if (x) {
	          style.left = "".concat(targetRect.left, "px");
	        }

	        main_core.Dom.style(dropPreview, style);
	      };

	      if (force) {
	        adjustPosition();
	      } else {
	        requestAnimationFrame(adjustPosition);
	      }
	    }
	  }, {
	    key: "showDropPreviewAfter",
	    value: function showDropPreviewAfter(element) {
	      var _this13 = this;

	      var elementRect = main_core.Dom.getRelativePosition(element, element.parentElement);
	      var marginBottom = main_core.Text.toNumber(main_core.Dom.style(element, 'margin-bottom'));
	      var marginTop = main_core.Text.toNumber(main_core.Dom.style(element, 'margin-top'));
	      var bottom = elementRect.bottom + marginBottom + marginTop;

	      var _this$getOptions4 = this.getOptions(),
	          transitionDuration = _this$getOptions4.transitionDuration;

	      requestAnimationFrame(function () {
	        main_core.Dom.style(_this13.getDropPreview(), {
	          top: "".concat(bottom, "px"),
	          transition: "all ".concat(transitionDuration, "ms ease 0s")
	        });
	      });
	    }
	  }, {
	    key: "pushDraggableElementToContainer",
	    value: function pushDraggableElementToContainer(element, container) {
	      var lastDraggableElement = this.getLastDraggableElementOfContainer(container);

	      if (lastDraggableElement) {
	        main_core.Dom.insertAfter(element, lastDraggableElement);
	      } else {
	        main_core.Dom.append(element, container);
	      }

	      this.invalidateContainersCache();
	    }
	  }, {
	    key: "resetDraggableElementsPosition",
	    value: function resetDraggableElementsPosition(container) {
	      var _this14 = this;

	      var _ref2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
	          _ref2$transition = _ref2.transition,
	          transition = _ref2$transition === void 0 ? true : _ref2$transition;

	      var draggableElements = function () {
	        if (container) {
	          return _this14.getDraggableElementsOfContainer(container);
	        }

	        return _this14.getDraggableElements();
	      }();

	      draggableElements.forEach(function (element) {
	        main_core.Dom.style(element, {
	          transform: null,
	          transition: !transition ? 'none' : undefined
	        });
	      });
	    }
	  }, {
	    key: "resetDraggableElementsTransition",
	    value: function resetDraggableElementsTransition(container) {
	      var _this15 = this;

	      var draggableElements = function () {
	        if (container) {
	          return _this15.getDraggableElementsOfContainer(container);
	        }

	        return _this15.getDraggableElements();
	      }();

	      draggableElements.forEach(function (element) {
	        main_core.Dom.style(element, {
	          transition: null
	        });
	      });
	    }
	  }, {
	    key: "getSortOffsetY",
	    value: function getSortOffsetY() {
	      var _this16 = this;

	      return this.cache.remember('sortOffsetY', function () {
	        var source = _this16.getSource();

	        var sourceRect = _this16.getSourceClientRect();

	        var marginTop = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-top'));
	        var marginBottom = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-bottom'));
	        return sourceRect.height + (marginTop + marginBottom);
	      });
	    }
	  }, {
	    key: "getSortOffsetX",
	    value: function getSortOffsetX() {
	      var _this17 = this;

	      return this.cache.remember('sortOffsetX', function () {
	        var source = _this17.getSource();

	        var sourceRect = _this17.getSourceClientRect();

	        var marginLeft = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-left'));
	        var marginRight = main_core.Text.toNumber(main_core.Dom.style(source, 'margin-right'));
	        return sourceRect.width + (marginLeft + marginRight);
	      });
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "getElementMiddlePoint",
	    value: function getElementMiddlePoint(element) {
	      var elementRect = element.getBoundingClientRect();
	      return {
	        x: elementRect.left + elementRect.width / 2,
	        y: elementRect.top + elementRect.height / 2
	      };
	    }
	  }, {
	    key: "getDraggableElementByChild",
	    value: function getDraggableElementByChild(child) {
	      return child.closest(this.getOptions().draggable);
	    }
	  }, {
	    key: "splitDraggableElementsListByPoint",
	    value: function splitDraggableElementsListByPoint(container, point) {
	      var _this18 = this;

	      var useRect = true;
	      return this.getDraggableElementsOfContainer(container).reduce(function (acc, element) {
	        if (useRect) {
	          var elementMiddlePoint = _this18.getElementMiddlePoint(element);

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
	  }, {
	    key: "invalidateContainersCache",
	    value: function invalidateContainersCache() {
	      var _this19 = this;

	      this.cache["delete"]('draggableElements');
	      this.getContainers().forEach(function (container) {
	        return _this19.cache["delete"](container);
	      });
	    }
	  }, {
	    key: "invalidateCache",
	    value: function invalidateCache() {
	      this.cache["delete"]('source');
	      this.cache["delete"]('sourceClientRect');
	      this.cache["delete"]('dropPreview');
	      this.cache["delete"]('sortOffsetY');
	      this.cache["delete"]('sortOffsetX');
	      this.cache["delete"]('sourceLeftOffset');
	      this.cache["delete"]('sourceLeftMargin');
	      this.invalidateContainersCache();
	    }
	  }, {
	    key: "isDepthEditorEnabled",
	    value: function isDepthEditorEnabled() {
	      var _this$getOptions5 = this.getOptions(),
	          depth = _this$getOptions5.depth,
	          type = _this$getOptions5.type;

	      return main_core.Type.isPlainObject(depth) && (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE);
	    }
	  }, {
	    key: "getDepthProperty",
	    value: function getDepthProperty() {
	      var _this$getOptions6 = this.getOptions(),
	          depth = _this$getOptions6.depth;

	      return depth.property || 'margin-left';
	    }
	  }, {
	    key: "getDepthMargin",
	    value: function getDepthMargin() {
	      var _this$getOptions7 = this.getOptions(),
	          depth = _this$getOptions7.depth;

	      return main_core.Text.toNumber(depth.margin) || 20;
	    } // eslint-disable-next-line

	  }, {
	    key: "getElementDepth",
	    value: function getElementDepth(element) {
	      return main_core.Text.toNumber(main_core.Dom.attr(element, 'data-depth'));
	    }
	  }, {
	    key: "setElementDepth",
	    value: function setElementDepth(element, depth) {
	      main_core.Dom.attr(element, 'data-depth', depth);
	      var depthMargin = this.getDepthMargin();
	      var sourceMargin = this.getSourceLeftMargin();
	      var margin = depthMargin * depth + sourceMargin;
	      main_core.Dom.style(element, this.getDepthProperty(), "".concat(margin, "px"));
	    }
	  }, {
	    key: "getStartSourceDepth",
	    value: function getStartSourceDepth() {
	      return this.dragStartEvent.data.sourceDepth;
	    }
	  }, {
	    key: "getSourceWidth",
	    value: function getSourceWidth() {
	      return this.getSourceClientRect().width;
	    }
	  }, {
	    key: "getSourceLeftOffset",
	    value: function getSourceLeftOffset() {
	      var _this20 = this;

	      return this.cache.remember('sourceLeftOffset', function () {
	        var source = _this20.getSource();

	        var sourceRect = main_core.Dom.getRelativePosition(source, source.parentElement);

	        var sourceMargin = _this20.getStartSourceDepth() * _this20.getDepthMargin();

	        return sourceRect.left - sourceMargin;
	      });
	    }
	  }, {
	    key: "getSourceLeftMargin",
	    value: function getSourceLeftMargin() {
	      var _this21 = this;

	      return this.cache.remember('sourceLeftMargin', function () {
	        var source = _this21.getSource();

	        var sourceDepth = _this21.getStartSourceDepth();

	        var depthMargin = _this21.getDepthMargin();

	        var sourceDepthMargin = sourceDepth * depthMargin;
	        var sourceMargin = main_core.Text.toNumber(main_core.Dom.style(source, _this21.getDepthProperty()));
	        return sourceMargin - sourceDepthMargin;
	      });
	    }
	  }, {
	    key: "setDropPreviewDepth",
	    value: function setDropPreviewDepth(depth) {
	      var sourceDepth = this.getStartSourceDepth();
	      var sourceWidth = this.getSourceWidth();
	      var depthMargin = this.getDepthMargin();
	      var sourceLeftOffset = this.getSourceLeftOffset();

	      var dropPreviewWidth = function () {
	        var depthDiff = Math.abs(sourceDepth - depth);

	        if (depth > sourceDepth) {
	          return sourceWidth - depthDiff * depthMargin;
	        }

	        if (depth < sourceDepth) {
	          return sourceWidth + depthDiff * depthMargin;
	        }

	        return sourceWidth;
	      }();

	      main_core.Dom.style(this.getDropPreview(), {
	        left: "".concat(depth * depthMargin + sourceLeftOffset, "px"),
	        width: "".concat(dropPreviewWidth, "px")
	      });
	    }
	  }, {
	    key: "calcDepthByOffset",
	    value: function calcDepthByOffset(offsetX) {
	      var startSourceDepth = this.getStartSourceDepth();
	      var depthMargin = this.getDepthMargin();
	      var sourceDepthMargin = startSourceDepth * depthMargin;
	      return Math.max(0, Math.floor((offsetX + sourceDepthMargin) / depthMargin));
	    }
	  }, {
	    key: "getChildren",
	    value: function getChildren(parent) {
	      var _this22 = this;

	      var parentDepth = this.getElementDepth(parent);
	      var parentRect = parent.getBoundingClientRect();
	      var container = this.getContainerByChild(parent);

	      var _this$splitDraggableE = this.splitDraggableElementsListByPoint(container, {
	        x: parentRect.left,
	        y: parentRect.bottom
	      }),
	          _this$splitDraggableE2 = babelHelpers.slicedToArray(_this$splitDraggableE, 2),
	          nextElements = _this$splitDraggableE2[1];

	      var stop = false;
	      return nextElements.reduce(function (acc, element) {
	        if (!stop) {
	          var currentDepth = _this22.getElementDepth(element);

	          if (currentDepth > parentDepth) {
	            return [].concat(babelHelpers.toConsumableArray(acc), [element]);
	          }

	          stop = true;
	        }

	        return acc;
	      }, []);
	    }
	  }, {
	    key: "getPreviousElement",
	    value: function getPreviousElement(element) {
	      var elementRect = element.getBoundingClientRect();
	      var container = this.getContainerByChild(element);

	      var _this$splitDraggableE3 = this.splitDraggableElementsListByPoint(container, {
	        x: elementRect.left,
	        y: elementRect.top
	      }),
	          _this$splitDraggableE4 = babelHelpers.slicedToArray(_this$splitDraggableE3, 1),
	          prevElements = _this$splitDraggableE4[0];

	      if (main_core.Type.isArrayFilled(prevElements)) {
	        return prevElements.pop();
	      }

	      return null;
	    }
	  }, {
	    key: "onDragStart",
	    value: function onDragStart(event) {
	      var _event$data = event.data,
	          originalSource = _event$data.originalSource,
	          sourceContainer = _event$data.sourceContainer,
	          clientX = _event$data.clientX,
	          clientY = _event$data.clientY;
	      var source = this.getDraggableElementByChild(originalSource);
	      var dragBeforeStartEvent = new DragBeforeStartEvent({
	        clientX: clientX,
	        clientY: clientY,
	        source: source,
	        sourceContainer: sourceContainer,
	        originalSource: originalSource
	      });
	      this.emit('beforeStart', dragBeforeStartEvent);

	      if (dragBeforeStartEvent.isDefaultPrevented()) {
	        event.preventDefault();
	        return;
	      }

	      this.setSource(source);
	      var sourceDepth = this.getElementDepth(source);
	      var sourceRect = this.getSourceClientRect();
	      var pointerOffsetX = clientX - sourceRect.left;
	      var pointerOffsetY = clientY - sourceRect.top;

	      var _this$getOptions8 = this.getOptions(),
	          type = _this$getOptions8.type;

	      var draggable = source;

	      if (type !== Draggable.HEADLESS) {
	        var clone = main_core.Runtime.clone(source);
	        main_core.Dom.style(clone, 'margin', 0);
	        draggable = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), clone);
	        main_core.Dom.style(draggable, {
	          width: "".concat(sourceRect.width, "px"),
	          height: "".concat(sourceRect.height, "px"),
	          top: "".concat(clientY - pointerOffsetY + this.getOptions().offset.y, "px"),
	          left: "".concat(clientX - pointerOffsetX + this.getOptions().offset.x, "px")
	        });
	        main_core.Dom.addClass(draggable, 'ui-draggable--draggable');
	        this.pushDraggableElementToContainer(draggable, sourceContainer);

	        if (this.isDepthEditorEnabled()) {
	          var children = this.getChildren(source);
	          this.childrenElements = children;

	          if (children.length > 0) {
	            main_core.Dom.append(main_core.Runtime.clone(clone), draggable);
	            children.forEach(function (element) {
	              main_core.Dom.style(element, 'display', 'none');
	            });
	          }
	        }
	      }

	      var dropPreview = this.getDropPreview();

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
	      main_core.Dom.addClass(this.getDocument().body, "ui-draggable--type-".concat(this.getOptions().type));
	      var sourceIndex = this.getElementIndex(source);
	      this.dragStartEvent = new DragStartEvent({
	        clientX: clientX,
	        clientY: clientY,
	        pointerOffsetX: pointerOffsetX,
	        pointerOffsetY: pointerOffsetY,
	        draggable: draggable,
	        dropPreview: dropPreview,
	        source: source,
	        sourceIndex: sourceIndex,
	        sourceContainer: sourceContainer,
	        sourceDepth: sourceDepth,
	        originalSource: originalSource
	      });
	      this.emit('start', this.dragStartEvent);

	      if (this.dragStartEvent.isDefaultPrevented()) {
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "onDragMove",
	    value: function onDragMove(event) {
	      var _this23 = this;

	      if (!this.isDragging()) {
	        return;
	      }

	      var _event$data2 = event.data,
	          clientX = _event$data2.clientX,
	          clientY = _event$data2.clientY,
	          sourceContainer = _event$data2.sourceContainer,
	          originalSource = _event$data2.originalSource;
	      var _this$dragStartEvent$ = this.dragStartEvent.data,
	          startClientX = _this$dragStartEvent$.clientX,
	          startClientY = _this$dragStartEvent$.clientY,
	          pointerOffsetX = _this$dragStartEvent$.pointerOffsetX,
	          pointerOffsetY = _this$dragStartEvent$.pointerOffsetY,
	          source = _this$dragStartEvent$.source,
	          sourceIndex = _this$dragStartEvent$.sourceIndex,
	          draggable = _this$dragStartEvent$.draggable,
	          dropPreview = _this$dragStartEvent$.dropPreview;
	      var offsetX = clientX - startClientX;
	      var offsetY = clientY - startClientY;
	      var dragMoveEvent = new DragMoveEvent({
	        clientX: clientX,
	        clientY: clientY,
	        offsetX: offsetX,
	        offsetY: offsetY,
	        pointerOffsetX: pointerOffsetX,
	        pointerOffsetY: pointerOffsetY,
	        draggable: draggable,
	        dropPreview: dropPreview,
	        source: source,
	        sourceIndex: sourceIndex,
	        sourceContainer: sourceContainer,
	        originalSource: originalSource
	      });
	      this.emit('move', dragMoveEvent);

	      if (dragMoveEvent.isDefaultPrevented()) {
	        event.preventDefault();
	      }

	      if (!main_core.Type.isDomNode(event.data.over)) {
	        return;
	      }

	      var originalOver = event.data.over;
	      var over = this.getDraggableElementByChild(originalOver);
	      var overContainer = this.getContainerByChild(originalOver);

	      var _this$getOptions9 = this.getOptions(),
	          type = _this$getOptions9.type;

	      if (type !== Draggable.HEADLESS) {
	        main_core.Dom.style(draggable, {
	          top: "".concat(clientY - pointerOffsetY + this.getOptions().offset.y, "px"),
	          left: "".concat(clientX - pointerOffsetX + this.getOptions().offset.x, "px")
	        });

	        if (overContainer && overContainer.contains(source) && !this.stopMove) {
	          var sortOffsetY = this.getSortOffsetY();
	          var draggableElements = this.getDraggableElementsOfContainer(overContainer);
	          var localSourceIndex = draggableElements.indexOf(source);
	          draggableElements.forEach(function (element, index) {
	            if (element !== source) {
	              var currentTransform = element.style.transform;

	              var elementMiddlePoint = _this23.getElementMiddlePoint(element);

	              if (elementMiddlePoint.y === 0) {
	                return;
	              }

	              if (index > localSourceIndex && clientY > elementMiddlePoint.y && currentTransform !== "translate3d(0px, ".concat(-sortOffsetY, "px, 0px)")) {
	                _this23.adjustDropPreview(element, {
	                  y: true
	                });

	                _this23.move(element, {
	                  y: -sortOffsetY
	                });

	                _this23.insertType = 'after';
	                _this23.insertElement = element;
	              }

	              if (index < localSourceIndex && clientY < elementMiddlePoint.y && currentTransform !== "translate3d(0px, ".concat(sortOffsetY, "px, 0px)")) {
	                _this23.adjustDropPreview(element, {
	                  y: true
	                });

	                _this23.move(element, {
	                  y: sortOffsetY
	                });

	                _this23.insertType = 'before';
	                _this23.insertElement = element;
	              }

	              if ((index < localSourceIndex && clientY > elementMiddlePoint.y || index > localSourceIndex && clientY < elementMiddlePoint.y) && currentTransform !== 'translate3d(0px, 0px, 0px)' && currentTransform !== '') {
	                _this23.adjustDropPreview(element, {
	                  y: true
	                });

	                _this23.move(element, {
	                  y: 0
	                });

	                _this23.insertElement = element;

	                if (index < localSourceIndex && clientY > elementMiddlePoint.y) {
	                  _this23.insertType = 'after';
	                }

	                if (index > localSourceIndex && clientY < elementMiddlePoint.y) {
	                  _this23.insertType = 'before';
	                }
	              }
	            }
	          });
	        }
	      }

	      if (this.isDepthEditorEnabled()) {
	        var currentDepth = this.calcDepthByOffset(offsetX);
	        var parentElement = this.getPreviousElement(dropPreview);

	        if (parentElement) {
	          var prevDepth = this.getElementDepth(parentElement);
	          var minDepth = 0;
	          var maxDepth = Math.max(minDepth, prevDepth + 1);
	          currentDepth = Math.max(minDepth, Math.min(currentDepth, maxDepth));
	        } else {
	          currentDepth = 0;
	        }

	        this.setDropPreviewDepth(currentDepth);
	        this.currentDepth = currentDepth;
	      }

	      if (main_core.Type.isDomNode(over) && source !== over) {
	        var dragOverEvent = new DragOverEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	          over: over,
	          originalOver: originalOver,
	          overContainer: overContainer
	        }));
	        this.emit('over', dragOverEvent);

	        if (!dragOverEvent.isDefaultPrevented()) {
	          main_core.Dom.addClass(over, 'ui-draggable--over');
	        }

	        if (over !== this.lastOver) {
	          var dragEnterEvent = new DragEnterEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	            enter: over,
	            enterContainer: overContainer
	          }));
	          this.emit('enter', dragEnterEvent);
	        }
	      }

	      this.lastOver = this.lastOver || over;

	      if (!over || over !== this.lastOver) {
	        if (this.lastOver) {
	          var outContainer = this.getContainerByChild(this.lastOver);
	          var dragOutEvent = new DragOutEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent), {}, {
	            out: this.lastOver,
	            outContainer: outContainer
	          }));
	          this.emit('out', dragOutEvent);
	          main_core.Dom.removeClass(this.lastOver, 'ui-draggable--over');
	        }

	        this.lastOver = over;
	      }

	      var sourceOver = this.getDocument().elementFromPoint(clientX, clientY);
	      var dropzoneOver = this.getDropzoneByChild(sourceOver);

	      if (dropzoneOver) {
	        var dragOverDropzoneEvent = new DragOverDropzoneEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	          dropzone: dropzoneOver
	        }));
	        this.emit('dropzone:over', dragOverDropzoneEvent);

	        if (dropzoneOver !== this.lastOverDropzone) {
	          var dragEnterDropzoneEvent = new DragEnterDropzoneEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	            dropzone: dropzoneOver
	          }));
	          this.emit('dropzone:enter', dragEnterDropzoneEvent);
	        }
	      }

	      this.lastOverDropzone = this.lastOverDropzone || dropzoneOver;

	      if (dropzoneOver !== this.lastOverDropzone) {
	        var dragOutDropzoneEvent = new DragOutDropzoneEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	          dropzone: this.lastOverDropzone
	        }));
	        this.emit('dropzone:out', dragOutDropzoneEvent);
	        this.lastOverDropzone = dropzoneOver;
	      }

	      if (overContainer) {
	        var dragOverContainerEvent = new DragOverContainerEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	          over: overContainer
	        }));
	        this.emit('container:over', dragOverContainerEvent);

	        if (overContainer !== this.lastOverContainer) {
	          var dragEnterContainerEvent = new DragEnterContainerEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	            enter: overContainer
	          }));
	          this.emit('container:enter', dragEnterContainerEvent);

	          if (!overContainer.contains(source)) {
	            var lastContainer = this.getContainerByChild(source);

	            var _this$splitDraggableE5 = this.splitDraggableElementsListByPoint(overContainer, {
	              x: clientX,
	              y: clientY
	            }),
	                _this$splitDraggableE6 = babelHelpers.slicedToArray(_this$splitDraggableE5, 2),
	                beforeElements = _this$splitDraggableE6[0],
	                afterElements = _this$splitDraggableE6[1];

	            if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	              this.stopMove = true;
	              setTimeout(function () {
	                _this23.stopMove = false;
	              }, 300);
	              this.pushDraggableElementToContainer(this.getDropPreview(), overContainer);
	            }

	            if (type !== Draggable.HEADLESS) {
	              this.pushDraggableElementToContainer(source, overContainer);
	            }

	            if (main_core.Type.isArrayFilled(beforeElements)) {
	              var lastElement = beforeElements[beforeElements.length - 1];

	              if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	                this.showDropPreviewAfter(lastElement);
	              }

	              this.insertType = 'after';
	              this.insertElement = lastElement;
	            } else if (main_core.Type.isArrayFilled(afterElements)) {
	              var _afterElements = babelHelpers.slicedToArray(afterElements, 1),
	                  firstElement = _afterElements[0];

	              if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE) {
	                this.adjustDropPreview(afterElements);
	              }

	              this.insertType = 'before';
	              this.insertElement = firstElement;
	            }

	            this.resetDraggableElementsTransition(lastContainer);
	            this.resetDraggableElementsPosition(lastContainer);

	            if (type !== Draggable.HEADLESS) {
	              if (main_core.Type.isArrayFilled(afterElements)) {
	                var _sortOffsetY = this.getSortOffsetY();

	                afterElements.forEach(function (element) {
	                  _this23.move(element, {
	                    y: _sortOffsetY
	                  });
	                });
	              }
	            }
	          }
	        }
	      }

	      this.lastOverContainer = this.lastOverContainer || overContainer;

	      if (overContainer !== this.lastOverContainer) {
	        var dragOutContainerEvent = new DragOutContainerEvent(_objectSpread$1(_objectSpread$1({}, dragMoveEvent.data), {}, {
	          out: this.lastOverContainer
	        }));
	        this.emit('container:out', dragOutContainerEvent);
	        this.lastOverContainer = overContainer;
	      }
	    }
	  }, {
	    key: "onDragEnd",
	    value: function onDragEnd(event) {
	      var _this24 = this;

	      var dragEndEvent = new DragEndEvent(_objectSpread$1(_objectSpread$1({}, this.dragStartEvent.data), {}, {
	        clientX: event.data.clientX,
	        clientY: event.data.clientY,
	        end: this.lastOver,
	        endContainer: this.lastOverContainer
	      }));
	      var _this$dragStartEvent$2 = this.dragStartEvent.data,
	          source = _this$dragStartEvent$2.source,
	          draggable = _this$dragStartEvent$2.draggable;

	      if (this.getOptions().type !== Draggable.HEADLESS) {
	        main_core.Dom.remove(draggable);
	      }

	      main_core.Dom.removeClass(source, 'ui-draggable--source');
	      this.getDraggableElements().forEach(function (element) {
	        main_core.Dom.removeClass(element, 'ui-draggable--draggable');
	        main_core.Dom.removeClass(element, 'ui-draggable--over');
	      });
	      main_core.Dom.remove(this.getDropPreview());
	      this.resetDraggableElementsPosition();
	      this.resetDraggableElementsTransition();

	      if (this.getOptions().type !== Draggable.HEADLESS) {
	        if (main_core.Type.isString(this.insertType)) {
	          if (this.insertType === 'after') {
	            main_core.Dom.insertAfter(source, this.insertElement);
	          } else {
	            main_core.Dom.insertBefore(source, this.insertElement);
	          }
	        }
	      }

	      if (this.isDepthEditorEnabled()) {
	        var startSourceDepth = this.getStartSourceDepth();

	        var depthDiff = function () {
	          if (main_core.Type.isNumber(_this24.currentDepth)) {
	            return _this24.currentDepth - startSourceDepth;
	          }

	          return 0;
	        }();

	        var lastElement = source;
	        this.childrenElements.forEach(function (element) {
	          var currentDepth = _this24.getElementDepth(element);

	          _this24.setElementDepth(element, currentDepth + depthDiff);

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
	      main_core.Dom.removeClass(this.getDocument().body, "ui-draggable--type-".concat(this.getOptions().type));
	      this.emit('end', dragEndEvent); // todo test in default
	    }
	  }, {
	    key: "onDragDrop",
	    value: function onDragDrop(event) {
	      var dragDropEvent = new DragDropEvent(_objectSpread$1(_objectSpread$1({}, this.dragStartEvent.data), {}, {
	        clientX: event.data.clientX,
	        clientY: event.data.clientY,
	        dropzone: event.data.dropzone
	      }));
	      this.emit('drop', dragDropEvent);
	    }
	  }]);
	  return Draggable;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Draggable, "MOVE", 'move');
	babelHelpers.defineProperty(Draggable, "CLONE", 'clone');
	babelHelpers.defineProperty(Draggable, "DROP_PREVIEW", 'drop-preview');
	babelHelpers.defineProperty(Draggable, "HEADLESS", 'headless');

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
