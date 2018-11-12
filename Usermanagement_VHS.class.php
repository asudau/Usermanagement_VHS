<?php
require_once 'lib/bootstrap.php';
require 'models/UsermanagementAccountStatus.class.php';

/**
 * ContactForm.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 0.1a
 */
//WebServicePlugin


class Usermanagement_VHS extends StudipPlugin implements AdministrationPlugin, SystemPlugin 
{

    public function __construct()
    {
        parent::__construct();
        global $perm;
        $this::check_scheduled_for_delete();
        //NotificationCenter::addObserver($this, "setup_navigation", "PageWillRender");
        if($perm->have_perm('root')){
            $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'index'));
            $navigation->addSubNavigation('index', new Navigation('Übersicht', PluginEngine::getURL($this, array(), 'index')));
            
            $navigation->addSubNavigation('nomail', new Navigation('Fehler bei Mailzustellung', PluginEngine::getURL($this, array(), 'index/nomail')));
            $navigation->addSubNavigation('problemdelete', new Navigation('Fehler beim Löschen', PluginEngine::getURL($this, array(), 'index/problemdelete')));
            Navigation::addItem('/admin/usermanagement', $navigation);
        } 
    }

    public function initialize ()
    {
        PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
        //PageLayout::addScript($this->getPluginURL().'/assets/application.js');
        
    }


    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return array();
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        // ...
    }

    public function getInfoTemplate($course_id)
    {
        // ...
    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
        
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
    
    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return dgettext('usermanagement', 'Erweiterte Nutzerverwaltung');
    }
    
    public function check_scheduled_for_delete(){
        $user_id = $GLOBALS['user']->id;
        if (UserConfig::get($user_id)->getValue(EXPIRATION_DATE)) {
            $status_info = UsermanagementAccountStatus::find($user_id);
            if ($status_info->delete_mode == 'aktivitaet' && $status_info->account_status != 3){
                $status_info->account_status = 3;
                $status_info->store();
                header('Location: '. PluginEngine::getURL($this, array(), 'deleteDialog'), false, 303);
                exit();
            }
            //TODO if status == 3 hinweis platzieren
        }
    } 
    
}
