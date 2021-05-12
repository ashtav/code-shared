<?php 

public function register(Request $request){
    try {

        $arrValid = [
            'name' => 'required|max:50|min:5',
            'username' => 'required|unique:users|max:100|min:5',
            'password' => 'required|confirmed|min:6|max:30',
        ];

        $request->validate(
            $arrValid,
            [
                'username.required' => 'Inputkan nama pengguna akun telegram Anda.',
                'username.unique' => 'Username ini sudah terpakai.',
                'password.confirmed' => 'Konfirmasi password wajib diisi.',
            ]
        );

        $key = generateRandomString(15);

        $input = $request->all();
        $input['name'] = ucwords($request->name);
        $input['password'] = app('hash')->make($input['password']);
        $input['remember_token'] = $key;

        $u_agent = $_SERVER['HTTP_USER_AGENT'];

        $user = User::create($input);

        $responses = [
            'ok' => true,
            'data' => $user,
            'message' => 'Registration has been success.',
            'key' => $key
        ];

        $this->telebot('
            [NEW REGISTRATION]
Name: '.ucwords($request->name).'
Telegram Username: @'.$request->username.'
Browser: '.$u_agent.'
        ');

        return new JsonResponse($responses, 201);
    } catch (\Illuminate\Validation\ValidationException $e ) {
        $arrError = $e->errors();

        foreach ($arrError as $key => $value) {
            $arrImplode[] = implode(', ', $arrError[$key]);
        }

        $arrResponse = [
            'data' => [],
            'message' => $arrImplode[0],
        ];

        return response()->json($arrResponse, 422);
    }
    
    catch (\Throwable $th) {
        throw new HttpException(400, $th);
    }
}