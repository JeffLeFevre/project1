<?php
/**
 * File name: InMemoryUserRepository.php
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
 * Class InMemoryUserRepository
 * @category  PHP
 * @package   Project1\Infrastructure
 * @author    donbstringham <donbstringham@gmail.com>
 * @link      http://donbstringham.us
 */
class InMemoryUserRepository implements UserRepository
{
    /** @var array */
    protected $storage;

    /**
     * InMemoryUserRepository constructor
     */
    public function __construct()
    {
        $this->storage = [];
    }

    /**
     * @param \Project1\Domain\User $newUser
     * @return $this
     */
    public function add(User $newUser)
    {
        $this->storage[] = $newUser;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->storage);
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByEmail(StringLiteral $fragment)
    {
        $responseStorage = [];

        /** @var \Project1\Domain\User $user */
        foreach ($this->storage as $user) {
            if ($fragment->equal($user->getEmail())) {
                $responseStorage[] = $user;
            }
        }

        return $responseStorage;
    }

    /**
     * @param StringLiteral $id
     * @return \Project1\Domain\User
     */
    public function findById(StringLiteral $id)
    {
        /** @var \Project1\Domain\User $user */
        foreach($this->storage as $user) {
           if ($id->equal($user->getId())) {
               return $user;
           }
        }

        return null;
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByName(StringLiteral $fragment)
    {
        $users = [];
        foreach($this->$this->storage as $user) {
            if (strpos($fragment, $user->getName()) !== false) {
                $users[] = $user;
            }
        }
        if(empty($users)) {
            return null;
        }

        return $users;
    }

    /**
     * @param StringLiteral $username
     * @return array
     */
    public function findByUsername(StringLiteral $username)
    {
        foreach($this->storage as $user) {
            if ($username->equal($user->getUsername())) {
                return $user;
            }
        }
        return null;
    }

    /**
     * @param \Project1\Domain\StringLiteral $id
     * @return $this
     */
    public function delete(StringLiteral $id)
    {
        for($i = 0; $i < $this->count(); $i++) {
            if ($id->equal($this->storage[$i]->getId())) {
                unset($this->storage[$i]);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        return true;
    }

    /**
     * @param \Project1\Domain\User $user
     * @return $this
     */
    public function update(User $user)
    {
        $this->delete($user->getId());
        $this->add($user);

        return $this;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->storage;
    }
}
