/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _window = /*#__PURE__*/new WeakMap();
	var _changeNavigation = /*#__PURE__*/new WeakSet();
	var _changeBreadcrumbsNavigation = /*#__PURE__*/new WeakSet();
	var Disk = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Disk, _EventEmitter);
	  function Disk(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Disk);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Disk).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _changeBreadcrumbsNavigation);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _changeNavigation);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _window, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Disk');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _window, params.window);
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _window).BX.Event.EventEmitter.subscribe('Disk.TileItem.Item:onItemDblClick', _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _changeNavigation, _changeNavigation2).bind(babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _window).BX.Event.EventEmitter.subscribe('Disk.TileItem.Item:onItemEnter', _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _changeNavigation, _changeNavigation2).bind(babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _window).BX.Event.EventEmitter.subscribe('Disk.Breadcrumbs:onClickBreadcrumb', _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _changeBreadcrumbsNavigation, _changeBreadcrumbsNavigation2).bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  return Disk;
	}(main_core_events.EventEmitter);
	function _changeNavigation2(baseEvent) {
	  var _baseEvent$getCompatD = baseEvent.getCompatData(),
	    _baseEvent$getCompatD2 = babelHelpers.slicedToArray(_baseEvent$getCompatD, 1),
	    item = _baseEvent$getCompatD2[0];
	  if (item.isFolder) {
	    this.emit('changePage', item.item.titleLink.href);
	  }
	}
	function _changeBreadcrumbsNavigation2(baseEvent) {
	  var _baseEvent$getCompatD3 = baseEvent.getCompatData(),
	    _baseEvent$getCompatD4 = babelHelpers.slicedToArray(_baseEvent$getCompatD3, 1),
	    breadcrumbLink = _baseEvent$getCompatD4[0];
	  this.emit('changePage', breadcrumbLink.href);
	}

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _renderSvg = /*#__PURE__*/new WeakSet();
	var DefaultLoader = /*#__PURE__*/function () {
	  function DefaultLoader() {
	    babelHelpers.classCallCheck(this, DefaultLoader);
	    _classPrivateMethodInitSpec$1(this, _renderSvg);
	  }
	  babelHelpers.createClass(DefaultLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sn-spaces__content-loader-default-container\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), _classPrivateMethodGet$1(this, _renderSvg, _renderSvg2).call(this));
	    }
	  }]);
	  return DefaultLoader;
	}();
	function _renderSvg2() {
	  return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<svg class=\"sn-spaces__content-loader-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t<circle\n\t\t\t\t\tclass=\"sn-spaces__content-loader-path\"\n\t\t\t\t\tcx=\"50\"\n\t\t\t\t\tcy=\"50\"\n\t\t\t\t\tr=\"20\"\n\t\t\t\t\tfill=\"none\"\n\t\t\t\t\tstroke-miterlimit=\"10\"\n\t\t\t\t/>\n\t\t\t</svg>\n\t\t"])));
	}

	var _templateObject$1;
	var CalendarBaseLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(CalendarBaseLoader, _DefaultLoader);
	  function CalendarBaseLoader() {
	    babelHelpers.classCallCheck(this, CalendarBaseLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CalendarBaseLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(CalendarBaseLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-calendar-base\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return CalendarBaseLoader;
	}(DefaultLoader);

	var _templateObject$2;
	var CalendarScheduleLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(CalendarScheduleLoader, _DefaultLoader);
	  function CalendarScheduleLoader() {
	    babelHelpers.classCallCheck(this, CalendarScheduleLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CalendarScheduleLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(CalendarScheduleLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-calendar-schedule\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return CalendarScheduleLoader;
	}(DefaultLoader);

	var _templateObject$3;
	var DiscussionsLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(DiscussionsLoader, _DefaultLoader);
	  function DiscussionsLoader() {
	    babelHelpers.classCallCheck(this, DiscussionsLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DiscussionsLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(DiscussionsLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-discussions\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return DiscussionsLoader;
	}(DefaultLoader);

	var _templateObject$4;
	var FilesListLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(FilesListLoader, _DefaultLoader);
	  function FilesListLoader() {
	    babelHelpers.classCallCheck(this, FilesListLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FilesListLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(FilesListLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-files-list\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return FilesListLoader;
	}(DefaultLoader);

	var _templateObject$5;
	var FilesTileLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(FilesTileLoader, _DefaultLoader);
	  function FilesTileLoader() {
	    babelHelpers.classCallCheck(this, FilesTileLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FilesTileLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(FilesTileLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-files-tile\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return FilesTileLoader;
	}(DefaultLoader);

	var _templateObject$6;
	var FilesBigTileLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(FilesBigTileLoader, _DefaultLoader);
	  function FilesBigTileLoader() {
	    babelHelpers.classCallCheck(this, FilesBigTileLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FilesBigTileLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(FilesBigTileLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-files-big-tile\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return FilesBigTileLoader;
	}(DefaultLoader);

	var _templateObject$7;
	var TasksCalendarLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksCalendarLoader, _DefaultLoader);
	  function TasksCalendarLoader() {
	    babelHelpers.classCallCheck(this, TasksCalendarLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksCalendarLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(TasksCalendarLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-tasks-calendar\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return TasksCalendarLoader;
	}(DefaultLoader);

	var _templateObject$8;
	var TasksGanttLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksGanttLoader, _DefaultLoader);
	  function TasksGanttLoader() {
	    babelHelpers.classCallCheck(this, TasksGanttLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksGanttLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(TasksGanttLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-tasks-gantt\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return TasksGanttLoader;
	}(DefaultLoader);

	var _templateObject$9;
	var TasksKanbanLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksKanbanLoader, _DefaultLoader);
	  function TasksKanbanLoader() {
	    babelHelpers.classCallCheck(this, TasksKanbanLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksKanbanLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(TasksKanbanLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-tasks-kanban\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return TasksKanbanLoader;
	}(DefaultLoader);

	var _templateObject$a;
	var TasksListLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksListLoader, _DefaultLoader);
	  function TasksListLoader() {
	    babelHelpers.classCallCheck(this, TasksListLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksListLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(TasksListLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-tasks-list\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return TasksListLoader;
	}(DefaultLoader);

	var _templateObject$b;
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _pageView = /*#__PURE__*/new WeakMap();
	var TasksScrumPlanLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksScrumPlanLoader, _DefaultLoader);
	  function TasksScrumPlanLoader(pageView) {
	    var _this;
	    babelHelpers.classCallCheck(this, TasksScrumPlanLoader);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksScrumPlanLoader).call(this, pageView));
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _pageView, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _pageView, pageView);
	    return _this;
	  }
	  babelHelpers.createClass(TasksScrumPlanLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$b || (_templateObject$b = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-", "\"\n\t\t\t></div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _pageView));
	    }
	  }]);
	  return TasksScrumPlanLoader;
	}(DefaultLoader);

	var _templateObject$c;
	var TasksTimelineLoader = /*#__PURE__*/function (_DefaultLoader) {
	  babelHelpers.inherits(TasksTimelineLoader, _DefaultLoader);
	  function TasksTimelineLoader() {
	    babelHelpers.classCallCheck(this, TasksTimelineLoader);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TasksTimelineLoader).apply(this, arguments));
	  }
	  babelHelpers.createClass(TasksTimelineLoader, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$c || (_templateObject$c = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"sn-spaces__content-loader-container sn-spaces__content-loader-tasks-timeline\"\n\t\t\t></div>\n\t\t"])));
	    }
	  }]);
	  return TasksTimelineLoader;
	}(DefaultLoader);

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _pageUrl = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _container = /*#__PURE__*/new WeakMap();
	var _loader = /*#__PURE__*/new WeakMap();
	var _initLoader = /*#__PURE__*/new WeakSet();
	var Loader = /*#__PURE__*/function () {
	  function Loader(params) {
	    babelHelpers.classCallCheck(this, Loader);
	    _classPrivateMethodInitSpec$2(this, _initLoader);
	    _classPrivateFieldInitSpec$2(this, _pageUrl, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _node, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$2(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _pageUrl, params.pageUrl);
	    this.setLoader(params.pageView);
	  }
	  babelHelpers.createClass(Loader, [{
	    key: "show",
	    value: function show(container) {
	      if (babelHelpers.classPrivateFieldGet(this, _node) !== null) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _container, container);
	      main_core.Dom.addClass(container, '--visible');
	      babelHelpers.classPrivateFieldSet(this, _node, babelHelpers.classPrivateFieldGet(this, _loader).render());
	      main_core.Dom.prepend(babelHelpers.classPrivateFieldGet(this, _node), container);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _container), '--visible');
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _node));
	      babelHelpers.classPrivateFieldSet(this, _node, null);
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return babelHelpers.classPrivateFieldGet(this, _node) !== null;
	    }
	  }, {
	    key: "setLoader",
	    value: function setLoader(pageView) {
	      babelHelpers.classPrivateFieldSet(this, _loader, _classPrivateMethodGet$2(this, _initLoader, _initLoader2).call(this, pageView));
	    }
	  }]);
	  return Loader;
	}();
	function _initLoader2(pageView) {
	  switch (pageView) {
	    case 'discussions':
	      return new DiscussionsLoader();
	    case 'tasks-list':
	      return new TasksListLoader();
	    case 'tasks-timeline':
	      return new TasksTimelineLoader();
	    case 'tasks-plan':
	    case 'tasks-kanban':
	      return new TasksKanbanLoader();
	    case 'tasks-calendar':
	      return new TasksCalendarLoader();
	    case 'tasks-gantt':
	      return new TasksGanttLoader();
	    case 'tasks-scrum-plan-sprint':
	    case 'tasks-scrum-plan-backlog':
	      return new TasksScrumPlanLoader(pageView);
	    case 'tasks-scrum-active':
	    case 'tasks-scrum-complete':
	      return new TasksKanbanLoader();
	    case 'calendar-base':
	      return new CalendarBaseLoader();
	    case 'calendar-schedule':
	      return new CalendarScheduleLoader();
	    case 'files-list':
	      return new FilesListLoader();
	    case 'files-tile-m':
	      return new FilesTileLoader();
	    case 'files-tile-xl':
	      return new FilesBigTileLoader();
	    default:
	      return new DefaultLoader();
	  }
	}

	var _templateObject$d;
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _pageId = /*#__PURE__*/new WeakMap();
	var _pageView$1 = /*#__PURE__*/new WeakMap();
	var _id = /*#__PURE__*/new WeakMap();
	var _src = /*#__PURE__*/new WeakMap();
	var _className = /*#__PURE__*/new WeakMap();
	var _loader$1 = /*#__PURE__*/new WeakMap();
	var _sidePanelManager = /*#__PURE__*/new WeakMap();
	var _container$1 = /*#__PURE__*/new WeakMap();
	var _node$1 = /*#__PURE__*/new WeakMap();
	var _window$1 = /*#__PURE__*/new WeakMap();
	var _render = /*#__PURE__*/new WeakSet();
	var _setSrc = /*#__PURE__*/new WeakSet();
	var _load = /*#__PURE__*/new WeakSet();
	var _initHacks = /*#__PURE__*/new WeakSet();
	var _initObserver = /*#__PURE__*/new WeakSet();
	var _changeLinksTargets = /*#__PURE__*/new WeakSet();
	var Frame = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Frame, _EventEmitter);
	  function Frame(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Frame);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Frame).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _changeLinksTargets);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _initObserver);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _initHacks);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _load);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _setSrc);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _render);
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _pageId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _pageView$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _src, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _className, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _loader$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _sidePanelManager, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _container$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _node$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _window$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Socialnetwork.Spaces.Frame');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _pageId, params.pageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _pageView$1, params.pageView);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _id, params.id);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _className, params.className);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loader$1, new Loader({
	      pageView: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _pageView$1),
	      pageUrl: params.src
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sidePanelManager, BX.SidePanel.Instance);
	    _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _setSrc, _setSrc2).call(babelHelpers.assertThisInitialized(_this), params.src);
	    _this.updateSrcDebounced = main_core.Runtime.debounce(_this.updateSrc.bind(babelHelpers.assertThisInitialized(_this)), 1000);
	    return _this;
	  }
	  babelHelpers.createClass(Frame, [{
	    key: "renderTo",
	    value: function renderTo(container) {
	      babelHelpers.classPrivateFieldSet(this, _container$1, container);
	      babelHelpers.classPrivateFieldGet(this, _loader$1).show(babelHelpers.classPrivateFieldGet(this, _container$1));
	      main_core.Dom.append(_classPrivateMethodGet$3(this, _render, _render2).call(this), container);
	    }
	  }, {
	    key: "reload",
	    value: function reload(pageView, src) {
	      babelHelpers.classPrivateFieldGet(this, _loader$1).setLoader(pageView);
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--hidden');
	      babelHelpers.classPrivateFieldGet(this, _loader$1).show(babelHelpers.classPrivateFieldGet(this, _container$1));
	      if (src) {
	        _classPrivateMethodGet$3(this, _setSrc, _setSrc2).call(this, src);
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _loader$1).isShown()) {
	        this.updateSrcDebounced();
	      } else {
	        this.updateSrc();
	      }
	    }
	  }, {
	    key: "updateSrc",
	    value: function updateSrc() {
	      babelHelpers.classPrivateFieldGet(this, _node$1).src = babelHelpers.classPrivateFieldGet(this, _src);
	    }
	  }, {
	    key: "getFrameNode",
	    value: function getFrameNode() {
	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }, {
	    key: "getWindow",
	    value: function getWindow() {
	      return babelHelpers.classPrivateFieldGet(this, _window$1);
	    }
	  }]);
	  return Frame;
	}(main_core_events.EventEmitter);
	function _render2() {
	  babelHelpers.classPrivateFieldSet(this, _node$1, main_core.Tag.render(_templateObject$d || (_templateObject$d = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<iframe\n\t\t\t\tid=\"", "\"\n\t\t\t\tclass=\"", "\"\n\t\t\t\tsrc=\"", "\"\n\t\t\t\tonload=\"", "\"\n\t\t\t>\n\t\t\t</iframe>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _id), "".concat(babelHelpers.classPrivateFieldGet(this, _className), " --hidden"), babelHelpers.classPrivateFieldGet(this, _src), _classPrivateMethodGet$3(this, _load, _load2).bind(this)));
	  return babelHelpers.classPrivateFieldGet(this, _node$1);
	}
	function _setSrc2(src) {
	  var uri = new main_core.Uri(src);
	  uri.setQueryParams({
	    IFRAME: 'Y'
	  });
	  babelHelpers.classPrivateFieldSet(this, _src, uri.toString());
	}
	function _load2(event) {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldSet(this, _window$1, event.target.contentWindow);
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _window$1), 'unload', function () {
	    _this2.emit('unload', {
	      src: babelHelpers.classPrivateFieldGet(_this2, _src),
	      window: babelHelpers.classPrivateFieldGet(_this2, _window$1)
	    });
	  });
	  var url = new URL(babelHelpers.classPrivateFieldGet(this, _src), location);
	  url.searchParams["delete"]('empty-state');
	  babelHelpers.classPrivateFieldSet(this, _src, url.toString());
	  _classPrivateMethodGet$3(this, _initHacks, _initHacks2).call(this);
	  _classPrivateMethodGet$3(this, _changeLinksTargets, _changeLinksTargets2).call(this, babelHelpers.classPrivateFieldGet(this, _window$1).document.body);
	  _classPrivateMethodGet$3(this, _initObserver, _initObserver2).call(this);
	  babelHelpers.classPrivateFieldGet(this, _loader$1).hide();
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _node$1), '--hidden');
	  this.emit('load', {
	    src: babelHelpers.classPrivateFieldGet(this, _src),
	    window: babelHelpers.classPrivateFieldGet(this, _window$1)
	  });
	}
	function _initHacks2() {
	  babelHelpers.classPrivateFieldGet(this, _sidePanelManager).registerAnchorListener(babelHelpers.classPrivateFieldGet(this, _window$1).document);
	}
	function _initObserver2() {
	  var _this3 = this;
	  if (main_core.Type.isUndefined(MutationObserver)) {
	    return;
	  }
	  var observer = new MutationObserver(function (mutations) {
	    mutations.forEach(function (mutation) {
	      for (var i = 0; i < mutation.addedNodes.length; ++i) {
	        _classPrivateMethodGet$3(_this3, _changeLinksTargets, _changeLinksTargets2).call(_this3, mutation.addedNodes.item(i));
	      }
	    });
	  });
	  observer.observe(babelHelpers.classPrivateFieldGet(this, _window$1).document.body, {
	    childList: true,
	    subtree: true
	  });
	}
	function _changeLinksTargets2(context) {
	  if (!context) {
	    return;
	  }
	  var list = [];
	  if (context.tagName === 'A') {
	    list = [context];
	  } else if (main_core.Type.isElementNode(context)) {
	    list = babelHelpers.toConsumableArray(context.querySelectorAll('a'));
	  }
	  list.filter(function (a) {
	    return !a.target;
	  })
	  // eslint-disable-next-line no-return-assign,no-param-reassign
	  .forEach(function (a) {
	    return a.target = '_top';
	  });
	}

	var _templateObject$e;
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popupId = /*#__PURE__*/new WeakMap();
	var _workpiece = /*#__PURE__*/new WeakMap();
	var _containerWithoutOverlay = /*#__PURE__*/new WeakMap();
	var _overlay = /*#__PURE__*/new WeakMap();
	var _leftOverlay = /*#__PURE__*/new WeakMap();
	var _topOverlay = /*#__PURE__*/new WeakMap();
	var _rightOverlay = /*#__PURE__*/new WeakMap();
	var _createOverlay = /*#__PURE__*/new WeakSet();
	var _createPartOfOverlay = /*#__PURE__*/new WeakSet();
	var _resizeWindow = /*#__PURE__*/new WeakSet();
	var _getSizes = /*#__PURE__*/new WeakSet();
	var _getOverlayParams = /*#__PURE__*/new WeakSet();
	var _resizeOverlay = /*#__PURE__*/new WeakSet();
	var Overlay = /*#__PURE__*/function () {
	  function Overlay(_params) {
	    babelHelpers.classCallCheck(this, Overlay);
	    _classPrivateMethodInitSpec$4(this, _resizeOverlay);
	    _classPrivateMethodInitSpec$4(this, _getOverlayParams);
	    _classPrivateMethodInitSpec$4(this, _getSizes);
	    _classPrivateMethodInitSpec$4(this, _resizeWindow);
	    _classPrivateMethodInitSpec$4(this, _createPartOfOverlay);
	    _classPrivateMethodInitSpec$4(this, _createOverlay);
	    _classPrivateFieldInitSpec$4(this, _popupId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _workpiece, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _containerWithoutOverlay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _overlay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _leftOverlay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _topOverlay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _rightOverlay, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _popupId, _params.popupId);
	    babelHelpers.classPrivateFieldSet(this, _workpiece, _params.workpiece);
	    babelHelpers.classPrivateFieldSet(this, _containerWithoutOverlay, _params.containerWithoutOverlay);
	    _classPrivateMethodGet$4(this, _createOverlay, _createOverlay2).call(this);
	    main_core.Event.bind(window, 'resize', _classPrivateMethodGet$4(this, _resizeWindow, _resizeWindow2).bind(this));
	  }
	  babelHelpers.createClass(Overlay, [{
	    key: "show",
	    value: function show() {
	      main_core.Dom.style(babelHelpers.classPrivateFieldGet(this, _overlay), 'display', 'block');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.style(babelHelpers.classPrivateFieldGet(this, _overlay), 'display', 'none');
	    }
	  }, {
	    key: "append",
	    value: function append() {
	      main_core.Dom.append(babelHelpers.classPrivateFieldGet(this, _overlay), document.body);
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _overlay));
	    }
	  }]);
	  return Overlay;
	}();
	function _createOverlay2() {
	  var params = _classPrivateMethodGet$4(this, _getOverlayParams, _getOverlayParams2).call(this);
	  babelHelpers.classPrivateFieldSet(this, _leftOverlay, _classPrivateMethodGet$4(this, _createPartOfOverlay, _createPartOfOverlay2).call(this, params.left.width, params.left.height));
	  babelHelpers.classPrivateFieldSet(this, _topOverlay, _classPrivateMethodGet$4(this, _createPartOfOverlay, _createPartOfOverlay2).call(this, params.top.width, params.top.height, params.top.left));
	  babelHelpers.classPrivateFieldSet(this, _rightOverlay, _classPrivateMethodGet$4(this, _createPartOfOverlay, _createPartOfOverlay2).call(this, params.right.width, params.right.height, params.right.left));
	  babelHelpers.classPrivateFieldSet(this, _overlay, main_core.Tag.render(_templateObject$e || (_templateObject$e = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _leftOverlay), babelHelpers.classPrivateFieldGet(this, _topOverlay), babelHelpers.classPrivateFieldGet(this, _rightOverlay)));
	}
	function _createPartOfOverlay2(width, height) {
	  var left = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	  var overlay = babelHelpers.classPrivateFieldGet(this, _workpiece).cloneNode(true);
	  _classPrivateMethodGet$4(this, _resizeOverlay, _resizeOverlay2).call(this, overlay, width, height, left);
	  return overlay;
	}
	function _resizeWindow2() {
	  var params = _classPrivateMethodGet$4(this, _getOverlayParams, _getOverlayParams2).call(this);
	  _classPrivateMethodGet$4(this, _resizeOverlay, _resizeOverlay2).call(this, babelHelpers.classPrivateFieldGet(this, _leftOverlay), params.left.width, params.left.height);
	  _classPrivateMethodGet$4(this, _resizeOverlay, _resizeOverlay2).call(this, babelHelpers.classPrivateFieldGet(this, _topOverlay), params.top.width, params.top.height, params.top.left);
	  _classPrivateMethodGet$4(this, _resizeOverlay, _resizeOverlay2).call(this, babelHelpers.classPrivateFieldGet(this, _rightOverlay), params.right.width, params.right.height, params.right.left);
	}
	function _getSizes2() {
	  var scrollWidth = document.documentElement.scrollWidth;
	  var scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
	  return [scrollWidth, scrollHeight];
	}
	function _getOverlayParams2() {
	  var _classPrivateMethodGe = _classPrivateMethodGet$4(this, _getSizes, _getSizes2).call(this),
	    _classPrivateMethodGe2 = babelHelpers.slicedToArray(_classPrivateMethodGe, 2),
	    scrollWidth = _classPrivateMethodGe2[0],
	    scrollHeight = _classPrivateMethodGe2[1];
	  var rect = main_core.Dom.getPosition(babelHelpers.classPrivateFieldGet(this, _containerWithoutOverlay));
	  return {
	    left: {
	      width: rect.left,
	      height: scrollHeight,
	      left: 0
	    },
	    top: {
	      width: rect.width,
	      height: rect.top,
	      left: rect.left
	    },
	    right: {
	      width: scrollWidth - rect.right,
	      height: scrollHeight,
	      left: rect.right
	    }
	  };
	}
	function _resizeOverlay2(overlay, width, height) {
	  var left = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
	  main_core.Dom.style(overlay, 'width', "".concat(width, "px"));
	  main_core.Dom.style(overlay, 'height', "".concat(height, "px"));
	  if (left) {
	    main_core.Dom.style(overlay, 'left', "".concat(left, "px"));
	  }
	}

	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$6(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _cache = /*#__PURE__*/new WeakMap();
	var _frame = /*#__PURE__*/new WeakMap();
	var _disk = /*#__PURE__*/new WeakMap();
	var _overlays = /*#__PURE__*/new WeakMap();
	var _popupIds = /*#__PURE__*/new WeakMap();
	var _showOverlays = /*#__PURE__*/new WeakSet();
	var _hideOverlays = /*#__PURE__*/new WeakSet();
	var _removeOverlays = /*#__PURE__*/new WeakSet();
	var _blockScroll = /*#__PURE__*/new WeakSet();
	var _unblockScroll = /*#__PURE__*/new WeakSet();
	var _initServices = /*#__PURE__*/new WeakSet();
	var _updateBaseTheme = /*#__PURE__*/new WeakSet();
	var _getParam = /*#__PURE__*/new WeakSet();
	var _changeBrowserHistory = /*#__PURE__*/new WeakSet();
	var _getPageType = /*#__PURE__*/new WeakSet();
	var _consoleError = /*#__PURE__*/new WeakSet();
	var Space = /*#__PURE__*/function () {
	  function Space(params) {
	    babelHelpers.classCallCheck(this, Space);
	    _classPrivateMethodInitSpec$5(this, _consoleError);
	    _classPrivateMethodInitSpec$5(this, _getPageType);
	    _classPrivateMethodInitSpec$5(this, _changeBrowserHistory);
	    _classPrivateMethodInitSpec$5(this, _getParam);
	    _classPrivateMethodInitSpec$5(this, _updateBaseTheme);
	    _classPrivateMethodInitSpec$5(this, _initServices);
	    _classPrivateMethodInitSpec$5(this, _unblockScroll);
	    _classPrivateMethodInitSpec$5(this, _blockScroll);
	    _classPrivateMethodInitSpec$5(this, _removeOverlays);
	    _classPrivateMethodInitSpec$5(this, _hideOverlays);
	    _classPrivateMethodInitSpec$5(this, _showOverlays);
	    _classPrivateFieldInitSpec$5(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$5(this, _frame, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(this, _disk, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(this, _overlays, {
	      writable: true,
	      value: new Map()
	    });
	    _classPrivateFieldInitSpec$5(this, _popupIds, {
	      writable: true,
	      value: new Set()
	    });
	    this.setParams(params);
	    _classPrivateMethodGet$5(this, _initServices, _initServices2).call(this);
	  }
	  babelHelpers.createClass(Space, [{
	    key: "setParams",
	    value: function setParams(params) {
	      babelHelpers.classPrivateFieldGet(this, _cache).set('params', params);
	    }
	  }, {
	    key: "renderContentTo",
	    value: function renderContentTo(container) {
	      if (!main_core.Type.isDomNode(container)) {
	        throw new Error('BX.Socialnetwork.Spaces.Space: HTMLElement for space not found');
	      }
	      babelHelpers.classPrivateFieldGet(this, _frame).renderTo(container);
	    }
	  }, {
	    key: "reloadPageContent",
	    value: function reloadPageContent(pageUrl) {
	      var _this = this;
	      var uri = new main_core.Uri(pageUrl);
	      var pageType = _classPrivateMethodGet$5(this, _getPageType, _getPageType2).call(this, uri);
	      var viewMode = '';
	      var viewSize = '';
	      var fState = '';
	      if (pageType === 'tasks') {
	        var _uri$getQueryParam;
	        fState = uri.getQueryParam('F_STATE');
	        viewMode = (_uri$getQueryParam = uri.getQueryParam('tab')) !== null && _uri$getQueryParam !== void 0 ? _uri$getQueryParam : '';
	      }
	      var isTrashMode = false;
	      if (pageType === 'files') {
	        var _uri$getQueryParam2, _uri$getQueryParam3;
	        if (uri.getPath().includes('trashcan')) {
	          isTrashMode = true;
	        }
	        viewMode = (_uri$getQueryParam2 = uri.getQueryParam('viewMode')) !== null && _uri$getQueryParam2 !== void 0 ? _uri$getQueryParam2 : '';
	        viewSize = (_uri$getQueryParam3 = uri.getQueryParam('viewSize')) !== null && _uri$getQueryParam3 !== void 0 ? _uri$getQueryParam3 : '';
	      }
	      main_core.ajax.runComponentAction('bitrix:socialnetwork.spaces', 'getPageView', {
	        mode: 'class',
	        data: {
	          pageType: pageType,
	          userId: _classPrivateMethodGet$5(this, _getParam, _getParam2).call(this, 'userId'),
	          groupId: _classPrivateMethodGet$5(this, _getParam, _getParam2).call(this, 'groupId'),
	          params: {
	            isTrashMode: isTrashMode,
	            viewMode: viewMode,
	            viewSize: viewSize
	          },
	          F_STATE: fState
	        }
	      }).then(function (response) {
	        babelHelpers.classPrivateFieldGet(_this, _frame).reload(response.data, pageUrl);
	      })["catch"](function (error) {
	        _classPrivateMethodGet$5(_this, _consoleError, _consoleError2).call(_this, 'getPageView', error);
	      });
	    }
	  }, {
	    key: "showOverlay",
	    value: function showOverlay(popupId, frameOverlay) {
	      _classPrivateMethodGet$5(this, _blockScroll, _blockScroll2).call(this, popupId);
	      var topOverlay = babelHelpers.classPrivateFieldGet(this, _overlays).has(popupId) ? babelHelpers.classPrivateFieldGet(this, _overlays).get(popupId) : new Overlay({
	        popupId: popupId,
	        workpiece: frameOverlay,
	        containerWithoutOverlay: babelHelpers.classPrivateFieldGet(this, _frame).getFrameNode()
	      });
	      babelHelpers.classPrivateFieldGet(this, _overlays).set(popupId, topOverlay);
	      topOverlay.append();
	    }
	  }, {
	    key: "hideOverlay",
	    value: function hideOverlay(popupId) {
	      if (babelHelpers.classPrivateFieldGet(this, _overlays).has(popupId)) {
	        babelHelpers.classPrivateFieldGet(this, _overlays).get(popupId).remove();
	      }
	      _classPrivateMethodGet$5(this, _unblockScroll, _unblockScroll2).call(this, popupId);
	    }
	  }]);
	  return Space;
	}();
	function _showOverlays2() {
	  babelHelpers.classPrivateFieldGet(this, _overlays).forEach(function (overlay) {
	    return overlay.show();
	  });
	}
	function _hideOverlays2() {
	  babelHelpers.classPrivateFieldGet(this, _overlays).forEach(function (overlay) {
	    return overlay.hide();
	  });
	}
	function _removeOverlays2() {
	  babelHelpers.classPrivateFieldGet(this, _overlays).forEach(function (overlay) {
	    return overlay.remove();
	  });
	}
	function _blockScroll2(popupId) {
	  babelHelpers.classPrivateFieldGet(this, _popupIds).add(popupId);
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _frame).getWindow().document.querySelector('.sn-spaces__wrapper'), '--scroll-disabled');
	}
	function _unblockScroll2(popupId) {
	  babelHelpers.classPrivateFieldGet(this, _popupIds)["delete"](popupId);
	  if (babelHelpers.classPrivateFieldGet(this, _popupIds).size === 0) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _frame).getWindow().document.querySelector('.sn-spaces__wrapper'), '--scroll-disabled');
	  }
	}
	function _initServices2() {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldSet(this, _frame, new Frame({
	    pageId: _classPrivateMethodGet$5(this, _getParam, _getParam2).call(this, 'pageId'),
	    pageView: _classPrivateMethodGet$5(this, _getParam, _getParam2).call(this, 'pageView'),
	    id: 'sn-spaces-iframe',
	    src: _classPrivateMethodGet$5(this, _getParam, _getParam2).call(this, 'contentUrl'),
	    className: 'sn-spaces-iframe'
	  }));
	  babelHelpers.classPrivateFieldGet(this, _frame).subscribe('load', function (baseEvent) {
	    var info = baseEvent.getData();
	    var uri = new main_core.Uri(info.src);
	    uri.removeQueryParam(['IFRAME']);
	    _classPrivateMethodGet$5(_this2, _changeBrowserHistory, _changeBrowserHistory2).call(_this2, uri.toString());
	    if (_classPrivateMethodGet$5(_this2, _getParam, _getParam2).call(_this2, 'pageId') === 'files') {
	      babelHelpers.classPrivateFieldSet(_this2, _disk, new Disk({
	        window: info.window
	      }));
	      babelHelpers.classPrivateFieldGet(_this2, _disk).subscribe('changePage', function (innerBaseEvent) {
	        _classPrivateMethodGet$5(_this2, _changeBrowserHistory, _changeBrowserHistory2).call(_this2, innerBaseEvent.getData());
	      });
	    }
	  });
	  babelHelpers.classPrivateFieldGet(this, _frame).subscribe('unload', function () {
	    _classPrivateMethodGet$5(_this2, _removeOverlays, _removeOverlays2).call(_this2);
	  });
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpen', function () {
	    _classPrivateMethodGet$5(_this2, _hideOverlays, _hideOverlays2).call(_this2);
	  });
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', function () {
	    _classPrivateMethodGet$5(_this2, _showOverlays, _showOverlays2).call(_this2);
	  });
	  new MutationObserver(function () {
	    var theme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
	    var themeStyles = document.head.querySelectorAll("link[data-theme-id=\"".concat(theme, "\""));
	    // eslint-disable-next-line promise/catch-or-return
	    Promise.all(babelHelpers.toConsumableArray(themeStyles).map(function (link) {
	      return new Promise(function (resolve) {
	        main_core.Event.bind(link, 'load', resolve);
	      });
	    })).then(function () {
	      return _classPrivateMethodGet$5(_this2, _updateBaseTheme, _updateBaseTheme2).call(_this2);
	    });
	  }).observe(document.head, {
	    childList: true,
	    subtree: false
	  });
	}
	function _updateBaseTheme2() {
	  var currentTheme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
	  var baseTheme = currentTheme.match(/(.*):/)[1];
	  var document = babelHelpers.classPrivateFieldGet(this, _frame).getFrameNode().contentDocument;
	  if (!document.body) {
	    return;
	  }
	  document.body.className = document.body.className.replace(/bitrix24-\S*-theme/, '');
	  main_core.Dom.addClass(document.body, "bitrix24-".concat(baseTheme, "-theme"));
	}
	function _getParam2(param) {
	  return babelHelpers.classPrivateFieldGet(this, _cache).get('params')[param];
	}
	function _changeBrowserHistory2(url) {
	  window.history.replaceState({}, '', url);
	}
	function _getPageType2(uri) {
	  if (uri.getPath().includes('general')) {
	    return 'discussions';
	  }
	  if (uri.getPath().includes('tasks')) {
	    return 'tasks';
	  }
	  if (uri.getPath().includes('calendar')) {
	    return 'calendar';
	  }
	  if (uri.getPath().includes('disk')) {
	    return 'files';
	  }
	  return 'discussions';
	}
	function _consoleError2(action, error) {
	  // eslint-disable-next-line no-console
	  console.error("Spaces: ".concat(action, " error"), error);
	}

	exports.Space = Space;

}((this.BX.Socialnetwork.Spaces = this.BX.Socialnetwork.Spaces || {}),BX.Event,BX));
//# sourceMappingURL=script.js.map
