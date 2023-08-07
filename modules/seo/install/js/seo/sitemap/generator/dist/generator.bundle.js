this.BX = this.BX || {};
this.BX.Seo = this.BX.Seo || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _statusContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("statusContainer");
	var _jobs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("jobs");
	var _do = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("do");
	var _finish = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("finish");
	var _printStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("printStatus");
	var _printError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("printError");
	var _getStatusNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStatusNode");
	class Generator extends main_core_events.EventEmitter {
	  /**
	   * @param container - HTML element for print sitemap statuses
	   */
	  constructor(container) {
	    super();
	    Object.defineProperty(this, _getStatusNode, {
	      value: _getStatusNode2
	    });
	    Object.defineProperty(this, _printError, {
	      value: _printError2
	    });
	    Object.defineProperty(this, _printStatus, {
	      value: _printStatus2
	    });
	    Object.defineProperty(this, _finish, {
	      value: _finish2
	    });
	    Object.defineProperty(this, _do, {
	      value: _do2
	    });
	    Object.defineProperty(this, _statusContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _jobs, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _statusContainer)[_statusContainer] = container;
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _statusContainer)[_statusContainer]);
	  }
	  add(sitemapId, jobData) {
	    // todo: after finish not running again until page refresh. Need rerun
	    if (sitemapId > 0 && !babelHelpers.classPrivateFieldLooseBase(this, _jobs)[_jobs].find(job => job.id === sitemapId)) {
	      const existsStatusNode = document.getElementById(Generator.STATUS_CLASS + '-' + sitemapId);
	      const statusNode = existsStatusNode || main_core.Tag.render(_t || (_t = _`
					<div id="${0}-${0}" class="${0}"></div>
				`), Generator.STATUS_CLASS, sitemapId, Generator.STATUS_CLASS);
	      main_core.Dom.append(statusNode, babelHelpers.classPrivateFieldLooseBase(this, _statusContainer)[_statusContainer]);
	      const newJob = {
	        id: sitemapId,
	        statusNode: statusNode,
	        step: Generator.START_STEP,
	        status: Generator.START_STATUS,
	        statusMessage: '',
	        formattedStatusMessage: ''
	      };
	      if (jobData) {
	        Object.assign(newJob, jobData);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _jobs)[_jobs].push(newJob);
	      if (newJob.formattedStatusMessage) {
	        newJob.status !== 'E' ? babelHelpers.classPrivateFieldLooseBase(this, _printStatus)[_printStatus](newJob.id, newJob.formattedStatusMessage) : babelHelpers.classPrivateFieldLooseBase(this, _printError)[_printError](newJob.id, newJob.formattedStatusMessage);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _do)[_do](sitemapId);
	    }
	  }
	}
	function _do2(jobId) {
	  this.emit('onBeforeDo', jobId);
	  main_core.ajax.runAction("seo.api.sitemap.job.do", {
	    data: {
	      sitemapId: jobId
	    }
	  }).then(result => {
	    this.emit('onAfterDo', jobId);
	    if (result && result.status === 'success') {
	      const data = result.data;
	      if (data.status !== Generator.STATUS_FINISH && data.status !== Generator.STATUS_ERROR) {
	        babelHelpers.classPrivateFieldLooseBase(this, _do)[_do](jobId);
	      }
	      if (data.status === Generator.STATUS_FINISH) {
	        babelHelpers.classPrivateFieldLooseBase(this, _printStatus)[_printStatus](jobId, data.formattedStatusMessage);
	        babelHelpers.classPrivateFieldLooseBase(this, _finish)[_finish](jobId);
	      } else if (data.status === Generator.STATUS_ERROR) {
	        babelHelpers.classPrivateFieldLooseBase(this, _printError)[_printError](jobId, data.statusMessage || 'Something went wrong');
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _printStatus)[_printStatus](jobId, data.formattedStatusMessage);
	      }
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _printError)[_printError](jobId, result.error || 'Something went wrong');
	    }
	  }).catch(err => {
	    const errMsg = err.errors.pop();
	    babelHelpers.classPrivateFieldLooseBase(this, _printError)[_printError](jobId, errMsg ? errMsg.message : 'Something went wrong');
	  });
	}
	function _finish2(jobId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _jobs)[_jobs] = babelHelpers.classPrivateFieldLooseBase(this, _jobs)[_jobs].filter(job => job.id !== jobId);
	  this.emit('onFinish', jobId);
	}
	function _printStatus2(jobId, status) {
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getStatusNode)[_getStatusNode](jobId);
	  const message = main_core.Tag.render(_t2 || (_t2 = _`<div>${0}</div>`), status);
	  main_core.Dom.clean(node);
	  main_core.Dom.append(message, node);
	}
	function _printError2(jobId, error) {
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getStatusNode)[_getStatusNode](jobId);
	  const message = main_core.Tag.render(_t3 || (_t3 = _`<div>${0}</div>`), error);
	  main_core.Dom.clean(node);
	  main_core.Dom.append(message, node);
	}
	function _getStatusNode2(jobId) {
	  const currentJob = babelHelpers.classPrivateFieldLooseBase(this, _jobs)[_jobs].find(job => job.id === jobId);
	  return currentJob ? currentJob.statusNode : null;
	}
	Generator.STATUS_REGISTER = 'R';
	Generator.STATUS_FINISH = 'F';
	Generator.STATUS_ERROR = 'E';
	Generator.START_STATUS = Generator.STATUS_REGISTER;
	Generator.START_STEP = 0;
	Generator.STATUS_CLASS = 'sitemap-status';

	exports.Generator = Generator;

}((this.BX.Seo.Sitemap = this.BX.Seo.Sitemap || {}),BX,BX.Event));
//# sourceMappingURL=generator.bundle.js.map
