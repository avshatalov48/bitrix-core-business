import { BaseField } from './base-field';
import { Tag, Loc } from 'main.core';
import { Label, LabelColor, LabelSize } from 'ui.label';

export type CRMStatusFieldType = {
	enabled: boolean,
}

export class CRMStatusField extends BaseField
{
	render(params: CRMStatusFieldType): void
	{
		const crmStatusContainer = Tag.render`
			<div class="mailbox-grid_active-status-container">
				${this.#getStatusLabel(params.enabled)}
			</div>
		`;

		this.appendToFieldNode(crmStatusContainer);
	}

	#getStatusLabel(active: boolean): HTMLElement
	{
		const labelText = active
			? Loc.getMessage('MAIL_MAILBOX_LIST_FIELD_CRM_STATUS_ENABLED')
			: Loc.getMessage('MAIL_MAILBOX_LIST_FIELD_CRM_STATUS_DISABLED')
		;

		const labelClass = active
			? 'mailbox-grid_crm-status-label-success'
			: 'mailbox-grid_crm-status-label-danger'
		;

		return new Label({
			text: labelText,
			size: LabelSize.LG,
			customClass: labelClass,
		}).render();
	}
}
