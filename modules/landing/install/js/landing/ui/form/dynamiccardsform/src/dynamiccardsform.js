import {Dom, Type} from 'main.core';
import {BaseForm} from 'landing.ui.form.baseform';
import {Env} from 'landing.env';
import {SourceField} from 'landing.ui.field.sourcefield';
import {Loc} from 'landing.loc';
import {Main} from 'landing.main';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class DynamicCardsForm extends BaseForm
{
	constructor(options)
	{
		super(options);

		this.type = options.type;
		this.code = options.code;
		this.presets = options.presets;
		this.sync = options.sync;
		this.forms = options.forms;
		this.id = `${this.code.replace('.', '')}-${BX.Landing.Utils.random()}`;
		this.onSourceChangeHandler = options.onSourceChange;
		this.dynamicParams = options.dynamicParams;
		this.settingFieldsSelectors = [
			'source',
			'pagesCount',
			'detailPage',
			'useSef',
		];

		this.sourceField = this.createSourceField();
		this.pagesField = this.createPagesField();

		this.addField(this.sourceField);
		this.addField(this.pagesField);

		this.detailPageGroup = new BX.Landing.UI.Card.DynamicFieldsGroup({
			items: [this.createLinkField()],
		});

		this.addCard(
			this.detailPageGroup,
		);
	}

	static getSources()
	{
		return Env.getInstance().getOptions().sources;
	}

	static getSourceItems()
	{
		return DynamicCardsForm.getSources()
			.map((item) => {
				return {
					name: item.name,
					value: item.id,
					url: item.url ? item.url.filter : '',
					filter: item.filter,
					sort: {
						items: item.sort.map((sortItem) => {
							return {name: sortItem.name, value: sortItem.id};
						}),
					},
					settings: item.settings,
				};
			});
	}

	static isReference(value): boolean
	{
		const sources = DynamicCardsForm.getSources();
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

	createSourceField(): SourceField
	{
		const sourceItems = DynamicCardsForm.getSourceItems();
		const [firstItem] = sourceItems;
		const value = {
			source: firstItem.value,
			filter: firstItem.filter,
		};

		if (
			Type.isPlainObject(this.dynamicParams)
			&& Type.isPlainObject(this.dynamicParams.settings)
			&& Type.isPlainObject(this.dynamicParams.settings.source)
		)
		{
			value.source = this.dynamicParams.settings.source.source;
			value.filter = this.dynamicParams.settings.source.filter;
			value.sort = this.dynamicParams.settings.source.sort;
		}

		return new SourceField({
			selector: 'source',
			title: Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_TITLE'),
			items: sourceItems,
			value,
			onValueChange: (field) => {
				const fieldValue = field.getValue();
				const source = DynamicCardsForm.getSources().find((item) => {
					return item.id === fieldValue.source;
				});

				setTimeout(() => {
					if (!this.sourceField.isDetailPageAllowed())
					{
						Dom.style(this.detailPageGroup.layout, 'display', 'none');
					}
					else
					{
						Dom.style(this.detailPageGroup.layout, 'display', null);
					}
					this.onSourceChangeHandler(source);
				}, 0);
			},
		});
	}

	createPagesField(): BX.Landing.UI.Field.Pages
	{
		return new BX.Landing.UI.Field.Pages({
			selector: 'pagesCount',
			title: Loc.getMessage('LANDING_CARDS__PAGES_FIELD_TITLE'),
			value: this.dynamicParams.settings.pagesCount,
		});
	}

	createLinkField(): BX.Landing.UI.Field.Link
	{
		let content = {
			text: '',
			href: '',
		};

		if (
			Type.isPlainObject(this.dynamicParams)
			&& Type.isPlainObject(this.dynamicParams.settings)
			&& Type.isPlainObject(this.dynamicParams.settings.detailPage)
		)
		{
			content = this.dynamicParams.settings.detailPage;
		}

		return new parent.BX.Landing.UI.Field.Link({
			selector: 'detailPage',
			title: Loc.getMessage('LANDING_CARDS__DETAIL_PAGE_FIELD_TITLE'),
			textOnly: true,
			disableCustomURL: true,
			disableBlocks: true,
			disallowType: true,
			allowedTypes: [
				BX.Landing.UI.Field.LinkUrl.TYPE_HREF_PAGE,
			],
			detailPageMode: true,
			sourceField: this.fields.find((field) => {
				return field.selector === 'source';
			}),
			options: {
				siteId: Env.getInstance().getOptions().site_id,
				landingId: Main.getInstance().id,
				filter: {
					'=TYPE': Env.getInstance().getOptions().params.type,
				},
			},
			content,
		});
	}

	serialize(): {[key: string]: any}
	{
		const isDetailPageAllowed = this.sourceField.isDetailPageAllowed();

		return this.fields.reduce((acc, field) => {
			if (field.selector === 'detailPage' && !isDetailPageAllowed)
			{
				return acc;
			}

			const value = field.getValue();

			if (this.settingFieldsSelectors.includes(field.selector))
			{
				if (field.selector === 'source')
				{
					acc.source = value.source;
				}

				acc.settings[field.selector] = value;
			}
			else if (
				value === '@hide'
				|| (Type.isPlainObject(value) && value.id === '@hide')
			)
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
			else
			if (
				DynamicCardsForm.isReference(value)
					|| (
						Type.isPlainObject(value)
						&& Type.isString(value.id)
					)
			)
			{
				if (DynamicCardsForm.isReference(value))
				{
					acc.references[field.selector] = {id: value};
				}
				else
				{
					acc.references[field.selector] = value;
				}
			}
			else
			{
				acc.stubs[field.selector] = value;
			}

			return acc;
		}, {settings: {}, references: {}, stubs: {}});
	}
}