<?php
class DeleteDialogController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Account-Löschung"));

        // $this->set_layout('layouts/base');
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action()
    {
        $user_id = $GLOBALS['user']->id;
        $this->expiration = date('d.m.Y', UserConfig::get($user_id)->getValue(EXPIRATION_DATE)); 
    }

     public function save_action($param){

        $user_id = $GLOBALS['user']->id;
        $status_info = UsermanagementAccountStatus::find($user_id);
        $status_info->account_status = 0;
        $status_info->store();
        UserConfig::get($user_id)->store("EXPIRATION_DATE", NULL);

        $message = MessageBox::success(_('Ihr Account ist jetzt nicht mehr zur Löschung vorgemerkt.'));
        PageLayout::postMessage($message);

        $this->redirect($GLOBALS['ABSOLUTE_URI_STUDIP']);
          
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
