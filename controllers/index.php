<?php
class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        Navigation::activateItem('admin/usermanagement/index');
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Erweitertes Usermanagement - Übersicht"));

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
            $message = MessageBox::success(_('Die Änderungen wurden übernommen.'));
            PageLayout::postMessage($message);
        }

        $this->redirect($this::url_for('/index'));
          
    }
    
    public function unset_action($user_id){

        $status_info = UsermanagementAccountStatus::find($user_id);
        $status_info->delete_mode = 'nie loeschen';
        $status_info->account_status = 0;
        UserConfig::get($user_id)->store("EXPIRATION_DATE", NULL);
        if ($status_info->store() !== false) {
            $message = MessageBox::success(_('Der Nutzer wird auch im Falle längerer Inaktivität nicht gelöscht.'));
            PageLayout::postMessage($message);
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
