import { Url } from './url'

export class History
{
    constructor(options)
    {
        this.location = options.location;
        this.params = options.params;
    }

    build()
    {
        let path = this.location;
        let params = this.params;

        try
        {
            for (let name in params)
            {
                if (!params.hasOwnProperty(name))
                {
                    continue;
                }
                path = Url.addLinkParam(path, name, params[name]);
            }
        }
        catch (e) {}

        return path;
    }

    static pushState(location, params)
    {
        let url = new History({location, params})
            .build();

        window.history.pushState(null, null, url);

        return url;
    }
}