/* eslint-disable */
(function (exports) {
	'use strict';

	const ParallelActivity = window.ParallelActivity;
	class ListenActivity extends ParallelActivity {
	  constructor() {
	    super();
	    this.Type = 'ListenActivity';
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	    this.__parallelActivityInitType = 'EventDrivenActivity';
	  }
	}

	exports.ListenActivity = ListenActivity;

}((this.window = this.window || {})));
//# sourceMappingURL=listenactivity.js.map
