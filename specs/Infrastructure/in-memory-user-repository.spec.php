<?php
/**
 * File name: in-memory-user-repository.spec.php
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

use Project1\Domain\StringLiteral;
use Project1\Domain\User;
use Project1\Infrastructure\InMemoryUserRepository;

describe('Project1\Infrastructure\InMemoryUserRepository', function () {
    beforeEach(function () {
        $this->repo = new InMemoryUserRepository();
        $user = new User(
            new StringLiteral('bill@email.com'),
            new StringLiteral('bill'),
            new StringLiteral('billharris')
        );
        $user->setId(new StringLiteral('1a'));
        $this->repo->add($user);
    });
    describe('->__construct()', function () {
        it('should return a InMemoryUserRepository object', function () {
            expect($this->repo)->to->be->instanceof(
                'Project1\Infrastructure\InMemoryUserRepository'
            );
        });
    });
    describe('->add()', function () {
        it('should return a valid User object', function () {
            $users = $this->repo->findByEmail(new StringLiteral('bill@email.com'));
            expect($users)->to->be->an('array');
            expect(1 === count($users))->to->be->true();
        });
    });
    describe('->delete("1a")', function () {
        it('should return a $this pointer after deleting the user', function () {
            $actual = $this->repo->delete(new StringLiteral('1a'));
            expect($actual)->to->be->instanceof(
                'Project1\Infrastructure\InMemoryUserRepository'
            );
            expect($this->repo->count())->to->equal(0);
        });
    });
    describe('->delete("")', function () {
        it('should return a $this pointer after deleting the user', function () {
            $actual = $this->repo->delete(new StringLiteral(''));
            expect($actual)->to->be->instanceof(
                'Project1\Infrastructure\InMemoryUserRepository'
            );
            expect($this->repo->count())->to->equal(1);
        });
    });
    describe('->findByEmail("bill@email.com")', function () {
        it('should return a valid User object', function () {
            $users = $this->repo->findByEmail(new StringLiteral('bill@email.com'));
            expect($users)->to->be->an('array');
            expect(1 === count($users))->to->be->true();
        });
    });
    describe('->findById("1a")', function () {
        it('should return a valid User object', function () {
            /** @var \Project1\Domain\User $user */
            $user = $this->repo->findById(new StringLiteral('1a'));
            expect($user)->to->be->instanceof('Project1\Domain\User');
            expect($user->getEmail()->equal(new StringLiteral('bill@email.com')))
                ->to->be->true();
        });
    });
    describe('->findById("2b")', function () {
        it('should return a null', function () {
            /** @var \Project1\Domain\User $user */
            $user = $this->repo->findById(new StringLiteral('2b'));
            expect($user)->to->be->a('NULL');
        });
    });
    describe('->findByName("bill")', function () {
        it('should return an array', function() {
            $users = $this->repo->findByName(new StringLiteral('bill'));
            expect($users)->to->be->an(array());
        });
    });
    describe('->findByUsername("billharris")', function() {
        it('should return a valid User object', function() {
           $user = $this->repo->findByUsername(new StringLiteral('billharris'));
           expect($user)->to->be->instanceof('Project1\Domain\User');
           expect($user->getUsername()->equal(new StringLiteral('billharris')));
        });
    });
});
