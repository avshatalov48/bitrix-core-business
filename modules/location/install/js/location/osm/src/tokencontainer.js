export default class TokenContainer
{
	#token;
	#sourceRepository;
	#isRefreshing = false;
	#refreshingPromise = null;

	constructor(props)
	{
		this.#token = props.token;
		this.#sourceRepository = props.sourceRepository;
	}

	get token()
	{
		return this.#token;
	}

	set token(token: string)
	{
		this.#token = token;
	}

	refreshToken()
	{
		if (this.#isRefreshing)
		{
			return this.#refreshingPromise;
		}

		this.#refreshingPromise = this.#sourceRepository.getProps()
			.then((sourceProps) => {
				this.token = sourceProps.sourceParams.token;
				this.#isRefreshing = false;
				return sourceProps.sourceParams.token;
			})
			.catch((response) => {
				this.#isRefreshing = false;
				console.error(response);
			});

		this.#isRefreshing = true;
		return this.#refreshingPromise;
	}
}