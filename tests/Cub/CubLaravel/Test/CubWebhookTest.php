<?php namespace Cub\CubLaravel\Test;

use Carbon\Carbon;
use Cub\CubLaravel\Test\Models\GroupMember;
use Cub\CubLaravel\Test\Models\Organization;
use Cub\CubLaravel\Test\Models\User;

class CubWebhookTest extends CubLaravelTestCase
{
    public function testWebhookUrlIsRegistered()
    {
        $actual = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => $this->details['id'],
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'username' => '',
            'last_login' => '2017-09-29T17:39:23Z',
            'deleted' => false,
        ]);
        self::assertEquals(200, $actual->getStatusCode());
    }

    public function testNewCubUserCreatesApplicationUser()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];
        $expectedCubId = 'usr_kjhdi7y3u4rkjsk';
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';
        $lastLogin = '2017-09-29T17:39:23Z';

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => $expectedCubId,
            'first_name' => $expectedFirstName,
            'last_name' => $expectedLastName,
            'email' => $expectedEmail,
            'username' => $expectedUsername,
            'last_login' => $lastLogin,
            'deleted' => false,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($expectedCubId)->first();
        self::assertEquals($expectedFirstName, $user->first_name);
        self::assertEquals($expectedLastName, $user->last_name);
        self::assertEquals($expectedEmail, $user->email);
        self::assertEquals($expectedUsername, $user->username);
        self::assertEquals(Carbon::parse($lastLogin)->setTimezone('UTC'), $user->last_login);
    }

    public function testNewDeletedCubUserCreatesDeletedApplicationUser()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];
        $expectedCubId = 'usr_kjhdi7y3u4rkjsk';
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.edu';
        $expectedUsername = 'lukie1';
        $lastLogin = '2017-09-29T17:39:23Z';

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => $expectedCubId,
            'first_name' => $expectedFirstName,
            'last_name' => $expectedLastName,
            'email' => $expectedEmail,
            'username' => $expectedUsername,
            'last_login' => $lastLogin,
            'deleted' => true,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($expectedCubId)->first();
        self::assertNull($user);
    }

    public function testUpdatedCubUserUpdatesApplicationUser()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';
        $lastLogin = '2017-09-29T17:39:23Z';

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => $this->details['id'],
            'first_name' => $expectedFirstName,
            'last_name' => $expectedLastName,
            'email' => $expectedEmail,
            'username' => $expectedUsername,
            'last_login' => $lastLogin,
            'deleted' => false,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($this->details['id'])->first();
        self::assertEquals($expectedFirstName, $user->first_name);
        self::assertEquals($expectedLastName, $user->last_name);
        self::assertEquals($expectedEmail, $user->email);
        self::assertEquals($expectedUsername, $user->username);
        self::assertEquals(Carbon::parse($lastLogin)->setTimezone('UTC'), $user->last_login);
    }

    public function testDeletedCubUserDeletesApplicationUser()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => $this->details['id'],
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'username' => '',
            'last_login' => '2017-09-29T17:39:23Z',
            'deleted' => true,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($this->details['id'])->first();
        self::assertNull($user);
    }

    public function testUpdatedCubObjectOtherThanUserReturnsCorrectResponse()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];

        $expectedName = 'Updated Testy';

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'organization',
            'id' => 'org_jhakjhwk4esjkjahs',
            'name' => $expectedName,
            'employees' => '',
            'tags' => '',
            'country' => '',
            'state' => '',
            'city' => '',
            'county' => '',
            'postal_code' => '',
            'address' => '',
            'phone' => '',
            'hr_phone' => '',
            'fax' => '',
            'website' => '',
            'created' => '',
            'logo' => '',
            'deleted' => false,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $org = Organization::whereCubId('org_jhakjhwk4esjkjahs')->first();
        self::assertEquals($expectedName, $org->name);
    }

    public function testUpdatedUntrackedCubObjectReturnsCorrectResponse()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'nothing_to_process']),
        ];

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'site',
            'id' => 'ste_jhakjhwk4esjkjahs',
            'deleted' => false,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());
    }

    public function testCreatedStudlyCapsObjectIsCreated()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'processed']),
        ];
        $expectedCubId = 'grm_kjhdi7y3u4rkjsk';
        $expectedGroup = 'grp_jhklu32yiweysih';
        $expectedMember = 'mbr_jhq2iu3wye8stkjkhlkd';
        $expectedAdmin = false;

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'groupmember',
            'id' => $expectedCubId,
            'group' => $expectedGroup,
            'member' => $expectedMember,
            'is_admin' => $expectedAdmin,
            'created' => '2017-09-29T17:39:23Z',
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());

        $groupMember = GroupMember::whereCubId($expectedCubId)->first();
        self::assertEquals($expectedGroup, $groupMember->group);
        self::assertEquals($expectedMember, $groupMember->member);
        self::assertEquals(0, $groupMember->is_admin);
    }

    public function testCubForbiddenReturnsSuccess()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'forbidden']),
        ];

        $response = $this->call('POST', config('cub.webhook_url'), [
            'object' => 'user',
            'id' => 'usr_98234wer9syd',
            'forbidden' => true,
        ]);

        self::assertEquals($expectedResponse['code'], $response->getStatusCode());
        self::assertEquals($expectedResponse['content'], $response->getContent());
    }
}
