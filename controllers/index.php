<?php
class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        Navigation::activateItem('admin/usermanagement/index');
        
        $navcreate = new LinksWidget();
        $navcreate->setTitle('Aktionen');
        //$attr = array("onclick"=>"showModalNewSupervisorGroupAction()");
        //$navcreate->addLink("Ausnahme hinzuf�gen", $this::url_for('/index'), Icon::create('add'), $attr);
        // add "add dozent" to infobox
        $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
                            . "FROM auth_user_md5 "
                            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
                            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
                            . "OR auth_user_md5.username LIKE :input)"
                            //. "AND auth_user_md5.user_id NOT IN "
                            //. "(SELECT supervisor_group_user.user_id FROM supervisor_group_user WHERE supervisor_group_user.supervisor_group_id = '". $supervisorgroupid ."')  "
                            . "ORDER BY Vorname, Nachname ",
                _("Ausnahme hinzuf�gen"), "username");
        
        $mp = MultiPersonSearch::get('unset_user')
            ->setLinkText(sprintf(_('Ausnahme hinzuf�gen')))
            //->setDefaultSelectedUser($filtered_members['dozent']->pluck('user_id'))
            ->setLinkIconPath("")
            ->setTitle(sprintf(_('Ausnahme hinzuf�gen')))
            ->setExecuteURL($this::url_for('/index/unset'))
            ->setSearchObject($search_obj)
            //->addQuickfilter(sprintf(_('%s der Einrichtung'), $this->status_groups['dozent']), $membersOfInstitute)
            //->setNavigationItem('/')
            ->render();
        $element = LinkElement::fromHTML($mp, Icon::create('community+add', 'clickable'));
        $navcreate->addElement($element);
        
        $sidebar = Sidebar::Get();
        $sidebar->addWidget($navcreate);
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Erweitertes Usermanagement - �bersicht"));

        // $this->set_layout('layouts/base');
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action()
    {
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

    public function save_action(){

        if ($entry->store() !== false) {
            $message = MessageBox::success(_('Die �nderungen wurden �bernommen.'));
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
                $message = MessageBox::success(_('Der Nutzer wird auch im Falle l�ngerer Inaktivit�t nicht gel�scht.'));
                PageLayout::postMessage($message);
            }
        } else {
            $mp = MultiPersonSearch::load('unset_user');
            # User der Gruppe hinzuf�gen
            foreach ($mp->getAddedUsers() as $user_id) {
                $status_info = UsermanagementAccountStatus::find($user_id);
                if ($status_info){
                    $status_info->delete_mode = 'nie loeschen';
                    $status_info->account_status = 0;
                    UserConfig::get($user_id)->store("EXPIRATION_DATE", NULL);
                    if ($status_info->store() !== false) {
                        $message = MessageBox::success(_('Der Nutzer wird auch im Falle l�ngerer Inaktivit�t nicht gel�scht.'));
                        PageLayout::postMessage($message);
                    }
                } else {
                    $status_info = new UsermanagementAccountStatus();
                    $status_info->user_id = $user_id;
                    $status_info->account_status = 0;
                    $status_info->delete_mode = 'nie loeschen'; //wenn nichts anderes bekannt ist das der default delete_mode
                    $status_info->chdate = time();
                    if ($status_info->store() !== false) {
                        $message = MessageBox::success(_('Der Nutzer wird auch im Falle l�ngerer Inaktivit�t nicht gel�scht.'));
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
            $message = MessageBox::success(_('Der Nutzer wird im Falle von Inaktivitaet gel�scht werden.'));
            PageLayout::postMessage($message);
        }
        $this->redirect($this::url_for('/index'));
          
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

            <h2>Teilnahmezertifikat f�r ' . $user . ':</h2>

            <p>Im Anhang finden Sie ein Teilnahmezertifikat f�r den/die Teilnehmer/in einer Onlineschulung</p>

            </body>
            </html>
            ';

            $empfaenger = $contact_mail;//$contact_mail; //Mailadresse
            //$absender   = "asudau@uos.de";
            $betreff    = "Teilnahmezertifikat f�r " . $user . " f�r erfolgreiche Teilnahme an Mitarbeiterschulung";
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
