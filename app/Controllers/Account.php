<?php

namespace App\Controllers;

use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;

class Account extends BaseController {
  protected $domain = 'lndo.site';

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
        $user_model->save([
          'email' => $email,
        ]);
        $user_id = $user_model->getInsertID();
        $webauthn = new WebAuthn("Simple Passkey App", $this->domain, NULL, true);
        $response['create_args'] = $webauthn->getCreateArgs($user_id, $email, $email);
        $response['create_args']->attestation = 'none';
        $session = session();
        $session->set([
          'email' => $email,
          'user_id' => $user_id,
          'challenge' => $webauthn->getChallenge()->getBinaryString()
        ]);
      }
    }
    else {
      $response = ['status' => 'error', 'message' => 'Invalid email address.'];
    }
    log_message('debug', "Email check response: " . json_encode($response));
    return $this->response->setJSON($response);
  }

  public function loginPreflight() {
    $webauthn = new WebAuthn("Simple Passkey App", $this->domain, null, true);
    $args = $webauthn->getGetArgs();
    log_message('debug', "Login preflight args: " . json_encode($args));
    $session = session();
    $session->set('challenge', $webauthn->getChallenge()->getBinaryString());
    return $this->response->setJSON($args);
  }

  public function passkeyLogin() {
    $session = session();
    $challenge = $session->get('challenge');
    $webauthn = new WebAuthn("Simple Passkey App", $this->domain);
    $request_data = $this->request->getJson(true);
    $credential_id = $this->b64urlDecode($request_data['id']);
    $client_data = $this->b64urlDecode($request_data['client']);
    $authenticator_data = $this->b64urlDecode($request_data['auth']);
    $signature = $this->b64urlDecode($request_data['sig']);
    log_message('debug', 'Credential ID: ' . base64_encode($credential_id));
    log_message('debug', 'Client data: ' . $client_data);
    log_message('debug', 'Authenticator data: ' . $authenticator_data);
    log_message('debug', 'Signature: ' . $signature);
    $passkey_model = model('Passkey');
    $passkey = $passkey_model->where('credential_id', base64_encode($credential_id))->first();
    if ($passkey) {
      try {
        if ($webauthn->processGet($client_data, $authenticator_data, $signature, base64_decode($passkey['public_key']), $challenge)) {
          log_message('debug', 'Credential validation successful');
          $session->set('logged_in', true);
          $session->set('user_id', $passkey['user_id']);
          return $this->response->setJSON(['status' => 'success', 'message' => 'Login successful']);
        }
        else {
          log_message('error', 'Credential validation failed');
        }
      }
      catch (WebAuthnException $e) {
        log_message('error', 'Error processing credential: ' . $e->getMessage());
      }
    }
    else {
      log_message('error', "No passkey found for credential ID " . base64_encode($credential_id));
    }
  }

  private function b64urlDecode(string $data): string {
    $data = strtr($data, '-_', '+/');
    $pad = strlen($data) % 4;
    if ($pad) { $data .= str_repeat('=', 4 - $pad); }
    return base64_decode($data);
  }

  public function createAccount() {
    $request_data = $this->request->getJson(true);
    $email = $request_data['email'];
    log_message('debug', 'Received email: ' . print_r($email, true));
    $session = session();
    $credential = $request_data['credential'];
    log_message('debug', 'Received credential: ' . print_r($credential, true));

    $webauthn = new WebAuthn("Simple Passkey App", $this->domain);

    $client_data = $this->b64urlDecode($credential['response']['clientDataJSON']);
    $attestation_data = $this->b64urlDecode($credential['response']['attestationObject']);
    log_message('debug', 'Client data: ' . $client_data);
    log_message('debug', 'Attestation data: ' . $attestation_data);

    $challenge = $session->get('challenge');
    log_message('debug', 'Challenge from session: ' . ($challenge ? 'present' : 'missing'));
    log_message('debug', 'Challenge length: ' . ($challenge ? strlen($challenge) : 'N/A'));
    log_message('debug', 'Email from session: ' . $session->get('email'));

    if (!$challenge) {
      log_message('error', 'No challenge found in session');
      return $this->response->setJSON(['status' => 'error', 'message' => 'Session expired or invalid']);
    }

    try {
      // First try with all formats
      $data = $webauthn->processCreate($client_data, $attestation_data, $challenge);
      log_message('debug', 'processCreate returned: ' . var_export($data, true));

      // If we get here, the credential was processed successfully
      log_message('debug', 'Credential validation successful');

      $user_id = $session->get('user_id');
      $nickname = "Passkey created on " . $this->request->getUserAgent();
      $passkey_data = [
        'user_id' => $user_id,
        'nickname' => $nickname,
        'credential_id' => base64_encode($data->credentialId),
        'public_key' => base64_encode($data->credentialPublicKey),
      ];
      $passkey_model = model('Passkey');
      $passkey_model->save($passkey_data);
      $session->set('logged_in', true);

      // TODO: Save the credential data to the database and create the user account
      return $this->response->setJSON(['status' => 'success', 'message' => 'Account created successfully']);

    }
    catch (WebAuthnException $e) {
      log_message('error', 'Error processing credential creation: ' . $e->getMessage());
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }

  }

  public function index() {
    // Check to see if user is really logged in.
    $session = session();
    $user_model = model('User');
    if (!$session->get('logged_in')) {
      return redirect()->to('/');
    }
    $user = $user_model->find($session->get('user_id'));
    return view('account', [
      'user' => $user,
    ]);
  }

  public function logout() {
    $session = session();
    $session->destroy();
    return redirect()->to('/');
  }

  // // Helper function for base64url decoding
  // protected function base64url_decode($data) {
  //   return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
  // }

}
