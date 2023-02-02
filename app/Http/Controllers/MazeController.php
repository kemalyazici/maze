<?php

namespace App\Http\Controllers;

use App\Models\Maze;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class MazeController extends Controller
{
    public function __construct() {
        $this->middleware(['auth:api','json']);
    }

    private $strArr = ['A'=> 1,'B'=> 2,'C' => 3,'D' => 4,'E' => 5,'F' => 6,'G' =>7,'H' => 8,'I' => 9,'J' => 10,'K' => 11,'L' => 12,'M' => 13,'N' => 14,'O' => 15,'P' => 16,'Q' => 17,'R' => 18,'S' => 19,'T' => 20,'U' => 21,'V' => 22,'W' => 23,'X' => 24,'Y' => 25,'Z'=>26];

    // Check grid size limit (according to alphabet)
    public function dimensionCheck($size){
        $x = explode('x',$size);
        return $x[0]<27;
    }

    // Gives every grid a number
    /* For example 4x4
           * |A1:0 |B1:1 |C1:2 |D1:3 |
           * |A2:4 |B2:5 |C2:6 |D2:7 |
           * |A3:18|B3:9 |C3:10|D3:11|
           * |A4:12|B4:13|C4:14|D4:15|
           * */
    public function createBase($gridSize){
        $grid = explode('x',$gridSize);
        $map = array();
        for($i=1;$i<=$grid[1];$i++){
            for($j=1;$j<=$grid[0];$j++){
                $key = array_search($j,$this->strArr);
                $map[] = $key.$i;
            }
        }
        return $map;
    }


    // User creates maze
    public function createMaze(Request $request){
        $data['entrance'] = $request->entrance;
        $data['gridSize'] = $request->gridSize;
        $data['walls'] = json_encode($request->walls);
        $rules = [
            'entrance' => 'required|string|min:2',
            'gridSize' => 'required|string',
            'walls' => 'required|string'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
           auth()->user()->mazes()->create([
               'entrance' => $data['entrance'],
               'gridSize' => $data['gridSize'],
               'walls' => $data['walls']
           ]);
           return response($request->all(),200);
        } else {
            return response(['message' =>'Validation error'],400);
        }
    }

    //User gets mazes those are he/she added
    public function getMazes(){
        $getMazes = auth()->user()->mazes()->paginate(50);
        if(count($getMazes)){
            $mazes = array();
            foreach ($getMazes as $k=>$maze){
                $mazes[$k] = $maze;
                $mazes[$k]->walls = json_decode($maze->walls, true);
            }
            return response($mazes,200);
        }else{
            return response(['message'=>auth()->user()->username.' did not create any maze yet!'],400);
        }

    }


    // Solution
    public function solution($id){
        $maze = Maze::find($id);
        if(isset($maze)){
            $entrance = $maze->entrance;
            $walls = json_decode($maze->walls, true);
            $gridSize = $maze->gridSize;
            if(!$this->dimensionCheck($gridSize)){
                return response(['message'=>'Grid size too big!'],400);
            }

            // Create baze and give every grid a number
            $base = $this->createBase($gridSize);

            //Find entrance number value
            $entranceNum = array_search($entrance,$base);

            //Calculate exits according to bottom corners
            $corners = explode('x',$gridSize);
            $last = intval($corners[0])*intval($corners[1]);
            $exitRight = $last-1;
            $exitLeft = $last-$corners[0];

            $wallsNum = array();
            if(count($walls)>0){
                //Find walls spesific location numbers
                foreach ($walls as $wall){
                    $wallsNum[] = array_search($wall,$base);
                }
            }

            //Check whether exits closed with walls
            if(in_array($exitRight,$wallsNum) && in_array($exitLeft,$wallsNum)){
                return response(['message' => 'All exits are blocked by walls'],400);
            }

            @$steps = $_GET['steps'];

            if($steps == "min" || $steps=="max"){
                if($steps=="min"){
                    //SHORTEST PATH
                    //Check Left exit
                    $shortLeft = $this->shortest_path($entranceNum,$exitLeft,$gridSize,$wallsNum);
                    //Check Right exit
                    $shortRight = $this->shortest_path($entranceNum,$exitRight,$gridSize,$wallsNum);
                    if(count($shortLeft)<count($shortRight)){
                        $short = count($shortLeft)!=0 ? $shortLeft : $shortRight;
                    }else{
                        $short = count($shortRight)!=0 ? $shortRight : $shortLeft;
                    }
                    //If array has zero value, there is no exit
                    if(count($short) == 0){
                        return response(['message'=>'Exit is blocked by a Wall'],400);
                    }

                    $shortPath = [];
                    // Find codes of path numbers
                    foreach ($short as $s){
                        $shortPath[] = $base[$s];
                    }

                    return response(['path' => $shortPath],200);

                }else{
                   //LONGEST PATH
                    //check left exit
                    $longLeft = $this->longest_path($entranceNum,$exitLeft,$gridSize,$wallsNum);
                    //check Right exit
                    $longRight = $this->longest_path($entranceNum,$exitRight,$gridSize,$wallsNum);
                    $long = count($longRight)>count($longLeft) ? $longRight: $longLeft;

                    //If array has zero value, there is no exit
                    if(count($long) == 0){
                        return response(['message'=>'Exit is blocked by a Wall'],400);
                    }

                    $longPath = [];
                    // Find codes of path numbers
                    foreach ($long as $l){
                        $longPath[] = $base[$l];
                    }
                    return response(['path' => $longPath],200);
                }
            }else{
                return response(['message'=>'There is no valid "steps" option']);
            }




        }else{
            return response(['message'=>'Invalid id'],400);
        }

    }


    function shortest_path($start, $end, $gridSize, $walls){
        // If end point is already closed, return empty
        if(in_array($end, $walls)){
            return [];
        }

        // Find row and col size
        list($row, $col) = explode('x', $gridSize);

        // Initialize the queue with the start node and an array containing only the start node (path from start)
        $queue = array(array($start, array($start)));

        // create an array for visited nodes
        $visited = array();

        // While the queue is not empty
        while(count($queue) > 0) {

            // Shift the first item in the queue and store it in variables $node and $path
            list($node, $path) = array_shift($queue);

            // If the current node is the end node, return the path
            if($node == $end){
                return $path;
            }

            // If the current node has already been visited or is a wall, skip to the next iteration of the loop
            if(in_array($node, $visited) || in_array($node, $walls)){
                continue;
            }

            // Add the current node to the list of visited nodes
            array_push($visited, $node);

            // Calculate the row and column of the current node
            $row_node = floor($node / $col);
            $col_node = $node % $col;

            // If the current node is not in the first row, add the node above it to the queue
            if($row_node > 0){
                array_push($queue, array($node - $col, array_merge($path, array($node - $col))));
            }

            // If the current node is not in the last row, add the node below it to the queue
            if($row_node < $row - 1) {
                array_push($queue, array($node + $col, array_merge($path, array($node + $col))));
            }

            // If the current node is not in the first column, add the node to the left to the queue
            if($col_node > 0) {
                array_push($queue, array($node - 1, array_merge($path, array($node - 1))));
            }

            // If the current node is not in the last column, add the node to the right to the queue
            if($col_node < $col - 1) {
                array_push($queue, array($node + 1, array_merge($path, array($node + 1))));
            }
        }

        // If the queue is empty, return an empty array-- means exit closed
        return [];
    }

    function longest_path($start, $end, $gridSize, $walls) {
        // Split the gridSize into row and col variables
        list($row, $col) = explode('x', $gridSize);

        // Initialize the queue with the start node and an array containing only the start node (path from start)
        $queue = array(array($start, array($start)));

        // Keep track of nodes visited to avoid revisiting
        $visited = array();

        // Store the longest path found
        $longest_path = array();

        // Continue until there are no more nodes to visit
        while (!empty($queue)) {

            // Pop a node and its path from the queue
            list($node, $path) = array_pop($queue);

            // If the node is the end, check if the path is the longest found so far
            if ($node == $end) {
                if (count($path) >= count($longest_path)) {
                    $longest_path = $path;
                }
            }

           // If the node has been visited or is a wall, skip it
            if (in_array($node, $visited) || in_array($node, $walls)) {
                continue;
            }

           // Mark the node as visited
            array_push($visited, $node);

            // Calculate the row and col of the current node
            $row_node = intval($node / $col);
            $col_node = $node % $col;

            // Check if it's possible to move to the top, if so add the node to the queue
            if ($row_node > 0 && !in_array($node - $col, $walls)) {
                array_push($queue, array($node - $col, array_merge($path, array($node - $col))));
            }

            // Check if it's possible to move to the bottom, if so add the node to the queue
            if ($row_node < $row - 1 && !in_array($node + $col, $walls)) {
                array_push($queue, array($node + $col, array_merge($path, array($node + $col))));
            }

           // Check if it's possible to move to the left, if so add the node to the queue
            if ($col_node > 0 && !in_array($node - 1, $walls)) {
                array_push($queue, array($node - 1, array_merge($path, array($node - 1))));
            }

            // Check if it's possible to move to the right, if so add the node to the queue
            if ($col_node < $col - 1 && !in_array($node + 1, $walls)) {
                array_push($queue, array($node + 1, array_merge($path, array($node + 1))));
            }
        }

        //Return the longest path found
        return $longest_path;
    }

}
