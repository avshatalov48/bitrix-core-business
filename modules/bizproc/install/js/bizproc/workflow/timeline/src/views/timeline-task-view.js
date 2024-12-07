import type { TaskUserData } from 'bizproc.task';
import { TaskStatus, UserStatus } from 'bizproc.task';
import type { TimelineUserData } from '../timeline';
import { DurationFormatter } from '../timeline';
import { Dom, Tag, Text, Loc, Type, Uri } from 'main.core';
import type { TimelineTask } from '../timeline-task';

import 'ui.hint';

export type TimelineTaskViewType = {
	task: TimelineTask,
	userId: ?number,
	dateFormat: string,
	dateFormatShort: string,
	taskNumber?: ?number,
	users: Map,
};

const TOO_LONG_PROCESS_DURATION = 60 * 60 * 24 * 3; // Three days

export class TimelineTaskView
{
	#task: TimelineTask;
	#userId: number = 0;
	#taskNumber: ?number = null;
	#dateFormat: string;
	#dateFormatShort: string;
	#users: Map;

	constructor(props: TimelineTaskViewType)
	{
		this.#task = props.task;
		this.#dateFormat = props.dateFormat;
		this.#dateFormatShort = props.dateFormatShort;
		this.#users = props.users;

		if (Type.isNumber(props.taskNumber) && props.taskNumber > 0)
		{
			this.#taskNumber = props.taskNumber;
		}

		if (Type.isNumber(props.userId) && props.userId > 0)
		{
			this.#userId = props.userId;
		}
	}

	renderTo(target: HTMLElement): void
	{
		Dom.append(this.render(), target);
	}

	render(): HTMLElement
	{
		return this.#task.canView() ? this.#renderContent() : this.#renderAccessDenied();
	}

	#renderContent(): HTMLElement
	{
		const isWaiting = this.#task.status.isWaiting();

		return Tag.render`
			<div class="bizproc-workflow-timeline-item ${isWaiting ? '--processing' : ''}">
				<div class="bizproc-workflow-timeline-item-inner">
					<div>
						<span class="bizproc-workflow-timeline-icon ${isWaiting ? '--processing' : '--success'}">
							${isWaiting ? '' : (this.#taskNumber || '')}
						</span>
						<div class="bizproc-workflow-timeline-title">
							<span>${Text.encode(this.#task.name)}</span>
							${this.#renderButton()}
						</div>
					</div>
					<div class="bizproc-workflow-timeline-subject">
						${Text.encode(DurationFormatter.formatDate(
							this.#task.modified, 
							this.#dateFormat, 
							this.#dateFormatShort,
						))}
					</div>
					<div class="bizproc-workflow-timeline-content">
						${this.#renderStatus()}
						${this.#renderUsers()}
						${this.#renderExecutionTime()}
					</div>
				</div>
			</div>
		`;
	}

	#renderButton(): ?HTMLElement
	{
		if (this.#userId === 0 || !this.#task.url)
		{
			return null;
		}

		const participant: TaskUserData = (this.#task.users.find((user) => user.id === this.#userId));

		if (Type.isUndefined(participant))
		{
			return null;
		}

		const isWaiting = (new UserStatus(participant.status)).isWaiting();

		return Tag.render`
			<a
				class="
					bizproc-workflow-timeline-task-link
					bizproc-workflow-timeline-task-link-${isWaiting ? 'blue' : 'gray'}
				"
				href="${Text.encode(this.#task.url || new Uri(`/company/personal/bizproc/${this.#task.id}/`).toString())}"
			>
				${Text.encode(
					isWaiting
						? Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_PROCEED')
						: Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_SEE'),
				)}
			</a>
		`;
	}

	#renderStatus(): HTMLElement
	{
		let message = Text.encode(this.#getStatusName(
			this.#task.status,
			this.#task.approveType,
			this.#task.users.length,
		));

		if (this.#task.status.isWaiting() && this.#task.approveType === 'vote')
		{
			let votedCount = 0;
			for (const user of this.#task.users)
			{
				if (!(new TaskStatus(user.status).isWaiting()))
				{
					votedCount++;
				}
			}

			message = Loc.getMessage(
				'BIZPROC_WORKFLOW_TIMELINE_SLIDER_VOTED',
				{
					'#VOTED#': votedCount,
					'#TOTAL#': this.#task.users.length,
				},
			);
		}

		return Tag.render`<div class="bizproc-workflow-timeline-caption">${message}</div>`;
	}

	#getStatusName(taskStatus: TaskStatus, taskApproveType: string, usersCount: number): string
	{
		if (taskStatus.isYes() || taskStatus.isOk())
		{
			return Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED');
		}

		if (taskStatus.isNo() || taskStatus.isCancel())
		{
			return Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_DECLINED');
		}

		if (taskStatus.isWaiting())
		{
			if (usersCount === 1)
			{
				return Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING');
			}
			let message = '';
			switch (taskApproveType)
			{
				case 'all': message = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ALL');
					break;
				case 'any': message = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ANY');
					break;
				case 'vote': message = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING_ALL');
					break;
				default: message = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMING');
					break;
			}

			return message;
		}

		return taskStatus.name;
	}

	#renderUsers(): HTMLElement
	{
		const showVoteResult = this.#task.users.length > 1;

		return Tag.render`
			<div class="bizproc-workflow-timeline-user-list">
				${Object.values(this.#task.users).map((user) => this.#renderUser(user, showVoteResult))}
			</div>
		`;
	}

	#renderUser(userData: TaskUserData, showVoteResult: boolean): ?HTMLElement
	{
		const user: TimelineUserData = this.#users.get(userData.id);
		if (!user)
		{
			return null;
		}

		const status = new TaskStatus(userData.status);

		let voteClass = '';
		if (showVoteResult)
		{
			if (status.isYes() || status.isOk())
			{
				voteClass = '--voted-up';
			}

			if (status.isNo())
			{
				voteClass = '--voted-down';
			}
		}

		let avatar = '<i></i>';
		if (Type.isString(user.avatarSize100))
		{
			avatar = `<i style="background-image: url('${encodeURI(Text.encode(user.avatarSize100))}')"></i>`;
		}

		return Tag.render`
			<div class="bizproc-workflow-timeline-user ${voteClass}">
				<div class="bizproc-workflow-timeline-userlogo ui-icon ui-icon-common-user">
					${avatar}
				</div>
				<div class="bizproc-workflow-timeline-user-block">
					<a class="bizproc-workflow-timeline-link" href="${user.link}">${Text.encode(user.fullName)}</a>
					<div class="bizproc-workflow-timeline-user-pos" title="${Text.encode(user.workPosition || '')}">
						${Text.encode(user.workPosition || '')}
					</div>
				</div>
			</div>
		`;
	}

	#renderExecutionTime(): ?HTMLElement
	{
		const executionTime = this.#task.executionTime;

		if (Type.isNil(executionTime))
		{
			return null;
		}

		const useHint = executionTime >= TOO_LONG_PROCESS_DURATION;
		const hint = Tag.render`
			<span
				data-hint="${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_TIME_LIMIT_EXCEEDED'))}"
			></span>
		`;

		const notice = Tag.render`
			<div class="bizproc-workflow-timeline-notice">
				<div class="bizproc-workflow-timeline-subject">
					${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EXECUTION_TIME'))}
				</div>
				<span class="bizproc-workflow-timeline-text">
					${Text.encode(DurationFormatter.formatTimeInterval(executionTime, 2))}
				</span>
				${useHint ? hint : null}
			</div>
		`;

		if (useHint)
		{
			BX.UI.Hint.init(notice);
		}

		return notice;
	}

	#renderAccessDenied(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-item --tech">
				<div class="bizproc-workflow-timeline-item-inner">
					<div>
						<span class="bizproc-workflow-timeline-icon"></span>
						<div class="bizproc-workflow-timeline-title">
							${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_RIGHTS_TO_VIEW'))}
						</div>
					</div>
					<div class="bizproc-workflow-timeline-subject">
						${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_RIGHTS_TO_VIEW_TIP'))}
					</div>
				</div>
			</div>
		`;
	}
}
