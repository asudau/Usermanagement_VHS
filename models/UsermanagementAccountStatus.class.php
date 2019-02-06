<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $user_id
 * @property string     $delete_mode : aktivitaet, nie loeschen, nach kursende
 * @property int        $account_status
 * status   0 == keine Aktion erforderlich/L�schvermerk zur�ckgesetzt
 * status   1 == zur L�schung vorgemerkt
 * status   2 == zur L�schung vorgemerkt und Erinnerungsmail wurde verschickt
 * status   3 == zur L�schung vorgemerkt aber Mail konnte nicht zugestellt werden
 * status   4 == konnte nicht gel�scht werden weil einziger Dozent in VA
 * status   5 == Nutzer trotz fehlerhafler Mailadresse l�schen
 * status   6 == erfolgreich gel�scht
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
