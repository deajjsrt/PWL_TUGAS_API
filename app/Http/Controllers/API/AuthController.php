<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class AuthController.
 * 
 * @author Yonathan <yonathan.422023017@civitas.ukrida.ac.id>
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/user/register",
     *      tags={"user"},
     *      summary="Register new user & get token",
     *      operationId="register",
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *          @OA\JsonContent()
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Request body description",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/User",
     *              example={"name": "Yonathan", "email": "yonathan0@gmail.com", "password": "dea123", "password_confirmation": "dea123"}
     *           ),
     *      )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
            $request['password'] = Hash::make($request['password']);
            $request['remember_token'] = \Illuminate\Support\Str::random(10);
            $user = User::create($request->toArray());
            $token = $user->createToken('All Yours')->accessToken; //string inside createToken is the token name
            return response()->json(
                array('name' => $request->name, 'email' => $request->get('email'), 'token' => $token),
                200
            );
        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data: {$exception->getMessage()}");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/user/login",
     *      tags={"user"},
     *      summary="Log in to existing user & get token",
     *      operationId="login",
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful",
     *          @OA\JsonContent()
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Request body description",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/User",
     *              example={"email": "yonathan0@gmail.com", "password": "dea123"}
     *          ),
     *      )
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
            $user = User::where('email', $request->email)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('All Yours')->accessToken;
                    return response()->json(
                        array('email' => $request->get('email'), 'token' => $token),
                        200
                    );
                } else {
                    return response()->json(array('message' => 'Password mismatch'), 400);
                }
            } else {
                return response()->json(array('message' => 'User does not exist'), 400);
            }
        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data: {$exception->getMessage()}");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/user/logout",
     *      tags={"user"},
     *      summary="Log out & destroy self token",
     *      operationId="logout",
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="path",
     *          description="User Email",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      security={{"passport_token_ready":{},"passport":{}}}
     * )
     */
    public function logout (Request $request)
    {
        try {
            $token = $request->user()->token();
            $token->revoke();
            return response()->json(array('message' => 'You have been successfully logged out!'), 200);
        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data: {$exception->getMessage()}");
        }
    }
}
