<?php

/**
 *
 * @author Annelene Sudau <asudau@uos.de>
 */
class CronjobScheduleDeleteInactiveUser extends Migration
{

    const FILENAME = 'public/plugins_packages/elan-ev/Usermanagement_VHS/cronjobs/schedule_delete_inactive_user.php';

    public function description()
    {
        return 'add cronjob for scheduling deletion of inactive user';
    }

    public function up()
    {
        $task_id = CronjobScheduler::registerTask(self::FILENAME, true);

        // Schedule job to run every day at 23:59
        if ($task_id) {
            CronjobScheduler::schedulePeriodic($task_id, -1000);  // negative value means "every x minutes"
        }
        
        Config::get()->create('USER_INACTIVITY_BEFORE_DELETE', array(
            'value'       => 365,
            'is_default'  => 0,
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'Wenn User l�nger als X Tage inaktiv sind werden Sie zur Loeschung vorgemerkt und informiert'
            ));
        
        Config::get()->create('USER_INACTIVITY_DELETE_MAIL', array(
            'value'       => 'Ihr Account wird aufgrund l�ngerer Inaktivit�t gel�scht, '.
                             'falls Sie sich nicht in den n�chsten 3 Wochen einloggen und der L�schung widersprechen.',
            'is_default'  => 0,
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'EMail Text fuer Systembenachrichtigung ueber bevorstehende Loeschung'
            ));
        
        Config::get()->create('USER_DELETE_MAIL_REMINDER', array(
            'value'       => 'Ihr Account wird aufgrund l�ngerer Inaktivit�t in 7 Tagen gel�scht. Loggen Sie sich vorher ein, um dem L�schvorgang zu widersprechen.',
            'is_default'  => 0,
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'EMail Text fuer Erinnerung an bevorstehende Account-Loeschung'
            ));
        
        Config::get()->create('USER_INACTIVITY_TIME_TILL_DELETE', array(
            'value'       => 22,
            'is_default'  => 0,
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'User, die zur Loeschung vorgemerkt sind, haben X Tage Zeit der Loeschung zu widersprechen.'
            ));
        
        Config::get()->create('USERMANAGEMENT_TEST_MODE', array(
            'value'       => 'true',
            'is_default'  => 0,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'Testmodus f�r den Cronjob zur L�schung inaktiver Nutzer'
            ));
        
        Config::get()->create('USERMANAGEMENT_TEST_MODE_USER', array(
            'value'       => 'b3570651fed225931a99d5f4683838c7',
            'is_default'  => 0,
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'Nutzer_ID f�r das Versenden einer Testmail'
            ));
        
        Config::get()->create('USER_INACTIVITY_DELETE_MAIL_SUBJECT', array(
            'value'       => 'L�schung Ihres Stud.IP Accounts',
            'is_default'  => 0,
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'vhs',
            'description' => 'Betreff f�r Systemmails an Nutzer'
            ));

    }

    function down()
    {
        if ($task_id = CronjobTask::findByFilename(self::FILENAME)->task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }
}
