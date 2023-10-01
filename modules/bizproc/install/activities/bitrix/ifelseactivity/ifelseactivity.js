/* eslint-disable */
(function (exports) {
	'use strict';

	const ParallelActivity = window.ParallelActivity;
	class IfElseActivity extends ParallelActivity {
	  constructor() {
	    super();
	    this.Type = 'IfElseActivity';
	    this.allowSort = true;
	    this.childActivities = [];
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private, no-underscore-dangle
	    this.__parallelActivityInitType = 'IfElseBranchActivity';
	  }
	}

	exports.IfElseActivity = IfElseActivity;

}((this.window = this.window || {})));
//# sourceMappingURL=ifelseactivity.js.map
