BX.namespace('BX.UI');

if (BX.Type.isUndefined(BX.UI.EntityFieldIcon))
{
	BX.UI.EntityFieldIcon = function(params = {})
	{
		this.editor = params.editor ?? {};
		this.mode = params.mode ?? null;
		this.fieldId = params.fieldId ?? null;
		this.fieldType = params.fieldType ?? null;
		this.isFieldMultiple = params.isFieldMultiple ?? null;
		this.fieldInnerWrapper = params.fieldInnerWrapper ?? null;
		this.isUserField = params.isUserField ?? false;
		this.target = params.target ?? null;
		this.context = undefined;

		this.isCheckedParams = (
			BX.Type.isDomNode(this.fieldInnerWrapper)
			&& BX.Type.isStringFilled(this.fieldId)
		);
		this.onAddressFieldInitiated = this.onAddressFieldInitiated.bind(this);
	};

	BX.UI.EntityFieldIcon.prototype = {
		onAddressFieldInitiated({ data })
		{
			const fieldName = data.fieldConfig.fieldName.replace('[]', '');
			if (fieldName !== this.fieldId)
			{
				return;
			}

			if (this.mode === BX.UI.EntityEditorMode.edit)
			{
				return;
			}

			const context = this.getContextFromAdditionalFieldsData();
			if (!context)
			{
				return;
			}

			this.renderFieldValueIcon(context);
		},

		renderFieldValueIcon()
		{
			if (!this.isUserField && !this.isFieldMultiple && this.target)
			{
				this.appendIcon(this.target, 0);

				return;
			}

			const valueContainers = this.fieldInnerWrapper.querySelectorAll('.fields');
			if (valueContainers.length === 0)
			{
				return;
			}

			const valueContainer = valueContainers[0];
			if (!valueContainer.children)
			{
				return;
			}

			const fieldsUsingDataset = this.getFieldTypesUsingDataset();
			const fieldType = this.fieldType;
			const useDatasetId = fieldsUsingDataset.includes(fieldType) && this.isFieldMultiple;

			const fieldItems = [...valueContainer.children]
				.filter((children) => BX.Dom.hasClass(children, 'field-item'))
			;

			fieldItems.forEach((item, number) => {
				const fieldValueId = (
					useDatasetId
						? item.dataset.id
						: number
				);

				this.appendIcon(item, fieldValueId);
			});
		},

		getFieldTypesUsingDataset()
		{
			return [
				BX.UI.EntityUserFieldType.enumeration,
				BX.UI.EntityUserFieldType.employee,
				BX.UI.EntityUserFieldType.crm,
				BX.UI.EntityUserFieldType.crmStatus,
				BX.UI.EntityUserFieldType.iblockElement,
				BX.UI.EntityUserFieldType.iblockSection,
				BX.UI.EntityUserFieldType.file,
			];
		},

		hasContextIconForFieldValue(fieldValueId = 0)
		{
			const context = this.getContextFromAdditionalFieldsData();
			if (!BX.Type.isObjectLike(context))
			{
				return false;
			}

			if (!this.isCheckedParams)
			{
				return false;
			}

			const fieldId = this.fieldId;
			const contextField = context.fields[fieldId];
			if (!contextField)
			{
				return false;
			}

			return BX.Type.isStringFilled(contextField[fieldValueId]);
		},

		appendIcon(target, fieldValueId)
		{
			const icon = this.getIconNode(fieldValueId);
			if (!BX.Type.isDomNode(icon))
			{
				return;
			}

			BX.Dom.append(icon, target);
		},

		getIconNode(fieldValueId = 0)
		{
			if (!this.hasContextIconForFieldValue(fieldValueId))
			{
				return null;
			}

			const context = this.getContextFromAdditionalFieldsData();
			const fieldId = this.fieldId;
			const contextField = context.fields[fieldId];
			const contextData = context.data.find(
				(dataItem) => Number(dataItem.id) === Number(contextField[fieldValueId])
			);

			return this.renderIcon(contextData);
		},

		getContextFromAdditionalFieldsData()
		{
			if (this.context === undefined)
			{
				const additionalFieldsData = this.editor.getAdditionalFieldsData();
				const context = additionalFieldsData.context;

				this.context = BX.Type.isObjectLike(context) ? context : null;
			}

			return this.context;
		},

		renderIcon(contextData)
		{
			return BX.Tag.render`
				<img class="ui-entity-editor-content-block-field-context-icon" alt="" src="${contextData.iconSvg}" >
			`;
		},
	};
}