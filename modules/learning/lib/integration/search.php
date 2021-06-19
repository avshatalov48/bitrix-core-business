<?

namespace Bitrix\Learning\Integration;

use Bitrix\Main\Loader;

class Search
{
	/**
	 * Makes a search index for the lesson.
	 *
	 * @param int $lessonId - Lesson Id.
	 *
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \LearnException
	 *
	 * @return void
	 */
	public static function indexLesson($lessonId)
	{
		if (!Loader::includeModule("search"))
		{
			return;
		}

		$courseId = \CLearnLesson::getLinkedCourse($lessonId);
		if ($courseId !== false)
		{
			$dbCourse = \CCourse::getList(
				[], ["ID" => $courseId, "ACTIVE" => "Y", "ACTIVE_DATE" => "Y"]
			);

			if (!$dbCourse->fetch())
			{
				\CSearch::deleteIndex("learning", "U\\_".$courseId."\\_%");
				return;
			}
		}

		\CSearch::deleteIndex("learning", "U\\_%", "L".$lessonId, null);

		$items = static::getIndexItems($lessonId);
		foreach ($items as $item)
		{
			unset($item["ID"]); //CSearchCallback uses ID, but CSearch::index throws a MYSQL Error
			\CSearch::index("learning", $item["ITEM_ID"], $item);
		}
	}

	/**
	 * Re-indexes all lessons
	 *
	 * @param array $nextStep - Next step settings.
	 * @param \CSearchCallback $callbackObject - Search callback object.
	 * @param string $callbackMethod - Search callback method name.
	 *
	 * @return array|bool|string
	 * @throws \LearnException
	 */
	public static function handleReindex($nextStep = [], $callbackObject = null, $callbackMethod = "")
	{
		$result = array();
		$elementStartId = 0;
		$indexElementType = "C"; // start reindex from courses

		if (isset($nextStep["ID"]) && $nextStep["ID"] <> '')
		{
			$indexElementType = mb_substr($nextStep["ID"], 0, 1);
			$elementStartId = intval(mb_substr($nextStep["ID"], 1));
		}

		if ($indexElementType === "C")
		{
			$dbCourse = \CCourse::getList(["ID" => "ASC"], [">ID" => $elementStartId]);
			while ($course = $dbCourse->fetch())
			{
				$linkedLessonId = \CCourse::courseGetLinkedLesson($course["ID"]);
				if ($linkedLessonId === false)
				{
					continue;
				}

				$res = true;
				$items = static::getIndexItems($linkedLessonId);

				foreach ($items as $item)
				{
					if ($callbackObject)
					{
						$res &= call_user_func(array($callbackObject, $callbackMethod), $item);
					}
					else
					{
						$result[] = $item;
					}
				}

				if (!$res)
				{
					return ("C".$course["ID"]);
				}
			}

			// Reindex of courses finished. Let's reindex lessons now.
			$indexElementType = "U";
			$elementStartId = 0;
		}

		if ($indexElementType === "U")
		{
			$dbLessons = \CLearnLesson::getList(
				["LESSON_ID" => "ASC"],
				["LINKED_LESSON_ID" => "", ">LESSON_ID" => $elementStartId]
			);

			while ($lesson = $dbLessons->fetch())
			{
				$res = true;
				$items = static::getIndexItems($lesson["LESSON_ID"]);

				foreach ($items as $item)
				{
					if ($callbackObject)
					{
						$res &= call_user_func(array($callbackObject, $callbackMethod), $item);
					}
					else
					{
						$result[] = $item;
					}
				}

				if (!$res)
				{
					return ("U".$lesson["LESSON_ID"]);
				}
			}
		}

		return !empty($result) ? $result : false;
	}

	private static function getLessonCourseId($lessonId)
	{
		$ids = [];
		$paths = \CLearnLesson::getListOfParentPathes($lessonId);

		foreach ($paths as $path)
		{
			$parentLessons = $path->getPathAsArray();
			foreach ($parentLessons as $parentLessonId)
			{
				$linkedCourseId = \CLearnLesson::getLinkedCourse($parentLessonId);
				if (
					$linkedCourseId !== false &&
					$linkedCourseId > 0 &&
					!\CLearnLesson::isPublishProhibited($lessonId, $parentLessonId)
				)
				{
					$ids[] = $linkedCourseId;
				}
			}
		}

		return $ids;
	}

	private static function getIndexItems($lessonId)
	{
		$dbResult = \CLearnLesson::getList([], ["=LESSON_ID" => $lessonId, "ACTIVE" => "Y"]);
		if (!$lesson = $dbResult->fetch())
		{
			return [];
		}

		$permissions = \CLearnAccess::getSymbolsAccessibleToLesson($lessonId, \CLearnAccess::OP_LESSON_READ);
		$isCourseEntity = intval($lesson["LINKED_LESSON_ID"]) > 0;
		$lessonCourses = $isCourseEntity ? [$lesson["COURSE_ID"]] : static::getLessonCourseId($lessonId);

		$result = [];
		foreach ($lessonCourses as $courseId)
		{
			if ($lesson["DETAIL_TEXT_TYPE"] !== "text")
			{
				$detailText = \CSearch::killTags($lesson["DETAIL_TEXT"]);
			}
			else
			{
				$detailText = strip_tags($lesson["DETAIL_TEXT"]);
			}

			$entityType = $isCourseEntity ? "C" : ($lesson["IS_CHILDS"] ? "H" : "L");
			$result[] = [
				"ID" => "U_".$courseId."_".$lesson["LESSON_ID"],
				"ITEM_ID" => "U_".$courseId."_".$lesson["LESSON_ID"],
				"PARAM1" => "L".$lesson["LESSON_ID"],
				"PARAM2" => "C".$courseId,
				"LAST_MODIFIED" => $lesson["TIMESTAMP_X"],
				"TITLE" => $lesson["NAME"],
				"BODY" => $detailText <> '' ? $detailText : $lesson["NAME"],
				"SITE_ID" => static::getCoursePaths($lesson["LESSON_ID"], $entityType, $courseId),
				"PERMISSIONS" => $permissions
			];

		}

		return $result;
	}

	private static function getCoursePaths($entityId, $entityType, $courseId)
	{
		static $courseToSiteCache = [];

		$courseId = intval($courseId);
		$paths = [];

		if (!isset($courseToSiteCache[$courseId]))
		{
			$rc = $GLOBALS["DB"]->query("SELECT SITE_ID FROM b_learn_course_site WHERE COURSE_ID=".$courseId, true);
			if ($rc === false)
			{
				return $paths;
			}

			$courseToSiteCache[$courseId] = [];
			while ($courseSite = $rc->fetch())
			{
				$courseToSiteCache[$courseId][] = $courseSite["SITE_ID"];
			}
		}

		if (!isset($courseToSiteCache[$courseId]))
		{
			return $paths;
		}

		$sitePaths = static::getSitePaths();
		foreach ($courseToSiteCache[$courseId] as $siteId)
		{
			if (!isset($sitePaths[$siteId]) || !isset($sitePaths[$siteId][$entityType]))
			{
				continue;
			}

			$url = str_replace("#COURSE_ID#", $courseId, $sitePaths[$siteId][$entityType]);
			$url = str_replace("#CHAPTER_ID#", "0".$entityId, $url);
			$url = str_replace("#LESSON_ID#", $entityId, $url);

			$paths[$siteId] = $url;
		}

		return $paths;
	}

	private static function getSitePaths()
	{
		static $paths = [];

		if (!empty($paths))
		{
			return $paths;
		}

		$sites = \CLang::getList("ID", "ASC", Array("TYPE" => "C"));
		while ($site = $sites->fetch())
		{
			foreach (["C", "H", "L"] as $entityCode)
			{
				$path = \CCourse::getSitePathes($site["LID"], $entityCode);
				$paths[$site["LID"]][$entityCode] = isset($path[0]) ? $path[0] : "";
			}
		}

		return $paths;
	}
}