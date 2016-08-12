<?php
/**
 * File name: RedisUserRepository.php
 * Project: project1
 * PHP version 5
 * @category  PHP
 * @package   Project1\Infrastructure
 * @author    Don B. Stringham <stringhamdb@ldschurch.org>
 * @copyright 2016 © Intellectual Reserve, Inc.
 * @license   http://opensource.org/licenses/MIT MIT
 * @version   GIT: <git_id>
 * @link      http://donbstringham.us
 * $LastChangedDate$
 * $LastChangedBy$
 */

namespace Project1\Infrastructure;

use Predis\Client;
use Project1\Domain\StringLiteral;
use Project1\Domain\User;
use Project1\Domain\UserRepository;

/**
 * Class RedisUserRepository
 * @category  PHP
 * @package   Project1\Infrastructure
 * @author    Don B. Stringham <stringhamdb@ldschurch.org>
 * @link      http://donbstringham.us
 */
class RedisUserRepository implements UserRepository
{
    /** @var \Predis\Client() */
    protected $client;
    /**
     * RedisUserRepository constructor
     * @param \Predis\Client $newClient
     */
    public function __construct(Client $newClient)
    {
        $this->client = $newClient;
    }

    /**
     * @param \Project1\Domain\User $user
     * @return $this
     */
    public function add(User $user)
    {
        $this->client->set($user->getId()->toNative(), json_encode($user));
        return $this;
    }

    /**
     * @param \Project1\Domain\StringLiteral $id
     * @return $this
     */
    public function delete(StringLiteral $id)
    {
        $this->client->del([$id->toNative()]);
        return $this;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        foreach($this->client->keys('*') as $key){
            $data[$key] = json_decode($this->client->get($key), true);
        }
        return $data;
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByEmail(StringLiteral $fragment)
    {
        return $this->client->get($fragment->toNative());
    }

    /**
     * @param StringLiteral $id
     * @return \Project1\Domain\User
     * @throws \InvalidArgumentException
     */
    public function findById(StringLiteral $id)
    {
        /** @var string $json */
        $json = $this->client->get($id->toNative());
        $data = json_decode($json, true);
        $user = new User(
            new StringLiteral($data['email']),
            new StringLiteral($data['name']),
            new StringLiteral($data['username'])
        );
        $user->setId($data['id']);

        return $user;
    }

    /**
     * @param StringLiteral $fragment
     * @return array
     */
    public function findByName(StringLiteral $fragment)
    {
        return json_decode($this->client->get($fragment->toNative()));
    }

    /**
     * @param StringLiteral $username
     * @return array
     */
    public function findByUsername(StringLiteral $username)
    {
        return json_decode($this->client->get($username->toNative()));
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
        echo "Redis update called\n";
        $userdata = array(json_decode($this->client->get($user->getId())));
        $userdata['email'] = $user->getEmail();
        $userdata['name'] = $user->getName();
        $userdata['username'] = $user->getUsername();
        $this->client->mset($user->getId(), json_encode($userdata));
        return $this;
    }
}
