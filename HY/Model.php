<?php
namespace HY;
use PDO;


class HY_MODEL{
    static public $pdo =false;
    static public $database;

}

class Model {
    public $pdo = false; 
    public $table;
    function __construct() {
        if(!HY_MODEL::$pdo){
            $a = microtime(TRUE);
            HY_MODEL::$pdo = new HY_SQL(array(
                // 必须配置项
                'database_type' => C("SQL_TYPE"),
                'database_name' => C("SQL_NAME"),
                'server' => C("SQL_IP"),
                'username' => C("SQL_USER"),
                'password' => C("SQL_PASS"),
                'charset' => C("SQL_CHARSET"),

                // 可选参数
                'port' => C("SQL_PORT"),

                // 可选，定义表的前缀
                'prefix' => strtolower(C("SQL_PREFIX")),

                // 连接参数扩展, 更多参考 http://www.php.net/manual/en/pdo.setattribute.php
                'option' => C("SQL_OPTION"),

            ));

            DEBUG_SQL::SQL_LOG('连接数据库 [耗时] ' .round(microtime(TRUE) - $a, 4).'ms');
        }

        $this->pdo = HY_MODEL::$pdo ;

        //parent::__construct();
        //if(!$this->con){
            //include HY_PATH. "medoo.php";
            //self::$database = new medoo();

            //self::$con = true;
        //}
        //HY_MODEL::init();
        //$this->con = HY_MODEL::$database;
        //echo ' 初始Model ';
    }

    // 插入数据 array('user'=>$user)
    public function insertAll($columns ,$datas){
        return $this->pdo->insertAll($this->table,$columns, $datas);
    }
    public function insert($data){
        return $this->pdo->insert($this->table, $data);
    }
    //查询数据 要查询的字段名.    查询的条件.
    public function select($join, $columns = null, $where = null){
        return $this->pdo->select($this->table,$join,$columns,$where);
    }
    public function update($data, $where=null){
        return $this->pdo->update($this->table,$data,$where);
    }
    public function delete($where){
        return $this->pdo->delete($this->table,$where);
    }
    public function get($join = null, $column = null, $where = null){
        return $this->pdo->get($this->table,$join,$column,$where);
    }
    public function find($join = null, $column = null, $where = null){
        return $this->pdo->get($this->table,$join,$column,$where);
    }
    public function replace($columns, $search = null, $replace = null, $where = null){
        return $this->pdo->replace($this->table,$columns, $search, $replace, $where);
    }
    public function has($join, $where=null){
        return $this->pdo->has($this->table,$join, $where);
    }
    public function count($join=null, $column=null, $where=null){
        return $this->pdo->count($this->table,$join, $column, $where);
    }
    public function max($join, $column = null, $where = null){
        return $this->pdo->max($this->table,$join, $column, $where);
    }
    public function min($join, $column = null, $where = null){
        return $this->pdo->min($this->table, $join, $column, $where);
    }
    public function avg($join, $column = null, $where = null){
        return $this->pdo->avg($this->table, $join, $column, $where);
    }
    public function sum($join, $column = null, $where = null){
        return $this->pdo->sum($this->table, $join, $column, $where);
    }
    public function action($actions){
        return $this->pdo->action($actions);
    }
    public function query($query){
        return $this->pdo->query($query);
    }
    public function quote($string){
        return $this->pdo->quote($string);
    }



}
