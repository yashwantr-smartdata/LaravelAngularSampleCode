<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Document;
use App\PasswordReset;
use Validator;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Repository\UserRepository;
use Auth;
use Mail;
use App\Mail\ForgotPassword;
use App\Mail\Verification;
use Illuminate\Support\Facades\Crypt;
use App\MobileVerification;
use App\Country;
use DB;
use Carbon\Carbon;
use File;

class UserController extends Controller
{

    private $profile_picture_upload_path;

    /**
    * Construction function
    *
    * @return 
    *
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function __construct(Request $request, Client $Client){
        $this->request = $request;

        $this->client = $Client;

        $this->secret = env('OAUTH_SECRET');

        $this->profile_picture_upload_path = '/uploads/images/profile_picture/';
    }

    /**
    * API Function to Authenticate User through Google Social Login
    *
    * @return status, token, message
    * Created By: Yashwant Rautela
    * Created At: 24July2019 
    */
    public function socialLoginWithGoogle(UserRepository $userRepository){
        try{
            $validator = Validator::make($this->request->all(), [
                'email' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                'authToken' => 'required',
                'id' => 'required',
                'idToken' => 'required',
                'photoUrl' => 'required',
                'provider' => 'required'
            ]);

            if ($validator->fails()) {
                # code...
                throw new Exception("Required parameter missing", 1);
            }

            $user = $userRepository->getData(['email'=>$this->request->email],'first',[],0);

            if(!isset($user)){
                $password = $this->request->firstName.'@12345';
                $newUser = $userRepository->createData([
                    'first_name'=> $this->request->firstName,
                    'last_name'=> $this->request->lastName,
                    'email'=> $this->request->email,
                    'password' => bcrypt($password),
                    'user_role' => 'user',
                    'is_active' => 1,
                    'social_auth_token'=> $this->request->authToken,
                    'social_id'=> $this->request->id,
                    'social_id_token'=> $this->request->idToken,
                    'photo_url'=> $this->request->photoUrl,
                    'provider'=> $this->request->provider
                ]);
                
                $res = Auth::attempt(['email'=>$this->request->email, 'password'=> $password]);
                $authUser = Auth::user();
                $token =  $authUser->createToken($this->secret)->accessToken;
                return response()->json([
                    'status'=>'success',
                    'token'=>$token,
                    'data' => $authUser,
                    'message' => 'Login Successful'
                ],200);
            }
            else{
                Auth::attempt(['email'=>$this->request->email, 'password'=> ($user['first_name'].'@12345')]);
                $authUser = Auth::user();
                $token =  $authUser->createToken($this->secret)->accessToken;
                return response()->json([
                    'status'=>'success',
                    'token'=>$token,
                    'data' => $authUser,
                    'message' => 'Login Successful'
                ],200);
            }
        }
        catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
    * API Function to Authenticate User through Facebook Social Login
    *
    * @return status, token, message
    * Created By: Yashwant Rautela
    * Created At: 25July2019 
    */
    public function socialLoginWithFb(UserRepository $userRepository){
        try{
            $validator = Validator::make($this->request->all(), [
                'email' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                'authToken' => 'required',
                'id' => 'required',
                'photoUrl' => 'required',
                'provider' => 'required'
            ]);

            if ($validator->fails()) {
                # code...
                throw new Exception("Required parameter missing", 1);
            }


            $user = $userRepository->getData(['email'=>$this->request->email],'first',[],0);

            
            if(!isset($user)){
                $password = $this->request->firstName.'@12345';
                $newUser = $userRepository->createData([
                    'first_name'=> $this->request->firstName,
                    'last_name'=> $this->request->lastName,
                    'email'=> $this->request->email,
                    'password' => bcrypt($password),
                    'user_role' => 'user',
                    'is_active' => 1,
                    'social_auth_token'=> $this->request->authToken,
                    'social_id'=> $this->request->id,
                    'photo_url'=> $this->request->photoUrl,
                    'provider'=> $this->request->provider
                ]);

                $res = Auth::attempt(['email'=>$this->request->email, 'password'=> $password]);
                $authUser = Auth::user();
                $token =  $authUser->createToken($this->secret)->accessToken;
                return response()->json([
                    'status'=>'success',
                    'token'=>$token,
                    'data' => $authUser,
                    'message' => 'Login Successful'
                ],200);
            }
            else{
                Auth::attempt(['email'=>$this->request->email, 'password'=> ($user['first_name'].'@12345')]);
                $authUser = Auth::user();
                $token =  $authUser->createToken($this->secret)->accessToken;
                return response()->json([
                    'status'=>'success',
                    'token'=>$token,
                    'data' => $authUser,
                    'message' => 'Login Successful'
                ],200);
            }
        }
        catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }

    }


    /**
    * API Function to Authenticate User
    *
    * @return status, token, message
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function authenticateUser(UserRepository $UserRepository){
        try{
            $validator = Validator::make($this->request->all(), [
                'email' => 'required',
                'password' =>'required'
            ]);

            if ($validator->fails()) {
               
                # code...
                throw new Exception("Required parameter missing", 1);
            }
            $check_attempts = User::where('email', $this->request->email)->first();
            if($check_attempts['login_attempts']<3){
                if(Auth::attempt(['email'=>$this->request->email, 'password'=> $this->request->password])){
                    $user = Auth::user();
                    $user->login_attempts = 0;
                    $user->save();
                    $user['image_path'] = $this->profile_picture_upload_path;
                    $token =  $user->createToken($this->secret)->accessToken;
                    return response()->json([
                        'status'=>'success',
                        'token'=>$token,
                        'data' => $user,
                        'secret' => $this->secret,
                        'message' => 'Login Successful'
                    ],200);
                }else{
                    $inc_login_attempts = User::where('email', $this->request->email)->first();
                    if(!empty($inc_login_attempts)){
                        $inc_login_attempts->login_attempts = $inc_login_attempts['login_attempts']+1;
                        $inc_login_attempts->save();
                    }
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid Username or Password'
                    ], 200);
                }
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is locked.'
                ], 200);
            }
                
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //

        try{
            $validator = Validator::make($request->all(), [
                        'id' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                                ], 200);
            }

            $user = User::where('id', $request->id)->first();
            if (empty($user)) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'User not exist!'
                ], 200);
            }else{
                return response()->json([
                    'status'=>'success',
                    'data' => $user,
                    'message' => 'Get user profile Successfully'
                ],200);
            }



        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
       }

    }

    /**
    * 
    * Created By: Aman Jain
    * Created At: 24July2019 
    * para:- email
    */

    public function forgotPassword(Request $request, UserRepository $userRepository) {
        try{
            $validator = Validator::make($request->all(), [
                        'email' => ['required', 'email']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                                ], 200);
            }
           $user = $userRepository->getData(['email'=>$this->request->email],'first',[], 0);
            if (empty($user->id)) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Email address is not registered!'
                ], 200);
            }
            $token = md5(uniqid() . mt_rand(999, 99999));
            $forgot_password = PasswordReset::insert(['email' => $request->email, 'token' => $token]);
            $url= env('APP_URL')."/reset-password/";
            if ($user->save()) {
                $data = ['token' => $token, 'recoverUrl' => $url.$token, 'name' => $user->first_name, 'email' => $user->email];
                try {
                    Mail::to($request->email)->send(new ForgotPassword($data));
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e,
                        'error_details' => 'on line : '.$e->getLine().' on file : '.$e->getFile(),
                    ], 200);
                }
                return response()->json([
                            'status' => 'success',
                            'message' => 'An email has been sent to you for recovering account!'
                ]);
            } else {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Something went wrong, not able to process forgot password request!'
                                ], 200);
            }
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
    * Function to recover account
    * Created By: Aman Jain
    * Created At: 24July2019 
    * para:- password_confirmation,password,token
    */

    public function recoverAccount(Request $request) {
        try{        
            $validator = Validator::make($request->all(), [
                        'token' => ['required'],
                        'password' => ['required', 'min:6', 'max:15','confirmed']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                                ], 200);
            }
            $queryResponse = PasswordReset::where('token', $request->token)->first();
            if (empty($queryResponse->id)) {
                return response()->json([
                            'status' => 'invalidToken',
                            'message' => 'Invalid url for verifying account!'
                                ], 200);
            }
            $new_password = Hash::make($request->password);
            $user = User::where('email', $queryResponse->email)->update(['password' => $new_password,'is_active' => '1','login_attempts' =>0]);
            $queryResponse->token = ''; 
            if ($queryResponse->save()) {
                return response()->json([
                            'status' => 'success',
                            'message' => 'Password has been successfully updated!'
                ]);
            } else {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Something went wrong, not able to update password, try again!'
                                ], 200);
            }
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Error : '.$ex->getMessage(),
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
    * Function to change password
    * Created By: Aman Jain
    * Created At: 24July2019 
    * para:- password_confirmation,password,current,id
    */

    public function changePassword(Request $request) {
        try{ 
            $validator = Validator::make($request->all(), [
                        'current' => ['required'],
                        'id' => ['required'],
                        'password' => ['required', 'min:6', 'max:15','confirmed']
            ]);
            $new_password = $request->password;
            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $field_name => $messages){
                    throw new Exception($messages[0], 1);
                }
            }   
            $user = User::where('id', $request->id)->first();
            if (!Hash::check($request->current, $user->password)) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Current password does not match'
                        ], 200);
            }
            $user->password = Hash::make($new_password);
            $user->save();
            if ($user) {
                return response()->json([
                            'status' => 'success',
                            'message' => 'Password has been successfully updated!'
                ]);
            } else {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Something went wrong, not able to update password, try again!'
                                ], 200);
            }
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Error : '.$ex->getMessage(),
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }    
    } 
     
    /**
    * Function to send verification code
    * Created By: Aman Jain
    * Created At: 25July2019 
    * para:- email,password
    */

    public function sendVerify(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                        'email' => ['required', 'email']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                                ], 200);
            }
            $user = User::where('email', $request->email)->first();
            if (empty($user->id)) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Email address is not registered!'
                ], 200);
            }
            $token = encrypt($request->email);
            $url = env('APP_URL');
            $data = ['token' => $token, 'recoverUrl' => $url.$token, 'name' => $user->first_name, 'email' => $user->email];  
            
            try {
                    Mail::to($request->email)->send(new Verification($data));
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'error_details' => 'on line : '.$e->getLine().' on file : '.$e->getFile(),
                    ], 200);
                }
                return response()->json([
                            'status' => 'success',
                            'message' => 'An email has been sent to you for recovering account!'
                ]);
            

        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
    * Function to verify user account
    * Created By: Aman Jain
    * Created At: 25July2019 
    * para:- token
    */

    public function Verify(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                        'token' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                                ], 200);
            }
            $email = decrypt($request->token);
            $user = User::where('email', $email)->first();
            if (empty($user->id)) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Token is not vaild'
                ], 200);
            }
            
            if($user->email_verified_at == NULL){
                $user->is_active ='1';
                $user->email_verified_at = date('Y-m-d H:i:s') ; 
                if($user->save()){
                    return response()->json([
                                'status' => 'success',
                                'message' => 'Email has been verified.'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => 'invaild',
                    'message' => 'Email already verified.'
                ]);
            } 
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        } 
            
    }

    /**
    * Function to upload document
    * Created By: Aman Jain
    * Created At: 25July2019 
    * para:- myfile,user_id
    */

    public function documentUpload(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                        'image' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json([
                            'status' => 'invalid',
                            'message' => implode('<br />', $validator->errors()->all())
                ]);
            }
            $arrImages = [];
            $image=$request->file('image');
                
                $parts = pathinfo($image->getClientOriginalName());
                $extension = strtolower($parts['extension']);
                $imageName = uniqid() . mt_rand(999, 9999) . '.' . $extension;
                
                if ($image->move(public_path() . '/images/documents/', $imageName)) {
                    $simg = Document::insert(['user_id' =>$request->user_id,'image' => $imageName]);
                    $arrImages[] = [
                        'thumbPath' => url('/images/documents/' . $imageName)
                    ];
                }

            if (count($arrImages) > 0) {
                return response()->json([
                            'status' => 'success',
                            'message' => 'Document successfully uploaded!',
                            'images' => $arrImages
                                ], 200);
            } else {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Request failed, please try again!'
                                ], 200);
            }
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        } 
    }
      /**
    * API Function to Update Admin Details
    *
    * @return status, data, message
    * Created By: Pankaj Joshi
    * Created At: 20July2019 
    */
    public function updateAdminData(UserRepository $userRepository, Request $request) {
        try{

            $validator = Validator::make($this->request->all(), [
                'email' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile_number' => 'required'
                
            ]);

            if ($validator->fails()) {

            
                throw new Exception("Required parameter missing", 1);
            } 
           $user = $userRepository->getData(['id'=>$this->request->id],'first',[],0);
          
            $update_user = $userRepository->createUpdateData(['id'=> $this->request->id],[
                    'first_name'=> $this->request->first_name,
                    'last_name'=> $this->request->last_name,
                    'email'=> $this->request->email,
                    'mobile_number'=> $this->request->mobile_number
                ]);
                
           return response()->json([
                'status' => 'success',
                'message' => 'Update Successfully',
                'data' => $update_user
            ],200);
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    public function uploadDocument(Request $request)
    {
        try{
            $arrImages = [];
            $image=$request->file('gst_proof');
                $parts = pathinfo($image->getClientOriginalName());
                $extension = strtolower($parts['extension']);
                $imageName = uniqid() . mt_rand(999, 9999) . '.' . $extension;
                
                if ($image->move(public_path() . '/images/documents/', $imageName)) {
                    $arrImages[] = [
                        'gst_proof' => url('/images/documents/' . $imageName)
                    ];
                }
            $image1=$request->file('id_proof');
                $parts1 = pathinfo($image1->getClientOriginalName());
                $extension1 = strtolower($parts1['extension']);
                $imageName1 = uniqid() . mt_rand(999, 9999) . '.' . $extension1;
                
                if ($image1->move(public_path() . '/images/documents/', $imageName1)) {
                   
                    $simg = Document::insert(['user_id' =>$request->user_id,'id_proof' => $imageName1,'gst_copy'=>$imageName,'gst_number'=>$request->gst_number]);

                    $arrImages[] = [
                        'id_proof' => url('/images/documents/' . $imageName1)
                    ];
                }    

            if (count($arrImages) > 0) {
                return response()->json([
                            'status' => 'success',
                            'message' => 'Document successfully uploaded!',
                            'images' => $arrImages
                                ], 200);
            } else {
                return response()->json([
                            'status' => 'error',
                            'message' => 'Request failed, please try again!'
                                ], 200);
            }
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Oops something went wrong.',
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        } 
    }

    /**
    * API Function to Update User Profile
    *
    * @return status, data, message
    * Created By: Ram Krishna Murthy
    * Created At: 12August2019 
    */
    public function update_profile(UserRepository $userRepository, Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'first_name'   => 'required|max:255',
                'last_name' => 'required|max:255',
                'country' => 'required',
                'payment_method' => 'required'
            ]);

            $validator_email = Validator::make($request->all(),[
                'email' => 'email|unique:users,email,'.$request->id,
            ]);
            $validator_phone = Validator::make($request->all(),[
                'mobile_number' => 'required|unique:users,mobile_number,'.$request->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 200);
            }

            if ($validator_email->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The email has already been taken.',
                ], 200);
            }

            if ($validator_phone->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The phone number has already been taken.',
                ], 200);
            }
            $update_user = $userRepository->createUpdateData(['id'=> $request->id],$request->all());
            $update_user['image_path'] = $this->profile_picture_upload_path;
           return response()->json([
                'status' => 'success',
                'message' => 'Profile Data Updated Successfully',
                'data' => $update_user
            ],200);
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Error : '.$ex->getMessage(),
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }

    /**
    * API Function to Add or Update User Profile Image
    *
    * @return file, id
    * Created By: Yashwant Rautela
    * Created At: 19August2019 
    */
    public function updateProfileImage(Request $request, UserRepository $userRepository){
        try{
            $validator = Validator::make($this->request->all(), [
                'user_id' => 'required',
                'photo_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                # code...
                foreach ($validator->messages()->getMessages() as $field_name => $messages){
                    throw new Exception($messages[0], 1);
                }
            }

            $userData = $userRepository->getData(['id' => $request->user_id], 'first',[],0);

            if (!empty($userData['photo_url'])) {
                if(file_exists(public_path($this->profile_picture_upload_path.$userData['photo_url']))) {
                    unlink(public_path($this->profile_picture_upload_path.$userData['photo_url']));
                }
            }

            $file = $request->file('photo_url');
            $file_title = $file->getClientOriginalName();
            $file_title = str_replace(" ","",$file_title);
            $file_name = strtotime(Carbon::now()).'_'.$file_title;

            $file->move(public_path($this->profile_picture_upload_path), $file_name);

            $responseUserData = $userRepository->createUpdateData(['id'=>$request->user_id],['photo_url'=>$file_name]);

            $responseUserData['image_path'] = $this->profile_picture_upload_path;
            return response()->json([
                'status' => 'success',
                'message' => 'Profile Image Uploaded Successfully',
                'data' => $responseUserData,
                'image_path' => $this->profile_picture_upload_path
            ],200);
        }catch (\Exception $ex){
            return response()->json([
                'status' => 'error',
                'message' => 'Error : '.$ex->getMessage(),
                'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
            ], 200);
        }
    }
}
 