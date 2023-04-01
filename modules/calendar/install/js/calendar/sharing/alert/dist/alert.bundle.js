this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3,main_core,calendar_util) {
	'use strict';

	const Application = {
	  props: {
	    link: {
	      type: Object,
	      default: null
	    }
	  },
	  name: 'Application',

	  data() {
	    return {
	      type: '',
	      returnButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_ALERT_RETURN_BUTTON'),
	        disabled: false
	      }
	    };
	  },

	  created() {
	    if (this.link && main_core.Type.isObject(this.link)) {
	      this.type = 'event';
	    } else {
	      this.type = 'calendar';
	    }

	    this.setPageVisualSettings();
	  },

	  methods: {
	    setPageVisualSettings() {
	      const htmlNode = document.querySelector('html');
	      const bodyNode = document.querySelector('body');

	      if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body')) {
	        main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html');
	      }

	      main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
	      main_core.Dom.addClass(htmlNode, 'calendar-sharing--alert');

	      if (calendar_util.Util.isMobileBrowser()) {
	        if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile')) {
	          main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
	        }

	        if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile')) {
	          main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
	        }
	      }
	    },

	    handleReturnButtonClick() {
	      this.returnButton.disabled = true;

	      if (this.link.userLinkHash) {
	        const sharingPath = '/pub/calendar-sharing/';
	        window.location.href = document.location.origin + sharingPath + this.link.userLinkHash;
	      }

	      this.returnButton.disabled = false;
	    }

	  },
	  template: `
		<div class="calendar-sharing-alert-container">
			<div class="calendar-sharing-alert-icon"></div>
			<div class="calendar-sharing-alert-info" v-if="type === 'event'">
				<div class="calendar-sharing-alert-title">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_EVENT_TITLE') }}
				</div>
				<div class="calendar-sharing-alert-description">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_EVENT_DESCRIPTION') }}
				</div>
				<div class="ui-btn-container ui-btn-container-center calendar-shared-alert_btn-box" v-if="link.userLinkHash">
					<button
						class="ui-btn ui-btn-success ui-btn-round"
						@click="handleReturnButtonClick"
						:disabled="returnButton.disabled"
					>
						{{ returnButton.text }}
					</button>
				</div>
			</div>
			<div class="calendar-sharing-alert-info" v-else>
				<div class="calendar-sharing-alert-title">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_TITLE') }}
				</div>
				<div class="calendar-sharing-alert-description">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_DESCRIPTION') }}
				</div>
			</div>
		</div>
	`
	};

	class Alert {
	  constructor(options = {}) {
	    this.link = options.link;
	    this.rootNode = BX('calendar-sharing-alert');
	    this.buildView();
	  }

	  buildView() {
	    this.application = ui_vue3.BitrixVue.createApp(Application, {
	      link: this.link
	    }).mount(this.rootNode);
	  }

	}

	exports.Alert = Alert;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Vue3,BX,BX.Calendar));
//# sourceMappingURL=alert.bundle.js.map
