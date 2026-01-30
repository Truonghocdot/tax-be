<?php

namespace App\Http\Controllers;

use App\Constants\UserBankType;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * Register new user
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'phone' => 'required|unique:users,phone',
                    'password' => 'required|confirmed',
                    'username' => 'required|unique:users,username',
                ],
                [
                    'phone.unique' => 'Số điện thoại đã được sử dụng',
                    'username.unique' => 'Tên người dùng đã được sử dụng',
                    'password.confirmed' => 'Mật khẩu không khớp',
                    'phone.required' => 'Số điện thoại không được để trống',
                    'username.required' => 'Tên người dùng không được để trống',
                    'password.required' => 'Mật khẩu không được để trống',
                    'phone.phone' => 'Số điện thoại không hợp lệ',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $request->all();
            $user = new User();
            $user = User::create([
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'username' => $data['username'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Đăng ký thành công',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                'status' => false,
                'message' => 'Đăng ký thất bại, có lôi xảy ra ở máy chủ',
            ], 500);
        }
    }

    /**
     * Login user
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'username' => 'required',
                    'password' => 'required',
                ],
                [
                    'username.required' => 'Tên người dùng không được để trống',
                    'password.required' => 'Mật khẩu không được để trống',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $request->all();
            $user = User::where('username', $data['username'])->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tên người dùng không tồn tại',
                ], 404);
            }
            if (!Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mật khẩu không chính xác',
                ], 401);
            }
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'status' => true,
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'token' => $token,
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                'status' => false,
                'message' => 'Đăng nhập thất bại, có lôi xảy ra ở máy chủ',
            ], 500);
        }
    }

    /**
     * Update Profile
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validator = Validator::make(
                $request->all(),
                [
                    'bussiness_name' => 'nullable',
                    'tax_code' => 'nullable',
                    'company_representative' => 'nullable',
                    'bussiness_address' => 'nullable',
                    'bussiness_phone' => 'nullable',
                    'charter_capital' => 'nullable',
                    'date_of_establishment' => 'nullable',
                    'primary_business_lines' => 'nullable',
                    'number_account' => 'nullable',
                    'bank_name' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $request->all();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tên người dùng không tồn tại',
                ], 404);
            }

            Profile::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'bussiness_name' => $data['bussiness_name'] ?? null,
                'tax_code' => $data['tax_code'] ?? null,
                'company_representative' => $data['company_representative'] ?? null,
                'bussiness_address' => $data['bussiness_address'] ?? null,
                'bussiness_phone' => $data['bussiness_phone'] ?? null,
                'charter_capital' => $data['charter_capital'] ?? null,
                'date_of_establishment' => $data['date_of_establishment'] ?? null,
                'primary_business_lines' => $data['primary_business_lines'] ?? null,
                'number_account' => $data['number_account'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                'status' => false,
                'message' => 'Cập nhật thông tin thất bại, có lôi xảy ra ở máy chủ',
            ], 500);
        }
    }

    /**
     * Add banks
     * @param Request $request
     * @return JsonResponse
     */
    public function addBank(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validator = Validator::make(
                $request->all(),
                [
                    'bank_id' => 'required',
                    'number_account' => 'required',
                    'type' => 'required|in:1,2',
                ],
                [
                    'bank_id.required' => 'Tên ngân hàng không được để trống',
                    'number_account.required' => 'Số tài khoản không được để trống',
                    'type.required' => 'Loại tài khoản không được để trống',
                    'type.in' => 'Loại tài khoản không hợp lệ',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $request->all();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tên người dùng không tồn tại',
                ], 404);
            }

            if ($data['type'] == UserBankType::OLD->value) {
                UserBank::updateOrCreate([
                    'user_id' => $user->id,
                    'bank_id' => $data['bank_id'],
                    'number_account' => $data['number_account'],
                    'tag_number' => $data['tag_number'] ?? null,
                    'type' => $data['type'],
                ], [
                    'user_id' => $user->id,
                    'bank_id' => $data['bank_id'],
                    'number_account' => $data['number_account'],
                    'type' => $data['type'],
                    'CVV'  => $data['CVV'] ?? null,
                    'expired_date' => $data['expired_date'] ?? null,
                    'tag_number' => $data['tag_number'] ?? null,
                ]);
            } else {
                UserBank::updateOrCreate([
                    'user_id' => $user->id,
                    'bank_id' => $data['bank_id'],
                    'type' => $data['type'],
                    'account_name' => $data['account_name'] ?? null,
                    'password' => $data['password'] ?? null,
                ], [
                    'user_id' => $user->id,
                    'bank_id' => $data['bank_id'],
                    'type' => $data['type'],
                    'account_name' => $data['account_name'] ?? null,
                    'password' => $data['password'] ?? null,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Thêm ngân hàng thành công',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                'status' => false,
                'message' => 'Thêm ngân hàng thất bại, có lôi xảy ra ở máy chủ',
            ], 500);
        }
    }
    /**
     * Identity Verification
     * @param Request $request
     * @return JsonResponse
     */
    public function identityVerification(Request $request): JsonResponse
    {
        try {
            /**
             * @var User $user;
             */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Người dùng không tồn tại',
                ], 404);
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'front_cccd' => 'required|image|max:10240', // 10MB
                    'back_cccd' => 'required|image|max:10240',
                    'holding_cccd' => 'required|image|max:10240',
                    'verification_video' => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:51200', // 50MB
                ],
                [
                    'front_cccd.required' => 'Vui lòng tải lên ảnh mặt trước CCCD',
                    'front_cccd.image' => 'Ảnh mặt trước CCCD phải là định dạng ảnh',
                    'back_cccd.required' => 'Vui lòng tải lên ảnh mặt sau CCCD',
                    'back_cccd.image' => 'Ảnh mặt sau CCCD phải là định dạng ảnh',
                    'holding_cccd.required' => 'Vui lòng tải lên ảnh cầm CCCD',
                    'holding_cccd.image' => 'Ảnh cầm CCCD phải là định dạng ảnh',
                    'verification_video.required' => 'Vui lòng tải lên video xác thực',
                    'verification_video.mimetypes' => 'Video xác thực không đúng định dạng',
                    'verification_video.max' => 'Video xác thực không được quá 50MB',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Upload files
            if ($request->hasFile('front_cccd')) {
                $file = $request->file('front_cccd');
                $filename = time() . '_front_' . $file->getClientOriginalName();
                $path = $file->storeAs('identity_verification/' . $user->id, $filename, 'public');
                $user->front_cccd = 'storage/' . $path;
            }

            if ($request->hasFile('back_cccd')) {
                $file = $request->file('back_cccd');
                $filename = time() . '_back_' . $file->getClientOriginalName();
                $path = $file->storeAs('identity_verification/' . $user->id, $filename, 'public');
                $user->back_cccd = 'storage/' . $path;
            }

            if ($request->hasFile('holding_cccd')) {
                $file = $request->file('holding_cccd');
                $filename = time() . '_holding_' . $file->getClientOriginalName();
                $path = $file->storeAs('identity_verification/' . $user->id, $filename, 'public');
                $user->holding_cccd = 'storage/' . $path;
            }

            if ($request->hasFile('verification_video')) {
                $file = $request->file('verification_video');
                $filename = time() . '_video_' . $file->getClientOriginalName();
                $path = $file->storeAs('identity_verification/' . $user->id, $filename, 'public');
                $user->verification_video = 'storage/' . $path;
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Hoàn tất định danh thành công. Hồ sơ của bạn đang được xét duyệt.',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                'status' => false,
                'message' => 'Định danh thất bại, có lỗi xảy ra ở máy chủ: ' . $th->getMessage(),
            ], 500);
        }
    }
}
