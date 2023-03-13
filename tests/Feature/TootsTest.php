<?php

use App\Models\Toot;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Notifications\TootLikeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('it lists toots', function() {

    $this->get('/api/toots')
         ->assertStatus(200)
         ->assertJsonCount(0, 'data');

    $user = User::factory()->create();
    $user->toots()->saveMany(Toot::factory(5)->make());
    $this->get('/api/toots')
         ->assertStatus(200) 
         ->assertJsonCount(5, 'data');
});


it('it creates a toot', function() {

    // Try it unauthenticated.
    $this->json('post', '/api/toots', [
        'text' => 'This is a toot.',
    ])->assertStatus(401);

    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $data = ['text' => 'This is a toot.'];

    $this->json('post', '/api/toots', $data)
         ->assertStatus(201)
         ->assertJsonFragment($data);

    $this->assertEquals(1, $user->toots()->count());
});


it('can follow a user', function() {
    $user = User::factory()->create();
    $userToFollow = User::factory()->create();
    $this->assertFalse($user->isFollowing($userToFollow));
    $this->assertFalse($userToFollow->isFollowedBy($user));
    
    $this->json('post', '/api/users/' . $userToFollow->id . '/follow')
         ->assertStatus(401);
    
    Sanctum::actingAs($user, ['*']);

    $this->json('post', '/api/users/' . $userToFollow->id . '/follow')
         ->assertStatus(200);

    $this->assertTrue($user->isFollowing($userToFollow));
    $this->assertTrue($userToFollow->isFollowedBy($user));
});


it('get followers', function() {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    Sanctum::actingAs($user, ['*']);
    $this->json('get', '/api/user/me')
         ->assertStatus(200)
         ->assertJsonFragment(['number_followers' => 0]);

    Sanctum::actingAs($follower);
    $this->json('post', '/api/users/' . $user->id . '/follow')
         ->assertStatus(200)
         ->assertJson(['data' => ['success' => true]]);

    $user->refresh();
    Sanctum::actingAs($user, ['*']);
    $this->json('get', '/api/user/me')
         ->assertStatus(200)
         ->assertJsonFragment(['number_followers' => 1]);
});


it('can reply to other toots', function() {
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    Sanctum::actingAs($user2, ['*']);
    $toot = $user->toots()->save(Toot::factory()->make());

    $this->json('post', '/api/toots', [
        'text' => 'This is a reply.',
        'reply_id' => $toot->id,
    ])->assertStatus(201);

    $this->json('post', '/api/toots', [
        'text' => 'This is another reply.',
        'reply_id' => $toot->id,
    ])->assertStatus(201);

    $user->refresh();
    Sanctum::actingAs($user, ['*']);
    $this->json('get', '/api/notifications')
         ->assertStatus(200)
         ->assertJsonCount(2, 'data');

    $this->json('post', '/api/notifications/mark-as-read')
         ->assertStatus(200)
         ->assertJson(['data' => ['success' => true]]);

    $this->assertEquals(0, $user->unreadNotifications()->count());

    $toot->refresh();
    $this->assertEquals(2, $toot->replies()->count());
    $this->assertEquals('This is a reply.', $toot->replies()->first()->text);
    $this->assertEquals('This is another reply.', $toot->replies()->offset(1)->first()->text);
});


it('can like a toot', function() {
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    Notification::fake();
    Sanctum::actingAs($user2, ['*']);
    $toot = $user->toots()->save(Toot::factory()->make());

    $this->json('post', '/api/toots/' . $toot->id . '/like')
         ->assertStatus(200)
         ->assertJson(['data' => ['success' => true]]);

    $toot->refresh();
    $this->assertTrue($toot->isLikedBy($user2));
    $this->assertEquals(1, $toot->likes()->count());
    $this->assertEquals(1, $toot->number_likes);

    $user->refresh();
    Notification::assertSentTo($user, TootLikeNotification::class);

    $this->json('post', '/api/toots/' . $toot->id . '/like')
         ->assertStatus(200)
         ->assertJson(['data' => ['success' => true]]);

    $toot->refresh();
    $this->assertFalse($toot->isLikedBy($user2));
    $this->assertEquals(0, $toot->likes()->count());
    $this->assertEquals(0, $toot->number_likes);
});


it('can show a single toot', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $toot = $user->toots()->save(Toot::factory()->make());
    $user2->toots()->save(Toot::factory()->make(['reply_id' => $toot->id]));

    $this->json('get', '/api/toots/' . $toot->id)
         ->assertStatus(200)
         ->assertJsonFragment(['text' => $toot->text])
         ->assertJsonCount(1, 'data.replies');
});


it('can show a currated list of toots', function () {

    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user2->toots()->createMany(Toot::factory(5)->make()->toArray());
    $user3->toots()->createMany(Toot::factory(2)->make()->toArray());
    Sanctum::actingAs($user, ['*']);

    // Not following anyone? Nothing here.
    $this->json('get', '/api/toots/timeline')
         ->assertStatus(200)
         ->assertJsonCount(0, 'data');

    // Follow a user, see their toots.
    $user->following()->save($user2);

    $this->json('get', '/api/toots/timeline')
         ->assertStatus(200)
         ->assertJsonCount(5, 'data');

    // Followed user likes something else.
    $user3->toots()->first()->likes()->create([
        'user_id' => $user2->id,
    ]);

    $this->json('get', '/api/toots/timeline')
         ->assertStatus(200)
         ->assertJsonCount(6, 'data');
});
