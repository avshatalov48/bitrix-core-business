/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_vue3,main_core_events,ui_tooltip,ui_iconSet_api_vue,main_core,main_date) {
	'use strict';

	const headerTypeEnum = Object.freeze({
	  TEXT: 'text',
	  STUB: 'stub'
	});
	const imageTypeEnum = Object.freeze({
	  IMAGE: 'image',
	  IMAGE_STUB: 'image-stub',
	  USER: 'user',
	  USER_STUB: 'user-stub',
	  ICON: 'icon',
	  COUNTER: 'counter'
	});
	const stackStatusEnum = Object.freeze({
	  CUSTOM: 'custom',
	  OK: 'ok',
	  CANCEL: 'cancel',
	  WAIT: 'wait'
	});
	const footerTypeEnum = Object.freeze({
	  TEXT: 'text',
	  STUB: 'stub',
	  DURATION: 'duration'
	});

	function validateStep(data) {
	  if (!main_core.Type.isStringFilled(data.id)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StepData.id must be filled string');
	    return false;
	  }
	  if (!main_core.Type.isUndefined(data.progressBox)) {
	    if (!main_core.Type.isPlainObject(data.progressBox)) {
	      // eslint-disable-next-line no-console
	      console.warn('UI.Image-Stack-Steps: StepData.progressBox must be plain object');
	    }
	    if (!main_core.Type.isString(data.progressBox.title)) {
	      // eslint-disable-next-line no-console
	      console.warn('UI.Image-Stack-Steps: StepData.progressBox.title must be string');
	      return false;
	    }
	  }
	  return validateHeader(data.header) && validateStack(data.stack) && validateFooter(data.footer);
	}
	function validateHeader(data) {
	  if (main_core.Type.isNil(data)) {
	    return true;
	  }
	  if (!main_core.Type.isPlainObject(data)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StepData.header must be plain object');
	    return false;
	  }
	  if (!Object.values(headerTypeEnum).includes(data.type)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StepData.header.type must be one of headerTypeEnum values');
	    return false;
	  }
	  if (data.type === headerTypeEnum.TEXT) {
	    var _data$data;
	    if (main_core.Type.isString((_data$data = data.data) == null ? void 0 : _data$data.text)) {
	      return true;
	    }

	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: ' + 'StepData.header with type "text" must contain HeaderData.text; ' + 'HeaderData.text must be string');
	    return false;
	  }
	  return data.type === headerTypeEnum.STUB;
	}
	function validateFooter(data) {
	  if (main_core.Type.isNil(data)) {
	    return true;
	  }
	  if (!main_core.Type.isPlainObject(data)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StepData.footer must be plain object');
	    return false;
	  }
	  if (data.type === footerTypeEnum.TEXT) {
	    var _data$data2;
	    if (main_core.Type.isString((_data$data2 = data.data) == null ? void 0 : _data$data2.text)) {
	      return true;
	    }

	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: ' + 'StepData.footer with type "text" must contain FooterData.text; ' + 'FooterData.text must be string');
	    return false;
	  }
	  if (data.type === footerTypeEnum.DURATION) {
	    return validateFooterDuration(data.data);
	  }
	  return data.type === footerTypeEnum.STUB;
	}
	function validateStack(data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StepData.stack must be plain object');
	    return false;
	  }
	  if (!main_core.Type.isUndefined(data.status)) {
	    if (!main_core.Type.isPlainObject(data.status)) {
	      // eslint-disable-next-line no-console
	      console.warn('UI.Image-Stack-Steps: StackData.status must be plain object');
	      return false;
	    }
	    if (!validateStatus(data.status)) {
	      return false;
	    }
	  }
	  if (!main_core.Type.isArrayFilled(data.images)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StackData.images must be filled array');
	    return false;
	  }
	  for (const image of data.images) {
	    if (!validateImage(image)) {
	      return false;
	    }
	  }
	  return true;
	}
	function validateImage(data) {
	  var _data$data3, _data$data4, _data$data5, _data$data6, _data$data7, _data$data8;
	  if (!main_core.Type.isPlainObject(data)) {
	    return false;
	  }
	  if (data.type === imageTypeEnum.IMAGE && main_core.Type.isString((_data$data3 = data.data) == null ? void 0 : _data$data3.src) && (main_core.Type.isUndefined((_data$data4 = data.data) == null ? void 0 : _data$data4.title) || main_core.Type.isStringFilled((_data$data5 = data.data) == null ? void 0 : _data$data5.title))) {
	    return true;
	  }
	  if (data.type === imageTypeEnum.USER && main_core.Type.isString((_data$data6 = data.data) == null ? void 0 : _data$data6.src) && main_core.Type.isNumber((_data$data7 = data.data) == null ? void 0 : _data$data7.userId) && data.data.userId > 0) {
	    return true;
	  }
	  if (data.type === imageTypeEnum.ICON && validateIcon(data.data)) {
	    return true;
	  }
	  if (data.type === imageTypeEnum.USER_STUB || data.type === imageTypeEnum.IMAGE_STUB) {
	    return true;
	  }
	  if (data.type === imageTypeEnum.COUNTER && main_core.Type.isStringFilled((_data$data8 = data.data) == null ? void 0 : _data$data8.text)) {
	    return true;
	  }

	  // eslint-disable-next-line no-console
	  console.warn('UI.Image-Stack-Steps: StackData.data must be correct', data);
	  return false;
	}
	function validateStatus(data) {
	  if (data.type === stackStatusEnum.CUSTOM && !validateIcon(data.data)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: StackData.status with type "custom" must be correct', data);
	    return false;
	  }
	  if (Object.values(stackStatusEnum).includes(data.type)) {
	    return true;
	  }

	  // eslint-disable-next-line no-console
	  console.warn('UI.Image-Stack-Steps: StackData.status must be correct', data);
	  return false;
	}
	function validateFooterDuration(data) {
	  if (!main_core.Type.isNumber(data.duration) || data.duration < 0) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: FooterDurationData.duration must be not negative number');
	    return false;
	  }
	  if (!main_core.Type.isBoolean(data.realtime)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: FooterDurationData.realtime must be boolean');
	    return false;
	  }
	  if (data.realtime === true && !main_core.Type.isUndefined(data.realtimeBoundary) && (!main_core.Type.isNumber(data.realtimeBoundary) || data.realtimeBoundary <= 0)) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: FooterDurationData.realtimeBoundary must be positive integer');
	    return false;
	  }
	  if (!main_core.Type.isUndefined(data.format) && !(main_core.Type.isString(data.format) || main_core.Type.isArray(data.format))) {
	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: FooterDurationData.format must be array or string');
	    return false;
	  }
	  return true;
	}
	function validateIcon(data) {
	  return main_core.Type.isPlainObject(data) && main_core.Type.isStringFilled(data.icon) && main_core.Type.isStringFilled(data.color);
	}

	const ProgressBox = {
	  name: 'ui-image-stack-steps-step-progress-box',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div
			:title="title"
			class="ui-image-stack-steps-step-progress-box"
		>
			<BIcon
				name="more"
				:size="12"
				color="var(--ui-color-base-70)"
				class="ui-image-stack-steps-step-progress-box__icon"
			/>
			<div class="ui-image-stack-steps-step-progress-box__icon-overlay"></div>
		</div>
	`
	};

	const Text = {
	  name: 'ui-image-stack-steps-text',
	  props: {
	    text: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-text" :title="text">{{ text }}</div>
	`
	};

	const TextStub = {
	  name: 'ui-image-stack-steps-text-skeleton',
	  template: `
		<div class="ui-image-stack-steps-text-skeleton-area">
			<div class="ui-image-stack-steps-text-skeleton-area-stub"></div>
		</div>
	`
	};

	const Header = {
	  name: 'ui-image-stack-steps-step-header',
	  props: {
	    /** @var {HeaderType} header */
	    header: {
	      type: Object,
	      required: true,
	      validator: value => {
	        return validateHeader(value);
	      }
	    }
	  },
	  methods: {
	    getComponent() {
	      if (this.header.type === headerTypeEnum.TEXT) {
	        return Text;
	      }
	      return TextStub;
	    },
	    getCustomStyles() {
	      var _this$header$styles;
	      const styles = {};
	      if (main_core.Type.isNumber((_this$header$styles = this.header.styles) == null ? void 0 : _this$header$styles.maxWidth)) {
	        styles.maxWidth = `${this.header.styles.maxWidth}px`;
	      }
	      return styles;
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-header" :style="getCustomStyles()">
			<component :is="getComponent()" v-bind="header.data"/>
		</div>
	`
	};

	const StackStatus = {
	  name: 'ui-image-stack-steps-step-stack-status',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    /** @var { StackStatusType } status */
	    status: {
	      type: Object,
	      required: true,
	      validator: value => {
	        return validateStatus(value);
	      }
	    }
	  },
	  computed: {
	    icon() {
	      switch (this.status.type) {
	        case stackStatusEnum.OK:
	          return 'circle-check';
	        case stackStatusEnum.WAIT:
	          return 'black-clock';
	        case stackStatusEnum.CANCEL:
	          return 'cross-circle-60';
	        default:
	          return this.status.data.icon;
	      }
	    },
	    color() {
	      switch (this.status.type) {
	        case stackStatusEnum.OK:
	          return 'var(--ui-color-primary-alt)';
	        case stackStatusEnum.WAIT:
	          return 'var(--ui-color-palette-blue-60)';
	        case stackStatusEnum.CANCEL:
	          return 'var(--ui-color-base-35)';
	        default:
	          return this.status.data.color;
	      }
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-step-stack-status">
			<BIcon
				v-if="icon"
				:name="icon" :color="color" :size="24"
				class="ui-image-stack-steps-step-stack-status-icon"
			/>
			<div class="ui-image-stack-steps-step-stack-status-icon__overlay"></div>
		</div>
	`
	};

	const Image = {
	  name: 'ui-image-stack-steps-image',
	  props: {
	    src: {
	      type: String,
	      required: true,
	      validator: value => {
	        return main_core.Type.isStringFilled(value);
	      }
	    },
	    title: {
	      type: String,
	      required: false,
	      validator: value => {
	        return main_core.Type.isStringFilled(value);
	      }
	    }
	  },
	  methods: {
	    getSafeSrc() {
	      return `url('${encodeURI(main_core.Text.encode(this.src))}')`;
	    }
	  },
	  template: `
		<div
			:style="{ backgroundImage: getSafeSrc()}"
			class="ui-image-stack-steps-image"
			:title="title"
		></div>
	`
	};

	const ImageStub = {
	  name: 'ui-image-stack-steps-image-stub',
	  template: `
		<div class="ui-image-stack-steps-image --image-stub"></div>
	`
	};

	const User = {
	  name: 'ui-image-stack-steps-user',
	  props: {
	    src: {
	      type: String,
	      required: true
	    },
	    userId: {
	      type: Number,
	      required: true,
	      validator: value => {
	        return value > 0;
	      }
	    }
	  },
	  data() {
	    return {
	      style: {
	        backgroundImage: main_core.Type.isStringFilled(this.src) ? this.getSafeSrc() : ''
	      }
	    };
	  },
	  methods: {
	    getSafeSrc() {
	      return `url('${encodeURI(main_core.Text.encode(this.src))}')`;
	    }
	  },
	  template: `
		<div 
			class="ui-image-stack-steps-image --user"
			:style="style"
			:bx-tooltip-user-id="userId"
		></div>
	`
	};

	const Icon = {
	  name: 'ui-image-stack-steps-icon',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    icon: {
	      type: String,
	      required: true,
	      validator: value => {
	        return main_core.Type.isStringFilled(value);
	      }
	    },
	    color: {
	      type: String,
	      required: true,
	      validator: value => {
	        return main_core.Type.isStringFilled(value);
	      }
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-image --icon">
			<BIcon :name="icon" :color="color" :size="24"/>
		</div>
	`
	};

	const UserStub = {
	  name: 'ui-image-stack-steps-user-stub',
	  components: {
	    Icon
	  },
	  template: `
		<Icon icon="person" color="var(--ui-color-base-15)"/>
	`
	};

	const Counter = {
	  name: 'ui-image-stack-steps-counter',
	  props: {
	    text: {
	      type: String,
	      required: true,
	      validator: value => {
	        return main_core.Type.isStringFilled(value);
	      }
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-counter">{{ text }}</div>
	`
	};

	const Stack = {
	  name: 'ui-image-stack-steps-step-stack',
	  components: {
	    StackStatus
	  },
	  props: {
	    /** @var { StackType } status */
	    stack: {
	      type: Object,
	      required: true,
	      validator: value => {
	        return validateStack(value);
	      }
	    }
	  },
	  computed: {
	    hasStatus() {
	      return main_core.Type.isPlainObject(this.stack.status);
	    }
	  },
	  methods: {
	    getComponent(image) {
	      switch (image.type) {
	        case imageTypeEnum.IMAGE:
	          return Image;
	        case imageTypeEnum.USER:
	          return User;
	        case imageTypeEnum.ICON:
	          return Icon;
	        case imageTypeEnum.USER_STUB:
	          return UserStub;
	        case imageTypeEnum.COUNTER:
	          return Counter;
	        default:
	          return ImageStub;
	      }
	    },
	    computeKey(image, index) {
	      let key = 'image-stub';

	      // eslint-disable-next-line default-case
	      switch (image.type) {
	        case imageTypeEnum.IMAGE:
	          key = image.data.src;
	          break;
	        case imageTypeEnum.USER:
	          key = String(image.data.userId);
	          break;
	        case imageTypeEnum.ICON:
	          key = `${image.data.icon}-${image.data.color}`;
	          break;
	        case imageTypeEnum.USER_STUB:
	          key = 'user-stub';
	          break;
	        case imageTypeEnum.COUNTER:
	          key = 'counter';
	          break;
	      }
	      return `${key}-${index}`;
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-step-stack">
			<StackStatus v-if="hasStatus" :status="stack.status"/>
			<template v-for="(image, index) in stack.images" :key="computeKey(image, index)">
				<component :is="getComponent(image)" v-bind="image.data"/>
			</template>
		</div>
	`
	};

	const Duration = {
	  name: 'ui-image-stack-steps-duration',
	  components: {
	    Text
	  },
	  props: {
	    duration: {
	      type: Number,
	      required: true,
	      validator: value => {
	        return value >= 0;
	      }
	    },
	    realtime: {
	      type: Boolean,
	      required: true
	    },
	    realtimeBoundary: {
	      type: Number,
	      required: false
	    },
	    format: {
	      type: [String, Array],
	      required: false
	    }
	  },
	  data() {
	    return {
	      defaultFormat: [['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']],
	      defaultRealtimeBoundary: 60 * 60,
	      // 1 hour
	      computedDuration: 0,
	      startTime: Math.floor(Date.now() / 1000),
	      timer: null
	    };
	  },
	  watch: {
	    duration() {
	      this.computedDuration = 0;
	      this.startTime = Math.floor(Date.now() / 1000);
	    },
	    isRealtime(realtime) {
	      if (realtime) {
	        this.startTime = Math.floor(Date.now() / 1000);
	        this.computedDuration = 0;
	        this.startTimer();
	      } else {
	        this.stopTimer();
	      }
	    }
	  },
	  computed: {
	    text() {
	      const duration = this.isRealtime ? this.duration + this.computedDuration : this.duration;
	      return main_date.DateTimeFormat.format(this.getFormat(), 0, duration);
	    },
	    isRealtime() {
	      return this.realtime;
	    }
	  },
	  mounted() {
	    this.startTimer();
	  },
	  unmounted() {
	    this.stopTimer();
	  },
	  methods: {
	    startTimer() {
	      if (!this.isRealtime) {
	        return;
	      }
	      this.timer = setInterval(() => {
	        if (!this.isRealtime) {
	          this.stopTimer();
	          return;
	        }
	        if (this.duration + this.computedDuration < (this.realtimeBoundary || this.defaultRealtimeBoundary)) {
	          this.computedDuration = Math.floor(Date.now() / 1000) - this.startTime;
	          return;
	        }
	        this.stopTimer();
	      }, 1000);
	    },
	    stopTimer() {
	      if (this.timer) {
	        clearInterval(this.timer);
	        this.timer = null;
	      }
	    },
	    getFormat() {
	      if (main_core.Type.isArray(this.format) || main_core.Type.isString(this.format)) {
	        return this.format;
	      }
	      return this.defaultFormat;
	    }
	  },
	  template: `
		<Text :text="text"/>
	`
	};

	const Footer = {
	  name: 'ui-image-stack-steps-step-footer',
	  props: {
	    /** @var { FooterType } footer */
	    footer: {
	      type: Object,
	      required: true,
	      validator: value => {
	        return main_core.Type.isPlainObject(value);
	      }
	    }
	  },
	  methods: {
	    getComponent() {
	      switch (this.footer.type) {
	        case footerTypeEnum.TEXT:
	          return Text;
	        case footerTypeEnum.DURATION:
	          return Duration;
	        default:
	          return TextStub;
	      }
	    },
	    getCustomStyles() {
	      var _this$footer$styles;
	      const styles = {};
	      if (main_core.Type.isNumber((_this$footer$styles = this.footer.styles) == null ? void 0 : _this$footer$styles.maxWidth)) {
	        styles.maxWidth = `${this.footer.styles.maxWidth}px`;
	      }
	      return styles;
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps-footer" :style="getCustomStyles()">
			<component :is="getComponent()" v-bind="footer.data"/>
		</div>
	`
	};

	const Step = {
	  name: 'ui-image-stack-steps-step',
	  components: {
	    ProgressBox,
	    Header,
	    Stack,
	    Footer
	  },
	  props: {
	    /** @var {StepType} step */
	    step: {
	      type: Object,
	      required: true,
	      validator: value => {
	        return validateStep(value);
	      }
	    }
	  },
	  computed: {
	    hasProgressBox() {
	      return main_core.Type.isPlainObject(this.step.progressBox);
	    },
	    hasHeader() {
	      return !main_core.Type.isNil(this.step.header);
	    },
	    hasFooter() {
	      return !main_core.Type.isNil(this.step.footer);
	    },
	    getCustomStyles() {
	      var _this$step$styles;
	      const styles = {};
	      if ((_this$step$styles = this.step.styles) != null && _this$step$styles.minWidth) {
	        styles.minWidth = `${main_core.Text.toInteger(this.step.styles.minWidth)}px`;
	      }
	      return styles;
	    }
	  },
	  template: `
		<div 
			class="ui-image-stack-steps-step"
			:class="{'--with-header': hasHeader, '--with-footer': hasFooter}"
			:style="getCustomStyles"
		>
			<ProgressBox v-if="hasProgressBox" :title="step.progressBox.title"/>
			<Header v-if="hasHeader" :header="step.header"/>
			<Stack :stack="step.stack"/>
			<Footer v-if="hasFooter" :footer="step.footer"/>
		</div>
	`
	};

	const Application = {
	  name: 'ui-image-stack-steps-application',
	  components: {
	    Step
	  },
	  props: {
	    initialSteps: {
	      type: Array,
	      required: true,
	      validator: value => {
	        return main_core.Type.isArrayFilled(value);
	      }
	    }
	  },
	  data() {
	    return {
	      steps: this.initialSteps
	    };
	  },
	  created() {
	    this.subscribeOnEvents();
	  },
	  methods: {
	    subscribeOnEvents() {
	      if (this.$root.$app) {
	        main_core_events.EventEmitter.subscribe(this.$root.$app, 'UI.ImageStackSteps.onUpdateSteps', () => {
	          this.steps = this.$root.$app.getSteps();
	        });
	      }
	    }
	  },
	  template: `
		<div class="ui-image-stack-steps">
			<template v-for="step in steps" :key="step.id">
				<Step :step="step"/>
			</template>
		</div>
	`
	};

	var _steps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("steps");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _setSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setSteps");
	var _initApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initApplication");
	class ImageStackSteps {
	  constructor(options) {
	    Object.defineProperty(this, _initApplication, {
	      value: _initApplication2
	    });
	    Object.defineProperty(this, _setSteps, {
	      value: _setSteps2
	    });
	    Object.defineProperty(this, _steps, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isArrayFilled(options.steps)) {
	      throw new TypeError('options.steps must be filled array');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setSteps)[_setSteps](options.steps);
	    if (!main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps])) {
	      throw new TypeError('options.steps must be contain correct steps data, see warnings');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initApplication)[_initApplication]();
	  }
	  renderTo(node) {
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].mount(node);
	  }
	  getSteps() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].map(step => ({
	      ...step
	    }));
	  }
	  addStep(stepData) {
	    if (validateStep(stepData)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].push(stepData);
	      main_core_events.EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');
	      return true;
	    }

	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: Step was skipped due to incorrect stepData', stepData);
	    return false;
	  }
	  updateStep(stepData, stepId) {
	    const index = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].findIndex(step => step.id === stepId);
	    if (index === -1) {
	      // eslint-disable-next-line no-console
	      console.warn(`UI.Image-Stack-Steps: Step with id ${stepId} not find`);
	      return false;
	    }
	    const oldStepData = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][index];
	    const modifiedData = Object.assign(oldStepData, stepData);
	    modifiedData.id = oldStepData.id;
	    if (validateStep(modifiedData)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][index] = modifiedData;
	      main_core_events.EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');
	      return true;
	    }

	    // eslint-disable-next-line no-console
	    console.warn('UI.Image-Stack-Steps: Step was not updated due to incorrect stepData', modifiedData);
	    return false;
	  }
	  deleteStep(stepId) {
	    const index = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].findIndex(step => step.id === stepId);
	    if (index === -1) {
	      return true;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].splice(index, 1);
	    main_core_events.EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');
	    return true;
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount();
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps] = null;
	  }
	}
	function _setSteps2(stepsData) {
	  babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps] = [];
	  stepsData.forEach(step => {
	    if (validateStep(step)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].push(step);
	    } else {
	      // eslint-disable-next-line no-console
	      console.warn('UI.Image-Stack-Steps: Step was skipped due to incorrect stepData', step);
	    }
	  });
	}
	function _initApplication2() {
	  // eslint-disable-next-line unicorn/no-this-assignment
	  const context = this;
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = ui_vue3.BitrixVue.createApp({
	    name: 'ui-image-stack-steps',
	    components: {
	      Application
	    },
	    props: {
	      steps: Array
	    },
	    created() {
	      this.$app = context;
	    },
	    template: `
					<Application
						:initialSteps="steps"
					></Application>
				`
	  }, {
	    steps: babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps]
	  });
	}

	exports.headerTypeEnum = headerTypeEnum;
	exports.imageTypeEnum = imageTypeEnum;
	exports.footerTypeEnum = footerTypeEnum;
	exports.stackStatusEnum = stackStatusEnum;
	exports.ImageStackSteps = ImageStackSteps;

}((this.BX.UI = this.BX.UI || {}),BX.Vue3,BX.Event,BX.UI,BX.UI.IconSet,BX,BX.Main));
//# sourceMappingURL=image-stack-steps.bundle.js.map
