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
 * @package    MB_Socialize
 * @license    LGPL
 * @filesource
 */
$GLOBALS['TL_CSS'][] = 'system/modules/z_mb_socialize/html/css/fixpos.css';
$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/z_mb_socialize/html/js/picker.js';
$GLOBALS['TL_DCA']['tl_mb_socialize'] = array(

	// Config
	'config' => array(
		'dataContainer' => 'Table',
		'enableVersioning' => true,
		'onload_callback' => array( array(
				'tl_mb_socialize',
				'checkState'
			)),
		'onsubmit_callback' => array( array(
				'tl_mb_socialize',
				'getUserSession'
			))
	),

	// List
	'list' => array(
		'sorting' => array(
			'mode' => 1,
			'fields' => array('name'),
			'flag' => 1
		),
		'label' => array('fields' => array(
				'name',
				'appid',
				'secret',
				'targetid',
				'authorid'
			)),
		'global_operations' => array('all' => array(
				'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href' => 'act=select',
				'class' => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)),
		'operations' => array(

			'connected' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['connected'],
				'icon' => 'system/modules/z_mb_socialize/html/connected0.gif',
				'attributes' => 'onclick="Backend.getScrollOffset();"',
				'button_callback' => array(
					'MbSocialize',
					'iconConnected'
				)
			),
			'edit' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['edit'],
				'href' => 'act=edit',
				'icon' => 'edit.gif'
			),
			'copy' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['copy'],
				'href' => 'act=copy',
				'icon' => 'copy.gif'
			),
			'delete' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['delete'],
				'href' => 'act=delete',
				'icon' => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['show'],
				'href' => 'act=show',
				'icon' => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__' => array(''),
		'default' => '{mb_socialize_legend},name,appid,secret,permissions;{mb_socialize_user_legend},targeturl,authorurl'
	),

	// Subpalettes
	'subpalettes' => array(),

	// Fields
	'fields' => array(
		'name' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['name'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array('mandatory' => true)
		),

		'appid' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['appid'],
			'exclude' => true,
			'inputType' => 'text',
			'wizard' => array( array(
					'MbSocialize',
					'getWizardApp'
				)),
			'eval' => array(
				'tl_class' => 'w50 wizard apppos',
				'mandatory' => true
			)
		),

		'secret' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['secret'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array(
				'tl_class' => 'w50',
				'mandatory' => true
			)
		),

		'targeturl' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['targeturl'],
			'exclude' => true,
			'inputType' => 'text',
			'wizard' => array( array(
					'MbSocialize',
					'getWizardTarget'
				)),
			'save_callback' => array( array(
					'MbSocialize',
					'getFurl'
				)),
			'eval' => array(
				'tl_class' => 'w50 wizard apppos',
				'mandatory' => true
			)
		),
		'authorurl' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['authorurl'],
			'exclude' => true,
			'inputType' => 'text',
			'wizard' => array( array(
					'MbSocialize',
					'getWizardAuthor'
				)),
			'save_callback' => array( array(
					'MbSocialize',
					'getFurl'
				)),
			'eval' => array(
				'tl_class' => 'w50 wizard apppos',
				'mandatory' => true
			)
		),
		'permissions' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_mb_socialize']['permissions'],
			'exclude' => true,
			'inputType' => 'select',
			'options' => array(
				'publish_stream',
				'create_event'
			),
			'default' => 'publish_stream',
			'eval' => array(
				'mandatory' => true,
				'multiple' => true
			)
		)
	)
);

/**
 * Class tl_mb_socialize
 *
 * PHP version 5
 * @copyright  mediabakery 2011
 * @author     Sebastian Tilch <http://mediabakery.de>
 * @package    MB_Socialize
 * @license    LGPL
 * @filesource
 */

class tl_mb_socialize extends System
{

	public function checkState()
	{

		$this->Import('Database');

		if(strlen($this->Input->get('state')))
		{
			
			$objFacebook = $this->Database->prepare("SELECT id,name,appid,secret,usersession,(SELECT fid FROM tl_mb_socialize_facebookid WHERE furl=targeturl LIMIT 0,1) AS targetid,(SELECT fid FROM tl_mb_socialize_facebookid WHERE furl=authorurl LIMIT 0,1) AS authorid FROM tl_mb_socialize WHERE id=?")->limit(1)->execute($this->Input->get('id'));
			

			
			try
			{
				require_once '../system/modules/z_mb_socialize/facebook-php-sdk/src/facebook.php';
				$facebook = new Facebook( array(
					'appId' => $objFacebook->appid,
					'secret' => $objFacebook->secret,
					'cockie' => true
				));

				$accesstoken = $facebook->getAccessToken();
				$authorname = $facebook->api('/me');
				$authorname = $authorname['name'];

				$accounts = $facebook->api('/me/accounts');

				foreach($accounts['data'] as $account)
				{
					if($account['id'] == $objFacebook->authorid)
					{
						$accesstoken = $account['access_token'];
						$authorname = $account['name'];
						break;
					}
				}

				$this->Database->prepare("UPDATE tl_mb_socialize %s WHERE id=?")->set(array(
					'tstamp' => time(),
					'usersession' => $accesstoken
				))->execute($this->Input->get('id'));
				$this->log($authorname . ' AccessToken set for MB Socialize Item ' . $objFacebook->id . ' (' . $objFacebook->name . ')', 'tl_mb_socialize, checkState', TL_GENERAL);

			}
			catch(exception $e)
			{
				$this->log($e, 'tl_mb_socialize, checkState', TL_ERROR);
			}

			$this->redirect($this->Environment->url . $this->Environment->scriptName . '?do=mb_socialize', 301);
		}
	}

	public function getUserSession(DataContainer $dc)
	{

		try
		{
			require_once '../system/modules/z_mb_socialize/facebook-php-sdk/src/facebook.php';
			$facebook = new Facebook( array(
				'appId' => $dc->activeRecord->appid,
				'secret' => $dc->activeRecord->secret
			));
			$scope = implode(',', deserialize($dc->activeRecord->permissions)) . ',manage_pages,offline_access';
			$url = $facebook->getLoginUrl(array(
				'scope' => $scope,
				'redirect_uri' => $this->Environment->url . $this->Environment->requestUri
			));

			$this->redirect($url, 301);

		}
		catch(exception $e)
		{
			$this->log($e, 'tl_mb_socialize, getUserSession', TL_ERROR);
		}
	}

}
?>