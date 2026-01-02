<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data;

use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Data\Block\Collector;
use Bitrix\Landing\Copilot\Data\Block\Operator;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Type\ColorsDto;
use Bitrix\Landing\Copilot\Data\Type\FontsDto;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Block;
use Bitrix\Landing\Node\Icon;
use Bitrix\Landing\Node\Img;
use Bitrix\Landing\Node\Link;
use Bitrix\Landing\Node\Text;
use Bitrix\Main\Localization\Loc;

/**
 * Class Site
 *
 * Manages site-related data including blocks, titles, colors, and fonts.
 */
class Site
{
	/**
	 * @var Data\Block[] Data of the blocks used in the site.
	 */
	private array $blocks = [];
	private ?string $titleStyleClass = null;

	private ?int $siteId = null;
	private ?int $landingId = null;
	private string $siteTitle;
	private string $pageTitle;
	private string $pageDescription;
	private string $siteTopic;
	private string $imageStyle;
	private ?string $keywords;
	private ?string $previewImagePromptText;
	private ?string $previewImageSrc;

	private ?ColorsDto $colors;
	private ?FontsDto $fonts;
	private Wishes $wishes;

	/**
	 * Site constructor.
	 */
	public function __construct()
	{
		$this->siteTitle = Loc::getMessage('LANDING_COPILOT_DEFAULT_SITE_TITLE');
		$this->pageTitle = Loc::getMessage('LANDING_COPILOT_DEFAULT_PAGE_TITLE');
		$this->pageDescription = Loc::getMessage('LANDING_COPILOT_DEFAULT_PAGE_DESCRIPTION');
		$this->siteTopic = '';
		$this->imageStyle = '';

		$this->keywords = null;

		$this->colors = new ColorsDto();
		$this->fonts = new FontsDto();

		$this->wishes = new Wishes();

		$this->previewImagePromptText = null;
		$this->previewImageSrc = null;
	}

	/**
	 * Initializes the site with random blocks and a random title style class.
	 *
	 * @return void
	 */
	public function initRandom(): void
	{
		$this->blocks = (new Collector())->getBlocks();
		foreach ($this->blocks as $block)
		{
			$block->setParentSite($this);
		}
		$this->titleStyleClass = Operator::getTitleStyleClass();
	}

	public function initByBlockIds(array $blockIds): void
	{
		foreach ($blockIds as $blockId)
		{
			$blockDataFromTable = BlockTable::getList([
				'select' => ['LID'],
				'filter' => [
					'ID' => $blockId,
				],
			])->fetch();

			if (!$blockDataFromTable)
			{
				continue;
			}

			$landingId = (int)$blockDataFromTable['LID'];
			Landing::setEditMode();
			$landingInstance = Landing::createInstance($landingId, [
				'blocks_id' => $blockId
			]);
			$pageTitle = $landingInstance->getTitle();
			$siteId = $landingInstance->getSiteId();
			$siteData = \Bitrix\Landing\Site::getList([
				'select' => [
					'TITLE'
				],
				'filter' => [
					'ID' => $siteId
				]
			 ])->fetch();
			$siteTitle = $siteData['TITLE'];
			$blockInstance = $landingInstance->getBlockById($blockId);
			if (!$blockInstance)
			{
				continue;
			}

			if (isset($siteId))
			{
				$this->setSiteId($siteId);
			}
			$this->setLandingId($landingId);
			if (isset($siteTitle) && is_string($siteTitle))
			{
				$this->setSiteTitle($siteTitle);
			}
			if (isset($pageTitle) && is_string($pageTitle))
			{
				$this->setPageTitle($pageTitle);
			}

			$this->initBlockById($blockInstance);
		}
	}

	private function initBlockById(Block $blockInstance): void
	{
		$blockNodeValues = $this->initBlockNodes($blockInstance);

		$block = new Data\Block($blockInstance->getCode(), '', '');
		$block->setId($blockInstance->getId());

		if (!empty($blockNodeValues))
		{
			foreach ($blockNodeValues as $key => $nodeData)
			{
				if ($node = Node::create(NodeType::from($nodeData['type'] ?? ''), $key, $nodeData))
				{
					$block->addNode($node);
				}
			}
		}

		$this->addBlock($block);
	}

	private function initBlockNodes(Block $blockInstance): array
	{
		$blockManifest = $blockInstance->getManifest();
		$blockContent = $blockInstance->getContent();
		$blockContentFromRepo = Block::getContentFromRepository($blockInstance->getCode());

		$blockNodeValues = [];

		foreach ($blockManifest['nodes'] as $node)
		{
			$nodeType = $node['type'] ?? '';
			$nodeCode = $node['code'] ?? '';
			$nodeValues = [];
			$dataImages = [];

			if (!in_array($nodeType, ['text', 'link', 'icon', 'img'], true))
			{
				continue;
			}

			switch ($nodeType)
			{
				case 'text':
					$nodesData = Text::getNode($blockInstance, $node['code']);
					foreach ($nodesData as $nodeData)
					{
						$nodeValues[] = $nodeData;
					}
					$nodeValues = array_merge($nodeValues, $nodesData);
					break;

				case 'link':
					$nodesData = Link::getNode($blockInstance, $node['code']);
					foreach ($nodesData as $nodeData)
					{
						$nodeValues[] = $nodeData['text'];
					}
					break;

				case 'icon':
					$nodesData = Icon::getNode($blockInstance, $node['code']);
					foreach ($nodesData as $nodeData)
					{
						$nodeValues[] = $nodeData['classList'][0];
					}
					break;

				case 'img':
					$nodesData = Img::getNode($blockInstance, $node['code']);
					$nodeCount = 0;

					foreach ($nodesData as $nodeData)
					{
						if (isset($nodeData['id']) && $nodeData['id'] > 0)
						{
							$fileArray = \CFile::GetFileArray($nodeData['id']);
							$width = $fileArray['WIDTH'] > 0 ? $fileArray['WIDTH'] : 1;
							$height = $fileArray['HEIGHT'] > 0 ? $fileArray['HEIGHT'] : 1;
							$src = $fileArray['SRC'] ?? '';
							$src2x = $src;
							$aspectRatio = Operator::getClosestAspectRatioKey($width / $height);
							$width = Operator::getClosestWidth((int)$width, $aspectRatio);
						}
						else
						{
							$sizeImages = Operator::getSizeDataImagesBySelector($nodeCode, $blockContentFromRepo);
							$aspectRatio = $sizeImages[$nodeCount]['aspectRatio'] ?? Operator::getDefaultAspectRatio();
						}
						if (isset($nodeData['src']) && $nodeData['src'] !== '')
						{
							$src = $nodeData['src'];
						}
						if (isset($nodeData['src2x']) && $nodeData['src2x'] !== '')
						{
							$src2x = $nodeData['src2x'];
						}

						$dataImages[] = [
							'aspectRatio' => $aspectRatio,
							'width' => $width ?? Operator::getDefaultWidth($aspectRatio),
						];
						$defaultSrcData = Operator::getDefaultSrc($dataImages);
						$src = $src ?? $defaultSrcData['src'][0];
						$nodeValues[] = [
							'defaultSrc' => $defaultSrcData['src'][0],
							'defaultSrc2x' => $defaultSrcData['src2x'][0],
							'src' => $src,
							'src2x' => $src2x ?? $src,
						];
						$nodeCount++;
					}
					break;
			}

			$trimNodeCode = ltrim($node['code'], '.');
			$nodeAmountWithSpace = substr_count($blockContent, $trimNodeCode . ' ');
			$nodeAmountWithQuota = substr_count($blockContent, $trimNodeCode . '"');
			$placeholders = array_fill(0, $nodeAmountWithSpace + $nodeAmountWithQuota, '');

			$blockNodeValues[$nodeCode] = [
				'type' => $nodeType,
				'values' => $nodeValues,
				'placeholders' => $placeholders,
			];

			if ($node['type'] === 'img')
			{
				$blockNodeValues[$nodeCode]['sizeData'] = $dataImages;
			}
		}

		return $blockNodeValues;
	}

	/**
	 * Adds a block to the site's blocks collection.
	 *
	 * @param Data\Block $block The Block object to be added to the site.
	 *
	 * @return self The updated Site object, enabling method chaining.
	 */
	public function addBlock(Data\Block $block): self
	{
		$block->setParentSite($this);
		$this->blocks[] = $block;

		return $this;
	}

	/**
	 * Retrieves the data of the blocks used in the site.
	 * @param int[]|null $ids - if set - return just blocks with selected IDs
	 * @return Block[] Array of blocks data.
	 */
	public function getBlocks(?array $ids = null): array
	{
		if (empty($ids))
		{
			return $this->blocks;
		}

		return array_filter($this->blocks, static function (Data\Block $block) use ($ids) {
			return in_array($block->getId(), $ids, true);
		});
	}

	/**
	 * Retrieves the CSS class used for styling titles within the site.
	 *
	 *
	 * @return string The CSS class name used for the site's title style.
	 */
	public function getTitleStyleClass(): string
	{
		return $this->titleStyleClass;
	}

	/**
	 * Sets the site title from the provided data.
	 *
	 * @param string $title
	 *
	 * @return Site
	 */
	public function setSiteTitle(string $title): self
	{
		$this->siteTitle = $title;

		return $this;
	}

	/**
	 * Sets the page title from the provided data.
	 *
	 * @param string $title
	 *
	 * @return Site
	 */
	public function setPageTitle(string $title): self
	{
		$this->pageTitle = $title;

		return $this;
	}

	/**
	 * Sets the page description from the provided data.
	 *
	 * @param string $title
	 *
	 * @return Site
	 */
	public function setPageDescription(string $title): self
	{
		$this->pageDescription = $title;

		return $this;
	}

	/**
	 * Sets the site topic from the provided data.
	 *
	 * @param string $topic
	 *
	 * @return Site
	 */
	public function setSiteTopic(string $topic): self
	{
		$this->siteTopic = $topic;

		return $this;
	}

	/**
	 * Sets the image style from the provided data.
	 *
	 * @param string $style
	 *
	 * @return Site
	 */
	public function setImageStyle(string $style): self
	{
		$this->imageStyle = $style;

		return $this;
	}

	/**
	 * Sets the keywords from the provided data.
	 *
	 * @param string $keywords
	 *
	 * @return Site
	 */
	public function setKeywords(string $keywords): self
	{
		$this->keywords = $keywords;

		return $this;
	}

	/**
	 * Retrieves the site title.
	 *
	 * @return string The site title or a default value if not set.
	 */
	public function getSiteTitle(): string
	{
		return $this->siteTitle;
	}

	/**
	 * Retrieves the page title.
	 *
	 * @return string The page title or a default value if not set.
	 */
	public function getPageTitle(): string
	{
		return $this->pageTitle;
	}

	/**
	 * Retrieves the page description.
	 *
	 * @return string The page title or a default value if not set.
	 */
	public function getPageDescription(): string
	{
		return $this->pageDescription;
	}

	/**
	 * Retrieves the site topic.
	 *
	 * @return string The site topic.
	 */
	public function getSiteTopic(): string
	{
		return $this->siteTopic;
	}

	/**
	 * Retrieves the image style.
	 *
	 * @return string The image style.
	 */
	public function getImageStyle(): string
	{
		return $this->imageStyle;
	}

	/**
	 * Retrieves the keywords.
	 *
	 * @return string|null The keywords or null if not set.
	 */
	public function getKeywords(): ?string
	{
		return $this->keywords;
	}

	/**
	 * Sets the colors used in the site from the provided data.
	 *
	 * @param ColorsDto $colors
	 *
	 * @return Site
	 */
	public function setColors(ColorsDto $colors): self
	{
		$this->colors = $colors;

		return $this;
	}

	/**
	 * Retrieves the colors used in the site.
	 *
	 * @return ColorsDto DTO of colors.
	 */
	public function getColors(): ColorsDto
	{
		return $this->colors;
	}

	/**
	 * Sets the fonts used in the site from the provided data.
	 *
	 * @param FontsDto $fonts
	 *
	 * @return Site
	 */
	public function setFonts(FontsDto $fonts): self
	{
		$this->fonts = $fonts;

		return $this;
	}

	/**
	 * Retrieves the fonts used in the site.
	 *
	 * @return FontsDto DTO of fonts.
	 */
	public function getFonts(): FontsDto
	{
		return $this->fonts;
	}

	/**
	 * Sets the preview image prompt text from the provided data.
	 *
	 * @param string $previewImagePromptText
	 *
	 * @return Site
	 */
	public function setPreviewImagePromptText(string $previewImagePromptText): self
	{
		$this->previewImagePromptText = $previewImagePromptText;

		return $this;
	}

	/**
	 * Retrieves the preview image prompt text from the provided data.
	 *
	 * @return string
	 */
	public function getPreviewImagePromptText(): string
	{
		return $this->previewImagePromptText;
	}

	/**
	 * Sets the preview image src from the provided data.
	 *
	 * @param string $previewImageSrc
	 *
	 * @return Site
	 */
	public function setPreviewImageSrc(string $previewImageSrc): self
	{
		$this->previewImageSrc = $previewImageSrc;

		return $this;
	}

	/**
	 * Retrieves the preview image src from the provided data.
	 *
	 * @return string|null
	 */
	public function getPreviewImageSrc(): ?string
	{
		return $this->previewImageSrc;
	}


	/**
	 * Retrieves the data of the menu block used in the site.
	 *
	 * @return Data\Block|null Menu block data.
	 */
	public function getMenuBlock(): ?Data\Block
	{
		foreach ($this->blocks as $block)
		{
			if ($block->isMenu())
			{
				return $block;
			}
		}

		return null;
	}

	/**
	 * Sets the site ID for the site.
	 *
	 * @param int $id The site ID to be set.
	 *
	 * @return self The updated Site object, enabling method chaining.
	 */
	public function setSiteId(int $id): self
	{
		$this->siteId = $id;

		return $this;
	}

	/**
	 * Sets the landing ID for the site.
	 *
	 * @param int $id The landing ID to be set for the site.
	 *
	 * @return self The updated Site object, allowing method chaining.
	 */
	public function setLandingId(int $id): self
	{
		$this->landingId = $id;

		return $this;
	}

	/**
	 * Retrieves the site ID associated with the site.
	 *
	 * @return int|null The site ID, or null if not initialized.
	 */
	public function getSiteId(): ?int
	{
		// todo: cant return not init param
		return $this->siteId;
	}

	/**
	 * Retrieves the landing ID associated with the site.
	 *
	 * @return int|null The landing ID of the site, or null if not set.
	 */
	public function getLandingId(): ?int
	{
		return $this->landingId;
	}

	/**
	 * Retrieves a landing instance by its ID.
	 *
	 * @return Landing|null The landing instance.
	 */
	public function getLandingInstance(): ?Landing
	{
		if (!$this->landingId || $this->landingId <= 0)
		{
			return null;
		}

		Landing::setEditMode();

		return Landing::createInstance($this->landingId);
	}

	/**
	 * Sets the user data associated with the site.
	 *
	 * @param Wishes $data The data to assign to the wishes property, generally an object encapsulating user details.
	 *
	 * @return self The updated Site object, allowing method chaining.
	 */
	public function setWishes(Wishes $data): self
	{
		$this->wishes = $data;

		return $this;
	}

	/**
	 * Sets the CSS class for the title style of the site.
	 *
	 * @param string $titleStyleClass The CSS class to be set for the site's title style.
	 *
	 * @return self The updated Site object, enabling method chaining.
	 */
	public function setTitleStyleClass(string $titleStyleClass): self
	{
		$this->titleStyleClass = $titleStyleClass;

		return $this;
	}

	/**
	 * Retrieves the user data associated with the site.
	 *
	 * @return Wishes The Chat object containing user data, or null if not set.
	 */
	public function getWishes(): Wishes
	{
		return $this->wishes;
	}

	/**
	 * Converts the Site object to an associative array representation.
	 *
	 * @return array An associative array representing the Site object and its components.
	 */
	public function toArray(): array
	{
		$data = [];

		$data['wishes'] = $this->wishes->toArray();

		$data['siteId'] = $this->siteId;
		$data['landingId'] = $this->landingId;

		$data['siteTitle'] = $this->siteTitle;
		$data['pageTitle'] = $this->pageTitle;
		$data['pageDescription'] = $this->pageDescription;
		$data['siteTopic'] = $this->siteTopic;
		$data['imageStyle'] = $this->imageStyle;
		$data['keywords'] = $this->keywords ?? '';

		$data['blocks'] = [];
		foreach ($this->blocks as $block)
		{
			$data['blocks'][] = $block->toArray();
		}

		$data['titleStyleClass'] = $this->titleStyleClass;

		$data['colors'] = $this->colors;
		$data['fonts'] = $this->fonts;

		$data['preview']['prompt'] = $this->previewImagePromptText ?? '';
		$data['preview']['src'] = $this->previewImageSrc ?? '';

		return $data;
	}

	/**
	 * Creates a Site object from an associative array of data.
	 *
	 * @param array $data An associative array containing site and block data.
	 *
	 * @return self A Site object with properties populated from the provided array.
	 */
	public static function fromArray(array $data): self
	{
		$siteData = new Site();

		$siteData->setWishes(Wishes::fromArray($data['wishes'] ?? []));

		if (isset($data['siteId']))
		{
			$siteData->setSiteId($data['siteId']);
		}
		if (isset($data['landingId']))
		{
			$siteData->setLandingId($data['landingId']);
		}

		$siteData->setSiteTitle($data['siteTitle']);
		$siteData->setPageTitle($data['pageTitle']);
		$siteData->setPageDescription($data['pageDescription']);
		if (isset($data['siteTopic']))
		{
			$siteData->setSiteTopic($data['siteTopic']);
		}
		if (isset($data['imageStyle']))
		{
			$siteData->setImageStyle($data['imageStyle']);
		}
		$siteData->setKeywords($data['keywords']);

		if (isset($data['preview']['prompt']))
		{
			$siteData->setPreviewImagePromptText($data['preview']['prompt']);
		}
		if (isset($data['preview']['src']))
		{
			$siteData->setPreviewImageSrc($data['preview']['src']);
		}

		foreach ($data['blocks'] as $block)
		{
			$block = Data\Block::fromArray($block);
			$siteData->addBlock($block);
		}

		$siteData->setTitleStyleClass($data['titleStyleClass'] ?? '');

		$siteData->setColors($data['colors']);
		$siteData->setFonts($data['fonts']);

		return $siteData;
	}

	/**
	 * Initializes various site properties using data from a JSON structure.
	 *
	 * @param Site $siteData The site data object to initialize.
	 * @param array $json An associative array containing initial configuration data for the site.
	 *
	 * @return void
	 */
	public static function initSite(Site $siteData, array $json): void
	{
		self::initTitles($siteData, $json);
		self::initDescription($siteData, $json);
		self::initImages($siteData, $json);
		self::initKeywords($siteData, $json);
		self::initColors($siteData, $json);
		self::initFonts($siteData, $json);
	}

	/**
	 * Initializes the site and page titles of a site from a JSON configuration.
	 *
	 * @param Site $siteData The site data object to update with title information.
	 * @param array $json An associative array containing title configuration data for the site.
	 *
	 * @return void
	 */
	private static function initTitles(Site $siteData, array $json): void
	{
		if (
			isset($json['siteData']['titles'])
			&& is_array($json['siteData']['titles'])
		)
		{
			$titles = $json['siteData']['titles'];

			if (isset($titles['site']) && is_string($titles['site']))
			{
				$siteData->setSiteTitle($titles['site']);
			}
			if (isset($titles['page']) && is_string($titles['page']))
			{
				$siteData->setPageTitle($titles['page']);
			}
		}
	}

	/**
	 * Initializes the page description of a site from a JSON configuration.
	 *
	 * @param Site $siteData The site data object to update with description information.
	 * @param array $json An associative array containing description configuration data for the site.
	 *
	 * @return void
	 */
	private static function initDescription(Site $siteData, array $json): void
	{
		if (
			isset($json['siteData']['description'])
			&& is_array($json['siteData']['description'])
		)
		{
			$descriptions = $json['siteData']['description'];

			if (isset($descriptions['page']) && is_string($descriptions['page']))
			{
				$siteData->setPageDescription($descriptions['page']);
			}
		}
	}

	private static function initImages(Site $siteData, array $json): void
	{
		if (
			isset($json['siteData']['images'])
			&& is_array($json['siteData']['images'])
		)
		{
			$images = $json['siteData']['images'];

			if (isset($images['styleEng']) && is_string($images['styleEng']))
			{
				$siteData->setImageStyle($images['styleEng']);
			}

			if (isset($images['siteTopicEng']) && is_string($images['siteTopicEng']))
			{
				$siteData->setSiteTopic($images['siteTopicEng']);
			}

			if (isset($images['imgPromptEng']) && is_string($images['imgPromptEng']))
			{
				$siteData->setPreviewImagePromptText($images['imgPromptEng']);
			}
		}
	}

	/**
	 * Initializes the page keywords of a site from a JSON configuration.
	 *
	 * @param Site $siteData The site data object to update with keyword information.
	 * @param array $json An associative array containing keyword configuration data for the site.
	 *
	 * @return void
	 */
	private static function initKeywords(Site $siteData, array $json): void
	{
		//todo: why array ? not $keywords['page'] !!!
		if (
			isset($json['siteData']['keywords'])
			&& is_array($json['siteData']['keywords'])
		)
		{
			$keywords = $json['siteData']['keywords'];

			if (isset($keywords['page']) && is_string($keywords['page']))
			{
				$siteData->setKeywords($keywords['page']);
			}
		}
	}

	/**
	 * Updates the site's color scheme using JSON configuration.
	 *
	 * Validates required color keys and ensures at least one contrast pair key is present.
	 * Copies existing contrast values when a counterpart is missing.
	 *
	 * @param Site $siteData The site data object to update with color information.
	 * @param array $json The associative array with the site's color configuration.
	 *
	 * @return void
	 */
	private static function initColors(Site $siteData, array $json): void
	{
		$colorsData = $json['siteData']['colors'] ?? null;
		if (!is_array($colorsData))
		{
			return;
		}

		$requiredKeys = ['theme', 'background'];
		foreach ($requiredKeys as $key)
		{
			if (!isset($colorsData[$key]['hex']))
			{
				return;
			}
		}

		$contrastPairs = [
			['headerContrastOnBackground', 'textContrastOnBackground'],
			['headerContrastOnTheme', 'textContrastOnTheme'],
		];
		foreach ($contrastPairs as [$key1, $key2])
		{
			if (!isset($colorsData[$key1]['hex']) && !isset($colorsData[$key2]['hex']))
			{
				return;
			}
			$colorsData[$key1]['hex'] = $colorsData[$key1]['hex'] ?? $colorsData[$key2]['hex'];
			$colorsData[$key2]['hex'] = $colorsData[$key2]['hex'] ?? $colorsData[$key1]['hex'];
		}

		$colors = new ColorsDto();
		$colorProperties = [
			'theme' => ['hex' => 'theme', 'name' => 'themeName'],
			'background' => ['hex' => 'background', 'name' => 'backgroundName'],
			'headerContrastOnBackground' => ['hex' => 'headersBg'],
			'textContrastOnBackground' => ['hex' => 'textsBg'],
			'headerContrastOnTheme' => ['hex' => 'headersTheme'],
			'textContrastOnTheme' => ['hex' => 'textsTheme'],
		];

		foreach ($colorProperties as $key => $properties)
		{
			foreach ($properties as $property => $colorField)
			{
				if (isset($colorsData[$key][$property]))
				{
					$colors->$colorField = $colorsData[$key][$property];
				}
			}
		}

		$siteData->setColors($colors);
	}

	/**
	 * Initializes the font settings of a site from a JSON configuration.
	 *
	 * @param Site $siteData The site data object to update with font information.
	 * @param array $json An associative array containing font configuration data for the site.
	 *
	 * @return void
	 */
	private static function initFonts(Site $siteData, array $json): void
	{
		if (
			isset($json['siteData']['fonts'])
			&& is_array($json['siteData']['fonts'])
		)
		{
			$fonts = new FontsDto();
			$fontsData = $json['siteData']['fonts'];

			if (isset($fontsData['headers']['name']))
			{
				$fonts->headers = $fontsData['headers']['name'];
			}
			if (isset($fontsData['texts']['name']))
			{
				$fonts->texts = $fontsData['texts']['name'];
			}

			$siteData->setFonts($fonts);
		}
	}

	public function getImagesSet(): array
	{
		$imageSet = [];
		foreach ($this->getBlocks() as $block)
		{
			foreach ($block->getNodes() as $node)
			{
				if ($node->getType() === NodeType::Img)
				{
					$position = 0;
					foreach ($node->getValues() as $value)
					{
						$value['position'] = $position;
						$value['nodeCode'] = $node->getCode();
						$imageSet[$block->getId()][] = $value;
						$position++;
					}
				}
			}
		}

		return $imageSet;
	}
}