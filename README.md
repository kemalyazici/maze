# Maze Application

This is a maze controller for a Laravel application that allows users to create mazes, get a list of their created mazes, and find solutions to the mazes.

### Installation

Edit .env file

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your-db
DB_USERNAME=username
DB_PASSWORD=password
```

Go to your project folder in Terminal screen.

- <code>composer update</code>
- <code>php artisan migrate</code>
- <code>php artisan jwt:secret</code>


### Features
- Middleware for authentication (using auth:api and json)
- Validation for user input using the Laravel Validator class
- Ability to store maze information (entrance, grid size, and walls) for each user
- Ability to retrieve a paginated list of mazes created by the user
- Ability to find solutions for mazes by creating a base, calculating exit positions, and finding the solution path


### API Endpoints

- POST /api/login: Logs in a user using the AuthController class and its login method.
- POST /api/register: Registers a new user using the AuthController class and its register method.
- POST /api/maze: Creates a new maze using the MazeController class and its createMaze method.
- GET /api/maze: Returns a list of all mazes using the MazeController class and its getMazes method.
- GET /api/maze/{id}/solution?steps={min|max}: Returns the solution for a specific maze using the MazeController class and its solution method.

### Unit Test for Maze Application
The class has three test methods, each testing a different functionality:

- test_maze_post(): This method tests the ability to post a maze by a user. A user with username happyUser is fetched and a JWT token is generated using the JWTAuth facade. Then a POST request is sent to the endpoint /api/maze with the required parameters in the body. The response is then checked for a status code of 200.
- test_get_maze_minimum_solution(): This method tests the ability to retrieve the minimum solution of a maze. A user with username happyUser is fetched and a JWT token is generated using the JWTAuth facade. Then a GET request is sent to the endpoint /api/maze/1/solution?steps=min with the Authorization header set to the JWT token. The response is then checked for a status code of 200.
- test_get_maze_maximum_solution(): This method tests the ability to retrieve the maximum solution of a maze. A user with username happyUser is fetched and a JWT token is generated using the JWTAuth facade. Then a GET request is sent to the endpoint /api/maze/1/solution?steps=max with the Authorization header set to the JWT token. The response is then checked for a status code of 200.

<p style="text-align: center">
<code>php artisan test --filter=MazeTest</code>
</p>

<p style="text-align: center">
<img src="https://maze.kemalyazici.com/test.png"/>
</p>
