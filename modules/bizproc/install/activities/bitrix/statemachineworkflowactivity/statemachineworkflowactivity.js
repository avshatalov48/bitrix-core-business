/* eslint-disable */
(function (exports,main_core,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	var _SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD");
	var _CHILD_ACTIVITY_BORDER = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("CHILD_ACTIVITY_BORDER");
	var _ARROW_SIZE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ARROW_SIZE");
	var _ARROW_HALF_SIZE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ARROW_HALF_SIZE");
	var _ARROW_QUARTER_SIZE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ARROW_QUARTER_SIZE");
	var _ARROW_ONE_EIGHTH_SIZE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ARROW_ONE_EIGHTH_SIZE");
	var _tableLeftPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tableLeftPosition");
	var _tableRightPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tableRightPosition");
	var _linesCenter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linesCenter");
	var _linesLeft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linesLeft");
	var _linesRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linesRight");
	var _serialize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("serialize");
	var _removeChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeChild");
	var _replaceChild = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("replaceChild");
	var _removeResources = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeResources");
	var _paintWholeLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("paintWholeLine");
	var _drawAllLines = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawAllLines");
	var _removeAllLines = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeAllLines");
	var _findTargetStateNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findTargetStateNames");
	var _drawLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drawLine");
	var _disposeLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeLine");
	var _disposeLinesFromRightToLeft = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeLinesFromRightToLeft");
	var _disposeLinesFromLeftToLeftColumn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeLinesFromLeftToLeftColumn2");
	var _disposeLinesFromLeftToLeftColumn3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeLinesFromLeftToLeftColumn1");
	var _disposeLinesFromLeftToRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeLinesFromLeftToRight");
	var _disposeArrowRightOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeArrowRightOut");
	var _disposeArrowRightIn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeArrowRightIn");
	var _disposeArrowLeftOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeArrowLeftOut");
	var _disposeArrowLeftIn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disposeArrowLeftIn");
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _renderAddNewStateButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAddNewStateButton");
	var _addNewState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addNewState");
	var _reCheckLineStatuses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reCheckLineStatuses");
	class StateMachineWorkflowActivity extends window.BizProcActivity {
	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private

	  constructor() {
	    super();
	    Object.defineProperty(this, _reCheckLineStatuses, {
	      value: _reCheckLineStatuses2
	    });
	    Object.defineProperty(this, _addNewState, {
	      value: _addNewState2
	    });
	    Object.defineProperty(this, _renderAddNewStateButton, {
	      value: _renderAddNewStateButton2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    Object.defineProperty(this, _disposeArrowLeftIn, {
	      value: _disposeArrowLeftIn2
	    });
	    Object.defineProperty(this, _disposeArrowLeftOut, {
	      value: _disposeArrowLeftOut2
	    });
	    Object.defineProperty(this, _disposeArrowRightIn, {
	      value: _disposeArrowRightIn2
	    });
	    Object.defineProperty(this, _disposeArrowRightOut, {
	      value: _disposeArrowRightOut2
	    });
	    Object.defineProperty(this, _disposeLinesFromLeftToRight, {
	      value: _disposeLinesFromLeftToRight2
	    });
	    Object.defineProperty(this, _disposeLinesFromLeftToLeftColumn3, {
	      value: _disposeLinesFromLeftToLeftColumn4
	    });
	    Object.defineProperty(this, _disposeLinesFromLeftToLeftColumn, {
	      value: _disposeLinesFromLeftToLeftColumn2
	    });
	    Object.defineProperty(this, _disposeLinesFromRightToLeft, {
	      value: _disposeLinesFromRightToLeft2
	    });
	    Object.defineProperty(this, _disposeLine, {
	      value: _disposeLine2
	    });
	    Object.defineProperty(this, _drawLine, {
	      value: _drawLine2
	    });
	    Object.defineProperty(this, _findTargetStateNames, {
	      value: _findTargetStateNames2
	    });
	    Object.defineProperty(this, _removeAllLines, {
	      value: _removeAllLines2
	    });
	    Object.defineProperty(this, _drawAllLines, {
	      value: _drawAllLines2
	    });
	    Object.defineProperty(this, _paintWholeLine, {
	      value: _paintWholeLine2
	    });
	    Object.defineProperty(this, _removeResources, {
	      value: _removeResources2
	    });
	    Object.defineProperty(this, _replaceChild, {
	      value: _replaceChild2
	    });
	    Object.defineProperty(this, _removeChild, {
	      value: _removeChild2
	    });
	    Object.defineProperty(this, _serialize, {
	      value: _serialize2
	    });
	    this.Table = null;
	    Object.defineProperty(this, _tableLeftPosition, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _tableRightPosition, {
	      writable: true,
	      value: null
	    });
	    this.__l = [];
	    this.StatusArrows = [];
	    Object.defineProperty(this, _linesCenter, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _linesLeft, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _linesRight, {
	      writable: true,
	      value: 0
	    });
	    this.classname = 'StateMachineWorkflowActivity';

	    // region compatibility
	    this.SerializeStateMachineWorkflowActivity = this.Serialize;
	    this.Serialize = babelHelpers.classPrivateFieldLooseBase(this, _serialize)[_serialize].bind(this);
	    this.LineMouseOver = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _paintWholeLine)[_paintWholeLine](event.target.id, '#e00', 'over');
	    };
	    this.LineMouseOut = event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _paintWholeLine)[_paintWholeLine](event.target.id, 'rgb(192, 193, 195)', 'out');
	    };
	    this.DrawLines = babelHelpers.classPrivateFieldLooseBase(this, _drawAllLines)[_drawAllLines].bind(this);
	    this.FindSetState = babelHelpers.classPrivateFieldLooseBase(this, _findTargetStateNames)[_findTargetStateNames].bind(this);
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.AddStatus = babelHelpers.classPrivateFieldLooseBase(this, _addNewState)[_addNewState].bind(this);
	    this.ReCheckPosition = babelHelpers.classPrivateFieldLooseBase(this, _reCheckLineStatuses)[_reCheckLineStatuses].bind(this);
	    this.RemoveChildStateMachine = this.RemoveChild;
	    this.RemoveChild = babelHelpers.classPrivateFieldLooseBase(this, _removeChild)[_removeChild].bind(this);
	    this.ReplaceChild = babelHelpers.classPrivateFieldLooseBase(this, _replaceChild)[_replaceChild].bind(this);
	    this.RemoveResourcesActivity = this.RemoveResources;
	    this.RemoveResources = babelHelpers.classPrivateFieldLooseBase(this, _removeResources)[_removeResources].bind(this);
	    // endregion
	  }
	}
	function _serialize2() {
	  if (this.childActivities.length > 0) {
	    this.Properties.InitialStateName = this.childActivities[0].Name;
	  }
	  return this.SerializeStateMachineWorkflowActivity();
	}
	function _removeChild2(activity) {
	  this.RemoveChildStateMachine(activity);
	  main_core.Dom.remove(this.Table);
	  this.Table = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _tableLeftPosition)[_tableLeftPosition] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _tableRightPosition)[_tableRightPosition] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw](this.statediv);
	}
	function _replaceChild2(activity1, activity2) {
	  const index1 = this.childActivities.indexOf(activity1);
	  const index2 = this.childActivities.indexOf(activity2);
	  if (index1 < 0 || index2 < 0) {
	    return;
	  }
	  this.childActivities[index1] = activity2;
	  this.childActivities[index2] = activity1;
	  window.BPTemplateIsModified = true;
	  babelHelpers.classPrivateFieldLooseBase(this, _removeResources)[_removeResources]();
	  babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw](this.statediv);
	}
	function _removeResources2() {
	  this.RemoveResourcesActivity();
	  if (this.Table) {
	    main_core.Dom.remove(this.Table);
	    this.Table = null;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _tableLeftPosition)[_tableLeftPosition] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _tableRightPosition)[_tableRightPosition] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _removeAllLines)[_removeAllLines]();
	}
	function _paintWholeLine2(id, color, type) {
	  const lineId = id.slice(0, Math.max(0, id.length - 2));
	  for (let i = 1; i <= 3; i++) {
	    const line = document.getElementById(`${lineId}.${i}`);
	    const zIndex = main_core.Text.toInteger(main_core.Dom.style(line, 'zIndex')) + (type === 'over' ? 1000 : -1000);
	    main_core.Dom.style(line, {
	      zIndex,
	      backgroundColor: color
	    });
	  }
	}
	function _drawAllLines2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _removeAllLines)[_removeAllLines]();
	  babelHelpers.classPrivateFieldLooseBase(this, _findTargetStateNames)[_findTargetStateNames](false, this);
	  for (const [index, pair] of this.StatusArrows.entries()) {
	    const fromPosition = window.ActGetRealPos(document.getElementById(pair[0]));
	    const toPosition = window.ActGetRealPos(document.getElementById(pair[1]));
	    if (fromPosition === false || toPosition === false || fromPosition.left <= 0 || toPosition.left <= 0) {
	      continue;
	    }
	    const {
	      d0,
	      d1,
	      d2,
	      d3,
	      d4
	    } = babelHelpers.classPrivateFieldLooseBase(this, _drawLine)[_drawLine](index, pair);
	    fromPosition.right += babelHelpers.classPrivateFieldLooseBase(this.constructor, _SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD)[_SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD];
	    fromPosition.left -= babelHelpers.classPrivateFieldLooseBase(this.constructor, _SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD)[_SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD];
	    babelHelpers.classPrivateFieldLooseBase(this, _disposeLine)[_disposeLine]([d0, d1, d2, d3, d4], fromPosition, toPosition, pair[0]);

	    // eslint-disable-next-line no-underscore-dangle
	    this.__l.push([d0, d1, d2, d3, d4]);
	  }
	}
	function _removeAllLines2() {
	  // eslint-disable-next-line no-underscore-dangle
	  this.__l.forEach(activityLines => activityLines.forEach(line => main_core.Dom.remove(line)));
	  // eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
	  this.__l = [];
	  this.StatusArrows = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _linesCenter)[_linesCenter] = 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _linesLeft)[_linesLeft] = 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _linesRight)[_linesRight] = 0;
	}
	function _findTargetStateNames2(activityName, targetActivity) {
	  if (targetActivity.Type === 'SetStateActivity') {
	    if (Object.hasOwn(targetActivity.Properties, 'TargetStateName')) {
	      this.StatusArrows.push([activityName, targetActivity.Properties.TargetStateName]);
	    }
	    return;
	  }
	  targetActivity.childActivities.forEach(activity => {
	    babelHelpers.classPrivateFieldLooseBase(this, _findTargetStateNames)[_findTargetStateNames](targetActivity.Type === 'StateActivity' ? activity.Name : activityName, activity);
	  });
	}
	function _drawLine2(index, pair) {
	  const {
	    root,
	    d0,
	    d4,
	    d1,
	    d2,
	    d3
	  } = main_core.Tag.render(_t || (_t = _`
			<div>
				<div 
					ref="d0"
					class="bizproc-designer-state-machine-workflow-activity-arrow-wrapper"
					style="z-index: ${0};"
				></div>
				<div
					ref="d4"
					class="bizproc-designer-state-machine-workflow-activity-arrow-wrapper"
					style="z-index: ${0};"
				></div>
				<div 
					ref="d1"
					id="${0}"
					class="bizproc-designer-state-machine-workflow-activity-horizontal-line"
					style="z-index: ${0};"
				></div>
				<div 
					ref="d2"
					id="${0}"
					class="bizproc-designer-state-machine-workflow-activity-vertical-line"
					style="z-index: ${0};"
				></div>
				<div 
					ref="d3"
					id="${0}"
					class="bizproc-designer-state-machine-workflow-activity-horizontal-line"
					style="z-index: ${0};"
				></div>
			</div>
		`), 14 + index * 10, 14 + index * 10, `${pair[0]}-${pair[1]}.1`, 15 + index * 10, `${pair[0]}-${pair[1]}.2`, 15 + index * 10, `${pair[0]}-${pair[1]}.3`, 15 + index * 10);
	  main_core.Event.bind(d1, 'mouseover', this.LineMouseOver);
	  main_core.Event.bind(d1, 'mouseout', this.LineMouseOut);
	  main_core.Event.bind(d2, 'mouseover', this.LineMouseOver);
	  main_core.Event.bind(d2, 'mouseout', this.LineMouseOut);
	  main_core.Event.bind(d3, 'mouseover', this.LineMouseOver);
	  main_core.Event.bind(d3, 'mouseout', this.LineMouseOut);
	  main_core.Dom.append(root, this.Table.parentNode);
	  return {
	    d0,
	    d4,
	    d1,
	    d2,
	    d3
	  };
	}
	function _disposeLine2(lines, fromPosition, toPosition, pair0) {
	  if (main_core.Text.toInteger(fromPosition.right) < main_core.Text.toInteger(toPosition.left)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _disposeLinesFromRightToLeft)[_disposeLinesFromRightToLeft](lines, fromPosition, toPosition);
	  } else if (main_core.Text.toInteger(fromPosition.left) === main_core.Text.toInteger(toPosition.left)) {
	    const columnNode = document.getElementById(pair0).closest('[data-column]');
	    if (columnNode && main_core.Dom.attr(columnNode, 'data-column') === 2) {
	      babelHelpers.classPrivateFieldLooseBase(this, _disposeLinesFromLeftToLeftColumn)[_disposeLinesFromLeftToLeftColumn](lines, fromPosition, toPosition);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _disposeLinesFromLeftToLeftColumn3)[_disposeLinesFromLeftToLeftColumn3](lines, fromPosition, toPosition);
	    }
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _disposeLinesFromLeftToRight)[_disposeLinesFromLeftToRight](lines, fromPosition, toPosition);
	  }
	}
	function _disposeLinesFromRightToLeft2(lines, fromPosition, toPosition) {
	  ++babelHelpers.classPrivateFieldLooseBase(this, _linesCenter)[_linesCenter];
	  const countLinesCenter = -50 + babelHelpers.classPrivateFieldLooseBase(this, _linesCenter)[_linesCenter] % 6 * 6;
	  const width = toPosition.left - fromPosition.right;
	  const direction = -1;
	  const [d0, d1, d2, d3, d4] = lines;
	  const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
	  const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
	  const lineWidth = width / 2 + countLinesCenter;
	  const lineHeight = 2;
	  const linePositionRight = fromPosition.right + lineWidth;
	  main_core.Dom.style(d1, {
	    top: `${quarterFromPositionTop}px`,
	    left: `${fromPosition.right + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] + 1}px`,
	    width: `${lineWidth}px`
	  });
	  main_core.Dom.style(d2, {
	    top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop}px`,
	    left: `${linePositionRight}px`,
	    height: `${fromPosition.top > toPosition.top ? fromPosition.top - toPosition.top + lineHeight * 2 - 9 : toPosition.top - fromPosition.top + lineHeight * 2 + 1}px`
	  });
	  main_core.Dom.style(d3, {
	    top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
	    left: `${linePositionRight}px`,
	    width: `${width / 2 + direction * countLinesCenter + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 + 2}px`
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowRightOut)[_disposeArrowRightOut](d0, fromPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] + 1, quarterFromPositionTop - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowRightIn)[_disposeArrowRightIn](d4, toPosition.left - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2, toPosition.top + Math.floor(toPosition.height / 2) - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	}
	function _disposeLinesFromLeftToLeftColumn2(lines, fromPosition, toPosition) {
	  ++babelHelpers.classPrivateFieldLooseBase(this, _linesRight)[_linesRight];
	  const countLinesRight = -50 + babelHelpers.classPrivateFieldLooseBase(this, _linesRight)[_linesRight] % 10 * 10;
	  const width = 150;
	  const [d0, d1, d2, d3, d4] = lines;
	  const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
	  const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
	  const lineWidth = width / 2 + countLinesRight;
	  const lineHeight = 2;
	  main_core.Dom.style(d1, {
	    top: `${quarterFromPositionTop}px`,
	    left: `${fromPosition.right + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 + 2}px`,
	    width: `${lineWidth - 2}px`
	  });
	  main_core.Dom.style(d2, {
	    top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
	    left: `${fromPosition.right + lineWidth}px`,
	    height: `${fromPosition.top > toPosition.top ? fromPosition.top - toPosition.top - lineHeight * 2 : toPosition.top - fromPosition.top - lineHeight * 2 + 7}px`
	  });
	  main_core.Dom.style(d3, {
	    top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2) - 1}px`,
	    left: `${toPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 - 2}px`,
	    width: `${lineWidth + 6}px`
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowRightOut)[_disposeArrowRightOut](d0, fromPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2, quarterFromPositionTop - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowLeftIn)[_disposeArrowLeftIn](d4, toPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 + 1, toPosition.top + Math.floor(toPosition.height / 2) - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	}
	function _disposeLinesFromLeftToLeftColumn4(lines, fromPosition, toPosition) {
	  ++babelHelpers.classPrivateFieldLooseBase(this, _linesLeft)[_linesLeft];
	  const countLinesLeft = -50 + babelHelpers.classPrivateFieldLooseBase(this, _linesLeft)[_linesLeft] % 10 * 10;
	  const width = 150;
	  const [d0, d1, d2, d3, d4] = lines;
	  const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
	  const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
	  const lineWidth = width / 2 - countLinesLeft;
	  const lineHeight = 2;
	  main_core.Dom.style(d1, {
	    top: `${quarterFromPositionTop}px`,
	    left: `${fromPosition.left - lineWidth - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] - 2}px`,
	    width: `${lineWidth}px`
	  });
	  main_core.Dom.style(d2, {
	    top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
	    left: `${fromPosition.left - width / 2 + countLinesLeft - 3}px`,
	    height: `${fromPosition.top > toPosition.top ? fromPosition.top - toPosition.top - lineHeight * 2 : toPosition.top - fromPosition.top - lineHeight * 2 + 7}px`
	  });
	  main_core.Dom.style(d3, {
	    top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
	    left: `${fromPosition.left - width / 2 + countLinesLeft - 1}px`,
	    width: `${lineWidth + 3}px`
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowLeftOut)[_disposeArrowLeftOut](d0, fromPosition.left - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2, quarterFromPositionTop - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowRightIn)[_disposeArrowRightIn](d4, toPosition.left - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2, toPosition.top + Math.floor(toPosition.height / 2) - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	}
	function _disposeLinesFromLeftToRight2(lines, fromPosition, toPosition) {
	  ++babelHelpers.classPrivateFieldLooseBase(this, _linesCenter)[_linesCenter];
	  const countLinesCenter = -50 + babelHelpers.classPrivateFieldLooseBase(this, _linesCenter)[_linesCenter] % 6 * 6;
	  const width = fromPosition.left - toPosition.right;
	  const direction = -1;
	  const [d0, d1, d2, d3, d4] = lines;
	  const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
	  const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
	  const lineWidth = width / 2 + countLinesCenter;
	  const lineHeight = 2;
	  main_core.Dom.style(d1, {
	    top: `${quarterFromPositionTop}px`,
	    left: `${fromPosition.left - lineWidth - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] - 1}px`,
	    width: `${lineWidth}px`
	  });
	  main_core.Dom.style(d2, {
	    top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
	    left: `${toPosition.right + width / 2 - countLinesCenter - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2}px`,
	    height: `${fromPosition.top > toPosition.top ? fromPosition.top - toPosition.top - lineHeight * 2 : toPosition.top - fromPosition.top - lineHeight * 2 + 7}px`
	  });
	  main_core.Dom.style(d3, {
	    top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
	    left: `${toPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 - 2}px`,
	    width: `${width / 2 + direction * countLinesCenter + 2}px`
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowLeftOut)[_disposeArrowLeftOut](d0, fromPosition.left - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_HALF_SIZE)[_ARROW_HALF_SIZE] - babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2, quarterFromPositionTop - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	  babelHelpers.classPrivateFieldLooseBase(this, _disposeArrowLeftIn)[_disposeArrowLeftIn](d4, toPosition.right - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE] + babelHelpers.classPrivateFieldLooseBase(this.constructor, _CHILD_ACTIVITY_BORDER)[_CHILD_ACTIVITY_BORDER] * 2 + 1, toPosition.top + Math.floor(toPosition.height / 2) - babelHelpers.classPrivateFieldLooseBase(this.constructor, _ARROW_QUARTER_SIZE)[_ARROW_QUARTER_SIZE]);
	}
	function _disposeArrowRightOut2(d0, left, top) {
	  const arrow = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-icon-set --chevron-right bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`));
	  main_core.Dom.append(arrow, d0);
	  main_core.Dom.style(d0, {
	    left: `${left}px`,
	    top: `${top}px`
	  });
	}
	function _disposeArrowRightIn2(d4, left, top) {
	  const arrow = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-icon-set --chevron-right bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`));
	  main_core.Dom.append(arrow, d4);
	  main_core.Dom.style(d4, {
	    left: `${left}px`,
	    top: `${top}px`,
	    backgroundColor: 'white',
	    maxWidth: '9px'
	  });
	}
	function _disposeArrowLeftOut2(d0, left, top) {
	  const arrow = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-icon-set --chevron-left bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`));
	  main_core.Dom.append(arrow, d0);
	  main_core.Dom.style(d0, {
	    left: `${left}px`,
	    top: `${top}px`,
	    maxWidth: '12px'
	  });
	}
	function _disposeArrowLeftIn2(d4, left, top) {
	  const arrow = main_core.Tag.render(_t5 || (_t5 = _`
			<div 
				class="ui-icon-set --chevron-left bizproc-designer-state-machine-workflow-activity-arrow"
				style="margin-left: -10px"
			></div>
		`));
	  main_core.Dom.append(arrow, d4);
	  main_core.Dom.style(d4, {
	    left: `${left}px`,
	    top: `${top}px`,
	    backgroundColor: 'white'
	  });
	}
	function _draw2(wrapper) {
	  this.statediv = wrapper;
	  const rows = Array.from({
	    length: this.childActivities.length
	  }, () => {
	    return main_core.Tag.render(_t6 || (_t6 = _`
				<tr>
					<td align="right" valign="center" data-column="1"></td>
					<td align="center" valign="center"></td>
					<td align="left" valign="center" data-column="2"></td>
				</tr>
			`));
	  });
	  const addNewStateButtonCell = this.childActivities.length % 2 * 2;
	  this.Table = main_core.Tag.render(_t7 || (_t7 = _`
			<table cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
				<tbody>
					${0}
					<tr>
						<td align="right" valign="center" width="350px">
							${0}
						</td>
						<td align="center" valign="center" width="150px"></td>
						<td align="left" valign="center">
							${0}
						</td>
					</tr>
				</tbody>
			</table>
		`), rows, addNewStateButtonCell === 0 ? babelHelpers.classPrivateFieldLooseBase(this, _renderAddNewStateButton)[_renderAddNewStateButton]() : '&nbsp', addNewStateButtonCell === 2 ? babelHelpers.classPrivateFieldLooseBase(this, _renderAddNewStateButton)[_renderAddNewStateButton]() : '&nbsp');
	  main_core.Dom.append(this.Table, wrapper);
	  this.childActivities.forEach((activity, index) => activity.Draw(this.Table.rows[index].cells[index % 2 * 2]));
	  babelHelpers.classPrivateFieldLooseBase(this, _reCheckLineStatuses)[_reCheckLineStatuses](true);
	}
	function _renderAddNewStateButton2() {
	  return new ui_buttons.Button({
	    text: window.BPMESS.STM_ADD_STATUS_1,
	    size: ui_buttons.Button.Size.EXTRA_SMALL,
	    color: ui_buttons.Button.Color.LIGHT_BORDER,
	    noCaps: true,
	    onclick: (button, event) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _addNewState)[_addNewState](event, button);
	    }
	  }).render();
	}
	function _addNewState2(event, button) {
	  event.preventDefault();
	  const numberChildActivities = this.childActivities.length;
	  const activity = window.CreateActivity('StateActivity');
	  this.childActivities.push(activity);
	  activity.parentActivity = this;
	  activity.Draw(this.Table.rows[numberChildActivities].cells[numberChildActivities % 2 * 2]);
	  const row = this.Table.insertRow(-1);
	  row.insertCell(-1).align = 'right';
	  row.insertCell(-1).align = 'center';
	  row.insertCell(-1).align = 'left';
	  if (button instanceof ui_buttons.Button) {
	    button.renderTo(this.Table.rows[numberChildActivities + 1].cells[(numberChildActivities + 1) % 2 * 2]);
	  } else {
	    main_core.Dom.append(event.target, this.Table.rows[numberChildActivities + 1].cells[(numberChildActivities + 1) % 2 * 2]);
	  }
	  activity.Settings();
	}
	function _reCheckLineStatuses2(needDrawLines) {
	  if (main_core.Dom.style(this.Table, 'display') === 'none') {
	    return;
	  }
	  const tablePosition = main_core.Dom.getPosition(this.Table);
	  if (needDrawLines || babelHelpers.classPrivateFieldLooseBase(this, _tableLeftPosition)[_tableLeftPosition] !== tablePosition.left || babelHelpers.classPrivateFieldLooseBase(this, _tableRightPosition)[_tableRightPosition] !== tablePosition.right) {
	    babelHelpers.classPrivateFieldLooseBase(this, _tableLeftPosition)[_tableLeftPosition] = tablePosition.left;
	    babelHelpers.classPrivateFieldLooseBase(this, _tableRightPosition)[_tableRightPosition] = tablePosition.right;
	    babelHelpers.classPrivateFieldLooseBase(this, _drawAllLines)[_drawAllLines]();
	  }
	  setTimeout(babelHelpers.classPrivateFieldLooseBase(this, _reCheckLineStatuses)[_reCheckLineStatuses].bind(this), 1000);
	}
	Object.defineProperty(StateMachineWorkflowActivity, _SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD, {
	  writable: true,
	  value: 5
	});
	Object.defineProperty(StateMachineWorkflowActivity, _CHILD_ACTIVITY_BORDER, {
	  writable: true,
	  value: 1
	});
	Object.defineProperty(StateMachineWorkflowActivity, _ARROW_SIZE, {
	  writable: true,
	  value: 18
	});
	Object.defineProperty(StateMachineWorkflowActivity, _ARROW_HALF_SIZE, {
	  writable: true,
	  value: Math.floor(babelHelpers.classPrivateFieldLooseBase(StateMachineWorkflowActivity, _ARROW_SIZE)[_ARROW_SIZE] / 2)
	});
	Object.defineProperty(StateMachineWorkflowActivity, _ARROW_QUARTER_SIZE, {
	  writable: true,
	  value: Math.floor(babelHelpers.classPrivateFieldLooseBase(StateMachineWorkflowActivity, _ARROW_SIZE)[_ARROW_SIZE] / 4)
	});
	Object.defineProperty(StateMachineWorkflowActivity, _ARROW_ONE_EIGHTH_SIZE, {
	  writable: true,
	  value: Math.floor(babelHelpers.classPrivateFieldLooseBase(StateMachineWorkflowActivity, _ARROW_SIZE)[_ARROW_SIZE] / 8)
	});

	exports.StateMachineWorkflowActivity = StateMachineWorkflowActivity;

}((this.window = this.window || {}),BX,BX.UI));
//# sourceMappingURL=statemachineworkflowactivity.js.map
