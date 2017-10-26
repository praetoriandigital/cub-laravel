<?php namespace Cub\CubLaravel\Test;

use Cub;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Test\Models\Country;
use Cub\CubLaravel\Test\Models\Member;
use Cub\CubLaravel\Test\Models\Organization;
use Cub\CubLaravel\Test\Models\State;
use Cub\CubLaravel\Test\Models\User;
use Cub_Object;
use Firebase\JWT\JWT;

class CubLaravelTest extends CubLaravelTestCase
{
    /** @test */
    public function application_user_is_returned_from_login()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = $login->getUser();

        $this->assertInstanceOf($this->app['config']->get('cub::config.maps.user.model'), $user);
        $this->assertEquals($user->email, $this->credentials['username']);
    }

    /** @test */
    public function application_user_is_returned_from_current_user()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = Cub::currentUser();

        $this->assertInstanceOf($this->app['config']->get('cub::config.maps.user.model'), $user);
        $this->assertEquals($login->getUser(), $user);
    }

    /** @test */
    public function cub_jwt_is_returned_from_current_token()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);

        $this->assertEquals($login->getToken(), Cub::currentToken());
    }

    /** @test */
    function logout_clears_current_user_and_token()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);

        $this->assertEquals($login->getUser(), Cub::currentUser());
        $this->assertEquals($login->getToken(), Cub::currentToken());

        Cub::logout();

        $this->assertNull(Cub::currentUser());
        $this->assertNull(Cub::currentToken());
    }

    /** @test */
    function check_is_accurate()
    {
        Cub::login($this->credentials['username'], $this->credentials['password']);

        $this->assertTrue(Cub::check());

        Cub::logout();

        $this->assertFalse(Cub::check());
    }

    /**
     * @test
     * @expectedException \Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException
     */
    public function exception_thrown_when_cub_user_is_not_application_user()
    {
        User::whereCubId($this->details['id'])->first()->delete();
        Cub::login($this->credentials['username'], $this->credentials['password']);
    }

    /** @test */
    public function application_user_is_returned_from_get_user_by_id()
    {
        $expected = User::whereCubId($this->details['id'])->first();
        $actual = Cub::getUserById($this->details['id']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException \Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException
     */
    public function exception_thrown_when_no_cub_user_id()
    {
        Cub::getUserById('');
    }

    /** @test */
    public function no_cub_user_id_exception_method_is_descript()
    {
        $expected = 'Object not found with Cub id {empty_string}';
        $actual = '';
        try {
            Cub::getUserById('');
        } catch (ObjectNotFoundByCubIdException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function application_user_is_returned_from_get_user_by_jwt()
    {
        $expected = User::whereCubId($this->details['id'])->first();

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = Cub::getUserByJWT($jwt);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function exception_thrown_when_no_jwt()
    {
        Cub::getUserByJWT('');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function exception_thrown_when_bad_jwt()
    {
        Cub::getUserByJWT('kjashdkfjahkjashdfklaj');
    }

    /** @test */
    public function get_expands_is_accurate()
    {
        $expected = 'organization,user,organization__state,organization__country';

        $object = Cub_Object::fromArray([
            'object' => 'member',
            'id' => 'mbr_jahu34iuy',
            'organization' => 'org_h34iweryiuklsj',
            'user' => 'usr_jh3iquy4iwey',
        ]);
        $actual = Cub::getObjectExpands($object);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function process_nonnested_object()
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

        $this->assertEquals($result->cub_id, $member->cub_id);
        $this->assertEquals($member->organization, $orgCubId);
        $this->assertEquals($member->user, $userCubId);
    }

    /** @test */
    public function process_nested_object()
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
            ],
        ]);

        $result = Cub::processObject($object);

        $member = Member::whereCubId($memberCubId)->first();
        $user = User::whereCubId($userCubId)->first();
        $org = Organization::whereCubId($orgCubId)->first();
        $country = Country::whereCubId($countryCubId)->first();
        $state = State::whereCubId($stateCubId)->first();

        $this->assertEquals($result->cub_id, $member->cub_id);
        $this->assertNotNull($user);
        $this->assertEquals($user->cub_id, $userCubId);
        $this->assertNotNull($org);
        $this->assertEquals($org->cub_id, $orgCubId);
        $this->assertNotNull($country);
        $this->assertEquals($country->cub_id, $countryCubId);
        $this->assertNotNull($state);
        $this->assertEquals($state->cub_id, $stateCubId);
    }
}
