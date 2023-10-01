/* eslint-disable */
(function (exports,main_core) {
	'use strict';

	const SequentialWorkflowActivity = window.SequentialWorkflowActivity;
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _afterSequenceDraw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("afterSequenceDraw");
	var _setError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setError");
	class EventDrivenActivity extends SequentialWorkflowActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _setError, {
	      value: _setError2
	    });
	    Object.defineProperty(this, _afterSequenceDraw, {
	      value: _afterSequenceDraw2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    this.Type = 'EventDrivenActivity';
	    this.DrawSequentialWorkflowActivity = this.Draw;
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.AfterSDraw = babelHelpers.classPrivateFieldLooseBase(this, _afterSequenceDraw)[_afterSequenceDraw].bind(this);
	    this.SetError = babelHelpers.classPrivateFieldLooseBase(this, _setError)[_setError].bind(this);
	  }
	}
	function _draw2(wrapper) {
	  if (this.parentActivity.Type === 'StateActivity') {
	    this.DrawSequentialWorkflowActivity(wrapper);
	  } else {
	    this.DrawSequenceActivity(wrapper);
	  }
	}
	function _afterSequenceDraw2() {
	  if (this.parentActivity.Type === 'StateActivity' && this.childsContainer.rows.length > 2) {
	    main_core.Dom.style(this.childsContainer.rows[0], 'display', 'none');
	    main_core.Dom.style(this.childsContainer.rows[1], 'display', 'none');
	  }
	}
	function _setError2(hasError, setFocus) {
	  this.parentActivity.SetError(hasError, setFocus);
	}

	exports.EventDrivenActivity = EventDrivenActivity;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=eventdrivenactivity.js.map
