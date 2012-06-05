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

/**
 * Class MbSocialize
 *
 * PHP version 5
 * @copyright  mediabakery 2011
 * @author     Sebastian Tilch <http://mediabakery.de>
 * @package    MB_Socialize
 * @license    LGPL
 * @filesource
 */

class MbSocialize extends Backend
{

	private $defaultIconTag;

	public function __construct()
	{
		parent::__construct();
		$this->defaultIconTag = $this->generateImage('system/modules/z_mb_socialize/html/error.gif', '?');
	}

	public function checkQueue()
	{
		$objQueue = $this->Database->prepare("SELECT pid FROM tl_mb_socialize_news WHERE facebookid=''")->execute();

		while($objQueue->next())
		{

			$objNews = $this->Database->prepare("SELECT * FROM tl_news WHERE id=?")->limit(1)->execute($objQueue->pid);
			if($objNews->published == '1' && $objNews->start <= time())
				$this->transmitNews($objNews);
		}

	}

	// NEWS START
	public function socializeNews(DataContainer $dc)
	{

		$this->import('Database');
		if(!$dc->activeRecord)
			return;

		if($dc->activeRecord->addSocial == '1' && $dc->activeRecord->published == '1')
		{

			$parentArchive = $this->Database->prepare("SELECT socialNews FROM tl_news_archive WHERE id=?")->limit(1)->execute($dc->activeRecord->pid);
			if($parentArchive->socialNews == 1)
			{

				$socialNewsItem = $this->Database->prepare("SELECT id FROM tl_mb_socialize_news WHERE pid=?")->execute($dc->activeRecord->id);

				if($socialNewsItem->numRows < 1)
				{

					$set = array(
						tstamp => time(),
						pid => $dc->activeRecord->id
					);

					$this->Database->prepare("INSERT INTO tl_mb_socialize_news %s")->set($set)->execute();
				}
			}
		}
		$this->checkQueue();
	}

	public function deleteNews(DataContainer $dc)
	{
		$this->Database->prepare("DELETE FROM tl_mb_socialize_news WHERE facebookid=?")->execute($dc->id);
		$this->log('Delete MB Socialize Item ' . $dc->id, 'MbSocialize, deleteNews', TL_GENERAL);
	}

	private function transmitNews(DB_Mysql_Result $objNews)
	{

		$objNewsArchive = $this->Database->prepare("SELECT socialNews,socialNewsService,facebookTeaserLength,defaultPic FROM tl_news_archive WHERE id=?")->limit(1)->execute($objNews->pid);

		if($objNewsArchive->socialNews == '1')
		{

			$objSocialize = $this->Database->prepare("SELECT appid, secret, usersession, (SELECT fid FROM tl_mb_socialize_facebookid WHERE furl=targeturl LIMIT 0,1) AS targetid FROM tl_mb_socialize WHERE id=?")->limit(1)->execute($objNewsArchive->socialNewsService);

			$message = $objNews->facebookTeaser;
			if($message == '')
				$message = $this->shortenText($objNews->teaser, $objNewsArchive->facebookTeaserLength);
			if($message == '')
				$message = $this->shortenText($objNews->text, $objNewsArchive->facebookTeaserLength);
			$message = $this->convertStringForFacebook($message);

			$newsurl = $this->getNewsUrl($objNews);

			$picture = $objNews->singleSRC;
			if($picture == '')
				$picture = $objNewsArchive->defaultPic;
			$pictureurl = $this->Environment->base . $picture;

			$name = $objNews->headline;
			$description = $objNews->subheadline;

			// FACEBOOK SDK
			try
			{

				if(TL_MODE == 'BE')
					require_once '../system/modules/z_mb_socialize/facebook-php-sdk/src/facebook.php';
				if(TL_MODE == 'FE')
					require_once 'system/modules/z_mb_socialize/facebook-php-sdk/src/facebook.php';

				$facebook = new Facebook( array(
					'appId' => $objSocialize->appid,
					'secret' => $objSocialize->secret,
					'cookie' => true
				));

				try
				{

					$postArray = array(
						'access_token' => $objSocialize->usersession,
						'message' => $message
					);
					if($newsurl != '')
					{
						$postArray['link'] = $newsurl;
						$postArray['picture'] = $pictureurl;
						$postArray['name'] = $name;
						$postArray['description'] = $description;
					}

					$publishStream = $facebook->api("/$objSocialize->targetid/feed", 'post', $postArray);

					$this->import('Request');
					$objCheck = new Request();
					$objCheck->send('https://graph.facebook.com/' . $publishStream['id'] . '?access_token=' . $objSocialize->usersession);

					if($objCheck->hasError() || $objCheck->response == 'false')
					{
						$this->log('undefined error while ' . $publishStream['id'] . 'transmiting news to facebook: ' . $message, 'MbSocialize, transmitNews', TL_ERROR);
					}
					else
					{

						$this->Database->prepare("UPDATE tl_mb_socialize_news %s WHERE pid=?")->set(array(
							'tstamp' => time(),
							'facebookid' => $publishStream['id']
						))->execute($objNews->id);

						$this->log('News transmitted to Facebook: ' . $message, 'MbSocialize, transmitNews', TL_GENERAL);
					}
				}
				catch (FacebookApiException $e)
				{
					$this->log($e . '@News ID ' . $objNews->id, 'MbSocialize, transmitNews', TL_ERROR);
				}

			}
			catch(exception $e)
			{
				$this->log($e, 'MbSocialize, transmitNews', TL_ERROR);
			}

		}
	}

	// NEWS END

	private function shortenText($text, $limit)
	{
		if($limit == 0)
			return $text;

		$array = explode(" ", $text, $limit + 1);

		if(count($array) > $limit)
		{
			unset($array[$limit]);
			return implode(" ", $array) . " …";
		}
		return $text;
	}

	private function convertStringForFacebook($val)
	{
		return str_replace('[nbsp]', ' ', strip_tags($val));
	}

	public function pageidfromurl($arrFragments)
	{
		$this->runcron();
		return array_unique($arrFragments);
	}

	private function runcron()
	{

		$minTime = 300;

		try
		{
			$fileCron = new File('system/modules/z_mb_socialize/data/cronjob.txt');
			// if ($fileCron->hasError()) throw new Exception($fileCron->error);
			$lastCron = $fileCron->getContent();

			if(time() - $lastCron >= $minTime)
			{
				$fileCron->write(time());
				$this->checkQueue();
			}
		}
		catch(exception $e)
		{
			$this->log($e, 'MbSocialize, runcron', TL_ERROR);
		}
	}

	/**
	 * Return the "socialize/unsocialize element" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function iconSocializedNews($row, $href, $label, $title, $icon, $attributes)
	{

		$this->Import('Database');

		$objSocial = $this->Database->prepare("SELECT facebookid FROM tl_mb_socialize_news WHERE pid=?")->limit(1)->execute($row['id']);

		if($objSocial->facebookid != '')
		{
			$icon = 'system/modules/z_mb_socialize/html/socialize1.gif';
		}

		return $this->generateImage($icon, $label);
	}

	public function getNewsUrl(Database_Result $objNewsItem)
	{

		switch($objNewsItem->source)
		{
			// Link to external page
			case 'external' :
				$this->import('String');

				if(substr($objNewsItem->url, 0, 7) == 'mailto:')
				{
					return $this->String->encodeEmail($objNewsItem->url);
				}
				else
				{
					return $this->Environment->host . '/' .ampersand($objNewsItem->url);
				}
				break;

			// Link to an internal page
			case 'internal' :
				$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")->limit(1)->execute($objNewsItem->jumpTo);

				if($objPage->numRows)
				{
					return $this->Environment->host . '/' .ampersand($this->generateFrontendUrl($objPage->row()));
				}
				break;

			// Link to an article
			case 'article' :
				$objPage = $this->Database->prepare("SELECT a.id AS aId, a.alias AS aAlias, a.title, p.id, p.alias FROM tl_article a, tl_page p WHERE a.pid=p.id AND a.id=?")->limit(1)->execute($objNewsItem->articleId);

				if($objPage->numRows)
				{
					return $this->Environment->host . '/' .ampersand($this->generateFrontendUrl($objPage->row(), '/articles/' . ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $objPage->aAlias != '') ? $objPage->aAlias : $objPage->aId)));
				}
				break;
		}

		// default
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=(SELECT jumpTo FROM tl_news_archive WHERE id=?)")->limit(1)->execute($objNewsItem->pid);

		if($objPage->numRows)
		{
			return $this->Environment->host . '/' .ampersand($this->generateFrontendUrl($objPage->row()));
		}

		return $this->Environment->host;
	}

	public function getWizardTarget($dc)
	{
		if(strlen($dc->activeRecord->targeturl))
		{
			$iconTag = $this->checkFacebookId($dc->activeRecord->targeturl);
			if($iconTag)
				return $iconTag;
		}
		return $this->defaultIconTag;
	}

	public function getWizardAuthor($dc)
	{
		if(strlen($dc->activeRecord->authorurl))
		{
			$iconTag = $this->checkFacebookId($dc->activeRecord->authorurl);
			if($iconTag)
				return $iconTag;
		}
		return $this->defaultIconTag;
	}

	public function getWizardApp($dc)
	{
		if(strlen($dc->activeRecord->appid))
		{
			$iconTag = $this->checkFacebookId($dc->activeRecord->appid);
			if($iconTag)
				return $iconTag;
		}
		return $this->defaultIconTag;
	}

	public function getFurl($strUrl, DataContainer $dc = null)
	{
		if(!strlen($strUrl))
			return '';
		$strUrl = trim(html_entity_decode($strUrl));
		if(preg_match('/\?id=([0-9]*)/', $strUrl))
		{
			preg_match('/\?id=([0-9]*)/', $strUrl, $arrId);
			$strUrl = $arrId[1];
		}
		else
		{
			$arrUrlParts = explode('?', $strUrl);
			$strUrl = $arrUrlParts[0];

			if(preg_match('/facebook.com\/(.*)/', $strUrl))
			{
				$arrUrlFragments = explode('/', $strUrl);
				$strUrl = end($arrUrlFragments);
			}
		}
		return str_replace('.', '', $strUrl);

	}

	private function checkFacebookId($strUrl)
	{

		if(strlen($strUrl) > 1)
		{
			$strUrl = $this->getFurl($strUrl);

			$objFid = $this->Database->prepare("SELECT fid, fpicurl FROM tl_mb_socialize_facebookid WHERE furl=?")->limit(1)->execute($strUrl);
			if($objFid->numRows == 1)
			{
				return '<img src="' . $objFid->fpicurl . '" alt="' . $objFid->fid . '"/>';
			}

			$this->import('Request');
			$objRequest = new Request();

			$objRequest->send('https://graph.facebook.com/' . $strUrl);

			if($objRequest->hasError())
			{
				$this->log('Error while requesting graph.facebook: ' . $objRequest->error, 'MbSocializeHelper checkFacebookId', TL_ERROR);
				return false;
			};
			$arrResult = json_decode($objRequest->response, true);

			if(is_array($arrResult['error']) || !$arrResult)
			{
				$this->log('Error while responding graph.facebook: ' . $arrResult['error']['message'], 'MbSocializeHelper checkFacebookId', TL_ERROR);
				return false;
			}

			$arrSet = array(
				'fid' => $arrResult['id'],
				'furl' => $strUrl
			);

			if(strlen($arrResult['picture']))
			{
				$arrSet['fpicurl'] = $arrResult['picture'];
			}
			else
			if(strlen($arrResult['icon_url']))
			{
				$arrSet['fpicurl'] = $arrResult['icon_url'];
			}
			else
			{
				$ch = curl_init('http://graph.facebook.com/' . $arrResult['id'] . '/picture');
				curl_exec($ch);
				if(!curl_errno($ch))
				{
					$arrSet['fpicurl'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				}
				curl_close($ch);
			}

			$objFUrl = $this->Database->prepare("SELECT id FROM tl_mb_socialize_facebookid WHERE furl=?")->limit(1)->execute($arrSet['furl']);
			if($objFUrl->numRows == 1)
			{
				$this->Database->prepare("UPDATE tl_mb_socialize_facebookid %s WHERE id=?")->set($arrSet)->execute($objFUrl->id);
			}
			else
			{
				$this->Database->prepare("INSERT INTO tl_mb_socialize_facebookid %s")->set($arrSet)->execute();
			}
			return '<img src="' . $arrSet['fpicurl'] . '" alt="' . $objFid->fid . '"/>';
		}
		return false;
	}

	public function ajaxGetIconTag($strAction)
	{
		if($strAction == 'mbSocializeGetIcon')
		{
			$this->import('Input');
			$arrResponse = array();
			$iconTag = $this->checkFacebookId($this->Input->post('url'));

			if(!$iconTag)
				$iconTag = $this->defaultIconTag;
			echo json_encode(array('content' => $iconTag));
			exit ;
		}
	}

	public function iconConnected($row, $href, $label, $title, $icon, $attributes)
	{
		if(strlen($row['usersession']))
			return $this->generateImage('system/modules/z_mb_socialize/html/connected1.gif', 'CONNECTION');

		return $this->generateImage('system/modules/z_mb_socialize/html/connected0.gif', 'NO CONNECTION');

	}

	public function delOrphans()
	{
		$objFUrls = $this->Database->query("SELECT appid, targeturl, authorurl FROM tl_mb_socialize");

		$arrFUrls = array();
		while($objFUrls->next())
		{
			$arrFUrls[$objFUrls->appid] = $objFUrls->appid;
			$arrFUrls[$objFUrls->targeturl] = $objFUrls->targeturl;
			$arrFUrls[$objFUrls->authorurl] = $objFUrls->authorurl;
		}

		$objOrphans = $this->Database->query("DELETE FROM tl_mb_socialize_facebookid WHERE furl NOT IN (" . implode(',', $arrFUrls) . ")");
	}

}
?>