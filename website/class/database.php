<?php
class DataBase {
    private $PDO;
    private $connected = false; // 添加属性来记录连接状态

    public function __construct() {
        try {
            $this->PDO = new PDO("mysql:host=" . DB_HOST . "; port=" . DB_PORT . "; dbname=" . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->connected = true; // 连接成功
        } catch (PDOException $ex) {
            // 仅记录连接失败的状态，不终止脚本
            $this->connected = false;
            // 可以选择记录错误信息，例如使用error_log($ex->getMessage());
        }
    }

    public function isConnected() {
        return $this->connected;
    }

    public function select($query, $bindings = []) {
        if (!$this->isConnected()) {
            return false; // 如果没有连接成功，则直接返回false
        }
        $STH = $this->PDO->prepare($query);
        $STH->execute($bindings);
        return $STH->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query($query, $bindings = []) {
        if (!$this->isConnected()) {
            return false; // 如果没有连接成功，则直接返回false
        }
        $STH = $this->PDO->prepare($query);
        return $STH->execute($bindings);
    }
}
