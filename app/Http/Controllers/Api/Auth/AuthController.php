<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
 use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Site\LoginUserRequest;
use App\Http\Requests\Site\StoreUserRequest;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function register(StoreUserRequest $request)
    {
         $data             = $request->except('password_confirmation');
        $data['password'] = $data['password'];
        $data['status']=1;
     // Convert Arabic numbers to Arabic numerals
     $request->merge(['phone' => convertArabicNumbers($request->phone)]);

     // Validate the phone number with additional fields as necessary
     $validatedData = $request->validate([
         'phone' => [
             'required',
             'string',
             'unique:vendors,phone', // Ensure the phone number is unique in the vendors table
          ],
         // Add more validation rules for other fields here as necessary
     ]);
     $data['phone']=convertArabicNumbers($request->phone);
        $user = Vendor::create($data);

        $user->sendOTP();
        // OtpLink($user->phone,$user->verification_code??'-');

        $userWithoutVerificationCode = new User($user->toArray());
         // unset($userWithoutVerificationCode->verification_code);

        return $this->success(data: ['token' => $user->createToken("API TOKEN")->plainTextToken, 'user' => $userWithoutVerificationCode]);
    }

    public function login(LoginUserRequest $request)
    {
        $phone = convertArabicNumbers($request->phone);
        $email = $request->email;

        // Try to find the user based on email or phone
        $vendor = Vendor::where('email', $email)
                        ->orWhere('phone', $phone)
                        ->first();

        if (!$vendor || !Hash::check($request->password, $vendor->password)) {
            // If vendor not found or password mismatch, return error
            $errors = [
                'login' => [__('Invalid credentials')],
            ];

            return $this->validationFailure(errors: $errors);
        }

        // Check if the vendor needs to verify their phone number
        if ($vendor->verification_code != null) {
            $message = __('Hello') . ' ' . $vendor->name . ' ' . __('Please verify Your Phone');

            // Send OTP
            $vendor->sendOTP();
            $otp = $vendor->verification_code;

            return $this->validationFailure(errors: [
                'message' => $message,
                'phone' => str_replace('966', ' ', $vendor->phone),
                'otp' => $otp,
                'verified' => $otp ? false : true,
            ]);
        }

        // Handle account status checks
        if ($vendor->status != 2) {
            if ($vendor->status == 1) {
                return $this->validationFailure(errors: ['message' => __('this account is pending please wait for admin approval')]);
            } elseif ($vendor->status == 0) {
                return $this->validationFailure(errors: ['message' => __('this account is blocked please contact with admin')]);
            } elseif ($vendor->status == 3) {
                return $this->validationFailure(errors: ['message' => __('this account is rejected please create new account or contact with admin')]);
            }
        }

        // Generate an API token for the authenticated vendor
        $token = $vendor->createToken('API TOKEN')->plainTextToken;

        return $this->success(data: [
            'token' => $token,
            'message' => __('Thank You for verified'),
            'user' => $vendor,
        ]);
    }


    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete(); // Revoke only the current token

            return  $this->success( __('Successfully logged out') );
        }

        return $this->success( __('You have to log in') );

    }
}
