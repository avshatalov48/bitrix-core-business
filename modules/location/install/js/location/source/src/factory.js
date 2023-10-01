import { BaseSource } from 'location.core';
import { Google } from 'location.google';
import { OSM, OSMFactory } from 'location.osm';

export class Factory
{
	static create(code: string, languageId: string, sourceLanguageId: string, sourceProps = {}): ?BaseSource
	{
		const props = sourceProps;
		props.languageId = languageId;
		props.sourceLanguageId = sourceLanguageId;

		if (code === Google.code)
		{
			return new Google(props);
		}
		else if (code === OSM.code)
		{
			return OSMFactory.createOSMSource(props);
		}

		return null;
	}
}
