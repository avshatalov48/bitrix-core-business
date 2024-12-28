import { BpMixedSelector } from 'bizproc.mixed-selector';
import { Reflection, Type, Event, Dom, Tag, Text } from 'main.core';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

type Property = {
	Id: string,
	Name: string,
	FieldName: string,
	Type: string,
	Required: ?boolean,
	Default: any,
	Options: ?Array,
	Settings: Object<string, any>,
}

type AccessType = number;
type ResultType = number;
type FieldId = string;
type FieldsMap = Object<FieldId, Property>;
type RawHTML = string;

class FixResultActivity
{
	resultFieldsContainer: ?HTMLDivElement = undefined;
	accessFieldsContainer: ?HTMLDivElement = undefined;
	accessTypeSelect: ?HTMLSelectElement = undefined;
	resultTypeSelect: ?HTMLSelectElement = undefined;
	resultFieldsMap: Object<ResultType, {
		documentType: [string, string, string],
		fieldsMap: FieldsMap,
	}>;

	accessFieldsMap: Object<AccessType, {
		documentType: [string, string, string],
		fieldsMap: FieldsMap,
	}>;

	currentResultValues: Object<FieldId, any> = {};
	currentAccessValues: Object<FieldId, any> = {};
	renderedResultProperties: Object<FieldId, RawHTML> = {};
	renderedAccessProperties: Object<FieldId, RawHTML> = {};

	selector: BpMixedSelector;
	objectTabs;
	template: Array;
	activityFilter: Array;

	constructor(options: {
		formName: string,
		resultFieldsMap: Object<ResultType, FieldsMap>,
		accessFieldsMap: Object<AccessType, FieldsMap>,
		currentResultValues: Object<FieldId, any>,
		currentAccessValues: Object<FieldId, any>,
		objectTabs: any,
		template: Array,
		activityFilter: Array,
	})
	{
		this.accessFieldsContainer = document.getElementById('access-fields-container');
		this.resultFieldsContainer = document.getElementById('result-fields-container');

		if (Type.isPlainObject(options))
		{
			const form = document.forms[options.formName];
			if (!Type.isNil(form))
			{
				this.accessTypeSelect = form.access_type;
				this.resultTypeSelect = form.result_type;
			}

			this.resultFieldsMap = options.resultFieldsMap;
			this.accessFieldsMap = options.accessFieldsMap;
			this.objectTabs = options.objectTabs;
			this.template = options.template;
			this.activityFilter = options.activityFilter;

			if (Type.isPlainObject(options.currentResultValues))
			{
				this.currentResultValues = options.currentResultValues;
			}

			if (Type.isPlainObject(options.currentAccessValues))
			{
				this.currentAccessValues = options.currentAccessValues;
			}
		}
	}

	get currentResultType(): number
	{
		if (!this.resultTypeSelect)
		{
			return 0;
		}

		return Text.toNumber(this.resultTypeSelect.value);
	}

	get currentAccessType(): number
	{
		if (!this.accessTypeSelect)
		{
			return 0;
		}

		return Text.toNumber(this.accessTypeSelect.value);
	}

	getBindFieldId(): string
	{
		return `${this.currentResultType}_BindToCurrentElement`;
	}

	init(): boolean
	{
		if (this.resultTypeSelect)
		{
			this.renderResultFields();
			Event.bind(this.resultTypeSelect, 'change', this.onResultTypeChange.bind(this));
		}

		if (this.accessTypeSelect)
		{
			this.renderAccessFields();
			Event.bind(this.accessTypeSelect, 'change', this.onAccessTypeChange.bind(this));
		}
	}

	onResultTypeChange(): void
	{
		Dom.clean(this.resultFieldsContainer);
		this.currentResultValues = {};
		this.renderResultFields();
	}

	onAccessTypeChange(): void
	{
		Dom.clean(this.accessFieldsContainer);
		this.currentAccessValues = {};
		this.renderAccessFields();
	}

	renderResultFields(): void
	{
		if (Object.hasOwn(this.resultFieldsMap, this.currentResultType))
		{
			const { documentType, fieldsMap } = this.resultFieldsMap[this.currentResultType];

			this.loadRenderedResultFields();

			for (const fieldId of Object.keys(fieldsMap))
			{
				Dom.append(this.#renderResultProperty(fieldId), this.resultFieldsContainer);
			}
		}
	}

	renderAccessFields(): void
	{
		if (Object.hasOwn(this.accessFieldsMap, this.currentAccessType))
		{
			const { documentType, fieldsMap } = this.accessFieldsMap[this.currentAccessType];

			this.loadRenderedAccessFields();

			for (const fieldId of Object.keys(fieldsMap))
			{
				Dom.append(this.#renderAccessProperty(fieldId), this.accessFieldsContainer);
			}
		}
	}

	loadRenderedResultFields()
	{
		const { documentType, fieldsMap } = this.resultFieldsMap[this.currentResultType];

		if (Type.isFunction(BX.Bizproc.FieldType.renderControlCollection))
		{
			this.renderedResultProperties = BX.Bizproc.FieldType.renderControlCollection(
				documentType,
				Object.entries(fieldsMap).map(([fieldId, field]) => ({
					property: field,
					fieldName: field.FieldName,
					value: this.currentResultValues[fieldId],
					controlId: fieldId,
				})),
				'designer',
			);
		}
	}

	loadRenderedAccessFields()
	{
		const { documentType, fieldsMap } = this.accessFieldsMap[this.currentAccessType];

		if (Type.isFunction(BX.Bizproc.FieldType.renderControlCollection))
		{
			this.renderedAccessProperties = BX.Bizproc.FieldType.renderControlCollection(
				documentType,
				Object.entries(fieldsMap).map(([fieldId, field]) => ({
					property: field,
					fieldName: field.FieldName,
					value: this.currentAccessValues[fieldId],
					controlId: fieldId,
				})),
				'designer',
			);
		}
	}

	#renderResultProperty(fieldId: FieldId): HTMLElement
	{
		const { documentType, fieldsMap } = this.resultFieldsMap[this.currentResultType];
		const property = fieldsMap[fieldId];

		if (property.Type === 'mixed')
		{
			return this.#createSource(property);
		}

		const fallback = () => BX.Bizproc.FieldType.renderControlDesigner(
			documentType,
			property,
			property.FieldName,
			this.currentResultValues[fieldId],
		);

		return Tag.render`
			<tr>
				<td class="adm-detail-content-cell-l" style="text-align: right; vertical-align: middle" align="right" width="25%">${Text.encode(property.Name)}</td>
				<td width="75%" class="adm-detail-content-cell-r">
					${Type.isDomNode(this.renderedResultProperties[fieldId]) ? this.renderedResultProperties[fieldId] : fallback()}
				</td>
			</tr>
		`;
	}

	#renderAccessProperty(fieldId: FieldId): HTMLElement
	{
		const { documentType, fieldsMap } = this.accessFieldsMap[this.currentAccessType];
		const property = fieldsMap[fieldId];

		const fallback = () => BX.Bizproc.FieldType.renderControlDesigner(
			documentType,
			property,
			property.FieldName,
			this.currentAccessValues[fieldId],
		);

		return Tag.render`
			<tr>
				<td class="adm-detail-content-cell-l" style="text-align: right; vertical-align: middle" align="right" width="25%">${Text.encode(property.Name)}</td>
				<td width="75%" class="adm-detail-content-cell-r">
					${Type.isDomNode(this.renderedAccessProperties[fieldId]) ? this.renderedAccessProperties[fieldId] : fallback()}
				</td>
			</tr>
		`;
	}

	#createSource(property): HTMLElement
	{
		const source = Tag.render`<td class="adm-detail-content-cell-r" width="75%"></td>`;
		const object = this.currentResultValues.ResultItem?.object;
		const field = this.currentResultValues.ResultItem?.field;

		this.selector = new BpMixedSelector({
			targetNode: source,
			template: this.template,
			exceptErrorMessages: true,
			objectTabs: this.objectTabs,
			activityFilter: this.activityFilter,
			inputNames: {
				object: 'result_item_object',
				field: 'result_item_field',
			},
		});
		this.selector.renderMixedSelector();

		if (object && field && this.objectTabs[object] && this.objectTabs[object][field])
		{
			this.selector.setSelectedObjectAndField(object, field, this.objectTabs[object][field].Name);
		}
		else
		{
			const sourceName = this.#findActivityTitle(object, field);
			if (sourceName)
			{
				this.selector.setSelectedObjectAndField(object, field, sourceName);
			}
		}

		const tr = Tag.render`
			<tr 
				data-object="${Text.encode(object ?? '')}"
				data-field="${Text.encode(field ?? '')}">
				<td class="adm-detail-content-cell-l" style="text-align: right; vertical-align: middle" align="right" width="25%">${Text.encode(property.Name)}:</td>
				${source}
			</tr>
		`;

		if (this.selector)
		{
			this.selector.subscribe('onSelect', (event) => {
				tr.setAttribute('data-object', event.data.item.object);
				tr.setAttribute('data-field', event.data.item.field);
			});
		}

		return tr;
	}

	#findActivityTitle(object, field): string | null
	{
		const activityTabItems = this.selector.getMenuItemsByTabName('Activity');

		for (const i in activityTabItems)
		{
			const activityInfo = activityTabItems[i];
			if (activityInfo.object === object)
			{
				const activityItems = activityInfo.items;
				for (const j in activityItems)
				{
					const itemInfo = activityItems[j];
					if (itemInfo.field === field)
					{
						return itemInfo.text;
					}
				}
			}
		}

		return null;
	}
}

namespace.FixResultActivity = FixResultActivity;
