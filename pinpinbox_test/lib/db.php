<?php

/**
 * db
 * @author lion
 * 2015-11-04 Lion：依資料型態回傳，參考 http://stackoverflow.com/questions/20079320/php-pdo-mysql-how-do-i-return-integer-and-numeric-columns-from-mysql-as-int
 */
class db extends PDO
{
    function __construct($obj)
    {
		//echo '$obj:'.$obj['DSN'].'|||'.$obj['USER'].'|||'.$obj['PASSWORD'];
		
        try {
            return parent::__construct($obj['DSN'], $obj['USER'], $obj['PASSWORD'], [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8mb4\'',
            ]);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
		
		echo "<script>console.log(".json_encode("\lib\db.php:end(DB等基本操作變數)".date('m/d/Y h:i:s a', time())).");</script>";

 
    }

    function beginTransaction()
    {
        parent::beginTransaction();
    }

    function commit()
    {
        parent::commit();
    }

    function errorCode()
    {
        return parent::errorCode();
    }

    function errorInfo()
    {
        return parent::errorInfo();
    }

    function exec($sql)
    {
        $result = parent::exec($sql);
		
		echo 'exec($sql)='.$sql;

        if ($this->errorCode() != '00000') {
            \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, $this->errorInfo()[2] . ' in "' . $sql . '".');
        }

        return $result;
    }

    function fetchColumn($sql)
    {
        return $this->query($sql)->fetchColumn();
    }

    function fetch($sql, $fetchType = PDO::FETCH_ASSOC)
    {
        return $this->query($sql)->fetch($fetchType);
    }

    function fetchAll($sql, $fetchType = PDO::FETCH_ASSOC)
    {
        return $this->query($sql)->fetchAll($fetchType);
    }

    function query($sql)
    {
        $result = parent::query($sql);

        if ($this->errorCode() != '00000') {
            \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, $this->errorInfo()[2] . ' in "' . $sql . '".');
        }

        return $result;
    }

    function quote($var, $quote = true)
    {
        if ($var === null) {
            $return = 'NULL';
        } elseif ($var === true) {
            $return = 'TRUE';
        } elseif ($var === false) {
            $return = 'FALSE';
        } else {
            $return = (is_string($var) && $quote) ? parent::quote($var) : $var;
        }

        return $return;
    }

    function rollBack()
    {
        try {
            return parent::rollBack();
        } catch (PDOException $e) {
            \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, $e->getMessage() . '.');
        }
    }
}