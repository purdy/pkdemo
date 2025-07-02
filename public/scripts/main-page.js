document.addEventListener('DOMContentLoaded', function() {
  // Add event listener for the "Create Account" button
  const createAccountButton = document.getElementById('create-account-button');
  if (createAccountButton) {
    createAccountButton.addEventListener('click', function(e) {
      e.preventDefault();
      // check to see if credential management is supported
      // post email to the server to check if it exists
      // if it does, we need to ask the user to click the log in link to log in instead
      // if it doesn't, then we have creation args that we can pass into the credential creation
      // once we have the credential back, we can post it to the server to create the account
      if (typeof window.PublicKeyCredential !== 'undefined'
        && typeof window.PublicKeyCredential.isConditionalMediationAvailable === 'function'
      ) {
        const email = document.getElementById('email').value;
        if (email) {
        }
      }
    });
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
