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


$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = str_replace(';{text_legend}',',facebookTeaser,addSocial;{text_legend}', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_news']['fields']['facebookTeaser'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news']['facebookTeaser'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'textarea',
	'eval'                    => array('style'=>'height:60px;', 'allowHtml'=>true)
);

$GLOBALS['TL_DCA']['tl_news']['fields']['addSocial'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news']['addSocial'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'checkbox',
    'default' 				  => true
);

array_insert($GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'],0,array
(
	array('MbSocialize','socializeNews')
));

array_insert($GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'],0,array
(
	array('MbSocialize', 'deleteNews')
));

$GLOBALS['TL_DCA']['tl_news']['list']['sorting']['headerFields'][] ='socialNews';

$GLOBALS['TL_DCA']['tl_news']['list']['operations']['socialized'] = array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_news']['socialized'],
				'icon'                => 'system/modules/z_mb_socialize/html/socialize0.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('MbSocialize', 'iconSocializedNews')
			);



?>