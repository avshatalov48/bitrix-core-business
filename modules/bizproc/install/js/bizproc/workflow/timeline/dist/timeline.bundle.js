/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,bizproc_document,bizproc_types,ui_icons_b24,ui_textcrop,main_popup,main_date,bizproc_task,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _errors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errors");
	class ErrorsView {
	  constructor(props) {
	    Object.defineProperty(this, _errors, {
	      writable: true,
	      value: []
	    });
	    if (main_core.Type.isArrayFilled(props.errors)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors] = props.errors;
	    }
	  }
	  render() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="bizproc-workflow-timeline_error-wrapper">
				<div class="bizproc-workflow-timeline_error-inner">
					${0}
					<div class="bizproc-workflow-timeline_error-img"></div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors].map(({
	      message
	    }) => main_core.Tag.render(_t2 || (_t2 = _`
						<p class="bizproc-workflow-timeline_error-text">${0}</p>
					`), main_core.Text.encode(message))));
	  }
	  renderTo(target) {
	    main_core.Dom.append(this.render(), target);
	  }
	}

	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	class TimelineTask {
	  constructor(data) {
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: {}
	    });
	    if (main_core.Type.isPlainObject(data)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = data;
	    }
	  }
	  canView() {
	    return main_core.Type.isBoolean(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].canView) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].canView : false;
	  }
	  get status() {
	    return new bizproc_task.TaskStatus(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].status);
	  }
	  get id() {
	    return main_core.Type.isInteger(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id : 0;
	  }
	  get name() {
	    return main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].name) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].name : '';
	  }
	  get modified() {
	    return main_core.Type.isInteger(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].modified) ? Math.max(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].modified, 0) : 0;
	  }
	  get users() {
	    return main_core.Type.isArray(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].users) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].users : [];
	  }
	  get executionTime() {
	    return main_core.Type.isInteger(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].executionTime) ? Math.max(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].executionTime, 0) : null;
	  }
	  get approveType() {
	    return main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].approveType) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].approveType : '';
	  }
	  get url() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].url) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].url : null;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	const TOO_LONG_PROCESS_DURATION = 60 * 60 * 24 * 3; // Three days
	var _task = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("task");
	var _userId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userId");
	var _taskNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("taskNumber");
	var _dateFormat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFormat");
	var _dateFormatShort = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFormatShort");
	var _users = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("users");
	var _renderContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _renderButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButton");
	var _renderStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStatus");
	var _getStatusName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStatusName");
	var _renderUsers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUsers");
	var _renderUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUser");
	var _renderExecutionTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExecutionTime");
	var _renderAccessDenied = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAccessDenied");
	class TimelineTaskView {
	  constructor(props) {
	    Object.defineProperty(this, _renderAccessDenied, {
	      value: _renderAccessDenied2
	    });
	    Object.defineProperty(this, _renderExecutionTime, {
	      value: _renderExecutionTime2
	    });
	    Object.defineProperty(this, _renderUser, {
	      value: _renderUser2
	    });
	    Object.defineProperty(this, _renderUsers, {
	      value: _renderUsers2
	    });
	    Object.defineProperty(this, _getStatusName, {
	      value: _getStatusName2
	    });
	    Object.defineProperty(this, _renderStatus, {
	      value: _renderStatus2
	    });
	    Object.defineProperty(this, _renderButton, {
	      value: _renderButton2
	    });
	    Object.defineProperty(this, _renderContent, {
	      value: _renderContent2
	    });
	    Object.defineProperty(this, _task, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _taskNumber, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _dateFormat, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateFormatShort, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _users, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _task)[_task] = props.task;
	    babelHelpers.classPrivateFieldLooseBase(this, _dateFormat)[_dateFormat] = props.dateFormat;
	    babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort)[_dateFormatShort] = props.dateFormatShort;
	    babelHelpers.classPrivateFieldLooseBase(this, _users)[_users] = props.users;
	    if (main_core.Type.isNumber(props.taskNumber) && props.taskNumber > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _taskNumber)[_taskNumber] = props.taskNumber;
	    }
	    if (main_core.Type.isNumber(props.userId) && props.userId > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId] = props.userId;
	    }
	  }
	  renderTo(target) {
	    main_core.Dom.append(this.render(), target);
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].canView() ? babelHelpers.classPrivateFieldLooseBase(this, _renderContent)[_renderContent]() : babelHelpers.classPrivateFieldLooseBase(this, _renderAccessDenied)[_renderAccessDenied]();
	  }
	}
	function _renderContent2() {
	  const isWaiting = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].status.isWaiting();
	  return main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="bizproc-workflow-timeline-item ${0}">
				<div class="bizproc-workflow-timeline-item-inner">
					<div>
						<span class="bizproc-workflow-timeline-icon ${0}">
							${0}
						</span>
						<div class="bizproc-workflow-timeline-title">
							<span>${0}</span>
							${0}
						</div>
					</div>
					<div class="bizproc-workflow-timeline-subject">
						${0}
					</div>
					<div class="bizproc-workflow-timeline-content">
						${0}
						${0}
						${0}
					</div>
				</div>
			</div>
		`), isWaiting ? '--processing' : '', isWaiting ? '--processing' : '--success', isWaiting ? '' : babelHelpers.classPrivateFieldLooseBase(this, _taskNumber)[_taskNumber] || '', main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].name), babelHelpers.classPrivateFieldLooseBase(this, _renderButton)[_renderButton](), main_core.Text.encode(DurationFormatter.formatDate(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].modified, babelHelpers.classPrivateFieldLooseBase(this, _dateFormat)[_dateFormat], babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort)[_dateFormatShort])), babelHelpers.classPrivateFieldLooseBase(this, _renderStatus)[_renderStatus](), babelHelpers.classPrivateFieldLooseBase(this, _renderUsers)[_renderUsers](), babelHelpers.classPrivateFieldLooseBase(this, _renderExecutionTime)[_renderExecutionTime]());
	}
	function _renderButton2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId] === 0 || !babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].url) {
	    return null;
	  }
	  const participant = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users.find(user => user.id === babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId]);
	  if (main_core.Type.isUndefined(participant)) {
	    return null;
	  }
	  const isWaiting = new bizproc_task.UserStatus(participant.status).isWaiting();
	  return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<a
				class="
					bizproc-workflow-timeline-task-link
					bizproc-workflow-timeline-task-link-${0}
				"
				href="${0}"
			>
				${0}
			</a>
		`), isWaiting ? 'blue' : 'gray', main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].url || new main_core.Uri(`/company/personal/bizproc/${babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].id}/`).toString()), main_core.Text.encode(isWaiting ? main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_PROCEED') : main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_SEE')));
	}
	function _renderStatus2() {
	  let message = main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _getStatusName)[_getStatusName](babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].status, babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].approveType, babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users.length));
	  if (babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].status.isWaiting() && babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].approveType === 'vote') {
	    let votedCount = 0;
	    for (const user of babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users) {
	      if (!new bizproc_task.TaskStatus(user.status).isWaiting()) {
	        votedCount++;
	      }
	    }
	    message = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_VOTED', {
	      '#VOTED#': votedCount,
	      '#TOTAL#': babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users.length
	    });
	  }
	  return main_core.Tag.render(_t3 || (_t3 = _$1`<div class="bizproc-workflow-timeline-caption">${0}</div>`), message);
	}
	function _getStatusName2(taskStatus, taskApproveType, usersCount) {
	  if (taskStatus.isYes() || taskStatus.isOk()) {
	    return main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED');
	  }
	  if (taskStatus.isNo() || taskStatus.isCancel()) {
	    return main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_DECLINED');
	  }
	  if (taskStatus.isWaiting()) {
	    if (usersCount === 1) {
	      return main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING');
	    }
	    let message = '';
	    switch (taskApproveType) {
	      case 'all':
	        message = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ALL');
	        break;
	      case 'any':
	        message = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ANY');
	        break;
	      case 'vote':
	        message = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ALL');
	        break;
	      default:
	        message = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING');
	        break;
	    }
	    return message;
	  }
	  return taskStatus.name;
	}
	function _renderUsers2() {
	  const showVoteResult = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users.length > 1;
	  return main_core.Tag.render(_t4 || (_t4 = _$1`
			<div class="bizproc-workflow-timeline-user-list">
				${0}
			</div>
		`), Object.values(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users).map(user => babelHelpers.classPrivateFieldLooseBase(this, _renderUser)[_renderUser](user, showVoteResult)));
	}
	function _renderUser2(userData, showVoteResult) {
	  const user = babelHelpers.classPrivateFieldLooseBase(this, _users)[_users].get(userData.id);
	  if (!user) {
	    return null;
	  }
	  const status = new bizproc_task.TaskStatus(userData.status);
	  let voteClass = '';
	  if (showVoteResult) {
	    if (status.isYes() || status.isOk()) {
	      voteClass = '--voted-up';
	    }
	    if (status.isNo()) {
	      voteClass = '--voted-down';
	    }
	  }
	  let avatar = '<i></i>';
	  if (main_core.Type.isString(user.avatarSize100)) {
	    avatar = `<i style="background-image: url('${encodeURI(main_core.Text.encode(user.avatarSize100))}')"></i>`;
	  }
	  return main_core.Tag.render(_t5 || (_t5 = _$1`
			<div class="bizproc-workflow-timeline-user ${0}">
				<div class="bizproc-workflow-timeline-userlogo ui-icon ui-icon-common-user">
					${0}
				</div>
				<div class="bizproc-workflow-timeline-user-block">
					<a class="bizproc-workflow-timeline-link" href="${0}">${0}</a>
					<div class="bizproc-workflow-timeline-user-pos" title="${0}">
						${0}
					</div>
				</div>
			</div>
		`), voteClass, avatar, user.link, main_core.Text.encode(user.fullName), main_core.Text.encode(user.workPosition || ''), main_core.Text.encode(user.workPosition || ''));
	}
	function _renderExecutionTime2() {
	  const executionTime = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].executionTime;
	  if (main_core.Type.isNil(executionTime)) {
	    return null;
	  }
	  const useHint = executionTime >= TOO_LONG_PROCESS_DURATION;
	  const hint = main_core.Tag.render(_t6 || (_t6 = _$1`
			<span
				data-hint="${0}"
			></span>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_TIME_LIMIT_EXCEEDED')));
	  const notice = main_core.Tag.render(_t7 || (_t7 = _$1`
			<div class="bizproc-workflow-timeline-notice">
				<div class="bizproc-workflow-timeline-subject">
					${0}
				</div>
				<span class="bizproc-workflow-timeline-text">
					${0}
				</span>
				${0}
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EXECUTION_TIME')), main_core.Text.encode(DurationFormatter.formatTimeInterval(executionTime, 2)), useHint ? hint : null);
	  if (useHint) {
	    BX.UI.Hint.init(notice);
	  }
	  return notice;
	}
	function _renderAccessDenied2() {
	  return main_core.Tag.render(_t8 || (_t8 = _$1`
			<div class="bizproc-workflow-timeline-item --tech">
				<div class="bizproc-workflow-timeline-item-inner">
					<div>
						<span class="bizproc-workflow-timeline-icon"></span>
						<div class="bizproc-workflow-timeline-title">
							${0}
						</div>
					</div>
					<div class="bizproc-workflow-timeline-subject">
						${0}
					</div>
				</div>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_RIGHTS_TO_VIEW')), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_RIGHTS_TO_VIEW_TIP')));
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20;
	var _limits = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("limits");
	var _getFormatString = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFormatString");
	var _getMultiplierByFormat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMultiplierByFormat");
	class DurationFormatter {
	  static formatTimestamp(timestamp) {
	    return main_date.DateTimeFormat.format(babelHelpers.classPrivateFieldLooseBase(this, _getFormatString)[_getFormatString](Date.now() / 1000 - timestamp), timestamp);
	  }
	  static formatTimeInterval(interval, values = 1) {
	    if (main_core.Type.isNil(interval)) {
	      return main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME_UNKNOWN');
	    }
	    if (interval === 0) {
	      return main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_ZERO_SECOND_INTERVAL');
	    }
	    let result = '';
	    let remainder = interval;
	    for (let i = 0; i < values; i++) {
	      const format = babelHelpers.classPrivateFieldLooseBase(this, _getFormatString)[_getFormatString](remainder);
	      // ignore seconds if we already have result
	      if (result.length > 0 && format === 'sdiff') {
	        return result;
	      }
	      const multiplier = babelHelpers.classPrivateFieldLooseBase(this, _getMultiplierByFormat)[_getMultiplierByFormat](format);
	      result += main_date.DateTimeFormat.format(format, 0, remainder);
	      result += ' ';
	      if (multiplier > 0) {
	        remainder %= multiplier;
	        if (remainder === 0) {
	          return result;
	        }
	      }
	    }
	    return result;
	  }
	  static formatDate(timestamp, format, formatShort) {
	    if (formatShort && main_date.DateTimeFormat.format('Y', timestamp) === main_date.DateTimeFormat.format('Y', Date.now() / 1000)) {
	      return main_date.DateTimeFormat.format(formatShort, timestamp);
	    }
	    return main_date.DateTimeFormat.format(format, timestamp);
	  }
	}
	function _getFormatString2(seconds) {
	  for (const limit of babelHelpers.classPrivateFieldLooseBase(this, _limits)[_limits]) {
	    if (seconds >= limit[0]) {
	      return limit[1];
	    }
	  }
	  return 'sdiff';
	}
	function _getMultiplierByFormat2(format) {
	  for (const limit of babelHelpers.classPrivateFieldLooseBase(this, _limits)[_limits]) {
	    if (format === limit[1]) {
	      return limit[0];
	    }
	  }
	  return 0;
	}
	Object.defineProperty(DurationFormatter, _getMultiplierByFormat, {
	  value: _getMultiplierByFormat2
	});
	Object.defineProperty(DurationFormatter, _getFormatString, {
	  value: _getFormatString2
	});
	Object.defineProperty(DurationFormatter, _limits, {
	  writable: true,
	  value: [[3600 * 24 * 365, 'Ydiff'], [3600 * 24 * 31, 'mdiff'], [3600 * 24, 'ddiff'], [3600, 'Hdiff'], [60, 'idiff']]
	});
	var _workflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _data$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _isLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoaded");
	var _errors$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errors");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _biPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("biPopup");
	var _dateFormat$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFormat");
	var _dateFormatShort$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFormatShort");
	var _loadTimeline = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadTimeline");
	var _setDataFromResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDataFromResponse");
	var _renderItemTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderItemTitle");
	var _renderSubject = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSubject");
	var _renderProceedTaskButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderProceedTaskButton");
	var _renderUser$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUser");
	var _renderDoc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDoc");
	var _renderCaption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCaption");
	var _renderNotice = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNotice");
	var _renderStatus$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStatus");
	var _renderMore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMore");
	var _renderContent$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _renderItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderItem");
	var _renderItemsList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderItemsList");
	var _renderFirstBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderFirstBlock");
	var _renderContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContainer");
	var _textCrop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textCrop");
	var _createEfficiencyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createEfficiencyPopup");
	var _getEfficiencyData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEfficiencyData");
	var _renderEfficiencyInlineContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiencyInlineContent");
	var _renderEfficiencyPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiencyPopupContent");
	var _createBiButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createBiButton");
	var _createBiPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createBiPopup");
	var _showBiMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showBiMenu");
	var _renderBiPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderBiPopupContent");
	var _renderLoadingStub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLoadingStub");
	var _hasErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasErrors");
	class Timeline {
	  // #taskId: number;

	  constructor(options, config) {
	    Object.defineProperty(this, _hasErrors, {
	      value: _hasErrors2
	    });
	    Object.defineProperty(this, _renderLoadingStub, {
	      value: _renderLoadingStub2
	    });
	    Object.defineProperty(this, _renderBiPopupContent, {
	      value: _renderBiPopupContent2
	    });
	    Object.defineProperty(this, _showBiMenu, {
	      value: _showBiMenu2
	    });
	    Object.defineProperty(this, _createBiPopup, {
	      value: _createBiPopup2
	    });
	    Object.defineProperty(this, _createBiButton, {
	      value: _createBiButton2
	    });
	    Object.defineProperty(this, _renderEfficiencyPopupContent, {
	      value: _renderEfficiencyPopupContent2
	    });
	    Object.defineProperty(this, _renderEfficiencyInlineContent, {
	      value: _renderEfficiencyInlineContent2
	    });
	    Object.defineProperty(this, _getEfficiencyData, {
	      value: _getEfficiencyData2
	    });
	    Object.defineProperty(this, _createEfficiencyPopup, {
	      value: _createEfficiencyPopup2
	    });
	    Object.defineProperty(this, _textCrop, {
	      value: _textCrop2
	    });
	    Object.defineProperty(this, _renderContainer, {
	      value: _renderContainer2
	    });
	    Object.defineProperty(this, _renderFirstBlock, {
	      value: _renderFirstBlock2
	    });
	    Object.defineProperty(this, _renderItemsList, {
	      value: _renderItemsList2
	    });
	    Object.defineProperty(this, _renderItem, {
	      value: _renderItem2
	    });
	    Object.defineProperty(this, _renderContent$1, {
	      value: _renderContent2$1
	    });
	    Object.defineProperty(this, _renderMore, {
	      value: _renderMore2
	    });
	    Object.defineProperty(this, _renderStatus$1, {
	      value: _renderStatus2$1
	    });
	    Object.defineProperty(this, _renderNotice, {
	      value: _renderNotice2
	    });
	    Object.defineProperty(this, _renderCaption, {
	      value: _renderCaption2
	    });
	    Object.defineProperty(this, _renderDoc, {
	      value: _renderDoc2
	    });
	    Object.defineProperty(this, _renderUser$1, {
	      value: _renderUser2$1
	    });
	    Object.defineProperty(this, _renderProceedTaskButton, {
	      value: _renderProceedTaskButton2
	    });
	    Object.defineProperty(this, _renderSubject, {
	      value: _renderSubject2
	    });
	    Object.defineProperty(this, _renderItemTitle, {
	      value: _renderItemTitle2
	    });
	    Object.defineProperty(this, _setDataFromResponse, {
	      value: _setDataFromResponse2
	    });
	    Object.defineProperty(this, _loadTimeline, {
	      value: _loadTimeline2
	    });
	    Object.defineProperty(this, _workflowId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isLoaded, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errors$1, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _biPopup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateFormat$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateFormatShort$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]();
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _textCrop)[_textCrop]();
	    }, 500);
	    if (main_core.Type.isPlainObject(options)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = options.workflowId;
	      // this.#taskId = options.taskId;
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoaded)[_isLoaded] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _loadTimeline)[_loadTimeline]();
	    }
	    if (main_core.Type.isPlainObject(config)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _dateFormat$1)[_dateFormat$1] = `${config.dateFormat || 'j F Y'} ${config.timeFormat || 'H:i'}`;
	      babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort$1)[_dateFormatShort$1] = `${config.dateFormatShort || 'j F'} ${config.timeFormat || 'H:i'}`;
	    }
	  }
	  static open(options) {
	    main_core.Runtime.loadExtension('sidepanel').then(() => {
	      BX.SidePanel.Instance.open(main_core.Uri.addParam('/bitrix/components/bitrix/bizproc.workflow.timeline.slider/index.php', main_core.Type.isPlainObject(options) ? options : {}), {
	        width: 950,
	        allowChangeHistory: false,
	        cacheable: false,
	        loader: '/bitrix/js/bizproc/workflow/timeline/img/skeleton.svg'
	      });
	    }).catch(response => console.error(response.errors));
	  }
	  render() {
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasErrors)[_hasErrors]()) {
	      const errorsView = new ErrorsView({
	        errors: babelHelpers.classPrivateFieldLooseBase(this, _errors$1)[_errors$1]
	      });
	      errorsView.renderTo(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	      return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isLoaded)[_isLoaded]) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderLoadingStub)[_renderLoadingStub](), babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	    }
	    if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1])) {
	      main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]());
	      babelHelpers.classPrivateFieldLooseBase(this, _createEfficiencyPopup)[_createEfficiencyPopup]().show();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].biMenu) {
	        this.showBiMenus(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].biMenu);
	      }
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  }
	  showBiMenus(menu) {
	    babelHelpers.classPrivateFieldLooseBase(this, _createBiButton)[_createBiButton](menu);
	    babelHelpers.classPrivateFieldLooseBase(this, _createBiPopup)[_createBiPopup](menu).show();
	  }
	}
	function _loadTimeline2() {
	  main_core.ajax.runAction('bizproc.workflow.getTimeline', {
	    data: {
	      workflowId: babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _setDataFromResponse)[_setDataFromResponse](response);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoaded)[_isLoaded] = true;
	    this.render();
	  }).catch(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _setDataFromResponse)[_setDataFromResponse](response);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoaded)[_isLoaded] = true;
	    this.render();
	  });
	}
	function _setDataFromResponse2(response) {
	  if (main_core.Type.isPlainObject(response)) {
	    const getString = (value, defaultValue = '') => main_core.Type.isString(value) ? value : defaultValue;
	    const getArray = (value, defaultValue = []) => main_core.Type.isArray(value) ? value : defaultValue;
	    const getBool = (value, defaultValue = false) => main_core.Type.isBoolean(value) ? value : defaultValue;
	    const getInteger = (value, defaultValue = 0) => main_core.Type.isInteger(value) ? value : defaultValue;
	    if (main_core.Type.isPlainObject(response.data)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1] = {
	        document: new bizproc_document.DocumentId({
	          documentId: getArray(response.data.documentType),
	          entityName: getString(response.data.entityName),
	          documentName: getString(response.data.documentName),
	          documentUrl: getString(response.data.documentUrl),
	          moduleName: getString(response.data.moduleName)
	        }),
	        isWorkflowRunning: getBool(response.data.isWorkflowRunning),
	        timeToStart: getInteger(response.data.timeToStart, null),
	        executionTime: getInteger(response.data.executionTime, null),
	        started: getInteger(response.data.started, null),
	        startedBy: getInteger(response.data.startedBy),
	        tasks: getArray(response.data.tasks).map(taskData => new TimelineTask(taskData)),
	        users: new Map(),
	        stats: {
	          averageDuration: getInteger(response.data.stats.averageDuration, null),
	          efficiency: getString(response.data.stats.efficiency)
	        },
	        biMenu: getArray(response.data.biMenu, null)
	      };
	      for (const user of getArray(response.data.users)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].users.set(main_core.Text.toInteger(user.id), user);
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _errors$1)[_errors$1] = getArray(response.errors);
	  }
	}
	function _renderItemTitle2(title, iconClass, iconText, crop) {
	  const iconClassValue = main_core.Type.isString(iconClass) ? ` ${iconClass}` : '';
	  const iconTextValue = main_core.Type.isString(iconText) ? iconText : '';
	  const cropValue = crop ? ' data-crop="crop"' : '';
	  return main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div>
				<span class="bizproc-workflow-timeline-icon${0}">${0}</span>
				<div class="bizproc-workflow-timeline-title"${0}>${0}</div>
			</div>
		`), iconClassValue, iconTextValue, cropValue, main_core.Text.encode(title));
	}
	function _renderSubject2(subject) {
	  return main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="bizproc-workflow-timeline-subject">${0}</div>
		`), main_core.Text.encode(subject));
	}
	function _renderProceedTaskButton2(task) {
	  const uri = task.url || new main_core.Uri(`/company/personal/bizproc/${task.id}/`).toString();
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="task-button --hidden">
				<a class="ui-btn ui-btn-xs ui-btn-primary ui-btn-round" href="${0}">
					${0}
				</a>
			</div>
		`), main_core.Text.encode(uri), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_PROCEED'));
	}
	function _renderUser2$1(userId, userData = null, task = null) {
	  const user = babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].users.get(userId || userData.id);
	  let userClass = '';
	  let isWaiting = false;
	  if (userData) {
	    const status = new bizproc_task.TaskStatus(userData.status);
	    if (status.isYes() || status.isOk()) {
	      userClass = ' --voted-up';
	    }
	    if (status.isNo()) {
	      userClass = ' --voted-down';
	    }
	    if (status.isWaiting()) {
	      isWaiting = true;
	    }
	  }
	  const position = main_core.Type.isString(user.workPosition) ? `<div class="bizproc-workflow-timeline-user-pos">${main_core.Text.encode(user.workPosition)}</div>` : '';
	  let avatar = '<i></i>';
	  if (main_core.Type.isString(user.avatarSize100)) {
	    avatar = `<i style="background-image: url('${encodeURI(user.avatarSize100)}')"></i>`;
	  }
	  const button = task != null && task.id && isWaiting ? babelHelpers.classPrivateFieldLooseBase(this, _renderProceedTaskButton)[_renderProceedTaskButton](task) : '';
	  return main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
			<div class="bizproc-workflow-timeline-user${0}">
				<div class="bizproc-workflow-timeline-userlogo ui-icon ui-icon-common-user">
					${0}
				</div>
				<div class="bizproc-workflow-timeline-user-block">
					<a class="bizproc-workflow-timeline-link" href="${0}">${0}</a>
					${0}
				</div>
				${0}
			</div>
		`), userClass, avatar, user.link, main_core.Text.encode(user.fullName), position, button);
	}
	function _renderDoc2(name, link, type, iconClass) {
	  return main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
			<div class="bizproc-workflow-timeline-doc">
				${0}
				<div class="bizproc-workflow-timeline-type">
					<span class="ui-icon-set ${0}"></span>
					<span class="bizproc-workflow-timeline-type-text">${0}</span>
				</div>
				<a class="bizproc-workflow-timeline-link" href="${0}" target="_top">${0}</a>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderCaption)[_renderCaption](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_START_DOC')), iconClass, type, link, main_core.Text.encode(name));
	}
	function _renderCaption2(caption) {
	  return main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
			<div class="bizproc-workflow-timeline-caption">${0}</div>
		`), caption);
	}
	function _renderNotice2(subject, text) {
	  return main_core.Tag.render(_t7$1 || (_t7$1 = _$2`
			<div class="bizproc-workflow-timeline-notice">
				<div class="bizproc-workflow-timeline-subject">${0}</div>
				<span class="bizproc-workflow-timeline-text">${0}</span>
			</div>
		`), main_core.Text.encode(subject), main_core.Text.encode(text));
	}
	function _renderStatus2$1(text, statusClass) {
	  const statusClassValue = main_core.Type.isString(statusClass) ? ` ${statusClass}` : '';
	  return main_core.Tag.render(_t8$1 || (_t8$1 = _$2`
			<div class="bizproc-workflow-timeline-status${0}">${0}</div>
		`), statusClassValue, main_core.Text.encode(text));
	}
	function _renderMore2() {
	  return main_core.Tag.render(_t9 || (_t9 = _$2`
			<div class="bizproc-workflow-timeline-item --more">
				<div class="bizproc-workflow-timeline-item-inner">
					<span class="bizproc-workflow-timeline-icon"></span>
					<button class="ui-btn ui-btn-light-border ui-btn-xs" type="button" onclick="expandMore(event)">
						<span class="ui-btn-text">
							${0}
						</span>
					</button>
					<script type="text/javascript">
						function expandMore(event)
						{
							const moreItemBlock = event.target.closest('.--more');
							const hiddenBlocks = document.querySelectorAll('.bizproc-workflow-timeline-item.--hidden:not(.--efficiency)');
							BX.Dom.addClass(moreItemBlock, '--hidden');
							hiddenBlocks.forEach((hiddenBlock) => BX.Dom.removeClass(hiddenBlock, '--hidden'));
						}
					</script>
				</div>
			</div>
		`), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_MORE_TASKS'));
	}
	function _renderContent2$1(children) {
	  return main_core.Tag.render(_t10 || (_t10 = _$2`
			<div class="bizproc-workflow-timeline-content">
				${0}
			</div>
		`), children);
	}
	function _renderItem2(children, itemClass, efficiencyClass) {
	  const itemClassValue = main_core.Type.isString(itemClass) ? ` ${itemClass}` : '';
	  const efficiencyClassValue = main_core.Type.isString(efficiencyClass) ? ` data-efficiency-class="${efficiencyClass}"` : '';
	  return main_core.Tag.render(_t11 || (_t11 = _$2`
			<div class="bizproc-workflow-timeline-item${0}"${0}>
				<div class="bizproc-workflow-timeline-item-inner">
					${0}
				</div>
			</div>
		`), itemClassValue, efficiencyClassValue, children);
	}
	function _renderItemsList2(items) {
	  return main_core.Tag.render(_t12 || (_t12 = _$2`
			<div class="bizproc-workflow-timeline-wrapper">
				<div class="bizproc-workflow-timeline-inner">
					<div class="bizproc-workflow-timeline-list">
						${0}
					</div>
					<script type="text/javascript">
						(function() {
							const buttons = document.querySelectorAll('.task-button.--hidden');
							const showButtons = buttons.length > 1;
							buttons.forEach(function (button) {
								BX.Dom.insertBefore(
									button.closest('.bizproc-workflow-timeline-user'),
									button.closest('.bizproc-workflow-timeline-user-list').firstChild
								);
								if (showButtons)
								{
									BX.Dom.removeClass(button, '--hidden')
								}
							});
						})();
					</script>
				</div>
			</div>
		`), items);
	}
	function _renderFirstBlock2() {
	  const content = [];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].startedBy) {
	    content.push(babelHelpers.classPrivateFieldLooseBase(this, _renderCaption)[_renderCaption](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_FROM')), babelHelpers.classPrivateFieldLooseBase(this, _renderUser$1)[_renderUser$1](babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].startedBy));
	  }
	  content.push(babelHelpers.classPrivateFieldLooseBase(this, _renderDoc)[_renderDoc](babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].document.name, babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].document.url, babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].document.moduleName, '--file-2'));
	  if (!main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].timeToStart)) {
	    content.push(babelHelpers.classPrivateFieldLooseBase(this, _renderNotice)[_renderNotice](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EXECUTION_TIME'), DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].timeToStart, 2)));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderItem)[_renderItem]([babelHelpers.classPrivateFieldLooseBase(this, _renderItemTitle)[_renderItemTitle](babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].document.entityName, '--success', '1'), babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].started && babelHelpers.classPrivateFieldLooseBase(this, _renderSubject)[_renderSubject](DurationFormatter.formatDate(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].started, babelHelpers.classPrivateFieldLooseBase(this, _dateFormat$1)[_dateFormat$1], babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort$1)[_dateFormatShort$1])), babelHelpers.classPrivateFieldLooseBase(this, _renderContent$1)[_renderContent$1](content)], '--selected');
	}
	function _renderContainer2() {
	  const items = [];
	  let efficiencyClass = '';
	  let isWaiting = false;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1]) {
	    let task = null;
	    items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderFirstBlock)[_renderFirstBlock]());
	    let taskNumber = 1;
	    let hasHidden = babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].tasks[0] ? !babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].tasks[0].status.isWaiting() : true;
	    for (const taskIndex of Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].tasks)) {
	      task = babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].tasks[taskIndex];
	      isWaiting = task.status.isWaiting();
	      if (!isWaiting) {
	        ++taskNumber;
	      }
	      const taskView = new TimelineTaskView({
	        task,
	        userId: main_core.Text.toInteger(main_core.Loc.getMessage('USER_ID')),
	        dateFormat: babelHelpers.classPrivateFieldLooseBase(this, _dateFormat$1)[_dateFormat$1],
	        dateFormatShort: babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort$1)[_dateFormatShort$1],
	        taskNumber: isWaiting ? null : taskNumber,
	        users: babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].users
	      });
	      const node = taskView.render();
	      if (!isWaiting && hasHidden) {
	        main_core.Dom.addClass(node, '--hidden');
	      }
	      if (isWaiting && hasHidden) {
	        items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderMore)[_renderMore]());
	        hasHidden = false;
	      }
	      items.push(node);
	    }
	    if (hasHidden && babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].tasks[0]) {
	      items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderMore)[_renderMore]());
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].isWorkflowRunning) {
	      if (isWaiting) {
	        items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderItem)[_renderItem]([babelHelpers.classPrivateFieldLooseBase(this, _renderItemTitle)[_renderItemTitle](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS')), babelHelpers.classPrivateFieldLooseBase(this, _renderSubject)[_renderSubject](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_TIP'))], '--tech --previous-item'));
	      } else {
	        items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderItem)[_renderItem]([babelHelpers.classPrivateFieldLooseBase(this, _renderItemTitle)[_renderItemTitle](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_INTERMEDIATE')), babelHelpers.classPrivateFieldLooseBase(this, _renderSubject)[_renderSubject](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_INTERMEDIATE_TIP'))], '--tech --previous-item'));
	      }
	    } else {
	      const isOk = !task || task.status.isOk() || task.status.isYes();
	      const content = [babelHelpers.classPrivateFieldLooseBase(this, _renderCaption)[_renderCaption](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PROCESS_FINISHED'))];
	      if (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].startedBy) {
	        content.push(isOk ? babelHelpers.classPrivateFieldLooseBase(this, _renderStatus$1)[_renderStatus$1](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_APPROVED_FOR')) : babelHelpers.classPrivateFieldLooseBase(this, _renderStatus$1)[_renderStatus$1](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_DECLINED'), '--failure'), babelHelpers.classPrivateFieldLooseBase(this, _renderUser$1)[_renderUser$1](babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].startedBy));
	      }
	      content.push(babelHelpers.classPrivateFieldLooseBase(this, _renderNotice)[_renderNotice](main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PROCESS_EXECUTED'), DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].executionTime)));
	      efficiencyClass = isOk ? '--success' : '--declined';
	      items.push(babelHelpers.classPrivateFieldLooseBase(this, _renderItem)[_renderItem]([babelHelpers.classPrivateFieldLooseBase(this, _renderItemTitle)[_renderItemTitle](babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].document.entityName, isOk ? '--success' : null), babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].started && babelHelpers.classPrivateFieldLooseBase(this, _renderSubject)[_renderSubject](DurationFormatter.formatDate(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].started + babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].executionTime, babelHelpers.classPrivateFieldLooseBase(this, _dateFormat$1)[_dateFormat$1], babelHelpers.classPrivateFieldLooseBase(this, _dateFormatShort$1)[_dateFormatShort$1])), babelHelpers.classPrivateFieldLooseBase(this, _renderContent$1)[_renderContent$1](content)], isOk ? '--success --previous' : '--declined --selected --previous', efficiencyClass), babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyInlineContent)[_renderEfficiencyInlineContent]());
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderItemsList)[_renderItemsList](items);
	}
	function _textCrop2() {
	  const textCropNodes = document.querySelectorAll('[data-crop="crop"]');
	  for (const textCropNode of textCropNodes) {
	    const text = new ui_textcrop.TextCrop({
	      rows: 2,
	      target: textCropNode
	    });
	    text.init();
	  }
	}
	function _createEfficiencyPopup2() {
	  return new main_popup.Popup({
	    width: 403,
	    minHeight: 345,
	    closeIcon: true,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyPopupContent)[_renderEfficiencyPopupContent](),
	    bindElement: {
	      left: 555,
	      top: 130
	    },
	    padding: 26,
	    borderRadius: '18px',
	    className: '--bizproc-timeline-popup',
	    events: {
	      onPopupClose: () => {
	        let inlineEfficiencyPrev = document.querySelector('.--previous-item');
	        if (!inlineEfficiencyPrev) {
	          inlineEfficiencyPrev = document.querySelector('.bizproc-workflow-timeline-item.--processing');
	        }
	        if (!inlineEfficiencyPrev) {
	          return;
	        }
	        BX.Dom.addClass(inlineEfficiencyPrev, '--previous');
	        let efficiencyInlineClass = inlineEfficiencyPrev.getAttribute('data-efficiency-class');
	        if (!efficiencyInlineClass) {
	          efficiencyInlineClass = '';
	        }
	        inlineEfficiencyPrev.after(babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyInlineContent)[_renderEfficiencyInlineContent](efficiencyInlineClass));
	      }
	    }
	  });
	}
	function _getEfficiencyData2() {
	  let logoClass = '--first';
	  let notice = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_STATS');
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].stats.efficiency) {
	    case 'fast':
	      if (DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].stats.averageDuration) === DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].executionTime)) {
	        logoClass = '--slow';
	        notice = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_SLOWLY');
	      } else {
	        logoClass = '--fast';
	        notice = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_QUICKLY');
	      }
	      break;
	    case 'slow':
	      logoClass = '--slow';
	      notice = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_SLOWLY');
	      break;
	    case 'stopped':
	      logoClass = '--stopped';
	      notice = main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_NO_PROGRESS');
	      break;
	    default:
	      break;
	  }
	  return [logoClass, notice];
	}
	function _renderEfficiencyInlineContent2(itemClass) {
	  const [logoClass, notice] = babelHelpers.classPrivateFieldLooseBase(this, _getEfficiencyData)[_getEfficiencyData]();
	  const efficiencyInlineContent = main_core.Tag.render(_t13 || (_t13 = _$2`
			<div class="bizproc-workflow-timeline-item --efficiency ${0}">
				<div class="bizproc-workflow-timeline-item-inner">
					<div class="bizproc-workflow-timeline-title">
						${0}
					</div>
					<div class="bizproc-workflow-timeline-content">
						<div class="bizproc-workflow-timeline-eff-icon ${0}"></div>
						<div class="bizproc-workflow-timeline-content-inner">
							<div class="bizproc-workflow-timeline-caption">${0}</div>
							<div class="bizproc-workflow-timeline-notice">
								<div class="bizproc-workflow-timeline-subject">
									${0}
								</div>
								<span class="bizproc-workflow-timeline-text">
									${0}
								</span>
								<span
									data-hint="${0}"
								></span>
							</div>
							<div class="bizproc-workflow-timeline-notice">
								<div class="bizproc-workflow-timeline-subject">
									${0}
								</div>
								<span class="bizproc-workflow-timeline-text">
									${0}
								</span>
							</div>
						</div>
					</div>
				</div>	
			</div>
		`), itemClass, main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EFFECTIVITY_MARK'), logoClass, notice, main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_CURRENT_PROCESS_TIME'), DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].executionTime), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_TIME_DIFFERENCE_MSGVER_1'), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME'), babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].stats.averageDuration === null ? main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME_UNKNOWN') : DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].stats.averageDuration));
	  BX.UI.Hint.init(efficiencyInlineContent);
	  return efficiencyInlineContent;
	}
	function _renderEfficiencyPopupContent2() {
	  const [logoClass, notice] = babelHelpers.classPrivateFieldLooseBase(this, _getEfficiencyData)[_getEfficiencyData]();
	  const popup = main_core.Tag.render(_t14 || (_t14 = _$2`
			<div class="bizproc-timeline-popup">
				<div class="bizproc-timeline-popup-title">
					${0}
				</div>
				<div class="bizproc-timeline-popup-main">
					<div class="bizproc-timeline-popup-status">
						<div class="bizproc-timeline-popup-logo ${0}"></div>
						<div class="bizproc-timeline-popup-notice">${0}</div>
					</div>
					<div class="bizproc-timeline-popup-content">
						<div class="bizproc-timeline-popup-block">
							<span class="bizproc-timeline-popup-val">
								${0}
							</span>
							<span
								data-hint="${0}"
							></span>
							<div class="bizproc-timeline-popup-prop">
								${0}
							</div>
						</div>
						<div class="bizproc-timeline-popup-block">
							<span class="bizproc-timeline-popup-val">
								${0}
							</span>
							<div class="bizproc-timeline-popup-prop">
								${0}
							</div>
						</div>
					</div>
				</div>
				<div class="bizproc-timeline-popup-footer">
					<p class="bizproc-timeline-popup-text">
						${0}
					</p>
					<a class="bizproc-timeline-popup-text" href="javascript:top.BX.Helper.show('redirect=detail&code=18783714')">
						${0}
					</a>
				</div>
			</div>
		`), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EFFECTIVITY_MARK'), logoClass, notice, DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].executionTime), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_TIME_DIFFERENCE_MSGVER_1'), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_CURRENT_PROCESS_TIME'), DurationFormatter.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _data$1)[_data$1].stats.averageDuration), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME'), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMANCE_TUNING_TIP'), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMANCE_TUNING_LINK'));
	  BX.UI.Hint.init(popup);
	  return popup;
	}
	function _createBiButton2(menu) {
	  const toolbarNode = document.querySelector('[data-role="page-toolbar"]');
	  if (!toolbarNode) {
	    return;
	  }
	  if (menu.length === 1) {
	    const linkBtn = main_core.Tag.render(_t15 || (_t15 = _$2`
				<a class="ui-btn ui-btn-light-border ui-btn-themes" href="${0}">
					${0}
				</a>
			`), main_core.Text.encode(menu[0].URL), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_BUTTON')));
	    main_core.Dom.prepend(linkBtn, toolbarNode);
	    return;
	  }
	  const clickHandler = babelHelpers.classPrivateFieldLooseBase(this, _showBiMenu)[_showBiMenu].bind(this, menu);
	  const dropBtn = main_core.Tag.render(_t16 || (_t16 = _$2`
			<button class="ui-btn ui-btn-light-border ui-btn-themes ui-btn-dropdown" onclick="${0}">
				${0}
			</button>
		`), clickHandler, main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_BUTTON')));
	  main_core.Dom.prepend(dropBtn, toolbarNode);
	}
	function _createBiPopup2(menu) {
	  babelHelpers.classPrivateFieldLooseBase(this, _biPopup)[_biPopup] = new main_popup.Popup({
	    width: 403,
	    minHeight: 183,
	    closeIcon: true,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _renderBiPopupContent)[_renderBiPopupContent](menu),
	    bindElement: {
	      left: 555,
	      top: 502
	    },
	    padding: 17,
	    borderRadius: '18px',
	    className: '--bizproc-timeline-popup --bi'
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _biPopup)[_biPopup];
	}
	function _showBiMenu2(menu, event) {
	  new main_popup.Menu({
	    bindElement: event.target,
	    items: menu.map(item => {
	      return {
	        text: item.TEXT,
	        href: item.URL
	      };
	    })
	  }).show();
	}
	function _renderBiPopupContent2(menu) {
	  let btn = null;
	  if (menu.length === 1) {
	    btn = main_core.Tag.render(_t17 || (_t17 = _$2`
				<a class="ui-btn ui-btn-light-border ui-btn-round ui-btn-xs" href="${0}">
					<span class="ui-btn-text">${0}</span>
				</a>
			`), main_core.Text.encode(menu[0].URL), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_LINK'));
	  } else {
	    const clickHandler = babelHelpers.classPrivateFieldLooseBase(this, _showBiMenu)[_showBiMenu].bind(this, menu);
	    btn = main_core.Tag.render(_t18 || (_t18 = _$2`
				<a 
					class="ui-btn ui-btn-light-border ui-btn-round ui-btn-xs ui-btn-dropdown"
					onclick="${0}"
				>
					<span class="ui-btn-text">${0}</span>
				</a>
			`), clickHandler, main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_LINK'));
	  }
	  return main_core.Tag.render(_t19 || (_t19 = _$2`
			<div class="bizproc-timeline-popup">
				<div class="bizproc-timeline-popup-title">${0}</div>
				<p class="bizproc-timeline-popup-info">${0}</p>
				${0}
			</div>
		`), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_TITLE'), main_core.Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_TIP'), btn);
	}
	function _renderLoadingStub2() {
	  return main_core.Tag.render(_t20 || (_t20 = _$2`
			<img src="/bitrix/js/bizproc/workflow/timeline/img/skeleton.svg"
				 style="width:100%; margin: 0; padding: 0;"/>
		`));
	}
	function _hasErrors2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _errors$1)[_errors$1].length > 0;
	}

	exports.DurationFormatter = DurationFormatter;
	exports.Timeline = Timeline;

}((this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {}),BX.Bizproc,BX.Bizproc,BX,BX.UI,BX.Main,BX.Main,BX.Bizproc,BX));
//# sourceMappingURL=timeline.bundle.js.map
