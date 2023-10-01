/* eslint-disable */
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	const ParallelActivity = window.ParallelActivity;
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	class ApproveActivity extends ParallelActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    this.Type = 'ApproveActivity';
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
	    this.__parallelActivityInitType = 'SequenceActivity';

	    // compatibility
	    this.DrawParallelActivity = this.Draw;
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw];
	  }
	}
	function _draw2(wrapper) {
	  this.activityContent = main_core.Tag.render(_t || (_t = _`
			<table style="font-size: 11px; width: 100%" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td align="left" valign="center" width="33">
							&nbsp;<span style="color: #007700">${0}</span>
						</td>
						<td 
							align="center"
							valign="center"
							style="background: url(${0}) 2px 2px no-repeat; height: 24px; width: 24px"
						></td>
						<td align="left" valign="center">${0}</td>
						<td align="right" valign="center">
							<span style="color: #770000">${0}</span>&nbsp;
						</td>
					</tr>
				</tbody>
			</table>
		`), main_core.Text.encode(window.BPMESS.APPR_YES), this.Icon, main_core.Text.encode(this.Properties.Title), main_core.Text.encode(window.BPMESS.APPR_NO));
	  this.activityHeight = '30px';
	  this.activityWidth = '200px';
	  this.DrawParallelActivity(wrapper);
	}

	exports.ApproveActivity = ApproveActivity;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=approveactivity.js.map
