<?php

namespace App\Http\Controllers;


use Auth;;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Ellaisys\Cognito\AwsCognitoClaim;
use Ellaisys\Cognito\Auth\AuthenticatesUsers;
use Ellaisys\Cognito\Auth\ChangePasswords;
use Ellaisys\Cognito\Auth\RegistersUsers;
//use Ellaisys\Cognito\Auth\RegisterMFA;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;

use Exception;
use Illuminate\Validation\ValidationException;
use Ellaisys\Cognito\Exceptions\AwsCognitoException;
use Ellaisys\Cognito\Exceptions\NoLocalUserException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ApiAuthController extends Controller
{
    use AuthenticateUsers;
    use ChangePassword;
    use RegistersMFA;


    public function actionRegister(Request $request)
    {
        return $this->register($request);
    }

    public function actionLogin(Request $request)
    {
        $collection = collect($request->all());

        if($claim = $this->attemptLogin($collection, 'api', 'username', 'password', true))
        {
            if($claim instanceof AwsCognitoClaim)
            {
                return $claim->getData();
            } else {
                return $claim;
            }
        }
    }

    protected function getRemoteUser()
    {
        try {
            $user = auth()->guard('api')->user();
            $response = auth()->guard()->getRemoteUserData($user['email']);

        }catch (NoLocalUserException $e) {
            $response = $this->createLocalUser($credentials);
        } catch (Exception $e){
            return $e;
        }

        return $response;
    }

    public function actionChangePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'requered|email',
                'password' => 'required',
                'new_password' => 'required|confirmed',
            ]);
            $validator->validate();

            $userCurrent = auth()->guard('web')->user();

            if ($this->reset($request))
            {
                return redirect(route('login'))->with('success', true);
            } else {
                return redirect()->back()
                -with('status', 'error')
                ->with('message', 'Password update failed');
            }
        } catch(Exception $e){
            $message = 'Error sending the reset mail';
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
        }
    }
}
