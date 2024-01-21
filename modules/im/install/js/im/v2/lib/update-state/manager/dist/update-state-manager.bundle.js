/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_const,im_v2_application_core) {
	'use strict';

	const WORKER_PATH = '/bitrix/js/im/v2/lib/update-state/shared-worker/dist/shared-worker.bundle.js';
	const WORKER_NAME = 'Bitrix24 UpdateState';
	const CSRF_TOKEN_NAME = 'bitrix_sessid';
	class UpdateStateManager {
	  static init() {
	    return new UpdateStateManager();
	  }
	  constructor() {
	    if (!('SharedWorker' in window)) {
	      return;
	    }
	    this.registerSharedWorker();
	    this.subscribeToEvents();
	  }
	  registerSharedWorker() {
	    this.worker = new SharedWorker(WORKER_PATH, WORKER_NAME);
	    this.handleMessageFromWorker();
	    this.startUpdateState();
	    this.worker.port.start();
	  }
	  setCsrfToken(token) {
	    if (!main_core.Type.isStringFilled(token)) {
	      return;
	    }
	    main_core.Loc.setMessage({
	      CSRF_TOKEN_NAME: token
	    });
	  }
	  getCsrfToken() {
	    return main_core.Loc.getMessage(CSRF_TOKEN_NAME);
	  }
	  getSessionTimeInMilliseconds() {
	    const {
	      sessionTime
	    } = im_v2_application_core.Core.getApplicationData();
	    return sessionTime * 1000;
	  }
	  subscribeToEvents() {
	    main_core.Event.bind(window, 'online', () => {
	      this.worker.port.postMessage({
	        force: true,
	        sessionTime: this.getSessionTimeInMilliseconds(),
	        csrfToken: this.getCsrfToken()
	      });
	    });
	  }
	  handleMessageFromWorker() {
	    main_core.Event.bind(this.worker.port, 'message', event => {
	      const {
	        csrfToken,
	        response
	      } = event.data;
	      this.setCsrfToken(csrfToken);
	      this.fireCountersEvent(response);
	    });
	  }
	  startUpdateState() {
	    this.worker.port.postMessage({
	      force: false,
	      sessionTime: this.getSessionTimeInMilliseconds(),
	      csrfToken: this.getCsrfToken()
	    });
	  }
	  fireCountersEvent(response) {
	    if (!(response != null && response.counters)) {
	      return;
	    }
	    main_core_events.EventEmitter.emit(window, im_v2_const.EventType.counter.onImUpdateCounter, [response.counters]);
	  }
	}

	exports.UpdateStateManager = UpdateStateManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Application));
//# sourceMappingURL=update-state-manager.bundle.js.map
