<?php

namespace Bitrix\Bizproc\Calc;

class Functions
{
	private static array $functions;
	private static array $libsClasses = [
		'logic' => Libs\LogicLib::class,
		'string' => Libs\StringLib::class,
		'math' => Libs\MathLib::class,
		'date' => Libs\DateLib::class,
		'array' => Libs\ArrayLib::class,
		'document' => Libs\DocumentLib::class,
	];

	public static function getList(): array
	{
		if (!isset(static::$functions))
		{
			static::$functions = [];
			foreach (static::getLibs() as $lib)
			{
				static::$functions += static::getLibFunctions($lib);
			}
		}

		return static::$functions;
	}

	/**
	 * @return Libs\BaseLib[]
	 */
	protected static function getLibs(): array
	{
		$libs = [];

		/** @var Libs\BaseLib $libClass */
		foreach (static::$libsClasses as $libClass)
		{
			$libs[] = new $libClass();
		}

		//TODO: send Event

		return $libs;
	}

	protected static function getLibFunctions(Libs\BaseLib $lib): array
	{
		return array_map(
			static function($function) use ($lib)
			{
				$function['func'] = \Closure::fromCallable([$lib, $function['func']]);

				return $function;
			},
			$lib->getFunctions()
		);
	}
}
