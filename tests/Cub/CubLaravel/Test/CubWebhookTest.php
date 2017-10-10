<?php namespace Cub\CubLaravel\Test;

use Carbon\Carbon;
use Cub\CubLaravel\Test\Models\Organization;
use Cub\CubLaravel\Test\Models\User;

class CubWebhookTest extends CubLaravelTestCase
{
    /** @test */
    public function webhook_url_is_registered()
    {
        $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'User',
            'id' => $this->details['id'],
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'username' => '',
            'last_login' => '',
            'deleted' => false,
        ]);
    }

    /** @test */
    public function new_cub_user_creates_application_user()
    {
        $expectedResponse = [
            'code' => 201,
            'content' => json_encode(['message' => 'created']),
        ];
        $expectedCubId = 'usr_kjhdi7y3u4rkjsk';
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';
        $lastLogin = '2017-09-29T17:39:23Z';

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'User',
            'id' => $expectedCubId,
            'first_name' => $expectedFirstName,
            'last_name' => $expectedLastName,
            'email' => $expectedEmail,
            'username' => $expectedUsername,
            'last_login' => $lastLogin,
            'deleted' => false,
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($expectedCubId)->first();
        $this->assertEquals($expectedFirstName, $user->first_name);
        $this->assertEquals($expectedLastName, $user->last_name);
        $this->assertEquals($expectedEmail, $user->email);
        $this->assertEquals($expectedUsername, $user->username);
        $this->assertEquals(Carbon::parse($lastLogin)->setTimezone('UTC'), $user->last_login);
    }

    /** @test */
    public function updated_cub_user_updates_application_user()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'updated']),
        ];
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';
        $lastLogin = '2017-09-29T17:39:23Z';

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'User',
            'id' => $this->details['id'],
            'first_name' => $expectedFirstName,
            'last_name' => $expectedLastName,
            'email' => $expectedEmail,
            'username' => $expectedUsername,
            'last_login' => $lastLogin,
            'deleted' => false,
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($this->details['id'])->first();
        $this->assertEquals($expectedFirstName, $user->first_name);
        $this->assertEquals($expectedLastName, $user->last_name);
        $this->assertEquals($expectedEmail, $user->email);
        $this->assertEquals($expectedUsername, $user->username);
        $this->assertEquals(Carbon::parse($lastLogin)->setTimezone('UTC'), $user->last_login);
    }

    /** @test */
    public function deleted_cub_user_deletes_application_user()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'deleted']),
        ];

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'User',
            'id' => $this->details['id'],
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'username' => '',
            'last_login' => '2017-09-29T17:39:23Z',
            'deleted' => true,
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($this->details['id'])->first();
        $this->assertNull($user);
    }

    /** @test */
    public function updated_cub_object_other_than_user_returns_correct_response()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'updated']),
        ];

        $expectedName = 'Updated Testy';

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'Organization',
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

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $org = Organization::whereCubId('org_jhakjhwk4esjkjahs')->first();
        $this->assertEquals($expectedName, $org->name);
    }

    /** @test */
    public function updated_untracked_cub_object_returns_correct_response()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'nothing_to_update_or_create']),
        ];

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            'object' => 'Group',
            'id' => 'grp_jhakjhwk4esjkjahs',
            'deleted' => false,
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());
    }
}
