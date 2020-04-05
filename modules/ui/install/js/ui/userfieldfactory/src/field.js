import {Text, Type} from 'main.core';
import {FieldTypes} from "./fieldtypes";
import {EnumItem} from "./enumitem";

/**
 * @memberof BX.UI.UserFieldFactory
 */
export class Field
{
	saved: boolean = false;

	constructor(data: Object)
	{
		this.data = data;
		const id = Text.toInteger(data.ID);
		if(id > 0)
		{
			this.saved = true;
		}
	}

	setData(data: Object): this
	{
		delete data.SIGNATURE;
		this.data = {...this.data, ...data};

		return this;
	}

	getData(): Object
	{
		return this.data;
	}

	markAsSaved(): this
	{
		this.saved = true;

		return this;
	}

	getName(): string
	{
		return this.data.FIELD;
	}

	setName(name: string): this
	{
		if(!this.isSaved())
		{
			this.data.FIELD = name;
		}

		return this;
	}

	getEntityId(): string
	{
		return this.data.ENTITY_ID;
	}

	getTypeId(): string
	{
		return this.data.USER_TYPE_ID;
	}

	getEnumeration(): Array
	{
		if(!Type.isArray(this.data.ENUM))
		{
			this.data.ENUM = [];
		}

		return this.data.ENUM;
	}

	saveEnumeration(items: EnumItem[])
	{
		this.data.ENUM = [];
		let sort = 100;

		items.forEach((item) =>
		{
			this.data.ENUM.push({
				VALUE: item.getValue(),
				SORT: sort,
			});

			sort += 100;
		});
	}

	static getTitleFields(): Array
	{
		return Array.from([
			'EDIT_FORM_LABEL',
			'LIST_COLUMN_LABEL',
			'LIST_FILTER_LABEL',
		]);
	}

	getTitle(): string
	{
		const titleFields = Field.getTitleFields();

		const titleFieldsCount = titleFields.length;

		for(let index = 0; index < titleFieldsCount; index++)
		{
			if(Type.isString(this.data[titleFields[index]]) && this.data[titleFields[index]].length > 0)
			{
				return this.data[titleFields[index]];
			}
		}

		return this.getName();
	}

	setTitle(title: string)
	{
		if(Type.isString(title) && title.length > 0)
		{
			Field.getTitleFields().forEach((label) =>
			{
				this.data[label] = title;
			});
			if(this.getTypeId() === FieldTypes.boolean)
			{
				this.data.SETTINGS.LABEL_CHECKBOX = title;
			}
		}
	}

	isSaved(): boolean
	{
		return this.saved;
	}

	isMultiple(): boolean
	{
		return (this.data.MULTIPLE === 'Y');
	}

	setIsMultiple(isMultiple)
	{
		if(!this.isSaved())
		{
			this.data.MULTIPLE = (Text.toBoolean(isMultiple) === true ? 'Y' : 'N');
		}
	}

	isDateField(): boolean
	{
		return (this.getTypeId() === FieldTypes.datetime || this.getTypeId() === FieldTypes.date);
	}

	isShowTime(): boolean
	{
		return (this.getTypeId() === FieldTypes.datetime);
	}

	setIsShowTime(isShowTime)
	{
		if(!this.isSaved())
		{
			isShowTime = Text.toBoolean(isShowTime);
			if(isShowTime)
			{
				this.data.USER_TYPE_ID = FieldTypes.datetime;
			}
			else
			{
				this.data.USER_TYPE_ID = FieldTypes.date;
			}
		}
	}

	isSearchable(): boolean
	{
		return (this.data.IS_SEARCHABLE === 'Y');
	}

	setIsSearchable(isSearchable)
	{
		this.data.IS_SEARCHABLE = (Text.toBoolean(isSearchable) === true ? 'Y' : 'N');
	}
}