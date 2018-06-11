<?php

class UsermanagementAddTable extends Migration
{
    public function description()
    {
        return 'Add DB table for extended Usermanagment';
    }

    public function up()
    {
        $db = DBManager::get();

        // add db-table
        $db->exec("CREATE TABLE IF NOT EXISTS `usermanagement_account_status` (
            `user_id` varchar(32) NOT NULL,
            `delete_mode` varchar(32) NOT NULL,
            `account_status` int(4) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (user_id)
        ) ");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE usermanagement_account_status");

        SimpleORMap::expireTableScheme();
    }
}
