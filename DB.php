<?php

require('./Helper.php');

class DB
{
    protected $pdo;

    function __construct()
    {
        $serverName = env("MYSQL_PORT_3306_TCP_ADDR", "localhost");
        $databaseName = env("MYSQL_INSTANCE_NAME", "homestead");
        $username = env("MYSQL_USERNAME", "homestead");
        $password = env("MYSQL_PASSWORD", "secret");

        // $serverName = env("MYSQL_PORT_3306_TCP_ADDR", "localhost");
        // $databaseName = env("MYSQL_INSTANCE_NAME", "app_contactus");
        // $username = env("MYSQL_USERNAME", "root");
        // $password = env("MYSQL_PASSWORD", "");
        try {
            $this->pdo = new PDO("mysql:host=$serverName;dbname=$databaseName", $username, $password);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 检测数据库是否存在表
            $isInstall = $this->pdo->query("SHOW TABLES like 'contacts';")
                ->rowCount();

            if (!$isInstall) {
                $sql = "
                        CREATE TABLE contacts (
                        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        phone VARCHAR(255) NOT NULL,
                        openid VARCHAR(255) NOT NULL )
                        ";
                $this->pdo->exec($sql);

                $sqlData = "
                        INSERT INTO `contacts` VALUES ('1', 'John', '188888888');
                        INSERT INTO `contacts` VALUES ('2', 'Bob', '166666666');
                        INSERT INTO `contacts` VALUES ('3', 'Zoe', '155555555');
                        ";
                $this->pdo->exec($sqlData);
            }


        } catch (PDOException $e) {
            echo "数据库链接失败: " . $e->getMessage();
            die();
        }
    }

    public function all()
    {
        return $this->pdo->query('SELECT * from contacts')
            ->fetchAll();
    }

    public function find($id)
    {
        return $this->pdo->query("SELECT * from contacts WHERE id = $id ")
            ->fetch();
    }

    public function remove($id)
    {
        return $this->pdo->exec("DELETE from contacts WHERE id = $id ");
    }

    public function add($name, $phone)
    {
        $sql = "INSERT INTO contacts ( name , phone ) VALUES ('$name','$phone')";

        return $this->pdo->exec($sql);
    }
    public function update_phone($phone,$openid){
         $sql = "UPDATE contacts SET phone=$phone WHERE openid='$openid'";

        return $this->pdo->exec($sql);
    }
    public function insert($name,$openid)
    {
        $sql = "INSERT INTO contacts ( name , openid ) VALUES ('$name','$openid')";
        return $this->pdo->exec($sql);
    }
    public function fetch_row($fields = '*' , $terms = '')
    {
        $query = "select {$fields} from contacts {$terms}";
        return $this->pdo->query($query)->fetch();
    }
}
