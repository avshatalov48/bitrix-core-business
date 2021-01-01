import {Dom, Type} from 'main.core';
import {BaseForm} from 'landing.ui.form.baseform';
import {Env} from 'landing.env';
import {Loc} from 'landing.loc';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class DynamicBlockForm extends BaseForm
{
	constructor(options)
	{
		super(options);

		this.type = options.type;
		this.forms = options.forms;
		this.code = options.code;
		this.onSourceChangeHandler = options.onSourceChange;
		this.dynamicParams = options.dynamicParams;
		this.settingFieldsSelectors = ['source'];

		this.addField(this.createSourceField());
	}

	static getSources(): Array<any>
	{
		return Env.getInstance().getOptions().sources;
	}

	static getSourceById(id: string)
	{
		return DynamicBlockForm.getSources().find((source) => {
			return String(source.id) === String(id);
		});
	}

	static getSourceFieldItems(): {name: string, value: string}
	{
		return DynamicBlockForm.getSources().map((source) => {
			return {name: source.name, value: source.id};
		});
	}

	static isReference(value): boolean
	{
		const sources = DynamicBlockForm.getSources();
		if (Type.isArray(sources))
		{
			return sources.some((source) => {
				if (Type.isArray(source.references))
				{
					return source.references.some((reference) => {
						return reference.id === value;
					});
				}

				return false;
			});
		}

		return false;
	}

	createSourceField(): BX.Landing.UI.Field.Dropdown
	{
		let value = '';

		if (
			Type.isPlainObject(this.dynamicParams)
			&& Type.isPlainObject(this.dynamicParams.wrapper)
			&& Type.isPlainObject(this.dynamicParams.wrapper.settings)
			&& Type.isString(this.dynamicParams.wrapper.settings.source)
		)
		{
			value = this.dynamicParams.wrapper.settings.source;
		}

		let source = DynamicBlockForm.getSourceById(value);
		if (!source)
		{
			[source] = DynamicBlockForm.getSources();
		}

		setTimeout(() => {
			this.onSourceChangeHandler(source);
		}, 0);

		return new BX.Landing.UI.Field.Dropdown({
			title: Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_TITLE'),
			selector: 'source',
			content: value,
			items: DynamicBlockForm.getSourceFieldItems(),
			onValueChange: (field) => {
				this.onSourceChangeHandler(DynamicBlockForm.getSourceById(field.getValue()));
			},
		});
	}

	serialize(): {[key: string]: any}
	{
		return this.fields.reduce((acc, field) => {
			const value = field.getValue();

			if (field.selector === 'source')
			{
				acc.source = value;
				acc.settings[field.selector] = value;
			}
			else if (value === '@hide' || (Type.isPlainObject(value) && value.id === '@hide'))
			{
				acc.references[field.selector] = '@hide';

				if (Dom.hasClass(field.layout, 'landing-ui-field-dynamic-dropdown'))
				{
					acc.stubs[field.selector] = '';
				}
				else if (Dom.hasClass(field.layout, 'landing-ui-field-dynamic-image'))
				{
					acc.stubs[field.selector] = {
						id: -1,
						src: 'data:image/gif;base64,R0lGODlhAQABAIAAAP',
						alt: '',
					};
				}
			}
			else if (DynamicBlockForm.isReference(value))
			{
				acc.references[field.selector] = {id: value};
			}
			else if (Type.isPlainObject(value) && Type.isString(value.id))
			{
				acc.references[field.selector] = value;
			}
			else
			{
				acc.stubs[field.selector] = value;
			}

			return acc;
		}, {settings: {}, references: {}, stubs: {}});
	}
}