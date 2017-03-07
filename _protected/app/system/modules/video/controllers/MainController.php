<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Video / Controller
 */
namespace PH7;

use
PH7\Framework\Security\Ban\Ban,
PH7\Framework\Navigation\Page,
PH7\Framework\Cache\Cache,
PH7\Framework\Url\Header,
PH7\Framework\Mvc\Router\Uri;

class MainController extends Controller
{
    const ALBUMS_PER_PAGE = 14;
    const VIDEOS_PER_PAGE = 10;

    private $oVideoModel, $oPage, $sUsername, $sUsernameLink, $iProfileId, $sTitle, $iTotalVideos;

    public function __construct()
    {
        parent::__construct();
        $this->oVideoModel = new VideoModel;
        $this->oPage = new Page;

        $this->sUsername = $this->httpRequest->get('username');

        $oUser = new UserCore;
        $this->sUsernameLink = $oUser->getProfileLink($this->sUsername);
        $this->view->oUser = $oUser;
        unset($oUser);

        $this->view->member_id = $this->session->get('member_id');
        $this->iProfileId = (new UserCoreModel)->getId(null, $this->sUsername);

        // Predefined meta_keywords tags
        $this->view->meta_keywords = t('video,videos,free,free videos,music,online,watch,dating,video dating,social,community,social network,people video,flirt');
    }

    public function index()
    {
        $this->albums();
    }

    public function addAlbum()
    {
        $this->view->page_title = $this->view->h2_title = t('Add a new Album');
        $this->output();
    }

    public function addVideo()
    {
        // Add JS file to choose the type of video (regular | embed)
        $this->design->addJs(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_JS, 'common.js');

        $this->view->page_title = $this->view->h2_title = t('Add a new Video');
        $this->output();
    }

    public function editAlbum()
    {
        $this->view->page_title = $this->view->h2_title = t('Edit Album');
        $this->output();
    }

    public function editVideo()
    {
        $this->view->page_title = $this->view->h2_title = t('Edit Video');
        $this->output();
    }

    public function albums()
    {
        $iProfileId = ($this->httpRequest->getExists('username')) ? $this->iProfileId : null;
        $this->view->total_pages = $this->oPage->getTotalPages(
            $this->oVideoModel->totalAlbums($iProfileId), self::ALBUMS_PER_PAGE
        );
        $this->view->current_page = $this->oPage->getCurrentPage();
        $oAlbums = $this->oVideoModel->album($iProfileId, null, 1, $this->oPage->getFirstItem(), $this->oPage->getNbItemsPerPage());

        if (empty($oAlbums))
        {
            $this->sTitle = t('No video albums found.');
            $this->_notFound(false); // Because the Ajax blocks profile, we cannot put HTTP error code 404, so the attribute is FALSE
        }
        else
        {
            // We can include HTML tags in the title since the template will erase them before displaying
            $this->sTitle = (!empty($iProfileId)) ? t("The <a href='%0%'>%1%</a>'s albums", $this->sUsernameLink, $this->str->upperFirst($this->sUsername)) : t('Video Gallery Community');
            $this->view->page_title = $this->view->h2_title = $this->sTitle;
            $this->view->meta_description = t("%0%'s Albums | Video Albums of the Dating Social Community - %site_name%", $this->str->upperFirst($this->sUsername));
            $this->view->albums = $oAlbums;
        }
        if (empty($iProfileId))
            $this->manualTplInclude('index.tpl');

        $this->output();
    }

    public function album()
    {
        // Adding the JS Video Player and "video_duration" CSS class
        $this->design->addJs(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_JS, 'Video.js');
        $this->design->addCss(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_CSS, 'common.css');

        $this->view->total_pages = $this->oPage->getTotalPages(
            $this->oVideoModel->totalVideos($this->iProfileId), self::ALBUMS_PER_PAGE
        );
        $this->view->current_page = $this->oPage->getCurrentPage();
        $oAlbum = $this->oVideoModel->video($this->iProfileId, $this->httpRequest->get('album_id', 'int'), null, 1, $this->oPage->getFirstItem(), $this->oPage->getNbItemsPerPage());

        if (empty($oAlbum))
        {
            $this->sTitle = t('Album not found or is still in pending approval.');
            $this->_notFound();
        }
        else
        {
            $this->sTitle = t("<a href='%0%'>%1%</a>'s video album", $this->sUsernameLink, $this->str->upperFirst($this->sUsername));
            $this->view->page_title = $this->sTitle; // We can include HTML tags in the title since the template will erase them before displaying
            $this->view->h2_title = $this->sTitle;
            $this->view->meta_description = t('Browse Videos From %0% | Video Album Social Community - %site_name%', $this->str->upperFirst($this->sUsername));
            $this->view->album = $oAlbum;

            // Set Video Album Statistics since it needs the foreach loop and it is unnecessary to do both, we have placed in the file album.tpl
        }

        $this->output();
    }

    public function video()
    {
        // Adding the JS Video Player file.
        $this->design->addJs(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_JS, 'Video.js');

        $oVideo = $this->oVideoModel->video($this->iProfileId, $this->httpRequest->get('album_id', 'int'), $this->httpRequest->get('video_id', 'int'), 1, 0, 1);

        if (empty($oVideo))
        {
            $this->sTitle = t('Video not found or is still in pending approval.');
            $this->_notFound();
        }
        else
        {
            $this->sTitle = t("Watch <a href='%0%'>%1%</a>'s video", $this->sUsernameLink, $this->str->upperFirst($this->sUsername));

            $sTitle = Ban::filterWord($oVideo->title, false);
            $this->view->page_title = t("%0%'s video, %1%", $oVideo->firstName, $sTitle);
            $this->view->meta_description = t("%0%'s video, %1%, %2%", $oVideo->firstName, $sTitle, substr(Ban::filterWord($oVideo->description, false), 0, 100));
            $this->view->meta_keywords = t('video,movie,videos,video sharing,music,gallery,%0%,%1%,%2%', str_replace(' ', ',', $sTitle), $oVideo->firstName, $oVideo->username);
            $this->view->h1_title = $this->sTitle;
            $this->view->video = $oVideo;

            //Set Video Statistics
            Framework\Analytics\Statistic::setView($oVideo->videoId, 'Videos');
        }

        $this->output();
    }

    public function deleteVideo()
    {
        $iVideoId = $this->httpRequest->post('video_id', 'int');
        CommentCoreModel::deleteRecipient($iVideoId, 'Video');
        $bVideo = $this->oVideoModel->deleteVideo($this->session->get('member_id'), $this->httpRequest->post('album_id', 'int'), $iVideoId);
        (new Video)->deleteVideo($this->httpRequest->post('album_id'), $this->session->get('member_username'), $this->httpRequest->post('video_link'));

        /* Clean VideoModel Cache */
        (new Cache)->start(VideoModel::CACHE_GROUP, null, null)->clear();

        Header::redirect(Uri::get('video', 'main', 'album', $this->session->get('member_username') . ',' . $this->httpRequest->post('album_title') . ',' . $this->httpRequest->post('album_id')), t('Your video has been deleted!'));
    }

    public function deleteAlbum()
    {
        $this->oVideoModel->deleteVideo($this->session->get('member_id'), $this->httpRequest->post('album_id', 'int'));
        $this->oVideoModel->deleteAlbum($this->session->get('member_id'), $this->httpRequest->post('album_id', 'int'));
        $sDir = PH7_PATH_PUBLIC_DATA_SYS_MOD . 'video/file/' . $this->session->get('member_username') . PH7_DS . $this->httpRequest->post('album_id') . PH7_DS;
        $this->file->deleteDir($sDir);

        /* Clean VideoModel Cache */
        (new Cache)->start(VideoModel::CACHE_GROUP, null, null)->clear();
        Header::redirect(Uri::get('video', 'main', 'albums'), t('Your album has been deleted!'));
    }

    public function search()
    {
        $this->view->page_title = $this->view->h2_title = t('Video Search - Looking for a video');
        $this->output();
    }

    public function result()
    {
        $this->iTotalVideos = $this->oVideoModel->search(
            $this->httpRequest->get('looking'),
            true,
            $this->httpRequest->get('order'),
            $this->httpRequest->get('sort'),
            null,
            null
        );
        $this->view->total_pages = $this->oPage->getTotalPages(
            $this->iTotalVideos, self::VIDEOS_PER_PAGE
        );
        $this->view->current_page = $this->oPage->getCurrentPage();

        $oSearch = $this->oVideoModel->search(
            $this->httpRequest->get('looking'),
            false,
            $this->httpRequest->get('order'),
            $this->httpRequest->get('sort'),
            $this->oPage->getFirstItem(),
            $this->oPage->getNbItemsPerPage()
        );

        if (empty($oSearch))
        {
            $this->sTitle = t('Sorry, Your search returned no results!');
            $this->_notFound();
        }
        else
        {
            $this->sTitle = t('Dating Social Video - Your search returned');
            $this->view->page_title = $this->view->h2_title = $this->sTitle;
            $this->view->h3_title = nt('%n% video found!', '%n% videos found!', $this->iTotalVideos);
            $this->view->meta_description = t('Search - %site_name% is a Dating Social Video Community!');
            $this->view->meta_keywords = t('search,video,dating,social network,community,music,movie,news,video sharing');
            $this->view->album = $oSearch;
        }

        $this->manualTplInclude('album.tpl');
        $this->output();
    }

    /**
     * Set a Not Found Error Message with HTTP 404 Code Status.
     *
     * @param boolean $b404Status For the Ajax blocks profile, we can not put HTTP error code 404, so the attribute must be set to "false". Default: TRUE
     * @return void
     */
    private function _notFound($b404Status = true)
    {
        if ($b404Status === true) {
            Framework\Http\Http::setHeadersByCode(404);
        }

        $sErrMsg = ($b404Status === true) ? '<br />' . t('Please return to <a href="%1%">the previous page</a> or <a href="%1%">add a new video</a> in this album.', 'javascript:history.back();', Uri::get('video', 'main', 'addvideo', $this->httpRequest->get('album_id'))) : '';

        $this->view->page_title = $this->view->h2_title = $this->sTitle;
        $this->view->error = $this->sTitle . $sErrMsg;
    }
}
