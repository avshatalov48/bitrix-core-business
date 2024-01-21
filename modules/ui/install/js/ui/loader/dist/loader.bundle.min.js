this.BX=this.BX||{};(function(e,t){"use strict";let i=e=>e,l,s;var a=babelHelpers.classPrivateFieldLooseKey("getContainer");class o{constructor(e){Object.defineProperty(this,a,{value:r});this.target=t.Type.isDomNode(e.target)?e.target:null;this.type=t.Type.isString(e.type)?e.type:null;this.size=t.Type.isString(e.size)?e.size:null;this.color=e.color?e.color:null;this.layout={container:null,bulletContainer:null}}bulletLoader(){const e=this.color?`background: ${this.color};`:"";if(!this.layout.bulletContainer){this.layout.bulletContainer=t.Tag.render(l||(l=i`
				<div class="ui-loader__bullet">
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
				</div>
			`),e,e,e,e,e)}this.layout.container=document.querySelector(".ui-loader__bullet");return this.layout.bulletContainer}show(){this.layout.container.style.display="block"}hide(){this.layout.container.style.display=""}render(){if(!t.Type.isDomNode(this.target)){console.warn("BX.LiveChatRestClient: your auth-token has expired, send query with a new token");return}else{t.Dom.append(babelHelpers.classPrivateFieldLooseBase(this,a)[a](),this.target);if(this.type==="BULLET"){if(this.size){if(this.size.toUpperCase()==="XS"){t.Dom.addClass(this.layout.container,"ui-loader__bullet--xs")}if(this.size.toUpperCase()==="S"){t.Dom.addClass(this.layout.container,"ui-loader__bullet--sm")}if(this.size.toUpperCase()==="M"){t.Dom.addClass(this.layout.container,"ui-loader__bullet--md")}if(this.size.toUpperCase()==="L"){t.Dom.addClass(this.layout.container,"ui-loader__bullet--lg")}if(this.size.toUpperCase()==="XL"){t.Dom.addClass(this.layout.container,"ui-loader__bullet--xl")}}}}}}function r(){if(!this.layout.container){this.layout.container=t.Tag.render(s||(s=i`
				<div class="ui-loader__container ui-loader__scope">
					${0}
				</div>
			`),this.type==="BULLET"?this.bulletLoader():"")}return this.layout.container}e.Loader=o})(this.BX.UI=this.BX.UI||{},BX);
//# sourceMappingURL=loader.bundle.map.js