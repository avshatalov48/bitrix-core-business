this.BX = this.BX || {};
(function (exports, main_core, main_loader) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-content-loader\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing
	 */

	var SliderHacks =
	/*#__PURE__*/
	function () {
	  function SliderHacks() {
	    babelHelpers.classCallCheck(this, SliderHacks);
	  }

	  babelHelpers.createClass(SliderHacks, null, [{
	    key: "getContentArea",
	    value: function getContentArea() {
	      return SliderHacks.cache.remember('contentArea', function () {
	        return document.querySelector('.landing-main');
	      });
	    }
	  }, {
	    key: "getContentLoader",
	    value: function getContentLoader() {
	      return SliderHacks.cache.remember('contentLoader', function () {
	        var wrapper = main_core.Tag.render(_templateObject());
	        var loader = new main_loader.Loader({
	          target: wrapper
	        });
	        loader.show();
	        return wrapper;
	      });
	    }
	  }, {
	    key: "showContentLoader",
	    value: function showContentLoader() {
	      var contentArea = SliderHacks.getContentArea();
	      var contentLoader = SliderHacks.getContentLoader();
	      main_core.Dom.style(contentArea, 'position', 'relative');
	      main_core.Dom.append(contentLoader, contentArea);
	    }
	  }, {
	    key: "hideContentLoader",
	    value: function hideContentLoader() {
	      main_core.Dom.style(SliderHacks.getContentArea(), 'position', null);
	      main_core.Dom.remove(SliderHacks.getContentLoader());
	    }
	  }, {
	    key: "reloadSlider",
	    value: function reloadSlider(url, context) {
	      return new Promise(function (resolve) {
	        var slider = BX.SidePanel.Instance.getSliderByWindow(context || window);

	        if (slider) {
	          SliderHacks.showContentLoader();
	          var srcFrame = slider.getFrame();
	          var frame = main_core.Runtime.clone(srcFrame);
	          frame.src = main_core.Uri.addParam(url, {
	            IFRAME: 'Y'
	          });
	          slider.iframe = frame;
	          main_core.Dom.style(frame, {
	            position: 'absolute',
	            opacity: 0,
	            left: 0,
	            transition: '200ms opacity ease'
	          });
	          main_core.Dom.insertAfter(frame, srcFrame);
	          main_core.Event.bind(frame, 'load', function (event) {
	            if (main_core.Type.isFunction(slider.handleFrameLoad)) {
	              slider.handleFrameLoad(event);
	            } else {
	              console.error('SliderHacks: slider.handleFrameLoad is not a function');
	            }

	            setTimeout(function () {
	              main_core.Dom.style(frame, 'opacity', null);
	              setTimeout(function () {
	                main_core.Dom.remove(srcFrame);
	                resolve();
	              }, 200);
	            }, 200);
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }]);
	  return SliderHacks;
	}();
	babelHelpers.defineProperty(SliderHacks, "cache", new main_core.Cache.MemoryCache());

	exports.SliderHacks = SliderHacks;

}(this.BX.Landing = this.BX.Landing || {}, BX, BX));
//# sourceMappingURL=sliderhacks.bundle.js.map
