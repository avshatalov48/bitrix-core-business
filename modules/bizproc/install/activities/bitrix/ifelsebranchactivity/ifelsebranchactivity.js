(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	const SequenceActivity = window.SequenceActivity;
	var _draw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draw");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	class IfElseBranchActivity extends SequenceActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _draw, {
	      value: _draw2
	    });
	    this.Type = 'IfElseBranchActivity';

	    // compatibility
	    this.Draw = babelHelpers.classPrivateFieldLooseBase(this, _draw)[_draw].bind(this);
	    this.iHead = 1;
	  }
	  static changeConditionTypeHandler(selectElement) {
	    [...selectElement.options].forEach(option => {
	      const container = document.getElementById(main_core.Dom.attr(option, 'data-id'));
	      main_core.Dom.style(container, 'display', option.selected ? '' : 'none');
	    });
	  }
	}
	function _draw2(wrapper) {
	  const rows = Array.from({
	    length: this.iHead + this.childActivities.length * 2
	  }, () => main_core.Tag.render(_t || (_t = _`
				<tr><td align="center" valign="center"></td></tr>
			`)));
	  const titleNode = babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle]();
	  this.childsContainer = main_core.Tag.render(_t2 || (_t2 = _`
			<table 
				class="seqactivitycontainer"
				id="${0}"
				style="height: 100%; width: 100%;"
				border="0"
				cellpadding="0"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td align="center" valign="center">
							<div class="activity" style="margin: 5px; text-align: center; width: 190px; height: 20px;">
								${0}
							</div>
						</td>
					</tr>
					${0}
				</tbody>
			</table>
		`), main_core.Text.encode(this.Name), titleNode, rows);
	  main_core.Dom.append(this.childsContainer, wrapper);
	  this.CreateLine(0);
	  this.childActivities.forEach((child, index) => {
	    child.Draw(this.childsContainer.rows[this.iHead + index * 2 + 1].cells[0]);
	    this.CreateLine(main_core.Text.toInteger(index) + 1);
	  });
	  this.drawEditorComment(titleNode);
	}
	function _renderTitle2() {
	  const activatedClass = !this.canBeActivated || this.Activated === 'N' ? ' --deactivated' : '';
	  const {
	    root,
	    setting
	  } = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="bizproc-designer-if-else-branch-activity-title-wrapper${0}">
				<table style="width: 100%; height: 100%" cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<td 
								align="center"
								title="${0}"
								style="width: 100%; font-size: 11px;"
							>
								<div class="bizproc-designer-if-else-branch-activity-title">
									${0}
								</div>
							</td>
							<td ref="setting" style="cursor: pointer;">
								<div 
									class="ui-icon-set --settings-2 bizproc-designer-if-else-branch-activity-setting-icon"
								></div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		`), activatedClass, main_core.Text.encode(this.Properties.Title), main_core.Text.encode(this.Properties.Title));
	  main_core.Event.bind(root, 'dblclick', this.OnSettingsClick.bind(this));
	  main_core.Event.bind(setting, 'click', this.Settings.bind(this));
	  return root;
	}

	exports.IfElseBranchActivity = IfElseBranchActivity;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=ifelsebranchactivity.js.map
