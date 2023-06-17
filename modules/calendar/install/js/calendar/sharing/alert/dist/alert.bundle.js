this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3) {
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
	      type: 'calendar'
	    };
	  },
	  created() {},
	  methods: {},
	  template: `
		<div class="calendar-sharing-alert-container">
			<div class="calendar-sharing-alert-info">
				<div class="calendar-sharing-alert-info-empty --icon-cross">
					<div class="calendar-sharing-alert-title">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_TITLE') }}
					</div>
					<div class="calendar-sharing-alert-description">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_DESCRIPTION') }}
					</div>
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

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Vue3));
//# sourceMappingURL=alert.bundle.js.map
