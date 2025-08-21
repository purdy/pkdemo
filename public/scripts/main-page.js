document.addEventListener('DOMContentLoaded', function() {
  // Add event listener for the "Create Account" button
  const createAccountButton = document.getElementById('create-account-button');
  if (createAccountButton) {
    createAccountButton.addEventListener('click', async function(e) {
      e.preventDefault();
      // check to see if credential management is supported
      if (typeof window.PublicKeyCredential !== 'undefined'
        && typeof window.PublicKeyCredential.isConditionalMediationAvailable === 'function'
      ) {
        const email = document.getElementById('email').value;
        if (email) {
          try {
            // post email to the server to check if it exists
            const response = await fetch('/check-email', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            if (data.status == 'available') {
              // let jsonArgs = data.create_args;
              // helper.bta(createArgs);
              try {
                data.create_args.publicKey = window.PublicKeyCredential.parseCreationOptionsFromJSON(data.create_args.publicKey);
                const cred = await navigator.credentials.create(data.create_args);
                const pubResp = cred.response;
                const payload = {
                  email: email,
                  credential: {
                    id: cred.id,
                    rawId: toBase64Url(cred.rawId),
                    type: cred.type,
                    response: {
                      attestationObject: toBase64Url(pubResp.attestationObject),
                      clientDataJSON: toBase64Url(pubResp.clientDataJSON)
                    }
                  }
                };
                console.log(payload);
                const createResponse = await fetch('/create-account', {
                  headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                  },
                  method: 'POST',
                  body: JSON.stringify(payload)
                });
                const createData = await createResponse.json();
                if (createData.status === 'success') {
                  window.location.href = '/account';
                }
                else {
                  alert('Failed to create account: ' + createData.message);
                }
              }
              catch (error) {
                console.error('Error creating credential:', error);
                alert('Failed to create credential. Please try again.');
                return;
              }
            }
            else {

            }
          } catch (error) {
            console.error('Error checking email:', error);
            alert('Failed to check email availability. Please try again.');
          }
          // if it does, we need to ask the user to click the log in link to log in instead
          // if it doesn't, then we have creation args that we can pass into the credential creation
          // once we have the credential back, we can post it to the server to create the account
        }
      }
    });
  }
  const loginButton = document.getElementById('login-btn');
  if (loginButton) {
    loginButton.addEventListener('click', async function(e) {
      e.preventDefault();
      let response = await fetch('/login-preflight');
      let data = await response.json();
      data.publicKey = window.PublicKeyCredential.parseRequestOptionsFromJSON(data.publicKey);
      console.log(data);
      let credential = await navigator.credentials.get(data);
      console.log(credential);
      let payload = {
        id: credential.rawId ? toBase64Url(credential.rawId) : null,
        client: credential.response.clientDataJSON ? toBase64Url(credential.response.clientDataJSON) : null,
        auth: credential.response.authenticatorData ? toBase64Url(credential.response.authenticatorData) : null,
        sig: credential.response.signature ? toBase64Url(credential.response.signature) : null,
        user: credential.response.userHandle ? toBase64Url(credential.response.userHandle) : null
      };
      response = await fetch('/passkey-login', {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const responseData = await response.json();
      if (responseData.status === 'success') {
        window.location.href = '/account';
      }
      else {
        alert('Failed to create account: ' + responseData.message);
      }
    });
  }

  function toBase64Url(ab) {
    const b64 = btoa(String.fromCharCode(...new Uint8Array(ab)));
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }

  /**
   * Convert a ArrayBuffer to Base64
   * @param {ArrayBuffer} buffer
   * @returns {String}
   */
  function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
  }
});

// Helper functions.
var helper = {
  // (A1) ARRAY BUFFER TO BASE 64
  atb: b => {
    let u = new Uint8Array(b), s = "";
    for (let i = 0; i < u.byteLength; i++) { s += String.fromCharCode(u[i]); }
    return btoa(s);
  },

  // (A2) BASE 64 TO ARRAY BUFFER
  bta: o => {
    let pre = "=?BINARY?B?", suf = "?=";
    for (let k in o) {
      if (typeof o[k] == "string") {
        let s = o[k];
        if (s.substring(0, pre.length) == pre && s.substring(s.length - suf.length) == suf) {
          let b = window.atob(s.substring(pre.length, s.length - suf.length)),
            u = new Uint8Array(b.length);
          for (let i = 0; i < b.length; i++) { u[i] = b.charCodeAt(i); }
          o[k] = u.buffer;
        }
      } else { helper.bta(o[k]); }
    }
  }
};
