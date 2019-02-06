<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $user_id
 * @property string     $delete_mode : aktivitaet, nie loeschen, nach kursende
 * @property int        $account_status
 * status   0 == keine Aktion erforderlich/Löschvermerk zurückgesetzt
 * status   1 == zur Löschung vorgemerkt
 * status   2 == zur Löschung vorgemerkt und Erinnerungsmail wurde verschickt
 * status   3 == zur Löschung vorgemerkt aber Mail konnte nicht zugestellt werden
 * status   4 == konnte nicht gelöscht werden weil einziger Dozent in VA
 * status   5 == Nutzer trotz fehlerhafler Mailadresse löschen
 * status   6 == erfolgreich gelöscht
 * @property int        $chdate
 */

class UsermanagementAccountStatus extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'usermanagement_account_status';

        parent::configure($config);
    }
    
    //TODO testen
    public function getDeleteOnInactivity(){
        if ($this->delete_mode == 'aktivitaet'){
            return true;
        } else return false;
    }
    
    public static function chdate($user_id){
        return self::find($user_id)->chdate;
    }
}
