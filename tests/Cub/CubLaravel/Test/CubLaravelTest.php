<?php namespace Cub\CubLaravel\Test;

use Cub;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Test\Models\Country;
use Cub\CubLaravel\Test\Models\Member;
use Cub\CubLaravel\Test\Models\Organization;
use Cub\CubLaravel\Test\Models\State;
use Cub\CubLaravel\Test\Models\User;
use Cub_Object;
use Exception;
use Firebase\JWT\JWT;

class CubLaravelTest extends CubLaravelTestCase
{
    public function testValidateReturnsTrueWithValidCredentials()
    {
        $actual = Cub::validate($this->credentials['username'], $this->credentials['password']);

        self::assertTrue($actual);
    }

    public function testValidateReturnsFalseWithInvalidCredentials()
    {
        $actual = Cub::validate('not_the_right_username', $this->credentials['password']);

        self::assertFalse($actual);
    }

    public function testApplicationUserIsReturnedFromLogin()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = $login->getUser();

        self::assertInstanceOf(config('cub.maps.user.model'), $user);
        self::assertEquals($user->email, $this->credentials['username']);
    }

    public function testApplicationUserIsReturnedFromCurrentUser()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = Cub::currentUser();

        self::assertInstanceOf(config('cub.maps.user.model'), $user);
        self::assertEquals($login->getUser(), $user);
    }

    public function testCubJwtIsReturnedFromCurrentToken()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);

        self::assertEquals($login->getToken(), Cub::currentToken());
    }

    public function testLogoutClearsCurrentUserAndToken()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);

        self::assertEquals($login->getUser(), Cub::currentUser());
        self::assertEquals($login->getToken(), Cub::currentToken());

        Cub::logout();

        self::assertNull(Cub::currentUser());
        self::assertNull(Cub::currentToken());
    }

    public function testCheckIsAccurate()
    {
        Cub::login($this->credentials['username'], $this->credentials['password']);

        self::assertTrue(Cub::check());

        Cub::logout();

        self::assertFalse(Cub::check());
    }

    public function testExceptionThrownWhenCubUserIsNotApplicationUser()
    {
        $this->expectException(ObjectNotFoundByCubIdException::class);
        User::whereCubId($this->details['id'])->first()->delete();
        Cub::login($this->credentials['username'], $this->credentials['password']);
    }

    public function testApplicationUserIsReturnedFromGetUserById()
    {
        $expected = User::whereCubId($this->details['id'])->first();
        $actual = Cub::getUserById($this->details['id']);

        self::assertEquals($expected, $actual);
    }

    public function testExceptionThrownWhenNoCubUserId()
    {
        $this->expectException(ObjectNotFoundByCubIdException::class);
        Cub::getUserById('');
    }

    public function testNoCubUserIdExceptionMethodIsDescript()
    {
        $expected = 'Object not found with Cub id {empty_string}';
        $actual = '';
        try {
            Cub::getUserById('');
        } catch (ObjectNotFoundByCubIdException $e) {
            $actual = $e->getMessage();
        }
        self::assertEquals($expected, $actual);
    }

    public function testApplicationUserIsReturnedFromGetUserByJwt()
    {
        $expected = User::whereCubId($this->details['id'])->first();

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = Cub::getUserByJWT($jwt);

        self::assertEquals($expected, $actual);
    }

    public function testExceptionThrownWhenNoJwt()
    {
        $this->expectException(Exception::class);
        Cub::getUserByJWT('');
    }

    public function testExceptionThrownWhenBadJwt()
    {
        $this->expectException(Exception::class);
        Cub::getUserByJWT('kjashdkfjahkjashdfklaj');
    }

    public function testGetExpandsIsAccurate()
    {
        $expected = 'organization,user,group_membership,organization__state,organization__country,group_membership__group,group_membership__member';

        $object = Cub_Object::fromArray([
            'object' => 'member',
            'id' => 'mbr_jahu34iuy',
            'organization' => 'org_h34iweryiuklsj',
            'user' => 'usr_jh3iquy4iwey',
        ]);
        $actual = Cub::getObjectExpands($object);

        self::assertEquals($expected, $actual);
    }

    public function testProcessNonnestedObject()
    {
        $memberCubId = 'mbr_jieuydijhadksj3473';
        $orgCubId = 'org_j237tausyg2hadkqwh';
        $userCubId = 'usr_2378qwyakhlsjhglu23';
        $object = Cub_Object::fromArray([
            'object' => 'member',
            'id' => $memberCubId,
            'organization' => $orgCubId,
            'user' => $userCubId,
        ]);

        $result = Cub::processObject($object);

        $member = Member::whereCubId($memberCubId)->first();

        self::assertEquals($result->cub_id, $member->cub_id);
        self::assertEquals($member->organization, $orgCubId);
        self::assertEquals($member->user, $userCubId);
    }

    public function testProcessNestedObject()
    {
        $memberCubId = 'mbr_jieuydijhadksj3473';
        $orgCubId = 'org_j237tausyg2hadkqwh';
        $stateCubId = 'stt_j237tausyg2hadkqwh';
        $countryCubId = 'cry_j237tausyg2hadkqwh';
        $userCubId = 'usr_2378qwyakhlsjhglu23';
        $object = Cub_Object::fromArray([
            'object' => 'member',
            'id' => $memberCubId,
            'organization' => [
                'object' => 'organization',
                'id' => $orgCubId,
                'name' => 'Org Name',
                'state' => [
                    'object' => 'state',
                    'id' => $stateCubId,
                    'name' => 'State',
                ],
                'country' => [
                    'object' => 'country',
                    'id' => $countryCubId,
                    'name' => 'Country',
                ],
            ],
            'user' => [
                'object' => 'user',
                'id' => $userCubId,
                'username' => 'Username',
                'first_name' => 'First',
                'last_name' => 'Last',
                'email' => 'joe@email.dev',
                'last_login' => '2017-09-29T17:39:23Z',
            ],
        ]);

        $result = Cub::processObject($object);

        $member = Member::whereCubId($memberCubId)->first();
        $user = User::whereCubId($userCubId)->first();
        $org = Organization::whereCubId($orgCubId)->first();
        $country = Country::whereCubId($countryCubId)->first();
        $state = State::whereCubId($stateCubId)->first();

        self::assertEquals($result->cub_id, $member->cub_id);
        self::assertNotNull($user);
        self::assertEquals($user->cub_id, $userCubId);
        self::assertNotNull($org);
        self::assertEquals($org->cub_id, $orgCubId);
        self::assertNotNull($country);
        self::assertEquals($country->cub_id, $countryCubId);
        self::assertNotNull($state);
        self::assertEquals($state->cub_id, $stateCubId);
    }

    public function testCurrentOrganizationWithNoCurrentOrgIdReturnsNull()
    {
        Cub::setCurrentOrganizationId(null);
        self::assertNull(Cub::currentOrganization());
    }

    /** @test */
    public function testCurrentOrganizationWithErroneousCurrentOrgIdReturnsNull()
    {
        Cub::setCurrentOrganizationId('kajhsdkfahdk');
        self::assertNull(Cub::currentOrganization());
    }

    public function testCurrentOrganizationWithErroneousCurrentOrgIdThrowsError()
    {
        $this->expectException(ObjectNotFoundByCubIdException::class);
        Cub::setCurrentOrganizationId('org_kajhsdkfahdk');
        self::assertNull(Cub::currentOrganization());
    }

    public function testCurrentOrganizationWithAccurateCookieReturnsOrganization()
    {
        $organization = Organization::create([
            'cub_id' => 'org_jhakjhwsfdgdssk4esjkjahs',
            'name' => 'Foo',
        ]);

        self::assertNull(Cub::currentOrganization());

        Cub::setCurrentOrganizationId($organization->cub_id);
        self::assertEquals(Cub::currentOrganization()->cub_id, $organization->cub_id);
    }
}
