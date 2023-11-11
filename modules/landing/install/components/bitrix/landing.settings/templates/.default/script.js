this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,main_loader) {
	'use strict';

	var _templateObject, _templateObject2;
	var LandingSettings = /*#__PURE__*/function () {
	  /**
	   * Constructor.
	   */
	  function LandingSettings(options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, LandingSettings);
	    this.siteId = options.siteId;
	    this.landingId = options.landingId;
	    this.type = options.type;

	    // pages
	    this.pages = options.pages;
	    this.container = document.getElementById(options.containerId);
	    this.menu = document.getElementById(options.menuId);
	    for (var page in this.pages) {
	      this.pages[page].container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-settings-page-container\"></div>"])));
	      main_core.Dom.append(this.pages[page].container, this.container);
	    }
	    this.loadingPages = [];
	    this.loaderContainer = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-settings-loader-container\"></div>"])));
	    main_core.Dom.insertAfter(this.loaderContainer, this.container);
	    this.loader = new main_loader.Loader({
	      target: this.loaderContainer
	    });

	    // links
	    this.links = [].slice.call(this.menu.querySelectorAll(LandingSettings.PAGE_LINK_SELECTOR));
	    var currentLink = this.links[0];
	    this.links.forEach(function (link) {
	      _this.bindMenuLink(link);
	      if (link.dataset.page && _this.pages[link.dataset.page] && _this.pages[link.dataset.page].current === true) {
	        currentLink = link;
	      }
	    });
	    if (currentLink) {
	      this.onMenuLinkClick(currentLink);
	    }

	    // save
	    this.saveButton = document.getElementById(options.saveButtonId);
	    this.onSave = this.onSave.bind(this);
	    main_core.Event.bind(this.saveButton, 'click', this.onSave);
	  }
	  babelHelpers.createClass(LandingSettings, [{
	    key: "showLoader",
	    value: function showLoader() {
	      this.loader.show();
	      main_core.Dom.show(this.loaderContainer);
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.loader.hide();
	      main_core.Dom.hide(this.loaderContainer);
	    }
	  }, {
	    key: "bindMenuLink",
	    value: function bindMenuLink(link) {
	      var _this2 = this;
	      main_core.Event.bind(link, 'click', function (event) {
	        event.preventDefault();
	        event.stopPropagation();
	        _this2.onMenuLinkClick(link);
	      });
	    }
	  }, {
	    key: "bindPageLink",
	    value: function bindPageLink(pageLink) {
	      if (pageLink.dataset.page) {
	        var currentMenuLink = this.links.find(function (menuLink) {
	          return menuLink.dataset.page === pageLink.dataset.page;
	        });
	        if (currentMenuLink) {
	          main_core.Event.bind(pageLink, 'click', function (event) {
	            event.preventDefault();
	            event.stopPropagation();
	            currentMenuLink.click();
	          });
	        }
	      }
	    }
	  }, {
	    key: "onMenuLinkClick",
	    value: function onMenuLinkClick(link) {
	      if (link.dataset.page) {
	        this.onPageChange(link.dataset.page);
	      } else if (link.dataset.placement) {
	        // for open app pages in slider
	        if (typeof BX.rest !== 'undefined' && typeof BX.rest.Marketplace !== 'undefined') {
	          BX.rest.Marketplace.bindPageAnchors({});
	        }
	        BX.rest.AppLayout.openApplication(link.dataset.appId, {
	          SITE_ID: this.siteId,
	          LID: this.landingId
	        }, {
	          PLACEMENT: link.dataset.placement,
	          PLACEMENT_ID: link.dataset.placementId
	        });
	      }
	    }
	  }, {
	    key: "onPageChange",
	    value: function onPageChange(pageId) {
	      var _this3 = this;
	      var pageToLoad = this.pages[pageId];
	      if (pageToLoad) {
	        if (pageToLoad.container.childNodes.length === 0) {
	          this.showLoader();
	          this.loadingPages.push(pageId);
	          main_core.ajax.get(pageToLoad.link, function (result) {
	            pageToLoad.container.innerHTML = result;
	            _this3.loadingPages.splice(_this3.loadingPages.indexOf(pageId), 1);
	            if (_this3.loadingPages.length === 0) {
	              _this3.hideLoader();
	            }
	            var form = pageToLoad.container.querySelector('form.landing-form');
	            if (form) {
	              pageToLoad.form = form;
	            }
	            var pageLinks = pageToLoad.container.querySelectorAll(LandingSettings.PAGE_LINK_SELECTOR);
	            if (pageLinks.length > 0) {
	              pageLinks.forEach(function (link) {
	                return _this3.bindPageLink(link);
	              });
	            }
	            if (_this3.currentPage) {
	              _this3.currentPage.container.hidden = true;
	            }
	            _this3.currentPage = pageToLoad;
	            _this3.currentPage.container.hidden = false;
	          });
	        } else {
	          if (this.currentPage) {
	            this.currentPage.container.hidden = true;
	          }
	          this.currentPage = pageToLoad;
	          this.currentPage.container.hidden = false;
	        }
	      }
	    }
	  }, {
	    key: "onSave",
	    value: function onSave() {
	      var _this4 = this;
	      this.showLoader();
	      var submits = [];
	      for (var page in this.pages) {
	        var currPage = this.pages[page];
	        if (currPage.form) {
	          submits.push(fetch(currPage.linkToSave, {
	            method: 'POST',
	            body: new FormData(currPage.form),
	            headers: {
	              'Bx-ajax': true
	            }
	          }));
	        }
	      }
	      Promise.all(submits).then(function (results) {
	        var all = true;
	        results.forEach(function (result) {
	          all = all && result.ok;
	        });
	        if (all) {
	          top.window['landingSettingsSaved'] = true;
	          top.BX.onCustomEvent('BX.Landing.Filter:apply');
	          _this4.hideLoader();
	          if (_this4.type === 'KNOWLEDGE' || _this4.type === 'GROUP') {
	            BX.SidePanel.Instance.close();
	            BX.SidePanel.Instance.reload();
	          } else {
	            top.window.location.reload();
	            BX.SidePanel.Instance.close();
	          }
	        }
	      })["catch"](function (err) {
	        console.error(err);
	      });
	    }
	  }]);
	  return LandingSettings;
	}();
	babelHelpers.defineProperty(LandingSettings, "PAGE_LINK_SELECTOR", 'a[data-page], a[data-placement]');

	exports.LandingSettings = LandingSettings;

}((this.BX.Landing.Component = this.BX.Landing.Component || {}),BX,BX));
//# sourceMappingURL=script.js.map
