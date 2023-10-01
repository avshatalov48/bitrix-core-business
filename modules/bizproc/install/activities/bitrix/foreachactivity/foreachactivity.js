/* eslint-disable */
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	const BizProcActivity = window.BizProcActivity;
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _onHideClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onHideClick");
	class ForEachActivity extends BizProcActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _onHideClick, {
	      value: _onHideClick2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    this.Type = 'ForEachActivity';

	    // compatibility
	    this.BizProcActivityDraw = this.Draw.bind(this);
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.CheckFields = () => true;
	    this.OnHideClick = babelHelpers.classPrivateFieldLooseBase(this, _onHideClick)[_onHideClick].bind(this);
	  }
	}
	function _draw2(wrapper) {
	  if (this.childActivities.length === 0) {
	    this.childActivities = [new window.SequenceActivity()];
	    this.childActivities[0].parentActivity = this;
	  }
	  this.container = main_core.Tag.render(_t || (_t = _`<div class="parallelcontainer"></div`));
	  main_core.Dom.append(this.container, wrapper);
	  this.BizProcActivityDraw(this.container);
	  this.activityContent = null;
	  main_core.Dom.style(this.div, {
	    position: 'relative',
	    top: '12px'
	  });
	  this.hideContainer = main_core.Tag.render(_t2 || (_t2 = _`
			<div 
				style="
					background: #fff;
					border: 1px #CCCCCC dashed;
					width: 250px;
					color: #aaa;
					padding: 13px 0 3px 0;
					cursor: pointer;
				"
			>${0}</div>
		`), main_core.Text.encode(window.BPMESS.PARA_MIN));
	  main_core.Event.bind(this.hideContainer, 'click', this.OnHideClick.bind(this));
	  main_core.Dom.append(this.hideContainer, this.container);
	  this.childsContainer = main_core.Tag.render(_t3 || (_t3 = _`
			<table id="${0}" width="100%" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<tr>
						<td align="center" valign="center" width="15%"></td>
						<td align="center" valign="center" width="70%" style="border: 2px #dfdfdf dashed; padding: 10px"></td>
						<td align="center" valign="center" width="15%"></td>
					</tr>
				</tbody>
			</table>
		`), main_core.Text.encode(this.Name));
	  main_core.Dom.append(this.childsContainer, this.container);

	  // eslint-disable-next-line no-underscore-dangle
	  if (this.Properties._DesMinimized === 'Y') {
	    main_core.Dom.hide(this.childsContainer);
	  } else {
	    main_core.Dom.hide(this.hideContainer);
	  }
	  this.childActivities[0].Draw(this.childsContainer.rows[0].cells[1]);
	}
	function _onHideClick2() {
	  // eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
	  this.Properties._DesMinimized = this.Properties._DesMinimized === 'Y' ? 'N' : 'Y';
	  main_core.Dom.toggle(this.childsContainer);
	  main_core.Dom.toggle(this.hideContainer);
	}

	exports.ForEachActivity = ForEachActivity;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=foreachactivity.js.map
