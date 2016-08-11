<?php
/**
 * File name: index.php
 * Project: project1
 * PHP version 5
 * @category  PHP
 * @author    donbstringham <donbstringham@gmail.com>
 * @copyright 2016 © donbstringham
 * @license   http://opensource.org/licenses/MIT MIT
 * @version   GIT: <git_id>
 * @link      http://donbstringham.us
 * $LastChangedDate$
 * $LastChangedBy$
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pimple\Container;
use Project1\Infrastructure\InMemoryUserRepository;
use Project1\Infrastructure\MysqlUserRepository;
use Project1\Infrastructure\RedisUserRepository;
use Project1\Domain\StringLiteral;
use Project1\Domain\User;

require_once __DIR__ . '/../vendor/autoload.php';

$dic = bootstrap();

$app = $dic['app'];

$app->before(function (Request $request) {
    $password = $request->getPassword();
    $username = $request->getUser();

    if ($username !== 'professor') {
        $response = new Response();
        $response->setStatusCode(401);

        return $response;
    }

    if ($password !== '1234pass') {
        $response = new Response();
        $response->setStatusCode(401);

        return $response;
    }

    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->get('/', function () {
    return '<h1>Welcome to the Final Project</h1>\n
            <h2>Deliverable 2!</h2>';
});

$app->get('/ping', function() use ($dic) {

   $response = new Response();

    $driver = $dic['db-driver'];
    if (!$driver instanceof \PDO) {
        $response->setStatusCode(500);
        $msg = ['msg' => 'could not connect to the database'];
        $response->setContent(json_encode($msg));

        return $response;
    }

    $repo = $dic['repo-mysql'];
    if (!$repo instanceof \Project1\Domain\UserRepository) {
        $response->setStatusCode(500);
        $msg = ['msg' => 'repository problem'];
        $response->setContent(json_encode($msg));

        return $response;
    }

    $response->setStatusCode(200);
    $msg = ['msg' => 'pong'];
    $response->setContent(json_encode($msg));

    return $response;

});

$app->get('/users', function () use ($dic) {
    $repo = $dic['repo-redis'];

    $response = new Response();
    $response->setStatusCode(200);
    $response->setContent(json_encode($repo->findAll()));

    return $response;
});

$app->get('/users/{id}', function ($id) use ($dic) {

    $repo = $dic['repo-redis'];
    $user = $repo->findById(new StringLiteral($id));

    $response = new Response();

    if ($user === null) {
        $response->setStatusCode(404);

        return $response;
    }

    $response->setStatusCode(200);
    $response->setContent(json_encode($user));

    return $response;
});

$app->delete('/users/{id}', function ($id) use ($dic) {

    $repo = $dic['repo-redis'];
    $result = $repo->delete(new StringLiteral($id));

    $response = new Response();

    if ($result === false) {
        $response->setStatusCode(500);
    } else {
        $response->setStatusCode(200);
    }

    return $response;
});

$app->post('/users', function (Request $request) use ($dic) {

    $repo = $dic['repo-redis'];
    $email = $request->get('email');
    $name = $request->get('name');
    $username = $request->get('username');

    $response = new Response();

    if(empty($email) || $email == "") {
        $response->setStatusCode(400);
        return $response;
    }
    elseif(empty($name) || $name == "") {
        $response->setStatusCode(400);
        return $response;
    }
    elseif(empty($username) || $username == "") {
        $response->setStatusCode(400);
        return $response;
    }

    $newUser = new User(new StringLiteral($email), new StringLiteral($name),
        new StringLiteral($username));
    $id = uniqid();
    $newUser->setId(new StringLiteral($id));
    $repo->add($newUser);
    $response->setStatusCode(201);
    return $response;
});

$app->put('/users/{id}', function ($id, Request $request) use ($dic) {

    $repo = $dic['repo-redis'];

    $response = new Response();

    $user = $repo->findById(new StringLiteral($id));
    if(empty($user) || $user == null) {
        $response->setStatusCode(400);
        return $response;
    }

    if(empty($request->get('email')) && empty($request->get('name')) &&
            empty($request->get('username'))) {
        $response->setStatusCode(400);
        return $response;
    }
    if(!empty($request->get('email'))) {
        $email = new StringLiteral($request->get('email'));
    }
    else{
        $email = $user->getEmail();
    }
    if(!empty($request->get('name'))) {
        $name = new StringLiteral($request->get('name'));
    }
    else{
        $name = $user->getName();
    }
    if(!empty($request->get('username'))) {
        $username = new StringLiteral($request->get('username'));
    }
    else{
        $username = $user->getUsername();
    }
    $newUser = new User($email, $name, $username);
    $newUser->setId($user->getId());
    $repo->update($newUser);
    $response->setStatusCode(200);
    return $response;
});

$app->run();


function bootstrap()
{
    $dic = new Container();

    $dic['app'] = function() {
        return new Silex\Application();
    };

    $dic['db-driver'] = function() {
        $host = 'mysqlserver';
        $db   = 'dockerfordevs';
        $user = 'root';
        $pass = 'docker';
        $charset = 'utf8';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, $user, $pass, $opt);
    };

    $dic['redis-client'] = function() {
        return new Predis\Client([
            'scheme' => 'tcp',
            'host'   => 'redisserver',
            'port'   => 6379,
        ]);
    };

    $pdo = $dic['redis-client'];
    $dic['repo-redis'] = function() use ($pdo) {
        return new RedisUserRepository($pdo);
    };

//    $pdo = $dic['db-driver'];
//    $dic['repo-mysql'] = function() use ($pdo) {
//        return new MysqlUserRepository($pdo);
//    };

    $dic['repo-mem'] = function() {
        $bill = new User(
            new StringLiteral('bill@email.com'),
            new StringLiteral('harris'),
            new StringLiteral('bharris')
        );
        $bill->setId(new StringLiteral('1'));

        $charlie = new User(
            new StringLiteral('charlie@email.com'),
            new StringLiteral('fuller'),
            new StringLiteral('cfuller')
        );
        $charlie->setId(new StringLiteral('2'));

        $dawn = new User(
            new StringLiteral('dawn@email.com'),
            new StringLiteral('brown'),
            new StringLiteral('dbrown')
        );
        $dawn->setId(new StringLiteral('3'));

        $repoMem = new InMemoryUserRepository();
        $repoMem->add($bill)->add($charlie)->add($dawn);

        return $repoMem;
    };

    return $dic;
}
