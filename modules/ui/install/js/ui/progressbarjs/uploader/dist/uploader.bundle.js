this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var Uploader = /*#__PURE__*/function () {
	  function Uploader() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Uploader);
	    this.container = params.container;

	    if (this.container && typeof params.blurElement === 'undefined') {
	      params.blurElement = this.container.firstElementChild;
	    }

	    this.blurElement = params.blurElement;
	    this.direction = Uploader.direction[params.direction] ? params.direction : Uploader.direction.vertical;
	    params.sizes = params.sizes && babelHelpers["typeof"](params.sizes) === 'object' ? params.sizes : {};
	    this.sizes = {
	      circle: params.sizes.circle ? params.sizes.circle : 54,
	      progress: params.sizes.progress ? params.sizes.progress : 4,
	      margin: params.sizes.margin ? params.sizes.margin : 0
	    };
	    params.labels = params.labels && babelHelpers["typeof"](params.labels) === 'object' ? params.labels : {};
	    this.labels = {
	      loading: params.labels.loading ? params.labels.loading : '',
	      completed: params.labels.completed ? params.labels.completed : '',
	      canceled: params.labels.canceled ? params.labels.canceled : '',
	      cancelTitle: params.labels.cancelTitle ? params.labels.cancelTitle : '',
	      megabyte: params.labels.megabyte ? params.labels.megabyte : 'MB'
	    };
	    this.cancelCallback = typeof params.cancelCallback === 'function' ? params.cancelCallback : null;
	    this.destroyCallback = typeof params.destroyCallback === 'function' ? params.destroyCallback : null;
	    this.icon = Uploader.icon[params.icon] ? params.icon : !this.cancelCallback ? Uploader.icon.cloud : Uploader.icon.cancel;
	    this.inited = !!this.container;
	    this.destroing = false;
	  }

	  babelHelpers.createClass(Uploader, [{
	    key: "start",
	    value: function start() {
	      var _this = this;

	      if (!this.inited) {
	        return false;
	      }

	      clearTimeout(this.timeoutSetIcon);
	      clearTimeout(this.timeout);
	      this.active = true;
	      this.canceled = false;
	      this.cancelCallbackDisabled = false;
	      this.wrapper = document.createElement('div');
	      this.wrapper.classList.add('ui-file-progressbar-loader-wrapper');
	      this.wrapper.innerHTML = "\n\t\t\t<div class=\"ui-file-progressbar-loader\">\n\t\t\t\t<div class=\"ui-file-progressbar-icon\"></div>\n\t\t\t\t<div class=\"ui-file-progressbar-progress ui-file-progressbar-rotating\"></div>\n\t\t\t</div>\n\t\t\t<div class=\"ui-file-progressbar-label\">".concat(this.labels.loading, "</div>\n\t\t");
	      this.processLoader = this.wrapper.getElementsByClassName('ui-file-progressbar-loader')[0];
	      this.processLoaderIcon = this.wrapper.getElementsByClassName('ui-file-progressbar-icon')[0];
	      this.processStatus = this.wrapper.getElementsByClassName('ui-file-progressbar-progress')[0];
	      this.proccesLabel = this.wrapper.getElementsByClassName('ui-file-progressbar-label')[0];

	      if (this.direction === Uploader.direction.horizontal) {
	        this.wrapper.classList.add('ui-file-progressbar-loader-horizontal');
	      }

	      this.container.classList.add('ui-file-progressbar-container-relative');
	      this.container.insertBefore(this.wrapper, this.container.firstChild);

	      if (this.blurElement) {
	        this.blurElement.classList.add("ui-file-progressbar-item-blurred");
	      }

	      var processLoaderStyle = "width: ".concat(this.sizes.circle, "px; height: ").concat(this.sizes.circle, "px;");

	      if (this.sizes.margin) {
	        processLoaderStyle = processLoaderStyle + "margin: ".concat(this.sizes.margin, "px;");
	        this.proccesLabel.style = "margin: ".concat(this.sizes.margin, "px;");
	      }

	      this.processLoader.style = processLoaderStyle;

	      if (this.cancelCallback) {
	        this.processLoader.addEventListener('click', function (event) {
	          if (_this.cancelCallbackDisabled) {
	            return false;
	          }

	          _this.setProgress(0);

	          if (_this.labels.canceled) {
	            _this.setProgressTitle(_this.labels.canceled);
	          }

	          _this.canceled = event;
	          _this.active = false;
	          clearTimeout(_this.timeout);
	          _this.timeout = setTimeout(function () {
	            return _this.destroy();
	          }, 1000);
	          return true;
	        });

	        if (this.labels.cancelTitle) {
	          this.processLoader.title = this.labels.cancelTitle;
	        }
	      }

	      if (!this.labels.loading) {
	        this.setProgressTitleVisibility(false);
	      }

	      this.setIcon(this.icon, true);
	      this.bar = new BX.ProgressBarJs.Circle(this.processStatus, {
	        easing: "linear",
	        strokeWidth: this.sizes.progress,
	        color: '#ffffff',
	        from: {
	          color: '#ffffff'
	        },
	        to: {
	          color: '#ffffff'
	        },
	        step: function step(state, bar) {
	          if (bar.value() == 1) {
	            clearTimeout(_this.timeout);
	            _this.timeout = setTimeout(function () {
	              if (_this.labels.completed) {
	                _this.setProgressTitle(_this.labels.completed);
	              }

	              _this.setIcon(Uploader.icon.done);

	              clearTimeout(_this.timeout);
	              _this.timeout = setTimeout(function () {
	                return _this.destroy();
	              }, 1000);
	            }, 200);
	          }
	        }
	      });
	    }
	  }, {
	    key: "setCancelDisable",
	    value: function setCancelDisable() {
	      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.cancelCallbackDisabled = !!value;

	      if (this.labels.cancelTitle) {
	        this.processLoader.title = this.cancelCallbackDisabled ? '' : this.labels.cancelTitle;
	      }
	    }
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      var _this2 = this;

	      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      this.processLoaderIcon.style.transform = "scale(0)";
	      clearTimeout(this.timeoutSetIcon);
	      this.timeoutSetIcon = setTimeout(function () {
	        _this2.processLoaderIcon.classList.remove("ui-file-progressbar-cancel", "ui-file-progressbar-done", "ui-file-progressbar-cloud", "ui-file-progressbar-error");

	        if (icon === Uploader.icon.done) {
	          _this2.processLoaderIcon.classList.add("ui-file-progressbar-done");

	          _this2.processLoaderIcon.style.transform = "scale(1)";
	        } else if (icon === Uploader.icon.cancel) {
	          _this2.processLoaderIcon.classList.add("ui-file-progressbar-cancel");

	          _this2.processLoaderIcon.style.transform = "scale(1)";
	        } else if (icon === Uploader.icon.error) {
	          _this2.processLoaderIcon.classList.add("ui-file-progressbar-error");

	          _this2.processLoaderIcon.style.transform = "scale(1)";
	        } else {
	          _this2.processLoaderIcon.classList.add("ui-file-progressbar-cloud");

	          _this2.processLoaderIcon.style.transform = "scale(1)";
	        }
	      }, force ? 0 : 200);
	      return true;
	    }
	  }, {
	    key: "setProgress",
	    value: function setProgress(percent) {
	      if (!this.active || this.canceled) {
	        return false;
	      }

	      this.bar.animate(percent / 100, {
	        duration: 500
	      });
	    }
	  }, {
	    key: "setProgressTitle",
	    value: function setProgressTitle(text) {
	      if (!this.proccesLabel) {
	        return false;
	      }

	      this.proccesLabel.innerHTML = text;
	    }
	  }, {
	    key: "setProgressTitleVisibility",
	    value: function setProgressTitleVisibility(visible) {
	      if (!this.proccesLabel) {
	        return;
	      }

	      if (visible) {
	        if (this.direction === Uploader.direction.horizontal) {
	          this.wrapper.classList.add('ui-file-progressbar-loader-horizontal');
	        }

	        this.proccesLabel.style.display = 'block';
	      } else {
	        if (this.direction === Uploader.direction.horizontal) {
	          this.wrapper.classList.remove('ui-file-progressbar-loader-horizontal');
	        }

	        this.proccesLabel.style.display = 'none';
	      }
	    }
	  }, {
	    key: "setByteSent",
	    value: function setByteSent(sent, total) {
	      if (this.canceled) {
	        return false;
	      }

	      this.setProgressTitle((sent / 1024 / 1024).toFixed(2) + " " + this.labels.megabyte + " " + " / " + (total / 1024 / 1024).toFixed(2) + " " + this.labels.megabyte);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      var _this3 = this;

	      var animated = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      clearTimeout(this.timeoutSetIcon);
	      clearTimeout(this.timeout);

	      if (this.destroing) {
	        return true;
	      }

	      this.active = false;
	      this.destroing = true;
	      this.processLoader.style.transform = "scale(0)";

	      if (this.proccesLabel) {
	        this.proccesLabel.style.transform = "scale(0)";
	      }

	      if (this.bar) {
	        this.bar.destroy();
	      }

	      if (this.blurElement) {
	        this.blurElement.classList.remove("ui-file-progressbar-item-blurred");
	      }

	      if (this.canceled && !this.cancelCallbackDisabled) {
	        if (this.cancelCallback) {
	          this.cancelCallback(this.canceled);
	        }

	        this.canceled = false;
	      }

	      if (animated) {
	        this.timeout = setTimeout(function () {
	          return _this3.destroyFinally();
	        }, 400);
	      } else {
	        this.destroyFinally();
	      }
	    }
	  }, {
	    key: "destroyFinally",
	    value: function destroyFinally() {
	      if (this.container) {
	        this.container.classList.remove('ui-file-progressbar-container-relative');
	        this.container.removeChild(this.wrapper);
	      }

	      if (this.destroyCallback) {
	        this.destroyCallback();
	      }
	    }
	  }]);
	  return Uploader;
	}();
	Uploader.direction = {
	  horizontal: 'horizontal',
	  vertical: 'vertical'
	};
	Uploader.icon = {
	  cloud: 'cloud',
	  cancel: 'cancel',
	  error: 'error',
	  done: 'done'
	};

	exports.Uploader = Uploader;

}((this.BX.ProgressBarJs = this.BX.ProgressBarJs || {})));
//# sourceMappingURL=uploader.bundle.js.map
