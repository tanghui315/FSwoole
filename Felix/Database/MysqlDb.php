<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/11/18
 * Time: 14:18
 */

namespace Felix\Database;
use Felix;

class MysqlDb {


    public static $db;

    public static function init($mysql_config){
        $mysql = new \mysqli();
        $mysql->connect( $mysql_config['host'], $mysql_config['user'], $mysql_config['pass'], $mysql_config['dbname'], $mysql_config['port'] );
        self::$db=$mysql;
    }
    /**
     * Perform a SELECT statement
     * @param string $user_id
     * @param string $sql
     * @param string $pk4index 主键 返回数组下标, null采用0子增下标
     * @return mixed array查询数组；FALSE,查询失败
     */
    public static function query( $sql, $pk4index = NULL) {

        $result = array ();

        //mysqli_query
        //Returns FALSE on failure.
        //For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a result object.
        //For other successful queries mysqli_query() will return TRUE.
        $stmt = self::$db->query($sql);
        if (is_bool($stmt)) {
            return FALSE;
        }

        if (isset($pk4index)) {
            while ($row = mysqli_fetch_assoc($stmt)) {
                $result[$row[$pk4index]] = $row;
            }
        }
        else {
            while ($row = mysqli_fetch_assoc($stmt)) {
                $result[] = $row;
            }
        }
        @mysqli_free_result($stmt); //Frees the memory associated with a result

        return $result; //返回二维数组
    }

    /**
     * 取得一行记录
     *
     * @param string $sql
     * @param boolean $is_first boolean, TRUE：取第1行；FALSE：取最后一行
     * @return mixed array()查询信息；FALSE,查询失败
     */
    public static function queryOne($sql, $is_first = TRUE) {

        $result = array ();

        $stmt = self::$db->query($sql);
        if (is_bool($stmt)) {
            return FALSE;
        }

        while ($row = mysqli_fetch_assoc($stmt)) {
            if ($is_first) {
                $result = $row;
                break;
            }
            else {
                $result[] = $row;
            }
        }
        @mysqli_free_result($stmt); //清空查询句柄

        if (!$is_first && (count($result) > 0)) {
            $result = array_pop($result);
        }

        return $result;
    }

    /**
     * Perform a insert query
     *
     * @param $user_id
     * @param string $sql
     * @return Returns FALSE on failure. Return TRUE for successful queries mysqli_query().
     */
    public static function insert($sql) {
        $stmt = self::$db->query($sql);

        if  (!$stmt) {
            return false;
        }
        return mysqli_insert_id(self::$db->connection);
    }

    /**
     * 防sql注入插入方式
     * @param $user_id
     * @param $patterns
     * @param $values
     * @return bool
     * $stmt = $mysqli->prepare("INSERT INTO CountryLanguage VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssd', $code, $language, $official, $percent);
     * i	corresponding variable has type integer
    d	corresponding variable has type double
    s	corresponding variable has type string
    b	corresponding variable is a blob and will be sent in packets
     */
    public static function insertX($table, $patterns, $values) {

        $fields = '';
        $value_str = '';
        foreach ($values as $key => $value) {
            $fields .= $key . ',';
            $value_str .= $value_str . ',';
            self::$db->bind_param($patterns[$key], $value);
        }
        $fields = substr($fields, 0, strlen($fields) - 1);
        $value_str = substr($fields, 0, strlen($value_str) - 1);
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$value_str}) ";
        $stmt = self::$db->prepare($sql);


        $stmt->execute();
        if  (!$stmt) {
            return false;
        }
        return mysqli_insert_id(self::$db->connection);
    }

    /**
     * Perform a Update query
     *
     * @param string $sql
     * @return Returns FALSE on failure. Return TRUE for successful queries mysqli_query().
     */
    public static function update( $sql) {
        self::$db->query( "set names utf8" );
        $stmt = self::$db->query($sql);
        if (!$stmt) {
            return false;
        }

        return self::$db->connection->affected_rows;
    }

    /**
     * Perform a Replace query
     *
     * @param string $sql
     * @return Returns FALSE on failure. Return TRUE for successful queries mysqli_query().
     */
    public static function replace($sql) {
        $stmt = self::$db->query($sql);
        if (!$stmt) {
            return false;
        }

        return $stmt;
    }

    /**
     * Perform a Delete query
     *
     * @param string $sql
     * @return Returns FALSE on failure. Return TRUE for successful queries mysqli_query().
     */
    public static function delete($sql) {
        $stmt = self::$db->query($sql);
        if (!$stmt) {
            return false;
        }

        return self::$db->connection->affected_rows;
    }

    /**
     * 获取数据库服务器时间
     *
     * @return int
     */
    public static function getTime() {
        return time();
    }

    /**
     * 查询数据库记录
     * @param $user_id
     * @param $dbTable
     * @param string $condition
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @param string $fields
     * @param string $groupBy
     * @return mixed
     */
    public static function select ( $dbTable,  $condition = '', $fields = '*', $orderBy = '', $limit = 0, $offset = 0, $groupBy = '' )
    {
        if ( is_array ( $fields ) ) {
            $fieldList = @implode( ',', $fields );
        } else {
            $fieldList = $fields;
        }
        if ( $condition != '' ) {
            $condition = "WHERE $condition";
        }
        $orderBy = trim ( $orderBy );
        if ( $orderBy != '' && !strstr ( strtoupper ( $orderBy ), 'ORDER BY' ) ) {
            $orderBy = "ORDER BY $orderBy";
        }

        $groupBy = trim ( $groupBy );
        if ( $groupBy != '' && !strstr ( strtoupper ( $groupBy ), 'GROUP BY' ) ) {
            $groupBy = "GROUP BY $groupBy";
        }

        $strSql = " SELECT $fieldList FROM $dbTable $condition $groupBy $orderBy";

        $limit = intval ( $limit );
        $offset = intval ( $offset );
        if ( $limit ) {
            $strSql .= " LIMIT $limit";
        }
        if ( $offset ) {
            $strSql .= " OFFSET $offset";
        }

        return self::query ($strSql);
    }

    /**
     * 查询数据库单条记录
     * @param $user_id
     * @param $dbTable
     * @param string $condition
     * @param string $orderBy
     * @param string $fields
     * @param string $groupBy
     * @return mixed
     */
    public static function selectOne ($dbTable, $condition = '', $fields = '*', $orderBy = '', $groupBy = '')
    {
        if ( is_array ( $fields ) ) {
            $fieldList = @implode( ',', $fields );
        } else {
            $fieldList = $fields;
        }
        if ( $condition != '' ) {
            $condition = "WHERE $condition";
        }
        $orderBy = trim ( $orderBy );
        if ( $orderBy != '' && !strstr ( strtoupper ( $orderBy ), 'ORDER BY' ) ) {
            $orderBy = "ORDER BY $orderBy";
        }

        $groupBy = trim ( $groupBy );
        if ( $groupBy != '' && !strstr ( strtoupper ( $groupBy ), 'GROUP BY' ) ) {
            $groupBy = "GROUP BY $groupBy";
        }

        $strSql = " SELECT $fieldList FROM $dbTable $condition $groupBy $orderBy";

        return  self::queryOne ($strSql);
    }

}