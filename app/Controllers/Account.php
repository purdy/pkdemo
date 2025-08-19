<?php

namespace App\Controllers;

use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;

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
        $user_model->save([
          'email' => $email,
        ]);
        $user_id = $user_model->getInsertID();
        $domain = "pkdemo.lndo.site";
        $webauthn = new WebAuthn("Simple Passkey App", $domain, NULL, true);
        $response['create_args'] = $webauthn->getCreateArgs($user_id, $email, $email);
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
    $response['create_args']->attestation = 'none';
    log_message('debug', "Email check response: " . json_encode($response));
    return $this->response->setJSON($response);
  }

  public function loginPreflight() {
    $domain = "pkdemo.lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);
    $args = $webauthn->getGetArgs();
    $session = session();
    $session->set('challenge', $webauthn->getChallenge()->getBinaryString());
    return $this->response->setJSON($args);
  }

  public function passkeyLogin() {
    $domain = "pkdemo.lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);

    $crendential_data = json_decode($this->request->getPost('credential'), true);
    $credential_id = bin2hex(base64_decode($crendential_data['id']));
    $unique_id = bin2hex(base64_decode($crendential_data['user']));
    log_message('debug', 'Credential ID: ' . $credential_id);
    log_message('debug', 'Unique ID: ' . $unique_id);
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

    $domain = "pkdemo.lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);

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
      log_message('debug', 'About to call processCreate with:');
      log_message('debug', 'Client data length: ' . strlen($client_data));
      log_message('debug', 'Attestation data length: ' . strlen($attestation_data));
      log_message('debug', 'Challenge length: ' . strlen($challenge));

      // Log the raw attestation data in hex format for debugging
      log_message('debug', 'Attestation data (hex): ' . bin2hex($attestation_data));

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
        'credential_id' => bin2hex($data->credentialId),
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

  public function index(): string {
    // Check to see if user is really logged in.
    $session = session();
    if (!$session->get('logged_in')) {
      return redirect()->to('/');
    }
    return view('account');
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
