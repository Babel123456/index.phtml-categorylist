<?php

/**
 * 2016-06-20 Lion:
 *     各 Model 自訂的函式類型
 *         1. ableToXXX: 處理參數檢查, 僅有這類函式才處理為 array_encode_return, 不置放於 Model 其他函式中, 而在 controller 流程中操作
 *         2. getXXX: 回傳數組
 * 2015-09-22 Lion:
 *     (!!不再開發此寫法, 預備棄用)各 Model 自定義的搜尋由於目前設計變數有覆寫情形，故需在第一位置調用(ex. Model('xxx')->customizeFunction->where->...)，
 *     但所下條件會合併(ex. 外部有下 order，自定義的搜尋沒有，產生的 sql 會有 order；外部下 column A，自定義的搜尋下 column B，產生的 sql 會有 column A & B)，
 *     而在自定義的搜尋裡再使用 Model 的部分必需擺在前頭
 */
class Model
{
    public static $switch_memcache = false;
    private static $switch_debug = false;
    private static $switch_transaction = false;
    private static $separate1 = '<#>';
    static $database_instance = [];
    static $memcache_instance = [];

    protected $column = [];
    protected $join = [];
    protected $where = [];
    protected $group = [];
    protected $having = [];
    protected $order = [];
    protected $limit;
    protected $lock;

    function __construct()
    {
    }

    function __construct_child()
    {
        self::setDatabase($this->database);

        if (self::$switch_memcache && !isset(self::$memcache_instance[$this->memcache])) self::$memcache_instance[$this->memcache] = new \Core\Memcache(Core::$_config['CONFIG']['MC'][$this->memcache]);
    }

    function __destruct()
    {
    }

    function add(array $param)
    {
        if ($param) {
            self::$database_instance[$this->database]->exec($this->add_logic($param));

            $id = (int)self::$database_instance[$this->database]->lastInsertId();//沒有 AUTO_INCREMENT 的話，會得到 0

            return $id;
        }
    }

    function add_logic(array $param, $replace = false)
    {
        $a_column = [];
        $s_value = null;
        switch (array_depth($param)) {
            case 1:
                $a_column = array_keys($param);
                $s_value = '(' . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $param)) . ')';
                break;

            case 2:
                $a_column = array_keys(reset($param));
                $a_value = [];
                foreach ($param as $v0) {
                    $a_value[] = '(' . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $v0)) . ')';
                }
                $s_value = implode(',', $a_value);
                break;

            default:
                throw new Exception('Unknown case');
                break;
        }
        $sql = 'Insert into ' . DB_PREFIX . $this->database . '.' . $this->table . ' (' . implode(',', $a_column) . ') values ' . $s_value;
        if ($replace) {
            $tmp0 = [];
            foreach ($a_column as $v0) {
                $tmp0[] = $v0 . '=values(' . $v0 . ')';
            }
            $sql .= ' on duplicate key update ' . implode(',', $tmp0);
        }

        if (self::$switch_debug) {
            echo '#[sql]<br>' . $sql . ';<br>';
            self::$switch_debug = false;
        }//debug

        return $sql;
    }

    function beginTransaction()
    {
        if (self::$switch_transaction === false) {
            $tmp0 = [];

            foreach (Core::$_config['CONFIG']['DB'] as $database => $v_0) {
                if (!in_array($v_0['DSN'], $tmp0)) {
                    $tmp0[] = $v_0['DSN'];

                    self::setDatabase($database);

                    self::getDatabase($database)->beginTransaction();
                }
            }

            self::$switch_transaction = true;
        }
    }

    function cachekey_encode($sql, $expire, $debug)
    {
        return implode(self::$separate1, [$sql, $expire, $debug]);
    }

    function column(array $column = null)
    {
        if ($column) {
            $column = array_filter($column, function ($v0) {
                return $v0 !== null;
            });
            $this->column = array_merge($this->column, $column);
        }

        return $this;
    }

    function commit()
    {
        $tmp0 = [];

        foreach (self::$database_instance as $k0 => $v0) {
            if (!in_array(Core::$_config['CONFIG']['DB'][$k0]['DSN'], $tmp0)) {
                $tmp0[] = Core::$_config['CONFIG']['DB'][$k0]['DSN'];

                $v0->commit();
            }
        }

        self::$switch_transaction = false;
    }

    function connection_id()
    {
        return self::$database_instance[$this->database]->fetchColumn('SELECT CONNECTION_ID()');
    }

    function debug()
    {
        self::$switch_debug = true;

        return $this;
    }

    function delete()
    {
        $sql = 'Delete from ' . DB_PREFIX . $this->database . '.' . $this->table;
        if (!empty($this->where)) $sql .= ' where ' . implode(' and ', $this->where);
        $this->reset();
        self::$database_instance[$this->database]->exec($sql);

        if (self::$switch_debug) {
            echo '#[sql]<br>' . $sql . ';<br>';
            self::$switch_debug = false;
        }//debug

        return true;
    }

    function edit(array $param)
    {
        if ($param) {
            $tmp0 = [];
            foreach ($param as $k0 => $v0) {
                $quote = true;
                if (is_array($v0)) list($v0, $quote) = $v0;

                $tmp0[] = $k0 . '=' . $this->quote($v0, $quote);
            }
            $sql = 'Update ' . DB_PREFIX . $this->database . '.' . $this->table . ' set ' . implode(',', $tmp0);
            if (!empty($this->where)) $sql .= ' where ' . implode(' and ', $this->where);

            self::$database_instance[$this->database]->exec($sql);
        }

        $this->reset();

        if (self::$switch_debug) {
            echo '#[sql]<br>' . $sql . ';<br>';
            self::$switch_debug = false;
        }//debug

        return true;
    }

    /**
     * when case 格式範例
     *     key=>array(
     *         'when'=>array(
     *             array(FIELD, OPERATOR, VALUE, THEN)
     *             [, array(...)]
     *         ),
     *         'else'=>ELSE
     *     )
     * @param array $param
     * @return boolean
     */
    function editByCase(array $param)
    {   
	    if ($param) {
            $sql = 'Update ' . DB_PREFIX . $this->database . '.' . $this->table;
            $tmp1 = [];
            foreach ($param as $k1 => $v1) {
                if ($v1 == null) {
                    $tmp1[] = $k1 . '=\'\'';
                } else {
                    if (is_array($v1)) {
                        $a_when = [];
                        foreach ($v1['when'] as $v2) {
                            list($field, $operator, $value, $then) = $v2;
                            $a_when[] = 'when ' . $field . ' ' . $operator . ' ' . $this->quote($value) . ' then ' . $this->quote($then);
                        }
                        $tmp1[] = $k1 . '= case ' . implode(' ', $a_when) . ' else ' . $v1['else'] . ' end';
                    } else {
                        $tmp1[] = $k1 . '=' . $this->quote($v1);
                    }
                }
            }
            $sql .= ' set ' . implode(',', $tmp1);
            if (!empty($this->where)) $sql .= ' where ' . implode(' and ', $this->where);

            if (self::$switch_debug) {
                echo '#[sql]<br>' . $sql . ';<br>';
                self::$switch_debug = false;
            }//debug

            $this->reset();
            self::$database_instance[$this->database]->exec($sql);
        }

        return true;
    }

    function fetch($expire = null)
    {
        return $this->fetch_logic(__FUNCTION__, $expire);
    }

    function fetchAll($expire = null)
    {
        return $this->fetch_logic(__FUNCTION__, $expire);
    }

    function fetchColumn($expire = null)
    {
        return $this->fetch_logic(__FUNCTION__, $expire);
    }

    function fetchEnum($field)
    {
        preg_match("/^enum\(\'(.*)\'\)$/", self::$database_instance[$this->database]->fetch('SHOW COLUMNS FROM ' . DB_PREFIX . $this->database . '.' . $this->table . ' WHERE Field = ' . self::$database_instance[$this->database]->quote($field))['Type'], $matches);

        return $matches ? explode("','", $matches[1]) : null;
    }

    function fetch_logic($fetch_case, $expire)
    {
        $sql = 'Select';
        $sql .= ($this->column) ? ' ' . implode(',', array_map('trim', $this->column)) : ' ' . $this->table . '.*';
        $sql .= ' from ' . DB_PREFIX . $this->database . '.' . $this->table;
        if (!empty($this->join)) $sql .= ' ' . implode(' ', $this->join);
        if (!empty($this->where)) $sql .= ' where ' . implode(' and ', $this->where);
        if (!empty($this->group)) $sql .= ' group by ' . implode(',', array_map('trim', $this->group));
        if (!empty($this->having)) $sql .= ' HAVING ' . implode(' ', $this->having);
        if (!empty($this->order)) $sql .= ' order by ' . implode(',', $this->order);
        if (!empty($this->limit)) $sql .= ' limit ' . $this->limit;
        if (!empty($this->lock)) $sql .= ' ' . $this->lock;

        if (self::$switch_memcache && $expire === null) $expire = self::$memcache_instance[$this->memcache]->expire;

        $a_debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0];
        $debug = $a_debug['file'] . ' on line ' . $a_debug['line'];
        $cachekey = $this->cachekey_encode($sql, $expire, $debug);

        if (self::$switch_debug) {
            echo '#[sql]<br>' . $sql . ';<br>';
            self::$switch_debug = false;
        }//debug

        $data = null;
        if (self::$switch_memcache && self::$memcache_instance[$this->memcache]->exists($cachekey) && $this->lock === null) {
            $data = self::$memcache_instance[$this->memcache]->get($cachekey);
        } else {
            $data = self::$database_instance[$this->database]->$fetch_case($sql);
            if (self::$switch_memcache) self::$memcache_instance[$this->memcache]->set($cachekey, $data, $expire);
        }

        $this->reset();

        return $data;
    }

    static function getDatabase($database)
    {
		return isset(self::$database_instance[$database]) ? self::$database_instance[$database] : null;
    }

    function group(array $group = null)
    {
        if ($group) {
            $group = array_filter($group, function ($v0) {
                return $v0 !== null;
            });
            $this->group = array_merge($this->group, $group);
        }

        return $this;
    }

    function having(array $having)
    {
        foreach ($having as $v_0) {
            if (is_array($v_0)) {
                if (empty($v_0)) continue;

                list ($column, $operator, $value) = $v_0;

                $quote = isset($v_0[3]) ? $v_0[3] : true;

                switch (strtolower($operator)) {
                    case '=':
                    case '!=':
                    case '>=':
                    case '>':
                    case '<=':
                    case '<':
                    case 'is':
                    case 'like':
                    case 'rlike':
                        $string = $column . " " . $operator . " " . $this->quote($value, $quote);
                        break;

                    case 'between':
                        $string = $column . " " . $operator . " " . $this->quote($value[0], $quote) . " AND " . $this->quote($value[1], $quote);
                        break;

                    case 'in':
                        if (empty($value)) {
                            $string = '0 = 1';
                        } else {
                            $value = array_unique($value);

                            $string = $column . " " . $operator . " (" . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $value, array_fill(0, count($value), $quote))) . ")";
                        }
                        break;

                    case 'not in':
                        if (empty($value)) {
                            $string = '1 = 1';
                        } else {
                            $value = array_unique($value);

                            $string = $column . " " . $operator . " (" . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $value, array_fill(0, count($value), $quote))) . ")";
                        }
                        break;

                    default:
                        \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, 'Unknown case. "' . $operator . '" of operator.');
                        break;
                }
            } else {
                if (trim($v_0) === '') continue;

                $string = $v_0;
            }

            $this->having[] = $string;
        }

        return $this;
    }

    function join(array $join = null)
    {
        if ($join) {
            foreach ($join as $v1) {
                list($join_case, $join_table, $join_condition) = $v1;

                //if (!in_array($join_table, $this->join_table)) throw new Exception('Unknown join table');//2016-08-19 Lion: 預計移除這行

                //2017-09-14 Lion: 相容
                switch ($join_table) {
                    case 'businessuser':
                        $string_0 = '\\' . $join_table . '\\' . 'Model';

                        $join_model = new $string_0;
                        break;

                    case 'split':
                        $string_0 = '\\Model\\' . $join_table;

                        $join_model = new $string_0;
                        break;

                    default:
                        $join_model = Model($join_table);
                        break;
                }

                $this->join[] = strtolower(trim($join_case)) . ' ' . DB_PREFIX . $join_model->database . '.' . trim($join_table) . ' ' . trim($join_condition);
            }
        }

        return $this;
    }

    function kill($connection_id)
    {
        self::$database_instance[$this->database]->exec('KILL ' . $connection_id);
    }

    function lastInsertId()
    {
        return self::$database_instance[$this->database]->lastInsertId();
    }

    function limit($limit = null)
    {
        if ($limit) $this->limit = str_replace(' ', '', $limit);
        return $this;
    }

    function lock($lock)
    {
        $this->lock = strtolower(trim($lock));
        return $this;
    }

    static function newly()
    {
        return new static();
    }

    function order(array $order = null)
    {
        if ($order) {
            foreach ($order as $k1 => $v1) {
                $this->order[] = trim($k1) . ' ' . strtolower(trim($v1));
            }
        }
        return $this;
    }

    function quote($value, $quote = true)
    {
        return self::$database_instance[$this->database]->quote($value, $quote);
    }

    function replace(array $param)
    {
        if ($param) self::$database_instance[$this->database]->exec($this->add_logic($param, true));
    }

    function reset()
    {
        $this->column = [];
        $this->join = [];
        $this->where = [];
        $this->group = [];
        $this->having = [];
        $this->order = [];
        $this->limit = null;
        $this->lock = null;
    }

    function rollBack()
    {
        $tmp0 = [];

        foreach (self::$database_instance as $k0 => $v0) {
            if (!in_array(Core::$_config['CONFIG']['DB'][$k0]['DSN'], $tmp0)) {
                $tmp0[] = Core::$_config['CONFIG']['DB'][$k0]['DSN'];

                $v0->rollBack();
            }
        }

        self::$switch_transaction = false;
    }

    static function setDatabase($database)
    {
		echo '$database='.$database.'</br>';
        if (!isset(self::$database_instance[$database])) {
            self::$database_instance[$database] = new db(Core::$_config['CONFIG']['DB'][$database]);
        }
    }

    function truncate()
    {
        self::$database_instance[$this->database]->exec('TRUNCATE TABLE ' . DB_PREFIX . $this->database . '.' . $this->table);
    }

    function where(array $where = null)
    {
        if ($where) {
            foreach ($where as $v1) {
                list($filters, $logic) = $v1;
                $tmp2 = [];
                foreach ($filters as $v2) {
                    list($field, $operator, $value) = $v2;
                    $field = trim($field);
                    $quote = isset($v2[3]) ? $v2[3] : true;
                    switch (strtolower(trim($operator))) {
                        case '=':
                        case '!=':
                        case '>=':
                        case '>':
                        case '<=':
                        case '<':
                        case 'is':
                        case 'is not':
                        case 'like':
                        case 'rlike':
                            $tmp2[] = $field . " " . $operator . " " . $this->quote($value, $quote);
                            break;

                        case 'between':
                            $tmp2[] = $field . " " . $operator . " " . $this->quote($value[0], $quote) . " and " . $this->quote($value[1], $quote);
                            break;

                        case 'in':
                            if (empty($value)) {
                                $tmp2[] = '0 = 1';
                            } else {
                                $value = array_unique($value);

                                $tmp2[] = $field . " " . $operator . " (" . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $value, array_fill(0, count($value), $quote))) . ")";
                            }
                            break;

                        case 'not in':
                            if (empty($value)) {
                                $tmp2[] = '1 = 1';
                            } else {
                                $value = array_unique($value);

                                $tmp2[] = $field . " " . $operator . " (" . implode(',', array_map([self::$database_instance[$this->database], 'quote'], $value, array_fill(0, count($value), $quote))) . ")";
                            }
                            break;

                        default:
                            \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, 'Unknown case. "' . $operator . '" of operator.');
                            break;
                    }
                }
                if (count($tmp2) > 1) {
                    switch ($logic) {
                        case 'AND':
                        case 'and':
                            $this->where[] = implode(' ' . $logic . ' ', $tmp2);
                            break;

                        case 'or':
                            $this->where[] = '(' . implode(' ' . $logic . ' ', $tmp2) . ')';
                            break;

                        default:
                            \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, 'Unknown case of where\'s logic');
                            break;
                    }
                } else {
                    $this->where[] = $tmp2[0];
                }
            }
        }

        return $this;
    }
}