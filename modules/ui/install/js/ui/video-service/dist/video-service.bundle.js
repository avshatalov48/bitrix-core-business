/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var _url = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("url");
	class BaseService {
	  constructor(url) {
	    Object.defineProperty(this, _url, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _url)[_url] = url;
	  }
	  static matchByUrl(url) {
	    return false;
	  }
	  static getDomains() {
	    return [];
	  }
	  getId() {
	    return null;
	  }
	  getMatcher() {
	    return /^$/;
	  }
	  getMatcherReplacement() {
	    return null;
	  }
	  getEmbeddedUrl() {
	    const replacement = this.getMatcherReplacement();
	    if (main_core.Type.isStringFilled(replacement) || main_core.Type.isFunction(replacement)) {
	      return this.getUrl().replace(this.getMatcher(), replacement);
	    }
	    return '';
	  }
	  getUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _url)[_url];
	  }
	}

	const YOUTUBE_MATCHER = /^((?:https?:)?\/\/)?((?:www|m)\.)?(youtube(-nocookie)?\.com|youtu\.be)(\/(?:[\w-]+\?v=|embed\/|shorts\/|live\/|v\/)?)(?<id>[\w-]+)(\S+)?$/;
	const YOUTUBE_EMBEDDED = 'https://www.youtube-nocookie.com/embed/$<id>';
	class Youtube extends BaseService {
	  static matchByUrl(url) {
	    return YOUTUBE_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['youtube.com', 'youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];
	  }
	  getId() {
	    return 'youtube';
	  }
	  getMatcher() {
	    return YOUTUBE_MATCHER;
	  }
	  getMatcherReplacement() {
	    return YOUTUBE_EMBEDDED;
	  }
	}

	const FACEBOOK_MATCHER = /^(?:(?:https?:)?\/\/)?(?:www.)?facebook\.com.*\/(videos?|watch)(\.php|\/|\?).+$/;
	class Facebook extends BaseService {
	  static matchByUrl(url) {
	    return FACEBOOK_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['facebook.com', 'www.facebook.com'];
	  }
	  getId() {
	    return 'facebook';
	  }
	  getMatcher() {
	    return FACEBOOK_MATCHER;
	  }
	  getEmbeddedUrl() {
	    const encodedUrl = encodeURIComponent(this.getUrl().replace(/\/$/, ''));
	    return `https://www.facebook.com/plugins/video.php?href=${encodedUrl}`;
	  }
	}

	const VIMEO_MATCHER = /^(?:(?:https?:)?\/\/)?(?:www.)?vimeo.com\/(.*\/)?(?<id>\d+)(.*)?/;
	const VIMEO_EMBEDDED = 'https://player.vimeo.com/video/$<id>';
	class Vimeo extends BaseService {
	  static matchByUrl(url) {
	    return VIMEO_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['vimeo.com', 'player.vimeo.com'];
	  }
	  getId() {
	    return 'vimeo';
	  }
	  getMatcher() {
	    return VIMEO_MATCHER;
	  }
	  getMatcherReplacement() {
	    return VIMEO_EMBEDDED;
	  }
	}

	const INSTAGRAM_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?(instagr\.am|instagram\.com)\/p\/(?<id>[\w-]+)\/?/;
	const INSTAGRAM_EMBEDDED = 'https://instagram.com/p/$<id>/embed/captioned';
	class Instagram extends BaseService {
	  static matchByUrl(url) {
	    return INSTAGRAM_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['www.instagram.com', 'instagram.com', 'instagr.am'];
	  }
	  getId() {
	    return 'instagram';
	  }
	  getMatcher() {
	    return INSTAGRAM_MATCHER;
	  }
	  getMatcherReplacement() {
	    return INSTAGRAM_EMBEDDED;
	  }
	}

	const VK_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?vk\.(com|ru)\/.*(video|clip)((?<oid>-?\d+)_(?<id>\d+))\/?/;
	const VK_EMBEDDED = 'https://vk.com/video_ext.php?oid=$<oid>&id=$<id>&hd=2';
	class VK extends BaseService {
	  static matchByUrl(url) {
	    return VK_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['vk.com', 'vk.ru'];
	  }
	  getId() {
	    return 'vk';
	  }
	  getDomains() {
	    return ['vk.com'];
	  }
	  getMatcher() {
	    return VK_MATCHER;
	  }
	  getMatcherReplacement() {
	    return VK_EMBEDDED;
	  }
	}

	const RUTUBE_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?rutube\.ru\/video\/(private\/)?(?<id>[\dA-Za-z]+)\/?/;
	const RUTUBE_EMBEDDED = 'https://rutube.ru/play/embed/$<id>';
	class Rutube extends BaseService {
	  static matchByUrl(url) {
	    return RUTUBE_MATCHER.test(url);
	  }
	  static getDomains() {
	    return ['rutube.ru', 'www.rutube.ru'];
	  }
	  getId() {
	    return 'rutube';
	  }
	  getMatcher() {
	    return RUTUBE_MATCHER;
	  }
	  getMatcherReplacement() {
	    return RUTUBE_EMBEDDED;
	  }
	}

	var _services = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("services");
	class VideoService {
	  static createByUrl(url) {
	    for (const ServiceClass of babelHelpers.classPrivateFieldLooseBase(this, _services)[_services]) {
	      if (ServiceClass.matchByUrl(url)) {
	        return new ServiceClass(url);
	      }
	    }
	    return null;
	  }
	  static createByHost(host) {
	    for (const ServiceClass of babelHelpers.classPrivateFieldLooseBase(this, _services)[_services]) {
	      if (ServiceClass.getDomains().includes(host)) {
	        return new ServiceClass(host);
	      }
	    }
	    return null;
	  }
	  static getEmbeddedUrl(url) {
	    const videoService = this.createByUrl(url);
	    if (videoService) {
	      return videoService.getEmbeddedUrl();
	    }
	    return null;
	  }
	}
	Object.defineProperty(VideoService, _services, {
	  writable: true,
	  value: [Youtube, Facebook, Vimeo, Instagram, VK, Rutube]
	});

	exports.VideoService = VideoService;
	exports.BaseService = BaseService;
	exports.VK = VK;
	exports.Facebook = Facebook;
	exports.Vimeo = Vimeo;
	exports.Rutube = Rutube;
	exports.Instagram = Instagram;
	exports.Youtube = Youtube;

}((this.BX.UI.VideoService = this.BX.UI.VideoService || {}),BX));
//# sourceMappingURL=video-service.bundle.js.map
