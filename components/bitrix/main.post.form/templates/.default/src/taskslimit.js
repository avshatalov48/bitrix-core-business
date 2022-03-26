import {Popup, PopupManager} from 'main.popup';
import {Loc, Dom} from 'main.core';

export default class TasksLimit
{
	static showPopup(
		params: {
			bindPosition: null,
		}
	)
	{
		let tasksLimitPopup = PopupManager.getPopupById(this.getPopupId());
		if (!tasksLimitPopup)
		{
			tasksLimitPopup = new Popup(this.getPopupId(), null, {
				content: this.getTasksLimitPopupContent(),
				lightShadow : false,
				offsetLeft: 20,
				autoHide: false,
				angle: {
					position: 'bottom',
				},
				closeByEsc: false,
				closeIcon: true,
			});
		}

		tasksLimitPopup.setBindElement(params.bindPosition);
		tasksLimitPopup.show();
	}

	static getPopupId()
	{
		return 'bx-post-mention-tasks-limit-popup';
	}

	static getTasksLimitPopupContent()
	{
		return Dom.create('DIV', {
			style: {
				width: '400px',
				padding: '10px',
			},
			children: [
				Dom.create('SPAN', {
					html: Loc.getMessage('MPF_MENTION_TASKS_LIMIT')
						.replace('#A_BEGIN#', '<a href="javascript:void(0);" onclick="BX.Main.PostFormTasksLimit.onClickTasksLimitPopupSlider();">')
						.replace('#A_END#', '</a>'),
				})
			],
		})
	}

	static onClickTasksLimitPopupSlider()
	{
		this.hidePopup();
		BX.UI.InfoHelper.show('limit_tasks_observers_participants', {
			isLimit: true,
			limitAnalyticsLabels: {
				module: 'tasks',
				source: 'postForm',
				subject: 'auditor'
			}
		});
	}

	static hidePopup()
	{
		const tasksLimitPopup = PopupManager.getPopupById(this.getPopupId());
		if (tasksLimitPopup)
		{
			tasksLimitPopup.close();
		}
	}
}
