<?php
/*
 * status   0 == keine Aktion erforderlich/Löschvermerk zurückgesetzt
 * status   1 == zur Löschung vorgemerkt
 * status   2 == zur Löschung vorgemerkt und Erinnerungsmail wurde verschickt
 * status   3 == zur Löschung vorgemerkt aber Mail konnte nicht zugestellt werden
 * status   4 == konnte nicht gelöscht werden weil einziger Dozent in VA
 * status   5 == Nutzer trotz fehlerhafler Mailadresse löschen
 * status   6 == erfolgreich gelöscht
*/

require_once 'lib/archiv.inc.php';

class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        
        $navcreate = new LinksWidget();
        $navcreate->setTitle('Aktionen');
        //$attr = array("onclick"=>"showModalNewSupervisorGroupAction()");
        //$navcreate->addLink("Ausnahme hinzufügen", $this::url_for('/index'), Icon::create('add'), $attr);
        // add "add dozent" to infobox
        $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
                            . "FROM auth_user_md5 "
                            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
                            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
                            . "OR auth_user_md5.username LIKE :input)"
                            //. "AND auth_user_md5.user_id NOT IN "
                            //. "(SELECT supervisor_group_user.user_id FROM supervisor_group_user WHERE supervisor_group_user.supervisor_group_id = '". $supervisorgroupid ."')  "
                            . "ORDER BY Vorname, Nachname ",
                _("Ausnahme hinzufügen"), "username");
        
        $mp = MultiPersonSearch::get('unset_user')
            ->setLinkText(sprintf(_('Ausnahme hinzufügen')))
            //->setDefaultSelectedUser($filtered_members['dozent']->pluck('user_id'))
            ->setLinkIconPath("")
            ->setTitle(sprintf(_('Ausnahme hinzufügen')))
            ->setExecuteURL($this::url_for('/index/unset'))
            ->setSearchObject($search_obj)
            //->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['dozent']), $membersOfInstitute)
            //->setNavigationItem('/')
            ->render();
        $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
        $navcreate->addElement($element);
        //new $navigation->addSubNavigation('settings', new Navigation('Einstellungen', PluginEngine::getURL($this, array(), 'settings')));
        
        $navcreate->addLink(
            _('Einstellungen'),
            PluginEngine::getLink($this->plugin, [], 'settings'),
            Icon::create('edit', 'clickable'), ['data-dialog' => "size=auto;reload-on-close"]);
        
        $sidebar = Sidebar::Get();
        $sidebar->addWidget($navcreate);
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Erweitertes Usermanagement - Übersicht"));

        // $this->set_layout('layouts/base');
        //$this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action()
    {
        Navigation::activateItem('admin/usermanagement/index');
        $status_infos = UsermanagementAccountStatus::findBySQL("account_status IN (1, 2) AND delete_mode LIKE 'aktivitaet'");
        $this->data = array();
        foreach ($status_infos as $status_info){
            $this->data[] = array('user' => User::find($status_info->user_id), 'status' => $status_info->account_status);
        }
        
        $status_infos = UsermanagementAccountStatus::findBySQL("delete_mode LIKE 'nie loeschen'");
        $this->data_spared = array();
        foreach ($status_infos as $status_info){
            $this->data_spared[] = array('user' => User::find($status_info->user_id), 'status' => $status_info->account_status);
        }
                
    }
    
    public function nomail_action()
    {
        Navigation::activateItem('admin/usermanagement/nomail');
        $status_infos = UsermanagementAccountStatus::findBySQL("account_status IN (3) AND delete_mode LIKE 'aktivitaet'");
        $this->data = array();
        foreach ($status_infos as $status_info){
            $this->data[] = array('user' => User::find($status_info->user_id), 'status' => $status_info->account_status);
        }
               
    }
    
    public function problemdelete_action()
    {
        Navigation::activateItem('admin/usermanagement/problemdelete');
        $status_infos = UsermanagementAccountStatus::findBySQL("account_status IN (4) AND delete_mode LIKE 'aktivitaet'");
        $this->data = array();
        foreach ($status_infos as $status_info){
            $user = User::find($status_info->user_id);
            if ($user->course_memberships){
                $seminare_dozent = $user->course_memberships->findBy('status', 'dozent');
                foreach($seminare_dozent as $membership){
                    if (Course::find($membership->seminar_id)){
                        $count = CourseMember::countByCourseAndStatus($membership->seminar_id, 'dozent');
                        //if ($count < 2){
                            $seminare[] = $membership;
                        //}
                    }
                }
                $this->data[] = array('user' => $user, 
                    'status' => $status_info->account_status,
                    'seminare' => $seminare
                    );
            } else {
                $status_info->account_status = 2;
                $status_info->store();
            }
        }  
            
    }
    
    public function archiveseminar_action($sem_id){
        in_archiv($sem_id);
        $sem = new Seminar($sem_id);
        // Delete that Seminar.
        if ($sem->delete()) {
            $message = MessageBox::success(_('Die Veranstaltung wurde archiviert.'));
            PageLayout::postMessage($message);
        }

        $this->redirect($this::url_for('/index/problemdelete'));
        
    }

    public function save_action(){

        if ($entry->store() !== false) {
            $message = MessageBox::success(_('Die Änderungen wurden übernommen.'));
            PageLayout::postMessage($message);
        }

        $this->redirect($this::url_for('/index'));
          
    }
    
    public function unset_action($user_id){

         if ($user_id){
            $status_info = UsermanagementAccountStatus::find($user_id);
            $status_info->delete_mode = 'nie loeschen';
            $status_info->account_status = 0;
            UserConfig::get($user_id)->store("EXPIRATION_DATE", NULL);
            if ($status_info->store() !== false) {
                $message = MessageBox::success(_('Der Nutzer wird auch im Falle längerer Inaktivität nicht gelöscht.'));
                PageLayout::postMessage($message);
            }
        } else {
            $mp = MultiPersonSearch::load('unset_user');
            # User der Gruppe hinzufügen
            foreach ($mp->getAddedUsers() as $user_id) {
                $status_info = UsermanagementAccountStatus::find($user_id);
                if ($status_info){
                    $status_info->delete_mode = 'nie loeschen';
                    $status_info->account_status = 0;
                    UserConfig::get($user_id)->store("EXPIRATION_DATE", NULL);
                    if ($status_info->store() !== false) {
                        $message = MessageBox::success(_('Der Nutzer wird auch im Falle längerer Inaktivität nicht gelöscht.'));
                        PageLayout::postMessage($message);
                    }
                } else {
                    $status_info = new UsermanagementAccountStatus();
                    $status_info->user_id = $user_id;
                    $status_info->account_status = 0;
                    $status_info->delete_mode = 'nie loeschen'; //wenn nichts anderes bekannt ist das der default delete_mode
                    $status_info->chdate = time();
                    if ($status_info->store() !== false) {
                        $message = MessageBox::success(_('Der Nutzer wird auch im Falle längerer Inaktivität nicht gelöscht.'));
                        PageLayout::postMessage($message);
                    }
                }
            }
        }
       
        $this->redirect($this::url_for('/index'));
          
    }
    
     public function set_action($user_id){

        $status_info = UsermanagementAccountStatus::find($user_id);
        $status_info->delete_mode = 'aktivitaet';
        $status_info->account_status = 0;
        if ($status_info->store() !== false) {
            $message = MessageBox::success(_('Der Nutzer wird im Falle von Inaktivitaet gelöscht werden.'));
            PageLayout::postMessage($message);
        }
        $this->redirect($this::url_for('/index'));
          
    }
    
    public function add_dozent_action($sem_id){
        
        $mp = MultiPersonSearch::load('add_dozent_' . $sem_id);
        
        $sem = new Seminar($sem_id);
        if ($sem){
            $old_dozenten = $sem->getMembers('dozent');
            # User der Gruppe hinzufügen
            foreach ($mp->getAddedUsers() as $user_id) {
                $sem->addMember($user_id, 'dozent');
                PageLayout::postMessage(MessageBox::success(_('Der Dozentenaccount wurd hinzugefügt.')));
            }
            foreach ($old_dozenten as $dozent){
                $status_info = UsermanagementAccountStatus::find($dozent['user_id']);
                if ($status_info){
                    $user = User::find($dozent['user_id']);
                    $single_dozent_in_seminar = false;
                    $seminare_dozent = $user->course_memberships->findBy('status', 'dozent');
                    foreach($seminare_dozent as $membership){
                        $count = CourseMember::countByCourseAndStatus($membership->seminar_id, 'dozent');
                        if ($count < 2){
                            //$single_dozent_in_seminar = true;
                        }
                     //falls kein Seminar existiert in welchem dieser Nutzer einziger Dozent ist: Account löschen
                    } if (!$single_dozent_in_seminar){
                        //zurücksetzen für standard lösch-prozess (status == 2)
                        $status_info->account_status = 2;
                        $status_info->store();
                    }
                }
            }
        }

        $this->redirect($this::url_for('/index/problemdelete'));
    }
    
    public function delete_without_mail_action($user_id){
        $status_info = UsermanagementAccountStatus::find($user_id);
        $status_info->account_status = 5;
        if ($status_info->store()){
                PageLayout::postMessage(MessageBox::success(_('Unzustellbare Mails werden für diesen Nutzer ignoriert.')));
        }
        $this->redirect($this::url_for('/index/nomail'));
    }
    
    public function get_mp($seminar_id){
        
        $search_object = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
                            . "FROM auth_user_md5 "
                            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
                            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
                            . "OR auth_user_md5.username LIKE :input)"
                            . "AND perms LIKE 'dozent'"
                            . "ORDER BY Vorname, Nachname ",
                _(""), "username");
        
        return MultiPersonSearch::get('add_dozent_' . $seminar_id)
            ->setLinkText(sprintf(_('')))
            //->setDefaultSelectedUser($filtered_members['dozent']->pluck('user_id'))
            ->setLinkIconPath(Icon::create("person+add"))
            ->setTitle(sprintf(_('Dozent hinzufügen')))
            ->setExecuteURL($this::url_for('/index/add_dozent/' . $seminar_id))
            ->setSearchObject($search_object)
            //->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['dozent']), $membersOfInstitute)
            //->setNavigationItem('/')
            ->render();
    }
    
    // customized #url_for for plugins
    public function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
    
     public function sendMail_action($user_id)
    {

        $seminar_id = Course::findCurrent()->seminar_id;
        $course = new Seminar($seminar_id);
        $institute = new Institute($course->getInstitutId());
        $zertifikatConfigEntry = UsermanagementAccountStatus::find($user_id);
        $contact_mail = $zertifikatConfigEntry->getValue('contact_mail');
        
        $filepath = $this->pdf_action($user, $course->name, $institute->name);

        $dateien = array($filepath);
        
        $mailtext = '<html>
          

            <body>

            <h2>Teilnahmezertifikat für ' . $user . ':</h2>

            <p>Im Anhang finden Sie ein Teilnahmezertifikat für den/die Teilnehmer/in einer Onlineschulung</p>

            </body>
            </html>
            ';

            $empfaenger = $contact_mail;//$contact_mail; //Mailadresse
            //$absender   = "asudau@uos.de";
            $betreff    = "Teilnahmezertifikat für " . $user . " für erfolgreiche Teilnahme an Mitarbeiterschulung";
            $filename = 'zertifikat_'. $this->clear_string($user) . '.pdf';

            $mail = new StudipMail();
            $sent =  $mail->addRecipient($empfaenger)
                //->addRecipient('elmar.ludwig@uos.de', 'Elmar Ludwig', 'Cc')
                 ->setReplyToEmail('')
                 ->setSenderEmail('')
                 ->setSenderName('E-Learning - DSO - Datenschutz')
                 ->setSubject($betreff)
                 ->addFileAttachment($filepath, $name = $filename)
                 ->setBodyHtml($mailtext)
                 ->setBodyHtml(strip_tags($mailtext))  
                 ->send();
 
            if ($sent){
                PageLayout::postMessage(MessageBox::success(sprintf(_('e-Mail gesendet'), $sem_name)));
                $this->redirect('index');
            } else {
                 PageLayout::postMessage(MessageBox::success(sprintf(_('Senden der eMail fehlgeschlagen'), $sem_name)));
                $this->redirect('index');
            }
            
    }
    
}
