import { Dom, Tag, Event, Text } from 'main.core';

import './css/style.css';

const BizProcActivity = window.BizProcActivity;

export class ParallelActivity extends BizProcActivity
{
	allowSort: boolean = false;
	childsContainer;
	container: ?HTMLDivElement;

	constructor()
	{
		super();

		this.Type = 'ParallelActivity';
		this.childActivities = [];
		// eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
		this.__parallelActivityInitType = 'SequenceActivity';
		this.allowSort = false;

		// region compatibility
		this.copyBranch = this.#copyBranch.bind(this);
		this.addBranch = this.#addBranch.bind(this);
		this.createBranch = this.#createBranch.bind(this);
		this.delBranch = (event) => {
			this.#deleteBranch(event.target.parentNode.parentNode);
		};
		this.DrawVLine = this.#drawVLine.bind(this);
		this.RefreshDelButton = this.#refreshDelButton.bind(this);
		this.OnHideClick = this.#onHideClick.bind(this);
		this.BizProcActivityDraw = this.Draw;
		this.Draw = this.#draw.bind(this);
		this.ActivityRemoveChild = this.RemoveChild;
		this.RemoveChild = this.#removeChild.bind(this);
		this.BizProcActivityRemoveResources = this.RemoveResources;
		this.RemoveResources = this.#removeResources.bind(this);
		this.drawMoveElement = this.#drawMoveBranchButtons.bind(this);
		this.moveToRight = (event) => {
			this.#moveBranchToRight(event.target.parentNode.parentNode, event);
		};

		this.moveToLeft = (event) => {
			this.#moveBranchToLeft(event.target.parentNode.parentNode, event);
		};
		this.swapBranch = this.#swapBranch.bind(this);
		// endregion
	}

	#copyBranch(childIndex: number, branchIndex: number)
	{
		this.#createBranch(this.childActivities[childIndex], branchIndex ?? childIndex);
	}

	#addBranch()
	{
		const lastBranchNumber = this.childsContainer.rows[2].cells.length;
		// eslint-disable-next-line no-underscore-dangle
		this.#createBranch(this.__parallelActivityInitType, lastBranchNumber - 1);
	}

	#createBranch(childActivityInfo, branchNumber: number)
	{
		const childActivity = window.CreateActivity(childActivityInfo);
		childActivity.parentActivity = this;
		childActivity.setCanBeActivated(this.getCanBeActivatedChild());
		this.childActivities.splice(branchNumber, 0, childActivity);

		for (let i = 0; i < this.childsContainer.rows.length; i++)
		{
			const cell = this.childsContainer.rows[i].insertCell(branchNumber);
			Dom.attr(cell, { align: 'center', vAlign: 'top' });
		}

		this.#drawVLine(branchNumber);
		childActivity.Draw(this.childsContainer.rows[2].cells[branchNumber]);
		this.#refreshDelButton();
	}

	#deleteBranch(target)
	{
		this.RemoveChild(this.childActivities[target.ind]);
	}

	#drawVLine(branchNumber: number)
	{
		Dom.attr(this.childsContainer.rows[0], 'class', 'trLine');
		Dom.attr(this.childsContainer.rows[3], 'class', 'trLine');
		Dom.style(
			this.childsContainer.rows[1].cells[branchNumber],
			'background',
			'url(/bitrix/images/bizproc/act_line_bg.gif) 50% top repeat-y',
		);
		Dom.style(
			this.childsContainer.rows[2].cells[branchNumber],
			'background',
			'url(/bitrix/images/bizproc/act_line_bg.gif) 50% top repeat-y',
		);
		Dom.attr(this.childsContainer.rows[2].cells[branchNumber], 'vAlign', 'top');

		const childActivityCell = this.childsContainer.rows[1].cells[branchNumber];
		Dom.attr(childActivityCell, { height: '20', vAlign: 'bottom' });

		const { root, remove } = Tag.render`
			<div style="margin-top: 14px; display: none;">
				<div class="bizproc-designer-parallel-activity__del_br"
					ref="remove"
					title="${Text.encode(window.BPMESS.PARA_DEL)}"
					alt="${Text.encode(window.BPMESS.PARA_DEL)}">
					<div class="ui-icon-set --minus-60"></div>
				</div>
			</div>
		`;
		Event.bind(remove, 'click', this.#deleteBranch.bind(this, childActivityCell));
		Dom.append(root, childActivityCell);

		if (this.allowSort)
		{
			this.#drawMoveBranchButtons(branchNumber, childActivityCell);
		}
	}

	#drawMoveBranchButtons(branchNumber, cell)
	{
		const { root, left, right } = Tag.render`
			<div class="move-thread">
				<div
					ref="left" 
					class="ui-icon-set --chevron-left bizproc-designer-parallel-activity-move-arrow"
					title="${Text.encode(window.BPMESS.PARA_MOVE_LEFT)}"
				></div>
				<div
					ref="right"
					class="ui-icon-set --chevron-right bizproc-designer-parallel-activity-move-arrow"
					title="${Text.encode(window.BPMESS.PARA_MOVE_RIGHT)}"
				></div>
			</div>
		`;
		Event.bind(left, 'click', this.#moveBranchToLeft.bind(this, cell));
		Event.bind(right, 'click', this.#moveBranchToRight.bind(this, cell));
		Dom.append(root, cell);
	}

	#moveBranchToLeft(cell, event)
	{
		const index = cell.ind;
		if (index !== 0)
		{
			if (this.#isEventWithCtrlKey(event))
			{
				this.#copyBranch(index, index);
			}
			else
			{
				this.#swapBranch(index - 1, index);
			}

			window.BPTemplateIsModified = true;
		}
	}

	#moveBranchToRight(cell, event)
	{
		const index = cell.ind;
		if (index !== this.childActivities.length)
		{
			if (this.#isEventWithCtrlKey(event))
			{
				this.#copyBranch(index, index + 1);
			}
			else
			{
				this.#swapBranch(index, index + 1);
			}

			window.BPTemplateIsModified = true;
		}
	}

	#isEventWithCtrlKey(event): boolean
	{
		return (event.ctrlKey === true || event.metaKey === true);
	}

	#swapBranch(branchIndex1, branchIndex2)
	{
		const tmp = this.childActivities[branchIndex1];
		this.childActivities[branchIndex1] = this.childActivities[branchIndex2];
		this.childActivities[branchIndex2] = tmp;

		for (let i = 1; i < 3; i++)
		{
			this.childsContainer.rows[i].cells[branchIndex1].ind = branchIndex2;
			this.childsContainer.rows[i].cells[branchIndex2].ind = branchIndex1;
			this.#swapNodes(
				this.childsContainer.rows[i].cells[branchIndex1],
				this.childsContainer.rows[i].cells[branchIndex2],
			);
		}
	}

	#swapNodes(node1, node2)
	{
		const beforeNode = node2.nextElementSibling;
		node1.replaceWith(node2);
		if (beforeNode)
		{
			Dom.insertBefore(node1, beforeNode);
		}
		else
		{
			Dom.append(node1, node2.parentNode);
		}
	}

	#refreshDelButton()
	{
		this.childActivities.forEach((child, index) => {
			Dom.style(
				this.childsContainer.rows[1].cells[index].childNodes[0],
				'display',
				this.childActivities.length > 2 ? 'block' : 'none',
			);
			this.childsContainer.rows[1].cells[index].ind = index;
		});
	}

	#onHideClick()
	{
		// eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
		this.Properties._DesMinimized = this.Properties._DesMinimized === 'Y' ? 'N' : 'Y';
		BX.Dom.toggle(this.childsContainer);
		BX.Dom.toggle(this.hideContainer);
	}

	#draw(wrapper)
	{
		if (this.childActivities.length === 0)
		{
			this.childActivities = [
				// eslint-disable-next-line no-underscore-dangle
				window.CreateActivity(this.__parallelActivityInitType),
				// eslint-disable-next-line no-underscore-dangle
				window.CreateActivity(this.__parallelActivityInitType),
			];
			this.childActivities[0].parentActivity = this;
			this.childActivities[0].setCanBeActivated(this.getCanBeActivatedChild());
			this.childActivities[1].parentActivity = this;
			this.childActivities[1].setCanBeActivated(this.getCanBeActivatedChild());
		}

		this.container = Tag.render`<div class="parallelcontainer">${this.#renderActivityContent()}</div>`;
		Dom.append(this.container, wrapper);

		this.BizProcActivityDraw(this.container);
		this.activityContent = null;

		Dom.style(this.div, { position: 'relative', top: '12px' });
		this.#drawHideContainer();
		this.#drawChildrenContainer();

		// eslint-disable-next-line no-underscore-dangle
		if (this.Properties._DesMinimized === 'Y')
		{
			Dom.hide(this.childsContainer);
		}
		else
		{
			Dom.hide(this.hideContainer);
		}

		this.childActivities.forEach((child, index) => {
			this.#drawVLine(index);
			child.Draw(this.childsContainer.rows[2].cells[index]);
		});

		this.#refreshDelButton();
	}

	#renderActivityContent(): HTMLTableElement | string
	{
		if (!this.activityContent)
		{
			const icon = this.Icon ?? '/bitrix/images/bizproc/act_icon.gif';
			const { root, add } = Tag.render`
				<table 
					cellpadding="0"
					cellspacing="0"
					border="0"
					style="width: 100%; font-size: 11px;"
				>
					<tbody>
						<tr>
							<td 
								align="center"
								valign="center"
								style="
									background: url('${icon}') 2px 2px no-repeat;
									height: 24px;
									width: 24px;
								"
							></td>
							<td align="left" valign="center">
								${Text.encode(this.Properties.Title)}
							</td>
							<td
								ref="add"
								class="bizproc-designer-parallel-activity-add-branch-icon"
								title="${Text.encode(window.BPMESS.PARA_ADD)}"
							></td>
						</tr>
					</tbody>
				</table>
			`;
			Event.bind(add, 'click', this.#addBranch.bind(this));
			this.activityContent = root;

			return this.activityContent;
		}

		return '';
	}

	#drawHideContainer()
	{
		this.hideContainer = Tag.render`
			<div 
				style="
					background: #FFFFFF;
					border: 1px #CCCCCC dotted;
					width: 250px;
					color: #AAAAAA;
					padding: 13px 0 3px 0;
					cursor: pointer;
				"
			>${Text.encode(window.BPMESS.PARA_MIN)}</div>
		`;
		Event.bind(this.hideContainer, 'click', this.#onHideClick.bind(this));
		Dom.append(this.hideContainer, this.container);
	}

	#drawChildrenContainer()
	{
		// eslint-disable-next-line no-underscore-dangle
		this.childsContainer = window._crt(4, this.childActivities.length);
		Dom.attr(this.childsContainer, 'id', Text.encode(this.Name));
		Dom.style(this.childsContainer, 'background', '#FFFFFF');
		Dom.append(this.childsContainer, this.container);
	}

	#removeChild(child)
	{
		const index = this.childActivities.indexOf(child);
		if (index !== -1)
		{
			this.ActivityRemoveChild(child);
			if (this.childsContainer)
			{
				this.childsContainer.rows[0].deleteCell(index);
				this.childsContainer.rows[1].deleteCell(index);
				this.childsContainer.rows[2].deleteCell(index);
				this.childsContainer.rows[3].deleteCell(index);

				this.#refreshDelButton();
			}
		}
	}

	#removeResources()
	{
		this.BizProcActivityRemoveResources();
		if (this.container && this.container.parentNode)
		{
			Dom.remove(this.container);
			this.container = null;
			this.childsContainer = null;
		}
	}
}
