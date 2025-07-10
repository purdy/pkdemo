<?php

namespace App\Controllers;

class Account extends BaseController {
  public function checkEmail() {
    $email = $this->request->getJsonVar('email');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $user_model = model('User');
      $user = $user_model->where('email', $email)->first();
      if ($user) {
        $response = ['status' => 'exists', 'message' => 'Email already exists.'];
      }
      else {
        $response = ['status' => 'available', 'message' => 'Email is available.'];
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
