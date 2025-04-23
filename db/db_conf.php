<?php

class AAConf {

    private static $databaseHost = "";
    private static $databaseUName = "";
    private static $databasePWord = "";
    private static $databaseName = "";
    private static $databaseName1 = "";
    private static $databasePort = "";

    public static function get_databaseURL() {
        global $DB_HOST,$DB_PORT;
        AAConf::$databaseHost = $DB_HOST;
        AAConf::$databasePort = $DB_PORT;
        return "".AAConf::$databaseHost.":".AAConf::$databasePort;
    }

    public static function get_databaseHost() {
        global $DB_HOST;
        AAConf::$databaseHost = $DB_HOST;
        return "".AAConf::$databaseHost;
    }

    public static function get_databaseUName() {
        global $DB_USER;
        AAConf::$databaseUName = $DB_USER;
        return "".AAConf::$databaseUName;
    }

    public static function get_databasePWord() {
        global $DB_PASS;
        AAConf::$databasePWord = $DB_PASS;
        return "".AAConf::$databasePWord;
    }

    public static function get_databaseName() {
        global $DB_NAME;
        AAConf::$databaseName = $DB_NAME;
        return "".AAConf::$databaseName;
    }

    public static function get_databasePort() {
        global $DB_PORT;
        AAConf::$databasePort = $DB_PORT;
        return "".AAConf::$databasePort;
    }

	public static function get_databaseName1() {
        return "".AAConf::$databaseName1;
    }
}
?>
