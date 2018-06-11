<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $user_id
 * @property string     $delete_mode
 * @property int        $account_status
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
