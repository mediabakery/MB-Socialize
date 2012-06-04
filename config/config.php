<?php
if(!defined('TL_ROOT'))
	die('You cannot access this file directly!');

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
 * @package    MB
 * @license    LGPL
 * @filesource
 */

/**
 * -------------------------------------------------------------------------
 * BACK END MODULE
 * -------------------------------------------------------------------------
 */

$GLOBALS['BE_MOD']['system']['mb_socialize'] = array(
	'tables' => array('tl_mb_socialize'),
	'icon' => 'system/modules/z_mb_socialize/html/icon.gif'
);

/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */

$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array(
	'MbSocialize',
	'pageidfromurl'
);

$GLOBALS['TL_HOOKS']['executePreActions'][] = array(
	'MbSocialize',
	'ajaxGetIconTag'
);

/**
 * -------------------------------------------------------------------------
 * CRON
 * -------------------------------------------------------------------------
 */

$GLOBALS['TL_CRON']['hourly'][] = array(
	'MbSocialize',
	'runcron'
);

$GLOBALS['TL_CRON']['weekly'][] = array(
	'MbSocialize',
	'delOrphans'
);
?>