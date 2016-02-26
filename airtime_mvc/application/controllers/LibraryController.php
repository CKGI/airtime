<?php

require_once 'formatters/LengthFormatter.php';
require_once 'formatters/SamplerateFormatter.php';
require_once 'formatters/BitrateFormatter.php';

class LibraryController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('contents-feed', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('duplicate', 'json')
                    ->addActionContext('delete-group', 'json')
                    ->addActionContext('context-menu', 'json')
                    ->addActionContext('get-file-metadata', 'html')
                    ->addActionContext('upload-file-soundcloud', 'json')
                    ->addActionContext('get-upload-to-soundcloud-status', 'json')
                    ->addActionContext('set-num-entries', 'json')
                    ->addActionContext('edit-file-md', 'json')
                    ->addActionContext('create-compendium', 'json')
                    ->addActionContext('handle-compendium', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        $CC_CONFIG = Config::getConfig();

        $request = $this->getRequest();
        $baseUrl = Application_Common_OsPath::getBaseDir();

        $this->view->headScript()->appendFile($baseUrl.'js/blockui/jquery.blockUI.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/contextmenu/jquery.contextMenu.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/js/jquery.dataTables.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.pluginAPI.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.fnSetFilteringDelay.js?'.$CC_CONFIG['airtime_version'],'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.ColVis.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.ColReorder.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.FixedColumns.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/datatables/plugin/dataTables.columnFilter.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');

        $this->view->headScript()->appendFile($baseUrl.'js/airtime/buttons/buttons.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/airtime/utilities/utilities.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/airtime/library/library.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/airtime/library/events/library_playlistbuilder.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');

        $this->view->headLink()->appendStylesheet($baseUrl.'css/media_library.css?'.$CC_CONFIG['airtime_version']);
        $this->view->headLink()->appendStylesheet($baseUrl.'css/jquery.contextMenu.css?'.$CC_CONFIG['airtime_version']);
        $this->view->headLink()->appendStylesheet($baseUrl.'css/datatables/css/ColVis.css?'.$CC_CONFIG['airtime_version']);
        $this->view->headLink()->appendStylesheet($baseUrl.'css/datatables/css/ColReorder.css?'.$CC_CONFIG['airtime_version']);
        $this->view->headLink()->appendStylesheet($baseUrl.'css/waveform.css?'.$CC_CONFIG['airtime_version']);

        $this->view->headScript()->appendFile($baseUrl.'js/airtime/library/spl.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/airtime/playlist/smart_blockbuilder.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');

        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/observer/observer.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/config.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/curves.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/fades.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/local_storage.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/controls.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/playout.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/track_render.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/track.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/time_scale.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');
        $this->view->headScript()->appendFile($baseUrl.'js/waveformplaylist/playlist.js?'.$CC_CONFIG['airtime_version'], 'text/javascript');

        //arbitrary attributes need to be allowed to set an id for the templates.
        $this->view->headScript()->setAllowArbitraryAttributes(true);
        //$this->view->headScript()->appendScript(file_get_contents(APPLICATION_PATH.'/../public/js/waveformplaylist/templates/bottombar.tpl'),
        //		'text/template', array('id' => 'tpl_playlist_cues', 'noescape' => true));

        $this->view->headLink()->appendStylesheet($baseUrl.'css/playlist_builder.css?'.$CC_CONFIG['airtime_version']);

        try {

            $obj_sess = new Zend_Session_Namespace(UI_PLAYLISTCONTROLLER_OBJ_SESSNAME);
            if (isset($obj_sess->id)) {
                $objInfo = Application_Model_Library::getObjInfo($obj_sess->type);
                Logging::info($obj_sess->id);
                Logging::info($obj_sess->type);

                $objInfo     = Application_Model_Library::getObjInfo($obj_sess->type);
                $obj         = new $objInfo['className']($obj_sess->id);
                $userInfo    = Zend_Auth::getInstance()->getStorage()->read();
                $user        = new Application_Model_User($userInfo->id);
                $isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));

                if ($isAdminOrPM || $obj->getCreatorId() == $userInfo->id) {
                    $this->view->obj = $obj;
                    if ($obj_sess->type == "block") {
                        $form = new Application_Form_SmartBlockCriteria();
                        $form->startForm($obj_sess->id);
                        $this->view->form = $form;
                    }
                }

                $formatter = new LengthFormatter($obj->getLength());
                $this->view->length = $formatter->format();
                $this->view->type = $obj_sess->type;
            }

            //get user settings and determine if we need to hide
            // or show the playlist editor
            $showPlaylist = false;
            $data = Application_Model_Preference::getLibraryScreenSettings();
            if (!is_null($data)) {
                if ($data["playlist"] == "true") {
                    $showPlaylist = true;
                }
            }
            $this->view->showPlaylist = $showPlaylist;
        } catch (PlaylistNotFoundException $e) {
            $this->playlistNotFound($obj_sess->type);
        } catch (Exception $e) {
            $this->playlistNotFound($obj_sess->type);
            Logging::info($e->getMessage());
            //$this->playlistUnknownError($e);
        }
    }

    protected function playlistNotFound($p_type)
    {
        $this->view->error = sprintf(_("%s not found"), $p_type);

        Logging::info("$p_type not found");
        Application_Model_Library::changePlaylist(null, $p_type);
        $this->createFullResponse(null);
    }

    protected function playlistUnknownError($e)
    {
        $this->view->error = _("Something went wrong.");
        Logging::info($e->getMessage());
    }

    protected function createFullResponse($obj = null, $isJson = false)
    {
        $isBlock = false;
        $viewPath = 'playlist/playlist.phtml';
        if ($obj instanceof Application_Model_Block) {
            $isBlock = true;
            $viewPath = 'playlist/smart-block.phtml';
        }

        if (isset($obj)) {
            $formatter = new LengthFormatter($obj->getLength());
            $this->view->length = $formatter->format();

            if ($isBlock) {
                $form = new Application_Form_SmartBlockCriteria();
                $form->removeDecorator('DtDdWrapper');
                $form->startForm($obj->getId());

                $this->view->form = $form;
                $this->view->obj = $obj;
                $this->view->id = $obj->getId();
                if ($isJson) {
                    return $this->view->render($viewPath);
                } else {
                    $this->view->html = $this->view->render($viewPath);
                }
            } else {
                $this->view->obj = $obj;
                $this->view->id = $obj->getId();
                $this->view->html = $this->view->render($viewPath);
                unset($this->view->obj);
            }
        } else {
            $this->view->html = $this->view->render($viewPath);
        }
    }

    public function contextMenuAction()
    {
        $baseUrl = Application_Common_OsPath::getBaseDir();
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');
        //playlist||timeline
        $screen = $this->_getParam('screen');

        $menu = array();

        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $user = new Application_Model_User($userInfo->id);

        //Open a jPlayer window and play the audio clip.
        $menu["play"] = array("name"=> _("Preview"), "icon" => "play", "disabled" => false);

        $isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));

        $obj_sess = new Zend_Session_Namespace(UI_PLAYLISTCONTROLLER_OBJ_SESSNAME);

        if ($type === "audioclip") {

            $file = Application_Model_StoredFile::RecallById($id);

            $menu["play"]["mime"] = $file->getPropelOrm()->getDbMime();

            if (isset($obj_sess->id) && $screen == "playlist") {
                // if the user is not admin or pm, check the creator and see if this person owns the playlist or Block
                if ($obj_sess->type == 'playlist') {
                    $obj = new Application_Model_Playlist($obj_sess->id);
                } elseif ($obj_sess->type == 'block') {
                    $obj = new Application_Model_Block($obj_sess->id);
                }
                if ($isAdminOrPM || $obj->getCreatorId() == $user->getId()) {
                    if ($obj_sess->type === "playlist") {
                        $menu["pl_add"] = array("name"=> _("Add to Playlist"), "icon" => "add-playlist", "icon" => "copy");
                    } elseif ($obj_sess->type === "block" && $obj->isStatic()) {
                        $menu["pl_add"] = array("name"=> _("Add to Smart Block"), "icon" => "add-playlist", "icon" => "copy");
                    }
                }
            }
            if ($isAdminOrPM || $file->getFileOwnerId() == $user->getId()) {
                $menu["del"] = array("name"=> _("Delete"), "icon" => "delete", "url" => $baseUrl."library/delete");
                $menu["edit"] = array("name"=> _("Edit Metadata"), "icon" => "edit", "url" => $baseUrl."library/edit-file-md/id/{$id}");
                $menu["compendium"] = array("name"=> _("Create Compendium"), "icon" => "compendium", "url" => $baseUrl."library/create-compendium/id/{$id}");
            }

            $url = $file->getRelativeFileUrl($baseUrl).'/download/true';
            $menu["download"] = array("name" => _("Download"), "icon" => "download", "url" => $url);
        } elseif ($type === "playlist" || $type === "block") {
            if ($type === 'playlist') {
                $obj = new Application_Model_Playlist($id);
                $menu["duplicate"] = array("name" => _("Duplicate Playlist"), "icon" => "edit", "url" => $baseUrl."library/duplicate");
            } elseif ($type === 'block') {
                $obj = new Application_Model_Block($id);
                if (!$obj->isStatic()) {
                    unset($menu["play"]);
                }
                if (($isAdminOrPM || $obj->getCreatorId() == $user->getId()) && $screen == "playlist") {
                    if ($obj_sess->type === "playlist") {
                        $menu["pl_add"] = array("name"=> _("Add to Playlist"), "icon" => "add-playlist", "icon" => "copy");
                    }
                }
            }

            if ($obj_sess->id !== $id && $screen == "playlist") {
                if ($isAdminOrPM || $obj->getCreatorId() == $user->getId()) {
                    $menu["edit"] = array("name"=> _("Edit"), "icon" => "edit");
                }
            }

            if ($isAdminOrPM || $obj->getCreatorId() == $user->getId()) {
                $menu["del"] = array("name"=> _("Delete"), "icon" => "delete", "url" => $baseUrl."library/delete");
            }
        } elseif ($type == "stream") {
            $webstream = CcWebstreamQuery::create()->findPK($id);
            $obj = new Application_Model_Webstream($webstream);

            $menu["play"]["mime"] = $webstream->getDbMime();

            if (isset($obj_sess->id) && $screen == "playlist") {
                if ($isAdminOrPM || $obj->getCreatorId() == $user->getId()) {
                    if ($obj_sess->type === "playlist") {
                        $menu["pl_add"] = array("name"=> _("Add to Playlist"), "icon" => "add-playlist", "icon" => "copy");
                    }
                }
            }
            if ($isAdminOrPM || $obj->getCreatorId() == $user->getId()) {
                if ($screen == "playlist") {
                    $menu["edit"] = array("name"=> _("Edit"), "icon" => "edit", "url" => $baseUrl."library/edit-file-md/id/{$id}");
                    $menu["compendium"] = array("name"=> _("Create Compendium"), "icon" => "compendium", "url" => $baseUrl."library/create-compendium/id/{$id}");
                }
                $menu["del"] = array("name"=> _("Delete"), "icon" => "delete", "url" => $baseUrl."library/delete");
            }
        }

        //SOUNDCLOUD MENU OPTIONS
        if ($type === "audioclip" && Application_Model_Preference::GetUploadToSoundcloudOption()) {

            //create a menu separator
            $menu["sep1"] = "-----------";

            //create a sub menu for Soundcloud actions.
            $menu["soundcloud"] = array("name" => _("Soundcloud"), "icon" => "soundcloud", "items" => array());

            $scid = $file->getSoundCloudId();

            if ($scid > 0) {
                $url = $file->getSoundCloudLinkToFile();
                $menu["soundcloud"]["items"]["view"] = array("name" => _("View on Soundcloud"), "icon" => "soundcloud", "url" => $url);
            }

            if (!is_null($scid)) {
                $text = _("Re-upload to SoundCloud");
            } else {
                $text = _("Upload to SoundCloud");
            }

            $menu["soundcloud"]["items"]["upload"] = array("name" => $text, "icon" => "soundcloud", "url" => $baseUrl."library/upload-file-soundcloud/id/{$id}");
        }

        if (empty($menu)) {
            $menu["noaction"] = array("name"=>_("No action available"));
        }

        $this->view->items = $menu;
    }

    public function deleteAction()
    {
        //array containing id and type of media to delete.
        $mediaItems = $this->_getParam('media', null);

        $user = Application_Model_User::getCurrentUser();
        //$isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));

        $files     = array();
        $playlists = array();
        $blocks    = array();
        $streams   = array();

        $message = null;
        $noPermissionMsg = _("You don't have permission to delete selected items.");

        foreach ($mediaItems as $media) {

            if ($media["type"] === "audioclip") {
                $files[] = intval($media["id"]);
            } elseif ($media["type"] === "playlist") {
                $playlists[] = intval($media["id"]);
            } elseif ($media["type"] === "block") {
                $blocks[] = intval($media["id"]);
            } elseif ($media["type"] === "stream") {
                $streams[] = intval($media["id"]);
            }
        }

        try {
            Application_Model_Playlist::deletePlaylists($playlists, $user->getId());
        } catch (PlaylistNoPermissionException $e) {
            $message = $noPermissionMsg;
        }

        try {
            Application_Model_Block::deleteBlocks($blocks, $user->getId());
        } catch (BlockNoPermissionException $e) {
            $message = $noPermissionMsg;
        } catch (Exception $e) {
            //TODO: warn user that not all blocks could be deleted.
        }

        try {
            Application_Model_Webstream::deleteStreams($streams, $user->getId());
        } catch (WebstreamNoPermissionException $e) {
            $message = $noPermissionMsg;
        } catch (Exception $e) {
            //TODO: warn user that not all streams could be deleted.
            Logging::info($e);
        }

        foreach ($files as $id) {

            $file = Application_Model_StoredFile::RecallById($id);

            if (isset($file)) {
                try {
                    $res = $file->delete();
                } catch (FileNoPermissionException $e) {
                    $message = $noPermissionMsg;
                } catch (Exception $e) {
                    //could throw a scheduled in future exception.
                    $message = _("Could not delete some scheduled files.");
                    Logging::debug($e->getMessage());
                }
            }
        }

        if (isset($message)) {
            $this->view->message = $message;
        }
    }

    // duplicate playlist
    public function duplicateAction(){
        $params = $this->getRequest()->getParams();
        $id = $params['id'];

        $originalPl = new Application_Model_Playlist($id);
        $newPl = new Application_Model_Playlist();

        $contents = $originalPl->getContents();
        foreach ($contents as &$c) {
            if ($c['type'] == '0') {
                $c[1] = 'audioclip';
            } else if ($c['type'] == '2') {
                $c[1] = 'block';
            } else if ($c['type'] == '1') {
                $c[1] = 'stream';
            }
            $c[0] = $c['item_id'];
        }

        $newPl->addAudioClips($contents, null, 'before');

        $newPl->setCreator(Application_Model_User::getCurrentUser()->getId());
        $newPl->setDescription($originalPl->getDescription());

        list($plFadeIn, ) = $originalPl->getFadeInfo(0);
        list(, $plFadeOut) = $originalPl->getFadeInfo($originalPl->getSize()-1);

        $newPl->setfades($plFadeIn, $plFadeOut);
        $newPl->setName(sprintf(_("Copy of %s"), $originalPl->getName()));
    }

    public function contentsFeedAction()
    {
        $params = $this->getRequest()->getParams();

        # terrible name for the method below. it does not only search files.
        $r = Application_Model_StoredFile::searchLibraryFiles($params);

        $this->view->sEcho = $r["sEcho"];
        $this->view->iTotalDisplayRecords = $r["iTotalDisplayRecords"];
        $this->view->iTotalRecords = $r["iTotalRecords"];
        $this->view->files = $r["aaData"];
    }

    public function editFileMdAction()
    {
        $user = Application_Model_User::getCurrentUser();
        $isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));

        $request = $this->getRequest();

        $file_id = $this->_getParam('id', null);
        $file = Application_Model_StoredFile::RecallById($file_id);

        if (!$isAdminOrPM && $file->getFileOwnerId() != $user->getId()) {
            return;
        }

        $form = new Application_Form_EditAudioMD();
        $form->startForm($file_id);
        $form->populate($file->getDbColMetadata());

        if ($request->isPost()) {

            $js = $this->_getParam('data');
            $serialized = array();
            //need to convert from serialized jQuery array.
            foreach ($js as $j) {
                $serialized[$j["name"]] = $j["value"];
            }

            if ($form->isValid($serialized)) {

                $formValues = $this->_getParam('data', null);
                $formdata = array();
                foreach ($formValues as $val) {
                    $formdata[$val["name"]] = $val["value"];
                }
                $file->setDbColMetadata($formdata);

                $data = $file->getMetadata();

                // set MDATA_KEY_FILEPATH
                $data['MDATA_KEY_FILEPATH'] = $file->getFilePath();
                Logging::info($data['MDATA_KEY_FILEPATH']);
                Application_Model_RabbitMq::SendMessageToMediaMonitor("md_update", $data);

                $this->_redirect('Library');
            }
        }

        $this->view->form = $form;
        $this->view->dialog = $this->view->render('library/edit-file-md.phtml');
    }

    public function getFileMetadataAction()
    {
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');

        try {
            if ($type == "audioclip") {
                $file = Application_Model_StoredFile::RecallById($id);
                $this->view->type = $type;
                $md = $file->getMetadata();

                foreach ($md as $key => $value) {
                    if ($key == 'MDATA_KEY_DIRECTORY') {
                        $musicDir = Application_Model_MusicDir::getDirByPK($value);
                        $md['MDATA_KEY_FILEPATH'] = Application_Common_OsPath::join($musicDir->getDirectory(), $md['MDATA_KEY_FILEPATH']);
                    }
                }

                $formatter = new SamplerateFormatter($md["MDATA_KEY_SAMPLERATE"]);
                $md["MDATA_KEY_SAMPLERATE"] = $formatter->format();

                $formatter = new BitrateFormatter($md["MDATA_KEY_BITRATE"]);
                $md["MDATA_KEY_BITRATE"] = $formatter->format();

                $formatter = new LengthFormatter($md["MDATA_KEY_DURATION"]);
                $md["MDATA_KEY_DURATION"] = $formatter->format();

                $this->view->md = $md;

            } elseif ($type == "playlist") {

                $file = new Application_Model_Playlist($id);
                $this->view->type = $type;
                $md = $file->getAllPLMetaData();

                $formatter = new LengthFormatter($md["dcterms:extent"]);
                $md["dcterms:extent"] = $formatter->format();

                $this->view->md = $md;
                $this->view->contents = $file->getContents();
            } elseif ($type == "block") {
                $block = new Application_Model_Block($id);
                $this->view->type = $type;
                $md = $block->getAllPLMetaData();

                $formatter = new LengthFormatter($md["dcterms:extent"]);
                $md["dcterms:extent"] = $formatter->format();

                $this->view->md = $md;
                if ($block->isStatic()) {
                    $this->view->blType = 'Static';
                    $this->view->contents = $block->getContents();
                } else {
                    $this->view->blType = 'Dynamic';
                    $this->view->contents = $block->getCriteria();
                }
                $this->view->block = $block;
            } elseif ($type == "stream") {
                $webstream = CcWebstreamQuery::create()->findPK($id);
                $ws = new Application_Model_Webstream($webstream);

                $md = $ws->getMetadata();

                $this->view->md = $md;
                $this->view->type = $type;
            }
        } catch (Exception $e) {
            Logging::info($e->getMessage());
        }
    }
    
    // added by bzf
    public function createCompendiumAction()
    {
        $user = Application_Model_User::getCurrentUser();
        $isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));

        $request = $this->getRequest();

        $file_id = $this->_getParam('id', null);
        $file = Application_Model_StoredFile::RecallById($file_id);

        if (!$isAdminOrPM && $file->getFileOwnerId() != $user->getId()) {
            return;
        }
        
        $fileName = $file->getName();

	$this->view->fileID = $file_id;
	$this->view->fileName = $fileName;
        $this->view->dialog = $this->view->render('library/create-compendium.phtml');
    }    
    
    public function handleCompendiumAction() {
    	
	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	
    	$user = Application_Model_User::getCurrentUser();  	
    	
        $isAdminOrPM = $user->isUserType(array(UTYPE_ADMIN, UTYPE_PROGRAM_MANAGER));
        
	$userInfo    = Zend_Auth::getInstance()->getStorage()->read();       

	// open database connection
	$airtimeConfig = parse_ini_file('/etc/airtime/airtime.conf');
	
	$dbName = $airtimeConfig['dbname'];
	
	$dbUser = $airtimeConfig['dbuser'];
	
	$dbPassword = $airtimeConfig['dbpass'];
	
	// open database connection	
	$dbConnection = pg_pconnect("host=localhost dbname=" . $dbName . " user=" . $dbUser . " password=" . $dbPassword) or die('Could not connect to Airtime database: ' . pg_last_error());
	
	// get last file ID
	$getLastFileQuery = pg_query("SELECT id FROM cc_files ORDER BY id DESC");
	$lastIDResult = pg_fetch_assoc($getLastFileQuery);
	$lastID = $lastIDResult['id'];
	
	// get last playlist ID
	$getLastPlaylistIDQuery = pg_query("SELECT id FROM cc_playlist ORDER BY id DESC");
	$lastPlaylistIDResult = pg_fetch_assoc($getLastPlaylistIDQuery);
	$lastPlaylistID = $lastPlaylistIDResult['id'];
	$newPlaylistID = $lastPlaylistID + 1;	
	
	// get last playlist entry ID
	$getLastPlaylistEntryIDQuery = pg_query("SELECT id FROM cc_playlistcontents ORDER BY id DESC");
	$lastPlaylistEntryIDResult = pg_fetch_assoc($getLastPlaylistEntryIDQuery);
	$lastPlaylistEntryID = $lastPlaylistEntryIDResult['id'];

	$params = $this->getRequest()->getParams();

	$fileID = $params['fileID'];

	// first, get the originating file info
	// copy the mime, ftype, directory, filepath, state, currentlyaccessing, editedby, mtime, utime, lptime, md5, bit_rate, sample_rate, format, file_exists, replay_gain, owner_id, silas_check, hidden, is_scheduled, is_playlist

	// query cc_playout_history
	$fileInfoQueryString = "SELECT * FROM cc_files WHERE id = " . $fileID;
	
	$fileInfoQuery = pg_query($fileInfoQueryString);		

	$fileInfo = pg_fetch_assoc($fileInfoQuery);
	
	// start new playlist here
	
	$thisDate = date("Y-m-d H:i:s");
	$newPlaylistQueryString = "INSERT INTO cc_playlist (id, name, mtime, utime, creator_id, length) VALUES ('" . $newPlaylistID . "', 'Compendium Playlist " . $thisDate  . "', '" . $thisDate . "', '" . $thisDate . "', '" . $userInfo->id . "', '" . $fileInfo['length'] . "')";
	$newPlaylistQuery = pg_query($newPlaylistQueryString);
	
	$data = $params['data'];
	
	$entry = json_decode($data, true);
	$entryData = var_export($entry, true);
	
	$handle = fopen('/var/log/airtime/test3.txt', 'a+');
	fwrite($handle, date("M j G:i:s T Y") . ":\n" . $entryData . "\n\n");
	fclose($handle); 	
	
	$numberOfEntries = count($entry);
	
	$a = 0;
	$b = 1;

	$newID = $lastID + 1;

	// playlist entry
	$newPlaylistEntryID = $lastPlaylistEntryID + 1;	
	
	do {
		
		if (strstr($entry[$a]['starts'], '.')) {
			$cueInMinutes = strstr($entry[$a]['starts'], '.', true);
			$cueInSeconds = trim(strstr($entry[$a]['starts'], ".") , ".");
		} else {
			$cueInMinutes = $entry[$a]['starts'];
			$cueInSeconds = 0;
		}
		
		if (strstr($entry[$a]['ends'], '.')) {
			$cueOutMinutes = strstr($entry[$a]['ends'], '.', true);
			$cueOutSeconds = trim(strstr($entry[$a]['ends'], ".") , ".");
		} else {
			$cueOutMinutes = $entry[$a]['ends'];
			$cueOutSeconds = 0;
		}		
		
		$startTimeInSeconds = ($cueInMinutes * 60) + $cueInSeconds;
		$endTimeInSeconds = ($cueOutMinutes * 60) + $cueOutSeconds;
		
		$cueIn = gmdate("H:i:s", $startTimeInSeconds) . ".00";
		$cueOut = gmdate("H:i:s", $endTimeInSeconds) . ".00";
		
		$lengthInSeconds = $endTimeInSeconds - $startTimeInSeconds; // 00:00:00.00
		$length = gmdate("H:i:s", $lengthInSeconds) . ".00";
		
		if (empty($entry[$a]['track_number'])) {
		
			$entry[$a]['track_number'] = 0;
		
		}
		
		if (empty($entry[$a]['channels'])) {
		
			$entry[$a]['channels'] = 0;
		
		}
		
		if (empty($entry[$a]['bpm'])) {
		
			$entry[$a]['bpm'] = 0;
		
		}		
		
		// length = cue in - cue out time
		$insertEntryQueryString = "INSERT INTO cc_files (id, name, mime, ftype, directory, filepath, state, mtime, utime, lptime, md5, track_title, artist_name, bit_rate, sample_rate, format, length, album_title, genre, year, track_number, url, bpm, rating, mood, label, composer, conductor, isrc_number, file_exists, replay_gain, owner_id, cuein, cueout, silan_check, hidden, is_scheduled, is_playlist) VALUES ('" . pg_escape_string($newID) . "', '', '" . pg_escape_string($fileInfo['mime']) . "', '" . pg_escape_string($fileInfo['ftype']) . "', '" . pg_escape_string($fileInfo['directory']) . "', '" . pg_escape_string($fileInfo['filepath']) . "', '" . pg_escape_string($fileInfo['state']) . "', '" . pg_escape_string($fileInfo['mtime']) . "', '" . pg_escape_string($fileInfo['utime']) . "', '" . pg_escape_string($fileInfo['lptime']) . "', '" . pg_escape_string($fileInfo['md5']) . "', '" . pg_escape_string($entry[$a]['track_title']) . "', '" . pg_escape_string($entry[$a]['artist_name']) . "', '" . pg_escape_string($fileInfo['bit_rate']) . "', '" . pg_escape_string($fileInfo['bit_rate']) . "', '" . pg_escape_string($fileInfo['format']) . "', '" . pg_escape_string($length) . "', '" . pg_escape_string($entry[$a]['album_title']) . "', '" . pg_escape_string($entry[$a]['genre']) . "', '" . pg_escape_string($entry[$a]['year']) . "', '" . pg_escape_string($entry[$a]['track_number']) . "', '" . pg_escape_string($entry[$a]['info_url']) . "', '" . pg_escape_string($entry[$a]['bpm']) . "', '" . pg_escape_string($fileInfo['rating']) . "', '" . pg_escape_string($entry[$a]['mood']) . "', '" . pg_escape_string($entry[$a]['label']) . "', '" . pg_escape_string($entry[$a]['composer']) . "', '" . pg_escape_string($entry[$a]['conductor']) . "', '" . pg_escape_string($entry[$a]['isrc_number']) . "', 'TRUE', '" . pg_escape_string($fileInfo['replay_gain']) . "', '" . pg_escape_string($fileInfo['owner_id']) . "', '" . pg_escape_string($cueIn) . "', '" . pg_escape_string($cueOut) . "', '" . pg_escape_string($fileInfo['silan_check']) . "', 'FALSE', 'FALSE', 'FALSE')";
		
		$insertEntryQuery = pg_query($insertEntryQueryString);
		
		$insertPlaylistEntryQueryString = "INSERT INTO cc_playlistcontents (id, playlist_id, file_id, position, cliplength, cuein, cueout, fadein, fadeout) VALUES ('" . pg_escape_string($newPlaylistEntryID) . "', '" . pg_escape_string($newPlaylistID) . "', '" . pg_escape_string($newID) . "', '" . pg_escape_string($b) . "', '" . pg_escape_string($fileInfo['length']) . "', '" . pg_escape_string($cueIn) . "', '" . pg_escape_string($cueOut) . "', '" . pg_escape_string($cueIn) . "', '" . pg_escape_string($cueOut) . "')";
		
		$insertPlaylistEntryQuery = pg_query($insertPlaylistEntryQueryString);
		
		$newPlaylistEntryID++;

		$newID++;

		$b++;

		$a++;
	} while ($a < $numberOfEntries);

	$results = json_encode($entry);

	/** $handle = fopen('/var/log/airtime/test.txt', 'a+');
	fwrite($handle, date("M j G:i:s T Y") . ": " . $results . "\n\n");
	fclose($handle); **/

	echo $this->_helper->json($entry);
            	
    }
    
    // end bzf addition

    public function uploadFileSoundcloudAction()
    {
        $id = $this->_getParam('id');
        Application_Model_Soundcloud::uploadSoundcloud($id);
        // we should die with ui info
        $this->_helper->json->sendJson(null);
    }

    public function getUploadToSoundcloudStatusAction()
    {
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');

        if ($type == "show") {
            $show_instance = new Application_Model_ShowInstance($id);
            $this->view->sc_id = $show_instance->getSoundCloudFileId();
            $file = $show_instance->getRecordedFile();
            $this->view->error_code = $file->getSoundCloudErrorCode();
            $this->view->error_msg = $file->getSoundCloudErrorMsg();
        } elseif ($type == "file") {
            $file                   = Application_Model_StoredFile::RecallById($id);
            $this->view->sc_id      = $file->getSoundCloudId();
            $this->view->error_code = $file->getSoundCloudErrorCode();
            $this->view->error_msg  = $file->getSoundCloudErrorMsg();
        } else {
            Logging::warn("Trying to upload unknown type: $type with id: $id");
        }
    }
}
