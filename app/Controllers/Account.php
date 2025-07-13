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
        $guid = random_bytes(3);
        $domain = "lndo.site";
        $webauthn = new WebAuthn("Simple Passkey App", $domain);
        $response['create_args'] = $webauthn->getCreateArgs($guid, $email, $email);
        // $response['create_args'] = $webauthn->getCreateArgs($guid, $email, $email);
        $session = session();
        $session->set([
          'email' => $email,
          'guid' => $guid,
          'challenge' => $webauthn->getChallenge()->getBinaryString()
        ]);
      }
    }
    else {
      $response = ['status' => 'error', 'message' => 'Invalid email address.'];
    }
    return $this->response->setJSON($response);
  }

  public function loginPreflight() {
    $domain = "lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);
    $args = $webauthn->getGetArgs();
    $session = session();
    $session->set('challenge', $webauthn->getChallenge()->getBinaryString());
    return $this->response->setJSON($args);
  }

  public function passkeyLogin() {
    $domain = "lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);

    $crendential_data = json_decode($this->request->getPost('credential'), true);
    $credential_id = bin2hex(base64_decode($crendential_data['id']));
    $unique_id = bin2hex(base64_decode($crendential_data['user']));
    log_message('debug', 'Credential ID: ' . $credential_id);
    log_message('debug', 'Unique ID: ' . $unique_id);

  }

  public function createAccount() {
    $email = $this->request->getPost('email');
    $session = session();
    $credential_json = $this->request->getPost('credential');
    log_message('debug', 'Received credential: ' . $credential_json);

    $domain = "lndo.site";
    $webauthn = new WebAuthn("Simple Passkey App", $domain);

    $credential = json_decode($credential_json, true);

    $client_data = base64_decode($credential['clientDataJSON']);
    $attestation_data = base64_decode($credential['attestationObject']);
    log_message('debug', 'Client data: ' . $client_data);
    log_message('debug', 'Attestation data: ' . $attestation_data);
    $challenge = $session->get('challenge');
    log_message('debug', 'Challenge from session: ' . ($challenge ? 'present' : 'missing'));
    log_message('debug', 'Challenge length: ' . ($challenge ? strlen($challenge) : 'N/A'));
    log_message('debug', 'Email from session: ' . $session->get('email'));
    log_message('debug', 'GUID from session: ' . ($session->get('guid') ? 'present' : 'missing'));

    if (!$challenge) {
      log_message('error', 'No challenge found in session');
      return $this->response->setJSON(['status' => 'error', 'message' => 'Session expired or invalid']);
    }

    try {
      log_message('debug', 'About to call processCreate with:');
      log_message('debug', 'Client data length: ' . strlen($client_data));
      log_message('debug', 'Attestation data length: ' . strlen($attestation_data));
      log_message('debug', 'Challenge length: ' . strlen($challenge));

      $data = $webauthn->processCreate($client_data, $attestation_data, $challenge);

      log_message('debug', 'processCreate returned: ' . var_export($data, true));
      log_message('debug', 'Data type: ' . gettype($data));
      log_message('debug', 'Data is empty: ' . (empty($data) ? 'true' : 'false'));
      log_message('debug', 'Data === null: ' . ($data === null ? 'true' : 'false'));
      log_message('debug', 'Data === false: ' . ($data === false ? 'true' : 'false'));
      log_message('debug', 'Data === true: ' . ($data === true ? 'true' : 'false'));

      // Check if the method succeeded even if it returns null/empty
      if ($data === null || $data === false) {
        log_message('error', 'processCreate returned null or false - credential validation failed');
        return $this->response->setJSON(['status' => 'error', 'message' => 'Credential validation failed']);
      }

      // If we get here, the credential was processed successfully
      log_message('debug', 'Credential validation successful');

      $user_model = model('User');
      $user_model->save([
        'email' => $session->get('email'),
        'guid' => $session->get('guid'),
      ]);
      $user_id = $user_model->getInsertID();
      $session->set('user_id', $user_id);
      $nickname = "Passkey created on " . $this->request->getUserAgent();
      $passkey_data = [
        'user_id' => $user_id,
        'unique_id' => $session->get('guid'),
        'nickname' => $nickname,
        'credential_id' => bin2hex($data->credentialId),
        'public_key' => base64_encode($data->credentialPublicKey),
      ];
      $passkey_model = model('Passkey');
      $passkey_model->save($passkey_data);

      // TODO: Save the credential data to the database and create the user account
      return $this->response->setJSON(['status' => 'success', 'message' => 'Account created successfully']);

    }
    catch (WebAuthnException $e) {
      log_message('error', 'Error processing credential creation: ' . $e->getMessage());
      return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    }


    // try {
    //   $credential = $webauthn->processCreate(
    //     $client_data,
    //     $attestation_data,
    //     $session->get('challenge'),
    //   );
    //   log_message('debug', 'Credential created successfully: ' . json_encode($credential));
    // }
    // catch (WebAuthnException $e) {
    //   return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
    // }

  }

  public function index(): string {
    return view('account');
  }
}
