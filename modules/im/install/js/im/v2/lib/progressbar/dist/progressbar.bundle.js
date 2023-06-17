this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,main_core,ui_progressbarjs_uploader,im_v2_const) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.CallBackground.ProgressBar';
	const SIZE_LOWER_THRESHOLD = 1024 * 1024 * 2;
	const CONTAINER_WIDTH_LOWER_THRESHOLD = 240;
	const CONTAINER_HEIGHT_LOWER_THRESHOLD = 54;
	const STARTING_PROGRESS = 5;
	var _getProgressBarParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProgressBarParams");
	var _adjustProgressBarTitleVisibility = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustProgressBarTitleVisibility");
	var _isSmallSizeFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSmallSizeFile");
	var _isSmallContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSmallContainer");
	class ProgressBarManager extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _isSmallContainer, {
	      value: _isSmallContainer2
	    });
	    Object.defineProperty(this, _isSmallSizeFile, {
	      value: _isSmallSizeFile2
	    });
	    Object.defineProperty(this, _adjustProgressBarTitleVisibility, {
	      value: _adjustProgressBarTitleVisibility2
	    });
	    Object.defineProperty(this, _getProgressBarParams, {
	      value: _getProgressBarParams2
	    });
	    this.setEventNamespace(EVENT_NAMESPACE);
	    const {
	      container,
	      uploadState,
	      customConfig: _customConfig = {}
	    } = params;
	    this.container = container;
	    this.uploadState = uploadState;
	    this.progressBar = new ui_progressbarjs_uploader.Uploader({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getProgressBarParams)[_getProgressBarParams](_customConfig),
	      container
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustProgressBarTitleVisibility)[_adjustProgressBarTitleVisibility]();
	  }
	  start() {
	    this.progressBar.start();
	    this.update();
	  }
	  update() {
	    if (this.uploadState.status === im_v2_const.FileStatus.error) {
	      this.progressBar.setProgress(0);
	      this.progressBar.setCancelDisable(false);
	      this.progressBar.setIcon(ui_progressbarjs_uploader.Uploader.icon.error);
	      this.progressBar.setProgressTitle(main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_ERROR'));
	    } else if (this.uploadState.status === im_v2_const.FileStatus.wait) {
	      this.progressBar.setProgress(this.uploadState.progress > STARTING_PROGRESS ? this.uploadState.progress : STARTING_PROGRESS);
	      this.progressBar.setCancelDisable(true);
	      this.progressBar.setIcon(ui_progressbarjs_uploader.Uploader.icon.cloud);
	      this.progressBar.setProgressTitle(main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_SAVING'));
	    } else if (this.uploadState.progress === 100) {
	      this.progressBar.setProgress(100);
	    } else if (this.uploadState.progress === -1) {
	      this.progressBar.setProgress(10);
	      this.progressBar.setProgressTitle(main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_WAITING'));
	    } else {
	      if (this.uploadState.progress === 0) {
	        this.progressBar.setIcon(ui_progressbarjs_uploader.Uploader.icon.cancel);
	      }
	      const progress = this.uploadState.progress > STARTING_PROGRESS ? this.uploadState.progress : STARTING_PROGRESS;
	      this.progressBar.setProgress(progress);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isSmallSizeFile)[_isSmallSizeFile]()) {
	        this.progressBar.setProgressTitle(main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_LOADING'));
	      } else {
	        const byteSent = this.uploadState.size / 100 * this.uploadState.progress;
	        this.progressBar.setByteSent(byteSent, this.uploadState.size);
	      }
	    }
	  }
	  destroy() {
	    this.progressBar.destroy(false);
	  }
	}
	function _getProgressBarParams2(customConfig) {
	  const defaultConfig = {
	    // direction: this.container.offsetHeight > CONTAINER_HEIGHT_LOWER_THRESHOLD? ProgressBar.direction.vertical: ProgressBar.direction.horizontal,
	    sizes: {
	      circle: this.container.offsetHeight > CONTAINER_HEIGHT_LOWER_THRESHOLD ? 54 : 38,
	      progress: this.container.offsetHeight > CONTAINER_HEIGHT_LOWER_THRESHOLD ? 4 : 8
	    },
	    labels: {
	      loading: main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_LOADING'),
	      completed: main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_COMPLETED'),
	      canceled: main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_CANCELED'),
	      cancelTitle: main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_UPLOAD_CANCEL_TITLE'),
	      megabyte: main_core.Loc.getMessage('IM_LIB_PROGRESSBAR_FILE_SIZE_MB')
	    },
	    cancelCallback: () => {
	      this.emit(ProgressBarManager.event.cancel);
	    },
	    destroyCallback: () => {
	      this.emit(ProgressBarManager.event.destroy);
	    }
	  };
	  return {
	    ...defaultConfig,
	    ...customConfig
	  };
	}
	function _adjustProgressBarTitleVisibility2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isSmallSizeFile)[_isSmallSizeFile]() || babelHelpers.classPrivateFieldLooseBase(this, _isSmallContainer)[_isSmallContainer]()) {
	    this.progressBar.setProgressTitleVisibility(false);
	  }
	}
	function _isSmallSizeFile2() {
	  return this.uploadState.size < SIZE_LOWER_THRESHOLD;
	}
	function _isSmallContainer2() {
	  return this.container.offsetHeight <= CONTAINER_HEIGHT_LOWER_THRESHOLD && this.container.offsetWidth < CONTAINER_WIDTH_LOWER_THRESHOLD;
	}
	ProgressBarManager.event = {
	  cancel: 'cancel',
	  destroy: 'destroy'
	};

	exports.ProgressBarManager = ProgressBarManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX,BX.ProgressBarJs,BX.Messenger.v2.Const));
//# sourceMappingURL=progressbar.bundle.js.map
