<?php namespace Cub\CubLaravel\Test;

use Cub\CubLaravel\Test\Models\User;

class CubWebhookTest extends CubLaravelTestCase
{
    /** @test */
    public function webhook_url_is_registered()
    {
        $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'username' => '',
                'deleted' => false,
            ]),
        ]);
    }

    /** @test */
    public function new_cub_user_creates_application_user()
    {
        $expectedResponse = [
            'code' => 201,
            'content' => json_encode(['message' => 'user_created']),
        ];
        $expectedCubId = 'usr_kjhdi7y3u4rkjsk';
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            json_encode([
                'object' => 'User',
                'id' => $expectedCubId,
                'first_name' => $expectedFirstName,
                'last_name' => $expectedLastName,
                'email' => $expectedEmail,
                'username' => $expectedUsername,
                'deleted' => false,
            ]),
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($expectedCubId)->first();
        $this->assertEquals($expectedFirstName, $user->first_name);
        $this->assertEquals($expectedLastName, $user->last_name);
        $this->assertEquals($expectedEmail, $user->email);
        $this->assertEquals($expectedUsername, $user->username);
    }

    /** @test */
    public function updated_cub_user_updates_application_user()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'user_updated']),
        ];
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => $expectedFirstName,
                'last_name' => $expectedLastName,
                'email' => $expectedEmail,
                'username' => $expectedUsername,
                'deleted' => false,
            ]),
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());

        $user = User::whereCubId($this->details['id'])->first();
        $this->assertEquals($expectedFirstName, $user->first_name);
        $this->assertEquals($expectedLastName, $user->last_name);
        $this->assertEquals($expectedEmail, $user->email);
        $this->assertEquals($expectedUsername, $user->username);
    }

    /** @test */
    public function deleted_cub_user_deletes_application_user()
    {
        $expectedResponse = [
            'code' => 200,
            'content' => json_encode(['message' => 'user_deleted']),
        ];

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'username' => '',
                'deleted' => true,
            ]),
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
            'content' => json_encode(['message' => 'nothing_to_update_or_create']),
        ];

        $response = $this->call('POST', $this->app['config']->get('cub::config.webhook_url'), [
            json_encode([
                'object' => 'Group',
                'id' => 'grp_jhakjhwk4esjkjahs',
                'deleted' => false,
            ]),
        ]);

        $this->assertEquals($expectedResponse['code'], $response->getStatusCode());
        $this->assertEquals($expectedResponse['content'], $response->getContent());
    }
}
