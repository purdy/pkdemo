<?php

namespace App\Controllers;

use lbuchs\WebAuthn\WebAuthn;

class Account extends BaseController {
  public function checkEmail() {
    $email = $this->request->getJsonVar('email');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $user_model = model('User');
      $user = $user_model->where([
        'email' => $email,
      ])->first();
      if ($user) {
        $response = ['status' => 'exists', 'message' => 'Email already exists.'];
      }
      else {
        $response = ['status' => 'available', 'message' => 'Email is available.'];
        $guid = random_bytes(3);
        $domain = "lndo.site";
        $webauthn = new WebAuthn("Simple Passkey App", $domain);
        $response['create_args'] = $webauthn->getCreateArgs($guid, $email, $email);
        $session = session();
        $session->set([
          'email' => $email,
          'guid' => $guid,
          'challenge' => ($webauthn->getChallenge())->getBinaryString()
        ]);
      }
    }
    else {
      $response = ['status' => 'error', 'message' => 'Invalid email address.'];
    }
    return $this->response->setJSON($response);
  }

  public function index(): string {
    return view('account');
  }
}
