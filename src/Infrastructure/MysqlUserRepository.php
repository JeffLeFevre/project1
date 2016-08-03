<?php
/**
 * File name: MysqlUserRepository.php
 * Project: project1
 * PHP version 5
 * @category  PHP
 * @package   Project1\Infrastructure
 * @author    donbstringham <donbstringham@gmail.com>
 * @copyright 2016 © donbstringham
 * @license   http://opensource.org/licenses/MIT MIT
 * @version   GIT: <git_id>
 * @link      http://donbstringham.us
 * $LastChangedDate$
 * $LastChangedBy$
 */

namespace Project1\Infrastructure;

use Project1\Domain\StringLiteral;
use Project1\Domain\User;
use Project1\Domain\UserRepository;

/**
 * Class MysqlUserRepository
 * @category  PHP
 * @package   Project1\Infrastructure
 * @author    donbstringham <donbstringham@gmail.com>
 * @link      http://donbstringham.us
 */
class MysqlUserRepository implements UserRepository
{
    /** @var \PDO */
    protected $driver;

    /**
     * MysqlUserRepository constructor
     * @param \PDO $driver
     */
    public function __construct(\PDO $driver)
    {
        $this->driver = $driver;
    }

    // For returning responses from the DB (SELECT)
    /**
     * @param String $query
     * @param bool $all
     */
    private function fetchSelect(String $query, bool $all)
    {
        $result = [];
        try {

            $sql = $this->driver->query($query);
            if($all){
                $result = $sql->fetchAll();
            }
            else{
                $result = $sql->fetch();
            }

        } catch (\PDOException $e) {

            if ($e->getCode() === 1062) {
                //not sure here either
                return;
            } else {
                throw $e;
            }
        }
        
        return $result;
    }

    // This is for any SQL that isn't going to return something from the DB
    /**
     * @param String $query
     */
    private function runSql(String $query)
    {
        try {

            $this->driver->exec($query);

        } catch (\PDOException $e) {

            if ($e->getCode() === 1062) {
                //not sure what to do here
                return;
            } else {
                throw $e;
            }
        }

    }

    // Can return either an array or a user based on the bool value
    /**
     * @param String $query
     * @param bool $arr
     * @return array|mixed|User|void
     */
    private function getUserData(String $query, bool $arr)
    {
        $result = $this->fetchSelect($query, false);
        if(!$arr) {
            $user = new User(new StringLiteral($result["email"]),
                new StringLiteral($result["name"]), new StringLiteral($result["user_name"]));
            $user->setId($result["id"]);
            return $user;
        }
        return $result;
    }

    /**
     * @param \Project1\Domain\User $user
     * @return $this
     * @throws \PDOException
     */
    public function add(User $user)
    {
        $data = json_decode(json_encode($user));
        $this->runSql(
            'INSERT INTO Users (user_name, name, email) VALUES ("' .$data->username.'", 
                "'.$data->name.'", "'.$data->email.'");'
        );
        return $this;
    }

    /**
     * @param \Project1\Domain\StringLiteral $id
     * @return $this
     */
    public function delete(StringLiteral $id)
    {
        $this->runSql('DELETE FROM Users WHERE id = ' .$id.';');
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $all = $this->fetchSelect('SELECT * FROM Users', true);
        return json_encode($all);
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByEmail(StringLiteral $fragment)
    {
        $query = 'SELECT id, email, name, user_name FROM Users WHERE email = "' .$fragment.'";';
        return $this->getUserData($query, true);
    }

    /**
     * @param StringLiteral $id
     * @return \Project1\Domain\User
     */
    public function findById(StringLiteral $id)
    {
        $query = 'SELECT id, email, name, user_name FROM Users WHERE id = ' .(string) $id.';';
        echo "";
        return $this->getUserData($query, false);
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByName(StringLiteral $fragment)
    {
        $query = 'SELECT id, email, name, user_name FROM Users WHERE name = ' .$fragment.';';
        return $this->getUserData($query, true);
    }

    /**
     * @param StringLiteral $username
     * @return array
     */
    public function findByUsername(StringLiteral $username)
    {
        $query = 'SELECT id, email, name, user_name FROM Users WHERE user_name = ' .$username.';';
        return $this->getUserData($query, true);
    }

    /**
     * @return bool
     */
    public function save()
    {
        // Since stuff is auto-committed to the DB I don't think this is used.
        return true;
    }

    /**
     * @param \Project1\Domain\User $user
     * @return $this
     */
    public function update(User $user)
    {
        $query = 'UPDATE Users SET email="' .$user->getEmail().'",
        name="'.$user->getName().'", user_name="'.$user->getUsername().'" 
        WHERE id='.$user->getId().';';

        return $this->runSql($query);
    }
}
