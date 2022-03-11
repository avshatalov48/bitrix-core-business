this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var Tooltip = /*#__PURE__*/function () {
	  function Tooltip() {
	    babelHelpers.classCallCheck(this, Tooltip);
	  }

	  babelHelpers.createClass(Tooltip, null, [{
	    key: "disable",
	    value: function disable() {
	      this.disabled = true;
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      this.disabled = false;
	    }
	  }, {
	    key: "getDisabledStatus",
	    value: function getDisabledStatus() {
	      return this.disabled;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      return '/bitrix/tools/tooltip.php';
	    }
	  }, {
	    key: "getIdPrefix",
	    value: function getIdPrefix() {
	      return 'bx-ui-tooltip-';
	    }
	  }]);
	  return Tooltip;
	}();
	babelHelpers.defineProperty(Tooltip, "disabled", false);
	babelHelpers.defineProperty(Tooltip, "tooltipsList", {});

	var TooltipBalloon = /*#__PURE__*/function () {
	  function TooltipBalloon(params) {
	    babelHelpers.classCallCheck(this, TooltipBalloon);
	    this.node = null;
	    this.userId = null;
	    this.loader = null;
	    this.version = null;
	    this.tracking = false;
	    this.active = false;
	    this.width = 364; // 393

	    this.height = 215; // 302

	    this.realAnchor = null;
	    this.coordsLeft = 0;
	    this.coordsTop = 0;
	    this.anchorRight = 0;
	    this.anchorTop = 0;
	    this.hMirror = false;
	    this.vMirror = false;
	    this.rootClassName = '';
	    this.INFO = null;
	    this.DIV = null;
	    this.ROOT_DIV = null;
	    this.params = {};
	    this.trackMouseHandle = this.trackMouse.bind(this);
	    this.init(params);
	    this.create();
	    return this;
	  }

	  babelHelpers.createClass(TooltipBalloon, [{
	    key: "init",
	    value: function init(params) {
	      this.node = params.node;
	      this.userId = params.userId;
	      this.loader = main_core.Type.isStringFilled(params.loader) ? params.loader : '';
	      this.version = !main_core.Type.isUndefined(params.version) && parseInt(params.version) > 0 ? parseInt(params.version) : main_core.Type.isStringFilled(this.loader) ? 2 : 3;
	      this.rootClassName = this.node.getAttribute('bx-tooltip-classname');
	      var paramsString = this.node.getAttribute('bx-tooltip-params');
	      var anchorParams = {};

	      if (main_core.Type.isStringFilled(paramsString)) {
	        anchorParams = JSON.parse(paramsString);

	        if (!main_core.Type.isPlainObject(anchorParams)) {
	          anchorParams = {};
	        }
	      }

	      this.params = anchorParams;
	    }
	  }, {
	    key: "create",
	    value: function create() {
	      if (!Tooltip.getDisabledStatus()) {
	        this.startTrackMouse();
	      }

	      this.node.addEventListener('mouseout', this.stopTrackMouse.bind(this));
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpen', this.onSliderOpen.bind(this));
	    }
	  }, {
	    key: "onSliderOpen",
	    value: function onSliderOpen() {
	      if (this.tracking) {
	        this.stopTrackMouse();
	      } else {
	        this.hideTooltip();
	      }
	    }
	  }, {
	    key: "startTrackMouse",
	    value: function startTrackMouse() {
	      var _this = this;

	      if (this.tracking) {
	        return;
	      }

	      var elCoords = BX.pos(this.node);
	      this.realAnchor = this.node;
	      this.coordsLeft = elCoords.width < 40 ? elCoords.left - 35 : elCoords.left + 0;
	      this.coordsTop = elCoords.top - 245; // 325

	      this.anchorRight = elCoords.right;
	      this.anchorTop = elCoords.top;
	      this.tracking = true;
	      document.addEventListener('mousemove', this.trackMouseHandle);
	      setTimeout(function () {
	        _this.tickTimer();
	      }, 500);
	      this.node.addEventListener('mouseout', this.stopTrackMouse.bind(this));
	    }
	  }, {
	    key: "stopTrackMouse",
	    value: function stopTrackMouse() {
	      var _this2 = this;

	      if (!this.tracking) {
	        return;
	      }

	      document.removeEventListener('mousemove', this.trackMouseHandle);
	      this.active = false;
	      setTimeout(function () {
	        _this2.hideTooltip();
	      }, 500);
	      this.tracking = false;
	    }
	  }, {
	    key: "trackMouse",
	    value: function trackMouse(e) {
	      if (!this.tracking) {
	        return;
	      }

	      var current = e && e.pageX ? {
	        x: e.pageX,
	        y: e.pageY
	      } : {
	        x: e.clientX + document.body.scrollLeft,
	        y: e.clientY + document.body.scrollTop
	      };

	      if (current.x < 0) {
	        current.x = 0;
	      }

	      if (current.y < 0) {
	        current.y = 0;
	      }

	      current.time = this.tracking;

	      if (!this.active) {
	        this.active = current;
	      } else {
	        if (this.active.x >= current.x - 1 && this.active.x <= current.x + 1 && this.active.y >= current.y - 1 && this.active.y <= current.y + 1) {
	          if (this.active.time + 20
	          /*2sec*/
	          <= current.time) {
	            this.showTooltip();
	          }
	        } else {
	          this.active = current;
	        }
	      }
	    }
	  }, {
	    key: "tickTimer",
	    value: function tickTimer() {
	      var _this3 = this;

	      if (!this.tracking) {
	        return;
	      }

	      this.tracking++;

	      if (this.active) {
	        if (this.active.time + 5
	        /*0.5sec*/
	        <= this.tracking) {
	          this.showTooltip();
	        }
	      }

	      setTimeout(function () {
	        _this3.tickTimer();
	      }, 100);
	    }
	  }, {
	    key: "hideTooltip",
	    value: function hideTooltip() {
	      if (this.tracking) {
	        return;
	      }

	      this.showOpacityEffect(1);
	    }
	  }, {
	    key: "showOpacityEffect",
	    value: function showOpacityEffect(bFade) {
	      var _this4 = this;

	      var steps = 3;
	      var period = 1;
	      var delta = 1 / steps;
	      var i = 0;
	      var intId = setInterval(function () {
	        i++;

	        if (i > steps) {
	          clearInterval(intId);
	          return;
	        }

	        var op = bFade ? 1 - i * delta : i * delta;

	        if (_this4.DIV != null) {
	          try {
	            _this4.DIV.style.opacity = op;
	          } catch (e) {} finally {
	            if (!bFade && i == 1) {
	              _this4.DIV.classList.add('ui-tooltip-info-shadow-show');

	              _this4.DIV.style.display = 'block';
	            }

	            if (bFade && i == steps && _this4.DIV) {
	              _this4.DIV.classList.remove('ui-tooltip-info-shadow-show');

	              _this4.DIV.classList.add('ui-tooltip-info-shadow-hide');

	              setTimeout(function () {
	                _this4.DIV.style.display = 'none';
	              }, 500);
	            }

	            if (bFade) {
	              main_core_events.EventEmitter.emit('onTooltipHide', new main_core_events.BaseEvent({
	                compatData: [_this4]
	              }));
	            }
	          }
	        }
	      }, period);
	    }
	  }, {
	    key: "showTooltip",
	    value: function showTooltip() {
	      var _this5 = this;

	      var old = document.getElementById("".concat(Tooltip.getIdPrefix()).concat(this.userId));

	      if (Tooltip.getDisabledStatus() || old && old.classList.contains('ui-tooltip-info-shadow-show')) {
	        return;
	      }

	      if (null == this.DIV && null == this.ROOT_DIV) {
	        this.ROOT_DIV = document.body.appendChild(document.createElement('DIV'));
	        this.ROOT_DIV.style.position = 'absolute';
	        BX.ZIndexManager.register(this.ROOT_DIV);
	        this.DIV = this.ROOT_DIV.appendChild(document.createElement('DIV'));
	        this.DIV.className = 'bx-ui-tooltip-info-shadow';
	        this.DIV.style.width = "".concat(this.width, "px");
	      }

	      var left = this.coordsLeft;
	      var top = this.coordsTop + 30;
	      var arScroll = BX.GetWindowScrollPos();
	      var body = document.body;
	      this.hMirror = false;
	      this.vMirror = top - arScroll.scrollTop < 0;

	      if (body.clientWidth + arScroll.scrollLeft < left + this.width) {
	        left = this.anchorRight - this.width;
	        this.hMirror = true;
	      }

	      this.ROOT_DIV.style.left = "".concat(parseInt(left), "px");
	      this.ROOT_DIV.style.top = "".concat(parseInt(top), "px");
	      BX.ZIndexManager.bringToFront(this.ROOT_DIV);
	      this.ROOT_DIV.addEventListener('click', function (e) {
	        e.stopPropagation();
	      });

	      if (main_core.Type.isStringFilled(this.rootClassName)) {
	        this.ROOT_DIV.className = this.rootClassName;
	      }

	      var loader = main_core.Type.isStringFilled(this.loader) ? this.loader : Tooltip.getLoader(); // create stub

	      var stubCreated = false;

	      if ('' == this.DIV.innerHTML) {
	        stubCreated = true;

	        if (this.version >= 3) {
	          main_core.ajax.runComponentAction('bitrix:ui.tooltip', 'getData', {
	            mode: 'ajax',
	            data: {
	              userId: this.userId,
	              params: !main_core.Type.isUndefined(this.params) ? this.params : {}
	            }
	          }).then(function (response) {
	            var detailUrl = main_core.Type.isStringFilled(response.data.user.detailUrl) ? response.data.user.detailUrl : '';
	            var cardUserName = '';

	            if (main_core.Type.isStringFilled(response.data.user.nameFormatted)) {
	              if (main_core.Type.isStringFilled(detailUrl)) {
	                cardUserName = "<a href=\"".concat(detailUrl, "\">").concat(response.data.user.nameFormatted, "</a>");
	              } else {
	                cardUserName = response.data.user.nameFormatted;
	              }
	            }

	            var cardFields = '<div class="bx-ui-tooltip-info-data-info">';
	            Object.keys(response.data.user.cardFields).forEach(function (fieldCode) {
	              cardFields += "<span class=\"bx-ui-tooltip-field-row bx-ui-tooltip-field-row-".concat(fieldCode.toLowerCase(), "\"><span class=\"bx-ui-tooltip-field-name\">").concat(response.data.user.cardFields[fieldCode].name, "</span>: <span class=\"bx-ui-tooltip-field-value\">").concat(response.data.user.cardFields[fieldCode].value, "</span></span>");
	            });
	            cardFields += '</div>';
	            var cardFieldsClassName = parseInt(main_core.Loc.getMessage('USER_ID')) > 0 && response.data.currentUserPerms.operations.videocall ? 'bx-ui-tooltip-info-data-cont-video' : 'bx-ui-tooltip-info-data-cont';
	            cardFields = "<div id=\"bx_user_info_data_cont_".concat(response.data.user.id, "\" class=\"").concat(cardFieldsClassName, "\">").concat(cardFields, "</div>");
	            var photo = '';
	            var photoClassName = 'bx-ui-tooltip-info-data-photo no-photo';

	            if (main_core.Type.isStringFilled(response.data.user.photo)) {
	              photo = response.data.user.photo;
	              photoClassName = 'bx-ui-tooltip-info-data-photo';
	            }

	            photo = main_core.Type.isStringFilled(detailUrl) ? "<a href=\"".concat(detailUrl, "\" class=\"").concat(photoClassName, "\">").concat(photo, "</a>") : "<span class=\"".concat(photoClassName, "\">").concat(photo, "</span>");
	            var toolbar = '';
	            var toolbar2 = '';

	            if (parseInt(main_core.Loc.getMessage('USER_ID')) > 0 && response.data.user.active && response.data.user.id != main_core.Loc.getMessage('USER_ID') && response.data.currentUserPerms.operations.message) {
	              toolbar2 += "<li class=\"bx-icon bx-icon-message\"><span onclick=\"return BX.tooltip.openIM(".concat(response.data.user.id, ");\">").concat(main_core.Loc.getMessage('MAIN_UL_TOOLBAR_MESSAGES_CHAT'), "</span></li>");
	              toolbar2 += "<li id=\"im-video-call-button".concat(response.data.user.id, "\" class=\"bx-icon bx-icon-video\"><span onclick=\"return BX.tooltip.openCallTo(").concat(response.data.user.id, ");\">").concat(main_core.Loc.getMessage('MAIN_UL_TOOLBAR_VIDEO_CALL'), "</span></li>");
	              toolbar2 += "<script>Event.ready(() => { BX.tooltip.checkCallTo(\"im-video-call-button".concat(response.data.user.id, "\"); };</script>");
	            }

	            toolbar2 = main_core.Type.isStringFilled(toolbar2) ? "<div class=\"bx-ui-tooltip-info-data-separator\"></div><ul>".concat(toolbar2, "</ul>") : '';

	            if (response.data.user.hasBirthday) {
	              toolbar += "<li class=\"bx-icon bx-icon-birth\">".concat(main_core.Loc.getMessage('MAIN_UL_TOOLBAR_BIRTHDAY'), "</li>");
	            }

	            if (response.data.user.hasHonour) {
	              toolbar += "<li class=\"bx-icon bx-icon-featured\">".concat(main_core.Loc.getMessage('MAIN_UL_TOOLBAR_HONORED'), "</li>");
	            }

	            if (response.data.user.hasAbsence) {
	              toolbar += "<li class=\"bx-icon bx-icon-away\">".concat(main_core.Loc.getMessage('MAIN_UL_TOOLBAR_ABSENT'), "</li>");
	            }

	            toolbar = main_core.Type.isStringFilled(toolbar) ? "<ul>".concat(toolbar, "</ul>") : '';

	            _this5.insertData({
	              RESULT: {
	                Name: cardUserName,
	                Position: main_core.Type.isStringFilled(response.data.user.position) ? response.data.user.position : '',
	                Card: cardFields,
	                Photo: photo,
	                Toolbar: toolbar,
	                Toolbar2: toolbar2
	              }
	            });

	            _this5.adjustPosition();
	          }, function () {});
	        } else {
	          var url = loader + (loader.indexOf('?') >= 0 ? '&' : '?') + "MODE=UI&MUL_MODE=INFO&USER_ID=".concat(this.userId) + "&site=".concat(main_core.Loc.getMessage('SITE_ID') || '') + "&version=".concat(this.version) + (!main_core.Type.isUndefined(this.params) && !main_core.Type.isUndefined(this.params.entityType) && main_core.Type.isStringFilled(this.params.entityType) ? "&entityType=".concat(this.params.entityType) : '') + (!main_core.Type.isUndefined(this.params) && !main_core.Type.isUndefined(this.params.entityId) && parseInt(this.params.entityId) > 0 ? "&entityId=".concat(parseInt(this.params.entityId)) : '');
	          main_core.ajax.get(url, function (data) {
	            _this5.insertData(data);

	            _this5.adjustPosition();
	          });
	        }

	        this.DIV.id = "".concat(Tooltip.getIdPrefix()).concat(this.userId);
	        this.DIV.innerHTML = '<div class="bx-ui-tooltip-info-wrap">' + '<div class="bx-ui-tooltip-info-leftcolumn">' + "<div class=\"bx-ui-tooltip-photo\" id=\"".concat(Tooltip.getIdPrefix(), "photo-").concat(this.userId, "\"><div class=\"bx-ui-tooltip-info-data-loading\">").concat(main_core.Loc.getMessage('JS_CORE_LOADING'), "</div></div>") + '</div>' + '<div class="bx-ui-tooltip-info-data">' + "<div id=\"".concat(Tooltip.getIdPrefix(), "data-card-").concat(this.userId, "\"></div>") + '<div class="bx-ui-tooltip-info-data-tools">' + "<div class=\"bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-left\" id=\"".concat(Tooltip.getIdPrefix(), "toolbar-").concat(this.userId, "\"></div>") + "<div class=\"bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-right\" id=\"".concat(Tooltip.getIdPrefix(), "toolbar2-").concat(this.userId, "\"></div>") + '<div class="bx-ui-tooltip-info-data-clear"></div>' + '</div>' + '</div>' + '</div><div class="bx-ui-tooltip-info-bottomarea"></div>';
	      }

	      this.DIV.className = 'bx-ui-tooltip-info-shadow';
	      this.classNameAnim = 'bx-ui-tooltip-info-shadow-anim';
	      this.classNameFixed = 'bx-ui-tooltip-info-shadow';

	      if (this.hMirror && this.vMirror) {
	        this.DIV.className = 'bx-ui-tooltip-info-shadow-hv';
	        this.classNameAnim = 'bx-ui-tooltip-info-shadow-hv-anim';
	        this.classNameFixed = 'bx-ui-tooltip-info-shadow-hv';
	      } else {
	        if (this.hMirror) {
	          this.DIV.className = 'bx-ui-tooltip-info-shadow-h';
	          this.classNameAnim = 'bx-ui-tooltip-info-shadow-h-anim';
	          this.classNameFixed = 'bx-ui-tooltip-info-shadow-h';
	        }

	        if (this.vMirror) {
	          this.DIV.className = 'bx-ui-tooltip-info-shadow-v';
	          this.classNameAnim = 'bx-ui-tooltip-info-shadow-v-anim';
	          this.classNameFixed = 'bx-ui-tooltip-info-shadow-v';
	        }
	      }

	      this.DIV.style.display = 'block';

	      if (!stubCreated) {
	        this.adjustPosition();
	      }

	      this.showOpacityEffect(0);

	      document.getElementById("".concat(Tooltip.getIdPrefix()).concat(this.userId)).onmouseover = function () {
	        _this5.startTrackMouse(_this5);
	      };

	      document.getElementById("".concat(Tooltip.getIdPrefix()).concat(this.userId)).onmouseout = function () {
	        _this5.stopTrackMouse(_this5);
	      };

	      main_core_events.EventEmitter.emit('onTooltipShow', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition() {
	      var tooltipCoords = BX.pos(this.DIV);

	      if (this.vMirror) {
	        this.ROOT_DIV.style.top = "".concat(parseInt(this.anchorTop + 13), "px");
	      } else {
	        this.ROOT_DIV.style.top = "".concat(parseInt(this.anchorTop - tooltipCoords.height - 13 + 12), "px"); // 12 - bottom block
	      }
	    }
	  }, {
	    key: "insertData",
	    value: function insertData(data) {
	      if (null != data && (this.version >= 3 || data.length > 0)) {
	        if (this.version >= 3) {
	          this.INFO = data;
	        } else {
	          eval("this.INFO = ".concat(data));
	        }

	        var cardEl = document.getElementById("".concat(Tooltip.getIdPrefix(), "data-card-").concat(this.userId));
	        cardEl.innerHTML = '';

	        if (main_core.Type.isStringFilled(this.INFO.RESULT.Name)) {
	          cardEl.innerHTML += "<div class=\"bx-ui-tooltip-user-name\">".concat(this.INFO.RESULT.Name, "</div>");
	        }

	        if (main_core.Type.isStringFilled(this.INFO.RESULT.Position)) {
	          cardEl.innerHTML += "<div class=\"bx-ui-tooltip-user-position\">".concat(this.INFO.RESULT.Position, "</div>");
	        }

	        cardEl.innerHTML += this.INFO.RESULT.Card;
	        var photoEl = document.getElementById("".concat(Tooltip.getIdPrefix(), "photo-").concat(this.userId));
	        photoEl.innerHTML = this.INFO.RESULT.Photo;
	        var toolbarEl = document.getElementById("".concat(Tooltip.getIdPrefix(), "toolbar-").concat(this.userId));
	        toolbarEl.innerHTML = this.INFO.RESULT.Toolbar;
	        var toolbar2El = document.getElementById("".concat(Tooltip.getIdPrefix(), "toolbar2-").concat(this.userId));
	        toolbar2El.innerHTML = this.INFO.RESULT.Toolbar2;

	        if (main_core.Type.isArray(this.INFO.RESULT.Scripts)) {
	          this.INFO.RESULT.Scripts.forEach(function (script) {
	            eval(script);
	          });
	        }

	        main_core_events.EventEmitter.emit('onTooltipInsertData', new main_core_events.BaseEvent({
	          compatData: [this]
	        }));
	      }
	    }
	  }]);
	  return TooltipBalloon;
	}();

	main_core.Event.ready(function () {
	  if (main_core.Browser.isAndroid() || main_core.Browser.isIOS()) {
	    return;
	  }

	  document.addEventListener('mouseover', function (e) {
	    var node = e.target;
	    var userId = node.getAttribute('bx-tooltip-user-id');
	    var loader = node.getAttribute('bx-tooltip-loader');
	    var tooltipId = userId; // don't use integer value!

	    if (main_core.Type.isStringFilled(loader)) {
	      var loaderHash = 0;
	      babelHelpers.toConsumableArray(loader).forEach(function (c, i) {
	        loaderHash = 31 * loaderHash + loader.charCodeAt(i) << 0;
	      });
	      tooltipId = loaderHash + userId;
	    }

	    if (main_core.Type.isStringFilled(userId)) {
	      if (null == Tooltip.tooltipsList[tooltipId]) {
	        Tooltip.tooltipsList[tooltipId] = new TooltipBalloon({
	          userId: userId,
	          node: node,
	          loader: loader
	        });
	      } else {
	        Tooltip.tooltipsList[tooltipId].node = node;
	        Tooltip.tooltipsList[tooltipId].create();
	      }

	      e.preventDefault();
	    }
	  });
	});

	exports.Tooltip = Tooltip;
	exports.TooltipBalloon = TooltipBalloon;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=tooltip.js.map
