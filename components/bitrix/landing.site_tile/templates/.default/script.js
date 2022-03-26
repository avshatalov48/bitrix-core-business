this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_popup,ui_dialogs_messagebox,main_core_events,ui_notification,main_core) {
	'use strict';

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a href=\"", "\" class=\"landing-sites__title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"landing-sites__title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__title-text --sub\">\n\t\t\t\t\t", "\n\t\t\t\t</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input\n\t\t\t\tvalue=\"", "\"\n\t\t\t\ttype=\"text\"\n\t\t\t\tclass=\"landing-sites__title-input\">\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__title-edit\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var EditableTitle = /*#__PURE__*/function () {
	  function EditableTitle(options) {
	    babelHelpers.classCallCheck(this, EditableTitle);
	    this.title = options.title;
	    this.phone = options.phone;
	    this.type = options.type;
	    this.item = options.item;
	    this.url = options.url;
	    this.disabled = options.disabled || false;
	    this.isEditMode = false;
	    this.$container = null;
	    this.$containerInput = null;
	    this.$containerTitle = null;
	    this.$containerEditIcon = null;
	    this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
	    this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	  }

	  babelHelpers.createClass(EditableTitle, [{
	    key: "getContainerEdit",
	    value: function getContainerEdit() {
	      if (!this.$containerEditIcon) {
	        this.$containerEditIcon = main_core.Tag.render(_templateObject()); // Event.bind(this.$containerEditIcon, 'click', this.adjustEditMode.bind(this));
	      }

	      return this.$containerEditIcon;
	    }
	  }, {
	    key: "adjustEditMode",
	    value: function adjustEditMode() {
	      this.isEditMode ? this.closeEdit() : this.openEdit();
	    }
	  }, {
	    key: "openEdit",
	    value: function openEdit() {
	      this.isEditMode = true;
	      this.getContainer().classList.add('--edit');
	      this.getContainerInput().select();
	      this.getContainerInput().focus();
	      this.getContainerInput().value = this.title;
	      main_core.Event.bind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	    }
	  }, {
	    key: "adjustCloseEditByClick",
	    value: function adjustCloseEditByClick(ev) {
	      if (ev.type !== 'click') {
	        return;
	      }

	      if (ev.target !== this.getContainerInput() && ev.target !== this.getContainerEdit()) {
	        this.closeEdit();
	      }
	    }
	  }, {
	    key: "adjustCloseEditByKeyDown",
	    value: function adjustCloseEditByKeyDown(ev) {
	      if (ev.type !== 'keydown') {
	        return;
	      }

	      if (ev.keyCode === 27) // close by Escape
	        {
	          this.closeEdit();
	          return;
	        }

	      if (ev.keyCode === 13) // close by Enter
	        {
	          this.closeEdit();
	          this.updateTitle(this.getContainerInput().value);
	        }
	    }
	  }, {
	    key: "closeEdit",
	    value: function closeEdit() {
	      this.isEditMode = false;
	      this.getContainer().classList.remove('--edit');
	      main_core.Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	    }
	  }, {
	    key: "updateTitle",
	    value: function updateTitle(title) {
	      if (this.getContainerInput().value !== this.getContainerTitle().innerText && this.getContainerInput().value !== '') {
	        this.title = title;
	        this.getContainerTitle().innerText = title;
	        var type = this.type[0].toUpperCase() + this.type.slice(1);
	        main_core_events.EventEmitter.emit('BX.Landing.SiteTile:update' + type, {
	          item: this.item,
	          title: this.title
	        });
	      }
	    }
	  }, {
	    key: "getContainerInput",
	    value: function getContainerInput() {
	      if (!this.$containerInput) {
	        this.$containerInput = main_core.Tag.render(_templateObject2(), main_core.Text.encode(this.title));
	      }

	      return this.$containerInput;
	    }
	  }, {
	    key: "getContainerTitle",
	    value: function getContainerTitle() {
	      if (!this.$containerTitle) {
	        var value;

	        if (this.phone) {
	          value = this.phone;
	        }

	        if (this.title) {
	          value = this.title;
	        }

	        this.$containerTitle = main_core.Tag.render(_templateObject3(), main_core.Text.encode(value));
	      }

	      return this.$containerTitle;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        if (this.disabled) {
	          this.$container = main_core.Tag.render(_templateObject4(), this.getContainerInput(), this.getContainerTitle());
	        } else {
	          this.$container = main_core.Tag.render(_templateObject5(), this.url, this.getContainerInput(), this.getContainerTitle(), this.getContainerEdit());
	        }
	      }

	      return this.$container;
	    }
	  }], [{
	    key: "getTitle",
	    get: function get() {
	      return this.title;
	    }
	  }]);
	  return EditableTitle;
	}();

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__helper landing-sites__helper-", "\">\n\t\t\t\t\t<div class=\"landing-sites__helper-title\">\n\t\t\t\t\t\t<div class=\"landing-sites__helper-title-text\">", "</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-sites__helper-container\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-sites__helper-item ", "\">\n\t\t\t\t\t\t<div class=\"landing-sites__helper-item-title\">", "</div>\n\t\t\t\t\t\t<div class=\"landing-sites__helper-item-container\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__helper-item-text\">", "</div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__helper-item-button ", "\"\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__helper-list\"></div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__helper-close-toggler\">", "</div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var LeaderShip = /*#__PURE__*/function () {
	  function LeaderShip(options) {
	    babelHelpers.classCallCheck(this, LeaderShip);
	    this.id = options.id;
	    this.item = options.item;
	    this.articles = options.articles || [];
	    this.$container = null;
	    this.$containerClose = null;
	    this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
	    this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	  }

	  babelHelpers.createClass(LeaderShip, [{
	    key: "show",
	    value: function show() {
	      this.getContainer().classList.add('--show');
	      main_core.Event.bind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	      main_core_events.EventEmitter.emit('BX.Landing.SiteTile:showLeadership', this.item);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.getContainer().classList.remove('--show');
	      main_core.Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	      main_core_events.EventEmitter.emit('BX.Landing.SiteTile:hideLeadership', this.item);
	    }
	  }, {
	    key: "adjustCloseEditByClick",
	    value: function adjustCloseEditByClick(ev) {
	      if (ev.type !== 'click') {
	        return;
	      }

	      if (!ev.target.closest('.landing-sites__helper-' + this.id) && ev.target.className !== 'landing-sites__preview-leadership-text') {
	        this.hide();
	      }
	    }
	  }, {
	    key: "adjustCloseEditByKeyDown",
	    value: function adjustCloseEditByKeyDown(ev) {
	      if (ev.type !== 'keydown') {
	        return;
	      }

	      if (ev.keyCode === 27) // close by Escape
	        {
	          this.hide();
	        }
	    }
	  }, {
	    key: "getContainerClose",
	    value: function getContainerClose() {
	      if (!this.$containerClose) {
	        this.$containerClose = main_core.Tag.render(_templateObject$1(), main_core.Loc.getMessage('LANDING_SITE_TILE_HIDE'));
	        main_core.Event.bind(this.$containerClose, 'click', this.hide.bind(this));
	      }

	      return this.$containerClose;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        var articlesNode = main_core.Tag.render(_templateObject2$1());

	        for (var i = 0; i < this.articles.length; i++) {
	          var item = this.articles[i];
	          articlesNode.appendChild(main_core.Tag.render(_templateObject3$1(), item.read ? '--read' : '', item.title, item.text, item.read ? '--read' : '', item.read ? main_core.Loc.getMessage('LANDING_SITE_TILE_READ') : main_core.Loc.getMessage('LANDING_SITE_TILE_TO_READ')));
	        }

	        this.$container = main_core.Tag.render(_templateObject4$1(), this.id, main_core.Loc.getMessage('LANDING_SITE_TILE_LEADERSHIP_TITLE'), this.getContainerClose(), articlesNode);
	      }

	      return this.$container;
	    }
	  }]);
	  return LeaderShip;
	}();

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__popup\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__popup-container --hide-left\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-sites__popup-wrapper\">\n\t\t\t\t\t\t<div class=\"landing-sites__popup-title\">\n\t\t\t\t\t\t\t<span class=\"landing-sites__popup-title-text\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"landing-sites__popup-content\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-text\">\n\t\t\t\t\t\t\t\t<div class=\"landing-sites__popup-text --list\"><span>1</span> ", "</div>\n\t\t\t\t\t\t\t\t<div class=\"landing-sites__popup-text --list\"><span>2</span> ", "</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-buttons\">\n\t\t\t\t\t\t\t\t<a href=\"", "\" class=\"ui-btn ui-btn-success ui-btn-round\">", "</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__popup-prev\"></div>"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__popup-close\"></div>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__popup-container\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-sites__popup-wrapper\">\n\t\t\t\t\t\t<div class=\"landing-sites__popup-title\">\n\t\t\t\t\t\t\t<span class=\"landing-sites__popup-title-text\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"landing-sites__popup-content\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-text\">", "</div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-image --first-order\"></div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-buttons\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t<a href=\"", "\" class=\"ui-btn ui-btn-light-border ui-btn-round\">", "</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__popup-close\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-btn ui-btn-success ui-btn-round\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\t\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__popup-container --qr\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-sites__popup-wrapper\">\n\t\t\t\t\t\t<div class=\"landing-sites__popup-content\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-text\">", "</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"landing-sites__popup-buttons\">\n\t\t\t\t\t\t\t\t<a href=\"", "\" target=\"_blank\" class=\"ui-btn ui-btn-light-border ui-btn-round\">", "</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"landing-sites__popup-bottom\">\n\t\t\t\t\t\t\t<a href=\"", "\" target=\"_blank\" class=\"landing-sites__popup-url\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__popup-close\"></div>"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-sites__popup-image\"></div>\n\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__popup-copy\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\ttype=\"text\" \n\t\t\t\t\tstyle=\"position: absolute; opacity: 0; pointer-events: none\"\n\t\t\t\t\tvalue=\"", "\">\n\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var PopupHelper = /*#__PURE__*/function () {
	  function PopupHelper(options) {
	    babelHelpers.classCallCheck(this, PopupHelper);
	    this.id = options.id;
	    this.url = options.url;
	    this.fullUrl = options.fullUrl;
	    this.ordersUrl = options.ordersUrl;
	    this.qr = null;
	    this.$container = null;
	    this.$containerClose = null;
	    this.$containerFirstStep = null;
	    this.$containerSecondStep = null;
	    this.$containerQr = null;
	    this.$containerQrimage = null;
	    this.$containerInputUrl = null;
	    this.$containerCopyLink = null;
	    this.$containerTestOrder = null;
	    this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
	    this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	  }

	  babelHelpers.createClass(PopupHelper, [{
	    key: "hide",
	    value: function hide() {
	      this.getContainer().classList.remove('--show');
	      main_core.Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	      main_core_events.EventEmitter.emit(this, 'BX.Landing.SiteTile.Popup:onHide', this);
	    }
	  }, {
	    key: "show",
	    value: function show(param) {
	      this.getContainer().classList.add('--show');

	      if (param === 'link') {
	        this.getContainerFirstStep().style.display = 'none';
	        this.getContainerSecondStep().style.display = 'none';
	      } else {
	        this.getContainerFirstStep().style.display = null;
	        this.getContainerSecondStep().style.display = null;
	      }

	      main_core.Event.bind(document.body, 'click', this.adjustCloseEditByClick);
	      main_core.Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
	      main_core_events.EventEmitter.emit(this, 'BX.Landing.SiteTile.Popup:onShow', this);
	    }
	  }, {
	    key: "adjustCloseEditByClick",
	    value: function adjustCloseEditByClick(ev) {
	      if (ev.type !== 'click') {
	        return;
	      }

	      if (ev.target.closest('.landing-sites__popup') || ev.target.closest('.landing-sites__container-link-' + this.id) || ev.target.closest('.landing-sites__status-' + this.id)) {
	        return;
	      }

	      this.hide();
	    }
	  }, {
	    key: "adjustCloseEditByKeyDown",
	    value: function adjustCloseEditByKeyDown(ev) {
	      if (ev.type !== 'keydown') {
	        return;
	      }

	      if (ev.keyCode === 27) // close by Escape
	        {
	          this.hide();
	        }
	    }
	  }, {
	    key: "showSecondStep",
	    value: function showSecondStep() {
	      this.getContainerFirstStep().classList.add('--hide-right');
	      this.getContainerSecondStep().classList.remove('--hide-left');
	    }
	  }, {
	    key: "showFirstStep",
	    value: function showFirstStep() {
	      this.getContainerFirstStep().classList.remove('--hide-right');
	      this.getContainerSecondStep().classList.add('--hide-left');
	    }
	  }, {
	    key: "getContainerInputUrl",
	    value: function getContainerInputUrl() {
	      if (!this.$containerInputUrl) {
	        this.$containerInputUrl = main_core.Tag.render(_templateObject$2(), this.fullUrl);
	      }

	      return this.$containerInputUrl;
	    }
	  }, {
	    key: "getContainerCopyLink",
	    value: function getContainerCopyLink() {
	      var _this = this;

	      if (!this.$containerCopyLink) {
	        this.$containerCopyLink = main_core.Tag.render(_templateObject2$2(), main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_COPY_LINK'));
	        main_core.Event.bind(this.$containerCopyLink, 'click', function () {
	          _this.getContainerInputUrl().select();

	          document.execCommand('copy');
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_COPY_LINK_COMPLETE'),
	            autoHideDelay: 2000
	          });
	        });
	      }

	      return this.$containerCopyLink;
	    }
	  }, {
	    key: "getContainerQrImage",
	    value: function getContainerQrImage() {
	      var node = main_core.Tag.render(_templateObject3$2());
	      new QRCode(node, {
	        text: this.fullUrl,
	        width: 250,
	        height: 250
	      });
	      return node;
	    }
	  }, {
	    key: "getContainerQr",
	    value: function getContainerQr() {
	      if (!this.$containerQr) {
	        var closeIcon = main_core.Tag.render(_templateObject4$2());
	        main_core.Event.bind(closeIcon, 'click', this.hide.bind(this));
	        this.$containerQr = main_core.Tag.render(_templateObject5$1(), closeIcon, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_3'), this.getContainerQrImage(), this.fullUrl, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_SITE'), this.fullUrl, this.url, this.getContainerInputUrl(), this.getContainerCopyLink());
	      }

	      return this.$containerQr;
	    }
	  }, {
	    key: "getContainerTestOrder",
	    value: function getContainerTestOrder() {
	      if (!this.$containerTestOrder) {
	        this.$containerTestOrder = main_core.Tag.render(_templateObject6(), main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_CREATE_TEST_ORDER'));
	        main_core.Event.bind(this.$containerTestOrder, 'click', this.showSecondStep.bind(this));
	      }

	      return this.$containerTestOrder;
	    }
	  }, {
	    key: "getContainerFirstStep",
	    value: function getContainerFirstStep() {
	      if (!this.$containerFirstStep) {
	        var closeIcon = main_core.Tag.render(_templateObject7());
	        main_core.Event.bind(closeIcon, 'click', this.hide.bind(this));
	        this.$containerFirstStep = main_core.Tag.render(_templateObject8(), closeIcon, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_CREATE_TEST_ORDER'), main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_TEXT'), this.getContainerTestOrder(), this.ordersUrl, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_CRM'));
	      }

	      return this.$containerFirstStep;
	    }
	  }, {
	    key: "getContainerSecondStep",
	    value: function getContainerSecondStep() {
	      if (!this.$containerSecondStep) {
	        var closeIcon = main_core.Tag.render(_templateObject9());
	        var prevIcon = main_core.Tag.render(_templateObject10());
	        main_core.Event.bind(closeIcon, 'click', this.hide.bind(this));
	        main_core.Event.bind(prevIcon, 'click', this.showFirstStep.bind(this));
	        this.$containerSecondStep = main_core.Tag.render(_templateObject11(), closeIcon, prevIcon, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER'), main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_1'), main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_2'), this.getContainerQrImage(), this.ordersUrl, main_core.Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_CRM'));
	      }

	      return this.$containerSecondStep;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject12(), this.getContainerFirstStep(), this.getContainerSecondStep(), this.getContainerQr());
	      }

	      return this.$container;
	    }
	  }]);
	  return PopupHelper;
	}();

	function _templateObject21() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__grid-item ", "\">\n\t\t\t\t\t<div class=\"landing-sites__item\" id=\"landing-sites__grid-item--", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject21 = function _templateObject21() {
	    return data;
	  };

	  return data;
	}

	function _templateObject20() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__item-container\">\n\t\t\t\t\t<a href=\"", "\" class=\"landing-sites__preview\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject20 = function _templateObject20() {
	    return data;
	  };

	  return data;
	}

	function _templateObject19() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a href=\"", "\" class=\"landing-sites__container-link landing-sites__container-link-", " --white-bg--alpha\">\n\t\t\t\t<div class=\"landing-sites__container-link-icon --", "\"></div>\n\t\t\t\t<div class=\"landing-sites__container-link-text\">", "</div>\n\t\t\t</a>\n\t\t"]);

	  _templateObject19 = function _templateObject19() {
	    return data;
	  };

	  return data;
	}

	function _templateObject18() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__container --without-bg --auto-height --flex\"></div>"]);

	  _templateObject18 = function _templateObject18() {
	    return data;
	  };

	  return data;
	}

	function _templateObject17() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__preview-leadership\">\n\t\t\t\t\t<div class=\"landing-sites__preview-leadership-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject17 = function _templateObject17() {
	    return data;
	  };

	  return data;
	}

	function _templateObject16() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__preview-show\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject16 = function _templateObject16() {
	    return data;
	  };

	  return data;
	}

	function _templateObject15() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__preview-status --not-published ", "\">\n\t\t\t\t\t<div class=\"landing-sites__preview-status-wrapper\">\n\t\t\t\t\t\t<div class=\"landing-sites__preview-status-icon\"></div>\n\t\t\t\t\t\t<div class=\"landing-sites__preview-status-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject15 = function _templateObject15() {
	    return data;
	  };

	  return data;
	}

	function _templateObject14() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__preview-image ", "\"></div>"]);

	  _templateObject14 = function _templateObject14() {
	    return data;
	  };

	  return data;
	}

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__container --white-bg --white-bg--alpha --domain\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-sites__container-left\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-sites__container-right\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__status landing-sites__status-", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject12$1 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__sub-title\">", "</div>\n\t\t\t"]);

	  _templateObject11$1 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__status-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>"]);

	  _templateObject10$1 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__status-icon --", "\"></div>\n\t\t\t"]);

	  _templateObject9$1 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__container-status --", "\"></div>\n\t\t\t"]);

	  _templateObject8$1 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__container --white-bg\">\n\t\t\t\t\t<div class=\"landing-sites__container-left\">\n\t\t\t\t\t\t<div class=\"landing-sites__title\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__title-text\" title=\"", "\">", "</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-sites__container-right\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject7$1 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__status-title\">", "</div>"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__status-round ", "\"></div>"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__more\"></div>"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__status-arrow\"></div>"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__title\">\n\t\t\t\t\t<div class=\"landing-sites__title-text\">", "</div>\n\t\t\t\t\t<div class=\"landing-sites__title-edit\"></div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Item = /*#__PURE__*/function () {
	  function Item(options) {
	    babelHelpers.classCallCheck(this, Item);
	    this.id = options.id;
	    this.grid = options.grid;
	    this.title = options.title;
	    this.url = options.url;
	    this.fullUrl = options.fullUrl;
	    this.domainProvider = options.domainProvider;
	    this.pagesUrl = options.pagesUrl;
	    this.ordersUrl = options.ordersUrl;
	    this.domainUrl = options.domainUrl;
	    this.contactsUrl = options.contactsUrl;
	    this.ordersCount = options.ordersCount;
	    this.phone = options.phone;
	    this.preview = options.preview;
	    this.published = options.published;
	    this.deleted = options.deleted;
	    this.domainStatus = options.domainStatus;
	    this.domainStatusMessage = options.domainStatusMessage;
	    this.menuItems = options.menuItems || [];
	    this.menuBottomItems = options.menuBottomItems || [];
	    this.access = options.access || {};
	    this.articles = options.articles || [];
	    this.editableTitle = null;
	    this.editableUrl = null;
	    this.leadership = null;
	    this.popupHelper = null;
	    this.popupStatus = null;
	    this.popupConfig = null;
	    this.loader = null;
	    this.$container = null;
	    this.$containerWrapper = null;
	    this.$containerPreviewImage = null;
	    this.$containerPreviewStatus = null;
	    this.$containerPreviewShowPages = null;
	    this.$containerPreviewInstruction = null;
	    this.$containerInfo = null;
	    this.$containerPhone = null;
	    this.$containerTitle = null;
	    this.$containerDomain = null;
	    this.$containerDomainLink = null;
	    this.$containerDomainStatus = null;
	    this.$containerDomainStatusIcon = null;
	    this.$containerDomainStatusTitle = null;
	    this.$containerDomainStatusMessage = null;
	    this.$containerSiteStatus = null;
	    this.$containerSiteStatusRound = null;
	    this.$containerSiteStatusTitle = null;
	    this.$containerSiteMore = null;
	    this.$containerLinks = null;
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Item, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe('BX.Landing.SiteTile:showLeadership', function (options) {
	        if (_this === options.data) {
	          _this.active();

	          _this.setContainerPosition();
	        }

	        if (_this !== options.data) {
	          _this.fade();
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.Landing.SiteTile:hideLeadership', function (options) {
	        if (_this === options.data) {
	          _this.unActive();

	          _this.unSetContainerPosition();
	        }

	        _this.unFade();
	      });
	      main_core_events.EventEmitter.subscribe(this.getPopupHelper(), 'BX.Landing.SiteTile.Popup:onShow', function () {
	        _this.getContainerWrapper().classList.add('--fade');
	      });
	      main_core_events.EventEmitter.subscribe(this.getPopupHelper(), 'BX.Landing.SiteTile.Popup:onHide', function () {
	        _this.getContainerWrapper().classList.remove('--fade');
	      });
	    }
	  }, {
	    key: "setContainerPosition",
	    value: function setContainerPosition() {
	      var offsetRight = window.innerWidth - this.getContainer().getBoundingClientRect().right;
	      var leaderShipWidth = this.getLeadership().getContainer().offsetWidth;
	      var previousItem = this.getContainer().previousSibling;

	      if (offsetRight > leaderShipWidth) {
	        return;
	      }

	      this.getContainer().style.transform = 'translateX(-' + (leaderShipWidth + 40 - offsetRight) + 'px)';

	      if (previousItem && previousItem.offsetTop === this.getContainer().offsetTop) {
	        previousItem.style.transform = 'translateX(-10px)';
	      }
	    }
	  }, {
	    key: "unSetContainerPosition",
	    value: function unSetContainerPosition() {
	      this.getContainer().style.transform = null;
	      var previousItem = this.getContainer().previousSibling;

	      if (previousItem && previousItem.offsetTop === this.getContainer().offsetTop) {
	        previousItem.style.transform = null;
	      }
	    }
	  }, {
	    key: "updatePublishedStatus",
	    value: function updatePublishedStatus(status) {
	      if (this.published === status) {
	        return;
	      }

	      this.popupStatus.destroy();
	      this.popupStatus = null;

	      if (status) {
	        this.published = true;
	        this.getContainerSiteStatusRound().className = 'landing-sites__status-round --success';
	        this.getContainerSiteStatusTitle().innerText = main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED');
	        this.getContainerPreviewStatus().classList.add('--hide');
	        return;
	      }

	      this.published = false;
	      this.getContainerSiteStatusRound().className = 'landing-sites__status-round --alert';
	      this.getContainerSiteStatusTitle().innerText = main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');
	      this.getContainerPreviewStatus().classList.remove('--hide');
	    }
	  }, {
	    key: "updateTitle",
	    value: function updateTitle(param) {
	      if (param) {
	        this.title = param;
	      }
	    }
	  }, {
	    key: "updateUrl",
	    value: function updateUrl(param) {
	      if (param) {
	        this.url = param;
	      }
	    }
	  }, {
	    key: "getContainerTitle",
	    value: function getContainerTitle() {
	      if (!this.$containerTitle) {
	        this.$containerTitle = main_core.Tag.render(_templateObject$3(), this.title);
	      }

	      return this.$containerTitle;
	    }
	  }, {
	    key: "mergeMenuItems",
	    value: function mergeMenuItems(items) {
	      var _this2 = this;

	      var addMenu = [{
	        text: this.deleted ? main_core.Loc.getMessage('LANDING_SITE_TILE_RESTORE') : main_core.Loc.getMessage('LANDING_SITE_TILE_REMOVE'),
	        access: 'delete',
	        onclick: function onclick() {
	          if (!_this2.deleted) {
	            var messageBox = new ui_dialogs_messagebox.MessageBox({
	              title: main_core.Loc.getMessage('LANDING_SITE_TILE_DELETE_ALERT_TITLE'),
	              message: main_core.Loc.getMessage('LANDING_SITE_TILE_DELETE_ALERT_MESSAGE'),
	              buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	              onOk: function onOk() {
	                main_core_events.EventEmitter.emit('BX.Landing.SiteTile:remove', [_this2, messageBox]);
	                messageBox.close();
	              },
	              popupOptions: {
	                autoHide: true,
	                closeByEsc: true,
	                minHeight: false,
	                minWidth: 260,
	                maxWidth: 300,
	                width: false,
	                animation: 'fading-slide'
	              }
	            });
	            messageBox.show();
	          } else {
	            main_core_events.EventEmitter.emit('BX.Landing.SiteTile:restore', _this2);

	            _this2.getPopupConfig().close();
	          }
	        }
	      }];
	      items.map(function (item, i) {
	        if (item.delimiter === true) ;

	        if (_this2.deleted) {
	          item.disabled = true;
	        }
	      });
	      addMenu.reverse().map(function (item) {
	        items.push(item);
	      });
	      return items;
	    }
	  }, {
	    key: "disableMenuItems",
	    value: function disableMenuItems(items) {
	      var _this3 = this;

	      items = items.map(function (item) {
	        if (item.access && _this3.access[item.access] !== true) {
	          item.disabled = true;
	        }

	        return item;
	      });
	      return items;
	    }
	  }, {
	    key: "getPopupConfig",
	    value: function getPopupConfig() {
	      var _this4 = this;

	      if (!this.popupConfig) {
	        this.popupConfig = new main_popup.PopupMenuWindow({
	          className: 'landing-sites__status-popup',
	          bindElement: this.getContainerSiteMore(),
	          offsetLeft: -61,
	          minWidth: 220,
	          closeByEsc: true,
	          autoHide: true,
	          angle: {
	            offset: 97
	          },
	          items: this.disableMenuItems(this.mergeMenuItems(this.menuItems)),
	          events: {
	            onPopupClose: function onPopupClose() {
	              _this4.getContainerSiteMore().classList.remove('--hover');
	            },
	            onPopupShow: function onPopupShow() {
	              _this4.getContainerSiteMore().classList.add('--hover');
	            }
	          },
	          animation: 'fading-slide'
	        });
	      }

	      return this.popupConfig;
	    }
	  }, {
	    key: "getPopupStatus",
	    value: function getPopupStatus() {
	      var _this5 = this;

	      if (!this.popupStatus) {
	        this.popupStatus = new main_popup.PopupMenuWindow({
	          className: 'landing-sites__status-popup',
	          bindElement: this.getContainerSiteStatus(),
	          minWidth: 220,
	          closeByEsc: true,
	          autoHide: true,
	          angle: {
	            offset: 97
	          },
	          items: [{
	            text: this.published ? main_core.Loc.getMessage('LANDING_SITE_TILE_UNPUBLISH') : main_core.Loc.getMessage('LANDING_SITE_TILE_PUBLISH'),
	            onclick: function onclick() {
	              _this5.popupStatus.close();

	              _this5.published ? main_core_events.EventEmitter.emit('BX.Landing.SiteTile:unPublish', _this5) : main_core_events.EventEmitter.emit('BX.Landing.SiteTile:publish', _this5);
	            }
	          }],
	          events: {
	            onPopupClose: function onPopupClose() {
	              _this5.getContainerSiteStatus().classList.remove('--hover');
	            },
	            onPopupShow: function onPopupShow() {
	              _this5.getContainerSiteStatus().classList.add('--hover');
	            }
	          },
	          animation: 'fading-slide'
	        });
	      }

	      return this.popupStatus;
	    }
	  }, {
	    key: "getContainerSiteStatus",
	    value: function getContainerSiteStatus() {
	      var _this6 = this;

	      if (!this.$containerSiteStatus) {
	        this.$containerSiteStatus = main_core.Tag.render(_templateObject2$3(), this.access.publication ? 'landing-sites__status' : 'landing-sites__status_disabled', this.getContainerSiteStatusRound(), this.getContainerSiteStatusTitle(), this.access.publication ? main_core.Tag.render(_templateObject3$3()) : '');

	        if (this.access.publication) {
	          main_core.Event.bind(this.$containerSiteStatus, 'click', function (ev) {
	            _this6.getPopupStatus().layout.menuContainer.style.left = _this6.$containerSiteStatus.getBoundingClientRect().left + 'px';

	            _this6.getPopupStatus().show();

	            ev.stopPropagation();
	          });
	        }
	      }

	      return this.$containerSiteStatus;
	    }
	  }, {
	    key: "getContainerSiteMore",
	    value: function getContainerSiteMore() {
	      var _this7 = this;

	      if (!this.$containerSiteMore) {
	        this.$containerSiteMore = main_core.Tag.render(_templateObject4$3());
	        main_core.Event.bind(this.$containerSiteMore, 'click', function (ev) {
	          _this7.getPopupConfig().show();

	          ev.stopPropagation();
	        });
	      }

	      return this.$containerSiteMore;
	    }
	  }, {
	    key: "getContainerSiteStatusRound",
	    value: function getContainerSiteStatusRound() {
	      if (!this.$containerSiteStatusRound) {
	        var status = this.published ? '--success' : '--alert';
	        this.$containerSiteStatusRound = main_core.Tag.render(_templateObject5$2(), status);
	      }

	      return this.$containerSiteStatusRound;
	    }
	  }, {
	    key: "getContainerSiteStatusTitle",
	    value: function getContainerSiteStatusTitle() {
	      if (!this.$containerSiteStatusTitle) {
	        var title = this.published ? main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED') : main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');
	        this.$containerSiteStatusTitle = main_core.Tag.render(_templateObject6$1(), title);
	      }

	      return this.$containerSiteStatusTitle;
	    }
	  }, {
	    key: "publush",
	    value: function publush() {
	      this.published = true;
	      this.getContainerSiteStatusRound().className = 'landing-sites__status-round --success';
	      this.getContainerSiteStatusTitle().innerText = main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED');
	      this.getContainerPreviewStatus().classList.add('--hide');
	    }
	  }, {
	    key: "unPublish",
	    value: function unPublish() {
	      this.published = false;
	      this.getContainerSiteStatusRound().className = 'landing-sites__status-round --alert';
	      this.getContainerSiteStatusTitle().innerText = main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');
	      this.getContainerPreviewStatus().classList.remove('--hide');
	    }
	  }, {
	    key: "getEditableTitle",
	    value: function getEditableTitle() {
	      if (!this.editableTitle) {
	        this.editableTitle = new EditableTitle({
	          phone: this.phone,
	          type: 'title',
	          item: this,
	          url: this.contactsUrl,
	          disabled: !this.access.settings
	        });
	      }

	      return this.editableTitle;
	    }
	  }, {
	    key: "getContainerInfo",
	    value: function getContainerInfo() {
	      if (!this.$containerInfo) {
	        this.$containerInfo = main_core.Tag.render(_templateObject7$1(), main_core.Text.encode(this.title), main_core.Text.encode(this.title), this.phone ? this.getEditableTitle().getContainer() : '', this.getContainerSiteStatus(), this.getContainerSiteMore());
	      }

	      return this.$containerInfo;
	    }
	  }, {
	    key: "updateDomainStatus",
	    value: function updateDomainStatus(status, statusText) {
	      // success
	      // alert
	      // danger
	      // clock
	      !status ? status = '' : null;
	      this.getContainerDomainStatus().className = 'landing-sites__container-status --' + status;
	      !statusText ? statusText = '' : null;
	      this.updateDomainStatusMessage(statusText);
	    }
	  }, {
	    key: "getContainerDomainStatus",
	    value: function getContainerDomainStatus() {
	      if (!this.$containerDomainStatus) {
	        this.$containerDomainStatus = main_core.Tag.render(_templateObject8$1(), this.domainStatus);
	      }

	      return this.$containerDomainStatus;
	    }
	  }, {
	    key: "getEditableUrl",
	    value: function getEditableUrl() {
	      if (!this.editableUrl) {
	        this.editableUrl = new EditableTitle({
	          title: this.url,
	          type: 'url',
	          item: this,
	          url: this.domainUrl,
	          disabled: !this.access.settings
	        });
	      }

	      return this.editableUrl;
	    }
	  }, {
	    key: "getContainerDomainStatusIcon",
	    value: function getContainerDomainStatusIcon() {
	      if (!this.$containerDomainStatusIcon) {
	        this.$containerDomainStatusIcon = main_core.Tag.render(_templateObject9$1(), this.domainStatus);
	      }

	      return this.$containerDomainStatusIcon;
	    }
	  }, {
	    key: "getContainerDomainStatusTitle",
	    value: function getContainerDomainStatusTitle() {
	      if (!this.$containerDomainStatusTitle) {
	        var title = main_core.Loc.getMessage('LANDING_SITE_TILE_OPEN');
	        this.$containerDomainStatusTitle = main_core.Tag.render(_templateObject10$1(), title);
	      }

	      return this.$containerDomainStatusTitle;
	    }
	  }, {
	    key: "updateDomainStatusMessage",
	    value: function updateDomainStatusMessage(text) {
	      !text ? text = '' : null;
	      this.getContainerDomainStatusMessage().innerText = text;
	      this.domainStatusMessage = text;
	    }
	  }, {
	    key: "getContainerDomainStatusMessage",
	    value: function getContainerDomainStatusMessage() {
	      if (!this.$containerDomainStatusMessage) {
	        !this.domainStatusMessage ? this.domainStatusMessage = '' : null;
	        this.$containerDomainStatusMessage = main_core.Tag.render(_templateObject11$1(), this.domainStatusMessage);
	      }

	      return this.$containerDomainStatusMessage;
	    }
	  }, {
	    key: "getContainerDomainLink",
	    value: function getContainerDomainLink() {
	      var _this8 = this;

	      if (!this.$containerDomainLink) {
	        this.$containerDomainLink = main_core.Tag.render(_templateObject12$1(), this.id, this.getContainerDomainStatusIcon(), this.getContainerDomainStatusTitle());
	        main_core.Event.bind(this.$containerDomainLink, 'click', function () {
	          _this8.getPopupHelper().show('link');
	        });
	      }

	      return this.$containerDomainLink;
	    }
	  }, {
	    key: "getContainerDomain",
	    value: function getContainerDomain() {
	      if (!this.$containerDomain) {
	        this.$containerDomain = main_core.Tag.render(_templateObject13(), this.getContainerDomainStatus(), this.getEditableUrl().getContainer(), this.getContainerDomainStatusMessage(), this.getContainerDomainLink());
	      }

	      return this.$containerDomain;
	    }
	  }, {
	    key: "getContainerPreviewImage",
	    value: function getContainerPreviewImage() {
	      if (!this.$containerPreviewImage) {
	        this.$containerPreviewImage = main_core.Tag.render(_templateObject14(), this.published ? '' : '--not-published');

	        if (this.preview) {
	          this.$containerPreviewImage.style.backgroundImage = 'url(' + this.preview + ')';
	          this.$containerPreviewImage.style.backgroundSize = 'cover';
	        }
	      }

	      return this.$containerPreviewImage;
	    }
	  }, {
	    key: "getContainerPreviewStatus",
	    value: function getContainerPreviewStatus() {
	      var _this9 = this;

	      if (!this.$containerPreviewStatus) {
	        this.$containerPreviewStatus = main_core.Tag.render(_templateObject15(), this.published ? '--hide' : '', main_core.Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED'));
	        main_core.Event.bind(this.$containerPreviewStatus, 'mouseenter', function () {
	          _this9.$containerPreviewStatus.style.width = _this9.$containerPreviewStatus.firstElementChild.offsetWidth + 'px';
	        });
	        main_core.Event.bind(this.$containerPreviewStatus, 'mouseleave', function () {
	          _this9.$containerPreviewStatus.style.width = null;
	        });
	      }

	      return this.$containerPreviewStatus;
	    }
	  }, {
	    key: "getContainerPreviewShowPages",
	    value: function getContainerPreviewShowPages() {
	      if (!this.$containerPreviewShowPages) {
	        this.$containerPreviewShowPages = main_core.Tag.render(_templateObject16(), main_core.Loc.getMessage('LANDING_SITE_TILE_SHOW_PAGES'));
	      }

	      return this.$containerPreviewShowPages;
	    }
	  }, {
	    key: "getContainerPreviewInstruction",
	    value: function getContainerPreviewInstruction() {
	      var _this10 = this;

	      if (!this.$containerPreviewInstruction) {
	        this.$containerPreviewInstruction = main_core.Tag.render(_templateObject17(), main_core.Loc.getMessage('LANDING_SITE_TILE_INSTRUCTION'));
	        main_core.Event.bind(this.$containerPreviewInstruction, 'click', function () {
	          _this10.getLeadership().show();
	        });
	      }

	      return this.$containerPreviewInstruction;
	    }
	  }, {
	    key: "getContainerLinks",
	    value: function getContainerLinks() {
	      var _this11 = this;

	      if (!this.$containerLinks) {
	        this.$containerLinks = main_core.Tag.render(_templateObject18());
	        this.menuBottomItems.map(function (menuItem) {
	          _this11.$containerLinks.appendChild(_this11.getContainerLinksItem(menuItem.code, menuItem.href, menuItem.text));
	        });
	      }

	      return this.$containerLinks;
	    }
	  }, {
	    key: "getContainerLinksItem",
	    value: function getContainerLinksItem(type, link, title) {
	      var _this12 = this;

	      var container = main_core.Tag.render(_templateObject19(), link, this.id, type, title);
	      main_core.Event.bind(container, 'click', function (event) {
	        main_core_events.EventEmitter.emit('BX.Landing.SiteTile:onBottomMenuClick', [type, event, _this12]);
	      });
	      return container;
	    }
	  }, {
	    key: "getLeadership",
	    value: function getLeadership() {
	      if (!this.leadership) {
	        this.leadership = new LeaderShip({
	          id: this.id,
	          item: this,
	          articles: this.articles
	        });
	      }

	      return this.leadership;
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      var _this13 = this;

	      this.getContainer().classList.add('--remove');
	      main_core.Event.bind(this.getContainer(), 'transitionend', function () {
	        var items = _this13.grid.getItems();

	        items.splice(items.indexOf(items), 1);
	        main_core.Dom.remove(_this13.getContainer());
	      });
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      this.getContainer().classList.add('--lock');

	      if (!this.loader) {
	        this.loader = new BX.Loader({
	          target: this.getContainer(),
	          size: 100
	        });
	      }

	      this.loader.show();
	    }
	  }, {
	    key: "unLock",
	    value: function unLock() {
	      this.getContainer().classList.remove('--lock');

	      if (this.loader) {
	        this.loader.hide();
	      }
	    }
	  }, {
	    key: "fade",
	    value: function fade() {
	      this.getContainer().classList.add('--fade');
	    }
	  }, {
	    key: "unFade",
	    value: function unFade() {
	      this.getContainer().classList.remove('--fade');
	    }
	  }, {
	    key: "active",
	    value: function active() {
	      this.getContainer().classList.add('--active');
	    }
	  }, {
	    key: "unActive",
	    value: function unActive() {
	      this.getContainer().classList.remove('--active');
	    }
	  }, {
	    key: "getPopupHelper",
	    value: function getPopupHelper() {
	      if (!this.popupHelper) {
	        this.popupHelper = new PopupHelper({
	          id: this.id,
	          url: this.url,
	          fullUrl: this.fullUrl,
	          ordersUrl: this.ordersUrl
	        });
	      }

	      return this.popupHelper;
	    }
	  }, {
	    key: "getContainerWrapper",
	    value: function getContainerWrapper() {
	      if (!this.$containerWrapper) {
	        this.$containerWrapper = main_core.Tag.render(_templateObject20(), this.pagesUrl, this.getContainerPreviewImage(), this.getContainerPreviewStatus(), this.getContainerPreviewShowPages(), this.articles.length > 0 ? this.getContainerPreviewInstruction() : '', this.getContainerInfo(), this.getContainerDomain(), this.getContainerLinks());
	      }

	      return this.$containerWrapper;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject21(), this.deleted ? '--deleted' : '', this.id, this.getLeadership().getContainer(), this.getContainerWrapper(), this.getPopupHelper().getContainer());
	      }

	      return this.$container;
	    }
	  }]);
	  return Item;
	}();

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__grid-item\">\n\t\t\t\t\t<div class=\"landing-sites__item --marketing\">\n\t\t\t\t\t\t<div class=\"landing-sites__item-container --flex\">\n\t\t\t\t\t\t\t<div class=\"landing-sites__item-marketing--title\">", "</div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__item-marketing--text\">", "</div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__item-marketing--icon\"></div>\n\t\t\t\t\t\t\t<div class=\"landing-sites__item-marketing--buttons\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-btn ui-btn-light-border ui-btn-round\" href=\"#\">", "</span>\n\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ItemMarketing = /*#__PURE__*/function () {
	  function ItemMarketing(options) {
	    babelHelpers.classCallCheck(this, ItemMarketing);
	    this.id = options.id;
	    this.grid = options.grid;
	    this.title = options.title;
	    this.text = options.text;
	    this.buttonText = options.buttonText;
	    this.url = options.url;
	    this.onClick = options.onClick;
	    this.$container = null;
	  }

	  babelHelpers.createClass(ItemMarketing, [{
	    key: "getButton",
	    value: function getButton() {
	      var button = main_core.Tag.render(_templateObject$4(), this.buttonText);

	      if (this.onClick) {
	        main_core.Event.bind(button, 'click', this.onClick);
	      }

	      return button;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject2$4(), this.title, this.text, this.getButton());
	      }

	      return this.$container;
	    }
	  }]);
	  return ItemMarketing;
	}();

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-sites__scroller landing-sites__scope\">\n\t\t\t\t\t<div class=\"landing-sites__scroller-button\">\n\t\t\t\t\t\t<div class=\"landing-sites__scroller-icon\"></div>\n\t\t\t\t\t\t<div class=\"landing-sites__scroller-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Scroller = /*#__PURE__*/function () {
	  function Scroller(options) {
	    babelHelpers.classCallCheck(this, Scroller);
	    this.grid = options.grid;
	    this.scrollerText = options.scrollerText;
	    this.$container = null;
	    this.$lastItem = null;
	    this.bindEvents();
	    this.init();
	  }

	  babelHelpers.createClass(Scroller, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core.Event.bind(window, 'scroll', this.adjustPosition.bind(this));
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.getContainer().classList.contains('--show')) {
	        this.getContainer().classList.remove('--hide');
	        this.getContainer().classList.add('--show');
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (!this.getContainer().classList.contains('--hide')) {
	        this.getContainer().classList.remove('--show');
	        this.getContainer().classList.add('--hide');
	      }
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition() {
	      if (!this.$lastItem) {
	        this.$lastItem = this.grid.getItems()[this.grid.getItems().length - 1].getContainer();
	      }

	      this.$lastItem.getBoundingClientRect().top > document.documentElement.clientHeight ? this.show() : this.hide();
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this = this;

	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject$5(), this.scrollerText ? this.scrollerText : main_core.Loc.getMessage('LANDING_SITE_TILE_SCROLLER_SITES'));
	        main_core.Event.bind(this.$container, 'click', function () {
	          var offsetY = window.pageYOffset;
	          var timer = setInterval(function () {
	            if (window.pageYOffset + 30 >= _this.$lastItem.getBoundingClientRect().top + window.pageYOffset - document.body.clientTop || window.pageYOffset + window.innerHeight >= document.body.scrollHeight) {
	              clearInterval(timer);
	            }

	            offsetY = offsetY + 10;
	            window.scrollTo(0, offsetY);
	          }, 10);
	        });
	      }

	      return this.$container;
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      document.body.appendChild(this.getContainer());
	      this.adjustPosition();
	    }
	  }]);
	  return Scroller;
	}();

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-sites__grid landing-sites__scope\"></div>"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var SiteTile = /*#__PURE__*/function () {
	  function SiteTile(options) {
	    babelHelpers.classCallCheck(this, SiteTile);
	    this.renderTo = options.renderTo || null;
	    this.items = options.items || [];
	    this.scrollerText = options.scrollerText || null;
	    this.siteTileItems = [];
	    this.$container = null;
	    this.scroller = null;
	    this.setData(this.items);
	    this.init();
	    setTimeout(this.refreshPreview, 3000);
	  }

	  babelHelpers.createClass(SiteTile, [{
	    key: "getItems",
	    value: function getItems() {
	      return this.siteTileItems;
	    }
	  }, {
	    key: "setData",
	    value: function setData(data) {
	      var _this = this;

	      this.siteTileItems = data.map(function (item) {
	        if (item.type === 'itemMarketing') {
	          return new ItemMarketing({
	            id: item.id || null,
	            title: item.title || null,
	            text: item.text || null,
	            buttonText: item.buttonText || null,
	            onClick: item.onClick || null
	          });
	        }

	        return new Item({
	          id: item.id || null,
	          title: item.title || null,
	          url: item.url || null,
	          fullUrl: item.fullUrl || null,
	          domainProvider: item.domainProvider || null,
	          pagesUrl: item.pagesUrl || null,
	          ordersUrl: item.ordersUrl || null,
	          domainUrl: item.domainUrl || null,
	          contactsUrl: item.contactsUrl || null,
	          ordersCount: parseInt(item.ordersCount) || null,
	          phone: item.phone || null,
	          preview: item.preview || null,
	          published: item.published || null,
	          deleted: item.deleted || null,
	          domainStatus: item.domainStatus || null,
	          domainStatusMessage: item.domainStatusMessage || null,
	          menuItems: item.menuItems || null,
	          menuBottomItems: item.menuBottomItems || null,
	          access: item.access || {},
	          articles: item.articles || null,
	          grid: _this
	        });
	      });
	      return this.siteTileItems;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject$6());

	        for (var i = 0; i < this.siteTileItems.length; i++) {
	          this.$container.appendChild(this.siteTileItems[i].getContainer());
	        }
	      }

	      return this.$container;
	    }
	  }, {
	    key: "draw",
	    value: function draw() {
	      if (this.renderTo) {
	        this.renderTo.appendChild(this.getContainer());
	      }

	      this.afterDraw();
	    }
	  }, {
	    key: "afterDraw",
	    value: function afterDraw() {
	      if (this.getItems().length > 4) {
	        if (!this.scroller) {
	          this.scroller = new Scroller({
	            grid: this,
	            scrollerText: this.scrollerText
	          });
	        }
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.draw();
	    }
	  }, {
	    key: "refreshPreview",
	    value: function refreshPreview() {
	      var previews = document.querySelectorAll('.landing-sites__preview-image');

	      if (previews) {
	        babelHelpers.toConsumableArray(previews).map(function (node) {
	          var url = node.style.backgroundImage.match(/url\(["']?([^"']*)["']?\)/);

	          if (url) {
	            url = url[1];
	            url += url.indexOf('?') > 0 ? '&' : '?';
	            node.style.backgroundImage = 'url(' + url + 'refreshed)';
	          }
	        });
	      }
	    }
	  }]);
	  return SiteTile;
	}();

	exports.SiteTile = SiteTile;

}((this.BX.Landing.Component = this.BX.Landing.Component || {}),BX.Main,BX.UI.Dialogs,BX.Event,BX,BX));
//# sourceMappingURL=script.js.map
