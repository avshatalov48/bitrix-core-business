/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	const Line = (width, height, radius) => {
	  const style = Object.entries({
	    width,
	    height,
	    radius
	  }).map(([prop, value]) => value ? `--${prop}: ${value}px;` : '');
	  return main_core.Tag.render(_t || (_t = _`<div class="ui-skeleton" style="${0}"></div>`), style.join(''));
	};

	const Circle = (size = 18) => Line(size, size, 99);

	const map = new Map();
	let css = null;
	async function renderSkeleton(path, root) {
	  var _css, _find;
	  let html = map.get(path);
	  if (!html) {
	    html = (await fetch(path).then(r => r.text())).replaceAll(/(Circle|Line)\((.*)\)/g, (_, fn, args) => parse({
	      Line,
	      Circle
	    }[fn], args)).replaceAll(/{(.+)}/g, (_, key) => main_core.localStorage.get(key));
	    map.set(path, html);
	  }
	  const shadowRoot = root.attachShadow({
	    mode: 'open'
	  });
	  (_css = css) != null ? _css : css = (_find = [...document.styleSheets].find(({
	    href
	  }) => href == null ? void 0 : href.includes('ui/system/skeleton'))) == null ? void 0 : _find.href;
	  shadowRoot.innerHTML = `${html}<link rel="stylesheet" href="${css}">`;
	  return root;
	}
	const parse = (func, args) => func(...args.split(',').filter(it => it).map(value => value.trim() === 'null' ? null : value)).outerHTML;

	exports.Line = Line;
	exports.Circle = Circle;
	exports.renderSkeleton = renderSkeleton;

}((this.BX.UI.System = this.BX.UI.System || {}),BX));
//# sourceMappingURL=skeleton.bundle.js.map
