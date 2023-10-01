import { Dom, Text, Tag, Event } from 'main.core';
import { Button } from 'ui.buttons';

import './css/style.css';

export class StateMachineWorkflowActivity extends window.BizProcActivity
{
	static #SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD = 5;
	static #CHILD_ACTIVITY_BORDER = 1;
	static #ARROW_SIZE = 18;
	static #ARROW_HALF_SIZE = Math.floor(StateMachineWorkflowActivity.#ARROW_SIZE / 2);
	static #ARROW_QUARTER_SIZE = Math.floor(StateMachineWorkflowActivity.#ARROW_SIZE / 4);
	static #ARROW_ONE_EIGHTH_SIZE = Math.floor(StateMachineWorkflowActivity.#ARROW_SIZE / 8);
	Table = null;
	#tableLeftPosition = null;
	#tableRightPosition = null;
	// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private
	__l: [] = [];
	StatusArrows: [] = [];
	statediv;

	#linesCenter: number = 0;
	#linesLeft: number = 0;
	#linesRight: number = 0;

	constructor()
	{
		super();
		this.classname = 'StateMachineWorkflowActivity';

		// region compatibility
		this.SerializeStateMachineWorkflowActivity = this.Serialize;
		this.Serialize = this.#serialize.bind(this);
		this.LineMouseOver = (event: PointerEvent) => {
			this.#paintWholeLine(event.target.id, '#e00', 'over');
		};

		this.LineMouseOut = (event: PointerEvent) => {
			this.#paintWholeLine(event.target.id, 'rgb(192, 193, 195)', 'out');
		};
		this.DrawLines = this.#drawAllLines.bind(this);
		this.FindSetState = this.#findTargetStateNames.bind(this);
		this.Draw = this.#draw.bind(this);
		this.AddStatus = this.#addNewState.bind(this);
		this.ReCheckPosition = this.#reCheckLineStatuses.bind(this);
		this.RemoveChildStateMachine = this.RemoveChild;
		this.RemoveChild = this.#removeChild.bind(this);
		this.ReplaceChild = this.#replaceChild.bind(this);
		this.RemoveResourcesActivity = this.RemoveResources;
		this.RemoveResources = this.#removeResources.bind(this);
		// endregion
	}

	#serialize(): {}
	{
		if (this.childActivities.length > 0)
		{
			this.Properties.InitialStateName = this.childActivities[0].Name;
		}

		return this.SerializeStateMachineWorkflowActivity();
	}

	#removeChild(activity)
	{
		this.RemoveChildStateMachine(activity);
		Dom.remove(this.Table);

		this.Table = null;
		this.#tableLeftPosition = null;
		this.#tableRightPosition = null;

		this.#draw(this.statediv);
	}

	#replaceChild(activity1, activity2)
	{
		const index1 = this.childActivities.indexOf(activity1);
		const index2 = this.childActivities.indexOf(activity2);

		if (index1 < 0 || index2 < 0)
		{
			return;
		}

		this.childActivities[index1] = activity2;
		this.childActivities[index2] = activity1;

		window.BPTemplateIsModified = true;
		this.#removeResources();
		this.#draw(this.statediv);
	}

	#removeResources()
	{
		this.RemoveResourcesActivity();
		if (this.Table)
		{
			Dom.remove(this.Table);
			this.Table = null;
		}
		this.#tableLeftPosition = null;
		this.#tableRightPosition = null;
		this.#removeAllLines();
	}

	#paintWholeLine(id: string, color: string, type: string)
	{
		const lineId = id.slice(0, Math.max(0, id.length - 2));
		for (let i = 1; i <= 3; i++)
		{
			const line = document.getElementById(`${lineId}.${i}`);
			const zIndex = Text.toInteger(Dom.style(line, 'zIndex')) + (type === 'over' ? 1000 : -1000);

			Dom.style(line, { zIndex, backgroundColor: color });
		}
	}

	#drawAllLines()
	{
		this.#removeAllLines();
		this.#findTargetStateNames(false, this);

		for (const [index, pair] of this.StatusArrows.entries())
		{
			const fromPosition = window.ActGetRealPos(document.getElementById(pair[0]));
			const toPosition = window.ActGetRealPos(document.getElementById(pair[1]));

			if (fromPosition === false || toPosition === false || fromPosition.left <= 0 || toPosition.left <= 0)
			{
				continue;
			}

			const { d0, d1, d2, d3, d4 } = this.#drawLine(index, pair);

			fromPosition.right += this.constructor.#SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD;
			fromPosition.left -= this.constructor.#SPACE_BETWEEN_STATE_TITLE_AND_STATE_CHILD;

			this.#disposeLine([d0, d1, d2, d3, d4], fromPosition, toPosition, pair[0]);

			// eslint-disable-next-line no-underscore-dangle
			this.__l.push([d0, d1, d2, d3, d4]);
		}
	}

	#removeAllLines()
	{
		// eslint-disable-next-line no-underscore-dangle
		this.__l.forEach((activityLines: []) => activityLines.forEach((line) => Dom.remove(line)));
		// eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
		this.__l = [];
		this.StatusArrows = [];

		this.#linesCenter = 0;
		this.#linesLeft = 0;
		this.#linesRight = 0;
	}

	#findTargetStateNames(activityName, targetActivity)
	{
		if (targetActivity.Type === 'SetStateActivity')
		{
			if (Object.hasOwn(targetActivity.Properties, 'TargetStateName'))
			{
				this.StatusArrows.push([activityName, targetActivity.Properties.TargetStateName]);
			}

			return;
		}

		targetActivity.childActivities.forEach((activity) => {
			this.#findTargetStateNames(
				targetActivity.Type === 'StateActivity' ? activity.Name : activityName,
				activity,
			);
		});
	}

	#drawLine(index: number, pair: []): {
		d0: HTMLDivElement,
		d4: HTMLDivElement,
		d1: HTMLDivElement,
		d2: HTMLDivElement,
		d3: HTMLDivElement
	}
	{
		const { root, d0, d4, d1, d2, d3 } = Tag.render`
			<div>
				<div 
					ref="d0"
					class="bizproc-designer-state-machine-workflow-activity-arrow-wrapper"
					style="z-index: ${14 + index * 10};"
				></div>
				<div
					ref="d4"
					class="bizproc-designer-state-machine-workflow-activity-arrow-wrapper"
					style="z-index: ${14 + index * 10};"
				></div>
				<div 
					ref="d1"
					id="${`${pair[0]}-${pair[1]}.1`}"
					class="bizproc-designer-state-machine-workflow-activity-horizontal-line"
					style="z-index: ${15 + index * 10};"
				></div>
				<div 
					ref="d2"
					id="${`${pair[0]}-${pair[1]}.2`}"
					class="bizproc-designer-state-machine-workflow-activity-vertical-line"
					style="z-index: ${15 + index * 10};"
				></div>
				<div 
					ref="d3"
					id="${`${pair[0]}-${pair[1]}.3`}"
					class="bizproc-designer-state-machine-workflow-activity-horizontal-line"
					style="z-index: ${15 + index * 10};"
				></div>
			</div>
		`;
		Event.bind(d1, 'mouseover', this.LineMouseOver);
		Event.bind(d1, 'mouseout', this.LineMouseOut);
		Event.bind(d2, 'mouseover', this.LineMouseOver);
		Event.bind(d2, 'mouseout', this.LineMouseOut);
		Event.bind(d3, 'mouseover', this.LineMouseOver);
		Event.bind(d3, 'mouseout', this.LineMouseOut);

		Dom.append(root, this.Table.parentNode);

		return { d0, d4, d1, d2, d3 };
	}

	#disposeLine(lines: [], fromPosition, toPosition, pair0)
	{
		if (Text.toInteger(fromPosition.right) < Text.toInteger(toPosition.left))
		{
			this.#disposeLinesFromRightToLeft(lines, fromPosition, toPosition);
		}
		else if (Text.toInteger(fromPosition.left) === Text.toInteger(toPosition.left))
		{
			const columnNode = document.getElementById(pair0).closest('[data-column]');
			if (columnNode && Dom.attr(columnNode, 'data-column') === 2)
			{
				this.#disposeLinesFromLeftToLeftColumn2(lines, fromPosition, toPosition);
			}
			else
			{
				this.#disposeLinesFromLeftToLeftColumn1(lines, fromPosition, toPosition);
			}
		}
		else
		{
			this.#disposeLinesFromLeftToRight(lines, fromPosition, toPosition);
		}
	}

	#disposeLinesFromRightToLeft(lines: [], fromPosition, toPosition)
	{
		++this.#linesCenter;
		const countLinesCenter = -50 + ((this.#linesCenter % 6) * 6);
		const width = toPosition.left - fromPosition.right;
		const direction = -1;

		const [d0, d1, d2, d3, d4] = lines;
		const quarterFromPositionTop = fromPosition.top + (Math.floor(fromPosition.height / 4));
		const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
		const lineWidth = width / 2 + countLinesCenter;
		const lineHeight = 2;
		const linePositionRight = fromPosition.right + lineWidth;

		Dom.style(d1, {
			top: `${quarterFromPositionTop}px`,
			left: `${fromPosition.right + this.constructor.#CHILD_ACTIVITY_BORDER + 1}px`,
			width: `${lineWidth}px`,
		});
		Dom.style(d2, {
			top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop}px`,
			left: `${linePositionRight}px`,
			height: `${
				fromPosition.top > toPosition.top
					? fromPosition.top - toPosition.top + lineHeight * 2 - 9
					: toPosition.top - fromPosition.top + lineHeight * 2 + 1
			}px`,
		});
		Dom.style(d3, {
			top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
			left: `${linePositionRight}px`,
			width: `${width / 2 + (direction * countLinesCenter) + this.constructor.#CHILD_ACTIVITY_BORDER * 2 + 2}px`,
		});

		this.#disposeArrowRightOut(
			d0,
			fromPosition.right - (this.constructor.#ARROW_HALF_SIZE) + this.constructor.#CHILD_ACTIVITY_BORDER + 1,
			quarterFromPositionTop - this.constructor.#ARROW_QUARTER_SIZE,
		);
		this.#disposeArrowRightIn(
			d4,
			toPosition.left - (this.constructor.#ARROW_HALF_SIZE) + this.constructor.#CHILD_ACTIVITY_BORDER * 2,
			toPosition.top + (Math.floor(toPosition.height / 2)) - this.constructor.#ARROW_QUARTER_SIZE,
		);
	}

	#disposeLinesFromLeftToLeftColumn2(lines: [], fromPosition, toPosition)
	{
		++this.#linesRight;
		const countLinesRight = -50 + ((this.#linesRight % 10) * 10);
		const width = 150;

		const [d0, d1, d2, d3, d4] = lines;
		const quarterFromPositionTop = fromPosition.top + (Math.floor(fromPosition.height / 4));
		const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
		const lineWidth = width / 2 + countLinesRight;
		const lineHeight = 2;

		Dom.style(d1, {
			top: `${quarterFromPositionTop}px`,
			left: `${fromPosition.right + this.constructor.#CHILD_ACTIVITY_BORDER * 2 + 2}px`,
			width: `${lineWidth - 2}px`,
		});
		Dom.style(d2, {
			top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
			left: `${fromPosition.right + lineWidth}px`,
			height: `${
				fromPosition.top > toPosition.top
					? fromPosition.top - toPosition.top - lineHeight * 2
					: toPosition.top - fromPosition.top - lineHeight * 2 + 7
			}px`,
		});
		Dom.style(d3, {
			top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2) - 1}px`,
			left: `${toPosition.right - this.constructor.#CHILD_ACTIVITY_BORDER * 2 - 2}px`,
			width: `${lineWidth + 6}px`,
		});

		this.#disposeArrowRightOut(
			d0,
			fromPosition.right - (this.constructor.#ARROW_HALF_SIZE) + this.constructor.#CHILD_ACTIVITY_BORDER * 2,
			quarterFromPositionTop - this.constructor.#ARROW_QUARTER_SIZE,
		);
		this.#disposeArrowLeftIn(
			d4,
			toPosition.right - this.constructor.#ARROW_QUARTER_SIZE + this.constructor.#CHILD_ACTIVITY_BORDER * 2 + 1,
			toPosition.top + (Math.floor(toPosition.height / 2)) - this.constructor.#ARROW_QUARTER_SIZE,
		);
	}

	#disposeLinesFromLeftToLeftColumn1(lines: [], fromPosition, toPosition)
	{
		++this.#linesLeft;
		const countLinesLeft = -50 + (this.#linesLeft % 10) * 10;
		const width = 150;

		const [d0, d1, d2, d3, d4] = lines;
		const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
		const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
		const lineWidth = width / 2 - countLinesLeft;
		const lineHeight = 2;

		Dom.style(d1, {
			top: `${quarterFromPositionTop}px`,
			left: `${fromPosition.left - lineWidth - this.constructor.#CHILD_ACTIVITY_BORDER - 2}px`,
			width: `${lineWidth}px`,
		});
		Dom.style(d2, {
			top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
			left: `${fromPosition.left - width / 2 + countLinesLeft - 3}px`,
			height: `${
				fromPosition.top > toPosition.top
					? fromPosition.top - toPosition.top - lineHeight * 2
					: toPosition.top - fromPosition.top - lineHeight * 2 + 7
			}px`,
		});
		Dom.style(d3, {
			top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
			left: `${fromPosition.left - width / 2 + countLinesLeft - 1}px`,
			width: `${lineWidth + 3}px`,
		});

		this.#disposeArrowLeftOut(
			d0,
			fromPosition.left - (this.constructor.#ARROW_HALF_SIZE) - this.constructor.#CHILD_ACTIVITY_BORDER * 2,
			quarterFromPositionTop - this.constructor.#ARROW_QUARTER_SIZE,
		);

		this.#disposeArrowRightIn(
			d4,
			toPosition.left - (this.constructor.#ARROW_HALF_SIZE) + this.constructor.#CHILD_ACTIVITY_BORDER * 2,
			toPosition.top + (Math.floor(toPosition.height / 2)) - this.constructor.#ARROW_QUARTER_SIZE,
		);
	}

	#disposeLinesFromLeftToRight(lines: [], fromPosition, toPosition)
	{
		++this.#linesCenter;
		const countLinesCenter = -50 + (this.#linesCenter % 6) * 6;
		const width = fromPosition.left - toPosition.right;
		const direction = -1;

		const [d0, d1, d2, d3, d4] = lines;
		const quarterFromPositionTop = fromPosition.top + Math.floor(fromPosition.height / 4);
		const quarterToPositionTop = toPosition.top + Math.floor(toPosition.height / 4);
		const lineWidth = width / 2 + countLinesCenter;
		const lineHeight = 2;

		Dom.style(d1, {
			top: `${quarterFromPositionTop}px`,
			left: `${fromPosition.left - lineWidth - this.constructor.#CHILD_ACTIVITY_BORDER - 1}px`,
			width: `${lineWidth}px`,
		});
		Dom.style(d2, {
			top: fromPosition.top > toPosition.top ? `${quarterToPositionTop + 7}px` : `${quarterFromPositionTop + 2}px`,
			left: `${toPosition.right + width / 2 - countLinesCenter - this.constructor.#CHILD_ACTIVITY_BORDER * 2}px`,
			height: `${
				fromPosition.top > toPosition.top
					? fromPosition.top - toPosition.top - lineHeight * 2
					: toPosition.top - fromPosition.top - lineHeight * 2 + 7
			}px`,
		});
		Dom.style(d3, {
			top: `${toPosition.top + Math.floor((toPosition.bottom - toPosition.top) / 2)}px`,
			left: `${toPosition.right - this.constructor.#CHILD_ACTIVITY_BORDER * 2 - 2}px`,
			width: `${width / 2 + direction * countLinesCenter + 2}px`,
		});

		this.#disposeArrowLeftOut(
			d0,
			fromPosition.left - (this.constructor.#ARROW_HALF_SIZE) - this.constructor.#CHILD_ACTIVITY_BORDER * 2,
			quarterFromPositionTop - this.constructor.#ARROW_QUARTER_SIZE,
		);
		this.#disposeArrowLeftIn(
			d4,
			toPosition.right - this.constructor.#ARROW_QUARTER_SIZE + this.constructor.#CHILD_ACTIVITY_BORDER * 2 + 1,
			toPosition.top + (Math.floor(toPosition.height / 2)) - this.constructor.#ARROW_QUARTER_SIZE,
		);
	}

	#disposeArrowRightOut(d0, left, top)
	{
		const arrow = Tag.render`
			<div class="ui-icon-set --chevron-right bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`;
		Dom.append(arrow, d0);
		Dom.style(d0, { left: `${left}px`, top: `${top}px` });
	}

	#disposeArrowRightIn(d4, left, top)
	{
		const arrow = Tag.render`
			<div class="ui-icon-set --chevron-right bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`;
		Dom.append(arrow, d4);
		Dom.style(d4, { left: `${left}px`, top: `${top}px`, backgroundColor: 'white', maxWidth: '9px' });
	}

	#disposeArrowLeftOut(d0, left, top)
	{
		const arrow = Tag.render`
			<div class="ui-icon-set --chevron-left bizproc-designer-state-machine-workflow-activity-arrow"></div>
		`;
		Dom.append(arrow, d0);
		Dom.style(d0, { left: `${left}px`, top: `${top}px`, maxWidth: '12px' });
	}

	#disposeArrowLeftIn(d4, left, top)
	{
		const arrow = Tag.render`
			<div 
				class="ui-icon-set --chevron-left bizproc-designer-state-machine-workflow-activity-arrow"
				style="margin-left: -10px"
			></div>
		`;
		Dom.append(arrow, d4);
		Dom.style(d4, { left: `${left}px`, top: `${top}px`, backgroundColor: 'white' });
	}

	#draw(wrapper)
	{
		this.statediv = wrapper;

		const rows = Array.from({ length: this.childActivities.length }, () => {
			return Tag.render`
				<tr>
					<td align="right" valign="center" data-column="1"></td>
					<td align="center" valign="center"></td>
					<td align="left" valign="center" data-column="2"></td>
				</tr>
			`;
		});

		const addNewStateButtonCell = (this.childActivities.length % 2) * 2;
		this.Table = Tag.render`
			<table cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
				<tbody>
					${rows}
					<tr>
						<td align="right" valign="center" width="350px">
							${addNewStateButtonCell === 0 ? this.#renderAddNewStateButton() : '&nbsp'}
						</td>
						<td align="center" valign="center" width="150px"></td>
						<td align="left" valign="center">
							${addNewStateButtonCell === 2 ? this.#renderAddNewStateButton() : '&nbsp'}
						</td>
					</tr>
				</tbody>
			</table>
		`;
		Dom.append(this.Table, wrapper);

		this.childActivities.forEach(
			(activity, index) => activity.Draw(this.Table.rows[index].cells[index % 2 * 2]),
		);

		this.#reCheckLineStatuses(true);
	}

	#renderAddNewStateButton(): Button
	{
		return (new Button({
			text: window.BPMESS.STM_ADD_STATUS_1,
			size: Button.Size.EXTRA_SMALL,
			color: Button.Color.LIGHT_BORDER,
			noCaps: true,
			onclick: (button: Button, event) => {
				this.#addNewState(event, button);
			},
		})).render();
	}

	#addNewState(event: PointerEvent, button)
	{
		event.preventDefault();

		const numberChildActivities = this.childActivities.length;
		const activity = window.CreateActivity('StateActivity');
		this.childActivities.push(activity);
		activity.parentActivity = this;

		activity.Draw(this.Table.rows[numberChildActivities].cells[numberChildActivities % 2 * 2]);

		const row = this.Table.insertRow(-1);
		row.insertCell(-1).align = 'right';
		row.insertCell(-1).align = 'center';
		row.insertCell(-1).align = 'left';

		if (button instanceof Button)
		{
			button.renderTo(this.Table.rows[numberChildActivities + 1].cells[(numberChildActivities + 1) % 2 * 2]);
		}
		else
		{
			Dom.append(
				event.target,
				this.Table.rows[numberChildActivities + 1].cells[(numberChildActivities + 1) % 2 * 2],
			);
		}

		activity.Settings();
	}

	#reCheckLineStatuses(needDrawLines: boolean)
	{
		if (Dom.style(this.Table, 'display') === 'none')
		{
			return;
		}

		const tablePosition = Dom.getPosition(this.Table);
		if (
			needDrawLines
			|| this.#tableLeftPosition !== tablePosition.left
			|| this.#tableRightPosition !== tablePosition.right
		)
		{
			this.#tableLeftPosition = tablePosition.left;
			this.#tableRightPosition = tablePosition.right;
			this.#drawAllLines();
		}

		setTimeout(this.#reCheckLineStatuses.bind(this), 1000);
	}
}
