(function (exports,ui_vue) {
	'use strict';

	/**
	 * Image Lazy Load Vue directive
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var WATCH = 'bx-lazyload-watch';
	var LOADING = 'bx-lazyload-loading';
	var SUCCESS = 'bx-lazyload-success';
	var ERROR = 'bx-lazyload-error';
	var HIDDEN = 'bx-lazyload-hidden';
	var lazyloadObserver = null;

	var lazyloadLoadImage = function lazyloadLoadImage(currentImage) {
	  var SUCCESS_CLASS = currentImage.dataset.lazyloadSuccessClass ? currentImage.dataset.lazyloadSuccessClass.split(" ") : [SUCCESS];
	  delete currentImage.dataset.lazyloadSuccessClass;
	  var ERROR_CLASS = currentImage.dataset.lazyloadErrorClass ? currentImage.dataset.lazyloadErrorClass.split(" ") : [ERROR];
	  delete currentImage.dataset.lazyloadErrorClass;
	  currentImage.classList.add(LOADING);
	  var newImage = new Image();
	  newImage.src = currentImage.dataset.lazyloadSrc;

	  if (!currentImage.dataset.lazyloadHiddenSrc) {
	    currentImage.dataset.lazyloadHiddenSrc = currentImage.src;
	  }

	  newImage.onload = function () {
	    var _currentImage$classLi;

	    if (currentImage.classList.contains(HIDDEN)) {
	      return false;
	    }

	    if (currentImage.dataset.lazyloadSrc) {
	      currentImage.src = currentImage.dataset.lazyloadSrc;
	    }

	    currentImage.classList.remove(LOADING);

	    (_currentImage$classLi = currentImage.classList).add.apply(_currentImage$classLi, babelHelpers.toConsumableArray(SUCCESS_CLASS));
	  };

	  newImage.onerror = function () {
	    var _currentImage$classLi2;

	    if (currentImage.classList.contains(HIDDEN)) {
	      return false;
	    }

	    if (currentImage.dataset.lazyloadErrorSrc) {
	      currentImage.src = currentImage.dataset.lazyloadErrorSrc;
	    } else {
	      currentImage.dataset.lazyloadSrc = currentImage.src;
	    }

	    currentImage.classList.remove(LOADING);

	    (_currentImage$classLi2 = currentImage.classList).add.apply(_currentImage$classLi2, babelHelpers.toConsumableArray(ERROR_CLASS));
	  };

	  if (typeof currentImage.dataset.lazyloadDontHide !== 'undefined') {
	    currentImage.classList.remove(WATCH);
	    delete currentImage.dataset.lazyloadDontHide;

	    if (lazyloadObserver) {
	      lazyloadObserver.unobserve(currentImage);
	    }
	  }
	};

	if (typeof window.IntersectionObserver !== 'undefined') {
	  lazyloadObserver = new IntersectionObserver(function (entries, observer) {
	    entries.forEach(function (entry) {
	      var currentImage = entry.target;

	      if (entry.isIntersecting) {
	        if (currentImage.classList.contains(HIDDEN)) {
	          if (currentImage.dataset.lazyloadSrc) {
	            currentImage.src = currentImage.dataset.lazyloadSrc;
	          }

	          currentImage.classList.remove(HIDDEN);
	        } else if (currentImage.classList.contains(WATCH)) {
	          return true;
	        } else {
	          currentImage.classList.add(WATCH);
	          lazyloadLoadImage(currentImage);
	        }
	      } else {
	        if (currentImage.classList.contains(HIDDEN) || !currentImage.classList.contains(WATCH)) {
	          return true;
	        }

	        if (currentImage.dataset.lazyloadHiddenSrc) {
	          currentImage.src = currentImage.dataset.lazyloadHiddenSrc;
	        }

	        currentImage.classList.remove(LOADING);
	        currentImage.classList.add(HIDDEN);
	      }
	    });
	  }, {
	    threshold: [0, 1]
	  });
	}

	ui_vue.Vue.directive('bx-lazyload', {
	  bind: function bind(element) {
	    if (!element.src || element.src === location.href.replace(location.hash, '')) {
	      element.src = "data:image/svg+xml,%3Csvg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E";
	    }

	    if (lazyloadObserver) {
	      lazyloadObserver.observe(element);
	    } else {
	      lazyloadLoadImage(element);
	    }
	  },
	  componentUpdated: function componentUpdated(element) {
	    if (!element.classList.contains(HIDDEN) && !element.classList.contains(LOADING) && element.dataset.lazyloadSrc && element.dataset.lazyloadSrc != element.src) {
	      if (!element.dataset.lazyloadSrc.startsWith('http')) {
	        var url = document.createElement('a');
	        url.href = element.dataset.lazyloadSrc;

	        if (url.href == element.src) {
	          return;
	        }
	      }

	      lazyloadLoadImage(element);
	    }
	  },
	  unbind: function unbind(element) {
	    if (lazyloadObserver) {
	      lazyloadObserver.unobserve(element);
	    }
	  }
	});

}((this.window = this.window || {}),BX));
//# sourceMappingURL=lazyload.bundle.js.map
