<?php

// Ensure, that data in database converted to 11.5.0 version of module
if (COption::GetOptionString(
		'learning', 
		'~LearnInstall201203ConvertDB::_IsAlreadyConverted', 
		'-9', 
		''
	)
	!== '1'
)
{
	CModule::AddAutoloadClasses(
		'learning',
		array(
			'CLearnInstall201203ConvertDB' => 'classes/general/legacy/converter_to_11.5.0.php'
		)
	);

	if ( ! CLearnInstall201203ConvertDB::_IsAlreadyConverted() )
	{
		$learningLangFile = __DIR__ . '/lang/' . LANGUAGE_ID . '/lang.php';

		// Load english version, if localization not available
		if ( ! (file_exists($learningLangFile) && is_readable($learningLangFile)) )
			$learningLangFile = __DIR__ . '/lang/en/lang.php';

		if (file_exists($learningLangFile) && is_readable($learningLangFile))
		{
			$learningNotifyMessage = '';
			include($learningLangFile);
			$learningNotifyMessage = str_replace(
				'#LANG#', 
				LANGUAGE_ID, 
				$MESS['LEARNING_DATA_IN_DB_NEEDS_TO_BE_CONVERTED']
			);

			define ('LEARNING_FAILED_TO_LOAD_REASON', $learningNotifyMessage);
		}

		// Data for module not converted yet.
		return (false);
	}
}

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/learning/lang.php');

global $LEARNING_CACHE_COURSE;
$LEARNING_CACHE_COURSE = array();

CModule::AddAutoloadClasses(
	'learning',
	array(
		'CCourse'                          => 'classes/general/course.php',
		'CLQuestion'                       => 'classes/general/question.php',
		'CLAnswer'                         => 'classes/general/answer.php',
		'CAllGradeBook'                    => 'classes/general/gradebook.php',
		'CGradeBook'                       => 'classes/mysql/gradebook.php',
		'CTest'                            => 'classes/mysql/test.php',
		'CTestAttempt'                     => 'classes/mysql/attempt.php',
		'CTestResult'                      => 'classes/general/testresult.php',
		'CLTestMark'                       => 'classes/general/testmark.php',
		'CCertification'                   => 'classes/mysql/certification.php',
		'CStudent'                         => 'classes/general/student.php',
		'CSitePath'                        => 'classes/mysql/sitepath.php',
		'CCourseImport'                    => 'classes/general/import.php',
		'CCourseSCORM'                     => 'classes/general/scorm.php',
		'CCoursePackage'                   => 'classes/general/export.php',
		'CRatingsComponentsLearning'       => 'classes/general/ratings_components.php',
		'CLearnHelper'                     => 'classes/general/clearnhelper.php',
		'ILearnGraphNode'                  => 'classes/general/ilearngraphnode.php',
		'CLearnGraphNode'                  => 'classes/general/ilearngraphnode.php',
		'ILearnGraphRelation'              => 'classes/general/ilearngraphrelation.php',
		'CLearnGraphRelation'              => 'classes/general/ilearngraphrelation.php',
		'ILearnLesson'                     => 'classes/general/clearnlesson.php',
		'CLearnLesson'                     => 'classes/general/clearnlesson.php',
		'LearnException'                   => 'classes/general/learnexception.php',
		'CLearnPath'                       => 'classes/general/clearnpath.php',
		'CLearnSharedArgManager'           => 'classes/general/clearnsharedargmanager.php',
		'CLearnLessonTree'                 => 'classes/general/clearnlessontree.php',
		'CLearnAccess'                     => 'classes/general/clearnaccess.php',
		'CLearnRenderRightsEdit'           => 'classes/general/clearnrenderrightsedit.php',
		'CLearnParsePermissionsFromFilter' => 'classes/general/clearnparsepermissionsfromfilter.php',
		'CLearnRelationHelper'             => 'classes/general/clearnrelationhelper.php',
		'CLearnAccessMacroses'             => 'classes/general/clearnaccessmacroses.php',
		'CLearnCacheOfLessonTreeComponent' => 'classes/general/clearncacheoflessontreecomponent.php',
		'CLearningGroup'                   => 'classes/general/group.php',
		'CLearningGroupMember'             => 'classes/general/groupmember.php',
		'CLearningGroupLesson'             => 'classes/general/grouplesson.php',
		'CLearningEvent'                   => 'classes/general/event.php',

		// For backward compatibility only, don't relay on it!
		'CChapter'                         => 'classes/general/legacy/cchapter.php',
		'CLesson'                          => 'classes/general/legacy/clesson.php'
	)
);
