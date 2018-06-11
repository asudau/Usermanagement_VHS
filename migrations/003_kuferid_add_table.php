<?php

class KuferidAddTable extends Migration
{
    public function description()
    {
        return 'Add DB tables for Kufer-Usermanagement';
    }

    public function up()
    {
        $db = DBManager::get();

        // add db-table
        $db->exec("CREATE TABLE IF NOT EXISTS `kufer_id_mapping` (
            `kufer_id` varchar(32) NOT NULL PRIMARY KEY,
            `studip_id` varchar(32) NOT NULL,
            `kufer_username` varchar(32) NOT NULL,
            `mkdate` int(11) NOT NULL
        )");
        
        $db = DBManager::get();

        // add db-table
        $db->exec("CREATE TABLE IF NOT EXISTS `kufer_date_id_mapping` (
            `kufer_id` varchar(32) NOT NULL PRIMARY KEY,
            `studip_id` varchar(32) NOT NULL
        )");
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE kufer_date_id_mapping");
        $db->exec("DROP TABLE kufer_id_mapping");

        SimpleORMap::expireTableScheme();
    }
}
