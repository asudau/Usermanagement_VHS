<?php
/**
*
* @author Annelene Sudau <asudau@uos.de>
* @access public
*/
require_once 'lib/classes/CronJob.class.php';

class ScheduleDeleteInactiveUser extends CronJob
{

    public static function getName()
    {
        return dgettext('Erweitertes Usermanagement', 'Inaktive Nutzer per Mail informieren und für Löschung vormerken');
    }

    public static function getDescription()
    {
        return dgettext('Erweitertes Usermanagement', 'Inaktive Nutzer per Mail informieren und für Löschung vormerken');
    }
    
    private static function sendInfoMail($user_id, $remind = false){
        
        $user = New User($user_id);
        $contact_mail = $user->Email; //TODO get user Mail
        
        if ($remind) {
            $config_mail = Config::get()->getValue('USER_DELETE_MAIL_REMINDER');
        } else $config_mail = Config::get()->getValue('USER_INACTIVITY_DELETE_MAIL');
        
        $mailtext = 'Liebe/r Nutzer/in             
                ' . $config_mail   . '
                    
                Account: ' . $user->Vorname . ' ' . $user->Nachname . '                       
                Username: ' . $user->username .'
                
                Link zum System: ' . $GLOBALS['ABSOLUTE_URI_STUDIP'] ;
            

            $empfaenger = $contact_mail;
            //$absender   = "asudau@uos.de";
            $betreff    = Config::get()->getValue('USER_INACTIVITY_DELETE_MAIL_SUBJECT');

            $template = $GLOBALS['template_factory']->open('mail/html');
            $template->set_attribute('lang', 'de');
            $template->set_attribute('message', $mailtext);
            $mailhtml = $template->render();
            
            
            return StudipMail::sendMessage($empfaenger, $betreff, $mailtext, $mailhtml);
            /**
            return $mail->addRecipient($empfaenger)
                 ->setReplyToEmail('')
                 ->setSenderEmail('el4@elan-ev.de')
                 ->setSenderName($GLOBALS['UNI_NAME_CLEAN']) //Globals UNI_NAME
                 ->setSubject($betreff)
                 ->setBodyText($mailtext)
                 ->send();''/
                 **/
    }

    
    private static function scheduleForDeleteAndInform($status_info, $remind = false){
        if (self::sendInfoMail($status_info->user_id, $remind)){
            $time = time();
            $sec_per_day = 86400;
            if (!$remind){
                //Löschung in x tagen, siehe konfiguration
                $time_till_delete = Config::get()->getValue(USER_INACTIVITY_TIME_TILL_DELETE);
                UserConfig::get($status_info->user_id)->store("EXPIRATION_DATE", $time + ($sec_per_day*$time_till_delete));
                $status_info->account_status = 1;
                $status_info->store();
                //wenn zur Löschung vorgemerkt (status == 1)
                echo 'vorgemerkt: ' . $status_info->user_id . ' \n';
            } else {
                $status_info->account_status = 2;
                $status_info->store();
            }
        } else echo 'mail konnte nicht versendet werden: ' . $status_info->user_id . ' \n';
    }
    
    
    
    public function execute($last_result, $parameters = array())
    {
        PluginEngine::getPlugin('Usermanagement');
        $max_inactivity = Config::get()->getValue(USER_INACTIVITY_BEFORE_DELETE);
        $sec_per_day = 86400;
        $last_inactivity = time() - ($max_inactivity * $sec_per_day);
        
        if (Config::get()->getValue(USERMANAGEMENT_TEST_MODE)){
            $user_id = Config::get()->getValue(USERMANAGEMENT_TEST_MODE_USER);
            echo 'test_mode';
            //$user_id = 'b3570651fed225931a99d5f4683838c7'; //asudau eL4
            self::check_on_user($user_id);
            $status_info = UsermanagementAccountStatus::find($user_id);
            self::scheduleForDeleteAndInform($status_info, true);
            $status_info->account_status = 0;
            $status_info->store();
            
        } else {
            
            echo 'los gehts';
            //wenn letzte Nutzeraktivität länger her ist als x (x wird in Konfiguration festgelegt)
            $db = DBManager::get();
            $query = 'SELECT user_id FROM user_online WHERE last_lifesign < :time';
            $statement = $db->prepare($query);
            $statement->execute(array(':time'=> $last_inactivity));
            $inactive_users = $statement->fetchAll();

            foreach ($inactive_users as $user_id) {
                echo 'inaktiv: ' . $user_id[0] . ' \n';
                self::check_on_user($user_id[0]);

            }
        }
        
        return true;
    }
    
    private function check_on_user($user_id){
        
        $status_info = UsermanagementAccountStatus::find($user_id);
        $sec_per_day = 86400;
        //Löschung in x tagen, siehe konfiguration
        $time_till_delete = Config::get()->getValue(USER_INACTIVITY_TIME_TILL_DELETE);
        
            //wenn die Gültigkeit des Accounts von der Aktivität abhängt und noch keine Mail versendet wurde (status == 0)
            if ($status_info->account_status == 0 && $status_info->delete_mode == 'aktivitaet'){
                echo 'schedulefordeleteandinform ' . $user_id;
                //schedule_for_delete_and_inform
                self::scheduleForDeleteAndInform($status_info);
                    
            } else if ($status_info->account_status == 1 && $status_info->delete_mode == 'aktivitaet'){
                $expiration = UserConfig::get($user_id)->getValue("EXPIRATION_DATE");
                $time = time();
                //wenn Halbzeit bis Löschung: Erinnerungsmail //TODO 2 frühestens wochen vor Löschung
                if ($time > ($expiration - ($sec_per_day*7))){
                    echo 'noch eine woche für ' . $user_id;
                    self::scheduleForDeleteAndInform($status_info, true);
                }
                
            } else if (!$status_info){
                //Neuen Eintrag im Usermanagement anlegen
                $status_info = new UsermanagementAccountStatus();
                $status_info->user_id = $user_id;
                $status_info->account_status = 0;
                $status_info->delete_mode = 'aktivitaet'; //wenn nichts anderes bekannt ist das der default delete_mode
                $status_info->chdate = time();
                $status_info->store();
                //schedule_for_delete_and_inform
                self::scheduleForDeleteAndInform($status_info);
            } else echo 'nix mehr zu tun';
    }
  
}
