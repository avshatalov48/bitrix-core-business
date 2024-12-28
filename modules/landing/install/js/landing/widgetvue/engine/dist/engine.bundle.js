/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_loader,ui_vue3,main_core_events,main_core) {
	'use strict';

	const fetchAlarmTime = 5000;
	const Content = {
	  props: {
	    defaultData: {
	      type: Object,
	      default: null
	    },
	    clickable: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isFetching: false,
	      timeout: null
	    };
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe('landing:widgetvue:engine:onSetData', this.onSetData);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe('landing:widgetvue:engine:onSetData', this.onSetData);
	  },
	  methods: {
	    onSetData(event) {
	      clearTimeout(this.timeout);
	      this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:endContentLoad');
	      this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:onHideMessage');
	      this.isFetching = false;
	      if (main_core.Type.isObject(event.getData().data)) {
	        const data = event.getData().data;
	        Object.keys(data).forEach(code => {
	          if (this[code] !== undefined) {
	            this[code] = data[code];
	          }
	          // todo: and crete refs if not exists?
	        });
	      }
	    },

	    fetch(params = {}) {
	      if (!this.clickable || this.isFetching) {
	        console.info('Events is disabled now');
	        return;
	      }
	      this.isFetching = true;
	      this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:startContentLoad');
	      this.timeout = setTimeout(() => {
	        this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:onMessage', {
	          message: main_core.Loc.getMessage('LANDING_WIDGETVUE_LOADER_TOO_LONG')
	        });
	        this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:endContentLoad');
	      }, fetchAlarmTime);
	      this.$bitrix.Application.get().fetch(params);
	    },
	    openApplication(params = {}) {
	      if (!this.clickable) {
	        console.info('Events is disabled now');
	        return;
	      }
	      this.$bitrix.Application.get().openApplication(params);
	    },
	    openPath(path) {
	      if (!this.clickable) {
	        console.info('Events is disabled now');
	        return;
	      }
	      this.$bitrix.Application.get().openPath(path);
	    }
	  },
	  setup(props) {
	    // todo: to docs. All refs must be implicated in default? or we can create, but v-for can be broken

	    // todo: or create refs via data? or pass when create
	    const dataRefs = {};
	    if (main_core.Type.isObject(props.defaultData)) {
	      Object.keys(props.defaultData).forEach(code => {
	        dataRefs[code] = ui_vue3.ref(props.defaultData[code]);
	      });
	    }
	    return dataRefs;
	  }
	};

	const Message = {
	  props: {
	    message: {
	      type: String,
	      default: main_core.Loc.getMessage('LANDING_WIDGETVUE_LOADER_DEFAULT_MESSAGE')
	    },
	    link: {
	      type: String,
	      default: null
	    },
	    linkText: {
	      type: String,
	      default: main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_DEFAULT_LINK_TEXT')
	    }
	  },
	  template: `
		<div class="w-loader">
			<div class="w-loader-icon"></div>
			<div class="w-loader-text">
				<div>{{message}}</div>
			</div>
		</div>
	`
	};

	const Error = {
	  props: {
	    message: {
	      type: String,
	      default: main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_DEFAULT_MESSAGE')
	    },
	    link: {
	      type: String,
	      default: null
	    },
	    linkText: {
	      type: String,
	      default: main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_DEFAULT_LINK_TEXT')
	    }
	  },
	  template: `
		<div class="w-error">
			<div class="w-loader-icon --error"></div>
			<div class="w-error-text">
				<div>{{message}}</div>
				<a
					v-show="link !== null"
					class="w-loader-link" :href="link"
				>
			        {{linkText}}
				</a>	
			</div>
		</div>
	`
	};

	var _parentOrigin = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parentOrigin");
	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _rootNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootNode");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _error = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("error");
	var _clickable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clickable");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _contentComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("contentComponent");
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _onMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMessage");
	var _refreshFrameSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refreshFrameSize");
	var _createApp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createApp");
	class Engine {
	  constructor(options) {
	    Object.defineProperty(this, _createApp, {
	      value: _createApp2
	    });
	    Object.defineProperty(this, _refreshFrameSize, {
	      value: _refreshFrameSize2
	    });
	    Object.defineProperty(this, _onMessage, {
	      value: _onMessage2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _message, {
	      value: _message2
	    });
	    Object.defineProperty(this, _parentOrigin, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _rootNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _error, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _clickable, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _contentComponent, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Type.isString(options.id) ? options.id : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] = document.querySelector(`#${babelHelpers.classPrivateFieldLooseBase(this, _id)[_id]}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _parentOrigin)[_parentOrigin] = main_core.Type.isString(options.origin) ? options.origin : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = main_core.Type.isObject(options.data) ? options.data : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _error)[_error] = main_core.Type.isString(options.error) ? options.error : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _clickable)[_clickable] = main_core.Type.isBoolean(options.clickable) ? options.clickable : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _contentComponent)[_contentComponent] = main_core.Runtime.clone(Content);
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]) {
	      this.loader = new main_loader.Loader({
	        target: babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _contentComponent)[_contentComponent].template = babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode].innerHTML || '';
	      babelHelpers.classPrivateFieldLooseBase(this, _contentComponent)[_contentComponent].template = `<div>${babelHelpers.classPrivateFieldLooseBase(this, _contentComponent)[_contentComponent].template}</div>`;
	      babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	      babelHelpers.classPrivateFieldLooseBase(this, _createApp)[_createApp]();
	    }
	  }
	  showLoader() {
	    this.loader.show();
	  }
	  hideLoader() {
	    this.loader.hide();
	  }
	  fetch(params = {}) {
	    if (params instanceof Event) {
	      params = {};
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('fetchData', params);
	  }
	  openApplication(params = {}) {
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('openApplication', params);
	  }
	  openPath(path) {
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('openPath', {
	      path
	    });
	  }
	}
	function _message2(name, params = {}) {
	  window.parent.postMessage({
	    name,
	    params,
	    origin: babelHelpers.classPrivateFieldLooseBase(this, _id)[_id]
	  }, babelHelpers.classPrivateFieldLooseBase(this, _parentOrigin)[_parentOrigin]);
	}
	function _bindEvents2() {
	  main_core.Event.bind(window, 'message', babelHelpers.classPrivateFieldLooseBase(this, _onMessage)[_onMessage].bind(this));
	}
	function _onMessage2(event) {
	  if (event.data && event.data.origin && event.data.name && event.data.params && main_core.Type.isObject(event.data.params)) {
	    if (event.data.origin !== babelHelpers.classPrivateFieldLooseBase(this, _id)[_id]) {
	      return;
	    }
	    if (event.data.name === 'setData' && main_core.Type.isObject(event.data.params.data)) {
	      main_core_events.EventEmitter.emit('landing:widgetvue:engine:onSetData', {
	        data: event.data.params.data
	      });
	    }
	    if (event.data.name === 'setError' && main_core.Type.isObject(event.data.params.error) && main_core.Type.isString(event.data.params.error.message)) {
	      main_core_events.EventEmitter.emit('landing:widgetvue:engine:onError', {
	        message: event.data.params.error.message
	      });
	    }
	    if (event.data.name === 'getSize') ;
	    babelHelpers.classPrivateFieldLooseBase(this, _refreshFrameSize)[_refreshFrameSize]();
	  }
	}
	function _refreshFrameSize2() {
	  requestAnimationFrame(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('setSize', {
	      size: babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode].offsetHeight
	    });
	  });
	}
	function _createApp2() {
	  const context = this;
	  const defaultError = babelHelpers.classPrivateFieldLooseBase(this, _error)[_error] ? {
	    message: babelHelpers.classPrivateFieldLooseBase(this, _error)[_error]
	  } : null;
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = ui_vue3.BitrixVue.createApp({
	    name: babelHelpers.classPrivateFieldLooseBase(this, _id)[_id],
	    components: {
	      Message,
	      Error,
	      Content: babelHelpers.classPrivateFieldLooseBase(this, _contentComponent)[_contentComponent]
	    },
	    props: {
	      defaultData: {
	        type: Object,
	        default: null
	      }
	    },
	    data() {
	      return {
	        message: null,
	        error: defaultError
	      };
	    },
	    created() {
	      this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:startContentLoad', this.onShowLoader);
	      this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:endContentLoad', this.onHideLoader);
	      this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:onMessage', this.onShowMessage);
	      this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:onHideMessage', this.onHideMessage);
	      main_core_events.EventEmitter.subscribe('landing:widgetvue:engine:onError', this.onShowError);
	    },
	    mounted() {
	      babelHelpers.classPrivateFieldLooseBase(this.$bitrix.Application.get(), _refreshFrameSize)[_refreshFrameSize]();
	      this.$nextTick(() => {
	        const links = this.$el.getElementsByTagName('a');
	        if (links.length > 0) {
	          [].slice.call(links).map(link => {
	            main_core.Event.bind(link, 'click', event => {
	              event.preventDefault();
	              event.stopPropagation();
	            });
	          });
	        }
	      });
	    },
	    beforeUnmount() {
	      this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:startContentLoad', this.onShowLoader);
	      this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:endContentLoad', this.onHideLoader);
	      this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:onMessage', this.onShowMessage);
	      this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:onHideMessage', this.onHideMessage);
	      main_core_events.EventEmitter.unsubscribe('landing:widgetvue:engine:onError', this.onShowError);
	    },
	    methods: {
	      onShowLoader() {
	        // todo: move loader to comp
	        this.$bitrix.Application.get().showLoader();
	      },
	      onHideLoader() {
	        // todo: move loader to comp
	        this.$bitrix.Application.get().hideLoader();
	      },
	      onShowMessage(event) {
	        var _event$getData;
	        const message = ((_event$getData = event.getData()) == null ? void 0 : _event$getData.message) || null;
	        this.message = message ? {
	          message
	        } : null;
	      },
	      onHideMessage() {
	        this.message = null;
	      },
	      onShowError(event) {
	        var _event$getData2;
	        // todo: set error link?
	        const message = ((_event$getData2 = event.getData()) == null ? void 0 : _event$getData2.message) || null;
	        this.error = message ? {
	          message
	        } : null;
	        this.onHideLoader();
	      }
	    },
	    beforeCreate() {
	      this.$bitrix.Application.set(context);
	    },
	    template: `
				<div class="widget">
					<Error
						v-show="error !== null"
						v-bind="error && error.message !== null ? error : {}"
					/>
					<Message
						v-show="message !== null"
						v-bind="message && message.message !== null ? message : {}"
					/>
					<Content
						v-show="message === null && error === null"
						
						:defaultData="defaultData"
						:clickable=${babelHelpers.classPrivateFieldLooseBase(this, _clickable)[_clickable]}
					/>
				</div>
			`
	  }, {
	    defaultData: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].mount(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]);
	}

	exports.Engine = Engine;

}((this.BX.Landing.WidgetVue = this.BX.Landing.WidgetVue || {}),BX,BX.Vue3,BX.Event,BX));
//# sourceMappingURL=engine.bundle.js.map
