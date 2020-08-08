(function (exports,main_core) {
	'use strict';

	var LazyLoad = {
	  observer: null,
	  images: {},
	  imageStatus: {
	    hidden: -2,
	    error: -1,
	    "undefined": 0,
	    inited: 1,
	    loaded: 2
	  },
	  imageTypes: {
	    image: 1,
	    background: 2
	  },
	  initObserver: function initObserver() {
	    this.observer = new IntersectionObserver(this.onIntersection.bind(this), {
	      rootMargin: '20% 0% 20% 0%',
	      threshold: 0.10
	    });
	  },
	  onIntersection: function onIntersection(entries) {
	    entries.forEach(function (entry) {
	      if (entry.isIntersecting) {
	        this.showImage(entry.target);
	      }
	    }.bind(this));
	  },
	  registerImage: function registerImage(id, isImageVisibleCallback, options) {
	    if (this.observer === null) {
	      this.initObserver();
	    }

	    options = options || {};

	    if (!main_core.Type.isStringFilled(id)) {
	      return;
	    }

	    if (main_core.Type.isObject(this.images[id])) {
	      return;
	    }

	    var element = document.getElementById(id);

	    if (!main_core.Type.isDomNode(element)) {
	      return;
	    }

	    this.observer.observe(element);
	    this.images[id] = {
	      id: id,
	      node: null,
	      src: null,
	      dataSrcName: options.dataSrcName || 'src',
	      type: null,
	      func: main_core.Type.isFunction(isImageVisibleCallback) ? isImageVisibleCallback : null,
	      status: this.imageStatus.undefined
	    };
	  },
	  registerImages: function registerImages(ids, isImageVisibleCallback, options) {
	    if (main_core.Type.isArray(ids)) {
	      for (var i = 0, length = ids.length; i < length; i++) {
	        this.registerImage(ids[i], isImageVisibleCallback, options);
	      }
	    }
	  },
	  showImage: function showImage(imageNode) {
	    var imageNodeId = imageNode.id;

	    if (!main_core.Type.isStringFilled(imageNodeId)) {
	      return;
	    }

	    var image = this.images[imageNodeId];

	    if (!main_core.Type.isPlainObject(image)) {
	      return;
	    }

	    if (image.status == this.imageStatus.undefined) {
	      this.initImage(image);
	    }

	    if (image.status !== this.imageStatus.inited) {
	      return;
	    }

	    if (!image.node || !image.node.parentNode) {
	      image.node = null;
	      image.status = this.imageStatus.error;
	      return;
	    }

	    if (image.type == this.imageTypes.image) {
	      image.node.src = image.src;
	    } else {
	      image.node.style.backgroundImage = "url('" + image.src + "')";
	    }

	    image.node.dataset[image.dataSrcName] = "";
	    image.status = this.imageStatus.loaded;
	  },
	  showImages: function showImages(checkOwnVisibility) {
	    checkOwnVisibility = checkOwnVisibility !== false;

	    for (var id in this.images) {
	      if (!this.images.hasOwnProperty(id)) {
	        continue;
	      }

	      var image = this.images[id];

	      if (image.status == this.imageStatus.undefined) {
	        this.initImage(image);
	      }

	      if (image.status !== this.imageStatus.inited) {
	        continue;
	      }

	      if (!image.node || !image.node.parentNode) {
	        image.node = null;
	        image.status = this.imageStatus.error;
	        continue;
	      }

	      var isImageVisible = true;

	      if (checkOwnVisibility && main_core.Type.isFunction(image.func)) {
	        isImageVisible = image.func(image);
	      }

	      if (isImageVisible === true && this.isElementVisibleOnScreen(image.node)) {
	        if (image.type == this.imageTypes.image) {
	          image.node.src = image.src;
	        } else {
	          image.node.style.backgroundImage = "url('" + image.src + "')";
	        }

	        image.node.dataset[image.dataSrcName] = "";
	        image.status = this.imageStatus.loaded;
	      }
	    }
	  },
	  initImage: function initImage(image) {
	    image.status = this.imageStatus.error;
	    var node = document.getElementById(image.id);

	    if (!main_core.Type.isDomNode(node)) {
	      return;
	    }

	    var src = node.dataset[image.dataSrcName];

	    if (main_core.Type.isStringFilled(src)) {
	      image.node = node;
	      image.src = src;
	      image.status = this.imageStatus.inited;
	      image.type = image.node.tagName.toLowerCase() == "img" ? this.imageTypes.image : this.imageTypes.background;
	    }
	  },
	  isElementVisibleOnScreen: function isElementVisibleOnScreen(element) {
	    var coords = this.getElementCoords(element);
	    var windowTop = window.pageYOffset || document.documentElement.scrollTop;
	    var windowBottom = windowTop + document.documentElement.clientHeight;
	    coords.bottom = coords.top + element.offsetHeight;
	    return coords.top > windowTop && coords.top < windowBottom || // topVisible
	    coords.bottom < windowBottom && coords.bottom > windowTop // bottomVisible
	    ;
	  },
	  isElementVisibleOn2Screens: function isElementVisibleOn2Screens(element) {
	    var windowHeight = document.documentElement.clientHeight;
	    var windowTop = window.pageYOffset || document.documentElement.scrollTop;
	    var windowBottom = windowTop + windowHeight;
	    var coords = this.getElementCoords(element);
	    coords.bottom = coords.top + element.offsetHeight;
	    windowTop -= windowHeight;
	    windowBottom += windowHeight;
	    return coords.top > windowTop && coords.top < windowBottom || // topVisible
	    coords.bottom < windowBottom && coords.bottom > windowTop // bottomVisible
	    ;
	  },
	  getElementCoords: function getElementCoords(element) {
	    var box = element.getBoundingClientRect();
	    return {
	      originTop: box.top,
	      originLeft: box.left,
	      top: box.top + window.pageYOffset,
	      left: box.left + window.pageXOffset
	    };
	  },
	  onScroll: function onScroll() {},
	  clearImages: function clearImages() {
	    this.images = [];
	  }
	};

	exports.LazyLoad = LazyLoad;

}((this.BX = this.BX || {}),BX));
//# sourceMappingURL=lazyload.bundle.js.map
