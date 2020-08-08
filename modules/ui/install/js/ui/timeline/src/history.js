import {Type, Tag, Loc, Text} from 'main.core';
import {Item} from './item';

export class History extends Item
{
	renderContainer(): Element
	{
		const container = super.renderContainer();
		if(this.isScopeAutomation())
		{
			container.classList.add('ui-item-detail-stream-section-icon-robot');
		}
		else
		{
			container.classList.add('ui-item-detail-stream-section-info');
		}

		return container;
	}

	renderHeader(): ?Element
	{
		return Tag.render`<div class="ui-item-detail-stream-content-header">
			<div class="ui-item-detail-stream-content-title">
				<span class="ui-item-detail-stream-content-title-text">${Text.encode(this.getTitle())}</span>
				<span class="ui-item-detail-stream-content-title-time">${this.formatTime(this.getCreatedTime())}</span>
			</div>
			${this.renderHeaderUser(this.getUserId())}
		</div>`
	}

	renderStageChangeTitle(): Element
	{
		return Tag.render`<div class="ui-item-detail-stream-content-title">
			<span class="ui-item-detail-stream-content-title-text">${Loc.getMessage('UI_TIMELINE_STAGE_CHANGE_SUBTITLE')}</span>
		</div>`;
	}

	renderStageChange(): ?Element
	{
		const stageFrom = this.getStageFrom();
		const stageTo = this.getStageTo();

		if(stageFrom && stageTo && stageFrom.id !== stageTo.id)
		{
			return Tag.render`<div class="ui-item-detail-stream-content-detail-info">
				<span class="ui-item-detail-stream-content-detail-info-status">${Text.encode(stageFrom.name)}</span>
				<span class="ui-item-detail-stream-content-detail-info-separator"></span>
				<span class="ui-item-detail-stream-content-detail-info-status">${Text.encode(stageTo.name)}</span>
			</div>`;
		}

		return null;
	}

	getStageFrom(): ?{id: ?number, name: ?string}
	{
		if(Type.isPlainObject(this.data.stageFrom))
		{
			return this.data.stageFrom;
		}

		return null;
	}

	getStageTo(): ?{id: ?number, name: ?string}
	{
		if(Type.isPlainObject(this.data.stageTo))
		{
			return this.data.stageTo;
		}

		return null;
	}

	getFields(): ?Array
	{
		if(Type.isArray(this.data.fields))
		{
			return this.data.fields;
		}

		return null;
	}

	renderFieldsChange(): ?Element
	{
		const fields = this.getFields();
		if(fields)
		{
			const list = [];
			fields.forEach((field) =>
			{
				list.push(Tag.render`<div class="ui-item-detail-stream-content-detail-field">${Text.encode(field.title)}</div>`);
			});

			return Tag.render`<div class="ui-item-detail-stream-content-detail-info ui-item-detail-stream-content-detail-info-break">
				${list}
			</div>`;
		}

		return null;
	}

	renderFieldsChangeTitle(): Element
	{
		return Tag.render`<div class="ui-item-detail-stream-content-title">
			<span class="ui-item-detail-stream-content-title-text">${Loc.getMessage('UI_TIMELINE_FIELDS_CHANGE_SUBTITLE')}</span>
		</div>`;
	}
}