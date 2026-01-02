/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {};
(function (exports,main_core,bizproc_types) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _LENGTH_LIMIT = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("LENGTH_LIMIT");
	var _status = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _renderBBCodeResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderBBCodeResult");
	var _renderNoRightResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNoRightResult");
	var _renderCollapsedResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCollapsedResult");
	var _replaceBrTag = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("replaceBrTag");
	var _clearTags = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearTags");
	var _replaceNewLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("replaceNewLine");
	var _handleExpandResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleExpandResult");
	var _isNoRightResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNoRightResult");
	var _isNeedCollapse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNeedCollapse");
	class WorkflowResult {
	  // 80 symbols without length of "...etc."

	  constructor(data) {
	    Object.defineProperty(this, _isNeedCollapse, {
	      value: _isNeedCollapse2
	    });
	    Object.defineProperty(this, _isNoRightResult, {
	      value: _isNoRightResult2
	    });
	    Object.defineProperty(this, _handleExpandResult, {
	      value: _handleExpandResult2
	    });
	    Object.defineProperty(this, _replaceNewLine, {
	      value: _replaceNewLine2
	    });
	    Object.defineProperty(this, _clearTags, {
	      value: _clearTags2
	    });
	    Object.defineProperty(this, _replaceBrTag, {
	      value: _replaceBrTag2
	    });
	    Object.defineProperty(this, _renderCollapsedResult, {
	      value: _renderCollapsedResult2
	    });
	    Object.defineProperty(this, _renderNoRightResult, {
	      value: _renderNoRightResult2
	    });
	    Object.defineProperty(this, _renderBBCodeResult, {
	      value: _renderBBCodeResult2
	    });
	    Object.defineProperty(this, _status, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _node, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = data.status;
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = main_core.Type.isStringFilled(data.text) ? data.text : '';
	  }
	  render() {
	    if (!main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node])) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _node)[_node];
	    }
	    let result = '';
	    switch (babelHelpers.classPrivateFieldLooseBase(this, _status)[_status]) {
	      case bizproc_types.WorkflowResultStatus.BB_CODE_RESULT:
	        result = babelHelpers.classPrivateFieldLooseBase(this, _renderBBCodeResult)[_renderBBCodeResult]();
	        break;
	      case bizproc_types.WorkflowResultStatus.NO_RIGHTS_RESULT:
	        result = babelHelpers.classPrivateFieldLooseBase(this, _renderNoRightResult)[_renderNoRightResult]();
	        break;
	      default:
	        result = babelHelpers.classPrivateFieldLooseBase(this, _text)[_text];
	    }
	    const cleanedResult = babelHelpers.classPrivateFieldLooseBase(this, _replaceNewLine)[_replaceNewLine](babelHelpers.classPrivateFieldLooseBase(this, _clearTags)[_clearTags](babelHelpers.classPrivateFieldLooseBase(this, _replaceBrTag)[_replaceBrTag](result)));
	    babelHelpers.classPrivateFieldLooseBase(this, _node)[_node] = main_core.Tag.render(_t || (_t = _`
			<div class="bp-workflow-result">
				${0}
				<span class="bp-workflow-result-full">
					${0}
				</span>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _isNeedCollapse)[_isNeedCollapse](cleanedResult) ? babelHelpers.classPrivateFieldLooseBase(this, _renderCollapsedResult)[_renderCollapsedResult](cleanedResult) : null, result);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isNoRightResult)[_isNoRightResult]()) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node], 'no-rights');
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isNeedCollapse)[_isNeedCollapse](cleanedResult)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleExpandResult)[_handleExpandResult]();
	    }
	    BX.UI.Hint.init(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _node)[_node];
	  }
	  renderTo(container) {
	    if (main_core.Type.isDomNode(container)) {
	      main_core.Dom.append(this.render(), container);
	    }
	  }
	}
	function _renderBBCodeResult2() {
	  return `${main_core.Loc.getMessage('BP_JS_WF_RESULT_VALUE')}<br>${babelHelpers.classPrivateFieldLooseBase(this, _text)[_text]}`;
	}
	function _renderNoRightResult2() {
	  return `
			${main_core.Loc.getMessage('BP_JS_WF_RESULT_NO_RIGHTS_VIEW')} 
			<span data-hint="${main_core.Loc.getMessage('BP_JS_WF_RESULT_NO_RIGHTS_TOOLTIP')}"></span>
		`;
	}
	function _renderCollapsedResult2(result) {
	  const collapsedResult = result.slice(0, babelHelpers.classPrivateFieldLooseBase(this.constructor, _LENGTH_LIMIT)[_LENGTH_LIMIT]);
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<span class="bp-workflow-result-collapsed">
				${0}
				...
				<a href="#" onclick="${0}">
					${0}
				</a>
			</span>
		`), main_core.Text.encode(collapsedResult), babelHelpers.classPrivateFieldLooseBase(this, _handleExpandResult)[_handleExpandResult].bind(this), main_core.Loc.getMessage('BP_JS_WF_RESULT_MORE'));
	}
	function _replaceBrTag2(text) {
	  return text.replaceAll(/(<br\s?\/?>)+/gm, ' ');
	}
	function _clearTags2(text) {
	  return main_core.Tag.render(_t3 || (_t3 = _`<span>${0}</span>`), text).textContent;
	}
	function _replaceNewLine2(text) {
	  return text.replaceAll(/\n+/gm, ' ');
	}
	function _handleExpandResult2(event) {
	  if (event) {
	    event.preventDefault();
	  }
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node], '--expanded');
	}
	function _isNoRightResult2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === bizproc_types.WorkflowResultStatus.NO_RIGHTS_RESULT;
	}
	function _isNeedCollapse2(result) {
	  return result.length > babelHelpers.classPrivateFieldLooseBase(this.constructor, _LENGTH_LIMIT)[_LENGTH_LIMIT];
	}
	Object.defineProperty(WorkflowResult, _LENGTH_LIMIT, {
	  writable: true,
	  value: 74
	});

	exports.WorkflowResult = WorkflowResult;

}((this.BX.Bizproc.Workflow.Result = this.BX.Bizproc.Workflow.Result || {}),BX,BX.Bizproc));
//# sourceMappingURL=result.bundle.js.map
