<?php
/**
 * Bancha test fixture
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Fixture
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Bancha test fixture
 *
 * @package       Bancha.Test.Fixture
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class ArticleFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => true),
		'date' => array('type' => 'datetime', 'null' => true),
		'body' => array('type' => 'string', 'null' => true),
		'published' => array('type' => 'boolean', 'default' => false),
		'user_id' => array('type' => 'integer', 'null' => false),
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('id' => 1001, 'title' => 'Title 1', 'date' => '2011-11-24 03:40:04', 'body' => 'Text 1', 'published' => true, 'user_id' => 2),
		array('id' => 1002, 'title' => 'Title 2', 'date' => '2011-12-24 03:40:04', 'body' => 'Text 2', 'published' => false, 'user_id' => 3),
		array('id' => 1003, 'title' => 'Title 3', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 3', 'published' => false, 'user_id' => 3),
		array('id' => 1004, 'title' => 'Title 4', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 4', 'published' => false, 'user_id' => 3),
		array('id' => 1005, 'title' => 'Title 5', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 5', 'published' => false, 'user_id' => 3),
		array('id' => 1006, 'title' => 'Title 6', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 6', 'published' => false, 'user_id' => 3),
		array('id' => 1007, 'title' => 'Title 7', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 7', 'published' => false, 'user_id' => 3),
		array('id' => 1008, 'title' => 'Title 8', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 8', 'published' => false, 'user_id' => 3),
		array('id' => 1009, 'title' => 'Title 9', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 9', 'published' => false, 'user_id' => 3),
		array('id' => 1010, 'title' => 'Title 10', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 10', 'published' => false, 'user_id' => 3),
		array('id' => 1011, 'title' => 'Title 11', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 11', 'published' => false, 'user_id' => 3),
		array('id' => 1012, 'title' => 'Title 12', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 12', 'published' => false, 'user_id' => 3),
		array('id' => 1013, 'title' => 'Title 13', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 13', 'published' => false, 'user_id' => 3),
		array('id' => 1014, 'title' => 'Title 14', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 14', 'published' => false, 'user_id' => 3),
		array('id' => 1015, 'title' => 'Title 15', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 15', 'published' => false, 'user_id' => 3),
		array('id' => 1016, 'title' => 'Title 16', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 16', 'published' => false, 'user_id' => 3),
		array('id' => 1017, 'title' => 'Title 17', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 17', 'published' => false, 'user_id' => 3),
		array('id' => 1018, 'title' => 'Title 18', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 18', 'published' => false, 'user_id' => 3),
		array('id' => 1019, 'title' => 'Title 19', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 19', 'published' => false, 'user_id' => 3),
		array('id' => 1020, 'title' => 'Title 20', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 20', 'published' => false, 'user_id' => 3),
		array('id' => 1021, 'title' => 'Title 21', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 21', 'published' => false, 'user_id' => 3),
		array('id' => 1022, 'title' => 'Title 22', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 22', 'published' => false, 'user_id' => 3),
		array('id' => 1023, 'title' => 'Title 23', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 23', 'published' => false, 'user_id' => 3),
		array('id' => 1024, 'title' => 'Title 24', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 24', 'published' => false, 'user_id' => 3),
		array('id' => 1025, 'title' => 'Title 25', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 25', 'published' => false, 'user_id' => 3),
		array('id' => 1026, 'title' => 'Title 26', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 26', 'published' => false, 'user_id' => 3),
		array('id' => 1027, 'title' => 'Title 27', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 27', 'published' => false, 'user_id' => 3),
		array('id' => 1028, 'title' => 'Title 28', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 28', 'published' => false, 'user_id' => 3),
		array('id' => 1029, 'title' => 'Title 29', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 29', 'published' => false, 'user_id' => 3),
		array('id' => 1030, 'title' => 'Title 30', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 30', 'published' => false, 'user_id' => 3),
		array('id' => 1031, 'title' => 'Title 31', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 31', 'published' => false, 'user_id' => 3),
		array('id' => 1032, 'title' => 'Title 32', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 32', 'published' => false, 'user_id' => 3),
		array('id' => 1033, 'title' => 'Title 33', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 33', 'published' => false, 'user_id' => 3),
		array('id' => 1034, 'title' => 'Title 34', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 34', 'published' => false, 'user_id' => 3),
		array('id' => 1035, 'title' => 'Title 35', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 35', 'published' => false, 'user_id' => 3),
		array('id' => 1036, 'title' => 'Title 36', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 36', 'published' => false, 'user_id' => 3),
		array('id' => 1037, 'title' => 'Title 37', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 37', 'published' => false, 'user_id' => 3),
		array('id' => 1038, 'title' => 'Title 38', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 38', 'published' => false, 'user_id' => 3),
		array('id' => 1039, 'title' => 'Title 39', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 39', 'published' => false, 'user_id' => 3),
		array('id' => 1040, 'title' => 'Title 40', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 40', 'published' => false, 'user_id' => 3),
		array('id' => 1041, 'title' => 'Title 41', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 41', 'published' => false, 'user_id' => 3),
		array('id' => 1042, 'title' => 'Title 42', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 42', 'published' => false, 'user_id' => 3),
		array('id' => 1043, 'title' => 'Title 43', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 43', 'published' => false, 'user_id' => 3),
		array('id' => 1044, 'title' => 'Title 44', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 44', 'published' => false, 'user_id' => 3),
		array('id' => 1045, 'title' => 'Title 45', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 45', 'published' => false, 'user_id' => 3),
		array('id' => 1046, 'title' => 'Title 46', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 46', 'published' => false, 'user_id' => 3),
		array('id' => 1047, 'title' => 'Title 47', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 47', 'published' => false, 'user_id' => 3),
		array('id' => 1048, 'title' => 'Title 48', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 48', 'published' => false, 'user_id' => 3),
		array('id' => 1049, 'title' => 'Title 49', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 49', 'published' => false, 'user_id' => 3),
		array('id' => 1050, 'title' => 'Title 50', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 50', 'published' => false, 'user_id' => 3),
		array('id' => 1051, 'title' => 'Title 51', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 51', 'published' => false, 'user_id' => 3),
		array('id' => 1052, 'title' => 'Title 52', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 52', 'published' => false, 'user_id' => 3),
		array('id' => 1053, 'title' => 'Title 53', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 53', 'published' => false, 'user_id' => 3),
		array('id' => 1054, 'title' => 'Title 54', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 54', 'published' => false, 'user_id' => 3),
		array('id' => 1055, 'title' => 'Title 55', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 55', 'published' => false, 'user_id' => 3),
		array('id' => 1056, 'title' => 'Title 56', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 56', 'published' => false, 'user_id' => 3),
		array('id' => 1057, 'title' => 'Title 57', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 57', 'published' => false, 'user_id' => 3),
		array('id' => 1058, 'title' => 'Title 58', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 58', 'published' => false, 'user_id' => 3),
		array('id' => 1059, 'title' => 'Title 59', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 59', 'published' => false, 'user_id' => 3),
		array('id' => 1060, 'title' => 'Title 60', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 60', 'published' => false, 'user_id' => 3),
		array('id' => 1061, 'title' => 'Title 61', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 61', 'published' => false, 'user_id' => 3),
		array('id' => 1062, 'title' => 'Title 62', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 62', 'published' => false, 'user_id' => 3),
		array('id' => 1063, 'title' => 'Title 63', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 63', 'published' => false, 'user_id' => 3),
		array('id' => 1064, 'title' => 'Title 64', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 64', 'published' => false, 'user_id' => 3),
		array('id' => 1065, 'title' => 'Title 65', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 65', 'published' => false, 'user_id' => 3),
		array('id' => 1066, 'title' => 'Title 66', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 66', 'published' => false, 'user_id' => 3),
		array('id' => 1067, 'title' => 'Title 67', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 67', 'published' => false, 'user_id' => 3),
		array('id' => 1068, 'title' => 'Title 68', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 68', 'published' => false, 'user_id' => 3),
		array('id' => 1069, 'title' => 'Title 69', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 69', 'published' => false, 'user_id' => 3),
		array('id' => 1070, 'title' => 'Title 70', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 70', 'published' => false, 'user_id' => 3),
		array('id' => 1071, 'title' => 'Title 71', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 71', 'published' => false, 'user_id' => 3),
		array('id' => 1072, 'title' => 'Title 72', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 72', 'published' => false, 'user_id' => 3),
		array('id' => 1073, 'title' => 'Title 73', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 73', 'published' => false, 'user_id' => 3),
		array('id' => 1074, 'title' => 'Title 74', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 74', 'published' => false, 'user_id' => 3),
		array('id' => 1075, 'title' => 'Title 75', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 75', 'published' => false, 'user_id' => 3),
		array('id' => 1076, 'title' => 'Title 76', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 76', 'published' => false, 'user_id' => 3),
		array('id' => 1077, 'title' => 'Title 77', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 77', 'published' => false, 'user_id' => 3),
		array('id' => 1078, 'title' => 'Title 78', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 78', 'published' => false, 'user_id' => 3),
		array('id' => 1079, 'title' => 'Title 79', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 79', 'published' => false, 'user_id' => 3),
		array('id' => 1080, 'title' => 'Title 80', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 80', 'published' => false, 'user_id' => 3),
		array('id' => 1081, 'title' => 'Title 81', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 81', 'published' => false, 'user_id' => 3),
		array('id' => 1082, 'title' => 'Title 82', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 82', 'published' => false, 'user_id' => 3),
		array('id' => 1083, 'title' => 'Title 83', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 83', 'published' => false, 'user_id' => 3),
		array('id' => 1084, 'title' => 'Title 84', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 84', 'published' => false, 'user_id' => 3),
		array('id' => 1085, 'title' => 'Title 85', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 85', 'published' => false, 'user_id' => 3),
		array('id' => 1086, 'title' => 'Title 86', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 86', 'published' => false, 'user_id' => 3),
		array('id' => 1087, 'title' => 'Title 87', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 87', 'published' => false, 'user_id' => 3),
		array('id' => 1088, 'title' => 'Title 88', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 88', 'published' => false, 'user_id' => 3),
		array('id' => 1089, 'title' => 'Title 89', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 89', 'published' => false, 'user_id' => 3),
		array('id' => 1090, 'title' => 'Title 90', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 90', 'published' => false, 'user_id' => 3),
		array('id' => 1091, 'title' => 'Title 91', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 91', 'published' => false, 'user_id' => 3),
		array('id' => 1092, 'title' => 'Title 92', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 92', 'published' => false, 'user_id' => 3),
		array('id' => 1093, 'title' => 'Title 93', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 93', 'published' => false, 'user_id' => 3),
		array('id' => 1094, 'title' => 'Title 94', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 94', 'published' => false, 'user_id' => 3),
		array('id' => 1095, 'title' => 'Title 95', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 95', 'published' => false, 'user_id' => 3),
		array('id' => 1096, 'title' => 'Title 96', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 96', 'published' => false, 'user_id' => 3),
		array('id' => 1097, 'title' => 'Title 97', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 97', 'published' => false, 'user_id' => 3),
		array('id' => 1098, 'title' => 'Title 98', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 98', 'published' => false, 'user_id' => 3),
		array('id' => 1099, 'title' => 'Title 99', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 99', 'published' => false, 'user_id' => 3),
		array('id' => 1100, 'title' => 'Title 100', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 100', 'published' => false, 'user_id' => 3),
		array('id' => 1101, 'title' => 'Title 101', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 101', 'published' => false, 'user_id' => 3),
		array('id' => 1102, 'title' => 'Title 102', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 102', 'published' => false, 'user_id' => 3),
		array('id' => 1103, 'title' => 'Title 103', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 103', 'published' => false, 'user_id' => 3),
	);
}
