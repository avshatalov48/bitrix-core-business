import { Tag, Text, Type } from 'main.core';
import type { Property } from '../types/property';

import 'bp_field_type';
import 'ui.forms';

import '../css/form.css';

export function renderBpForm(
	formName: string,
	title: string,
	fields: Array<Property>,
	documentType: [],
	description: ?string,
	signedDocumentId: string,
): HTMLFormElement
{
	let context = {};
	if (Type.isStringFilled(signedDocumentId))
	{
		context = { isStartWorkflow: true, signedDocumentId };
	}

	const controls = BX.Bizproc.FieldType.renderControlCollection(
		documentType,
		fields.map((field) => ({
			property: field,
			fieldName: field.Id,
			value: field.Default,
			controlId: field.Id,
		})),
		'public',
		context,
	);

	return Tag.render`
		<form name="${formName}">
			<div class="bizproc__ws_start__content-form-title-block">
				<div class="bizproc__ws_start__content-form-title">${Text.encode(title)}</div>
				<div class="bizproc__ws_start__content-form-description">${Text.encode(description)}</div>
			</div>
				${fields.map((property) => {
		const control = (
			Type.isElementNode(controls[property.Id])
				? controls[property.Id]
				: BX.Bizproc.FieldType.renderControlPublic(
					documentType,
					property,
					property.Id,
					property.Default,
					false,
				)
		);

		return renderBpFieldForForm(property, control);
	})}
		</form>
	`;
}

export function renderBpFieldForForm(property: Property, control: HTMLElement): HTMLElement
{
	return Tag.render`
		<div class="bizproc__ws_start__content-form-block">
			<div class="ui-ctl-title${Text.toBoolean(property.Required) ? ' --required' : ''}">
				${Text.encode(property.Name)}
			</div>
			${control}
		</div>
	`;
}
