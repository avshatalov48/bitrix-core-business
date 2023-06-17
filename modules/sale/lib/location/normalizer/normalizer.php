<?
namespace Bitrix\Sale\Location\Normalizer;

/**
 * Normalize Locations names for search and mapping purposes.
 * Class Normalizer
 * @package Bitrix\Sale\Location\Normalizer
 */
class Normalizer implements INormalizer
{
	/** @var INormalizer [] */
	protected $normalizers = [];

	/**
	 * Normalizer constructor.
	 * @param INormalizer[] $normalizers
	 */
	public function __construct(array $normalizers)
	{
		foreach($normalizers as $normalizer)
		{
			$this->addNormalizer($normalizer);
		}
	}

	/**
	 * @param INormalizer $normalizer
	 */
	protected function addNormalizer(INormalizer $normalizer)
	{
		$this->normalizers[] = $normalizer;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function normalize($string)
	{
		$result = $string;

		/** @var INormalizer $normalizer */
		foreach($this->normalizers as $normalizer)
		{
			$result = $normalizer->normalize($result);
		}

		return $result;
	}
}
