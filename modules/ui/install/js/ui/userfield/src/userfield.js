import {Text, Type, Loc, ajax as Ajax} from 'main.core';

declare type UserFieldData = {
	id: ?number,
	fieldName: string,
	detailUrl: ?string,
	enum: ?Array,
	entityId: string,
	xmlId: ?string,
	sort: number,
	mandatory: string,
	showFilter: string,
	isSearchable: string,
	settings: ?{},
};

class UserField
{
	data;
	languageId;
	moduleId;
	progress;
	deleted;

	constructor(data: UserFieldData, params: ?{
		languageId: string,
		moduleId: string,
	})
	{
		this.progress = false;
		this.deleted = false;
		this.setData(data);
		if(Type.isPlainObject(params))
		{
			if(Type.isString(params.languageId))
			{
				this.setLanguageId(params.languageId);
			}
			this.moduleId = params.moduleId;
		}
		this.actions = {
			get: 'main.userFieldConfig.get',
			add: 'main.userFieldConfig.add',
			update: 'main.userFieldConfig.update',
			delete: 'main.userFieldConfig.delete',
		}
	}
	
	load(): Promise<UserField,string[]>
	{
		return new Promise((resolve, reject) => {
			const errors = [];

			if(this.progress)
			{
				errors.push('Another action is in progress');
				reject(errors);
				return;
			}
			
			if(!this.isSaved())
			{
				errors.push('Cant load UserField without id');
				reject(errors);
				return;
			}
			
			const action = this.actions.get;
			if(!Type.isString(action) || action.length <= 0)
			{
				errors.push('UserField load action is not specified');
				reject(errors);
				return;
			}

			this.progress = true;
			Ajax.runAction(action, {
				data: {
					id: this.getId(),
					moduleId: this.moduleId,
				},
			}).then((response) => {
				this.progress = false;
				this.setData(response.data.field);
				resolve(response);
			}).catch((response) => {
				this.progress = false;
				response.errors.forEach(({message}) => {
					errors.push(message);
				});
				reject(errors);
			});
		});
	}

	save(): Promise<UserField,string[]>
	{
		return new Promise((resolve, reject) => {
			const errors = [];

			if(this.progress)
			{
				errors.push('Another action is in progress');
				reject(errors);
				return;
			}

			let action;
			let data;
			if(this.isSaved())
			{
				action = this.actions.update;
				data = {
					id: this.getId(),
					field: this.getData(),
					moduleId: this.moduleId,
				}
			}
			else
			{
				action = this.actions.add;
				data = {
					field: this.getData(),
					moduleId: this.moduleId,
				}
			}

			if(!Type.isString(action) || action.length <= 0)
			{
				errors.push('UserField action is not specified');
				reject(errors);
				return;
			}

			this.progress = true;
			Ajax.runAction(action, {
				data,
			}).then((response) => {
				this.progress = false;
				this.setData(response.data.field);
				resolve(response);
			}).catch((response) => {
				this.progress = false;
				response.errors.forEach(({message}) => {
					if(Type.isPlainObject(message) && message.text)
					{
						errors.push(message.text);
					}
					else
					{
						errors.push(message);
					}
				});
				reject(errors);
			});
		});
	}

	delete(): Promise<UserField,string[]>
	{
		return new Promise((resolve, reject) => {
			const errors = [];

			if(this.progress)
			{
				errors.push('Another action is in progress');
				reject(errors);
				return;
			}

			if(!this.isSaved())
			{
				errors.push('Cant delete UserField without id');
				reject(errors);
				return;
			}

			const action = this.actions.delete;
			if(!Type.isString(action) || action.length <= 0)
			{
				errors.push('UserField action is not specified');
				reject(errors);
				return;
			}

			this.progress = true;
			Ajax.runAction(action, {
				data: {
					id: this.getId(),
					moduleId: this.moduleId,
				},
			}).then(() => {
				this.deleted = true;
				this.progress = false;
				resolve();
			}).catch((response) => {
				this.progress = false;
				response.errors.forEach(({message}) => {
					errors.push(message);
				});
				reject(errors);
			});
		});
	}

	setLanguageId(languageId: string): this
	{
		this.languageId = languageId;

		return this;
	}

	setModuleId(moduleId: string): this
	{
		this.moduleId = moduleId;

		return this;
	}

	getLanguageId(): string
	{
		if(!this.languageId)
		{
			return Loc.getMessage('LANGUAGE_ID');
		}

		return this.languageId;
	}

	getId(): number
	{
		return Text.toInteger(this.data.id);
	}

	isSaved(): boolean
	{
		return (this.getId() > 0);
	}

	setData(data: UserFieldData): this
	{
		this.data = data;

		return this;
	}

	getData(): UserFieldData
	{
		return this.data;
	}

	getName(): ?string
	{
		return this.data.fieldName;
	}

	setName(name: string): this
	{
		if(this.isSaved())
		{
			console.error('Changing name is not available on saved UserField');
			return this;
		}

		this.data.fieldName = name;

		return this;
	}

	getEntityId(): string
	{
		return this.data.entityId;
	}

	getUserTypeId(): string
	{
		return this.data.userTypeId;
	}

	setUserTypeId(userTypeId: string): this
	{
		if(this.isSaved())
		{
			console.error('Changing userTypeId is not available on saved UserField');
			return this;
		}

		this.data.userTypeId = userTypeId;

		return this;
	}

	getEnumeration(): ?Array
	{
		if(!Type.isArray(this.data.enum))
		{
			this.data.enum = [];
		}

		return this.data.enum;
	}

	setEnumeration(items: Array): this
	{
		this.data.enum = items;

		return this;
	}

	static getTitleFieldNames(): Array
	{
		return [
			'editFormLabel',
			'listColumnLabel',
			'listFilterLabel',
		];
	}

	getTitle(): string
	{
		const titleFieldNames = UserField.getTitleFieldNames();
		const titleFieldsCount = titleFieldNames.length;
		const languageId = this.getLanguageId();

		for(let index = 0; index < titleFieldsCount; index++)
		{
			if(
				this.data[titleFieldNames[index]]
				&& Type.isString(this.data[titleFieldNames[index]][languageId])
				&& this.data[titleFieldNames[index]][languageId].length > 0
			)
			{
				return this.data[titleFieldNames[index]][languageId];
			}
		}

		return this.getName();
	}

	setTitle(title: string, languageId: ?string): this
	{
		if(Type.isString(title) && title.length > 0)
		{
			if(!languageId)
			{
				languageId = this.getLanguageId();
			}
			if(!this.data['editFormLabel'])
			{
				this.data['editFormLabel'] = {};
			}
			this.data['editFormLabel'][languageId] = title;
			if(this.getUserTypeId() === 'boolean')
			{
				if(!this.data.settings)
				{
					this.data.settings = {};
				}
				this.data.settings.LABEL_CHECKBOX = title;
			}
		}

		return this;
	}

	isMultiple(): boolean
	{
		return (this.data.multiple === 'Y');
	}

	isMandatory(): boolean
	{
		return (this.data.mandatory === 'Y');
	}

	setIsMandatory(mandatory: boolean): this
	{
		this.data.mandatory = (Text.toBoolean(mandatory) ? 'Y' : 'N');
	}

	setIsMultiple(isMultiple: boolean|string): this
	{
		if(this.isSaved())
		{
			console.error('Changing multiple is not available on saved UserField');
			return this;
		}

		this.data.multiple = (Text.toBoolean(isMultiple) === true ? 'Y' : 'N');

		return this;
	}

	getDetailUrl(): ?string
	{
		return this.data.detailUrl;
	}

	isDeleted(): boolean
	{
		return this.deleted;
	}

	serialize(): string
	{
		return JSON.stringify({
			data: this.data,
			languageId: this.languageId,
			moduleId: this.moduleId,
			progress: this.progress,
			deleted: this.deleted,
		});
	}

	static unserialize(serializedData: string): UserField
	{
		const serializedUserField = JSON.parse(serializedData);
		const userField = new UserField(serializedUserField.data, {
			languageId: serializedUserField.languageId,
			moduleId: serializedUserField.moduleId,
		});

		userField.progress = serializedUserField.progress;
		userField.deleted = serializedUserField.deleted;

		return userField;
	}
}

export {UserField};