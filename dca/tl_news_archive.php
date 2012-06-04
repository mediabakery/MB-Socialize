<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  mediabakery 2011
 * @author     Sebastian Tilch <http://mediabakery.de>
 * @package    MB_Socialize
 * @license    LGPL
 * @filesource
 */


$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace('makeFeed','makeFeed;{mb_socialize_legend:hide},socialNews, socialNewsService, facebookTeaserLength, defaultPic', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);


$GLOBALS['TL_DCA']['tl_news_archive']['fields']['socialNews'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['socialNews'],
	'inputType'               => 'checkbox',
	'eval'                    => array('mandatory'=>false),
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['socialNewsService'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['socialNewsService'],
	'inputType'               => 'select',
	'eval'                    => array('mandatory'=>false),
	'foreignKey'              => 'tl_mb_socialize.name'
);


$GLOBALS['TL_DCA']['tl_news_archive']['fields']['facebookTeaserLength'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['facebookTeaserLength'],
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit')
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['defaultPic'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['defaultPic'],
	'inputType'               => 'fileTree',
	'eval'                    => array('mandatory'=>false, 'files' =>true, 'filesOnly'=>true, 'fieldType'=>'radio','extensions'=>'jpg,gif,png')
);

?>