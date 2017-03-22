<?php

namespace InstagramAPI\Settings\Storage;

use PDO;

class MySQL implements \InstagramAPI\Settings\StorageInterface
{
    private $_sets;
    private $_pdo;
    private $_instagramUsername;

    public $dbTableName = 'user_settings';

    public function __construct(
        $username,
        $mysqlOptions)
    {
        $this->_instagramUsername = $username;

        if (isset($mysqlOptions['db_tablename'])) {
            $this->dbTableName = $mysqlOptions['db_tablename'];
        }

        if (isset($mysqlOptions['pdo'])) {
            // Pre-provided PDO object.
            $this->_pdo = $mysqlOptions['pdo'];
        } else {
            // We should connect for the user.
            $username = (is_null($mysqlOptions['db_username']) ? 'root' : $mysqlOptions['db_username']);
            $password = (is_null($mysqlOptions['db_password']) ? '' : $mysqlOptions['db_password']);
            $host = (is_null($mysqlOptions['db_host']) ? 'localhost' : $mysqlOptions['db_host']);
            $dbName = (is_null($mysqlOptions['db_name']) ? 'instagram' : $mysqlOptions['db_name']);
            $this->_connect($username, $password, $host, $dbName);
        }

        $this->_autoInstall();
        $this->_populateObject();
    }

    /**
     * Does a preliminary guess about whether we're logged in.
     *
     * The session it looks for may be expired, so there's no guarantee.
     *
     * @return bool
     */
    public function maybeLoggedIn()
    {
        return $this->get('id') !== null // Cannot use empty() since row can be 0.
            && !empty($this->get('username_id'))
            && !empty($this->get('token'));
    }

    public function get(
        $key,
        $default = null)
    {
        if ($key == 'sets') {
            return $this->_sets; // Return '_sets' itself which contains all data.
        }

        if (isset($this->_sets[$key])) {
            return $this->_sets[$key];
        }

        return $default;
    }

    public function set(
        $key,
        $value)
    {
        if ($key == 'sets' || $key == 'username') {
            return; // Don't allow writing to special 'sets' or 'username' keys.
        }

        $this->_sets[$key] = $value;
        $this->save();
    }

    public function save()
    {
        // Special key where we store what username these settings belong to.
        $this->_sets['username'] = $this->_instagramUsername;

        // Update if user already exists in db, otherwise insert.
        if (isset($this->_sets['id'])) {
            $sql = "update {$this->dbTableName} set ";
            $bindList[':id'] = $this->_sets['id'];
        } else {
            $sql = "insert into {$this->dbTableName} set ";
        }

        // Add all settings to storage.
        foreach ($this->_sets as $key => $value) {
            if ($key == 'id') {
                continue;
            }
            $fieldList[] = "{$key} = :{$key}";
            $bindList[":{$key}"] = $value;
        }

        $sql = $sql.implode(',', $fieldList).(isset($this->_sets['id']) ? ' where id=:id' : '');
        $std = $this->_pdo->prepare($sql);

        $std->execute($bindList);

        // Keep track of which database row id the user has been assigned as.
        if (!isset($this->_sets['id'])) {
            $this->_sets['id'] = $this->_pdo->lastinsertid();
        }
    }

    /**
     * @throws \InstagramAPI\Exception\SettingsException
     */
    private function _connect(
        $username,
        $password,
        $host,
        $dbName)
    {
        try {
            $pdo = new \PDO("mysql:host={$host};dbname={$dbName}", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->query('SET NAMES UTF8');
            $pdo->setAttribute(PDO::ERRMODE_WARNING, PDO::ERRMODE_EXCEPTION);
            $this->_pdo = $pdo;
        } catch (\PDOException $e) {
            throw new \InstagramAPI\Exception\SettingsException('Cannot connect to MySQL settings adapter.');
        }
    }

    private function _autoInstall()
    {
        // Detect the name of the MySQL database that PDO is connected to.
        $dbName = $this->_pdo->query('select database()')->fetchColumn();

        $std = $this->_pdo->prepare('SHOW TABLES WHERE tables_in_'.$dbName.' = :tableName');
        $std->execute([':tableName' => $this->dbTableName]);
        if ($std->rowCount()) {
            return true;
        }

        $this->_pdo->exec('CREATE TABLE `'.$this->dbTableName."` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NULL DEFAULT NULL,
            `username_id` BIGINT(20) NULL DEFAULT NULL,
            `devicestring` VARCHAR(255) NULL DEFAULT NULL,
            `device_id` VARCHAR(255) NULL DEFAULT NULL,
            `phone_id` VARCHAR(255) NULL DEFAULT NULL,
            `uuid` VARCHAR(255) NULL DEFAULT NULL,
            `token` VARCHAR(255) NULL DEFAULT NULL,
            `cookies` TEXT NULL,
            `date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login` BIGINT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_username` (`username`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
    }

    private function _populateObject()
    {
        $std = $this->_pdo->prepare("select * from {$this->dbTableName} where username=:username");
        $std->execute([':username' => $this->_instagramUsername]);
        $result = $std->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            foreach ($result as $key => $value) {
                $this->_sets[$key] = $value;
            }
        }
    }
}
