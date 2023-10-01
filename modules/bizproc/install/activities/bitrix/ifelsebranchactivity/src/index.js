import { Tag, Dom, Text, Event } from 'main.core';
import './css/style.css';

const SequenceActivity = window.SequenceActivity;

export class IfElseBranchActivity extends SequenceActivity
{
	constructor()
	{
		super();
		this.Type = 'IfElseBranchActivity';

		// compatibility
		this.Draw = this.#draw.bind(this);
		this.iHead = 1;
	}

	#draw(wrapper)
	{
		const rows = Array.from(
			{ length: this.iHead + this.childActivities.length * 2 },
			() => Tag.render`
				<tr><td align="center" valign="center"></td></tr>
			`,
		);

		const titleNode = this.#renderTitle();
		this.childsContainer = Tag.render`
			<table 
				class="seqactivitycontainer"
				id="${Text.encode(this.Name)}"
				style="height: 100%; width: 100%;"
				border="0"
				cellpadding="0"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td align="center" valign="center">
							<div class="activity" style="margin: 5px; text-align: center; width: 190px; height: 20px;">
								${titleNode}
							</div>
						</td>
					</tr>
					${rows}
				</tbody>
			</table>
		`;
		Dom.append(this.childsContainer, wrapper);

		this.CreateLine(0);
		this.childActivities.forEach((child, index) => {
			child.Draw(this.childsContainer.rows[this.iHead + index * 2 + 1].cells[0]);
			this.CreateLine(Text.toInteger(index) + 1);
		});

		this.drawEditorComment(titleNode);
	}

	#renderTitle(): HTMLDivElement
	{
		const activatedClass = (
			!this.canBeActivated || this.Activated === 'N'
				? ' --deactivated'
				: ''
		);

		const { root, setting } = Tag.render`
			<div class="bizproc-designer-if-else-branch-activity-title-wrapper${activatedClass}">
				<table style="width: 100%; height: 100%" cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<td 
								align="center"
								title="${Text.encode(this.Properties.Title)}"
								style="width: 100%; font-size: 11px;"
							>
								<div class="bizproc-designer-if-else-branch-activity-title">
									${Text.encode(this.Properties.Title)}
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
		`;
		Event.bind(root, 'dblclick', this.OnSettingsClick.bind(this));
		Event.bind(setting, 'click', this.Settings.bind(this));

		return root;
	}

	static changeConditionTypeHandler(selectElement)
	{
		[...selectElement.options].forEach((option) => {
			const container = document.getElementById(Dom.attr(option, 'data-id'));
			Dom.style(container, 'display', option.selected ? '' : 'none');
		});
	}
}
