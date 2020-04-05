const aliases = {
	mousewheel: [
		'DOMMouseScroll',
	],
	bxchange: [
		'change',
		'cut',
		'paste',
		'drop',
		'keyup',
	],
	animationend: [
		'animationend',
		'oAnimationEnd',
		'webkitAnimationEnd',
		'MSAnimationEnd',
	],
	transitionend: [
		'webkitTransitionEnd',
		'otransitionend',
		'oTransitionEnd',
		'msTransitionEnd',
		'transitionend',
	],
	fullscreenchange: [
		'fullscreenchange',
		'webkitfullscreenchange',
		'mozfullscreenchange',
		'MSFullscreenChange',
	],
	fullscreenerror: [
		'fullscreenerror',
		'webkitfullscreenerror',
		'mozfullscreenerror',
		'MSFullscreenError',
	],
};

export default aliases;