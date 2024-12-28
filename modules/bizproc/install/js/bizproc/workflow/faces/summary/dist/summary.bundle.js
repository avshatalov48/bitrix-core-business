/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {};
(function (exports,main_core,main_date,bizproc_workflow_timeline) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _isFinal = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFinal");
	var _workflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _durationTexts = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("durationTexts");
	var _calculateDurationTexts = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateDurationTexts");
	var _renderContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _openTimeline = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openTimeline");
	class Summary {
	  constructor(props = {}) {
	    var _props$data, _props$data2, _props$data3;
	    Object.defineProperty(this, _openTimeline, {
	      value: _openTimeline2
	    });
	    Object.defineProperty(this, _renderContent, {
	      value: _renderContent2
	    });
	    Object.defineProperty(this, _calculateDurationTexts, {
	      value: _calculateDurationTexts2
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isFinal, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _workflowId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _durationTexts, {
	      writable: true,
	      value: {
	        nameBefore: '',
	        value: '',
	        nameAfter: ''
	      }
	    });
	    if (!main_core.Type.isStringFilled(props.workflowId)) {
	      throw new TypeError('workflowId must be filled string');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = props.workflowId;
	    babelHelpers.classPrivateFieldLooseBase(this, _isFinal)[_isFinal] = ((_props$data = props.data) == null ? void 0 : _props$data.status) === 'success';
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = main_core.Type.isStringFilled((_props$data2 = props.data) == null ? void 0 : _props$data2.name) ? props.data.name : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _calculateDurationTexts)[_calculateDurationTexts]((_props$data3 = props.data) == null ? void 0 : _props$data3.duration);
	  }
	  render() {
	    const title = main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _name)[_name]);
	    const footerTitle = main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_SUMMARY_TIMELINE_MSGVER_1'));
	    return main_core.Tag.render(_t || (_t = _`
			<div class="bp-workflow-faces-summary-item">
				<div class="bp-workflow-faces-summary-name">
					<div class="bp-workflow-faces-summary__text-area" title="${0}">${0}</div>
				</div>
				${0}
				<div class="bp-workflow-faces-summary__duration" onclick="${0}">
					<div class="bp-workflow-faces-summary__text-area" title="${0}">${0}</div>
				</div>
			</div>
		`), title, title, babelHelpers.classPrivateFieldLooseBase(this, _renderContent)[_renderContent](), babelHelpers.classPrivateFieldLooseBase(this, _openTimeline)[_openTimeline].bind(this), footerTitle, footerTitle);
	  }
	}
	function _calculateDurationTexts2(time) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isFinal)[_isFinal]) {
	    return;
	  }
	  const duration = main_core.Type.isNumber(time) ? main_date.DateTimeFormat.format([['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']], 0, time) : null;
	  if (duration) {
	    const pattern = /\d+/;
	    const match = duration.match(pattern);
	    if (match) {
	      babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].value = String(match[0]);
	      const index = duration.indexOf(babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].value);
	      if (index !== -1) {
	        babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].nameBefore = duration.slice(0, index).trim();
	        babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].nameAfter = duration.slice(index + babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].value.length).trim();
	      }
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].nameAfter = duration;
	    }
	  }
	}
	function _renderContent2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFinal)[_isFinal]) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="bp-workflow-faces-summary__summary">
					<div class="bp-workflow-faces-summary__summary-name">${0}</div>
					<div class="bp-workflow-faces-summary__summary-value">${0}</div>
					<div class="bp-workflow-faces-summary__summary-name">${0}</div>
				</div>
			`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].nameBefore), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].value), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _durationTexts)[_durationTexts].nameAfter));
	  }
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="bp-workflow-faces-summary__icon-wrapper">
				<div class="ui-icon-set --clock-2 bp-workflow-faces-summary__icon"></div>
			</div>
		`));
	}
	function _openTimeline2(event) {
	  event.stopPropagation();
	  event.preventDefault();
	  bizproc_workflow_timeline.Timeline.open({
	    workflowId: babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]
	  });
	}

	exports.Summary = Summary;

}((this.BX.Bizproc.Workflow.Faces = this.BX.Bizproc.Workflow.Faces || {}),BX,BX.Main,BX.Bizproc.Workflow));
//# sourceMappingURL=summary.bundle.js.map
