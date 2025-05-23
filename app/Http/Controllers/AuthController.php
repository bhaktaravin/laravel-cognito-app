<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\AuthenticatesUsers;
use Ellaisys\Cognito\Auth\ChangePasswords;
use Ellaisys\Cognito\Auth\RegisterMFA;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class AuthController extends BaseController
{
    use AuthenticatesUsers;
    use ChangePasswords;
    use RegisterMFA;


    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $attributes = [];

        $userFields = ['name', 'email'];

        foreach($userFields as $userField) {

            if ($request->$userField === null) {
                throw new \Exception("The configured user field $userField is not provided in the request.");
            }

            $attributes[$userField] = $request->$userField;
        }

        app()->make(CognitoClient::class)->register($request->email, $request->password, $attributes);

        event(new Registered($user = $this->create($request->all())));

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    } //Function ends


    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function getRemoteUser(Request $request)
    {
        try {
            $user =  auth()->guard('api')->user();
            if ($user) {
                $response = auth()->guard()->getRemoteUserData($user['email']);
                if ($response) {
                    //Check if request is json
                    if ($request->isJson()) {
                        $response = $response->toArray();
                        return new JsonResponse($response, 200);
                    } else {
                        return $response;
                    } //End if
                } else {
                    throw new BadRequestHttpException('ERROR_COGNITO_USER_DETAILS_NOT_FOUND');
                } //End if
            } else {
                throw new BadRequestHttpException('ERROR_COGNITO_USER_NOT_FOUND');
            } //End if
        } catch (NoLocalUserException $e) {
            $response = $this->createLocalUser($credentials);
        } catch (Exception $e) {
            throw $e;
        } //End try-catch
    } //Function ends


    public function webLogin(Request $request)
    {
        try
        {
            //Create credentials object
            $collection = collect($request->all());

            $response = $this->attemptLogin($collection, 'web');

            if ($response===true) {
                $request->session()->regenerate();

                return redirect(route('home'));

                   // ->intended('home');
            } else if ($response===false) {
                return redirect()
                    ->back()
                    ->withInput($request->only('username', 'remember'))
                    ->withErrors([
                        'username' => 'Incorrect username and/or password !!',
                    ]);
            } else {
                return $response;
            } //End if
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $response = $this->sendFailedLoginResponse($collection, $e);
            return $response->back()->withInput($request->only('username', 'remember'));
        } //try-catch ends
    } //Function ends


	/**
	 * Action to update the user password
	 *
	 * @param  \Illuminate\Http\Request  $request
	 */
    public function actionChangePassword(Request $request)
    {
		try
		{
            //Validate request
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password'  => 'string|min:8',
                'new_password' => 'required|confirmed|min:8',
            ]);
            $validator->validate();

            // Get Current User
            $userCurrent = auth()->guard('web')->user();

            if ($this->reset($request)) {
                return redirect(route('login'))->with('success', true);
            } else {
				return redirect()->back()
					->with('status', 'error')
					->with('message', 'Password updated failed');
			} //End if
        } catch(Exception $e) {
			$message = 'Error sending the reset mail.';
			if ($e instanceof ValidationException) {
                $message = $e->errors();
            } else if ($e instanceof CognitoIdentityProviderException) {
				$message = $e->getAwsErrorMessage();
			} else {
                //Do nothing
            } //End if

			return redirect()->back()
				->with('status', 'error')
				->with('message', $message);
        } //Try-catch ends
    } //Function ends

} //Class ends
