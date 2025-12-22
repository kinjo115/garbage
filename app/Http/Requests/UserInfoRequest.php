<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->user();
        $emailRule = ['required', 'email'];
        
        // 認証済みユーザーの場合、自分のメールアドレスはuniqueチェックから除外
        if ($user) {
            $emailRule[] = 'unique:users,email,' . $user->id;
        } else {
            $emailRule[] = 'unique:users,email';
        }

        return [
            'last_name' => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
            'housing_type_id' => 'required|exists:housing_types,id',
            'postal_code' => 'required|string|regex:/^\d{3}-?\d{4}$/',
            'prefecture_id' => 'required|exists:prefectures,id|in:23', // 愛知県のみ許可
            'city' => 'required|string|max:100|in:名古屋市', // 名古屋市のみ許可
            'town' => 'required|string|max:100',
            'chome' => 'nullable|string|max:20',
            'building_number' => 'nullable|string|max:20',
            'house_number' => 'nullable|string|max:20',
            'apartment_name' => 'nullable|string|max:100',
            'apartment_number' => 'nullable|string|max:30',
            'phone_number' => ['required', 'string', 'regex:/^[\d-]{10,15}$/'],
            'emergency_contact' => ['required', 'string', 'regex:/^[\d-]{10,15}$/'],
            'email' => $emailRule,
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => '姓を入力してください。',
            'last_name.max' => '姓は50文字以内で入力してください。',
            'first_name.required' => '名を入力してください。',
            'first_name.max' => '名は50文字以内で入力してください。',
            'housing_type_id.required' => '住宅種別を選択してください。',
            'housing_type_id.exists' => '選択された住宅種別が無効です。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号は7桁の数字で入力してください。',
            'prefecture_id.required' => '都道府県を選択してください。',
            'prefecture_id.exists' => '選択された都道府県が無効です。',
            'prefecture_id.in' => '名古屋市に在住の方のみ、お申し込みができます。',
            'city.required' => '市区町村を入力してください。',
            'city.max' => '市区町村は100文字以内で入力してください。',
            'city.in' => '名古屋市に在住の方のみ、お申し込みができます。',
            'town.required' => '町名を入力してください。',
            'town.max' => '町名は100文字以内で入力してください。',
            'chome.max' => '丁目は10文字以内で入力してください。',
            'building_number.max' => '番は10文字以内で入力してください。',
            'house_number.max' => '号は10文字以内で入力してください。',
            'apartment_name.max' => 'マンション名は100文字以内で入力してください。',
            'apartment_number.max' => '部屋番号は20文字以内で入力してください。',
            'phone_number.required' => '電話番号を入力してください。',
            'phone_number.regex' => '電話番号は10桁から15桁の数字で入力してください（ハイフンは自動で削除されます）。',
            'emergency_contact.required' => '緊急連絡先を入力してください。',
            'emergency_contact.regex' => '緊急連絡先は10桁から15桁の数字で入力してください（ハイフンは自動で削除されます）。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
        ];
    }
}