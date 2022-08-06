import {Type} from 'main.core';
import {DocumentType, DocumentCategory, DocumentStatus} from './document/types';
import {TemplateScope} from './template-scope';

export default class TemplatesScheme
{
	#scheme: Array<TemplateScope>;

	constructor(scheme: Array<TemplateScope>)
	{
		this.#scheme = [];

		if (Type.isArray(scheme))
		{
			scheme.forEach(rawScope => {
				const scope = new TemplateScope(rawScope);
				this.#scheme.push(scope);
			});
		}
	}

	getDocumentTypes(): Array<DocumentType>
	{
		const documentTypes = new Map();

		for (const scope of this.#scheme)
		{
			documentTypes.set(scope.getDocumentType().Type, scope.getDocumentType());
		}

		return Array.from(documentTypes.values());
	}

	getTypeCategories(documentType: DocumentType): Array<DocumentCategory>
	{
		const documentCategories = new Map();

		for (const scope of this.#scheme)
		{
			if (scope.hasCategory() && scope.getDocumentType().Type === documentType.Type)
			{
				const category = scope.getDocumentCategory();
				documentCategories.set(category.Id, category);
			}
		}

		return Array.from(documentCategories.values());
	}

	getTypeStatuses(documentType: DocumentType, documentCategory: DocumentCategory | null): Array<DocumentStatus>
	{
		const takenStatuses = new Set();
		if (Type.isNil(documentCategory))
		{
			documentCategory = {Id: null};
		}

		const predicate = scope => {
			const shouldBeTaken = (
				scope.getDocumentType().Type === documentType.Type
				&& (scope.hasCategory() ? scope.getDocumentCategory().Id === documentCategory.Id : true)
				&& !takenStatuses.has(scope.getDocumentStatus().Id)
			);

			if (shouldBeTaken)
			{
				takenStatuses.add(scope.getDocumentStatus().Id);
			}

			return shouldBeTaken;
		};

		return Array.from(this.#filterBy(predicate)).map(scope => scope.getDocumentStatus());
	}

	#filterBy(predicate: (TemplateScope) => boolean): Iterable<TemplateScope>
	{
		const generator = function*(scheme)
		{
			for (const scope of scheme)
			{
				if (predicate(scope))
				{
					yield scope;
				}
			}
		};

		return generator(this.#scheme);
	}
}