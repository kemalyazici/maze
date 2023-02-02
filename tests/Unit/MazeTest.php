<?php

namespace Tests\Unit;

use App\Models\User;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;



class MazeTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */

    //User post test
    public function test_maze_post()
    {
        $user = User::where('username','happyUser')->first();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => "Bearer ".$token])->json('POST','/api/maze',[
            'entrance' => 'H1',
            "gridSize"=> "8x8",
            "walls"=> ['G1','G2','H6']

        ]);

        $response->assertStatus(200);
    }

    //User get mazes test
    public function test_get_maze_minimum_solution(){
        $user = User::where('username','happyUser')->first();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => "Bearer ".$token])->json('GET','/api/maze/1/solution?steps=min');

        $response->assertStatus(200);
    }

    //User get mazes test
    public function test_get_maze_maximum_solution(){
        $user = User::where('username','happyUser')->first();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => "Bearer ".$token])->json('GET','/api/maze/1/solution?steps=max');
        $response->assertStatus(200);
    }


}
