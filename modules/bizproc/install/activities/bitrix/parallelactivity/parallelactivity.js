/* eslint-disable */
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	const BizProcActivity = window.BizProcActivity;
	var _copyBranch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyBranch");
	var _addBranch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addBranch");
	var _createBranch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createBranch");
	var _deleteBranch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteBranch");
	var _drawVLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawVLine");
	var _drawMoveBranchButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawMoveBranchButtons");
	var _moveBranchToLeft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moveBranchToLeft");
	var _moveBranchToRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moveBranchToRight");
	var _isEventWithCtrlKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEventWithCtrlKey");
	var _swapBranch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("swapBranch");
	var _swapNodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("swapNodes");
	var _refreshDelButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refreshDelButton");
	var _onHideClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onHideClick");
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _renderActivityContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActivityContent");
	var _drawHideContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawHideContainer");
	var _drawChildrenContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawChildrenContainer");
	var _removeChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeChild");
	var _removeResources = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeResources");
	class ParallelActivity extends BizProcActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _removeResources, {
	      value: _removeResources2
	    });
	    Object.defineProperty(this, _removeChild, {
	      value: _removeChild2
	    });
	    Object.defineProperty(this, _drawChildrenContainer, {
	      value: _drawChildrenContainer2
	    });
	    Object.defineProperty(this, _drawHideContainer, {
	      value: _drawHideContainer2
	    });
	    Object.defineProperty(this, _renderActivityContent, {
	      value: _renderActivityContent2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    Object.defineProperty(this, _onHideClick, {
	      value: _onHideClick2
	    });
	    Object.defineProperty(this, _refreshDelButton, {
	      value: _refreshDelButton2
	    });
	    Object.defineProperty(this, _swapNodes, {
	      value: _swapNodes2
	    });
	    Object.defineProperty(this, _swapBranch, {
	      value: _swapBranch2
	    });
	    Object.defineProperty(this, _isEventWithCtrlKey, {
	      value: _isEventWithCtrlKey2
	    });
	    Object.defineProperty(this, _moveBranchToRight, {
	      value: _moveBranchToRight2
	    });
	    Object.defineProperty(this, _moveBranchToLeft, {
	      value: _moveBranchToLeft2
	    });
	    Object.defineProperty(this, _drawMoveBranchButtons, {
	      value: _drawMoveBranchButtons2
	    });
	    Object.defineProperty(this, _drawVLine, {
	      value: _drawVLine2
	    });
	    Object.defineProperty(this, _deleteBranch, {
	      value: _deleteBranch2
	    });
	    Object.defineProperty(this, _createBranch, {
	      value: _createBranch2
	    });
	    Object.defineProperty(this, _addBranch, {
	      value: _addBranch2
	    });
	    Object.defineProperty(this, _copyBranch, {
	      value: _copyBranch2
	    });
	    this.allowSort = false;
	    this.Type = 'ParallelActivity';
	    this.childActivities = [];
	    // eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
	    this.__parallelActivityInitType = 'SequenceActivity';
	    this.allowSort = false;

	    // region compatibility
	    this.copyBranch = babelHelpers.classPrivateFieldLooseBase(this, _copyBranch)[_copyBranch].bind(this);
	    this.addBranch = babelHelpers.classPrivateFieldLooseBase(this, _addBranch)[_addBranch].bind(this);
	    this.createBranch = babelHelpers.classPrivateFieldLooseBase(this, _createBranch)[_createBranch].bind(this);
	    this.delBranch = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _deleteBranch)[_deleteBranch](event.target.parentNode.parentNode);
	    };
	    this.DrawVLine = babelHelpers.classPrivateFieldLooseBase(this, _drawVLine)[_drawVLine].bind(this);
	    this.RefreshDelButton = babelHelpers.classPrivateFieldLooseBase(this, _refreshDelButton)[_refreshDelButton].bind(this);
	    this.OnHideClick = babelHelpers.classPrivateFieldLooseBase(this, _onHideClick)[_onHideClick].bind(this);
	    this.BizProcActivityDraw = this.Draw;
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.ActivityRemoveChild = this.RemoveChild;
	    this.RemoveChild = babelHelpers.classPrivateFieldLooseBase(this, _removeChild)[_removeChild].bind(this);
	    this.BizProcActivityRemoveResources = this.RemoveResources;
	    this.RemoveResources = babelHelpers.classPrivateFieldLooseBase(this, _removeResources)[_removeResources].bind(this);
	    this.drawMoveElement = babelHelpers.classPrivateFieldLooseBase(this, _drawMoveBranchButtons)[_drawMoveBranchButtons].bind(this);
	    this.moveToRight = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _moveBranchToRight)[_moveBranchToRight](event.target.parentNode.parentNode, event);
	    };
	    this.moveToLeft = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _moveBranchToLeft)[_moveBranchToLeft](event.target.parentNode.parentNode, event);
	    };
	    this.swapBranch = babelHelpers.classPrivateFieldLooseBase(this, _swapBranch)[_swapBranch].bind(this);
	    // endregion
	  }
	}
	function _copyBranch2(childIndex, branchIndex) {
	  babelHelpers.classPrivateFieldLooseBase(this, _createBranch)[_createBranch](this.childActivities[childIndex], branchIndex != null ? branchIndex : childIndex);
	}
	function _addBranch2() {
	  const lastBranchNumber = this.childsContainer.rows[2].cells.length;
	  // eslint-disable-next-line no-underscore-dangle
	  babelHelpers.classPrivateFieldLooseBase(this, _createBranch)[_createBranch](this.__parallelActivityInitType, lastBranchNumber - 1);
	}
	function _createBranch2(childActivityInfo, branchNumber) {
	  const childActivity = window.CreateActivity(childActivityInfo);
	  childActivity.parentActivity = this;
	  childActivity.setCanBeActivated(this.getCanBeActivatedChild());
	  this.childActivities.splice(branchNumber, 0, childActivity);
	  for (let i = 0; i < this.childsContainer.rows.length; i++) {
	    const cell = this.childsContainer.rows[i].insertCell(branchNumber);
	    main_core.Dom.attr(cell, {
	      align: 'center',
	      vAlign: 'top'
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _drawVLine)[_drawVLine](branchNumber);
	  childActivity.Draw(this.childsContainer.rows[2].cells[branchNumber]);
	  babelHelpers.classPrivateFieldLooseBase(this, _refreshDelButton)[_refreshDelButton]();
	}
	function _deleteBranch2(target) {
	  this.RemoveChild(this.childActivities[target.ind]);
	}
	function _drawVLine2(branchNumber) {
	  main_core.Dom.attr(this.childsContainer.rows[0], 'class', 'trLine');
	  main_core.Dom.attr(this.childsContainer.rows[3], 'class', 'trLine');
	  main_core.Dom.style(this.childsContainer.rows[1].cells[branchNumber], 'background', 'url(/bitrix/images/bizproc/act_line_bg.gif) 50% top repeat-y');
	  main_core.Dom.style(this.childsContainer.rows[2].cells[branchNumber], 'background', 'url(/bitrix/images/bizproc/act_line_bg.gif) 50% top repeat-y');
	  main_core.Dom.attr(this.childsContainer.rows[2].cells[branchNumber], 'vAlign', 'top');
	  const childActivityCell = this.childsContainer.rows[1].cells[branchNumber];
	  main_core.Dom.attr(childActivityCell, {
	    height: '20',
	    vAlign: 'bottom'
	  });
	  const {
	    root,
	    remove
	  } = main_core.Tag.render(_t || (_t = _`
			<div style="margin-top: 14px; display: none;">
				<div class="bizproc-designer-parallel-activity__del_br"
					ref="remove"
					title="${0}"
					alt="${0}">
					<div class="ui-icon-set --minus-60"></div>
				</div>
			</div>
		`), main_core.Text.encode(window.BPMESS.PARA_DEL), main_core.Text.encode(window.BPMESS.PARA_DEL));
	  main_core.Event.bind(remove, 'click', babelHelpers.classPrivateFieldLooseBase(this, _deleteBranch)[_deleteBranch].bind(this, childActivityCell));
	  main_core.Dom.append(root, childActivityCell);
	  if (this.allowSort) {
	    babelHelpers.classPrivateFieldLooseBase(this, _drawMoveBranchButtons)[_drawMoveBranchButtons](branchNumber, childActivityCell);
	  }
	}
	function _drawMoveBranchButtons2(branchNumber, cell) {
	  const {
	    root,
	    left,
	    right
	  } = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="move-thread">
				<div
					ref="left" 
					class="ui-icon-set --chevron-left bizproc-designer-parallel-activity-move-arrow"
					title="${0}"
				></div>
				<div
					ref="right"
					class="ui-icon-set --chevron-right bizproc-designer-parallel-activity-move-arrow"
					title="${0}"
				></div>
			</div>
		`), main_core.Text.encode(window.BPMESS.PARA_MOVE_LEFT), main_core.Text.encode(window.BPMESS.PARA_MOVE_RIGHT));
	  main_core.Event.bind(left, 'click', babelHelpers.classPrivateFieldLooseBase(this, _moveBranchToLeft)[_moveBranchToLeft].bind(this, cell));
	  main_core.Event.bind(right, 'click', babelHelpers.classPrivateFieldLooseBase(this, _moveBranchToRight)[_moveBranchToRight].bind(this, cell));
	  main_core.Dom.append(root, cell);
	}
	function _moveBranchToLeft2(cell, event) {
	  const index = cell.ind;
	  if (index !== 0) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isEventWithCtrlKey)[_isEventWithCtrlKey](event)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _copyBranch)[_copyBranch](index, index);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _swapBranch)[_swapBranch](index - 1, index);
	    }
	    window.BPTemplateIsModified = true;
	  }
	}
	function _moveBranchToRight2(cell, event) {
	  const index = cell.ind;
	  if (index !== this.childActivities.length) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isEventWithCtrlKey)[_isEventWithCtrlKey](event)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _copyBranch)[_copyBranch](index, index + 1);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _swapBranch)[_swapBranch](index, index + 1);
	    }
	    window.BPTemplateIsModified = true;
	  }
	}
	function _isEventWithCtrlKey2(event) {
	  return event.ctrlKey === true || event.metaKey === true;
	}
	function _swapBranch2(branchIndex1, branchIndex2) {
	  const tmp = this.childActivities[branchIndex1];
	  this.childActivities[branchIndex1] = this.childActivities[branchIndex2];
	  this.childActivities[branchIndex2] = tmp;
	  for (let i = 1; i < 3; i++) {
	    this.childsContainer.rows[i].cells[branchIndex1].ind = branchIndex2;
	    this.childsContainer.rows[i].cells[branchIndex2].ind = branchIndex1;
	    babelHelpers.classPrivateFieldLooseBase(this, _swapNodes)[_swapNodes](this.childsContainer.rows[i].cells[branchIndex1], this.childsContainer.rows[i].cells[branchIndex2]);
	  }
	}
	function _swapNodes2(node1, node2) {
	  const beforeNode = node2.nextElementSibling;
	  node1.replaceWith(node2);
	  if (beforeNode) {
	    main_core.Dom.insertBefore(node1, beforeNode);
	  } else {
	    main_core.Dom.append(node1, node2.parentNode);
	  }
	}
	function _refreshDelButton2() {
	  this.childActivities.forEach((child, index) => {
	    main_core.Dom.style(this.childsContainer.rows[1].cells[index].childNodes[0], 'display', this.childActivities.length > 2 ? 'block' : 'none');
	    this.childsContainer.rows[1].cells[index].ind = index;
	  });
	}
	function _onHideClick2() {
	  // eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
	  this.Properties._DesMinimized = this.Properties._DesMinimized === 'Y' ? 'N' : 'Y';
	  BX.Dom.toggle(this.childsContainer);
	  BX.Dom.toggle(this.hideContainer);
	}
	function _draw2(wrapper) {
	  if (this.childActivities.length === 0) {
	    this.childActivities = [
	    // eslint-disable-next-line no-underscore-dangle
	    window.CreateActivity(this.__parallelActivityInitType),
	    // eslint-disable-next-line no-underscore-dangle
	    window.CreateActivity(this.__parallelActivityInitType)];
	    this.childActivities[0].parentActivity = this;
	    this.childActivities[0].setCanBeActivated(this.getCanBeActivatedChild());
	    this.childActivities[1].parentActivity = this;
	    this.childActivities[1].setCanBeActivated(this.getCanBeActivatedChild());
	  }
	  this.container = main_core.Tag.render(_t3 || (_t3 = _`<div class="parallelcontainer">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _renderActivityContent)[_renderActivityContent]());
	  main_core.Dom.append(this.container, wrapper);
	  this.BizProcActivityDraw(this.container);
	  this.activityContent = null;
	  main_core.Dom.style(this.div, {
	    position: 'relative',
	    top: '12px'
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _drawHideContainer)[_drawHideContainer]();
	  babelHelpers.classPrivateFieldLooseBase(this, _drawChildrenContainer)[_drawChildrenContainer]();

	  // eslint-disable-next-line no-underscore-dangle
	  if (this.Properties._DesMinimized === 'Y') {
	    main_core.Dom.hide(this.childsContainer);
	  } else {
	    main_core.Dom.hide(this.hideContainer);
	  }
	  this.childActivities.forEach((child, index) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _drawVLine)[_drawVLine](index);
	    child.Draw(this.childsContainer.rows[2].cells[index]);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _refreshDelButton)[_refreshDelButton]();
	}
	function _renderActivityContent2() {
	  if (!this.activityContent) {
	    var _this$Icon;
	    const icon = (_this$Icon = this.Icon) != null ? _this$Icon : '/bitrix/images/bizproc/act_icon.gif';
	    const {
	      root,
	      add
	    } = main_core.Tag.render(_t4 || (_t4 = _`
				<table 
					cellpadding="0"
					cellspacing="0"
					border="0"
					style="width: 100%; font-size: 11px;"
				>
					<tbody>
						<tr>
							<td 
								align="center"
								valign="center"
								style="
									background: url('${0}') 2px 2px no-repeat;
									height: 24px;
									width: 24px;
								"
							></td>
							<td align="left" valign="center">
								${0}
							</td>
							<td
								ref="add"
								class="bizproc-designer-parallel-activity-add-branch-icon"
								title="${0}"
							></td>
						</tr>
					</tbody>
				</table>
			`), icon, main_core.Text.encode(this.Properties.Title), main_core.Text.encode(window.BPMESS.PARA_ADD));
	    main_core.Event.bind(add, 'click', babelHelpers.classPrivateFieldLooseBase(this, _addBranch)[_addBranch].bind(this));
	    this.activityContent = root;
	    return this.activityContent;
	  }
	  return '';
	}
	function _drawHideContainer2() {
	  this.hideContainer = main_core.Tag.render(_t5 || (_t5 = _`
			<div 
				style="
					background: #FFFFFF;
					border: 1px #CCCCCC dotted;
					width: 250px;
					color: #AAAAAA;
					padding: 13px 0 3px 0;
					cursor: pointer;
				"
			>${0}</div>
		`), main_core.Text.encode(window.BPMESS.PARA_MIN));
	  main_core.Event.bind(this.hideContainer, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onHideClick)[_onHideClick].bind(this));
	  main_core.Dom.append(this.hideContainer, this.container);
	}
	function _drawChildrenContainer2() {
	  // eslint-disable-next-line no-underscore-dangle
	  this.childsContainer = window._crt(4, this.childActivities.length);
	  main_core.Dom.attr(this.childsContainer, 'id', main_core.Text.encode(this.Name));
	  main_core.Dom.style(this.childsContainer, 'background', '#FFFFFF');
	  main_core.Dom.append(this.childsContainer, this.container);
	}
	function _removeChild2(child) {
	  const index = this.childActivities.indexOf(child);
	  if (index !== -1) {
	    this.ActivityRemoveChild(child);
	    if (this.childsContainer) {
	      this.childsContainer.rows[0].deleteCell(index);
	      this.childsContainer.rows[1].deleteCell(index);
	      this.childsContainer.rows[2].deleteCell(index);
	      this.childsContainer.rows[3].deleteCell(index);
	      babelHelpers.classPrivateFieldLooseBase(this, _refreshDelButton)[_refreshDelButton]();
	    }
	  }
	}
	function _removeResources2() {
	  this.BizProcActivityRemoveResources();
	  if (this.container && this.container.parentNode) {
	    main_core.Dom.remove(this.container);
	    this.container = null;
	    this.childsContainer = null;
	  }
	}

	exports.ParallelActivity = ParallelActivity;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=parallelactivity.js.map
